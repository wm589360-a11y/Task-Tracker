<?php
require_once __DIR__ . '/../../config/database.php';

class Report {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    // ─── Custom Report Aggregator ──────────────────────────────────────────
    public function generateReport($userId, $params = []) {
        $entity   = $params['entity'] ?? 'tasks';
        $groupBy  = $params['group_by'] ?? 'category';
        $filters  = $params['filters'] ?? [];

        if ($entity === 'time') {
            return $this->generateTimeReport($userId, $groupBy, $filters);
        } elseif ($entity === 'expenses') {
            return $this->generateExpensesReport($userId, $groupBy, $filters);
        } else {
            return $this->generateTaskReport($userId, $groupBy, $filters);
        }
    }

    private function generateTaskReport($userId, $groupBy, $filters) {
        $groupClause = match($groupBy) {
            'status'      => 't.status',
            'priority'    => 't.priority',
            'assigned_to' => "COALESCE(u.name, 'Unassigned')",
            'month'       => "DATE_FORMAT(t.created_at, '%b %Y')",
            'due_month'   => "COALESCE(DATE_FORMAT(t.due_date, '%b %Y'), 'No Due Date')",
            default       => "COALESCE(c.name, 'Uncategorized')"
        };

        $groupAlias = match($groupBy) {
            'status'      => 'Status',
            'priority'    => 'Priority',
            'assigned_to' => 'Assigned User',
            'month'       => 'Created Month',
            'due_month'   => 'Due Month',
            default       => 'Category'
        };

        $sql = "SELECT 
                    $groupClause AS group_name,
                    COUNT(DISTINCT t.id) AS total_tasks,
                    SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) AS completed_tasks,
                    SUM(CASE WHEN t.status = 'Pending' THEN 1 ELSE 0 END) AS pending_tasks,
                    SUM(CASE WHEN t.status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress_tasks,
                    SUM(CASE WHEN t.status != 'Completed' AND t.due_date IS NOT NULL AND t.due_date < CURDATE() THEN 1 ELSE 0 END) AS overdue_tasks,
                    ROUND(COALESCE(SUM(te.duration_minutes), 0) / 60, 2) AS total_hours
                FROM tasks t
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN time_entries te ON t.id = te.task_id
                WHERE t.created_by = :user_id AND t.deleted_at IS NULL";

        $bind = [':user_id' => $userId];

        if (!empty($filters['start_date'])) {
            $sql .= " AND t.created_at >= :start_date";
            $bind[':start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND t.created_at <= :end_date";
            $bind[':end_date'] = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $bind[':status'] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = :priority";
            $bind[':priority'] = $filters['priority'];
        }
        if (!empty($filters['category_id'])) {
            $sql .= " AND t.category_id = :category_id";
            $bind[':category_id'] = $filters['category_id'];
        }
        if (!empty($filters['assigned_to'])) {
            $sql .= " AND t.assigned_to = :assigned_to";
            $bind[':assigned_to'] = $filters['assigned_to'];
        }

        $sql .= " GROUP BY $groupClause ORDER BY total_tasks DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compute completion rates and totals
        $overallTotal = 0;
        $overallCompleted = 0;
        $overallHours = 0;

        foreach ($rows as &$row) {
            $total = (int)$row['total_tasks'];
            $comp  = (int)$row['completed_tasks'];
            $row['completion_rate'] = $total > 0 ? round(($comp / $total) * 100, 1) : 0;
            
            $overallTotal     += $total;
            $overallCompleted += $comp;
            $overallHours     += (float)$row['total_hours'];
        }

        return [
            'entity'       => 'tasks',
            'group_label'  => $groupAlias,
            'summary'      => [
                'total_records'   => $overallTotal,
                'completed_count' => $overallCompleted,
                'completion_rate' => $overallTotal > 0 ? round(($overallCompleted / $overallTotal) * 100, 1) : 0,
                'total_hours'     => round($overallHours, 2)
            ],
            'data' => $rows
        ];
    }

    private function generateTimeReport($userId, $groupBy, $filters) {
        $groupClause = match($groupBy) {
            'user'   => "u.name",
            'month'  => "DATE_FORMAT(te.clock_in, '%b %Y')",
            'status' => "te.status",
            default  => "COALESCE(c.name, 'Uncategorized')"
        };

        $groupAlias = match($groupBy) {
            'user'   => 'User',
            'month'  => 'Clocked Month',
            'status' => 'Status',
            default  => 'Task Category'
        };

        $sql = "SELECT 
                    $groupClause AS group_name,
                    COUNT(te.id) AS total_entries,
                    ROUND(COALESCE(SUM(te.duration_minutes), 0) / 60, 2) AS total_hours,
                    ROUND(COALESCE(SUM(CASE WHEN te.status = 'approved' THEN te.duration_minutes ELSE 0 END), 0) / 60, 2) AS approved_hours,
                    ROUND(COALESCE(SUM(CASE WHEN te.status = 'pending' THEN te.duration_minutes ELSE 0 END), 0) / 60, 2) AS pending_hours,
                    ROUND(COALESCE(AVG(te.duration_minutes), 0) / 60, 2) AS avg_hours_per_entry
                FROM time_entries te
                JOIN users u ON te.user_id = u.id
                LEFT JOIN tasks t ON te.task_id = t.id
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE te.user_id = :user_id";

        $bind = [':user_id' => $userId];

        if (!empty($filters['start_date'])) {
            $sql .= " AND te.clock_in >= :start_date";
            $bind[':start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND te.clock_in <= :end_date";
            $bind[':end_date'] = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['status'])) {
            $sql .= " AND te.status = :status";
            $bind[':status'] = strtolower($filters['status']);
        }

        $sql .= " GROUP BY $groupClause ORDER BY total_hours DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $overallHours = 0;
        $overallEntries = 0;
        foreach ($rows as $row) {
            $overallHours += (float)$row['total_hours'];
            $overallEntries += (int)$row['total_entries'];
        }

        return [
            'entity'      => 'time',
            'group_label' => $groupAlias,
            'summary'     => [
                'total_records' => $overallEntries,
                'total_hours'   => round($overallHours, 2),
                'avg_hours'     => $overallEntries > 0 ? round($overallHours / $overallEntries, 2) : 0
            ],
            'data' => $rows
        ];
    }

    private function generateExpensesReport($userId, $groupBy, $filters) {
        $groupClause = match($groupBy) {
            'user'   => "u.name",
            'month'  => "DATE_FORMAT(e.expense_date, '%b %Y')",
            'status' => "e.status",
            default  => "COALESCE(e.category, 'General')"
        };

        $groupAlias = match($groupBy) {
            'user'   => 'User',
            'month'  => 'Expense Month',
            'status' => 'Status',
            default  => 'Category'
        };

        $sql = "SELECT 
                    $groupClause AS group_name,
                    COUNT(e.id) AS total_expenses,
                    ROUND(COALESCE(SUM(e.amount), 0), 2) AS total_amount,
                    ROUND(COALESCE(AVG(e.amount), 0), 2) AS avg_amount,
                    ROUND(COALESCE(SUM(CASE WHEN e.status = 'approved' THEN e.amount ELSE 0 END), 0), 2) AS approved_amount
                FROM expenses e
                JOIN users u ON e.user_id = u.id
                WHERE e.user_id = :user_id";

        $bind = [':user_id' => $userId];

        if (!empty($filters['start_date'])) {
            $sql .= " AND e.expense_date >= :start_date";
            $bind[':start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND e.expense_date <= :end_date";
            $bind[':end_date'] = $filters['end_date'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :status";
            $bind[':status'] = strtolower($filters['status']);
        }

        $sql .= " GROUP BY $groupClause ORDER BY total_amount DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bind);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $overallAmount = 0;
        $overallCount  = 0;
        foreach ($rows as $row) {
            $overallAmount += (float)$row['total_amount'];
            $overallCount  += (int)$row['total_expenses'];
        }

        return [
            'entity'      => 'expenses',
            'group_label' => $groupAlias,
            'summary'     => [
                'total_records' => $overallCount,
                'total_amount'  => round($overallAmount, 2),
                'avg_amount'    => $overallCount > 0 ? round($overallAmount / $overallCount, 2) : 0
            ],
            'data' => $rows
        ];
    }

    // ─── Saved Reports Management ──────────────────────────────────────────
    public function getSavedReports($userId) {
        $sql = "SELECT * FROM saved_reports WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSavedReportById($id, $userId) {
        $sql = "SELECT * FROM saved_reports WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveReport($userId, $data) {
        $sql = "INSERT INTO saved_reports (user_id, title, description, entity, group_by, metrics, filters, chart_type)
                VALUES (:user_id, :title, :description, :entity, :group_by, :metrics, :filters, :chart_type)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id'     => $userId,
            ':title'       => trim($data['title']),
            ':description' => trim($data['description'] ?? ''),
            ':entity'      => $data['entity'] ?? 'tasks',
            ':group_by'    => $data['group_by'] ?? 'category',
            ':metrics'     => json_encode($data['metrics'] ?? []),
            ':filters'     => json_encode($data['filters'] ?? []),
            ':chart_type'  => $data['chart_type'] ?? 'bar'
        ]);
        return $this->db->lastInsertId();
    }

    public function deleteSavedReport($id, $userId) {
        $sql = "DELETE FROM saved_reports WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':user_id' => $userId]);
    }
}
?>

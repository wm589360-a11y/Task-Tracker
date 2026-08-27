<?php
require_once BASE_PATH . '/config/database.php';

class Project {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all projects with task statistics and metrics
     */
    public function getAll($filters = []) {
        $sql = "SELECT p.*, 
                       u.name as owner_name, 
                       u.email as owner_email,
                       c.name as category_name,
                       COUNT(t.id) as total_tasks,
                       SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
                       SUM(CASE WHEN t.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                       SUM(CASE WHEN t.status = 'Pending' THEN 1 ELSE 0 END) as pending_tasks,
                       SUM(CASE WHEN t.due_date < CURDATE() AND t.status != 'Completed' THEN 1 ELSE 0 END) as overdue_tasks
                FROM projects p
                LEFT JOIN users u ON p.owner_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN tasks t ON p.id = t.project_id AND t.deleted_at IS NULL
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['health'])) {
            $sql .= " AND p.health = :health";
            $params[':health'] = $filters['health'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.code LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " GROUP BY p.id ORDER BY p.updated_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process progress percentage & budget calculations for each project
        foreach ($projects as &$prj) {
            $total = (int)$prj['total_tasks'];
            $comp = (int)$prj['completed_tasks'];
            $prj['progress'] = $total > 0 ? round(($comp / $total) * 100, 1) : 0;

            $budget = (float)$prj['budget'];
            $spent = (float)$prj['spent'];
            $prj['budget_burn'] = $budget > 0 ? round(($spent / $budget) * 100, 1) : 0;
        }

        return $projects;
    }

    /**
     * Get a single project by ID with stats
     */
    public function getById($id) {
        $sql = "SELECT p.*, 
                       u.name as owner_name, 
                       u.email as owner_email,
                       c.name as category_name,
                       COUNT(t.id) as total_tasks,
                       SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks,
                       SUM(CASE WHEN t.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                       SUM(CASE WHEN t.status = 'Pending' THEN 1 ELSE 0 END) as pending_tasks,
                       SUM(CASE WHEN t.due_date < CURDATE() AND t.status != 'Completed' THEN 1 ELSE 0 END) as overdue_tasks
                FROM projects p
                LEFT JOIN users u ON p.owner_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN tasks t ON p.id = t.project_id AND t.deleted_at IS NULL
                WHERE p.id = :id
                GROUP BY p.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $prj = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($prj) {
            $total = (int)$prj['total_tasks'];
            $comp = (int)$prj['completed_tasks'];
            $prj['progress'] = $total > 0 ? round(($comp / $total) * 100, 1) : 0;
            $budget = (float)$prj['budget'];
            $spent = (float)$prj['spent'];
            $prj['budget_burn'] = $budget > 0 ? round(($spent / $budget) * 100, 1) : 0;
        }

        return $prj;
    }

    /**
     * Create a new project
     */
    public function create($data) {
        $sql = "INSERT INTO projects (name, code, description, status, health, owner_id, category_id, budget, spent, start_date, end_date) 
                VALUES (:name, :code, :description, :status, :health, :owner_id, :category_id, :budget, :spent, :start_date, :end_date)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':code' => strtoupper($data['code'] ?? ('PRJ-' . rand(100, 999))),
            ':description' => $data['description'] ?? null,
            ':status' => $data['status'] ?? 'Active',
            ':health' => $data['health'] ?? 'On Track',
            ':owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : null,
            ':category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            ':budget' => $data['budget'] ?? 0.00,
            ':spent' => $data['spent'] ?? 0.00,
            ':start_date' => !empty($data['start_date']) ? $data['start_date'] : null,
            ':end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Update existing project
     */
    public function update($id, $data) {
        $sql = "UPDATE projects 
                SET name = :name, 
                    code = :code, 
                    description = :description, 
                    status = :status, 
                    health = :health, 
                    owner_id = :owner_id, 
                    category_id = :category_id, 
                    budget = :budget, 
                    spent = :spent, 
                    start_date = :start_date, 
                    end_date = :end_date,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':code' => strtoupper($data['code']),
            ':description' => $data['description'] ?? null,
            ':status' => $data['status'],
            ':health' => $data['health'],
            ':owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : null,
            ':category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            ':budget' => $data['budget'] ?? 0.00,
            ':spent' => $data['spent'] ?? 0.00,
            ':start_date' => !empty($data['start_date']) ? $data['start_date'] : null,
            ':end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
        ]);
    }

    /**
     * Quick update health/status
     */
    public function updateQuick($id, $health, $status = null) {
        if ($status) {
            $stmt = $this->db->prepare("UPDATE projects SET health = :health, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            return $stmt->execute([':health' => $health, ':status' => $status, ':id' => $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE projects SET health = :health, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            return $stmt->execute([':health' => $health, ':id' => $id]);
        }
    }

    /**
     * Delete project
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get aggregate portfolio stats
     */
    public function getPortfolioSummary() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_projects,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_projects,
                SUM(CASE WHEN status = 'Planning' THEN 1 ELSE 0 END) as planning_projects,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_projects,
                SUM(CASE WHEN status = 'On Hold' THEN 1 ELSE 0 END) as on_hold_projects,
                SUM(CASE WHEN health = 'On Track' THEN 1 ELSE 0 END) as on_track_health,
                SUM(CASE WHEN health = 'At Risk' THEN 1 ELSE 0 END) as at_risk_health,
                SUM(CASE WHEN health = 'Off Track' THEN 1 ELSE 0 END) as off_track_health,
                SUM(budget) as total_budget,
                SUM(spent) as total_spent
            FROM projects
        ");
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate overall tasks stats
        $taskStmt = $this->db->query("
            SELECT 
                COUNT(t.id) as total_tasks,
                SUM(CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END) as completed_tasks
            FROM tasks t
            WHERE t.project_id IS NOT NULL AND t.deleted_at IS NULL
        ");
        $taskStats = $taskStmt->fetch(PDO::FETCH_ASSOC);

        $summary['total_tasks'] = (int)($taskStats['total_tasks'] ?? 0);
        $summary['completed_tasks'] = (int)($taskStats['completed_tasks'] ?? 0);
        $summary['overall_progress'] = $summary['total_tasks'] > 0 
            ? round(($summary['completed_tasks'] / $summary['total_tasks']) * 100, 1) 
            : 0;

        $summary['overall_burn'] = (float)$summary['total_budget'] > 0 
            ? round(((float)$summary['total_spent'] / (float)$summary['total_budget']) * 100, 1) 
            : 0;

        return $summary;
    }

    /**
     * Get all tasks for a specific project
     */
    public function getTasksForProject($projectId) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as assigned_name, c.name as category_name
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.project_id = :project_id AND t.deleted_at IS NULL
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent activity / changes for real-time stream
     */
    public function getRealtimeFeed($sinceTimestamp = null) {
        $projects = $this->getAll();
        $summary = $this->getPortfolioSummary();

        // Check if there are updates since timestamp
        $maxProjectUpdated = $this->db->query("SELECT MAX(updated_at) as max_up FROM projects")->fetchColumn();
        $maxTaskUpdated = $this->db->query("SELECT MAX(updated_at) as max_up FROM tasks WHERE project_id IS NOT NULL")->fetchColumn();
        $latestTime = max($maxProjectUpdated, $maxTaskUpdated);

        $hasUpdates = true;
        if ($sinceTimestamp && $latestTime) {
            $hasUpdates = strtotime($latestTime) > strtotime($sinceTimestamp);
        }

        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'has_updates' => $hasUpdates,
            'summary' => $summary,
            'projects' => $projects
        ];
    }
}

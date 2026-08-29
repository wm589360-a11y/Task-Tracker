<?php
require_once __DIR__ . '/../../config/database.php';

class Task {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    public function getDashboardStats($userId) {
        $stats = [];

        $sql = "SELECT COUNT(*) as total FROM tasks WHERE created_by = :user_id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $stats['total_tasks'] = $stmt->fetch()['total'];

        $sql = "SELECT COUNT(*) as total FROM tasks WHERE created_by = :user_id AND status = 'Completed' AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $stats['completed_tasks'] = $stmt->fetch()['total'];

        $sql = "SELECT COUNT(*) as total FROM tasks WHERE created_by = :user_id AND status = 'Pending' AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $stats['pending_tasks'] = $stmt->fetch()['total'];

        $sql = "SELECT COUNT(*) as total FROM tasks WHERE created_by = :user_id AND status = 'In Progress' AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $stats['in_progress_tasks'] = $stmt->fetch()['total'];

        $sql = "SELECT COUNT(*) as total FROM tasks WHERE created_by = :user_id AND status != 'Completed' AND due_date < CURRENT_DATE AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $stats['overdue_tasks'] = $stmt->fetch()['total'];

        $sql = "SELECT priority, COUNT(*) as count FROM tasks WHERE created_by = :user_id AND deleted_at IS NULL GROUP BY priority";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $stats['priority_breakdown'] = $stmt->fetchAll();

        $sql = "SELECT * FROM tasks WHERE created_by = :user_id AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $stats['recent_tasks'] = $stmt->fetchAll();

        if ($stats['total_tasks'] > 0) {
            $stats['completion_rate'] = round(($stats['completed_tasks'] / $stats['total_tasks']) * 100, 1);
        } else {
            $stats['completion_rate'] = 0;
        }

        return $stats;
    }

    public function createTask($data) {
        $sql = "INSERT INTO tasks (title, description, priority, start_date, due_date, due_time, category_id, project_id, created_by, assigned_to) 
                VALUES (:title, :description, :priority, :start_date, :due_date, :due_time, :category_id, :project_id, :created_by, :assigned_to)";
        
        $stmt = $this->db->prepare($sql);
        
        $startDate = !empty($data['start_date']) ? $data['start_date'] : null;
        $dueDate = !empty($data['due_date']) ? $data['due_date'] : null;
        $dueTime = !empty($data['due_time']) ? $data['due_time'] : null;
        $categoryId = !empty($data['category_id']) ? $data['category_id'] : null;
        $projectId = !empty($data['project_id']) ? $data['project_id'] : null;
        $assignedTo = !empty($data['assigned_to']) ? $data['assigned_to'] : null;

        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? '',
            ':priority' => $data['priority'] ?? 'Medium',
            ':start_date' => $startDate,
            ':due_date' => $dueDate,
            ':due_time' => $dueTime,
            ':category_id' => $categoryId,
            ':project_id' => $projectId,
            ':created_by' => $data['created_by'],
            ':assigned_to' => $assignedTo
        ]);
        
        return $this->db->lastInsertId();
    }

    public function getAllTasks($userId, $filters = []) {
        $sql = "SELECT t.*, c.name as category_name, u.name as assigned_name, p.name as project_name, p.code as project_code 
                FROM tasks t
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.created_by = :user_id AND t.deleted_at IS NULL";
        
        $params = [':user_id' => $userId];

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = :priority";
            $params[':priority'] = $filters['priority'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE :search OR t.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function deleteTask($taskId, $userId) {
        $sql = "UPDATE tasks SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND created_by = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $taskId,
            ':user_id' => $userId
        ]);
    }

    public function updateTaskStatus($taskId, $userId, $status) {
        $sql = "UPDATE tasks SET status = :status WHERE id = :id AND created_by = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $taskId,
            ':user_id' => $userId
        ]);
    }

    public function getTaskById($taskId, $userId) {
        $sql = "SELECT t.*, c.name as category_name, u.name as assigned_name, p.name as project_name
                FROM tasks t
                LEFT JOIN categories c ON t.category_id = c.id
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.id = :id AND t.created_by = :user_id AND t.deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $taskId, ':user_id' => $userId]);
        return $stmt->fetch();
    }

    public function getComments($taskId) {
        $sql = "SELECT tc.*, u.name as user_name 
                FROM task_comments tc
                JOIN users u ON tc.user_id = u.id
                WHERE tc.task_id = :task_id
                ORDER BY tc.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    public function addComment($taskId, $userId, $comment) {
        $sql = "INSERT INTO task_comments (task_id, user_id, comment) VALUES (:task_id, :user_id, :comment)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':task_id' => $taskId,
            ':user_id' => $userId,
            ':comment' => $comment
        ]);
    }

    public function getAttachments($taskId) {
        $sql = "SELECT ta.*, u.name as uploader_name, lu.name as locked_by_name 
                FROM task_attachments ta
                JOIN users u ON ta.uploaded_by = u.id
                LEFT JOIN users lu ON ta.locked_by = lu.id
                WHERE ta.task_id = :task_id
                ORDER BY ta.uploaded_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    public function addAttachment($taskId, $userId, $fileName, $filePath, $fileSize) {
        $sql = "INSERT INTO task_attachments (task_id, uploaded_by, file_name, file_path, file_size) 
                VALUES (:task_id, :user_id, :file_name, :file_path, :file_size)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':task_id'   => $taskId,
            ':user_id'   => $userId,
            ':file_name' => $fileName,
            ':file_path' => $filePath,
            ':file_size' => $fileSize
        ]);
    }

    public function getAttachmentById($attachmentId) {
        $sql = "SELECT * FROM task_attachments WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $attachmentId]);
        return $stmt->fetch();
    }

    public function lockAttachment($attachmentId, $userId) {
        $sql = "UPDATE task_attachments SET locked_by = :user_id, locked_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':user_id' => $userId, ':id' => $attachmentId]);
    }

    public function unlockAttachment($attachmentId) {
        $sql = "UPDATE task_attachments SET locked_by = NULL, locked_at = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $attachmentId]);
    }
    
    public function deleteAttachment($attachmentId) {
        $sql = "DELETE FROM task_attachments WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $attachmentId]);
    }

    // ─── Overdue Notifications ───────────────────────────────────────────
    public function getOverdueTasks($userId) {
        $sql = "SELECT id, title, due_date FROM tasks
                WHERE created_by = :user_id
                AND status != 'Completed'
                AND due_date < CURRENT_DATE
                AND deleted_at IS NULL
                ORDER BY due_date ASC LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // ─── Analytics ────────────────────────────────────────────────────────
    public function getMonthlyStats($userId) {
        $sql = "SELECT 
                    TO_CHAR(created_at, 'Mon YYYY') as month, 
                    COUNT(*) as total, 
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed 
                FROM tasks 
                WHERE created_by = :user_id AND deleted_at IS NULL 
                AND created_at >= CURRENT_TIMESTAMP - INTERVAL '6 months' 
                GROUP BY TO_CHAR(created_at, 'YYYY-MM'), TO_CHAR(created_at, 'Mon YYYY') 
                ORDER BY MIN(created_at) ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getCategoryStats($userId) {
        $sql = "SELECT
                    COALESCE(c.name, 'Uncategorized') as category,
                    COUNT(*) as count
                FROM tasks t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE t.created_by = :user_id AND t.deleted_at IS NULL
                GROUP BY t.category_id, c.name
                ORDER BY count DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // ─── Calendar View ────────────────────────────────────────────────────
    public function getTasksForMonth($userId, $month, $year) {
        $sql = "SELECT id, title, due_date, priority, status 
                FROM tasks 
                WHERE created_by = :user_id 
                AND deleted_at IS NULL 
                AND EXTRACT(MONTH FROM due_date) = :month 
                AND EXTRACT(YEAR FROM due_date) = :year";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':month' => $month, ':year' => $year]);
        return $stmt->fetchAll();
    }

    // ─── Tags System ──────────────────────────────────────────────────────
    public function getTagsForTask($taskId) {
        $sql = "SELECT t.id, t.name FROM tags t
                JOIN task_tags tt ON t.id = tt.tag_id
                WHERE tt.task_id = :task_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    public function getAllTags() {
        $sql = "SELECT * FROM tags ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addTagToTask($taskId, $tagName) {
        $tagName = strtolower(trim($tagName));
        $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = :name");
        $stmt->execute([':name' => $tagName]);
        $tag = $stmt->fetch();
        if (!$tag) {
            $stmt = $this->db->prepare("INSERT INTO tags (name) VALUES (:name)");
            $stmt->execute([':name' => $tagName]);
            $tagId = $this->db->lastInsertId();
        } else {
            $tagId = $tag['id'];
        }
        $stmt = $this->db->prepare("INSERT IGNORE INTO task_tags (task_id, tag_id) VALUES (:task_id, :tag_id)");
        return $stmt->execute([':task_id' => $taskId, ':tag_id' => $tagId]);
    }

    public function removeTagFromTask($tagId, $taskId) {
        $stmt = $this->db->prepare("DELETE FROM task_tags WHERE tag_id = :tag_id AND task_id = :task_id");
        return $stmt->execute([':tag_id' => $tagId, ':task_id' => $taskId]);
    }

    // ─── Inline Edit ──────────────────────────────────────────────────────
    public function updateTaskField($taskId, $userId, $field, $value) {
        $allowed = ['title', 'status', 'priority'];
        if (!in_array($field, $allowed)) return false;
        $sql = "UPDATE tasks SET $field = :value WHERE id = :id AND created_by = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':value' => $value, ':id' => $taskId, ':user_id' => $userId]);
    }

    // ─── Sub-tasks / Checklists ──────────────────────────────────────────
    public function getSubtasks($parentId) {
        $sql = "SELECT * FROM tasks
                WHERE parent_task_id = :parent_id AND deleted_at IS NULL
                ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':parent_id' => $parentId]);
        return $stmt->fetchAll();
    }

    public function createSubtask($parentId, $userId, $title) {
        $sql = "INSERT INTO tasks (title, created_by, parent_task_id, status) VALUES (:title, :user_id, :parent_id, 'Pending')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':title' => $title, ':user_id' => $userId, ':parent_id' => $parentId]);
        return $this->db->lastInsertId();
    }

    public function updateSubtaskStatus($subtaskId, $userId, $status) {
        $stmt = $this->db->prepare("UPDATE tasks SET status = :status WHERE id = :id AND parent_task_id IS NOT NULL");
        return $stmt->execute([':status' => $status, ':id' => $subtaskId]);
    }

    public function deleteSubtask($subtaskId, $userId) {
        $stmt = $this->db->prepare("UPDATE tasks SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND created_by = :user_id");
        return $stmt->execute([':id' => $subtaskId, ':user_id' => $userId]);
    }

    public function updateTask($taskId, $userId, $data) {
        $sql = "UPDATE tasks SET
                    title       = :title,
                    description = :description,
                    priority    = :priority,
                    status      = :status,
                    start_date  = :start_date,
                    due_date    = :due_date,
                    due_time    = :due_time,
                    category_id = :category_id,
                    project_id  = :project_id,
                    assigned_to = :assigned_to
                WHERE id = :id AND created_by = :user_id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title'       => $data['title'],
            ':description' => $data['description'],
            ':priority'    => $data['priority'],
            ':status'      => $data['status'],
            ':start_date'  => $data['start_date'],
            ':due_date'    => $data['due_date'],
            ':due_time'    => $data['due_time'],
            ':category_id' => $data['category_id'],
            ':project_id'  => !empty($data['project_id']) ? $data['project_id'] : null,
            ':assigned_to' => $data['assigned_to'],
            ':id'          => $taskId,
            ':user_id'     => $userId,
        ]);
    }

    // ─── Gantt Chart ──────────────────────────────────────────────────────
    public function getGanttData($userId) {
        $sql = "SELECT t.id, t.title as name, t.start_date, t.due_date, t.status,
                       STRING_AGG(td.depends_on_task_id::text, ',') as dependencies
                FROM tasks t
                LEFT JOIN task_dependencies td ON t.id = td.task_id
                WHERE t.created_by = :user_id AND t.deleted_at IS NULL
                GROUP BY t.id
                ORDER BY t.start_date ASC, t.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format for Frappe Gantt
        $ganttTasks = [];
        foreach ($tasks as $task) {
            $progress = match($task['status']) {
                'Completed' => 100,
                'In Progress' => 50,
                default => 0
            };
            
            // Frappe Gantt needs YYYY-MM-DD
            $start = $task['start_date'] ?: date('Y-m-d');
            $end = $task['due_date'] ?: date('Y-m-d', strtotime('+1 day', strtotime($start)));
            
            $ganttTasks[] = [
                'id' => (string)$task['id'],
                'name' => $task['name'],
                'start' => $start,
                'end' => $end,
                'progress' => $progress,
                'dependencies' => $task['dependencies'] ?: '',
                'custom_class' => 'gantt-status-' . strtolower(str_replace(' ', '-', $task['status']))
            ];
        }
        return $ganttTasks;
    }

    public function updateTaskDates($taskId, $userId, $startDate, $dueDate) {
        $sql = "UPDATE tasks SET start_date = :start_date, due_date = :due_date 
                WHERE id = :id AND created_by = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':start_date' => $startDate,
            ':due_date' => $dueDate,
            ':id' => $taskId,
            ':user_id' => $userId
        ]);
    }
    
    public function saveDependencies($taskId, $dependencyIds) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM task_dependencies WHERE task_id = :task_id");
            $stmt->execute([':task_id' => $taskId]);
            
            if (!empty($dependencyIds)) {
                $stmt = $this->db->prepare("INSERT IGNORE INTO task_dependencies (task_id, depends_on_task_id) VALUES (:task_id, :depends_on)");
                foreach ($dependencyIds as $depId) {
                    $stmt->execute([
                        ':task_id' => $taskId,
                        ':depends_on' => $depId
                    ]);
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>

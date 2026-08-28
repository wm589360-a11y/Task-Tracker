<?php
require_once __DIR__ . '/../../config/database.php';

class TimeEntry {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    public function getActivePunch($userId) {
        $sql = "SELECT t.*, tk.title as task_title 
                FROM time_entries t 
                LEFT JOIN tasks tk ON t.task_id = tk.id
                WHERE t.user_id = :user_id AND t.clock_out IS NULL 
                ORDER BY t.clock_in DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch();
    }

    public function punchIn($userId, $taskId = null, $notes = '') {
        // Ensure no active punch
        $active = $this->getActivePunch($userId);
        if ($active) {
            return false;
        }

        $sql = "INSERT INTO time_entries (user_id, task_id, clock_in, notes) 
                VALUES (:user_id, :task_id, CURRENT_TIMESTAMP, :notes)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':task_id' => $taskId,
            ':notes' => $notes
        ]);
    }

    public function punchOut($userId, $entryId) {
        // Calculate duration and update
        $sql = "UPDATE time_entries 
                SET clock_out = CURRENT_TIMESTAMP, 
                    duration_minutes = EXTRACT(EPOCH FROM (CURRENT_TIMESTAMP - clock_in))/60
                WHERE id = :id AND user_id = :user_id AND clock_out IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $entryId,
            ':user_id' => $userId
        ]);
    }

    public function getUserTimeEntries($userId, $limit = 50) {
        $sql = "SELECT t.*, tk.title as task_title 
                FROM time_entries t 
                LEFT JOIN tasks tk ON t.task_id = tk.id
                WHERE t.user_id = :user_id 
                ORDER BY t.clock_in DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPendingTimesheets() {
        $sql = "SELECT t.*, tk.title as task_title, u.name as user_name
                FROM time_entries t 
                LEFT JOIN tasks tk ON t.task_id = tk.id
                JOIN users u ON t.user_id = u.id
                WHERE t.status = 'pending' AND t.clock_out IS NOT NULL
                ORDER BY t.clock_in DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus($entryId, $status) {
        $sql = "UPDATE time_entries SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $entryId
        ]);
    }

    public function getReportData($filters = []) {
        $sql = "SELECT t.*, tk.title as task_title, u.name as user_name
                FROM time_entries t 
                LEFT JOIN tasks tk ON t.task_id = tk.id
                JOIN users u ON t.user_id = u.id
                WHERE t.clock_out IS NOT NULL";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND t.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(t.clock_in) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(t.clock_in) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        $sql .= " ORDER BY t.clock_in DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getSummaryStats($filters = []) {
        $sql = "SELECT 
                SUM(duration_minutes) as total_minutes,
                SUM(CASE WHEN status = 'approved' THEN duration_minutes ELSE 0 END) as approved_minutes,
                SUM(CASE WHEN status = 'pending' THEN duration_minutes ELSE 0 END) as pending_minutes,
                SUM(CASE WHEN status = 'rejected' THEN duration_minutes ELSE 0 END) as rejected_minutes
                FROM time_entries t
                WHERE t.clock_out IS NOT NULL";
                
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND t.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(t.clock_in) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(t.clock_in) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
?>

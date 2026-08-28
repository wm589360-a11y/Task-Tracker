<?php
require_once __DIR__ . '/../../config/database.php';

class Expense {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    public function create($userId, $amount, $description, $category, $date, $receiptPath = null) {
        $sql = "INSERT INTO expenses (user_id, amount, description, category, expense_date, receipt_path) 
                VALUES (:user_id, :amount, :description, :category, :expense_date, :receipt_path)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':amount' => $amount,
            ':description' => $description,
            ':category' => $category,
            ':expense_date' => $date,
            ':receipt_path' => $receiptPath
        ]);
    }

    public function getByUserId($userId) {
        $sql = "SELECT * FROM expenses WHERE user_id = :user_id ORDER BY expense_date DESC, created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getById($id, $userId = null) {
        $sql = "SELECT * FROM expenses WHERE id = :id";
        $params = [':id' => $id];
        
        if ($userId) {
            $sql .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function delete($id, $userId = null) {
        $sql = "DELETE FROM expenses WHERE id = :id";
        $params = [':id' => $id];
        
        if ($userId) {
            $sql .= " AND user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function getAllPending() {
        $sql = "SELECT e.*, u.name as user_name, u.email as user_email 
                FROM expenses e 
                JOIN users u ON e.user_id = u.id 
                WHERE e.status = 'pending' 
                ORDER BY e.expense_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE expenses SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function getReportData($startDate = null, $endDate = null) {
        $sql = "SELECT e.category, SUM(e.amount) as total_amount, COUNT(e.id) as expense_count 
                FROM expenses e 
                WHERE e.status = 'approved'";
        $params = [];
        
        if ($startDate && $endDate) {
            $sql .= " AND e.expense_date BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }
        
        $sql .= " GROUP BY e.category ORDER BY total_amount DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getMonthlyTotals($year) {
        $sql = "SELECT EXTRACT(MONTH FROM expense_date) as month, SUM(amount) as total 
                FROM expenses 
                WHERE status = 'approved' AND EXTRACT(YEAR FROM expense_date) = :year 
                GROUP BY EXTRACT(MONTH FROM expense_date) 
                ORDER BY month ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll();
    }
}
?>

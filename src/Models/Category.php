<?php
require_once __DIR__ . '/../../config/database.php';

class Category {
    /** @var PDO $db */
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    /**
     * @param int|null $userId
     * @return array
     */
    public function getAll($userId) {
        $sql = "SELECT * FROM categories WHERE user_id IS NULL OR user_id = :user_id ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * @param string $name
     * @param int|null $userId
     * @return bool
     */
    public function create($name, $userId) {
        $sql = "INSERT INTO categories (name, user_id) VALUES (:name, :user_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':user_id' => $userId
        ]);
    }

    /**
     * @param int $id
     * @param int|null $userId
     * @return bool
     */
    public function delete($id, $userId) {
        $sql = "DELETE FROM categories WHERE id = :id AND (user_id = :user_id OR user_id IS NULL)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }
}
?>
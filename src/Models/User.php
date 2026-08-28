<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    /** @var PDO $db */
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    /**
     * Create a user.
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @return int|false
     */
    public function create($name, $email, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
            return $this->db->lastInsertId();
        } catch(PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $sql = "SELECT id, name, email FROM users WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * @param int|null $excludeId
     * @return array
     */
    public function getAllUsers($excludeId = null) {
        $sql = "SELECT id, name, email FROM users WHERE deleted_at IS NULL";
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }
        $sql .= " ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        if ($excludeId) {
            $stmt->execute([':exclude_id' => $excludeId]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    /**
     * @param int $userId
     * @param string $name
     * @param string $email
     * @return bool
     */
    public function updateProfile($userId, $name, $email) {
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':name' => $name, ':email' => $email, ':id' => $userId]);
    }

    /**
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function changePassword($userId, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':password' => $hash, ':id' => $userId]);
    }
}
?>

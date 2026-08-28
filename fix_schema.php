<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = "ALTER TABLE tasks ADD COLUMN IF NOT EXISTS project_id BIGINT NULL REFERENCES projects(id) ON DELETE SET NULL";
    $db->exec($sql);
    echo "Successfully added project_id to tasks table.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

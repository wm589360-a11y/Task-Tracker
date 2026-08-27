<?php
require_once __DIR__ . '/../../config/database.php';

class CustomField {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    // ─── Field Definitions ───────────────────────────────────────────────

    public function getFieldsForUser($userId) {
        $sql = "SELECT * FROM custom_field_definitions WHERE user_id = :user_id ORDER BY sort_order ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFieldById($fieldId, $userId) {
        $sql = "SELECT * FROM custom_field_definitions WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $fieldId, ':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createField($userId, $data) {
        $fieldKey = $this->slugify($data['label']);
        // Ensure uniqueness
        $fieldKey = $this->ensureUniqueKey($userId, $fieldKey);

        $sql = "INSERT INTO custom_field_definitions 
                    (user_id, label, field_key, field_type, options, is_required, placeholder, min_value, max_value, sort_order)
                VALUES 
                    (:user_id, :label, :field_key, :field_type, :options, :is_required, :placeholder, :min_value, :max_value, :sort_order)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id'    => $userId,
            ':label'      => trim($data['label']),
            ':field_key'  => $fieldKey,
            ':field_type' => $data['field_type'] ?? 'text',
            ':options'    => !empty($data['options']) ? $data['options'] : null,
            ':is_required'=> !empty($data['is_required']) ? 1 : 0,
            ':placeholder'=> $data['placeholder'] ?? null,
            ':min_value'  => isset($data['min_value']) && $data['min_value'] !== '' ? $data['min_value'] : null,
            ':max_value'  => isset($data['max_value']) && $data['max_value'] !== '' ? $data['max_value'] : null,
            ':sort_order' => $data['sort_order'] ?? 0,
        ]);
        return $this->db->lastInsertId();
    }

    public function updateField($fieldId, $userId, $data) {
        $sql = "UPDATE custom_field_definitions SET
                    label       = :label,
                    field_type  = :field_type,
                    options     = :options,
                    is_required = :is_required,
                    placeholder = :placeholder,
                    min_value   = :min_value,
                    max_value   = :max_value,
                    sort_order  = :sort_order
                WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':label'      => trim($data['label']),
            ':field_type' => $data['field_type'] ?? 'text',
            ':options'    => !empty($data['options']) ? $data['options'] : null,
            ':is_required'=> !empty($data['is_required']) ? 1 : 0,
            ':placeholder'=> $data['placeholder'] ?? null,
            ':min_value'  => isset($data['min_value']) && $data['min_value'] !== '' ? $data['min_value'] : null,
            ':max_value'  => isset($data['max_value']) && $data['max_value'] !== '' ? $data['max_value'] : null,
            ':sort_order' => $data['sort_order'] ?? 0,
            ':id'         => $fieldId,
            ':user_id'    => $userId,
        ]);
    }

    public function deleteField($fieldId, $userId) {
        $sql = "DELETE FROM custom_field_definitions WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $fieldId, ':user_id' => $userId]);
    }

    // ─── Field Values ────────────────────────────────────────────────────

    public function getValuesForTask($taskId, $userId) {
        $sql = "SELECT cfv.field_id, cfv.value, cfd.label, cfd.field_type, cfd.field_key,
                       cfd.is_required, cfd.options, cfd.placeholder, cfd.min_value, cfd.max_value
                FROM custom_field_values cfv
                JOIN custom_field_definitions cfd ON cfv.field_id = cfd.id
                WHERE cfv.task_id = :task_id AND cfd.user_id = :user_id
                ORDER BY cfd.sort_order ASC, cfd.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':task_id' => $taskId, ':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Key by field_id for easy lookup
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['field_id']] = $row;
        }
        return $indexed;
    }

    public function saveValues($taskId, $fieldDefinitions, $postedValues) {
        $stmt = $this->db->prepare(
            "INSERT INTO custom_field_values (task_id, field_id, value)
             VALUES (:task_id, :field_id, :value)
             ON DUPLICATE KEY UPDATE value = :value2, updated_at = NOW()"
        );
        foreach ($fieldDefinitions as $field) {
            $fieldId  = $field['id'];
            $inputKey = 'custom_field_' . $fieldId;
            $value    = $postedValues[$inputKey] ?? null;

            // Checkbox: convert to 1/0
            if ($field['field_type'] === 'checkbox') {
                $value = !empty($value) ? '1' : '0';
            }

            $stmt->execute([
                ':task_id'  => $taskId,
                ':field_id' => $fieldId,
                ':value'    => $value !== '' ? $value : null,
                ':value2'   => $value !== '' ? $value : null,
            ]);
        }
    }

    // ─── Server-side Validation ──────────────────────────────────────────

    public function validateValues($fieldDefinitions, $postedValues) {
        $errors = [];
        foreach ($fieldDefinitions as $field) {
            $inputKey = 'custom_field_' . $field['id'];
            $value    = $postedValues[$inputKey] ?? null;
            $label    = htmlspecialchars($field['label']);

            // Required check
            if ($field['is_required'] && ($value === null || $value === '')) {
                $errors[] = "\"$label\" is required.";
                continue;
            }

            if ($value === null || $value === '') continue;

            switch ($field['field_type']) {
                case 'number':
                    if (!is_numeric($value)) {
                        $errors[] = "\"$label\" must be a number.";
                    } elseif ($field['min_value'] !== null && (float)$value < (float)$field['min_value']) {
                        $errors[] = "\"$label\" must be at least {$field['min_value']}.";
                    } elseif ($field['max_value'] !== null && (float)$value > (float)$field['max_value']) {
                        $errors[] = "\"$label\" must be at most {$field['max_value']}.";
                    }
                    break;
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "\"$label\" must be a valid email address.";
                    }
                    break;
                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $errors[] = "\"$label\" must be a valid URL.";
                    }
                    break;
                case 'date':
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        $errors[] = "\"$label\" must be a valid date.";
                    }
                    break;
                case 'select':
                    $opts = json_decode($field['options'] ?? '[]', true);
                    if (!in_array($value, $opts, true)) {
                        $errors[] = "\"$label\" has an invalid selection.";
                    }
                    break;
                case 'text':
                    if (mb_strlen($value) > 500) {
                        $errors[] = "\"$label\" must be 500 characters or fewer.";
                    }
                    break;
            }
        }
        return $errors;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function slugify($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        return trim($text, '_');
    }

    private function ensureUniqueKey($userId, $key, $suffix = 0) {
        $attempt = $suffix ? $key . '_' . $suffix : $key;
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM custom_field_definitions WHERE user_id = :uid AND field_key = :key"
        );
        $stmt->execute([':uid' => $userId, ':key' => $attempt]);
        if ((int)$stmt->fetchColumn() > 0) {
            return $this->ensureUniqueKey($userId, $key, $suffix + 1);
        }
        return $attempt;
    }
}
?>

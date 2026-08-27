<?php
require_once dirname(__DIR__) . '/Models/CustomField.php';
require_once dirname(__DIR__) . '/helpers/SessionHelper.php';

class CustomFieldController {
    private $model;

    public function __construct() {
        $this->model = new CustomField();
        SessionHelper::start();
        SessionHelper::requireLogin();
    }

    private function userId() {
        return SessionHelper::get('user_id');
    }

    // ─── List all field definitions ──────────────────────────────────────
    public function index() {
        $fields = $this->model->getFieldsForUser($this->userId());
        require_once dirname(__DIR__) . '/../templates/custom_fields/index.php';
    }

    // ─── Create ──────────────────────────────────────────────────────────
    public function create() {
        require_once dirname(__DIR__) . '/../templates/custom_fields/create.php';
    }

    public function store() {
        $label = trim($_POST['label'] ?? '');
        if (empty($label)) {
            SessionHelper::setFlash('error', 'Field label is required.');
            header('Location: /Task-Tracker/public/custom-fields/create');
            exit();
        }

        $this->model->createField($this->userId(), [
            'label'      => $label,
            'field_type' => $_POST['field_type'] ?? 'text',
            'options'    => $this->buildOptionsJson($_POST['field_type'] ?? '', $_POST['options_raw'] ?? ''),
            'is_required'=> $_POST['is_required'] ?? 0,
            'placeholder'=> $_POST['placeholder'] ?? null,
            'min_value'  => $_POST['min_value'] ?? null,
            'max_value'  => $_POST['max_value'] ?? null,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ]);

        SessionHelper::setFlash('success', 'Custom field created successfully.');
        header('Location: /Task-Tracker/public/custom-fields');
        exit();
    }

    // ─── Edit ────────────────────────────────────────────────────────────
    public function edit($id) {
        $field = $this->model->getFieldById($id, $this->userId());
        if (!$field) {
            SessionHelper::setFlash('error', 'Field not found.');
            header('Location: /Task-Tracker/public/custom-fields');
            exit();
        }
        // Decode options back to a comma-separated string for the form
        $optionsRaw = '';
        if ($field['field_type'] === 'select' && !empty($field['options'])) {
            $opts = json_decode($field['options'], true);
            $optionsRaw = is_array($opts) ? implode(', ', $opts) : '';
        }
        require_once dirname(__DIR__) . '/../templates/custom_fields/edit.php';
    }

    public function update($id) {
        $label = trim($_POST['label'] ?? '');
        if (empty($label)) {
            SessionHelper::setFlash('error', 'Field label is required.');
            header("Location: /Task-Tracker/public/custom-fields/edit/$id");
            exit();
        }

        $this->model->updateField($id, $this->userId(), [
            'label'      => $label,
            'field_type' => $_POST['field_type'] ?? 'text',
            'options'    => $this->buildOptionsJson($_POST['field_type'] ?? '', $_POST['options_raw'] ?? ''),
            'is_required'=> $_POST['is_required'] ?? 0,
            'placeholder'=> $_POST['placeholder'] ?? null,
            'min_value'  => $_POST['min_value'] ?? null,
            'max_value'  => $_POST['max_value'] ?? null,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ]);

        SessionHelper::setFlash('success', 'Custom field updated successfully.');
        header('Location: /Task-Tracker/public/custom-fields');
        exit();
    }

    // ─── Delete ──────────────────────────────────────────────────────────
    public function delete($id) {
        $this->model->deleteField($id, $this->userId());
        SessionHelper::setFlash('success', 'Custom field deleted.');
        header('Location: /Task-Tracker/public/custom-fields');
        exit();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────
    private function buildOptionsJson($type, $raw) {
        if ($type !== 'select' || trim($raw) === '') return null;
        $opts = array_map('trim', explode(',', $raw));
        $opts = array_filter($opts, fn($o) => $o !== '');
        return json_encode(array_values($opts));
    }
}
?>

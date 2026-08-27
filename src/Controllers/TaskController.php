<?php
require_once dirname(__DIR__) . '/Models/Task.php';
require_once dirname(__DIR__) . '/Models/Category.php';
require_once dirname(__DIR__) . '/Models/User.php';
require_once dirname(__DIR__) . '/Models/CustomField.php';
require_once dirname(__DIR__) . '/helpers/SessionHelper.php';

class TaskController {
    /** @var Task $taskModel */
    private $taskModel;
    /** @var Category $categoryModel */
    private $categoryModel;
    /** @var User $userModel */
    private $userModel;
    /** @var CustomField $customFieldModel */
    private $customFieldModel;

    public function __construct() {
        $this->taskModel = new Task();
        $this->categoryModel = new Category();
        $this->userModel = new User();
        $this->customFieldModel = new CustomField();
        SessionHelper::start();
        SessionHelper::requireLogin();
    }

    public function dashboard() {
        $stats = $this->taskModel->getDashboardStats(SessionHelper::get('user_id'));
        $recentTasks = $stats['recent_tasks'] ?? [];
        require_once dirname(__DIR__) . '/../templates/dashboard.php';
    }

    public function index() {
        $filters = [
            'status'   => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'search'   => $_GET['search'] ?? '',
        ];
        $tasks = $this->taskModel->getAllTasks(SessionHelper::get('user_id'), $filters);
        $categories = $this->categoryModel->getAll(SessionHelper::get('user_id'));
        require_once dirname(__DIR__) . '/../templates/tasks/index.php';
    }

    public function export() {
        $tasks = $this->taskModel->getAllTasks(SessionHelper::get('user_id'));
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tasks_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Title', 'Description', 'Priority', 'Status', 'Due Date', 'Category', 'Assigned To', 'Created At']);
        foreach ($tasks as $task) {
            fputcsv($out, [
                $task['id'],
                $task['title'],
                $task['description'],
                $task['priority'],
                $task['status'],
                $task['due_date'] ?? '',
                $task['category_name'] ?? '',
                $task['assigned_name'] ?? '',
                $task['created_at'],
            ]);
        }
        fclose($out);
        exit();
    }

    /**
     * @param int $id
     */
    public function show($id) {
        $userId      = SessionHelper::get('user_id');
        $task        = $this->taskModel->getTaskById($id, $userId);
        if (!$task) {
            SessionHelper::setFlash('error', 'Task not found.');
            header('Location: /Task-Tracker/public/tasks');
            exit();
        }
        $comments          = $this->taskModel->getComments($id);
        $attachments       = $this->taskModel->getAttachments($id);
        $tags              = $this->taskModel->getTagsForTask($id);
        $allTags           = $this->taskModel->getAllTags();
        $subtasks          = $this->taskModel->getSubtasks($id);
        $customFields      = $this->customFieldModel->getFieldsForUser($userId);
        $customFieldValues = $this->customFieldModel->getValuesForTask($id, $userId);
        require_once dirname(__DIR__) . '/../templates/tasks/show.php';
    }

    /**
     * @param int $taskId
     */
    public function addComment($taskId) {
        $comment = trim($_POST['comment'] ?? '');
        if (empty($comment)) {
            SessionHelper::setFlash('error', 'Comment cannot be empty.');
        } else {
            $this->taskModel->addComment($taskId, SessionHelper::get('user_id'), $comment);
            SessionHelper::setFlash('success', 'Comment added.');
        }
        header("Location: /Task-Tracker/public/tasks/view/$taskId");
        exit();
    }

    /**
     * @param int $taskId
     */
    public function addAttachment($taskId) {
        $attachments = $this->taskModel->getAttachments($taskId);
        $userId = SessionHelper::get('user_id');
        
        foreach ($attachments as $att) {
            if (!empty($att['locked_by']) && $att['locked_by'] != $userId) {
                SessionHelper::setFlash('error', 'Cannot upload: A document is currently locked by ' . htmlspecialchars($att['locked_by_name']));
                header("Location: /Task-Tracker/public/tasks/view/$taskId");
                exit();
            }
        }

        if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            SessionHelper::setFlash('error', 'File upload failed.');
            header("Location: /Task-Tracker/public/tasks/view/$taskId");
            exit();
        }
        $file     = $_FILES['attachment'];
        $allowed  = ['jpg','jpeg','png','gif','pdf','doc','docx','xlsx','txt','zip'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed) || $file['size'] > 5 * 1024 * 1024) {
            SessionHelper::setFlash('error', 'Invalid file type or file too large (max 5MB).');
            header("Location: /Task-Tracker/public/tasks/view/$taskId");
            exit();
        }
        $uploadDir = dirname(__DIR__) . '/../public/assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = uniqid('attach_') . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);
        $this->taskModel->addAttachment($taskId, SessionHelper::get('user_id'), $file['name'], 'assets/uploads/' . $fileName, $file['size']);
        SessionHelper::setFlash('success', 'File uploaded successfully.');
        header("Location: /Task-Tracker/public/tasks/view/$taskId");
        exit();
    }
    /**
     * @param int $attachmentId
     */
    public function lockAttachment($attachmentId) {
        $attachment = $this->taskModel->getAttachmentById($attachmentId);
        if (!$attachment) {
            SessionHelper::setFlash('error', 'Attachment not found.');
            $referer = $_SERVER['HTTP_REFERER'] ?? '/Task-Tracker/public/tasks';
            header("Location: $referer");
            exit();
        }
        
        if (!empty($attachment['locked_by'])) {
            SessionHelper::setFlash('error', 'Attachment is already locked.');
        } else {
            $this->taskModel->lockAttachment($attachmentId, SessionHelper::get('user_id'));
            SessionHelper::setFlash('success', 'Document locked successfully.');
        }
        header("Location: /Task-Tracker/public/tasks/view/" . $attachment['task_id']);
        exit();
    }

    /**
     * @param int $attachmentId
     */
    public function unlockAttachment($attachmentId) {
        $attachment = $this->taskModel->getAttachmentById($attachmentId);
        if (!$attachment) {
            SessionHelper::setFlash('error', 'Attachment not found.');
            $referer = $_SERVER['HTTP_REFERER'] ?? '/Task-Tracker/public/tasks';
            header("Location: $referer");
            exit();
        }
        
        $userId = SessionHelper::get('user_id');
        $task = $this->taskModel->getTaskById($attachment['task_id'], $userId);
        
        if ($attachment['locked_by'] != $userId && !$task) {
            SessionHelper::setFlash('error', 'You do not have permission to unlock this document.');
        } else {
            $this->taskModel->unlockAttachment($attachmentId);
            SessionHelper::setFlash('success', 'Document unlocked successfully.');
        }
        header("Location: /Task-Tracker/public/tasks/view/" . $attachment['task_id']);
        exit();
    }

    /**
     * @param int $attachmentId
     */
    public function deleteAttachment($attachmentId) {
        $attachment = $this->taskModel->getAttachmentById($attachmentId);
        if (!$attachment) {
            SessionHelper::setFlash('error', 'Attachment not found.');
            $referer = $_SERVER['HTTP_REFERER'] ?? '/Task-Tracker/public/tasks';
            header("Location: $referer");
            exit();
        }

        $userId = SessionHelper::get('user_id');
        if (!empty($attachment['locked_by']) && $attachment['locked_by'] != $userId) {
            SessionHelper::setFlash('error', 'Cannot delete: Document is locked by another user.');
        } else {
            $filePath = dirname(__DIR__) . '/../public/' . $attachment['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->taskModel->deleteAttachment($attachmentId);
            SessionHelper::setFlash('success', 'Attachment deleted successfully.');
        }
        header("Location: /Task-Tracker/public/tasks/view/" . $attachment['task_id']);
        exit();
    }

    public function create() {
        $userId = SessionHelper::get('user_id');
        $categories = $this->categoryModel->getAll($userId);
        $users = $this->userModel->getAllUsers();
        $customFields = $this->customFieldModel->getFieldsForUser($userId);
        require_once dirname(__DIR__) . '/../templates/tasks/create.php';
    }

    public function store() {
        $title = trim($_POST['title'] ?? '');
        $userId = SessionHelper::get('user_id');

        if (empty($title)) {
            SessionHelper::setFlash('error', 'Task title is required');
            header('Location: /Task-Tracker/public/tasks/create');
            exit();
        }

        // Validate custom fields
        $customFields = $this->customFieldModel->getFieldsForUser($userId);
        $cfErrors = $this->customFieldModel->validateValues($customFields, $_POST);
        if (!empty($cfErrors)) {
            SessionHelper::setFlash('error', implode('<br>', $cfErrors));
            header('Location: /Task-Tracker/public/tasks/create');
            exit();
        }

        $data = [
            'title'       => $title,
            'description' => $_POST['description'] ?? '',
            'priority'    => $_POST['priority'] ?? 'Medium',
            'start_date'  => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'due_date'    => $_POST['due_date'] ?? null,
            'due_time'    => $_POST['due_time'] ?? null,
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'created_by'  => $userId,
            'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null
        ];

        $taskId = $this->taskModel->createTask($data);

        if ($taskId) {
            // Save custom field values
            $this->customFieldModel->saveValues($taskId, $customFields, $_POST);
            SessionHelper::setFlash('success', 'Task created successfully');
            header('Location: /Task-Tracker/public/dashboard');
        } else {
            SessionHelper::setFlash('error', 'Failed to create task');
            header('Location: /Task-Tracker/public/tasks/create');
        }
        exit();
    }


    /**
     * @param int $id
     */
    public function edit($id) {
        $userId = SessionHelper::get('user_id');
        $task = $this->taskModel->getTaskById($id, $userId);
        if (!$task) {
            SessionHelper::setFlash('error', 'Task not found.');
            header('Location: /Task-Tracker/public/tasks');
            exit();
        }
        $categories        = $this->categoryModel->getAll($userId);
        $users             = $this->userModel->getAllUsers();
        $customFields      = $this->customFieldModel->getFieldsForUser($userId);
        $customFieldValues = $this->customFieldModel->getValuesForTask($id, $userId);
        require_once dirname(__DIR__) . '/../templates/tasks/edit.php';
    }

    /**
     * @param int $id
     */
    public function update($id) {
        $userId = SessionHelper::get('user_id');
        $title  = trim($_POST['title'] ?? '');
        if (empty($title)) {
            SessionHelper::setFlash('error', 'Task title is required.');
            header("Location: /Task-Tracker/public/tasks/edit/$id");
            exit();
        }

        // Validate custom fields
        $customFields = $this->customFieldModel->getFieldsForUser($userId);
        $cfErrors = $this->customFieldModel->validateValues($customFields, $_POST);
        if (!empty($cfErrors)) {
            SessionHelper::setFlash('error', implode('<br>', $cfErrors));
            header("Location: /Task-Tracker/public/tasks/edit/$id");
            exit();
        }

        $data = [
            'title'       => $title,
            'description' => $_POST['description'] ?? '',
            'priority'    => $_POST['priority']    ?? 'Medium',
            'status'      => $_POST['status']      ?? 'Pending',
            'start_date'  => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'due_date'    => !empty($_POST['due_date'])    ? $_POST['due_date']    : null,
            'due_time'    => !empty($_POST['due_time'])    ? $_POST['due_time']    : null,
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null,
        ];

        $updated = $this->taskModel->updateTask($id, $userId, $data);

        if ($updated) {
            // Save custom field values
            $this->customFieldModel->saveValues($id, $customFields, $_POST);
            SessionHelper::setFlash('success', 'Task updated successfully!');
            header("Location: /Task-Tracker/public/tasks/view/$id");
        } else {
            SessionHelper::setFlash('error', 'Failed to update task.');
            header("Location: /Task-Tracker/public/tasks/edit/$id");
        }
        exit();
    }

    /**
     * @param int $id
     */
    public function delete($id) {
        $deleted = $this->taskModel->deleteTask($id, SessionHelper::get('user_id'));
        if ($deleted) {
            SessionHelper::setFlash('success', 'Task deleted successfully.');
        } else {
            SessionHelper::setFlash('error', 'Failed to delete task or task not found.');
        }
        header('Location: /Task-Tracker/public/tasks');
        exit();
    }

    /**
     * @param int $id
     */
    public function updateStatus($id) {
        $status = $_GET['s'] ?? 'Pending';
        $validStatuses = ['Pending', 'In Progress', 'Completed'];
        
        if (in_array($status, $validStatuses)) {
            $this->taskModel->updateTaskStatus($id, SessionHelper::get('user_id'), $status);
            SessionHelper::setFlash('success', "Task status updated to $status.");
        } else {
            SessionHelper::setFlash('error', 'Invalid status provided.');
        }
        
        // Redirect back to referring page or tasks list
        $referer = $_SERVER['HTTP_REFERER'] ?? '/Task-Tracker/public/tasks';
        header("Location: $referer");
        exit();
    }

    // ─── Analytics ────────────────────────────────────────────────────
    public function analytics() {
        $userId      = SessionHelper::get('user_id');
        $stats       = $this->taskModel->getDashboardStats($userId);
        $monthlyData = $this->taskModel->getMonthlyStats($userId);
        $categoryData = $this->taskModel->getCategoryStats($userId);
        require_once dirname(__DIR__) . '/../templates/analytics.php';
    }

    // ─── Calendar ───────────────────────────────────────────────────
    public function calendar() {
        $userId = SessionHelper::get('user_id');
        $month  = (int)($_GET['month'] ?? date('n'));
        $year   = (int)($_GET['year']  ?? date('Y'));
        $tasks  = $this->taskModel->getTasksForMonth($userId, $month, $year);
        require_once dirname(__DIR__) . '/../templates/calendar.php';
    }

    // ─── Tags ─────────────────────────────────────────────────────────
    /**
     * @param int $taskId
     */
    public function addTag($taskId) {
        $tagName = trim($_POST['tag_name'] ?? '');
        if (!empty($tagName)) {
            $this->taskModel->addTagToTask($taskId, $tagName);
            SessionHelper::setFlash('success', 'Tag added.');
        }
        header("Location: /Task-Tracker/public/tasks/view/$taskId"); exit();
    }

    /**
     * @param int $taskId
     * @param int $tagId
     */
    public function removeTag($taskId, $tagId) {
        $this->taskModel->removeTagFromTask($tagId, $taskId);
        SessionHelper::setFlash('success', 'Tag removed.');
        header("Location: /Task-Tracker/public/tasks/view/$taskId"); exit();
    }

    // ─── Inline Edit API ─────────────────────────────────────────────
    /**
     * @param int $id
     */
    public function apiUpdateStatus($id) {
        header('Content-Type: application/json');
        $status = $_POST['status'] ?? '';
        $valid  = ['Pending', 'In Progress', 'Completed'];
        if (in_array($status, $valid)) {
            $this->taskModel->updateTaskField($id, SessionHelper::get('user_id'), 'status', $status);
            echo json_encode(['success' => true, 'status' => $status]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid status']);
        }
        exit();
    }

    /**
     * @param int $id
     */
    public function apiUpdateTitle($id) {
        header('Content-Type: application/json');
        $title = trim($_POST['title'] ?? '');
        if (!empty($title)) {
            $this->taskModel->updateTaskField($id, SessionHelper::get('user_id'), 'title', $title);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Title cannot be empty']);
        }
        exit();
    }

    // ─── Sub-tasks ───────────────────────────────────────────────────
    /**
     * @param int $parentId
     */
    public function addSubtask($parentId) {
        $title = trim($_POST['subtask_title'] ?? '');
        if (!empty($title)) {
            $this->taskModel->createSubtask($parentId, SessionHelper::get('user_id'), $title);
            SessionHelper::setFlash('success', 'Sub-task added.');
        }
        header("Location: /Task-Tracker/public/tasks/view/$parentId"); exit();
    }

    /**
     * @param int $subtaskId
     */
    public function toggleSubtask($subtaskId) {
        $currentStatus = $_GET['current'] ?? 'Pending';
        $newStatus = ($currentStatus === 'Completed') ? 'Pending' : 'Completed';
        $this->taskModel->updateSubtaskStatus($subtaskId, SessionHelper::get('user_id'), $newStatus);
        $parentId = $_GET['parent'] ?? 0;
        header("Location: /Task-Tracker/public/tasks/view/$parentId"); exit();
    }

    /**
     * @param int $subtaskId
     */
    public function deleteSubtask($subtaskId) {
        $parentId = $_GET['parent'] ?? 0;
        $this->taskModel->deleteSubtask($subtaskId, SessionHelper::get('user_id'));
        SessionHelper::setFlash('success', 'Sub-task deleted.');
        header("Location: /Task-Tracker/public/tasks/view/$parentId"); exit();
    }

    // ─── Gantt Chart ─────────────────────────────────────────────────
    public function gantt() {
        $userId = SessionHelper::get('user_id');
        $ganttTasks = $this->taskModel->getGanttData($userId);
        require_once dirname(__DIR__) . '/../templates/tasks/gantt.php';
    }

    public function apiGanttUpdate() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid method']);
            exit();
        }

        $taskId = $_POST['task_id'] ?? null;
        $start = $_POST['start'] ?? null;
        $end = $_POST['end'] ?? null;
        $progress = $_POST['progress'] ?? null;

        if (!$taskId) {
            echo json_encode(['success' => false, 'error' => 'Missing task ID']);
            exit();
        }

        $userId = SessionHelper::get('user_id');
        
        if ($start && $end) {
            $this->taskModel->updateTaskDates($taskId, $userId, $start, $end);
        }

        if ($progress !== null) {
            $status = 'In Progress';
            if ($progress == 100) $status = 'Completed';
            if ($progress == 0) $status = 'Pending';
            $this->taskModel->updateTaskField($taskId, $userId, 'status', $status);
        }

        echo json_encode(['success' => true]);
        exit();
    }
}
?>
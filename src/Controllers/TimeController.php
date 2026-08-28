<?php
require_once __DIR__ . '/../Models/TimeEntry.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Task.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';

class TimeController {
    /** @var TimeEntry $timeModel */
    private $timeModel;

    public function __construct() {
        SessionHelper::requireLogin();
        $this->timeModel = new TimeEntry();
    }

    public function punch() {
        $userId = $_SESSION['user_id'];
        $action = $_POST['action'] ?? '';

        if ($action === 'in') {
            $taskId = !empty($_POST['task_id']) ? $_POST['task_id'] : null;
            $notes = $_POST['notes'] ?? '';
            $success = $this->timeModel->punchIn($userId, $taskId, $notes);
            if ($success) {
                $_SESSION['flash_success'] = "Punched in successfully.";
            } else {
                $_SESSION['flash_error'] = "You are already punched in.";
            }
        } elseif ($action === 'out') {
            $entryId = $_POST['entry_id'] ?? null;
            if ($entryId) {
                $success = $this->timeModel->punchOut($userId, $entryId);
                if ($success) {
                    $_SESSION['flash_success'] = "Punched out successfully.";
                } else {
                    $_SESSION['flash_error'] = "Failed to punch out.";
                }
            }
        }
        
        $redirect = $_SERVER['HTTP_REFERER'] ?? URL_ROOT . '/time/my-time';
        header("Location: $redirect");
        exit;
    }

    public function myTime() {
        $userId = $_SESSION['user_id'];
        $activePunch = $this->timeModel->getActivePunch($userId);
        $recentEntries = $this->timeModel->getUserTimeEntries($userId);
        
        $taskModel = new Task();
        $tasks = $taskModel->getAllTasks($userId);
        
        $pageTitle = "My Time";
        
        ob_start();
        require BASE_PATH . '/templates/time/my_time.php';
        $content = ob_get_clean();
        
        require BASE_PATH . '/templates/layouts/main.php';
    }

    // --- Admin/Manager Functions ---
    private function requireAdmin() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['flash_error'] = "You do not have permission to access this page.";
            header("Location: " . URL_ROOT . "/dashboard");
            exit;
        }
    }

    public function timesheets() {
        $this->requireAdmin();
        
        $pendingEntries = $this->timeModel->getPendingTimesheets();
        
        $pageTitle = "Timesheet Approvals";
        
        ob_start();
        require BASE_PATH . '/templates/time/timesheets.php';
        $content = ob_get_clean();
        
        require BASE_PATH . '/templates/layouts/main.php';
    }

    /**
     * @param int $id
     */
    public function approve($id) {
        $this->requireAdmin();
        $this->timeModel->updateStatus($id, 'approved');
        $_SESSION['flash_success'] = "Time entry approved.";
        header("Location: " . URL_ROOT . "/time/timesheets");
        exit;
    }

    /**
     * @param int $id
     */
    public function reject($id) {
        $this->requireAdmin();
        $this->timeModel->updateStatus($id, 'rejected');
        $_SESSION['flash_success'] = "Time entry rejected.";
        header("Location: " . URL_ROOT . "/time/timesheets");
        exit;
    }

    public function reports() {
        $filters = [
            'user_id' => $_GET['user_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'), // Default to start of month
            'end_date' => $_GET['end_date'] ?? date('Y-m-d')
        ];
        
        // If not admin, force user_id to self
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $filters['user_id'] = $_SESSION['user_id'];
        }
        
        $reportData = $this->timeModel->getReportData($filters);
        $summaryStats = $this->timeModel->getSummaryStats($filters);
        
        // Fetch users for filter dropdown if admin
        $users = [];
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT id, name FROM users ORDER BY name");
            $users = $stmt->fetchAll();
        }

        $pageTitle = "Time Reports";
        
        ob_start();
        require BASE_PATH . '/templates/time/reports.php';
        $content = ob_get_clean();
        
        require BASE_PATH . '/templates/layouts/main.php';
    }
}
?>

<?php
require_once __DIR__ . '/../Models/Expense.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';

class ExpenseController {
    private $expenseModel;
    private $userModel;

    public function __construct() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: ' . URL_ROOT . '/login');
            exit();
        }
        $this->expenseModel = new Expense();
        $this->userModel = new User();
    }

    public function index() {
        $userId = $_SESSION['user_id'];
        $expenses = $this->expenseModel->getByUserId($userId);
        
        $error = $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);
        
        require_once BASE_PATH . '/templates/expenses/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $amount = $_POST['amount'] ?? 0;
            $description = trim($_POST['description'] ?? '');
            $category = trim($_POST['category'] ?? 'General');
            $date = $_POST['expense_date'] ?? date('Y-m-d');
            
            // Handle file upload if any
            $receiptPath = null;
            if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = BASE_PATH . '/public/uploads/receipts/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = time() . '_' . basename($_FILES['receipt']['name']);
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['receipt']['tmp_name'], $targetFile)) {
                    $receiptPath = '/uploads/receipts/' . $fileName;
                }
            }
            
            if (empty($description) || $amount <= 0) {
                $_SESSION['error'] = "Description and a valid amount are required.";
            } else {
                $this->expenseModel->create($userId, $amount, $description, $category, $date, $receiptPath);
                $_SESSION['success'] = "Expense submitted successfully.";
            }
            
            header('Location: ' . URL_ROOT . '/expenses');
            exit();
        }
    }

    public function delete($id) {
        $userId = $_SESSION['user_id'];
        $expense = $this->expenseModel->getById($id, $userId);
        
        if ($expense && $expense['status'] === 'pending') {
            $this->expenseModel->delete($id, $userId);
            $_SESSION['success'] = "Expense deleted successfully.";
        } else {
            $_SESSION['error'] = "You cannot delete this expense.";
        }
        
        header('Location: ' . URL_ROOT . '/expenses');
        exit();
    }

    public function approvals() {
        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = "Unauthorized access.";
            header('Location: ' . URL_ROOT . '/dashboard');
            exit();
        }
        
        $pendingExpenses = $this->expenseModel->getAllPending();
        
        $error = $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['error'], $_SESSION['success']);
        
        require_once BASE_PATH . '/templates/expenses/approvals.php';
    }

    public function approve($id) {
        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: ' . URL_ROOT . '/dashboard');
            exit();
        }
        
        $this->expenseModel->updateStatus($id, 'approved');
        $_SESSION['success'] = "Expense approved.";
        header('Location: ' . URL_ROOT . '/expenses/approvals');
        exit();
    }

    public function reject($id) {
        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: ' . URL_ROOT . '/dashboard');
            exit();
        }
        
        $this->expenseModel->updateStatus($id, 'rejected');
        $_SESSION['success'] = "Expense rejected.";
        header('Location: ' . URL_ROOT . '/expenses/approvals');
        exit();
    }

    public function reports() {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $reportData = $this->expenseModel->getReportData($startDate, $endDate);
        
        require_once BASE_PATH . '/templates/expenses/reports.php';
    }
}
?>

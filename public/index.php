<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

define('BASE_PATH', dirname(__DIR__));

// Use APP_URL from environment if available, otherwise determine dynamically
$appUrl = getenv('APP_URL');
if (!$appUrl) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseDir = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', BASE_PATH . '/public'));
    $appUrl = $protocol . $host . $baseDir;
}
define('URL_ROOT', rtrim($appUrl, '/'));

// Simple autoloader
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/src/Controllers/',
        BASE_PATH . '/src/Controllers/Web/',
        BASE_PATH . '/src/Controllers/Api/',
        BASE_PATH . '/src/Models/',
        BASE_PATH . '/src/helpers/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once BASE_PATH . '/config/database.php';

// Get route
$request = $_SERVER['REQUEST_URI'];
$basePath = parse_url(URL_ROOT, PHP_URL_PATH) ?: '';
$path = $request;
if ($basePath && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

$segments = explode('/', $path);
$route = $segments[0] ?? '';
$id = $segments[1] ?? null;
$action = $segments[2] ?? null;

file_put_contents(BASE_PATH . '/debug.log', "URI: {$_SERVER['REQUEST_URI']} | Path: $path | Route: $route | Method: $method\n", FILE_APPEND);

try {
    if ($route === 'register') {
        $authController = new AuthController();
        if ($method === 'POST') {
            $authController->register();
        } else {
            $authController->showRegister();
        }
    }
    elseif ($route === 'login') {
        $authController = new AuthController();
        if ($method === 'POST') {
            $authController->login();
        } else {
            $authController->showLogin();
        }
    }
    elseif ($route === 'logout') {
        $authController = new AuthController();
        $authController->logout();
    }
    elseif ($route === 'dashboard') {
        $taskController = new TaskController();
        $taskController->dashboard();
    }
    elseif ($route === '') {
        if (SessionHelper::isLoggedIn()) {
            header('Location: ' . URL_ROOT . '/dashboard');
        } else {
            header('Location: ' . URL_ROOT . '/login');
        }
        exit();
    }
    elseif ($route === 'tasks') {
        $taskController = new TaskController();
        if ($id === 'create') {
            if ($method === 'POST') {
                $taskController->store();
            } else {
                $taskController->create();
            }
        } elseif ($id === 'export') {
            $taskController->export();
        } elseif ($id === 'delete' && !empty($action)) {
            $taskController->delete($action);
        } elseif ($id === 'status' && !empty($action)) {
            $taskController->updateStatus($action);
        } elseif ($id === 'edit' && !empty($action)) {
            if ($method === 'POST') {
                $taskController->update($action);
            } else {
                $taskController->edit($action);
            }
        } elseif ($id === 'view' && !empty($action)) {
            $taskController->show($action);
        } elseif ($id === 'comment' && !empty($action) && $method === 'POST') {
            $taskController->addComment($action);
        } elseif ($id === 'attach' && !empty($action) && $method === 'POST') {
            $taskController->addAttachment($action);
        } else {
            $taskController->index();
        }
    }
    elseif ($route === 'profile') {
        $authController = new AuthController();
        if ($method === 'POST') {
            $action_type = $_POST['action_type'] ?? '';
            if ($action_type === 'update_profile') {
                $authController->updateProfile();
            } elseif ($action_type === 'change_password') {
                $authController->changePassword();
            }
        } else {
            $authController->showProfile();
        }
    }
    elseif ($route === 'analytics') {
        $taskController = new TaskController();
        $taskController->analytics();
    }
    elseif ($route === 'calendar') {
        $taskController = new TaskController();
        $taskController->calendar();
    }
    elseif ($route === 'gantt') {
        $taskController = new TaskController();
        $taskController->gantt();
    }
    elseif ($route === 'tags') {
        // /tags/add/{taskId}  POST
        // /tags/remove/{taskId}/{tagId}
        $taskController = new TaskController();
        if ($id === 'add' && !empty($action) && $method === 'POST') {
            $taskController->addTag($action);
        } elseif ($id === 'remove' && !empty($action)) {
            $tagId = $segments[3] ?? null;
            $taskController->removeTag($action, $tagId);
        }
    }
    elseif ($route === 'api') {
        $taskController = new TaskController();
        if ($id === 'update-status' && !empty($action)) {
            $taskController->apiUpdateStatus($action);
        } elseif ($id === 'update-title' && !empty($action)) {
            $taskController->apiUpdateTitle($action);
        } elseif ($id === 'gantt-update') {
            $taskController->apiGanttUpdate();
        } elseif ($id === 'portfolio-stats') {
            $apiPortfolio = new ApiPortfolioController();
            $apiPortfolio->getStats();
        } elseif ($id === 'portfolio-stream') {
            $apiPortfolio = new ApiPortfolioController();
            $apiPortfolio->getRealtimeStream();
        } elseif ($id === 'portfolio-quick-update') {
            $apiPortfolio = new ApiPortfolioController();
            $apiPortfolio->quickUpdate();
        }
    }
    elseif ($route === 'portfolio') {
        $portfolioController = new PortfolioController();
        if ($id === 'create' && $method === 'POST') {
            $portfolioController->store();
        } elseif ($id === 'edit' && !empty($action) && $method === 'POST') {
            $portfolioController->update($action);
        } elseif ($id === 'delete' && !empty($action)) {
            $portfolioController->delete($action);
        } elseif ($id === 'view' && !empty($action)) {
            $portfolioController->show($action);
        } else {
            $portfolioController->index();
        }
    }
    elseif ($route === 'subtasks') {
        // /subtasks/add/{parentId}    POST
        // /subtasks/toggle/{subtaskId}
        // /subtasks/delete/{subtaskId}
        $taskController = new TaskController();
        if ($id === 'add' && !empty($action) && $method === 'POST') {
            $taskController->addSubtask($action);
        } elseif ($id === 'toggle' && !empty($action)) {
            $taskController->toggleSubtask($action);
        } elseif ($id === 'delete' && !empty($action)) {
            $taskController->deleteSubtask($action);
        }
    }
    elseif ($route === 'attachments') {
        $taskController = new TaskController();
        if ($id === 'lock' && !empty($action)) {
            $taskController->lockAttachment($action);
        } elseif ($id === 'unlock' && !empty($action)) {
            $taskController->unlockAttachment($action);
        } elseif ($id === 'delete' && !empty($action)) {
            $taskController->deleteAttachment($action);
        }
    }
    elseif ($route === 'expenses') {
        $expenseController = new ExpenseController();
        if ($id === 'approvals') {
            $expenseController->approvals();
        } elseif ($id === 'approve' && !empty($action) && $method === 'POST') {
            $expenseController->approve($action);
        } elseif ($id === 'reject' && !empty($action) && $method === 'POST') {
            $expenseController->reject($action);
        } elseif ($id === 'reports') {
            $expenseController->reports();
        } elseif ($id === 'delete' && !empty($action) && $method === 'POST') {
            $expenseController->delete($action);
        } elseif ($id === 'create' && $method === 'POST') {
            $expenseController->store();
        } else {
            $expenseController->index();
        }
    }
    elseif ($route === 'time') {
        $timeController = new TimeController();
        if ($id === 'punch' && $method === 'POST') {
            $timeController->punch();
        } elseif ($id === 'my-time') {
            $timeController->myTime();
        } elseif ($id === 'timesheets') {
            $timeController->timesheets();
        } elseif ($id === 'approve' && !empty($action)) {
            $timeController->approve($action);
        } elseif ($id === 'reject' && !empty($action)) {
            $timeController->reject($action);
        } elseif ($id === 'reports') {
            $timeController->reports();
        } else {
            $timeController->myTime();
        }
    }
    elseif ($route === 'custom-fields') {
        $cfController = new CustomFieldController();
        if ($id === 'create') {
            if ($method === 'POST') {
                $cfController->store();
            } else {
                $cfController->create();
            }
        } elseif ($id === 'edit' && !empty($action)) {
            if ($method === 'POST') {
                $cfController->update($action);
            } else {
                $cfController->edit($action);
            }
        } elseif ($id === 'delete' && !empty($action)) {
            $cfController->delete($action);
        } else {
            $cfController->index();
        }
    }
    elseif ($route === 'reports') {
        $reportController = new ReportController();
        if ($id === 'generate') {
            $reportController->generateApi();
        } elseif ($id === 'save') {
            $reportController->save();
        } elseif ($id === 'export') {
            $reportController->exportCsv();
        } elseif ($id === 'delete' && !empty($action)) {
            $reportController->delete($action);
        } else {
            $reportController->index();
        }
    }
    else {
        require_once BASE_PATH . '/templates/404.php';
    }
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
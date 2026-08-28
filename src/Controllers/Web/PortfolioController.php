<?php
require_once BASE_PATH . '/src/Models/Project.php';
require_once BASE_PATH . '/src/Models/Category.php';
require_once BASE_PATH . '/src/Models/User.php';
require_once BASE_PATH . '/src/helpers/SessionHelper.php';

class PortfolioController {
    /** @var Project */
    private $projectModel;
    /** @var Category */
    private $categoryModel;
    /** @var User */
    private $userModel;

    public function __construct() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: ' . URL_ROOT . '/login');
            exit();
        }
        $this->projectModel = new Project();
        $this->categoryModel = new Category();
        $this->userModel = new User();
    }

    public function index() {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'health' => $_GET['health'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $projects = $this->projectModel->getAll($filters);
        $summary = $this->projectModel->getPortfolioSummary();
        $categories = $this->categoryModel->getAll($_SESSION['user_id']);
        $users = $this->userModel->getAllUsers();

        $pageTitle = "Portfolio Management & Real-Time Tracking";
        
        ob_start();
        require BASE_PATH . '/templates/portfolio/index.php';
        $content = ob_get_clean();
        require BASE_PATH . '/templates/layouts/main.php';
    }

    /**
     * Show a single project
     *
     * @param int $id
     * @return void
     */
    public function show($id) {
        $project = $this->projectModel->getById($id);
        if (!$project) {
            $_SESSION['flash_error'] = "Project not found.";
            header('Location: ' . URL_ROOT . '/portfolio');
            exit();
        }

        $tasks = $this->projectModel->getTasksForProject($id);
        $users = $this->userModel->getAllUsers();
        $categories = $this->categoryModel->getAll($_SESSION['user_id']);

        $pageTitle = "Project: " . htmlspecialchars($project['name']);

        ob_start();
        require BASE_PATH . '/templates/portfolio/show.php';
        $content = ob_get_clean();
        require BASE_PATH . '/templates/layouts/main.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/portfolio');
            exit();
        }

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'code'        => trim($_POST['code'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status'      => $_POST['status'] ?? 'Active',
            'health'      => $_POST['health'] ?? 'On Track',
            'owner_id'    => !empty($_POST['owner_id']) ? $_POST['owner_id'] : null,
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'budget'      => !empty($_POST['budget']) ? (float)$_POST['budget'] : 0.00,
            'spent'       => !empty($_POST['spent']) ? (float)$_POST['spent'] : 0.00,
            'start_date'  => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'end_date'    => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        ];

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = "Project name is required.";
            header('Location: ' . URL_ROOT . '/portfolio');
            exit();
        }

        $projectId = $this->projectModel->create($data);
        if ($projectId) {
            $_SESSION['flash_success'] = "Project '{$data['name']}' created successfully!";
        } else {
            $_SESSION['flash_error'] = "Failed to create project.";
        }

        header('Location: ' . URL_ROOT . '/portfolio');
        exit();
    }

    /**
     * Update an existing project
     *
     * @param int $id
     * @return void
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/portfolio');
            exit();
        }

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'code'        => trim($_POST['code'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status'      => $_POST['status'] ?? 'Active',
            'health'      => $_POST['health'] ?? 'On Track',
            'owner_id'    => !empty($_POST['owner_id']) ? $_POST['owner_id'] : null,
            'category_id' => !empty($_POST['category_id']) ? $_POST['category_id'] : null,
            'budget'      => !empty($_POST['budget']) ? (float)$_POST['budget'] : 0.00,
            'spent'       => !empty($_POST['spent']) ? (float)$_POST['spent'] : 0.00,
            'start_date'  => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'end_date'    => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        ];

        if ($this->projectModel->update($id, $data)) {
            $_SESSION['flash_success'] = "Project '{$data['name']}' updated successfully!";
        } else {
            $_SESSION['flash_error'] = "Failed to update project.";
        }

        header('Location: ' . URL_ROOT . '/portfolio');
        exit();
    }

    /**
     * Delete a project
     *
     * @param int $id
     * @return void
     */
    public function delete($id) {
        if ($this->projectModel->delete($id)) {
            $_SESSION['flash_success'] = "Project deleted successfully!";
        } else {
            $_SESSION['flash_error'] = "Failed to delete project.";
        }

        header('Location: ' . URL_ROOT . '/portfolio');
        exit();
    }
}

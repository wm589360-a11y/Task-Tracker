<?php
require_once BASE_PATH . '/src/Models/Project.php';
require_once BASE_PATH . '/src/helpers/SessionHelper.php';

class ApiPortfolioController {
    /** @var Project $projectModel */
    private $projectModel;

    public function __construct() {
        header('Content-Type: application/json');
        if (!SessionHelper::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
        $this->projectModel = new Project();
    }

    public function getStats() {
        $summary = $this->projectModel->getPortfolioSummary();
        $projects = $this->projectModel->getAll();
        echo json_encode([
            'success' => true,
            'summary' => $summary,
            'projects' => $projects
        ]);
        exit();
    }

    public function getRealtimeStream() {
        $since = $_GET['since'] ?? null;
        $feed = $this->projectModel->getRealtimeFeed($since);
        echo json_encode(array_merge(['success' => true], $feed));
        exit();
    }

    public function quickUpdate() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $id = $input['id'] ?? null;
        $health = $input['health'] ?? null;
        $status = $input['status'] ?? null;

        if (!$id || !$health) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing project ID or health value']);
            exit();
        }

        $success = $this->projectModel->updateQuick($id, $health, $status);
        echo json_encode(['success' => $success]);
        exit();
    }
}

<?php
require_once dirname(__DIR__) . '/Models/Report.php';
require_once dirname(__DIR__) . '/Models/Category.php';
require_once dirname(__DIR__) . '/Models/User.php';
require_once dirname(__DIR__) . '/helpers/SessionHelper.php';

class ReportController {
    private $reportModel;
    private $categoryModel;
    private $userModel;

    public function __construct() {
        $this->reportModel   = new Report();
        $this->categoryModel = new Category();
        $this->userModel     = new User();
        SessionHelper::start();
        SessionHelper::requireLogin();
    }

    private function userId() {
        return SessionHelper::get('user_id');
    }

    public function index() {
        $userId       = $this->userId();
        $categories   = $this->categoryModel->getAll($userId);
        $users        = $this->userModel->getAllUsers();
        $savedReports = $this->reportModel->getSavedReports($userId);

        // Load active report configuration if 'load' parameter is present
        $activeReport = null;
        if (!empty($_GET['load'])) {
            $activeReport = $this->reportModel->getSavedReportById((int)$_GET['load'], $userId);
        }

        // Default initial report parameters
        $params = [
            'entity'   => $_GET['entity'] ?? ($activeReport['entity'] ?? 'tasks'),
            'group_by' => $_GET['group_by'] ?? ($activeReport['group_by'] ?? 'category'),
            'filters'  => [
                'start_date'  => $_GET['start_date']  ?? '',
                'end_date'    => $_GET['end_date']    ?? '',
                'status'      => $_GET['status']      ?? '',
                'priority'    => $_GET['priority']    ?? '',
                'category_id' => $_GET['category_id'] ?? '',
                'assigned_to' => $_GET['assigned_to'] ?? '',
            ]
        ];

        // Parse filters if activeReport exists
        if ($activeReport && !empty($activeReport['filters'])) {
            $decoded = json_decode($activeReport['filters'], true);
            if (is_array($decoded)) {
                $params['filters'] = array_merge($params['filters'], array_filter($decoded));
            }
        }

        $reportResult = $this->reportModel->generateReport($userId, $params);
        $chartType    = $_GET['chart_type'] ?? ($activeReport['chart_type'] ?? 'bar');

        require_once dirname(__DIR__) . '/../templates/reports/builder.php';
    }

    public function generateApi() {
        header('Content-Type: application/json');
        $userId = $this->userId();
        
        $params = [
            'entity'   => $_POST['entity'] ?? 'tasks',
            'group_by' => $_POST['group_by'] ?? 'category',
            'filters'  => [
                'start_date'  => $_POST['start_date']  ?? '',
                'end_date'    => $_POST['end_date']    ?? '',
                'status'      => $_POST['status']      ?? '',
                'priority'    => $_POST['priority']    ?? '',
                'category_id' => $_POST['category_id'] ?? '',
                'assigned_to' => $_POST['assigned_to'] ?? '',
            ]
        ];

        $result = $this->reportModel->generateReport($userId, $params);
        echo json_encode(['success' => true, 'result' => $result]);
        exit();
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            SessionHelper::setFlash('error', 'Invalid request method.');
            header('Location: ' . URL_ROOT . '/reports');
            exit();
        }

        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            SessionHelper::setFlash('error', 'Report title is required.');
            header('Location: ' . URL_ROOT . '/reports');
            exit();
        }

        $data = [
            'title'       => $title,
            'description' => $_POST['description'] ?? '',
            'entity'      => $_POST['entity'] ?? 'tasks',
            'group_by'    => $_POST['group_by'] ?? 'category',
            'chart_type'  => $_POST['chart_type'] ?? 'bar',
            'metrics'     => $_POST['metrics'] ?? ['count', 'completion_rate'],
            'filters'     => [
                'start_date'  => $_POST['start_date']  ?? '',
                'end_date'    => $_POST['end_date']    ?? '',
                'status'      => $_POST['status']      ?? '',
                'priority'    => $_POST['priority']    ?? '',
                'category_id' => $_POST['category_id'] ?? '',
                'assigned_to' => $_POST['assigned_to'] ?? '',
            ]
        ];

        $reportId = $this->reportModel->saveReport($this->userId(), $data);

        if ($reportId) {
            SessionHelper::setFlash('success', 'Report template saved successfully.');
        } else {
            SessionHelper::setFlash('error', 'Failed to save report template.');
        }

        header('Location: ' . URL_ROOT . '/reports?load=' . $reportId);
        exit();
    }

    public function exportCsv() {
        $userId = $this->userId();
        $params = [
            'entity'   => $_GET['entity'] ?? 'tasks',
            'group_by' => $_GET['group_by'] ?? 'category',
            'filters'  => [
                'start_date'  => $_GET['start_date']  ?? '',
                'end_date'    => $_GET['end_date']    ?? '',
                'status'      => $_GET['status']      ?? '',
                'priority'    => $_GET['priority']    ?? '',
                'category_id' => $_GET['category_id'] ?? '',
                'assigned_to' => $_GET['assigned_to'] ?? '',
            ]
        ];

        $result = $this->reportModel->generateReport($userId, $params);
        $rows   = $result['data'] ?? [];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="custom_report_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        if (!empty($rows)) {
            // Write CSV headers dynamically from result keys
            $headers = array_map(function($key) {
                return ucwords(str_replace('_', ' ', $key));
            }, array_keys($rows[0]));
            
            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, array_values($row));
            }
        } else {
            fputcsv($output, ['No data available for the selected filters']);
        }

        fclose($output);
        exit();
    }

    public function delete($id) {
        $deleted = $this->reportModel->deleteSavedReport($id, $this->userId());
        if ($deleted) {
            SessionHelper::setFlash('success', 'Saved report deleted.');
        } else {
            SessionHelper::setFlash('error', 'Failed to delete report.');
        }
        header('Location: ' . URL_ROOT . '/reports');
        exit();
    }
}
?>

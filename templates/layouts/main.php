<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Advanced Task Tracker'; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= URL_ROOT ?>/assets/css/style.css">
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?= URL_ROOT ?>/">
            <i class="bi bi-check2-square"></i> Advanced Task Tracker
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_ROOT ?>/dashboard">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_ROOT ?>/tasks">
                            <i class="bi bi-list-check"></i> Tasks
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_ROOT ?>/analytics">
                            <i class="bi bi-bar-chart-line"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_ROOT ?>/calendar">
                            <i class="bi bi-calendar3"></i> Calendar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_ROOT ?>/gantt">
                            <i class="bi bi-bar-chart-steps"></i> Gantt
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_ROOT ?>/reports">
                            <i class="bi bi-pie-chart"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="expensesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-wallet2"></i> Expenses
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="expensesDropdown">
                            <li><a class="dropdown-item" href="<?= URL_ROOT ?>/expenses">My Expenses</a></li>
                            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= URL_ROOT ?>/expenses/approvals">Approvals</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= URL_ROOT ?>/expenses/reports">Reports</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="timeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-clock"></i> Time
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="timeDropdown">
                            <li><a class="dropdown-item" href="<?= URL_ROOT ?>/time/my-time">My Time</a></li>
                            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= URL_ROOT ?>/time/timesheets">Timesheets</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= URL_ROOT ?>/time/reports">Reports</a></li>
                        </ul>
                    </li>
                    <?php
                        $timeModelForNav = class_exists('TimeEntry') ? new TimeEntry() : null;
                        if (!$timeModelForNav && file_exists(BASE_PATH . '/src/Models/TimeEntry.php')) {
                            require_once BASE_PATH . '/src/Models/TimeEntry.php';
                            $timeModelForNav = new TimeEntry();
                        }
                        if ($timeModelForNav) {
                            $activePunch = $timeModelForNav->getActivePunch($_SESSION['user_id']);
                            if ($activePunch):
                    ?>
                    <li class="nav-item">
                        <form method="POST" action="<?= URL_ROOT ?>/time/punch" class="d-inline">
                            <input type="hidden" name="action" value="out">
                            <input type="hidden" name="entry_id" value="<?php echo $activePunch['id']; ?>">
                            <button type="submit" class="btn btn-warning btn-sm mt-1 ms-2 fw-bold text-dark">
                                <i class="bi bi-stop-circle"></i> Punch Out (<?php echo date('H:i', strtotime($activePunch['clock_in'])); ?>)
                            </button>
                        </form>
                    </li>
                    <?php endif; } ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_ROOT ?>/profile">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn btn-link" id="themeToggle" onclick="toggleTheme()">
                            <i class="bi bi-moon-fill"></i>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="settingsDropdown">
                            <li><a class="dropdown-item" href="<?= URL_ROOT ?>/custom-fields">
                                <i class="bi bi-sliders me-2"></i>Custom Fields
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_ROOT ?>/logout">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_ROOT ?>/login">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= URL_ROOT ?>/register">
                            <i class="bi bi-person-plus"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="py-4">
    <div class="container">
        <!-- Flash Messages -->
        <?php if(isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i>
                <?php
                echo $_SESSION['flash_success'];
                unset($_SESSION['flash_success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i>
                <?php
                echo $_SESSION['flash_error'];
                unset($_SESSION['flash_error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php
        // ── Overdue Task Notification Banner ───────────────────────────
        if (isset($_SESSION['user_id'])) {
            $taskModelForAlert = new Task();
            $overdueTasks = $taskModelForAlert->getOverdueTasks($_SESSION['user_id']);
            if (!empty($overdueTasks)):
        ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 border-0" role="alert"
             style="background: linear-gradient(135deg,#ff4e50,#f9d423); color:#fff; border-radius:10px;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <strong><?php echo count($overdueTasks); ?> Overdue Task(s):</strong>
                <?php foreach($overdueTasks as $ot): ?>
                    <a href="<?= URL_ROOT ?>/tasks/view/<?php echo $ot['id']; ?>"
                       class="badge bg-white text-danger text-decoration-none me-1">
                        <?php echo htmlspecialchars($ot['title']); ?>
                        &mdash; <?php echo date('M d', strtotime($ot['due_date'])); ?>
                    </a>
                <?php endforeach; ?>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php endif; } ?>

        <!-- Page Content -->
        <?php echo $content ?? ''; ?>
    </div>
</main>

<!-- Footer -->
<footer class="bg-light py-3 mt-4">
    <div class="container text-center">
        <small class="text-muted">
            &copy; <?php echo date('Y'); ?> Advanced Task Tracker. All rights reserved.
        </small>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Global App URL for JS files -->
<script>
    const APP_URL = "<?= URL_ROOT ?>";
</script>
<!-- Custom JS -->
<script src="<?= URL_ROOT ?>/assets/js/main.js"></script>
</body>
</html>
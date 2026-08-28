<?php
$pageTitle = 'Dashboard - Advanced Task Tracker';
ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h3 mb-0 text-gray-800">Welcome back, <?php echo htmlspecialchars(SessionHelper::get('user_name', 'User')); ?>! 👋</h2>
            <a href="<?= URL_ROOT ?>/tasks/create" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-lg"></i> Create New Task
            </a>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <!-- Total Tasks Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-primary border-4">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Tasks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($stats['total_tasks'] ?? 0); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-list-task fs-2 text-secondary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Tasks Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-success border-4">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Completed</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($stats['completed_tasks'] ?? 0); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fs-2 text-secondary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Tasks Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-warning border-4">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars($stats['pending_tasks'] ?? 0); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-hourglass-split fs-2 text-secondary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Completion Rate Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 py-2 border-start border-info border-4">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Completion Rate
                        </div>
                        <div class="row no-gutters align-items-center mt-2">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800 me-2"><?php echo htmlspecialchars($stats['completion_rate'] ?? 0); ?>%</div>
                            </div>
                            <div class="col">
                                <div class="progress progress-sm mr-2">
                                    <div class="progress-bar bg-info" role="progressbar"
                                        style="width: <?php echo htmlspecialchars($stats['completion_rate'] ?? 0); ?>%" aria-valuenow="<?php echo htmlspecialchars($stats['completion_rate'] ?? 0); ?>" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-percent fs-2 text-secondary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Tasks List -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white border-bottom-0">
                <h6 class="m-0 font-weight-bold text-primary">Recent Tasks</h6>
                <a href="<?= URL_ROOT ?>/tasks" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentTasks)): ?>
                    <div class="text-center py-5">
                        <img src="<?= URL_ROOT ?>/assets/img/empty-tasks.svg" alt="No tasks" class="img-fluid mb-3" style="max-height: 150px; opacity: 0.5;">
                        <p class="text-muted">No tasks found. Time to create your first one!</p>
                        <a href="<?= URL_ROOT ?>/tasks/create" class="btn btn-primary mt-2">Create Task</a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush rounded-bottom">
                        <?php foreach ($recentTasks as $task): ?>
                            <a href="<?= URL_ROOT ?>/tasks/view/<?php echo $task['id']; ?>" class="list-group-item list-group-item-action py-3">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <h5 class="mb-1 text-truncate" style="max-width: 70%;"><?php echo htmlspecialchars($task['title']); ?></h5>
                                    <?php
                                        $badgeClass = 'bg-secondary';
                                        if ($task['status'] === 'Completed') $badgeClass = 'bg-success';
                                        elseif ($task['status'] === 'In Progress') $badgeClass = 'bg-primary';
                                        elseif ($task['status'] === 'Pending') $badgeClass = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge rounded-pill <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($task['status']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        <?php if (!empty($task['due_date'])): ?>
                                            <i class="bi bi-calendar-event me-1"></i> Due: <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                        <?php else: ?>
                                            <i class="bi bi-clock me-1"></i> No due date
                                        <?php endif; ?>
                                    </small>
                                    <?php
                                        $prioClass = 'text-secondary';
                                        if ($task['priority'] === 'Urgent') $prioClass = 'text-danger fw-bold';
                                        elseif ($task['priority'] === 'High') $prioClass = 'text-danger';
                                        elseif ($task['priority'] === 'Medium') $prioClass = 'text-primary';
                                    ?>
                                    <small class="<?php echo $prioClass; ?>">
                                        <i class="bi bi-flag-fill me-1"></i> <?php echo htmlspecialchars($task['priority']); ?>
                                    </small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Overdue Tasks & Priority Breakdown -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header py-3 bg-white border-bottom-0">
                <h6 class="m-0 font-weight-bold text-danger">Attention Needed</h6>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex align-items-center p-3 bg-danger bg-opacity-10 rounded">
                    <div class="me-3">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-3"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 text-danger font-weight-bold"><?php echo htmlspecialchars($stats['overdue_tasks'] ?? 0); ?></h4>
                        <span class="text-danger small">Overdue Tasks</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Priority Breakdown -->
        <div class="card shadow-sm border-0">
            <div class="card-header py-3 bg-white border-bottom-0">
                <h6 class="m-0 font-weight-bold text-primary">Task Priorities</h6>
            </div>
            <div class="card-body pt-0">
                <?php if (empty($stats['priority_breakdown'])): ?>
                    <p class="text-muted text-center small mb-0">No tasks available for priority breakdown.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush mt-2">
                        <?php foreach ($stats['priority_breakdown'] as $pb): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-0 pb-2">
                                <?php echo htmlspecialchars($pb['priority']); ?>
                                <span class="badge bg-secondary rounded-pill"><?php echo htmlspecialchars($pb['count']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Specific Styles */
.border-start {
    border-left-width: 4px !important;
}
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.list-group-item {
    transition: background-color 0.2s ease;
}
.list-group-item:hover {
    background-color: #f8f9fa;
    z-index: 1;
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layouts/main.php';
?>

<?php
$pageTitle = 'All Tasks - Advanced Task Tracker';
ob_start();
$filters = [
    'status'   => $_GET['status']   ?? '',
    'priority' => $_GET['priority'] ?? '',
    'search'   => $_GET['search']   ?? '',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="h3 mb-0"><i class="bi bi-list-task text-primary me-2"></i>My Tasks
        <span class="badge bg-secondary ms-2"><?php echo count($tasks); ?></span>
    </h2>
    <div class="d-flex gap-2">
        <a href="<?= URL_ROOT ?>/tasks/export" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
        <a href="<?= URL_ROOT ?>/tasks/create" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg"></i> New Task
        </a>
    </div>
</div>

<!-- Search & Filter Bar -->
<form method="GET" action="<?= URL_ROOT ?>/tasks" class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by title or description..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php foreach(['Pending','In Progress','Completed'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $filters['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <?php foreach(['Low','Medium','High','Urgent'] as $p): ?>
                        <option value="<?php echo $p; ?>" <?php echo $filters['priority'] === $p ? 'selected' : ''; ?>><?php echo $p; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i> Filter</button>
                <a href="<?= URL_ROOT ?>/tasks" class="btn btn-outline-secondary flex-fill"><i class="bi bi-x-lg"></i> Clear</a>
            </div>
        </div>
    </div>
</form>

<?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['priority'])): ?>
    <div class="alert alert-info alert-sm py-2 d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-info-circle-fill"></i>
        Showing <strong><?php echo count($tasks); ?></strong> result(s) for your filter.
        <a href="<?= URL_ROOT ?>/tasks" class="ms-auto btn btn-sm btn-outline-info">Clear Filters</a>
    </div>
<?php endif; ?>

<!-- Task Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <?php if (empty($tasks)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted opacity-50"></i>
                <h4 class="text-muted mt-3">No tasks found.</h4>
                <a href="<?= URL_ROOT ?>/tasks/create" class="btn btn-primary mt-2">Create a Task</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small text-secondary">
                        <tr>
                            <th class="ps-4 border-0">Task</th>
                            <th class="border-0">Category</th>
                            <th class="border-0">Due Date</th>
                            <th class="border-0 text-center">Priority</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="pe-4 border-0 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tasks as $task): ?>
                            <tr>
                                <td class="ps-4">
                                    <h6 class="mb-0 fw-semibold inline-title"
                                        data-task-id="<?php echo $task['id']; ?>"
                                        title="Double-click to inline-edit">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </h6>
                                    <?php if (!empty($task['description'])): ?>
                                        <small class="text-muted text-truncate d-inline-block" style="max-width:260px;">
                                            <?php echo htmlspecialchars($task['description']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($task['category_name'])): ?>
                                        <span class="badge bg-light text-dark border"><i class="bi bi-tag me-1"></i><?php echo htmlspecialchars($task['category_name']); ?></span>
                                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($task['due_date'])): ?>
                                        <?php $overdue = strtotime($task['due_date']) < strtotime('today') && $task['status'] !== 'Completed'; ?>
                                        <span class="<?php echo $overdue ? 'text-danger fw-bold' : ''; ?>">
                                            <i class="bi <?php echo $overdue ? 'bi-exclamation-circle-fill' : 'bi-calendar-event'; ?> me-1"></i>
                                            <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                        </span>
                                    <?php else: ?><span class="text-muted small">No date</span><?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                        $pc = match($task['priority']) {
                                            'Urgent' => 'bg-danger',
                                            'High'   => 'bg-warning text-dark',
                                            'Medium' => 'bg-primary',
                                            default  => 'bg-secondary',
                                        };
                                    ?>
                                    <span class="badge rounded-pill <?php echo $pc; ?>"><?php echo $task['priority']; ?></span>
                                </td>
                                <td class="text-center">
                                    <select class="form-select form-select-sm ajax-status"
                                            data-task-id="<?php echo $task['id']; ?>"
                                            style="width:auto;margin:0 auto;">
                                        <option value="Pending"     <?php echo $task['status']==='Pending'?'selected':''; ?>>⏳ Pending</option>
                                        <option value="In Progress" <?php echo $task['status']==='In Progress'?'selected':''; ?>>🔵 In Progress</option>
                                        <option value="Completed"   <?php echo $task['status']==='Completed'?'selected':''; ?>>✅ Completed</option>
                                    </select>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group shadow-sm">
                                        <a href="<?= URL_ROOT ?>/tasks/view/<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                                        <a href="<?= URL_ROOT ?>/tasks/edit/<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="<?= URL_ROOT ?>/tasks/delete/<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this task?');"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/main.php'; ?>
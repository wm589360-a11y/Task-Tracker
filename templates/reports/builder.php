<?php
$pageTitle = 'Custom Report Builder - Advanced Task Tracker';
ob_start();

$currentEntity  = $params['entity'] ?? 'tasks';
$currentGroupBy = $params['group_by'] ?? 'category';
$currentFilters = $params['filters'] ?? [];
$summary        = $reportResult['summary'] ?? [];
$rows           = $reportResult['data'] ?? [];
$groupLabel     = $reportResult['group_label'] ?? 'Group';
?>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
    --rpt-gradient: linear-gradient(135deg, #111827 0%, #1f2937 50%, #374151 100%);
    --rpt-card-radius: 16px;
}
.rpt-hero {
    background: var(--rpt-gradient);
    border-radius: var(--rpt-card-radius);
    color: #fff;
    padding: 2rem 2rem 1.5rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}
.rpt-hero::before {
    content: '';
    position: absolute; top: -50px; right: -50px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.rpt-card {
    background: #fff;
    border-radius: var(--rpt-card-radius);
    border: 1px solid #e5e7eb;
}
.kpi-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    padding: 1.25rem;
    transition: transform .15s, box-shadow .15s;
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}
.kpi-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
}
</style>

<!-- Hero Banner -->
<div class="rpt-hero shadow">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative" style="z-index:1">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-pie-chart-fill text-info me-2"></i>Custom Report Builder</h1>
            <p class="mb-0 opacity-75">Aggregate, group, and analyze your tasks, time tracking, and expense data.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#saveReportModal">
                <i class="bi bi-bookmark-plus me-1"></i> Save Template
            </button>
            <a href="/Task-Tracker/public/reports/export?<?php echo http_build_query($_GET); ?>" class="btn btn-success btn-sm fw-semibold shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
        </div>
    </div>
</div>

<!-- Builder Controls & Filter Panel -->
<div class="card rpt-card shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 px-4">
        <h5 class="mb-0 fw-bold text-gray-800">
            <i class="bi bi-sliders me-2 text-primary"></i>Report Configuration & Aggregation Controls
        </h5>
    </div>
    <div class="card-body px-4 pt-0 pb-4">
        <form method="GET" action="/Task-Tracker/public/reports" id="reportFilterForm">
            <div class="row g-3">
                <!-- Entity Selector -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Data Source (Entity)</label>
                    <select name="entity" class="form-select fw-semibold" onchange="onEntityChange(this.value)">
                        <option value="tasks" <?php echo $currentEntity === 'tasks' ? 'selected' : ''; ?>>📋 Tasks</option>
                        <option value="time" <?php echo $currentEntity === 'time' ? 'selected' : ''; ?>>⏱️ Time Entries</option>
                        <option value="expenses" <?php echo $currentEntity === 'expenses' ? 'selected' : ''; ?>>💰 Expenses</option>
                    </select>
                </div>

                <!-- Group By Dimension -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Group By (Dimension)</label>
                    <select name="group_by" id="groupBySelect" class="form-select fw-semibold">
                        <?php if ($currentEntity === 'tasks'): ?>
                            <option value="category" <?php echo $currentGroupBy === 'category' ? 'selected' : ''; ?>>Category</option>
                            <option value="status" <?php echo $currentGroupBy === 'status' ? 'selected' : ''; ?>>Status</option>
                            <option value="priority" <?php echo $currentGroupBy === 'priority' ? 'selected' : ''; ?>>Priority</option>
                            <option value="assigned_to" <?php echo $currentGroupBy === 'assigned_to' ? 'selected' : ''; ?>>Assigned User</option>
                            <option value="month" <?php echo $currentGroupBy === 'month' ? 'selected' : ''; ?>>Creation Month</option>
                            <option value="due_month" <?php echo $currentGroupBy === 'due_month' ? 'selected' : ''; ?>>Due Month</option>
                        <?php elseif ($currentEntity === 'time'): ?>
                            <option value="category" <?php echo $currentGroupBy === 'category' ? 'selected' : ''; ?>>Task Category</option>
                            <option value="user" <?php echo $currentGroupBy === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="month" <?php echo $currentGroupBy === 'month' ? 'selected' : ''; ?>>Clocked Month</option>
                            <option value="status" <?php echo $currentGroupBy === 'status' ? 'selected' : ''; ?>>Approval Status</option>
                        <?php else: ?>
                            <option value="category" <?php echo $currentGroupBy === 'category' ? 'selected' : ''; ?>>Category</option>
                            <option value="user" <?php echo $currentGroupBy === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="month" <?php echo $currentGroupBy === 'month' ? 'selected' : ''; ?>>Expense Month</option>
                            <option value="status" <?php echo $currentGroupBy === 'status' ? 'selected' : ''; ?>>Approval Status</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Chart Type -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Visualization Chart</label>
                    <select name="chart_type" class="form-select fw-semibold">
                        <option value="bar" <?php echo $chartType === 'bar' ? 'selected' : ''; ?>>📊 Bar Chart</option>
                        <option value="doughnut" <?php echo $chartType === 'doughnut' ? 'selected' : ''; ?>>🍩 Doughnut / Pie</option>
                        <option value="line" <?php echo $chartType === 'line' ? 'selected' : ''; ?>>📈 Line Trend</option>
                    </select>
                </div>

                <!-- Date Filters -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($currentFilters['start_date'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($currentFilters['end_date'] ?? ''); ?>">
                </div>

                <!-- Filters for Tasks -->
                <?php if ($currentEntity === 'tasks'): ?>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Filter Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php echo ($currentFilters['status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo ($currentFilters['status'] ?? '') === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo ($currentFilters['status'] ?? '') === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Filter Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="Low" <?php echo ($currentFilters['priority'] ?? '') === 'Low' ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo ($currentFilters['priority'] ?? '') === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo ($currentFilters['priority'] ?? '') === 'High' ? 'selected' : ''; ?>>High</option>
                        <option value="Urgent" <?php echo ($currentFilters['priority'] ?? '') === 'Urgent' ? 'selected' : ''; ?>>Urgent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Filter Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($currentFilters['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Submit Buttons -->
                <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                    <a href="/Task-Tracker/public/reports" class="btn btn-outline-secondary px-4">Reset</a>
                    <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                        <i class="bi bi-play-fill me-1"></i> Run Aggregated Report
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-stack"></i>
            </div>
            <div>
                <div class="small fw-semibold text-muted">Total Aggregated Records</div>
                <div class="fs-4 fw-bold text-dark"><?php echo number_format($summary['total_records'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <?php if ($currentEntity === 'tasks'): ?>
    <div class="col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <div class="small fw-semibold text-muted">Overall Completion Rate</div>
                <div class="fs-4 fw-bold text-dark"><?php echo $summary['completion_rate'] ?? 0; ?>%</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="small fw-semibold text-muted">Total Time Logged</div>
                <div class="fs-4 fw-bold text-dark"><?php echo number_format($summary['total_hours'] ?? 0, 1); ?> hrs</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-check2-all"></i>
            </div>
            <div>
                <div class="small fw-semibold text-muted">Completed Tasks Count</div>
                <div class="fs-4 fw-bold text-dark"><?php echo number_format($summary['completed_count'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    <?php elseif ($currentEntity === 'time'): ?>
    <div class="col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-clock-fill"></i>
            </div>
            <div>
                <div class="small fw-semibold text-muted">Total Duration</div>
                <div class="fs-4 fw-bold text-dark"><?php echo number_format($summary['total_hours'] ?? 0, 2); ?> hrs</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon bg-secondary bg-opacity-10 text-secondary">
                <i class="bi bi-calculator"></i>
            </div>
            <div>
                <div class="small fw-semibold text-muted">Avg Hours / Entry</div>
                <div class="fs-4 fw-bold text-dark"><?php echo number_format($summary['avg_hours'] ?? 0, 2); ?> hrs</div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <div class="small fw-semibold text-muted">Total Amount Spoken</div>
                <div class="fs-4 fw-bold text-dark">$<?php echo number_format($summary['total_amount'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card d-flex align-items-center gap-3">
            <div class="kpi-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-bar-chart"></i>
            </div>
            <div>
                <div class="small fw-semibold text-muted">Average Expense</div>
                <div class="fs-4 fw-bold text-dark">$<?php echo number_format($summary['avg_amount'] ?? 0, 2); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Chart & Data Table Grid -->
<div class="row g-4 mb-4">
    <!-- Visualization Chart -->
    <div class="col-lg-6">
        <div class="card rpt-card shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h5 class="mb-0 fw-bold"><i class="bi bi-graph-up text-primary me-2"></i>Visualization Chart</h5>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center">
                <?php if (empty($rows)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-bar-chart display-4 opacity-50 d-block mb-2"></i>
                        No data available for chart generation with current filters.
                    </div>
                <?php else: ?>
                    <div style="width:100%; height:320px;">
                        <canvas id="reportChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="col-lg-6">
        <div class="card rpt-card shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-table text-primary me-2"></i>Aggregated Data Breakdown</h5>
                <span class="badge bg-light text-dark border"><?php echo count($rows); ?> Groups</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($rows)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-4 opacity-50 d-block mb-2"></i>
                        No matching aggregate records found.
                    </div>
                <?php else: ?>
                    <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-4"><?php echo htmlspecialchars($groupLabel); ?></th>
                                    <?php if ($currentEntity === 'tasks'): ?>
                                        <th class="text-center">Tasks</th>
                                        <th class="text-center">Done</th>
                                        <th class="text-center">Overdue</th>
                                        <th class="text-center">Rate</th>
                                    <?php elseif ($currentEntity === 'time'): ?>
                                        <th class="text-center">Entries</th>
                                        <th class="text-center">Total Hours</th>
                                        <th class="text-center">Approved</th>
                                    <?php else: ?>
                                        <th class="text-center">Count</th>
                                        <th class="text-center">Total Amount</th>
                                        <th class="text-center">Avg Amount</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold text-dark"><?php echo htmlspecialchars($row['group_name']); ?></td>
                                        <?php if ($currentEntity === 'tasks'): ?>
                                            <td class="text-center"><?php echo $row['total_tasks']; ?></td>
                                            <td class="text-center text-success fw-bold"><?php echo $row['completed_tasks']; ?></td>
                                            <td class="text-center text-danger"><?php echo $row['overdue_tasks']; ?></td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center gap-1">
                                                    <span class="small fw-bold"><?php echo $row['completion_rate']; ?>%</span>
                                                    <div class="progress" style="width:50px;height:6px;">
                                                        <div class="progress-bar bg-success" style="width:<?php echo $row['completion_rate']; ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        <?php elseif ($currentEntity === 'time'): ?>
                                            <td class="text-center"><?php echo $row['total_entries']; ?></td>
                                            <td class="text-center fw-bold text-primary"><?php echo $row['total_hours']; ?>h</td>
                                            <td class="text-center text-success"><?php echo $row['approved_hours']; ?>h</td>
                                        <?php else: ?>
                                            <td class="text-center"><?php echo $row['total_expenses']; ?></td>
                                            <td class="text-center fw-bold text-warning">$<?php echo number_format($row['total_amount'], 2); ?></td>
                                            <td class="text-center">$<?php echo number_format($row['avg_amount'], 2); ?></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Saved Reports Gallery -->
<div class="card rpt-card shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 px-4">
        <h5 class="mb-0 fw-bold"><i class="bi bi-bookmark-star-fill text-warning me-2"></i>Saved Report Templates</h5>
    </div>
    <div class="card-body px-4 pb-4">
        <?php if (empty($savedReports)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-bookmarks display-4 opacity-40 d-block mb-2"></i>
                No saved report templates yet. Configure your report above and click <strong>"Save Template"</strong> for quick reuse!
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($savedReports as $sr): ?>
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($sr['title']); ?></h6>
                                    <span class="badge bg-primary text-capitalize"><?php echo $sr['entity']; ?></span>
                                </div>
                                <?php if (!empty($sr['description'])): ?>
                                    <p class="small text-muted mb-2"><?php echo htmlspecialchars($sr['description']); ?></p>
                                <?php endif; ?>
                                <div class="small text-muted">
                                    <i class="bi bi-funnel me-1"></i>Group: <strong><?php echo ucfirst($sr['group_by']); ?></strong>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3 pt-2 border-top">
                                <a href="/Task-Tracker/public/reports?load=<?php echo $sr['id']; ?>" class="btn btn-outline-primary btn-sm flex-fill fw-semibold">
                                    <i class="bi bi-play-circle me-1"></i> Run
                                </a>
                                <a href="/Task-Tracker/public/reports/delete/<?php echo $sr['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this saved report template?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Save Report Template -->
<div class="modal fade" id="saveReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-bookmark-plus text-primary me-2"></i>Save Report Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/Task-Tracker/public/reports/save" method="POST">
                <div class="modal-body py-3">
                    <input type="hidden" name="entity" value="<?php echo htmlspecialchars($currentEntity); ?>">
                    <input type="hidden" name="group_by" value="<?php echo htmlspecialchars($currentGroupBy); ?>">
                    <input type="hidden" name="chart_type" value="<?php echo htmlspecialchars($chartType); ?>">
                    <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($currentFilters['start_date'] ?? ''); ?>">
                    <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($currentFilters['end_date'] ?? ''); ?>">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($currentFilters['status'] ?? ''); ?>">
                    <input type="hidden" name="priority" value="<?php echo htmlspecialchars($currentFilters['priority'] ?? ''); ?>">
                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($currentFilters['category_id'] ?? ''); ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Template Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder='e.g. "Monthly Task Summary by Category"' required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Describe the purpose of this report template..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function onEntityChange(entity) {
    const groupSelect = document.getElementById('groupBySelect');
    groupSelect.innerHTML = '';

    if (entity === 'tasks') {
        groupSelect.innerHTML = `
            <option value="category">Category</option>
            <option value="status">Status</option>
            <option value="priority">Priority</option>
            <option value="assigned_to">Assigned User</option>
            <option value="month">Creation Month</option>
            <option value="due_month">Due Month</option>
        `;
    } else if (entity === 'time') {
        groupSelect.innerHTML = `
            <option value="category">Task Category</option>
            <option value="user">User</option>
            <option value="month">Clocked Month</option>
            <option value="status">Approval Status</option>
        `;
    } else {
        groupSelect.innerHTML = `
            <option value="category">Category</option>
            <option value="user">User</option>
            <option value="month">Expense Month</option>
            <option value="status">Approval Status</option>
        `;
    }
}

// Render Chart.js Visualization
document.addEventListener("DOMContentLoaded", function() {
    const chartCanvas = document.getElementById('reportChart');
    if (!chartCanvas) return;

    const rows = <?php echo json_encode($rows); ?>;
    const entity = "<?php echo $currentEntity; ?>";
    const chartType = "<?php echo $chartType; ?>";

    const labels = rows.map(r => r.group_name);
    let dataValues = [];
    let datasetLabel = '';

    if (entity === 'tasks') {
        dataValues = rows.map(r => parseInt(r.total_tasks));
        datasetLabel = 'Total Tasks';
    } else if (entity === 'time') {
        dataValues = rows.map(r => parseFloat(r.total_hours));
        datasetLabel = 'Total Hours';
    } else {
        dataValues = rows.map(r => parseFloat(r.total_amount));
        datasetLabel = 'Total Amount ($)';
    }

    const palette = [
        '#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#6f42c1', 
        '#fd7e14', '#d63384', '#20c997', '#6c757d', '#667eea'
    ];

    new Chart(chartCanvas, {
        type: chartType === 'doughnut' ? 'doughnut' : (chartType === 'line' ? 'line' : 'bar'),
        data: {
            labels: labels,
            datasets: [{
                label: datasetLabel,
                data: dataValues,
                backgroundColor: chartType === 'line' ? 'rgba(13, 110, 253, 0.15)' : palette,
                borderColor: chartType === 'line' ? '#0d6efd' : '#fff',
                borderWidth: chartType === 'line' ? 3 : 1,
                fill: chartType === 'line',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: chartType === 'doughnut' }
            },
            scales: chartType === 'doughnut' ? {} : {
                y: { beginAtZero: true, grid: { borderDash: [4, 4] } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/main.php'; ?>

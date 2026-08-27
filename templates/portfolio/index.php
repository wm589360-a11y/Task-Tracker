<!-- Portfolio Management Dashboard -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h2 class="h3 mb-0 fw-bold"><i class="bi bi-briefcase me-2 text-primary"></i>Portfolio Management</h2>
            <span id="liveSyncBadge" class="badge bg-success-subtle text-success border border-success d-inline-flex align-items-center gap-1">
                <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 0.5rem; height: 0.5rem;"></span>
                Live Sync Active
            </span>
        </div>
        <p class="text-muted small mb-0">Track multi-project health, budgets, burn rates, and real-time task progress</p>
    </div>
    <div>
        <button class="btn btn-primary shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#createProjectModal">
            <i class="bi bi-plus-circle me-1"></i> Create New Project
        </button>
    </div>
</div>

<!-- Portfolio Executive Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card portfolio-stat-card primary shadow-sm h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Projects</span>
                    <h3 class="mb-0 mt-1 fw-bold text-primary" id="metricTotalProjects">
                        <?php echo $summary['total_projects'] ?? 0; ?>
                    </h3>
                    <small class="text-muted">
                        <span id="metricActiveProjects"><?php echo $summary['active_projects'] ?? 0; ?></span> Active
                    </small>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                    <i class="bi bi-diagram-3"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-lg-3">
        <div class="card portfolio-stat-card success shadow-sm h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Portfolio Budget</span>
                    <h3 class="mb-0 mt-1 fw-bold text-success" id="metricTotalBudget">
                        $<?php echo number_format($summary['total_budget'] ?? 0, 2); ?>
                    </h3>
                    <small class="text-muted">
                        Spent: <span id="metricTotalSpent" class="fw-semibold">$<?php echo number_format($summary['total_spent'] ?? 0, 2); ?></span>
                    </small>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle fs-4">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card portfolio-stat-card warning shadow-sm h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Portfolio Health</span>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge bg-success" title="On Track">
                            <i class="bi bi-check-circle me-1"></i><span id="metricOnTrack"><?php echo $summary['on_track_health'] ?? 0; ?></span>
                        </span>
                        <span class="badge bg-warning text-dark" title="At Risk">
                            <i class="bi bi-exclamation-triangle me-1"></i><span id="metricAtRisk"><?php echo $summary['at_risk_health'] ?? 0; ?></span>
                        </span>
                        <span class="badge bg-danger" title="Off Track">
                            <i class="bi bi-x-circle me-1"></i><span id="metricOffTrack"><?php echo $summary['off_track_health'] ?? 0; ?></span>
                        </span>
                    </div>
                    <small class="text-muted mt-1 d-block">Live Health Breakdown</small>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle fs-4">
                    <i class="bi bi-heart-pulse"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card portfolio-stat-card info shadow-sm h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Task Completion</span>
                    <h3 class="mb-0 mt-1 fw-bold text-info" id="metricOverallTaskComp">
                        <?php echo $summary['overall_progress'] ?? 0; ?>%
                    </h3>
                    <small class="text-muted">
                        Burn Rate: <span id="metricOverallBurn" class="fw-semibold"><?php echo $summary['overall_burn'] ?? 0; ?>%</span>
                    </small>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-circle fs-4">
                    <i class="bi bi-pie-chart"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Search Toolbar -->
<div class="card shadow-sm mb-4 border-0">
    <div class="card-body p-3">
        <form method="GET" action="/Task-Tracker/public/portfolio" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search project name, code or description..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Active" <?php echo ($filters['status'] ?? '') === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Planning" <?php echo ($filters['status'] ?? '') === 'Planning' ? 'selected' : ''; ?>>Planning</option>
                    <option value="On Hold" <?php echo ($filters['status'] ?? '') === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                    <option value="Completed" <?php echo ($filters['status'] ?? '') === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select name="health" class="form-select" onchange="this.form.submit()">
                    <option value="">All Health Indicators</option>
                    <option value="On Track" <?php echo ($filters['health'] ?? '') === 'On Track' ? 'selected' : ''; ?>>On Track</option>
                    <option value="At Risk" <?php echo ($filters['health'] ?? '') === 'At Risk' ? 'selected' : ''; ?>>At Risk</option>
                    <option value="Off Track" <?php echo ($filters['health'] ?? '') === 'Off Track' ? 'selected' : ''; ?>>Off Track</option>
                    <option value="On Hold" <?php echo ($filters['health'] ?? '') === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                </select>
            </div>

            <div class="col-12 col-md-2 text-end">
                <a href="/Task-Tracker/public/portfolio" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Projects Grid -->
<?php if(empty($projects)): ?>
    <div class="card shadow-sm p-5 text-center my-4">
        <i class="bi bi-briefcase text-muted fs-1 mb-2"></i>
        <h5 class="fw-bold">No Projects Found</h5>
        <p class="text-muted small">No project match your search filters. Create a new project to start tracking your portfolio.</p>
        <div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                <i class="bi bi-plus-circle me-1"></i> Create Project
            </button>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach($projects as $prj): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="project-card h-100 p-4 d-flex flex-column justify-content-between shadow-sm">
                    <div>
                        <!-- Header & Badges -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-light text-dark border me-1 fw-bold"><?php echo htmlspecialchars($prj['code']); ?></span>
                                <?php
                                    $statusClass = 'bg-info text-dark';
                                    if ($prj['status'] === 'Completed') $statusClass = 'bg-success';
                                    if ($prj['status'] === 'Planning') $statusClass = 'bg-secondary';
                                    if ($prj['status'] === 'On Hold') $statusClass = 'bg-warning text-dark';
                                ?>
                                <span id="prj-status-badge-<?php echo $prj['id']; ?>" class="badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($prj['status']); ?>
                                </span>
                            </div>

                            <!-- Quick Health Dropdown -->
                            <div class="dropdown">
                                <?php
                                    $healthClass = 'bg-success';
                                    if ($prj['health'] === 'At Risk') $healthClass = 'bg-warning text-dark';
                                    if ($prj['health'] === 'Off Track') $healthClass = 'bg-danger';
                                    if ($prj['health'] === 'On Hold') $healthClass = 'bg-secondary';
                                ?>
                                <button id="prj-health-badge-<?php echo $prj['id']; ?>" 
                                        class="badge <?php echo $healthClass; ?> dropdown-toggle border-0 cursor-pointer" 
                                        type="button" 
                                        data-bs-toggle="dropdown">
                                    <?php echo htmlspecialchars($prj['health']); ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Quick Change Health</h6></li>
                                    <li><a class="dropdown-item text-success" href="#" onclick="quickUpdateHealth(<?php echo $prj['id']; ?>, 'On Track'); return false;"><i class="bi bi-check-circle me-1"></i> On Track</a></li>
                                    <li><a class="dropdown-item text-warning" href="#" onclick="quickUpdateHealth(<?php echo $prj['id']; ?>, 'At Risk'); return false;"><i class="bi bi-exclamation-triangle me-1"></i> At Risk</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="quickUpdateHealth(<?php echo $prj['id']; ?>, 'Off Track'); return false;"><i class="bi bi-x-circle me-1"></i> Off Track</a></li>
                                    <li><a class="dropdown-item text-secondary" href="#" onclick="quickUpdateHealth(<?php echo $prj['id']; ?>, 'On Hold'); return false;"><i class="bi bi-pause-circle me-1"></i> On Hold</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Project Title & Description -->
                        <h5 class="fw-bold mb-1">
                            <a href="/Task-Tracker/public/portfolio/view/<?php echo $prj['id']; ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($prj['name']); ?>
                            </a>
                        </h5>
                        <p class="text-muted small mb-3 text-truncate-2" style="min-height: 2.6rem;">
                            <?php echo htmlspecialchars($prj['description'] ?? 'No description provided.'); ?>
                        </p>

                        <!-- Task Completion Progress Bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted fw-semibold"><i class="bi bi-check-square me-1"></i>Task Progress</span>
                                <span id="prj-progress-text-<?php echo $prj['id']; ?>" class="small fw-bold">
                                    <?php echo $prj['progress']; ?>%
                                </span>
                            </div>
                            <div class="progress portfolio-progress-bar">
                                <div id="prj-progress-bar-<?php echo $prj['id']; ?>" 
                                     class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                                     role="progressbar" 
                                     style="width: <?php echo $prj['progress']; ?>%;"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mt-1">
                                <span><?php echo $prj['completed_tasks']; ?> / <?php echo $prj['total_tasks']; ?> Tasks Completed</span>
                                <?php if($prj['overdue_tasks'] > 0): ?>
                                    <span class="text-danger fw-bold"><i class="bi bi-clock-history"></i> <?php echo $prj['overdue_tasks']; ?> Overdue</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Budget Burn Rate -->
                        <div class="mb-3 bg-light p-2.5 rounded">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted fw-semibold"><i class="bi bi-wallet2 me-1"></i>Budget Burn Rate</span>
                                <span id="prj-budget-burn-<?php echo $prj['id']; ?>" class="small fw-bold text-secondary">
                                    <?php echo $prj['budget_burn']; ?>%
                                </span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <?php
                                    $burnClass = 'bg-info';
                                    if ($prj['budget_burn'] > 75) $burnClass = 'bg-warning';
                                    if ($prj['budget_burn'] > 95) $burnClass = 'bg-danger';
                                ?>
                                <div id="prj-budget-bar-<?php echo $prj['id']; ?>" 
                                     class="progress-bar <?php echo $burnClass; ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo min($prj['budget_burn'], 100); ?>%;"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mt-1">
                                <span>Spent: $<?php echo number_format($prj['spent'], 2); ?></span>
                                <span>Budget: $<?php echo number_format($prj['budget'], 2); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer & Actions -->
                    <div class="border-top pt-3 mt-2 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-person-circle text-secondary"></i>
                            <span class="small text-muted fw-semibold">
                                <?php echo htmlspecialchars($prj['owner_name'] ?? 'Unassigned'); ?>
                            </span>
                        </div>

                        <div class="d-flex gap-1">
                            <a href="/Task-Tracker/public/portfolio/view/<?php echo $prj['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Project Breakdown">
                                <i class="bi bi-eye"></i> Details
                            </a>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editProjectModal<?php echo $prj['id']; ?>" title="Edit Project">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="/Task-Tracker/public/portfolio/delete/<?php echo $prj['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this project?')" title="Delete Project">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Project Modal for this Project -->
            <div class="modal fade" id="editProjectModal<?php echo $prj['id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="POST" action="/Task-Tracker/public/portfolio/edit/<?php echo $prj['id']; ?>">
                            <div class="modal-header bg-light">
                                <h5 class="modal-header-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Project: <?php echo htmlspecialchars($prj['name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Project Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($prj['name']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Project Code <span class="text-danger">*</span></label>
                                        <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($prj['code']); ?>" required>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($prj['description']); ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="Planning" <?php echo $prj['status'] === 'Planning' ? 'selected' : ''; ?>>Planning</option>
                                            <option value="Active" <?php echo $prj['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="On Hold" <?php echo $prj['status'] === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                                            <option value="Completed" <?php echo $prj['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Archived" <?php echo $prj['status'] === 'Archived' ? 'selected' : ''; ?>>Archived</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Health Status</label>
                                        <select name="health" class="form-select">
                                            <option value="On Track" <?php echo $prj['health'] === 'On Track' ? 'selected' : ''; ?>>On Track</option>
                                            <option value="At Risk" <?php echo $prj['health'] === 'At Risk' ? 'selected' : ''; ?>>At Risk</option>
                                            <option value="Off Track" <?php echo $prj['health'] === 'Off Track' ? 'selected' : ''; ?>>Off Track</option>
                                            <option value="On Hold" <?php echo $prj['health'] === 'On Hold' ? 'selected' : ''; ?>>On Hold</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Project Lead / Owner</label>
                                        <select name="owner_id" class="form-select">
                                            <option value="">Unassigned</option>
                                            <?php foreach($users as $u): ?>
                                                <option value="<?php echo $u['id']; ?>" <?php echo $prj['owner_id'] == $u['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($u['name']); ?> (<?php echo htmlspecialchars($u['email']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Category</label>
                                        <select name="category_id" class="form-select">
                                            <option value="">None</option>
                                            <?php foreach($categories as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" <?php echo $prj['category_id'] == $c['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($c['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Total Budget ($)</label>
                                        <input type="number" step="0.01" name="budget" class="form-control" value="<?php echo $prj['budget']; ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Actual Spent ($)</label>
                                        <input type="number" step="0.01" name="spent" class="form-control" value="<?php echo $prj['spent']; ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="<?php echo $prj['start_date']; ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="<?php echo $prj['end_date']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Create Project Modal -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/Task-Tracker/public/portfolio/create">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-header-title fw-bold"><i class="bi bi-briefcase me-2"></i>Create New Project</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Website Redesign v3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Project Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. PRJ-200" value="PRJ-<?php echo rand(100, 999); ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Overview of project objectives, scope, and key deliverables..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Initial Status</label>
                            <select name="status" class="form-select">
                                <option value="Planning">Planning</option>
                                <option value="Active" selected>Active</option>
                                <option value="On Hold">On Hold</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Health Status</label>
                            <select name="health" class="form-select">
                                <option value="On Track" selected>On Track</option>
                                <option value="At Risk">At Risk</option>
                                <option value="Off Track">Off Track</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Project Lead / Owner</label>
                            <select name="owner_id" class="form-select">
                                <option value="">Select Project Lead</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">Select Category</option>
                                <?php foreach($categories as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Budget ($)</label>
                            <input type="number" step="0.01" name="budget" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Initial Spent ($)</label>
                            <input type="number" step="0.01" name="spent" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/Task-Tracker/public/assets/js/portfolio.js"></script>

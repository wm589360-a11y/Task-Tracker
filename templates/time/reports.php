<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" action="<?= URL_ROOT ?>/time/reports" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($_GET['start_date'] ?? date('Y-m-01')); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($_GET['end_date'] ?? date('Y-m-d')); ?>">
                    </div>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <div class="col-md-3">
                        <label class="form-label">User</label>
                        <select name="user_id" class="form-select">
                            <option value="">All Users</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>" <?php echo (isset($_GET['user_id']) && $_GET['user_id'] == $u['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($u['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo (isset($_GET['status']) && $_GET['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo (isset($_GET['status']) && $_GET['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <?php
        $stats = [
            'total' => $summaryStats['total_minutes'] ?? 0,
            'approved' => $summaryStats['approved_minutes'] ?? 0,
            'pending' => $summaryStats['pending_minutes'] ?? 0,
            'rejected' => $summaryStats['rejected_minutes'] ?? 0,
        ];
        
        function formatMins($mins) {
            $h = floor($mins / 60);
            $m = $mins % 60;
            return "{$h}h {$m}m";
        }
    ?>
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="text-white-50">Total Time</h6>
                <h2><?php echo formatMins($stats['total']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="text-white-50">Approved</h6>
                <h2><?php echo formatMins($stats['approved']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="text-dark-50">Pending</h6>
                <h2><?php echo formatMins($stats['pending']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="text-white-50">Rejected</h6>
                <h2><?php echo formatMins($stats['rejected']); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Detailed Report</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <th>User</th>
                                <?php endif; ?>
                                <th>Task</th>
                                <th>Notes</th>
                                <th>Time</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportData)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No time entries found for the selected filters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($row['clock_in'])); ?></td>
                                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <?php if ($row['task_title']): ?>
                                                <a href="<?= URL_ROOT ?>/tasks/view/<?php echo $row['task_id']; ?>">
                                                    <?php echo htmlspecialchars($row['task_title']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="small"><?php echo htmlspecialchars($row['notes']); ?></span></td>
                                        <td>
                                            <?php echo date('h:i A', strtotime($row['clock_in'])); ?> - 
                                            <?php echo date('h:i A', strtotime($row['clock_out'])); ?>
                                        </td>
                                        <td><strong><?php echo formatMins($row['duration_minutes']); ?></strong></td>
                                        <td>
                                            <?php if ($row['status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif ($row['status'] == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

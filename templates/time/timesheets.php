<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-ui-checks"></i> Pending Timesheets</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Date</th>
                                <th>Time (In/Out)</th>
                                <th>Duration</th>
                                <th>Task & Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingEntries)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-check-circle display-4 text-success d-block mb-2"></i>
                                        All caught up! No pending timesheets.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingEntries as $entry): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($entry['user_name']); ?></strong>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($entry['clock_in'])); ?></td>
                                        <td>
                                            <?php echo date('h:i A', strtotime($entry['clock_in'])); ?> - 
                                            <?php echo date('h:i A', strtotime($entry['clock_out'])); ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $hours = floor($entry['duration_minutes'] / 60);
                                                $mins = $entry['duration_minutes'] % 60;
                                                echo "<strong>{$hours}h {$mins}m</strong>";
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($entry['task_title']): ?>
                                                <a href="/Task-Tracker/public/tasks/view/<?php echo $entry['task_id']; ?>" class="d-block mb-1">
                                                    <?php echo htmlspecialchars($entry['task_title']); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($entry['notes']): ?>
                                                <span class="small text-muted"><i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($entry['notes']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/Task-Tracker/public/time/approve/<?php echo $entry['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </a>
                                            <a href="/Task-Tracker/public/time/reject/<?php echo $entry['id']; ?>" class="btn btn-sm btn-danger ms-1">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </a>
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

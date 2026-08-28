<div class="row">
    <!-- Active Session / Punch Clock -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Time Clock</h5>
            </div>
            <div class="card-body text-center">
                <?php if ($activePunch): ?>
                    <h4 class="text-success mb-3">Currently Clocked In</h4>
                    <p class="mb-2"><strong>Started:</strong> <?php echo date('M d, Y h:i A', strtotime($activePunch['clock_in'])); ?></p>
                    <?php if ($activePunch['task_title']): ?>
                        <p class="mb-3"><strong>Task:</strong> <?php echo htmlspecialchars($activePunch['task_title']); ?></p>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= URL_ROOT ?>/time/punch">
                        <input type="hidden" name="action" value="out">
                        <input type="hidden" name="entry_id" value="<?php echo $activePunch['id']; ?>">
                        <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold">
                            <i class="bi bi-stop-circle"></i> Punch Out
                        </button>
                    </form>
                <?php else: ?>
                    <h4 class="text-secondary mb-3">Not Clocked In</h4>
                    <form method="POST" action="<?= URL_ROOT ?>/time/punch">
                        <input type="hidden" name="action" value="in">
                        
                        <div class="mb-3 text-start">
                            <label class="form-label">Select Task (Optional)</label>
                            <select name="task_id" class="form-select">
                                <option value="">-- No Task --</option>
                                <?php if (isset($tasks)): foreach ($tasks as $task): ?>
                                    <option value="<?php echo $task['id']; ?>">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3 text-start">
                            <label class="form-label">Notes (Optional)</label>
                            <input type="text" name="notes" class="form-control" placeholder="What are you working on?">
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">
                            <i class="bi bi-play-circle"></i> Punch In
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Time Entries -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Time Entries</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time (In/Out)</th>
                                <th>Duration</th>
                                <th>Task</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentEntries)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">No recent time entries found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentEntries as $entry): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($entry['clock_in'])); ?></td>
                                        <td>
                                            <?php echo date('h:i A', strtotime($entry['clock_in'])); ?> - 
                                            <?php echo $entry['clock_out'] ? date('h:i A', strtotime($entry['clock_out'])) : '<span class="text-success">Active</span>'; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if ($entry['clock_out']) {
                                                    $hours = floor($entry['duration_minutes'] / 60);
                                                    $mins = $entry['duration_minutes'] % 60;
                                                    echo "{$hours}h {$mins}m";
                                                } else {
                                                    echo "-";
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($entry['task_title']): ?>
                                                <a href="<?= URL_ROOT ?>/tasks/view/<?php echo $entry['task_id']; ?>">
                                                    <?php echo htmlspecialchars($entry['task_title']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                            <?php if ($entry['notes']): ?>
                                                <div class="small text-muted"><?php echo htmlspecialchars($entry['notes']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($entry['status'] == 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif ($entry['status'] == 'approved'): ?>
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

<?php
$pageTitle = 'Expense Approvals';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-shield-check text-primary"></i> Expense Approvals</h2>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Receipt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendingExpenses)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No pending expenses to approve.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pendingExpenses as $expense): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($expense['expense_date'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($expense['user_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($expense['user_email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($expense['description']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($expense['category']); ?></span></td>
                                <td class="fw-bold">$<?php echo number_format($expense['amount'], 2); ?></td>
                                <td>
                                    <?php if ($expense['receipt_path']): ?>
                                        <a href="<?= URL_ROOT ?><?php echo $expense['receipt_path']; ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-file-earmark-text"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted"><small>None</small></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="<?= URL_ROOT ?>/expenses/approve/<?php echo $expense['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="<?= URL_ROOT ?>/expenses/reject/<?php echo $expense['id']; ?>" onsubmit="return confirm('Are you sure you want to reject this expense?');">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/templates/layouts/main.php';
?>

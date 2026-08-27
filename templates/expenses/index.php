<?php
$pageTitle = 'My Expenses';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-wallet2 text-primary"></i> My Expenses</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        <i class="bi bi-plus-lg"></i> Add Expense
    </button>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Receipt</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No expenses found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($expense['expense_date'])); ?></td>
                                <td><?php echo htmlspecialchars($expense['description']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($expense['category']); ?></span></td>
                                <td class="fw-bold">$<?php echo number_format($expense['amount'], 2); ?></td>
                                <td>
                                    <?php if ($expense['receipt_path']): ?>
                                        <a href="/Task-Tracker/public<?php echo $expense['receipt_path']; ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-file-earmark-text"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted"><small>None</small></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $statusClass = 'bg-warning text-dark';
                                        if ($expense['status'] === 'approved') $statusClass = 'bg-success';
                                        if ($expense['status'] === 'rejected') $statusClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($expense['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($expense['status'] === 'pending'): ?>
                                        <form method="POST" action="/Task-Tracker/public/expenses/delete/<?php echo $expense['id']; ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted"><small>Locked</small></span>
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

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/Task-Tracker/public/expenses/create" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addExpenseModalLabel">Add New Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="expense_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required placeholder="e.g. Client Lunch">
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="Travel">Travel</option>
                            <option value="Meals">Meals</option>
                            <option value="Office Supplies">Office Supplies</option>
                            <option value="Software">Software</option>
                            <option value="Other" selected>Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount ($)</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required min="0.01" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label for="receipt" class="form-label">Receipt (Optional)</label>
                        <input type="file" class="form-control" id="receipt" name="receipt" accept="image/*,.pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/templates/layouts/main.php';
?>

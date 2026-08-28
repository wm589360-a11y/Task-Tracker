<?php
$pageTitle = 'Expense Reports';
ob_start();

$totalAmount = 0;
foreach ($reportData as $data) {
    $totalAmount += $data['total_amount'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pie-chart text-primary"></i> Expense Reports</h2>
    
    <form class="d-flex gap-2 align-items-end" method="GET" action="<?= URL_ROOT ?>/expenses/reports">
        <div>
            <label class="form-label mb-0"><small>Start Date</small></label>
            <input type="date" class="form-control form-control-sm" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
        </div>
        <div>
            <label class="form-label mb-0"><small>End Date</small></label>
            <input type="date" class="form-control form-control-sm" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
    </form>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-primary text-white h-100">
            <div class="card-body">
                <h6 class="text-uppercase mb-2 text-white-50">Total Approved Expenses</h6>
                <h2 class="mb-0">$<?php echo number_format($totalAmount, 2); ?></h2>
                <small class="text-white-50">For selected period</small>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Expenses by Category</h5>
    </div>
    <div class="card-body">
        <?php if (empty($reportData)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                No approved expenses found for the selected period.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Count</th>
                            <th>Total Amount</th>
                            <th>% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $row): 
                            $percentage = $totalAmount > 0 ? ($row['total_amount'] / $totalAmount) * 100 : 0;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><?php echo $row['expense_count']; ?></td>
                                <td class="fw-bold">$<?php echo number_format($row['total_amount'], 2); ?></td>
                                <td style="width: 30%">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <small><?php echo number_format($percentage, 1); ?>%</small>
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

<?php
$content = ob_get_clean();
require BASE_PATH . '/templates/layouts/main.php';
?>

<?php
$pageTitle = 'Analytics - Advanced Task Tracker';
ob_start();

$monthLabels    = json_encode(array_column($monthlyData, 'month'));
$monthTotal     = json_encode(array_column($monthlyData, 'total'));
$monthCompleted = json_encode(array_column($monthlyData, 'completed'));
$catLabels      = json_encode(array_column($categoryData, 'category'));
$catCounts      = json_encode(array_column($categoryData, 'count'));

$total      = $stats['total_tasks'] ?? 0;
$completed  = $stats['completed_tasks'] ?? 0;
$pending    = $stats['pending_tasks'] ?? 0;
$inProgress = $stats['in_progress_tasks'] ?? 0;
$overdue    = $stats['overdue_tasks'] ?? 0;
$rate       = $stats['completion_rate'] ?? 0;
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0"><i class="bi bi-bar-chart-line text-primary me-2"></i>Analytics Dashboard</h2>
    <a href="/Task-Tracker/public/tasks" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-list-check me-1"></i>View Tasks
    </a>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['label' => 'Total Tasks',    'value' => $total,      'icon' => 'bi-list-task',         'gradient' => 'linear-gradient(135deg,#667eea,#764ba2)'],
        ['label' => 'Completed',      'value' => $completed,  'icon' => 'bi-check-circle-fill',  'gradient' => 'linear-gradient(135deg,#11998e,#38ef7d)'],
        ['label' => 'In Progress',    'value' => $inProgress, 'icon' => 'bi-play-circle-fill',   'gradient' => 'linear-gradient(135deg,#4776e6,#8e54e9)'],
        ['label' => 'Overdue',        'value' => $overdue,    'icon' => 'bi-exclamation-triangle-fill', 'gradient' => 'linear-gradient(135deg,#ff4e50,#f9d423)'],
    ];
    foreach($cards as $c): ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 text-white h-100 shadow-sm" style="background:<?php echo $c['gradient']; ?>;border-radius:16px;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="small text-white-50 mb-1"><?php echo $c['label']; ?></div>
                        <div class="fw-bold" style="font-size:1.8rem;"><?php echo $c['value']; ?></div>
                    </div>
                    <i class="bi <?php echo $c['icon']; ?>" style="font-size:1.8rem;opacity:.4;"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <!-- Monthly Chart -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2 text-primary"></i>Tasks Created vs Completed (Last 6 Months)</h6>
                <canvas id="monthlyChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <!-- Doughnut Chart -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2 text-success"></i>Tasks by Category</h6>
                <canvas id="categoryChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Completion Rate -->
<div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="fw-bold mb-3"><i class="bi bi-trophy text-warning me-2"></i>Overall Completion Rate</h6>
                <div class="progress" style="height:24px;border-radius:12px;">
                    <div class="progress-bar bg-success" role="progressbar"
                         style="width:<?php echo $rate; ?>%;border-radius:12px;font-size:.9rem;"
                         aria-valuenow="<?php echo $rate; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo $rate; ?>%
                    </div>
                </div>
                <p class="text-muted small mt-2 mb-0"><?php echo $completed; ?> of <?php echo $total; ?> tasks completed</p>
            </div>
            <div class="col-md-4 text-center">
                <!-- SVG Circular Ring -->
                <?php $dash = round($rate * 2.51); ?>
                <svg width="120" height="120" viewBox="0 0 36 36">
                    <path d="M18 2.0845a15.9155 15.9155 0 0 1 0 31.831a15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none" stroke="#e9ecef" stroke-width="3.8"/>
                    <path d="M18 2.0845a15.9155 15.9155 0 0 1 0 31.831a15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none" stroke="url(#grad)" stroke-width="3.8"
                          stroke-dasharray="<?php echo $dash; ?>, 251" stroke-linecap="round"/>
                    <defs>
                        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#11998e"/>
                            <stop offset="100%" stop-color="#38ef7d"/>
                        </linearGradient>
                    </defs>
                    <text x="18" y="20.35" text-anchor="middle" font-size="8" font-weight="bold" fill="#333"><?php echo $rate; ?>%</text>
                </svg>
                <p class="text-muted small">Productivity Score</p>
            </div>
        </div>
    </div>
</div>

<script>
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: <?php echo $monthLabels; ?>,
        datasets: [
            {
                label: 'Total Created',
                data: <?php echo $monthTotal; ?>,
                backgroundColor: 'rgba(102,126,234,0.5)',
                borderColor: '#667eea',
                borderWidth: 2,
                borderRadius: 6
            },
            {
                label: 'Completed',
                data: <?php echo $monthCompleted; ?>,
                backgroundColor: 'rgba(17,153,142,0.5)',
                borderColor: '#11998e',
                borderWidth: 2,
                borderRadius: 6
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

const catCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(catCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo $catLabels; ?>,
        datasets: [{
            data: <?php echo $catCounts; ?>,
            backgroundColor: ['#667eea','#11998e','#f9d423','#ff4e50','#764ba2','#4776e6','#38ef7d','#f093fb'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        },
        cutout: '60%'
    }
});
</script>

<?php $content = ob_get_clean(); require_once __DIR__ . '/layouts/main.php'; ?>

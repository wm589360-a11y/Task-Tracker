<?php
$pageTitle = 'Calendar - Advanced Task Tracker';
ob_start();

// Build calendar data
$daysInMonth = (int)date('t', mktime(0,0,0,$month,1,$year));
$firstDay    = (int)date('w', mktime(0,0,0,$month,1,$year)); // 0=Sun
$today       = date('Y-m-d');

// Group tasks by day
$tasksByDay = [];
foreach($tasks as $t) {
    $day = (int)date('j', strtotime($t['due_date']));
    $tasksByDay[$day][] = $t;
}

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1)  { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1;  $nextYear++; }

$monthName = date('F Y', mktime(0,0,0,$month,1,$year));

$priorityColors = [
    'Urgent' => 'bg-danger',
    'High'   => 'bg-warning text-dark',
    'Medium' => 'bg-primary',
    'Low'    => 'bg-secondary',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="h3 mb-0"><i class="bi bi-calendar3 text-primary me-2"></i><?php echo $monthName; ?></h2>
    <div class="d-flex gap-2">
        <a href="<?= URL_ROOT ?>/calendar?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>"
           class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></a>
        <a href="<?= URL_ROOT ?>/calendar" class="btn btn-outline-primary btn-sm">Today</a>
        <a href="<?= URL_ROOT ?>/calendar?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>"
           class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></a>
        <a href="<?= URL_ROOT ?>/tasks/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>New Task
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
    <div class="card-body p-0">
        <!-- Day headers -->
        <div class="row g-0" style="background:linear-gradient(135deg,#667eea,#764ba2);">
            <?php foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d): ?>
            <div class="col text-white text-center py-2 fw-semibold small" style="border-right:1px solid rgba(255,255,255,.15);">
                <?php echo $d; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Calendar grid -->
        <?php
        $cell = 0;
        $row  = 0;
        echo '<div class="row g-0" style="border-top:1px solid #e9ecef;">';
        // Leading blank cells
        for($b = 0; $b < $firstDay; $b++) {
            echo '<div class="col border-bottom border-end p-2" style="min-height:100px;background:#fafafa;"></div>';
            $cell++;
        }
        for($d = 1; $d <= $daysInMonth; $d++) {
            if ($cell % 7 === 0 && $cell > 0) {
                echo '</div><div class="row g-0">';
            }
            $dateStr   = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $isToday   = ($dateStr === $today);
            $isPast    = ($dateStr < $today);
            $dayTasks  = $tasksByDay[$d] ?? [];

            $bg = $isToday ? 'background:#e8f0fe;' : ($isPast ? 'background:#fafafa;' : '');
            echo '<div class="col border-bottom border-end p-2" style="min-height:100px;' . $bg . '">';
            echo '<div class="d-flex justify-content-between mb-1">';
            echo '<span class="fw-bold ' . ($isToday ? 'badge bg-primary rounded-circle' : 'text-muted small') . '">' . $d . '</span>';
            if (!empty($dayTasks)) echo '<span class="badge bg-secondary rounded-pill" style="font-size:.6rem;">' . count($dayTasks) . '</span>';
            echo '</div>';
            foreach($dayTasks as $t) {
                $pc = $priorityColors[$t['priority']] ?? 'bg-secondary';
                echo '<a href="<?= URL_ROOT ?>/tasks/view/' . $t['id'] . '" class="badge ' . $pc . ' d-block text-truncate mb-1 text-decoration-none" style="font-size:.65rem;max-width:100%;" title="' . htmlspecialchars($t['title']) . '">' . htmlspecialchars($t['title']) . '</a>';
            }
            echo '</div>';
            $cell++;
        }
        // Trailing blank cells
        $remaining = 7 - ($cell % 7);
        if ($remaining < 7) {
            for($r = 0; $r < $remaining; $r++) {
                echo '<div class="col border-bottom border-end p-2" style="min-height:100px;background:#fafafa;"></div>';
            }
        }
        echo '</div>';
        ?>
    </div>
</div>

<!-- Task list for month -->
<?php if (!empty($tasks)): ?>
<div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-list-check me-2 text-primary"></i>
            Tasks Due in <?php echo $monthName; ?> (<?php echo count($tasks); ?>)
        </h6>
        <div class="list-group list-group-flush">
            <?php foreach($tasks as $t):
                $pc = $priorityColors[$t['priority']] ?? 'bg-secondary';
                $done = $t['status'] === 'Completed';
            ?>
            <a href="<?= URL_ROOT ?>/tasks/view/<?php echo $t['id']; ?>"
               class="list-group-item list-group-item-action border-0 py-2 px-0 d-flex align-items-center gap-3 <?php echo $done ? 'opacity-50' : ''; ?>">
                <i class="bi <?php echo $done ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted'; ?>"></i>
                <span class="<?php echo $done ? 'text-decoration-line-through' : ''; ?> flex-fill">
                    <?php echo htmlspecialchars($t['title']); ?>
                </span>
                <span class="badge <?php echo $pc; ?> rounded-pill"><?php echo $t['priority']; ?></span>
                <span class="text-muted small"><?php echo date('M d', strtotime($t['due_date'])); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php else: ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-calendar-x display-3 opacity-25"></i>
    <p class="mt-3">No tasks due in <?php echo $monthName; ?>.</p>
    <a href="<?= URL_ROOT ?>/tasks/create" class="btn btn-primary">Create a Task</a>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); require_once __DIR__ . '/layouts/main.php'; ?>

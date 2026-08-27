<?php
$pageTitle = 'Gantt Chart - Advanced Task Tracker';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0"><i class="bi bi-bar-chart-steps text-primary me-2"></i>Project Gantt Chart</h2>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="gantt.change_view_mode('Day')">Day</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="gantt.change_view_mode('Week')">Week</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="gantt.change_view_mode('Month')">Month</button>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <?php if (empty($ganttTasks)): ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x display-1 text-muted opacity-50"></i>
                <h4 class="text-muted mt-3">No tasks found.</h4>
                <p class="text-muted">Create some tasks to see them on the Gantt chart.</p>
                <a href="/Task-Tracker/public/tasks/create" class="btn btn-primary mt-2">Create a Task</a>
            </div>
        <?php else: ?>
            <div class="gantt-container" style="overflow-x: auto;">
                <svg id="gantt"></svg>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Frappe Gantt CSS & JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.6.1/frappe-gantt.min.js"></script>

<style>
    .gantt-container {
        padding: 10px;
        background-color: #fff;
        border-radius: 8px;
    }
    
    .gantt-status-completed .bar { fill: #198754; }
    .gantt-status-in-progress .bar { fill: #0d6efd; }
    .gantt-status-pending .bar { fill: #6c757d; }
    
    .gantt .bar-progress { fill: rgba(0, 0, 0, 0.2); }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const rawTasks = <?php echo json_encode($ganttTasks ?? []); ?>;
        
        if (rawTasks.length === 0) return;

        var tasks = rawTasks.map(task => {
            return {
                id: task.id,
                name: task.name,
                start: task.start,
                end: task.end,
                progress: task.progress,
                dependencies: task.dependencies,
                custom_class: task.custom_class
            };
        });

        var gantt = new Gantt("#gantt", tasks, {
            view_mode: 'Week',
            date_format: 'YYYY-MM-DD',
            on_click: function (task) {
                window.location.href = '/Task-Tracker/public/tasks/view/' + task.id;
            },
            on_date_change: function(task, start, end) {
                updateTaskOnServer(task.id, start, end, task.progress);
            },
            on_progress_change: function(task, progress) {
                updateTaskOnServer(task.id, task.start, task.end, progress);
            },
            on_view_change: function(mode) {
                // Do something on view change
            }
        });
        
        window.gantt = gantt;

        function updateTaskOnServer(taskId, start, end, progress) {
            // Format dates
            let startDate = start.getFullYear() + '-' + ('0' + (start.getMonth() + 1)).slice(-2) + '-' + ('0' + start.getDate()).slice(-2);
            let endDate = end.getFullYear() + '-' + ('0' + (end.getMonth() + 1)).slice(-2) + '-' + ('0' + end.getDate()).slice(-2);
            
            const formData = new FormData();
            formData.append('task_id', taskId);
            formData.append('start', startDate);
            formData.append('end', endDate);
            formData.append('progress', progress);

            fetch('/Task-Tracker/public/api/gantt-update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error updating task: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error connecting to server.');
            });
        }
    });
</script>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/main.php'; ?>

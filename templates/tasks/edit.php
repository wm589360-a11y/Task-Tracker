<?php
$pageTitle = 'Edit Task - Advanced Task Tracker';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0">
                <i class="bi bi-pencil-square text-primary me-2"></i>Edit Task
            </h2>
            <div class="d-flex gap-2">
                <a href="/Task-Tracker/public/tasks/view/<?php echo $task['id']; ?>" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-eye"></i> View Task
                </a>
                <a href="/Task-Tracker/public/tasks" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Tasks
                </a>
            </div>
        </div>

        <!-- Status Banner -->
        <div class="alert border-0 shadow-sm mb-4 d-flex align-items-center gap-3 py-3
            <?php echo $task['status'] === 'Completed' ? 'alert-success' : ($task['status'] === 'In Progress' ? 'alert-primary' : 'alert-warning'); ?>">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <div>
                <strong>Current Status:</strong> <?php echo htmlspecialchars($task['status']); ?>
                &nbsp;·&nbsp;
                <strong>Priority:</strong> <?php echo htmlspecialchars($task['priority']); ?>
                &nbsp;·&nbsp;
                Created: <?php echo date('M d, Y', strtotime($task['created_at'])); ?>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="/Task-Tracker/public/tasks/edit/<?php echo $task['id']; ?>" method="POST">

                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title"
                               value="<?php echo htmlspecialchars($task['title']); ?>" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($task['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="row mb-4">
                        <!-- Status -->
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php foreach (['Pending', 'In Progress', 'Completed'] as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $task['status'] === $s ? 'selected' : ''; ?>>
                                        <?php echo $s; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Priority -->
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="priority" class="form-label fw-bold">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <?php foreach (['Low', 'Medium', 'High', 'Urgent'] as $p): ?>
                                    <option value="<?php echo $p; ?>" <?php echo $task['priority'] === $p ? 'selected' : ''; ?>>
                                        <?php echo $p; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-md-4">
                            <label for="category_id" class="form-label fw-bold">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">-- No Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $task['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Due Date -->
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="due_date" class="form-label fw-bold">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date"
                                   value="<?php echo htmlspecialchars($task['due_date'] ?? ''); ?>">
                        </div>

                        <!-- Due Time -->
                        <div class="col-md-6">
                            <label for="due_time" class="form-label fw-bold">Due Time</label>
                            <input type="time" class="form-control" id="due_time" name="due_time"
                                   value="<?php echo htmlspecialchars($task['due_time'] ?? ''); ?>">
                        </div>
                    </div>

                    <!-- Assign To -->
                    <div class="mb-4">
                        <label for="assigned_to" class="form-label fw-bold">Assign To</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">-- Unassigned --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $task['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Custom Fields Section -->
                    <?php if (!empty($customFields)): ?>
                    <div class="mb-4">
                        <hr class="my-4">
                        <h6 class="fw-bold text-muted mb-3">
                            <i class="bi bi-sliders me-2 text-primary"></i>Custom Fields
                        </h6>
                        <div class="row g-3">
                        <?php foreach ($customFields as $cf):
                            $cfInputId  = 'cf_' . $cf['id'];
                            $cfName     = 'custom_field_' . $cf['id'];
                            $cfSaved    = $customFieldValues[$cf['id']]['value'] ?? '';
                        ?>
                            <div class="col-md-<?php echo $cf['field_type'] === 'checkbox' ? '12' : '6'; ?>">
                                <?php if ($cf['field_type'] === 'checkbox'): ?>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>" value="1" <?php echo $cfSaved == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-semibold" for="<?php echo $cfInputId; ?>">
                                            <?php echo htmlspecialchars($cf['label']); ?>
                                            <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                        </label>
                                    </div>
                                <?php elseif ($cf['field_type'] === 'select'): ?>
                                    <label for="<?php echo $cfInputId; ?>" class="form-label fw-semibold">
                                        <?php echo htmlspecialchars($cf['label']); ?>
                                        <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                    </label>
                                    <select id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>" class="form-select" <?php echo $cf['is_required'] ? 'required' : ''; ?>>
                                        <option value="">-- Select --</option>
                                        <?php foreach (json_decode($cf['options'] ?? '[]', true) ?? [] as $opt): ?>
                                            <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo $cfSaved === $opt ? 'selected' : ''; ?>><?php echo htmlspecialchars($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($cf['field_type'] === 'number'): ?>
                                    <label for="<?php echo $cfInputId; ?>" class="form-label fw-semibold">
                                        <?php echo htmlspecialchars($cf['label']); ?>
                                        <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                    </label>
                                    <input type="number" step="any" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>"
                                           class="form-control" value="<?php echo htmlspecialchars($cfSaved); ?>"
                                           placeholder="<?php echo htmlspecialchars($cf['placeholder'] ?? ''); ?>"
                                           <?php echo $cf['min_value'] !== null ? 'min="' . $cf['min_value'] . '"' : ''; ?>
                                           <?php echo $cf['max_value'] !== null ? 'max="' . $cf['max_value'] . '"' : ''; ?>
                                           <?php echo $cf['is_required'] ? 'required' : ''; ?>>
                                <?php elseif ($cf['field_type'] === 'date'): ?>
                                    <label for="<?php echo $cfInputId; ?>" class="form-label fw-semibold">
                                        <?php echo htmlspecialchars($cf['label']); ?>
                                        <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                    </label>
                                    <input type="date" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>"
                                           class="form-control" value="<?php echo htmlspecialchars($cfSaved); ?>"
                                           <?php echo $cf['is_required'] ? 'required' : ''; ?>>
                                <?php elseif ($cf['field_type'] === 'email'): ?>
                                    <label for="<?php echo $cfInputId; ?>" class="form-label fw-semibold">
                                        <?php echo htmlspecialchars($cf['label']); ?>
                                        <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                    </label>
                                    <input type="email" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>"
                                           class="form-control" value="<?php echo htmlspecialchars($cfSaved); ?>"
                                           placeholder="<?php echo htmlspecialchars($cf['placeholder'] ?? ''); ?>"
                                           <?php echo $cf['is_required'] ? 'required' : ''; ?>>
                                <?php elseif ($cf['field_type'] === 'url'): ?>
                                    <label for="<?php echo $cfInputId; ?>" class="form-label fw-semibold">
                                        <?php echo htmlspecialchars($cf['label']); ?>
                                        <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                    </label>
                                    <input type="url" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>"
                                           class="form-control" value="<?php echo htmlspecialchars($cfSaved); ?>"
                                           placeholder="<?php echo htmlspecialchars($cf['placeholder'] ?? 'https://'); ?>"
                                           <?php echo $cf['is_required'] ? 'required' : ''; ?>>
                                <?php else: /* text */ ?>
                                    <label for="<?php echo $cfInputId; ?>" class="form-label fw-semibold">
                                        <?php echo htmlspecialchars($cf['label']); ?>
                                        <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                    </label>
                                    <input type="text" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>"
                                           class="form-control" value="<?php echo htmlspecialchars($cfSaved); ?>"
                                           placeholder="<?php echo htmlspecialchars($cf['placeholder'] ?? ''); ?>"
                                           <?php echo $cf['is_required'] ? 'required' : ''; ?>>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 mt-5">
                        <button type="submit" class="btn btn-primary btn-lg flex-fill">
                            <i class="bi bi-save me-2"></i> Save Changes
                        </button>
                        <a href="/Task-Tracker/public/tasks/view/<?php echo $task['id']; ?>" class="btn btn-outline-secondary btn-lg px-4">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-danger border-2 mt-4 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <h6 class="text-danger mb-1 fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>Danger Zone</h6>
                    <small class="text-muted">Deleting a task is permanent and cannot be undone.</small>
                </div>
                <a href="/Task-Tracker/public/tasks/delete/<?php echo $task['id']; ?>"
                   class="btn btn-outline-danger btn-sm"
                   onclick="return confirm('Are you sure you want to permanently delete this task?');">
                    <i class="bi bi-trash me-1"></i> Delete Task
                </a>
            </div>
        </div>

    </div>
</div>

<style>
.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}
.card { border-radius: 12px; }
</style>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/main.php'; ?>
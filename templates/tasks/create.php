<?php
$pageTitle = 'Create Task - Advanced Task Tracker';
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800">
                <i class="bi bi-plus-circle text-primary me-2"></i>Create New Task
            </h2>
            <a href="<?= URL_ROOT ?>/dashboard" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="<?= URL_ROOT ?>/tasks/create" method="POST">
                    <!-- Title -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">Task Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" placeholder="What needs to be done?" required autofocus>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Add more details about this task..."></textarea>
                    </div>

                    <div class="row mb-4">
                        <!-- Priority -->
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="priority" class="form-label fw-bold">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                        
                        <!-- Category -->
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-bold">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">-- No Category --</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <!-- Due Date -->
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="due_date" class="form-label fw-bold">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <!-- Due Time -->
                        <div class="col-md-6">
                            <label for="due_time" class="form-label fw-bold">Due Time</label>
                            <input type="time" class="form-control" id="due_time" name="due_time">
                        </div>
                    </div>

                    <!-- Assignment -->
                    <div class="mb-4">
                        <label for="assigned_to" class="form-label fw-bold">Assign To</label>
                        <select class="form-select" id="assigned_to" name="assigned_to">
                            <option value="">-- Unassigned --</option>
                            <?php foreach($users as $user): ?>
                                <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php echo $user['id'] == SessionHelper::get('user_id') ? 'selected' : ''; ?>>
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
                            $cfInputId = 'cf_' . $cf['id'];
                            $cfName    = 'custom_field_' . $cf['id'];
                        ?>
                            <div class="col-md-<?php echo in_array($cf['field_type'], ['checkbox']) ? '12' : '6'; ?>">
                                <?php if ($cf['field_type'] === 'checkbox'): ?>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>" value="1">
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
                                            <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($cf['field_type'] === 'number'): ?>
                                    <label for="<?php echo $cfInputId; ?>" class="form-label fw-semibold">
                                        <?php echo htmlspecialchars($cf['label']); ?>
                                        <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                    </label>
                                    <input type="number" step="any" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>"
                                           class="form-control"
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
                                           class="form-control" <?php echo $cf['is_required'] ? 'required' : ''; ?>>
                                <?php elseif ($cf['field_type'] === 'email'): ?>
                                    <label for="<?php echo $cfInputId; ?>" class="form-label fw-semibold">
                                        <?php echo htmlspecialchars($cf['label']); ?>
                                        <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                    </label>
                                    <input type="email" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>"
                                           class="form-control"
                                           placeholder="<?php echo htmlspecialchars($cf['placeholder'] ?? ''); ?>"
                                           <?php echo $cf['is_required'] ? 'required' : ''; ?>>
                                <?php elseif ($cf['field_type'] === 'url'): ?>
                                    <label for="<?php echo $cfInputId; ?>" class="form-label fw-semibold">
                                        <?php echo htmlspecialchars($cf['label']); ?>
                                        <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                    </label>
                                    <input type="url" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>"
                                           class="form-control"
                                           placeholder="<?php echo htmlspecialchars($cf['placeholder'] ?? 'https://'); ?>"
                                           <?php echo $cf['is_required'] ? 'required' : ''; ?>>
                                <?php else: /* text */ ?>
                                    <label for="<?php echo $cfInputId; ?>" class="form-label fw-semibold">
                                        <?php echo htmlspecialchars($cf['label']); ?>
                                        <?php if ($cf['is_required']): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                                    </label>
                                    <input type="text" id="<?php echo $cfInputId; ?>" name="<?php echo $cfName; ?>"
                                           class="form-control"
                                           placeholder="<?php echo htmlspecialchars($cf['placeholder'] ?? ''); ?>"
                                           <?php echo $cf['is_required'] ? 'required' : ''; ?>>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Submit Button -->
                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save me-2"></i> Save Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}
.card {
    border-radius: 12px;
}
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>

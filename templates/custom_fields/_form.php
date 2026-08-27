<?php
// This is the shared partial — included by both create.php and edit.php
// $isEdit, $field, $optionsRaw, $formAction must be set before including this file.
$fieldType = $field['field_type'] ?? 'text';
?>

<style>
.cf-form-card { border-radius: 16px; border: none; }
.type-option { cursor: pointer; }
.type-option input[type=radio] { display: none; }
.type-option .type-tile {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: .75rem .5rem;
    text-align: center;
    transition: all .15s;
    font-size: .78rem;
    font-weight: 600;
    color: #6c757d;
    background: #fff;
}
.type-option input:checked + .type-tile {
    border-color: #667eea;
    background: linear-gradient(135deg,#f0f0ff,#e8ecff);
    color: #667eea;
    box-shadow: 0 4px 14px rgba(102,126,234,.2);
}
.type-tile i { display: block; font-size: 1.5rem; margin-bottom: .3rem; }
.section-divider { border-top: 2px dashed #e9ecef; margin: 1.5rem 0; }
.validation-panel {
    background: #f8f9fc;
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid #e9ecef;
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 fw-bold">
                <i class="bi bi-<?php echo $isEdit ? 'pencil-square' : 'plus-circle'; ?> text-primary me-2"></i>
                <?php echo $isEdit ? 'Edit Custom Field' : 'Create Custom Field'; ?>
            </h2>
            <a href="/Task-Tracker/public/custom-fields" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>

        <div class="card shadow-sm cf-form-card">
            <div class="card-body p-4">
                <form action="<?php echo $formAction; ?>" method="POST" id="cfForm">

                    <!-- Label -->
                    <div class="mb-4">
                        <label for="label" class="form-label fw-bold">Field Label <span class="text-danger">*</span></label>
                        <input type="text" id="label" name="label" class="form-control form-control-lg"
                               placeholder='e.g. "Client Name", "Story Points", "Risk Level"'
                               value="<?php echo htmlspecialchars($field['label'] ?? ''); ?>"
                               required autofocus>
                        <div class="form-text">This label appears next to the input on task forms.</div>
                    </div>

                    <!-- Field Type -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Field Type <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <?php
                            $types = [
                                'text'     => ['icon' => 'bi-fonts',            'label' => 'Text'],
                                'number'   => ['icon' => 'bi-123',              'label' => 'Number'],
                                'date'     => ['icon' => 'bi-calendar-date',    'label' => 'Date'],
                                'select'   => ['icon' => 'bi-menu-button-wide', 'label' => 'Dropdown'],
                                'checkbox' => ['icon' => 'bi-check2-square',    'label' => 'Checkbox'],
                                'url'      => ['icon' => 'bi-link-45deg',       'label' => 'URL'],
                                'email'    => ['icon' => 'bi-envelope',         'label' => 'Email'],
                            ];
                            foreach ($types as $typeKey => $typeInfo): ?>
                            <div class="col-6 col-sm-4 col-lg-3">
                                <label class="type-option w-100">
                                    <input type="radio" name="field_type" value="<?php echo $typeKey; ?>"
                                           <?php echo $fieldType === $typeKey ? 'checked' : ''; ?>
                                           onchange="onTypeChange(this.value)">
                                    <div class="type-tile">
                                        <i class="bi <?php echo $typeInfo['icon']; ?>"></i>
                                        <?php echo $typeInfo['label']; ?>
                                    </div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <!-- Required toggle -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="is_required" name="is_required" value="1"
                                   <?php echo !empty($field['is_required']) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-semibold" for="is_required">
                                Required field <span class="text-muted fw-normal small">(task cannot be saved without a value)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Placeholder (text / url / email) -->
                    <div id="panelPlaceholder" class="mb-4 validation-panel" style="display:none">
                        <label for="placeholder" class="form-label fw-semibold">
                            <i class="bi bi-type me-1 text-primary"></i>Placeholder Text
                        </label>
                        <input type="text" id="placeholder" name="placeholder" class="form-control"
                               placeholder='e.g. "https://..." or "user@example.com"'
                               value="<?php echo htmlspecialchars($field['placeholder'] ?? ''); ?>">
                    </div>

                    <!-- Select options -->
                    <div id="panelSelect" class="mb-4 validation-panel" style="display:none">
                        <label for="options_raw" class="form-label fw-semibold">
                            <i class="bi bi-list-ul me-1 text-warning"></i>Dropdown Options
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="options_raw" name="options_raw" class="form-control"
                               placeholder='e.g. Low, Medium, High, Critical'
                               value="<?php echo htmlspecialchars($optionsRaw ?? ''); ?>">
                        <div class="form-text">Comma-separated values. Each becomes a selectable option.</div>
                        <div id="optionsPreview" class="mt-2 d-flex flex-wrap gap-1"></div>
                    </div>

                    <!-- Number range -->
                    <div id="panelNumber" class="mb-4 validation-panel" style="display:none">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-rulers me-1 text-success"></i>Number Range (optional)
                        </label>
                        <div class="row g-3">
                            <div class="col-6">
                                <label for="min_value" class="form-label small text-muted">Minimum Value</label>
                                <input type="number" step="any" id="min_value" name="min_value" class="form-control"
                                       placeholder="e.g. 0"
                                       value="<?php echo htmlspecialchars($field['min_value'] ?? ''); ?>">
                            </div>
                            <div class="col-6">
                                <label for="max_value" class="form-label small text-muted">Maximum Value</label>
                                <input type="number" step="any" id="max_value" name="max_value" class="form-control"
                                       placeholder="e.g. 100"
                                       value="<?php echo htmlspecialchars($field['max_value'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Sort order -->
                    <div class="mb-4">
                        <label for="sort_order" class="form-label fw-semibold">Display Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" style="max-width:120px"
                               value="<?php echo (int)($field['sort_order'] ?? 0); ?>" min="0">
                        <div class="form-text">Lower number = appears first on task forms.</div>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex gap-3 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary btn-lg flex-fill fw-semibold">
                            <i class="bi bi-save me-2"></i><?php echo $isEdit ? 'Save Changes' : 'Create Field'; ?>
                        </button>
                        <a href="/Task-Tracker/public/custom-fields" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function onTypeChange(type) {
    document.getElementById('panelPlaceholder').style.display = ['text','url','email'].includes(type) ? '' : 'none';
    document.getElementById('panelSelect').style.display   = type === 'select'   ? '' : 'none';
    document.getElementById('panelNumber').style.display   = type === 'number'   ? '' : 'none';
}
document.getElementById('options_raw')?.addEventListener('input', function() {
    const preview = document.getElementById('optionsPreview');
    preview.innerHTML = '';
    this.value.split(',').map(s=>s.trim()).filter(Boolean).forEach(opt => {
        const span = document.createElement('span');
        span.className = 'badge bg-warning text-dark me-1 mb-1';
        span.textContent = opt;
        preview.appendChild(span);
    });
});
(function() {
    const checked = document.querySelector('input[name=field_type]:checked');
    if (checked) onTypeChange(checked.value);
    const optsInput = document.getElementById('options_raw');
    if (optsInput && optsInput.value) optsInput.dispatchEvent(new Event('input'));
})();
</script>

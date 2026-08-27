<?php
$pageTitle = 'Custom Fields - Advanced Task Tracker';
ob_start();

$typeLabels = [
    'text'     => ['icon' => 'bi-fonts',           'label' => 'Text',     'color' => 'primary'],
    'number'   => ['icon' => 'bi-123',             'label' => 'Number',   'color' => 'success'],
    'date'     => ['icon' => 'bi-calendar-date',   'label' => 'Date',     'color' => 'info'],
    'select'   => ['icon' => 'bi-menu-button-wide','label' => 'Dropdown', 'color' => 'warning'],
    'checkbox' => ['icon' => 'bi-check2-square',   'label' => 'Checkbox', 'color' => 'secondary'],
    'url'      => ['icon' => 'bi-link-45deg',      'label' => 'URL',      'color' => 'dark'],
    'email'    => ['icon' => 'bi-envelope',        'label' => 'Email',    'color' => 'danger'],
];
?>

<style>
:root {
    --cf-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --cf-card-radius: 16px;
}
.cf-hero {
    background: var(--cf-gradient);
    border-radius: var(--cf-card-radius);
    color: #fff;
    padding: 2rem 2rem 1.5rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}
.cf-hero::before {
    content: '';
    position: absolute; top: -40px; right: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}
.cf-hero::after {
    content: '';
    position: absolute; bottom: -60px; right: 60px;
    width: 140px; height: 140px;
    background: rgba(255,255,255,0.06);
    border-radius: 50%;
}
.field-card {
    background: #fff;
    border-radius: var(--cf-card-radius);
    border: 1px solid #e9ecef;
    transition: transform .18s, box-shadow .18s;
    overflow: hidden;
}
.field-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(102,126,234,.15);
}
.field-card .field-type-badge {
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .04em;
    padding: .35em .75em;
    border-radius: 30px;
}
.field-card .field-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.field-card .meta-chip {
    font-size: .72rem;
    background: #f0f2ff;
    color: #6574cd;
    border-radius: 20px;
    padding: .2em .65em;
    display: inline-block;
}
.empty-state {
    padding: 4rem 2rem;
    text-align: center;
    background: #f8f9fc;
    border-radius: var(--cf-card-radius);
    border: 2px dashed #dee2e6;
}
</style>

<!-- Hero Banner -->
<div class="cf-hero shadow">
    <div class="d-flex justify-content-between align-items-center position-relative" style="z-index:1">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-sliders me-2"></i>Custom Fields</h1>
            <p class="mb-0 opacity-75">Define extra data fields that appear on all your tasks.</p>
        </div>
        <a href="/Task-Tracker/public/custom-fields/create" class="btn btn-light fw-semibold shadow-sm px-4">
            <i class="bi bi-plus-lg me-1"></i> New Field
        </a>
    </div>
    <div class="mt-3 d-flex gap-3 flex-wrap position-relative" style="z-index:1">
        <div class="bg-white bg-opacity-10 rounded-3 px-3 py-2 text-center">
            <div class="fs-4 fw-bold"><?php echo count($fields); ?></div>
            <div class="small opacity-75">Total Fields</div>
        </div>
        <div class="bg-white bg-opacity-10 rounded-3 px-3 py-2 text-center">
            <div class="fs-4 fw-bold"><?php echo count(array_filter($fields, fn($f) => $f['is_required'])); ?></div>
            <div class="small opacity-75">Required</div>
        </div>
        <div class="bg-white bg-opacity-10 rounded-3 px-3 py-2 text-center">
            <div class="fs-4 fw-bold"><?php echo count(array_filter($fields, fn($f) => $f['field_type'] === 'select')); ?></div>
            <div class="small opacity-75">Dropdowns</div>
        </div>
    </div>
</div>

<?php if (empty($fields)): ?>
<div class="empty-state">
    <i class="bi bi-sliders2 display-3 text-muted opacity-40 d-block mb-3"></i>
    <h5 class="fw-semibold text-muted">No custom fields yet</h5>
    <p class="text-muted small mb-4">Create fields like "Client Name", "Story Points", "Risk Level" and they'll appear on all your tasks.</p>
    <a href="/Task-Tracker/public/custom-fields/create" class="btn btn-primary btn-lg px-5">
        <i class="bi bi-plus-lg me-2"></i>Create Your First Field
    </a>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($fields as $field):
        $ti = $typeLabels[$field['field_type']] ?? ['icon'=>'bi-puzzle','label'=>$field['field_type'],'color'=>'secondary'];
    ?>
    <div class="col-md-6 col-xl-4">
        <div class="field-card p-3 h-100">
            <div class="d-flex align-items-start gap-3">
                <!-- Icon -->
                <div class="field-icon bg-<?php echo $ti['color']; ?> bg-opacity-10 text-<?php echo $ti['color']; ?>">
                    <i class="bi <?php echo $ti['icon']; ?>"></i>
                </div>
                <!-- Content -->
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h6 class="mb-0 fw-semibold text-truncate"><?php echo htmlspecialchars($field['label']); ?></h6>
                        <?php if ($field['is_required']): ?>
                            <span class="badge bg-danger-subtle text-danger" style="font-size:.65rem">Required</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="field-type-badge bg-<?php echo $ti['color']; ?> text-white">
                            <i class="bi <?php echo $ti['icon']; ?> me-1"></i><?php echo $ti['label']; ?>
                        </span>
                        <span class="text-muted small font-monospace"><?php echo htmlspecialchars($field['field_key']); ?></span>
                    </div>
                    <!-- Extra info chips -->
                    <div class="mt-2 d-flex gap-1 flex-wrap">
                        <?php if (!empty($field['placeholder'])): ?>
                            <span class="meta-chip"><i class="bi bi-type me-1"></i><?php echo htmlspecialchars($field['placeholder']); ?></span>
                        <?php endif; ?>
                        <?php if ($field['min_value'] !== null): ?>
                            <span class="meta-chip">Min: <?php echo $field['min_value']; ?></span>
                        <?php endif; ?>
                        <?php if ($field['max_value'] !== null): ?>
                            <span class="meta-chip">Max: <?php echo $field['max_value']; ?></span>
                        <?php endif; ?>
                        <?php if ($field['field_type'] === 'select' && !empty($field['options'])): ?>
                            <?php $opts = json_decode($field['options'], true); ?>
                            <span class="meta-chip"><?php echo count($opts ?? []); ?> options</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Actions -->
            <div class="d-flex gap-2 mt-3 pt-3 border-top">
                <a href="/Task-Tracker/public/custom-fields/edit/<?php echo $field['id']; ?>" class="btn btn-outline-primary btn-sm flex-fill">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <a href="/Task-Tracker/public/custom-fields/delete/<?php echo $field['id']; ?>"
                   class="btn btn-outline-danger btn-sm flex-fill"
                   onclick="return confirm('Delete this field and all its saved values?')">
                    <i class="bi bi-trash me-1"></i>Delete
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Add New card -->
    <div class="col-md-6 col-xl-4">
        <a href="/Task-Tracker/public/custom-fields/create" class="text-decoration-none">
            <div class="field-card p-3 h-100 d-flex align-items-center justify-content-center"
                 style="border: 2px dashed #d0d7de; background: #fafbff; min-height:140px;">
                <div class="text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mx-auto mb-2"
                         style="width:48px;height:48px;font-size:1.4rem">
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <p class="mb-0 fw-semibold text-primary">Add Field</p>
                </div>
            </div>
        </a>
    </div>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/main.php'; ?>

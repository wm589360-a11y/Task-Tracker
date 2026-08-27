<?php
$pageTitle = htmlspecialchars($task['title']) . ' - Task Tracker';
ob_start();
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <a href="/Task-Tracker/public/tasks" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Tasks</a>
    <a href="/Task-Tracker/public/tasks/edit/<?php echo $task['id']; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit Task</a>
</div>

<!-- Task Header -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0"><?php echo htmlspecialchars($task['title']); ?></h2>
            <div class="d-flex gap-2">
                <?php
                    $pc = match($task['priority']) { 'Urgent'=>'bg-danger','High'=>'bg-warning text-dark','Medium'=>'bg-primary',default=>'bg-secondary' };
                    $sc = match($task['status'])   { 'Completed'=>'bg-success','In Progress'=>'bg-primary',default=>'bg-warning text-dark' };
                ?>
                <span class="badge rounded-pill <?php echo $pc; ?> fs-6 px-3"><?php echo $task['priority']; ?></span>
                <span class="badge rounded-pill <?php echo $sc; ?> fs-6 px-3"><?php echo $task['status']; ?></span>
            </div>
        </div>

        <?php if (!empty($task['description'])): ?>
            <p class="text-muted fs-6 mb-4"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-sm-4">
                <div class="d-flex align-items-center gap-2 text-muted">
                    <i class="bi bi-calendar-event fs-5 text-primary"></i>
                    <div>
                        <div class="small fw-semibold text-dark">Due Date</div>
                        <div><?php echo !empty($task['due_date']) ? date('M d, Y', strtotime($task['due_date'])) : 'Not set'; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="d-flex align-items-center gap-2 text-muted">
                    <i class="bi bi-tag fs-5 text-primary"></i>
                    <div>
                        <div class="small fw-semibold text-dark">Category</div>
                        <div><?php echo !empty($task['category_name']) ? htmlspecialchars($task['category_name']) : 'None'; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="d-flex align-items-center gap-2 text-muted">
                    <i class="bi bi-person-circle fs-5 text-primary"></i>
                    <div>
                        <div class="small fw-semibold text-dark">Assigned To</div>
                        <div><?php echo !empty($task['assigned_name']) ? htmlspecialchars($task['assigned_name']) : 'Unassigned'; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Tags Section ──────────────────────────────────────────── -->
        <div class="mt-4 pt-3 border-top">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-bold small text-muted"><i class="bi bi-tags me-1"></i>Tags:</span>
                <?php foreach($tags as $tag): ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1">
                        #<?php echo htmlspecialchars($tag['name']); ?>
                        <a href="/Task-Tracker/public/tags/remove/<?php echo $task['id']; ?>/<?php echo $tag['id']; ?>"
                           class="ms-1 text-danger text-decoration-none" title="Remove tag"
                           onclick="return confirm('Remove this tag?')">×</a>
                    </span>
                <?php endforeach; ?>
                <?php if (empty($tags)): ?>
                    <span class="text-muted small">No tags yet.</span>
                <?php endif; ?>

                <!-- Inline add tag form -->
                <form action="/Task-Tracker/public/tags/add/<?php echo $task['id']; ?>" method="POST"
                      class="d-flex gap-1 ms-2">
                    <input type="text" name="tag_name" class="form-control form-control-sm"
                           placeholder="Add tag…" style="width:130px;" list="tagSuggestions">
                    <datalist id="tagSuggestions">
                        <?php foreach($allTags as $t): ?>
                            <option value="<?php echo htmlspecialchars($t['name']); ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── Custom Fields Display ──────────────────────────────────────────── -->
<?php if (!empty($customFields) && !empty($customFieldValues)): ?>
<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="card-header bg-white border-0 py-3 px-4">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-sliders text-primary me-2"></i>Custom Fields
        </h5>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="row g-3">
        <?php
        $typeIcons = [
            'text'     => 'bi-fonts',
            'number'   => 'bi-123',
            'date'     => 'bi-calendar-date',
            'select'   => 'bi-menu-button-wide',
            'checkbox' => 'bi-check2-square',
            'url'      => 'bi-link-45deg',
            'email'    => 'bi-envelope',
        ];
        foreach ($customFields as $cf):
            $val = $customFieldValues[$cf['id']]['value'] ?? null;
            if ($val === null || $val === '') continue;
            $icon = $typeIcons[$cf['field_type']] ?? 'bi-puzzle';
        ?>
        <div class="col-sm-6 col-lg-4">
            <div class="p-3 rounded-3 border" style="background:#f8f9fc;">
                <div class="small text-muted fw-semibold mb-1">
                    <i class="bi <?php echo $icon; ?> me-1"></i><?php echo htmlspecialchars($cf['label']); ?>
                </div>
                <div class="fw-semibold">
                    <?php if ($cf['field_type'] === 'checkbox'): ?>
                        <?php if ($val == '1'): ?>
                            <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Yes</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><i class="bi bi-x-lg me-1"></i>No</span>
                        <?php endif; ?>
                    <?php elseif ($cf['field_type'] === 'url'): ?>
                        <a href="<?php echo htmlspecialchars($val); ?>" target="_blank" rel="noopener" class="text-primary text-truncate d-inline-block" style="max-width:200px">
                            <?php echo htmlspecialchars($val); ?>
                        </a>
                    <?php elseif ($cf['field_type'] === 'email'): ?>
                        <a href="mailto:<?php echo htmlspecialchars($val); ?>" class="text-primary"><?php echo htmlspecialchars($val); ?></a>
                    <?php elseif ($cf['field_type'] === 'date'): ?>
                        <?php echo date('M d, Y', strtotime($val)); ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars($val); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Sub-tasks / Checklist ──────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="card-header bg-white border-0 py-3 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-check2-square text-primary me-2"></i>Sub-tasks
                <span class="badge bg-light text-dark border ms-2"><?php echo count($subtasks); ?></span>
            </h5>
            <?php if (!empty($subtasks)):
                $done = count(array_filter($subtasks, fn($s) => $s['status'] === 'Completed'));
                $pct  = round(($done / count($subtasks)) * 100);
            ?>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted"><?php echo $done; ?>/<?php echo count($subtasks); ?> done</small>
                <div class="progress" style="width:80px;height:8px;border-radius:4px;">
                    <div class="progress-bar bg-success" style="width:<?php echo $pct; ?>%;"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body px-4 pt-0 pb-3">
        <!-- Sub-task list -->
        <?php if (!empty($subtasks)): ?>
        <ul class="list-group list-group-flush mb-3">
            <?php foreach($subtasks as $s):
                $done = $s['status'] === 'Completed';
            ?>
            <li class="list-group-item px-0 d-flex align-items-center gap-3">
                <a href="/Task-Tracker/public/subtasks/toggle/<?php echo $s['id']; ?>?current=<?php echo urlencode($s['status']); ?>&parent=<?php echo $task['id']; ?>"
                   class="btn btn-sm <?php echo $done ? 'btn-success' : 'btn-outline-secondary'; ?> rounded-circle p-0 d-flex align-items-center justify-content-center"
                   style="width:28px;height:28px;" title="<?php echo $done ? 'Mark Pending' : 'Mark Complete'; ?>">
                    <i class="bi <?php echo $done ? 'bi-check' : 'bi-circle'; ?>"></i>
                </a>
                <span class="flex-fill <?php echo $done ? 'text-decoration-line-through text-muted' : ''; ?>">
                    <?php echo htmlspecialchars($s['title']); ?>
                </span>
                <a href="/Task-Tracker/public/subtasks/delete/<?php echo $s['id']; ?>?parent=<?php echo $task['id']; ?>"
                   class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center"
                   style="width:24px;height:24px;" title="Delete"
                   onclick="return confirm('Delete this sub-task?')">
                    <i class="bi bi-x" style="font-size:.8rem;"></i>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="text-muted small mb-3 mt-2">No sub-tasks yet. Break this task into smaller steps!</p>
        <?php endif; ?>

        <!-- Add sub-task form -->
        <form action="/Task-Tracker/public/subtasks/add/<?php echo $task['id']; ?>" method="POST"
              class="d-flex gap-2">
            <input type="text" name="subtask_title" class="form-control form-control-sm"
                   placeholder="Add a sub-task…" required>
            <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                <i class="bi bi-plus me-1"></i>Add
            </button>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Comments Section -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h5 class="mb-0 fw-bold"><i class="bi bi-chat-dots text-primary me-2"></i>Comments
                    <span class="badge bg-light text-dark border ms-2"><?php echo count($comments); ?></span>
                </h5>
            </div>
            <div class="card-body px-4">
                <div class="comment-thread mb-4" style="max-height:400px;overflow-y:auto;">
                    <?php if (empty($comments)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-chat-square display-4 opacity-25"></i>
                            <p class="mt-2 mb-0">No comments yet. Be the first!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($comments as $c): ?>
                            <div class="d-flex gap-3 mb-4">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
                                     style="width:38px;height:38px;">
                                    <?php echo strtoupper(substr($c['user_name'], 0, 1)); ?>
                                </div>
                                <div class="flex-fill">
                                    <div class="bg-light rounded-3 p-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="fw-semibold small"><?php echo htmlspecialchars($c['user_name']); ?></span>
                                            <span class="text-muted small"><?php echo date('M d, Y h:i A', strtotime($c['created_at'])); ?></span>
                                        </div>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <form action="/Task-Tracker/public/tasks/comment/<?php echo $task['id']; ?>" method="POST">
                    <div class="d-flex gap-2 align-items-start">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 fw-bold"
                             style="width:38px;height:38px;">
                            <?php echo strtoupper(substr(SessionHelper::get('user_name','U'), 0, 1)); ?>
                        </div>
                        <div class="flex-fill">
                            <textarea name="comment" class="form-control" rows="2"
                                      placeholder="Write a comment…" required></textarea>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-send me-1"></i>Post Comment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Attachments Section -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h5 class="mb-0 fw-bold"><i class="bi bi-paperclip text-primary me-2"></i>Attachments
                    <span class="badge bg-light text-dark border ms-2"><?php echo count($attachments); ?></span>
                </h5>
            </div>
            <div class="card-body px-4">
                <?php
                $anyLockedByOther = false;
                $lockedByOtherName = '';
                $currentUserId = SessionHelper::get('user_id');
                foreach ($attachments as $att) {
                    if (!empty($att['locked_by']) && $att['locked_by'] != $currentUserId) {
                        $anyLockedByOther = true;
                        $lockedByOtherName = $att['locked_by_name'] ?? 'another user';
                        break;
                    }
                }
                ?>
                
                <?php if ($anyLockedByOther): ?>
                    <div class="alert alert-warning py-2 mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Uploads are temporarily disabled because a document is locked by <strong><?php echo htmlspecialchars($lockedByOtherName); ?></strong>.
                    </div>
                <?php else: ?>
                    <form action="/Task-Tracker/public/tasks/attach/<?php echo $task['id']; ?>" method="POST"
                          enctype="multipart/form-data" class="mb-4">
                    <div class="upload-zone p-4 text-center rounded-3 border border-2 border-primary border-opacity-25 mb-2">
                        <i class="bi bi-cloud-arrow-up fs-2 text-muted mb-2 d-block"></i>
                        <p class="text-muted small mb-2">Drag & drop or click to upload</p>
                        <input type="file" name="attachment" class="form-control form-control-sm"
                               accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xlsx,.txt,.zip">
                        <p class="text-muted mt-1 mb-0" style="font-size:.75rem;">Images, PDF, Word, Excel, ZIP — Max 5MB</p>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-upload me-1"></i>Upload File
                    </button>
                </form>
                <?php endif; ?>


                <?php if (empty($attachments)): ?>
                    <p class="text-muted text-center small">No attachments yet.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($attachments as $att):
                            $ext  = strtolower(pathinfo($att['file_name'], PATHINFO_EXTENSION));
                            $icon = match(true) {
                                in_array($ext, ['jpg','jpeg','png','gif']) => 'bi-file-image text-success',
                                $ext === 'pdf'                             => 'bi-file-pdf text-danger',
                                in_array($ext, ['doc','docx'])            => 'bi-file-word text-primary',
                                in_array($ext, ['xls','xlsx'])            => 'bi-file-excel text-success',
                                $ext === 'zip'                            => 'bi-file-zip text-warning',
                                default                                   => 'bi-file-text text-secondary',
                            };
                            $size = round($att['file_size'] / 1024, 1) . ' KB';
                        ?>
                        <li class="list-group-item px-0 d-flex align-items-center gap-2">
                            <i class="bi <?php echo $icon; ?> fs-5"></i>
                            <div class="flex-fill overflow-hidden">
                                <a href="/Task-Tracker/public/<?php echo htmlspecialchars($att['file_path']); ?>"
                                   target="_blank" class="fw-semibold text-truncate d-block text-decoration-none"
                                   style="max-width:180px;">
                                    <?php echo htmlspecialchars($att['file_name']); ?>
                                </a>
                                <small class="text-muted"><?php echo $size; ?> · <?php echo htmlspecialchars($att['uploader_name']); ?></small>
                                <?php if (!empty($att['locked_by'])): ?>
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;"><i class="bi bi-lock-fill"></i> Locked by <?php echo htmlspecialchars($att['locked_by_name'] ?? 'Unknown'); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php
                            $isLocked = !empty($att['locked_by']);
                            $isLockedByMe = $isLocked && $att['locked_by'] == $currentUserId;
                            $isLockedByOther = $isLocked && $att['locked_by'] != $currentUserId;
                            $canUnlock = $isLockedByMe || $task['created_by'] == $currentUserId;
                            ?>
                            
                            <div class="d-flex gap-1">
                                <a href="/Task-Tracker/public/<?php echo htmlspecialchars($att['file_path']); ?>"
                                   target="_blank" class="btn btn-sm btn-outline-secondary" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <?php if (!$isLockedByOther): ?>
                                    <a href="/Task-Tracker/public/attachments/delete/<?php echo $att['id']; ?>"
                                       class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this attachment?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Locked by <?php echo htmlspecialchars($att['locked_by_name'] ?? 'Unknown'); ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if (!$isLocked): ?>
                                    <form action="/Task-Tracker/public/attachments/lock/<?php echo $att['id']; ?>" method="POST" class="m-0">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Lock Document"><i class="bi bi-lock"></i></button>
                                    </form>
                                <?php elseif ($canUnlock): ?>
                                    <form action="/Task-Tracker/public/attachments/unlock/<?php echo $att['id']; ?>" method="POST" class="m-0">
                                        <button type="submit" class="btn btn-sm btn-warning" title="Unlock Document"><i class="bi bi-unlock"></i></button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Locked by <?php echo htmlspecialchars($att['locked_by_name'] ?? 'Unknown'); ?>"><i class="bi bi-lock-fill"></i></button>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.upload-zone { background:#f8f9fa; cursor:pointer; transition:background .2s,border-color .2s; }
.upload-zone:hover { background:#e9f0ff; }
</style>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/main.php'; ?>
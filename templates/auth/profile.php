<?php
$pageTitle = 'My Profile - Advanced Task Tracker';
ob_start();
$initial = strtoupper(substr($user['name'] ?? 'U', 0, 1));
?>

<div class="row justify-content-center">
    <div class="col-lg-10">

        <!-- Profile Header Card -->
        <div class="card border-0 shadow-sm mb-4"
             style="background: linear-gradient(135deg,#667eea,#764ba2); color:#fff; border-radius:16px;">
            <div class="card-body py-4 px-5 d-flex align-items-center gap-4 flex-wrap">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow"
                     style="width:80px;height:80px;font-size:2rem;background:rgba(255,255,255,0.25);">
                    <?php echo $initial; ?>
                </div>
                <div>
                    <h3 class="mb-1 fw-bold"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="mb-0 opacity-75"><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Update Profile Card -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-person-gear text-primary me-2"></i>Edit Profile
                        </h5>
                        <form method="POST" action="<?= URL_ROOT ?>/profile">
                            <input type="hidden" name="action_type" value="update_profile">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" name="name" class="form-control"
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-muted small">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control"
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-shield-lock text-danger me-2"></i>Change Password
                        </h5>
                        <form method="POST" action="<?= URL_ROOT ?>/profile">
                            <input type="hidden" name="action_type" value="change_password">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small">Current Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
                                    <input type="password" name="current_password" class="form-control"
                                           placeholder="Your current password" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted small">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-key text-muted"></i></span>
                                    <input type="password" name="new_password" class="form-control"
                                           placeholder="Min 6 characters" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-muted small">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-key-fill text-muted"></i></span>
                                    <input type="password" name="confirm_password" class="form-control"
                                           placeholder="Repeat new password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-danger w-100 py-2">
                                <i class="bi bi-shield-check me-2"></i>Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php $content = ob_get_clean(); require_once __DIR__ . '/../layouts/main.php'; ?>

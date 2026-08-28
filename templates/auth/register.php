<?php
$pageTitle = 'Register - Create Account';
ob_start();
?>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus"></i> Create Account</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= URL_ROOT ?>/register" id="registerForm" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                   required autofocus>
                            <div class="invalid-feedback">Name must be at least 2 characters.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="password-strength mt-2" id="passwordStrength"></div>
                            <div class="password-strength-text" id="passwordStrengthText"></div>
                            <small class="text-muted">Minimum 6 characters with mix of letters and numbers.</small>
                            <div class="invalid-feedback">Password must be at least 6 characters.</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a>
                                </label>
                                <div class="invalid-feedback">You must agree to the terms.</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-person-plus"></i> Register
                        </button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    Already have an account? <a href="<?= URL_ROOT ?>/login">Login here</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms of Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>By using this application, you agree to use it responsibly and lawfully.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            // Password strength
            password.addEventListener('input', function() {
                const strength = checkPasswordStrength(this.value);
                const bar = document.getElementById('passwordStrength');
                const text = document.getElementById('passwordStrengthText');

                if (this.value.length === 0) {
                    bar.className = 'password-strength';
                    text.textContent = '';
                    return;
                }

                bar.className = 'password-strength ' + strength.className;
                text.textContent = strength.message;
                text.className = 'password-strength-text text-' +
                    (strength.className === 'weak' ? 'danger' :
                        strength.className === 'medium' ? 'warning' : 'success');
            });

            // Confirm password match
            confirmPassword.addEventListener('input', function() {
                if (this.value !== password.value) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;

                if (document.getElementById('name').value.length < 2) {
                    document.getElementById('name').classList.add('is-invalid');
                    isValid = false;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(document.getElementById('email').value)) {
                    document.getElementById('email').classList.add('is-invalid');
                    isValid = false;
                }

                if (password.value.length < 6) {
                    password.classList.add('is-invalid');
                    isValid = false;
                }

                if (password.value !== confirmPassword.value) {
                    confirmPassword.classList.add('is-invalid');
                    isValid = false;
                }

                if (!document.getElementById('terms').checked) {
                    document.getElementById('terms').classList.add('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
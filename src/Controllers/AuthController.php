<?php
// Use absolute paths to avoid confusion
require_once dirname(__DIR__) . '/Models/User.php';
require_once dirname(__DIR__) . '/helpers/SessionHelper.php';

class AuthController {
    /** @var User $userModel */
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
        SessionHelper::start();
    }

    public function showRegister() {
        if (SessionHelper::isLoggedIn()) {
            header('Location: /Task-Tracker/public/dashboard');
            exit();
        }
        require_once dirname(__DIR__) . '/../templates/auth/register.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Task-Tracker/public/register');
            exit();
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($name) || strlen($name) < 2) {
            $errors[] = "Name must be at least 2 characters";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        }

        if (!empty($errors)) {
            SessionHelper::setFlash('error', implode('<br>', $errors));
            header('Location: /Task-Tracker/public/register');
            exit();
        }

        $userId = $this->userModel->create($name, $email, $password);

        if ($userId) {
            SessionHelper::set('user_id', $userId);
            SessionHelper::set('user_name', $name);
            SessionHelper::set('user_email', $email);
            SessionHelper::set('user_role', 'user');
            SessionHelper::setFlash('success', 'Registration successful!');
            header('Location: /Task-Tracker/public/dashboard');
        } else {
            SessionHelper::setFlash('error', 'Email already exists');
            header('Location: /Task-Tracker/public/register');
        }
        exit();
    }

    public function showLogin() {
        if (SessionHelper::isLoggedIn()) {
            header('Location: /Task-Tracker/public/dashboard');
            exit();
        }
        require_once dirname(__DIR__) . '/../templates/auth/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Task-Tracker/public/login');
            exit();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            SessionHelper::setFlash('error', 'Email and password are required');
            header('Location: /Task-Tracker/public/login');
            exit();
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
            SessionHelper::set('user_id', $user['id']);
            SessionHelper::set('user_name', $user['name']);
            SessionHelper::set('user_email', $user['email']);
            SessionHelper::set('user_role', $user['role'] ?? 'user');
            SessionHelper::setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            header('Location: /Task-Tracker/public/dashboard');
        } else {
            SessionHelper::setFlash('error', 'Invalid email or password');
            header('Location: /Task-Tracker/public/login');
        }
        exit();
    }

    public function logout() {
        SessionHelper::destroy();
        SessionHelper::start();
        SessionHelper::setFlash('success', 'Logged out successfully');
        header('Location: /Task-Tracker/public/login');
        exit();
    }

    public function showProfile() {
        $user = $this->userModel->findById(SessionHelper::get('user_id'));
        require_once dirname(__DIR__) . '/../templates/auth/profile.php';
    }

    public function updateProfile() {
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        if (empty($name) || empty($email)) {
            SessionHelper::setFlash('error', 'Name and email are required.');
            header('Location: /Task-Tracker/public/profile'); exit();
        }
        $updated = $this->userModel->updateProfile(SessionHelper::get('user_id'), $name, $email);
        if ($updated) {
            SessionHelper::set('user_name', $name);
            SessionHelper::set('user_email', $email);
            SessionHelper::setFlash('success', 'Profile updated successfully!');
        } else {
            SessionHelper::setFlash('error', 'Failed to update profile.');
        }
        header('Location: /Task-Tracker/public/profile'); exit();
    }

    public function changePassword() {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (empty($current) || empty($new) || empty($confirm)) {
            SessionHelper::setFlash('error', 'All password fields are required.');
            header('Location: /Task-Tracker/public/profile'); exit();
        }
        if ($new !== $confirm) {
            SessionHelper::setFlash('error', 'New passwords do not match.');
            header('Location: /Task-Tracker/public/profile'); exit();
        }
        if (strlen($new) < 6) {
            SessionHelper::setFlash('error', 'Password must be at least 6 characters.');
            header('Location: /Task-Tracker/public/profile'); exit();
        }
        $user = $this->userModel->findByEmail(SessionHelper::get('user_email'));
        if (!$user || !password_verify($current, $user['password'])) {
            SessionHelper::setFlash('error', 'Current password is incorrect.');
            header('Location: /Task-Tracker/public/profile'); exit();
        }
        $this->userModel->changePassword(SessionHelper::get('user_id'), $new);
        SessionHelper::setFlash('success', 'Password changed successfully!');
        header('Location: /Task-Tracker/public/profile'); exit();
    }
}
?>
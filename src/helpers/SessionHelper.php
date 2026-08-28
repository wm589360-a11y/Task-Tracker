<?php

class SessionHelper
{
    /**
     * Start session if not started.
     *
     * @return void
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @param string $key
     * @return void
     */
    public static function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public static function destroy()
    {
        session_destroy();
        $_SESSION = [];
    }

    /**
     * @param string $key
     * @param string $message
     * @return void
     */
    public static function setFlash($key, $message)
    {
        $_SESSION['flash_' . $key] = $message;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public static function getFlash($key)
    {
        $message = $_SESSION['flash_' . $key] ?? null;
        unset($_SESSION['flash_' . $key]);
        return $message;
    }

    /**
     * @return bool
     */
    public static function isLoggedIn()
    {
        return self::has('user_id');
    }

    /**
     * Redirect to login if not authenticated.
     *
     * @return void
     */
    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            self::setFlash('error', 'Please login to access this page');
            header('Location: ' . URL_ROOT . '/login');
            exit();
        }
    }
}

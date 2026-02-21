<?php
namespace App\Core;

/**
 * Session manager with security features
 * Handles session creation, validation, and security
 */
class SessionManager {

    const SESSION_TIMEOUT = 1800; // 30 minutes
    const SESSION_SECURE = false;

    /**
     * Initialize session
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => self::SESSION_TIMEOUT,
                'path' => '/',
                'domain' => '',
                'secure' => self::SESSION_SECURE,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            session_start();

            // Check for timeout
            if (isset($_SESSION['last_activity'])) {
                if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
                    self::destroy();
                    return false;
                }
            }

            $_SESSION['last_activity'] = time();
            return true;
        }
        return true;
    }

    /**
     * Set session value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Delete session key
     */
    public static function delete($key) {
        unset($_SESSION[$key]);
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        return self::has('user_id') && self::has('username');
    }

    /**
     * Get current user ID
     */
    public static function getUserId() {
        return self::get('user_id');
    }

    /**
     * Get current user role
     */
    public static function getUserRole() {
        return self::get('role', 'guest');
    }

    /**
     * Regenerate session ID (on privilege escalation)
     */
    public static function regenerate() {
        session_regenerate_id(true);
    }

    /**
     * Destroy session
     */
    public static function destroy() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Create authenticated session
     */
    public static function createUserSession($userId, $username, $role, $clientId = null) {
        self::regenerate();
        self::set('user_id', $userId);
        self::set('username', $username);
        self::set('role', $role);
        if ($clientId) {
            self::set('client_id', $clientId);
        }
        self::set('csrf_token', Encryption::generateCSRFToken());
    }
}

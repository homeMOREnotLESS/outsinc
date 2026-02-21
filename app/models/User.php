<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Encryption;

/**
 * User Model
 * Handles user account management, authentication, and profile
 */
class User {
    private $db;

    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch (\Exception $e) {
            // Database not available in test/dev environment
            $this->db = null;
        }
    }

    /**
     * Create new user account
     */
    public function create($username, $email, $password, $role = 'client') {
        // Validate password strength
        $passwordErrors = Encryption::validatePasswordStrength($password);
        if (!empty($passwordErrors)) {
            throw new \Exception("Password does not meet requirements: " . implode(", ", $passwordErrors));
        }

        // Check for duplicate username
        if ($this->usernameExists($username)) {
            throw new \Exception("Username already exists");
        }

        // Hash password
        $passwordHash = Encryption::hashPassword($password);

        // Insert user
        $userId = $this->db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'status' => 'active'
        ]);

        return $userId;
    }

    /**
     * Get user by username
     */
    public function getByUsername($username) {
        $this->db->query(
            "SELECT * FROM users WHERE username = :username",
            [':username' => $username]
        );
        return $this->db->fetch();
    }

    /**
     * Get user by ID
     */
    public function getById($userId) {
        $this->db->query(
            "SELECT * FROM users WHERE user_id = :user_id",
            [':user_id' => $userId]
        );
        return $this->db->fetch();
    }

    /**
     * Verify password
     */
    public function verifyPassword($username, $password) {
        $user = $this->getByUsername($username);

        if (!$user) {
            return false;
        }

        // Check if account is locked
        if ($user['status'] === 'suspended' || $user['account_locked_until']) {
            if ($user['account_locked_until'] && strtotime($user['account_locked_until']) > time()) {
                return false;
            }
        }

        return Encryption::verifyPassword($password, $user['password_hash']);
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username) {
        $this->db->query(
            "SELECT user_id FROM users WHERE username = :username LIMIT 1",
            [':username' => $username]
        );
        return $this->db->fetch() !== false;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin($userId) {
        $this->db->update(
            'users',
            ['last_login' => date('Y-m-d H:i:s')],
            'user_id = :user_id',
            [':user_id' => $userId]
        );
    }

    /**
     * Record failed login attempt
     */
    public function recordFailedLogin($username) {
        $user = $this->getByUsername($username);

        if (!$user) {
            return;
        }

        $failedAttempts = $user['failed_login_attempts'] + 1;
        $lockUntil = null;

        // Lock account after 5 failed attempts
        if ($failedAttempts >= 5) {
            $lockUntil = date('Y-m-d H:i:s', time() + 3600); // Lock for 1 hour
        }

        $this->db->update(
            'users',
            [
                'failed_login_attempts' => $failedAttempts,
                'account_locked_until' => $lockUntil
            ],
            'user_id = :user_id',
            [':user_id' => $user['user_id']]
        );
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedLoginAttempts($userId) {
        $this->db->update(
            'users',
            [
                'failed_login_attempts' => 0,
                'account_locked_until' => null
            ],
            'user_id = :user_id',
            [':user_id' => $userId]
        );
    }

    /**
     * Change password
     */
    public function changePassword($userId, $newPassword) {
        $passwordErrors = Encryption::validatePasswordStrength($newPassword);
        if (!empty($passwordErrors)) {
            throw new \Exception("Password does not meet requirements");
        }

        $passwordHash = Encryption::hashPassword($newPassword);

        $this->db->update(
            'users',
            ['password_hash' => $passwordHash],
            'user_id = :user_id',
            [':user_id' => $userId]
        );

        // Reset failed attempts
        $this->resetFailedLoginAttempts($userId);
    }

    /**
     * Get all users by role
     */
    public function getByRole($role) {
        $this->db->query(
            "SELECT * FROM users WHERE role = :role AND status = 'active' ORDER BY created_at DESC",
            [':role' => $role]
        );
        return $this->db->fetchAll();
    }

    /**
     * Update user status
     */
    public function updateStatus($userId, $status) {
        if (!in_array($status, ['active', 'inactive', 'suspended'])) {
            throw new \Exception("Invalid status");
        }

        $this->db->update(
            'users',
            ['status' => $status],
            'user_id = :user_id',
            [':user_id' => $userId]
        );
    }
}

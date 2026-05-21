<?php
/**
 * Auth helper - checks session-based authentication & authorization
 */
class Auth {
    /**
     * Store logged-in user in session
     */
    public static function login(array $user): void {
        Session::set('user_id',   $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['full_name']);
    }

    /**
     * Clear session
     */
    public static function logout(): void {
        Session::destroy();
    }

    /**
     * Check whether a user is authenticated
     */
    public static function check(): bool {
        return Session::has('user_id');
    }

    /**
     * Get the current user's ID
     */
    public static function id(): ?int {
        $id = Session::get('user_id');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Get the current user's role
     */
    public static function role(): ?string {
        return Session::get('user_role');
    }

    /**
     * Check if the current user is an admin
     */
    public static function isAdmin(): bool {
        return self::role() === 'ADMIN';
    }

    /**
     * Redirect to login if not authenticated
     */
    public static function requireLogin(): void {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    /**
     * Abort with 403 if not admin
     */
    public static function requireAdmin(): void {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            require APP_PATH . '/views/errors/403.php';
            exit;
        }
    }
}

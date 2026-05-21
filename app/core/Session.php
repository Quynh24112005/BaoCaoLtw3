<?php
/**
 * Session wrapper - provides typed, safe session access
 */
class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $savePath = session_save_path();
            if (empty($savePath) || !is_dir($savePath) || !is_writable($savePath)) {
                session_save_path(sys_get_temp_dir());
            }
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void {
        session_unset();
        session_destroy();
    }

    public static function flash(string $key, mixed $value = null): mixed {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}

<?php
/**
 * Database connection using PDO (Singleton pattern)
 */
class Database {
    private static ?PDO $instance = null;

    private static string $host = 'localhost';
    private static string $dbname = 'hr_management';
    private static string $username = 'root';
    private static string $password = '2411';
    private static string $charset = 'utf8mb4';

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::$host
                 . ";dbname=" . self::$dbname
                 . ";charset=" . self::$charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$instance = new PDO($dsn, self::$username, self::$password, $options);
        }
        return self::$instance;
    }

    // Prevent instantiation
    private function __construct() {}
    private function __clone() {}
}

<?php
/**
 * Base Model - provides PDO access and common query helpers
 */
abstract class Model {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Execute a prepared statement and return all rows
     */
    protected function query(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a prepared statement and return a single row
     */
    protected function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Execute a prepared statement (INSERT/UPDATE/DELETE)
     * Returns last insert id for INSERT, affected rows for others
     */
    protected function execute(string $sql, array $params = []): int {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $this->db->lastInsertId() ?: $stmt->rowCount();
    }

    /**
     * Run a callable inside a database transaction
     */
    protected function transaction(callable $callback): mixed {
        $this->db->beginTransaction();
        try {
            $result = $callback();
            $this->db->commit();
            return $result;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Count rows matching a WHERE clause
     */
    protected function count(string $table, string $where = '1=1', array $params = []): int {
        $row = $this->queryOne("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}", $params);
        return (int) ($row['cnt'] ?? 0);
    }
}

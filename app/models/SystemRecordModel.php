<?php
require_once APP_PATH . '/core/Model.php';

/**
 * SystemRecordModel - handles the `system_records` table
 *
 * record_type: TICKET | AUDIT | AI_REPORT
 */
class SystemRecordModel extends Model {

    // TICKETS

    public function createTicket(array $data): int {
        return $this->execute(
            "INSERT INTO system_records
                (record_type, employee_id, related_entity_type, related_entity_id,
                 status, title, description, created_at, updated_at)
             VALUES ('TICKET', ?, ?, ?, 'OPEN', ?, ?, NOW(), NOW())",
            [
                $data['employee_id'],
                $data['related_entity_type'],
                $data['related_entity_id'],
                $data['title'],
                $data['description'],
            ]
        );
    }

    public function findTicketById(int $id): ?array {
        return $this->queryOne(
            "SELECT sr.*, u.full_name, u.employee_code
             FROM system_records sr
             JOIN users u ON u.id = sr.employee_id
             WHERE sr.id = ? AND sr.record_type = 'TICKET'",
            [$id]
        );
    }

    public function getTicketsByEmployee(int $employeeId): array {
        return $this->query(
            "SELECT * FROM system_records
             WHERE record_type = 'TICKET'
               AND employee_id = ?
             ORDER BY created_at DESC",
            [$employeeId]
        );
    }

    public function getAllTickets(string $status = ''): array {
        $where  = $status ? "AND sr.status = ?" : '';
        $params = $status ? [$status] : [];
        return $this->query(
            "SELECT sr.*, u.full_name, u.employee_code
             FROM system_records sr
             JOIN users u ON u.id = sr.employee_id
             WHERE sr.record_type = 'TICKET' {$where}
             ORDER BY sr.created_at DESC",
            $params
        );
    }

    public function updateTicketStatus(int $id, string $status, int $handledBy, ?string $note = null): void {
        $handledAt = in_array($status, ['RESOLVED', 'REJECTED', 'CLOSED'], true) ? 'NOW()' : 'NULL';
        $this->execute(
            "UPDATE system_records
             SET status = ?, handled_by = ?,
                 handled_at = {$handledAt},
                 description = CONCAT(description, IFNULL(CONCAT('\n\n[Note] ', ?), '')),
                 updated_at = NOW()
             WHERE id = ?",
            [$status, $handledBy, $note, $id]
        );
    }

    /**
     * Check if there are open tickets linked to a payroll period
     */
    public function hasOpenTicketsForPeriod(int $periodId): bool {
        $row = $this->queryOne(
            "SELECT id FROM system_records
             WHERE record_type         = 'TICKET'
               AND related_entity_type = 'payroll_period'
               AND related_entity_id   = ?
               AND status IN ('OPEN', 'IN_PROGRESS')
             LIMIT 1",
            [$periodId]
        );
        return $row !== null;
    }

    // AUDIT LOG

    public function writeAudit(array $data): void {
        $this->execute(
            "INSERT INTO system_records
                (record_type, actor_user_id, employee_id, related_entity_type,
                 related_entity_id, action, old_value_json, new_value_json,
                 description, created_at, updated_at)
             VALUES ('AUDIT', ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['actor_user_id'],
                $data['employee_id']          ?? null,
                $data['related_entity_type']  ?? null,
                $data['related_entity_id']    ?? null,
                $data['action'],
                isset($data['old_value'])     ? json_encode($data['old_value'])  : null,
                isset($data['new_value'])     ? json_encode($data['new_value'])  : null,
                $data['description']          ?? null,
            ]
        );
    }

    public function getAuditLog(int $page = 1, int $perPage = 30): array {
        $offset = ($page - 1) * $perPage;
        return $this->query(
            "SELECT sr.*, u.full_name AS actor_name
             FROM system_records sr
             LEFT JOIN users u ON u.id = sr.actor_user_id
             WHERE sr.record_type = 'AUDIT'
             ORDER BY sr.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }

    public function countAuditLog(): int {
        return $this->count('system_records', "record_type = 'AUDIT'");
    }
}

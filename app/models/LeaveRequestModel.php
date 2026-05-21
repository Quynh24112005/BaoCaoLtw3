<?php
require_once APP_PATH . '/core/Model.php';

/**
 * LeaveRequestModel - handles the `leave_requests` table
 */
class LeaveRequestModel extends Model {

    public function create(array $data): int {
        return $this->execute(
            "INSERT INTO leave_requests
                (employee_id, leave_type, start_date, end_date, reason, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 'PENDING', NOW(), NOW())",
            [
                $data['employee_id'],
                $data['leave_type'],
                $data['start_date'],
                $data['end_date'],
                $data['reason'],
            ]
        );
    }

    public function findById(int $id): ?array {
        return $this->queryOne(
            "SELECT lr.*, u.full_name, u.employee_code, u.department
             FROM leave_requests lr
             JOIN users u ON u.id = lr.employee_id
             WHERE lr.id = ?",
            [$id]
        );
    }

    public function findByEmployee(int $employeeId, int $page = 1, int $perPage = 15): array {
        $offset = ($page - 1) * $perPage;
        return $this->query(
            "SELECT * FROM leave_requests
             WHERE employee_id = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            [$employeeId, $perPage, $offset]
        );
    }

    public function findAll(string $status = '', int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $where  = $status ? "AND lr.status = ?" : '';
        $params = $status ? [$status, $perPage, $offset] : [$perPage, $offset];

        return $this->query(
            "SELECT lr.*, u.full_name, u.employee_code, u.department
             FROM leave_requests lr
             JOIN users u ON u.id = lr.employee_id
             WHERE 1=1 {$where}
             ORDER BY lr.created_at DESC
             LIMIT ? OFFSET ?",
            $params
        );
    }

    public function countAll(string $status = ''): int {
        if ($status) {
            return $this->count('leave_requests', 'status = ?', [$status]);
        }
        return $this->count('leave_requests', '1=1');
    }

    /**
     * Check for date overlap with existing PENDING or APPROVED requests
     */
    public function hasOverlap(int $employeeId, string $startDate, string $endDate, ?int $excludeId = null): bool {
        $excludeSql = $excludeId ? 'AND id != ?' : '';
        $params     = [$employeeId, $startDate, $endDate, $startDate, $endDate];
        if ($excludeId) $params[] = $excludeId;

        $row = $this->queryOne(
            "SELECT id FROM leave_requests
             WHERE employee_id = ?
               AND status IN ('PENDING','APPROVED')
               AND start_date <= ?
               AND end_date   >= ?
               {$excludeSql}
             LIMIT 1",
            $params
        );
        return $row !== null;
    }

    public function approve(int $id, int $reviewedBy): void {
        $this->execute(
            "UPDATE leave_requests
             SET status = 'APPROVED', reviewed_by = ?, reviewed_at = NOW(), updated_at = NOW()
             WHERE id = ?",
            [$reviewedBy, $id]
        );
    }

    public function reject(int $id, int $reviewedBy, string $reason): void {
        $this->execute(
            "UPDATE leave_requests
             SET status = 'REJECTED', reviewed_by = ?, reviewed_at = NOW(),
                 rejection_reason = ?, updated_at = NOW()
             WHERE id = ?",
            [$reviewedBy, $reason, $id]
        );
    }

    public function cancel(int $id): void {
        $this->execute(
            "UPDATE leave_requests
             SET status = 'CANCELLED', updated_at = NOW()
             WHERE id = ? AND status = 'PENDING'",
            [$id]
        );
    }

    /**
     * Get approved/pending dates for an employee (used to block shift registration)
     */
    public function getBlockedDates(int $employeeId): array {
        $requests = $this->query(
            "SELECT start_date, end_date FROM leave_requests
             WHERE employee_id = ?
               AND status IN ('PENDING','APPROVED')",
            [$employeeId]
        );

        $dates = [];
        foreach ($requests as $req) {
            $start = new DateTime($req['start_date']);
            $end   = new DateTime($req['end_date']);
            $end->modify('+1 day');
            $interval = new DateInterval('P1D');
            $period   = new DatePeriod($start, $interval, $end);
            foreach ($period as $dt) {
                $dates[] = $dt->format('Y-m-d');
            }
        }
        return array_unique($dates);
    }

    public function findApprovedInRange(int $employeeId, string $startDate, string $endDate): array {
        return $this->query(
            "SELECT * FROM leave_requests
             WHERE employee_id = ?
               AND status = 'APPROVED'
               AND start_date <= ?
               AND end_date   >= ?",
            [$employeeId, $endDate, $startDate]
        );
    }
}

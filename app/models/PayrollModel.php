<?php
require_once APP_PATH . '/core/Model.php';

class PayrollModel extends Model {

    // PERIOD

    public function createPeriod(array $data): int {
        return $this->execute(
            "INSERT INTO payroll_records
                (record_type, name, period_start, period_end, status, created_by, created_at, updated_at)
             VALUES ('PERIOD', ?, ?, ?, 'DRAFT', ?, NOW(), NOW())",
            [
                $data['name'],
                $data['period_start'],
                $data['period_end'],
                $data['created_by'],
            ]
        );
    }

    public function getAllPeriods(): array {
        return $this->query(
            "SELECT * FROM payroll_records
             WHERE record_type = 'PERIOD'
             ORDER BY period_start DESC"
        );
    }

    public function getPeriodById(int $id): ?array {
        return $this->queryOne(
            "SELECT * FROM payroll_records WHERE id = ? AND record_type = 'PERIOD'",
            [$id]
        );
    }

    public function updatePeriodStatus(int $id, string $status): void {
        $extra = $status === 'PUBLISHED' ? ", published_at = NOW()" : '';
        $this->execute(
            "UPDATE payroll_records
             SET status = ? {$extra}, updated_at = NOW()
             WHERE id = ? AND record_type = 'PERIOD'",
            [$status, $id]
        );
    }

    // PAYROLL ITEMS

    public function upsertItem(array $data): void {
        $existing = $this->queryOne(
            "SELECT id FROM payroll_records
             WHERE record_type = 'ITEM'
               AND parent_id   = ?
               AND employee_id = ?",
            [$data['period_id'], $data['employee_id']]
        );

        $snapshot = json_encode($data['snapshot'] ?? []);

        if ($existing) {
            $this->execute(
                "UPDATE payroll_records
                 SET base_amount = ?, overtime_amount = ?, allowance_amount = ?,
                     deduction_amount = ?, final_amount = ?,
                     calculation_snapshot_json = ?,
                     status = 'READY', updated_at = NOW()
                 WHERE id = ?",
                [
                    $data['base_amount'],
                    $data['overtime_amount'],
                    $data['allowance_amount'],
                    $data['deduction_amount'],
                    $data['final_amount'],
                    $snapshot,
                    (int)$existing['id'],
                ]
            );
        } else {
            $this->execute(
                "INSERT INTO payroll_records
                    (record_type, parent_id, employee_id, period_start, period_end,
                     base_amount, overtime_amount, allowance_amount, deduction_amount,
                     final_amount, calculation_snapshot_json, status, created_by,
                     created_at, updated_at)
                 VALUES ('ITEM', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'READY', ?, NOW(), NOW())",
                [
                    $data['period_id'],
                    $data['employee_id'],
                    $data['period_start'],
                    $data['period_end'],
                    $data['base_amount'],
                    $data['overtime_amount'],
                    $data['allowance_amount'],
                    $data['deduction_amount'],
                    $data['final_amount'],
                    $snapshot,
                    $data['created_by'],
                ]
            );
        }
    }

    public function getItemsByPeriod(int $periodId): array {
        return $this->query(
            "SELECT pr.*, u.full_name, u.employee_code, u.department, u.position
             FROM payroll_records pr
             JOIN users u ON u.id = pr.employee_id
             WHERE pr.record_type = 'ITEM'
               AND pr.parent_id   = ?
             ORDER BY u.full_name",
            [$periodId]
        );
    }

    public function getItemByEmployeeAndPeriod(int $employeeId, int $periodId): ?array {
        return $this->queryOne(
            "SELECT pr.*, pp.name AS period_name, pp.status AS period_status
             FROM payroll_records pr
             JOIN payroll_records pp ON pp.id = pr.parent_id
             WHERE pr.record_type = 'ITEM'
               AND pr.employee_id  = ?
               AND pr.parent_id    = ?",
            [$employeeId, $periodId]
        );
    }

    public function getPublishedItemsByEmployee(int $employeeId): array {
        return $this->query(
            "SELECT pr.*, pp.name AS period_name
             FROM payroll_records pr
             JOIN payroll_records pp ON pp.id = pr.parent_id
             WHERE pr.record_type  = 'ITEM'
               AND pr.employee_id  = ?
               AND pp.status       = 'PUBLISHED'
             ORDER BY pr.period_start DESC",
            [$employeeId]
        );
    }

    public function publishItems(int $periodId): void {
        $this->execute(
            "UPDATE payroll_records
             SET status = 'PUBLISHED', updated_at = NOW()
             WHERE record_type = 'ITEM'
               AND parent_id   = ?",
            [$periodId]
        );
    }

    /**
     * Adjust a single payroll item (admin override)
     */
    public function adjustItem(int $itemId, array $data): void {
        $this->execute(
            "UPDATE payroll_records
             SET final_amount = ?, allowance_amount = ?, deduction_amount = ?,
                 status = 'ADJUSTED', updated_at = NOW()
             WHERE id = ?",
            [
                $data['final_amount'],
                $data['allowance_amount'],
                $data['deduction_amount'],
                $itemId,
            ]
        );
    }
}

<?php
require_once APP_PATH . '/core/Model.php';

/**
 * WorkRecordModel - handles the `work_records` table
 *
 * record_type values:
 *   SCHEDULE_PERIOD | SHIFT_TEMPLATE | SHIFT_SLOT
 *   REGISTRATION    | ASSIGNMENT     | ATTENDANCE
 */
class WorkRecordModel extends Model {

    // =========================================================================
    // SCHEDULE PERIOD
    // =========================================================================

    public function createPeriod(array $data): int {
        $meta = json_encode([
            'registration_open_at'  => null,
            'registration_close_at' => null,
            'published_at'          => null,
        ]);

        return $this->execute(
            "INSERT INTO work_records
                (record_type, week_start_date, week_end_date, record_status,
                 note, meta_json, created_by, created_at, updated_at)
             VALUES ('SCHEDULE_PERIOD', ?, ?, 'DRAFT', ?, ?, ?, NOW(), NOW())",
            [
                $data['week_start_date'],
                $data['week_end_date'],
                $data['note'] ?? null,
                $meta,
                $data['created_by'],
            ]
        );
    }

    public function getAllPeriods(): array {
        return $this->query(
            "SELECT * FROM work_records
             WHERE record_type = 'SCHEDULE_PERIOD'
             ORDER BY week_start_date DESC"
        );
    }

    public function getPeriodById(int $id): ?array {
        return $this->queryOne(
            "SELECT * FROM work_records WHERE id = ? AND record_type = 'SCHEDULE_PERIOD'",
            [$id]
        );
    }

    public function updatePeriodStatus(int $id, string $status, array $metaUpdates = []): void {
        $period = $this->getPeriodById($id);
        $meta   = json_decode($period['meta_json'] ?? '{}', true);
        $meta   = array_merge($meta, $metaUpdates);

        $this->execute(
            "UPDATE work_records
             SET record_status = ?, meta_json = ?, updated_at = NOW()
             WHERE id = ?",
            [$status, json_encode($meta), $id]
        );
    }

    // =========================================================================
    // SHIFT SLOTS
    // =========================================================================

    public function createSlot(array $data): int {
        return $this->execute(
            "INSERT INTO work_records
                (record_type, parent_id, work_date, shift_name, start_time, end_time,
                 break_minutes, required_headcount, is_night_shift, record_status,
                 meta_json, created_by, created_at, updated_at)
             VALUES ('SHIFT_SLOT', ?, ?, ?, ?, ?, ?, ?, ?, 'OPEN', ?, ?, NOW(), NOW())",
            [
                $data['period_id'],
                $data['work_date'],
                $data['shift_name'],
                $data['start_time'],
                $data['end_time'],
                $data['break_minutes']     ?? 30,
                $data['required_headcount'] ?? 1,
                $data['is_night_shift']    ?? 0,
                json_encode($data['meta'] ?? []),
                $data['created_by'],
            ]
        );
    }

    public function getSlotsByPeriod(int $periodId): array {
        return $this->query(
            "SELECT wr.*,
                    COUNT(CASE WHEN reg.record_status = 'ACCEPTED' THEN 1 END) AS filled_count
             FROM work_records wr
             LEFT JOIN work_records reg
                    ON reg.record_type = 'ASSIGNMENT'
                   AND reg.parent_id   = wr.id
                   AND reg.record_status = 'ASSIGNED'
             WHERE wr.record_type = 'SHIFT_SLOT'
               AND wr.parent_id   = ?
             GROUP BY wr.id
             ORDER BY wr.work_date, wr.start_time",
            [$periodId]
        );
    }

    public function getSlotById(int $id): ?array {
        return $this->queryOne(
            "SELECT * FROM work_records WHERE id = ? AND record_type = 'SHIFT_SLOT'",
            [$id]
        );
    }

    public function updateSlotStatus(int $id, string $status): void {
        $this->execute(
            "UPDATE work_records
             SET record_status = ?, updated_at = NOW()
             WHERE id = ? AND record_type = 'SHIFT_SLOT'",
            [$status, $id]
        );
    }

    // =========================================================================
    // REGISTRATIONS
    // =========================================================================

    public function createRegistration(array $data): int {
        return $this->execute(
            "INSERT INTO work_records
                (record_type, parent_id, employee_id, preference_level, record_status,
                 note, created_by, created_at, updated_at)
             VALUES ('REGISTRATION', ?, ?, ?, 'PENDING', ?, ?, NOW(), NOW())",
            [
                $data['slot_id'],
                $data['employee_id'],
                $data['preference_level'] ?? 1,
                $data['note']             ?? null,
                $data['employee_id'],
            ]
        );
    }

    public function getRegistrationByEmployeeAndSlot(int $employeeId, int $slotId): ?array {
        return $this->queryOne(
            "SELECT * FROM work_records
             WHERE record_type = 'REGISTRATION'
               AND employee_id = ?
               AND parent_id   = ?",
            [$employeeId, $slotId]
        );
    }

    public function getRegistrationsByEmployee(int $employeeId): array {
        return $this->query(
            "SELECT reg.*, slot.work_date, slot.shift_name, slot.start_time, slot.end_time
             FROM work_records reg
             JOIN work_records slot ON slot.id = reg.parent_id
             WHERE reg.record_type = 'REGISTRATION'
               AND reg.employee_id = ?
             ORDER BY slot.work_date DESC",
            [$employeeId]
        );
    }

    public function getRegistrationsBySlot(int $slotId): array {
        return $this->query(
            "SELECT reg.*, u.full_name, u.employee_code
             FROM work_records reg
             JOIN users u ON u.id = reg.employee_id
             WHERE reg.record_type = 'REGISTRATION'
               AND reg.parent_id   = ?
               AND reg.record_status NOT IN ('WITHDRAWN')
             ORDER BY reg.preference_level DESC",
            [$slotId]
        );
    }

    // =========================================================================
    // ASSIGNMENTS
    // =========================================================================

    public function createAssignment(array $data): int {
        $meta = json_encode(['assigned_at' => date('Y-m-d H:i:s')]);
        return $this->execute(
            "INSERT INTO work_records
                (record_type, parent_id, employee_id, work_date, shift_name,
                 start_time, end_time, record_status, meta_json, created_by,
                 created_at, updated_at)
             VALUES ('ASSIGNMENT', ?, ?, ?, ?, ?, ?, 'ASSIGNED', ?, ?, NOW(), NOW())",
            [
                $data['slot_id'],
                $data['employee_id'],
                $data['work_date'],
                $data['shift_name'],
                $data['start_time'],
                $data['end_time'],
                $meta,
                $data['created_by'],
            ]
        );
    }

    public function getAssignmentsByEmployee(int $employeeId, ?string $weekStart = null): array {
        $sql    = "SELECT asgn.*, u.full_name
                   FROM work_records asgn
                   JOIN users u ON u.id = asgn.employee_id
                   WHERE asgn.record_type = 'ASSIGNMENT'
                     AND asgn.employee_id = ?
                     AND asgn.record_status = 'ASSIGNED'";
        $params = [$employeeId];
        if ($weekStart) {
            $sql    .= " AND asgn.work_date >= ? AND asgn.work_date < DATE_ADD(?, INTERVAL 7 DAY)";
            $params[] = $weekStart;
            $params[] = $weekStart;
        }
        $sql .= " ORDER BY asgn.work_date, asgn.start_time";
        return $this->query($sql, $params);
    }

    public function getAssignmentsBySlot(int $slotId): array {
        return $this->query(
            "SELECT asgn.*, u.full_name, u.employee_code
             FROM work_records asgn
             JOIN users u ON u.id = asgn.employee_id
             WHERE asgn.record_type  = 'ASSIGNMENT'
               AND asgn.parent_id   = ?
               AND asgn.record_status = 'ASSIGNED'",
            [$slotId]
        );
    }

    public function removeAssignment(int $id, string $reason): void {
        $row  = $this->queryOne("SELECT meta_json FROM work_records WHERE id = ?", [$id]);
        $meta = json_decode($row['meta_json'] ?? '{}', true);
        $meta['removed_at']      = date('Y-m-d H:i:s');
        $meta['removal_reason']  = $reason;

        $this->execute(
            "UPDATE work_records
             SET record_status = 'REMOVED', meta_json = ?, updated_at = NOW()
             WHERE id = ?",
            [json_encode($meta), $id]
        );
    }

    /**
     * Remove all ASSIGNED records for an employee in a date range (used on leave approval)
     */
    public function removeAssignmentsByEmployeeInRange(
        int $employeeId, string $startDate, string $endDate, string $reason
    ): array {
        $assignments = $this->query(
            "SELECT id, parent_id FROM work_records
             WHERE record_type   = 'ASSIGNMENT'
               AND employee_id   = ?
               AND record_status = 'ASSIGNED'
               AND work_date    BETWEEN ? AND ?",
            [$employeeId, $startDate, $endDate]
        );

        foreach ($assignments as $asgn) {
            $this->removeAssignment((int)$asgn['id'], $reason);
        }

        return $assignments;
    }

    /**
     * Check if an employee already has a conflicting assignment on a date/time
     */
    public function hasConflictingAssignment(
        int $employeeId, string $workDate, string $startTime, string $endTime
    ): bool {
        $row = $this->queryOne(
            "SELECT id FROM work_records
             WHERE record_type   = 'ASSIGNMENT'
               AND employee_id   = ?
               AND record_status = 'ASSIGNED'
               AND work_date     = ?
               AND start_time  < ?
               AND end_time    > ?",
            [$employeeId, $workDate, $endTime, $startTime]
        );
        return $row !== null;
    }

    // =========================================================================
    // ATTENDANCE
    // =========================================================================

    public function upsertAttendance(array $data): void {
        $existing = $this->queryOne(
            "SELECT id FROM work_records
             WHERE record_type = 'ATTENDANCE'
               AND employee_id = ?
               AND work_date   = ?
               AND parent_id   = ?",
            [$data['employee_id'], $data['work_date'], $data['assignment_id']]
        );

        if ($existing) {
            $this->execute(
                "UPDATE work_records
                 SET check_in_at = ?, check_out_at = ?, worked_minutes = ?,
                     late_minutes = ?, overtime_minutes = ?,
                     record_status = ?, source = ?, note = ?,
                     updated_at = NOW()
                 WHERE id = ?",
                [
                    $data['check_in_at'],
                    $data['check_out_at'],
                    $data['worked_minutes'],
                    $data['late_minutes']    ?? 0,
                    $data['overtime_minutes'] ?? 0,
                    $data['record_status']   ?? 'PRESENT',
                    $data['source']          ?? 'MANUAL',
                    $data['note']            ?? null,
                    (int)$existing['id'],
                ]
            );
        } else {
            $this->execute(
                "INSERT INTO work_records
                    (record_type, parent_id, employee_id, work_date,
                     check_in_at, check_out_at, worked_minutes,
                     late_minutes, overtime_minutes, record_status,
                     source, note, created_by, created_at, updated_at)
                 VALUES ('ATTENDANCE', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $data['assignment_id'],
                    $data['employee_id'],
                    $data['work_date'],
                    $data['check_in_at'],
                    $data['check_out_at'],
                    $data['worked_minutes'],
                    $data['late_minutes']    ?? 0,
                    $data['overtime_minutes'] ?? 0,
                    $data['record_status']   ?? 'PRESENT',
                    $data['source']          ?? 'MANUAL',
                    $data['note']            ?? null,
                    $data['created_by'],
                ]
            );
        }
    }

    public function getAttendanceByEmployee(int $employeeId, string $startDate, string $endDate): array {
        return $this->query(
            "SELECT * FROM work_records
             WHERE record_type = 'ATTENDANCE'
               AND employee_id = ?
               AND work_date  BETWEEN ? AND ?
             ORDER BY work_date",
            [$employeeId, $startDate, $endDate]
        );
    }

    public function getAttendanceById(int $id): ?array {
        return $this->queryOne(
            "SELECT * FROM work_records WHERE id = ? AND record_type = 'ATTENDANCE'",
            [$id]
        );
    }

    public function updateAttendance(int $id, array $data): void {
        $this->execute(
            "UPDATE work_records
             SET check_in_at = ?, check_out_at = ?, worked_minutes = ?,
                 late_minutes = ?, overtime_minutes = ?, note = ?,
                 updated_at = NOW()
             WHERE id = ?",
            [
                $data['check_in_at'],
                $data['check_out_at'],
                $data['worked_minutes'],
                $data['late_minutes']     ?? 0,
                $data['overtime_minutes'] ?? 0,
                $data['note']             ?? null,
                $id,
            ]
        );
    }

    public function markOnLeave(int $employeeId, string $date): void {
        $this->execute(
            "UPDATE work_records
             SET record_status = 'ON_LEAVE', updated_at = NOW()
             WHERE record_type  = 'ATTENDANCE'
               AND employee_id  = ?
               AND work_date    = ?",
            [$employeeId, $date]
        );
    }

    /**
     * Aggregate attendance stats for payroll calculation
     */
    public function getAttendanceSummary(int $employeeId, string $startDate, string $endDate): array {
        $row = $this->queryOne(
            "SELECT
                SUM(worked_minutes)   AS total_worked,
                SUM(late_minutes)     AS total_late,
                SUM(overtime_minutes) AS total_overtime,
                COUNT(*)              AS total_days
             FROM work_records
             WHERE record_type = 'ATTENDANCE'
               AND employee_id  = ?
               AND work_date   BETWEEN ? AND ?
               AND record_status NOT IN ('ABSENT')",
            [$employeeId, $startDate, $endDate]
        );
        return $row ?? ['total_worked' => 0, 'total_late' => 0, 'total_overtime' => 0, 'total_days' => 0];
    }

    /**
     * Weekly total worked hours per employee (used by AI burnout)
     */
    public function getWeeklyWorkHoursByEmployee(string $since): array {
        return $this->query(
            "SELECT employee_id,
                    YEARWEEK(work_date, 1)  AS yw,
                    SUM(worked_minutes)/60  AS hours,
                    SUM(overtime_minutes)/60 AS overtime_hours,
                    SUM(CASE WHEN is_night_shift = 1 THEN 1 ELSE 0 END) AS night_shifts,
                    COUNT(CASE WHEN record_status = 'LATE' THEN 1 END)  AS late_count
             FROM work_records
             WHERE record_type = 'ATTENDANCE'
               AND work_date  >= ?
             GROUP BY employee_id, YEARWEEK(work_date, 1)",
            [$since]
        );
    }
}

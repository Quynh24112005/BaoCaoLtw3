<?php
require_once APP_PATH . '/core/Model.php';

/**
 * UserModel - handles the `users` table
 *
 * Merged: user account + employee profile
 */
class UserModel extends Model {

    // Read

    public function findById(int $id): ?array {
        return $this->queryOne(
            "SELECT * FROM users WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function findByEmail(string $email): ?array {
        return $this->queryOne(
            "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL",
            [$email]
        );
    }

    public function findAll(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        return $this->query(
            "SELECT id, employee_code, full_name, email, role, status, department,
                    position, employment_type, start_date, created_at
             FROM users
             WHERE deleted_at IS NULL
             ORDER BY full_name
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }

    public function countAll(): int {
        return $this->count('users', 'deleted_at IS NULL');
    }

    public function findFiltered(array $filters, int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT id, employee_code, full_name, email, role, status, department,
                       position, employment_type, start_date, created_at
                FROM users
                WHERE deleted_at IS NULL";
        
        $params = [];
        
        if (!empty($filters['q'])) {
            $sql .= " AND (full_name LIKE ? OR email LIKE ? OR employee_code LIKE ?)";
            $like = "%{$filters['q']}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        
        if (!empty($filters['department'])) {
            $sql .= " AND department = ?";
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY full_name LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->query($sql, $params);
    }

    public function countFiltered(array $filters): int {
        $sql = "SELECT COUNT(*) AS count FROM users WHERE deleted_at IS NULL";
        $params = [];
        
        if (!empty($filters['q'])) {
            $sql .= " AND (full_name LIKE ? OR email LIKE ? OR employee_code LIKE ?)";
            $like = "%{$filters['q']}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        
        if (!empty($filters['department'])) {
            $sql .= " AND department = ?";
            $params[] = $filters['department'];
        }
        
        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        $res = $this->queryOne($sql, $params);
        return (int)($res['count'] ?? 0);
    }

    public function getDepartments(): array {
        $rows = $this->query(
            "SELECT DISTINCT department FROM users WHERE deleted_at IS NULL AND department IS NOT NULL AND department != '' ORDER BY department"
        );
        return array_column($rows, 'department');
    }

    public function getDashboardStats(): array {
        $totalStaff = $this->countAll();

        $deptResult = $this->queryOne(
            "SELECT COUNT(DISTINCT department) AS count FROM users WHERE deleted_at IS NULL AND department IS NOT NULL AND department != ''"
        );
        $totalDepts = (int)($deptResult['count'] ?? 0);

        $leaveResult = $this->queryOne(
            "SELECT COUNT(DISTINCT employee_id) AS count FROM leave_requests WHERE status = 'APPROVED' AND CURRENT_DATE() BETWEEN start_date AND end_date"
        );
        $onLeave = (int)($leaveResult['count'] ?? 0);

        $hiresResult = $this->queryOne(
            "SELECT COUNT(*) AS count FROM users WHERE deleted_at IS NULL AND start_date >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')"
        );
        $newHires = (int)($hiresResult['count'] ?? 0);

        return [
            'total_staff' => $totalStaff,
            'total_departments' => $totalDepts,
            'on_leave' => $onLeave,
            'new_hires' => $newHires
        ];
    }

    public function search(string $keyword): array {
        $like = "%{$keyword}%";
        return $this->query(
            "SELECT id, employee_code, full_name, email, role, status, department, position
             FROM users
             WHERE deleted_at IS NULL
               AND (full_name LIKE ? OR email LIKE ? OR employee_code LIKE ?)",
             [$like, $like, $like]
        );
    }

    public function getStaffCountByDepartment(): array {
        return $this->query(
            "SELECT department, COUNT(*) AS count 
             FROM users 
             WHERE deleted_at IS NULL AND department IS NOT NULL AND department != '' 
             GROUP BY department 
             ORDER BY count DESC"
        );
    }

    // Write

    public function create(array $data): int {
        $sql = "INSERT INTO users
                    (email, password_hash, role, status, employee_code, full_name,
                     phone, date_of_birth, gender, department, position,
                     employment_type, base_salary, hourly_rate, start_date, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        return $this->execute($sql, [
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role']            ?? 'EMPLOYEE',
            $data['status']          ?? 'ACTIVE',
            $data['employee_code'],
            $data['full_name'],
            $data['phone']           ?? null,
            $data['date_of_birth']   ?? null,
            $data['gender']          ?? null,
            $data['department']      ?? null,
            $data['position']        ?? null,
            $data['employment_type'] ?? 'FULL_TIME',
            $data['base_salary']     ?? 0,
            $data['hourly_rate']     ?? 0,
            $data['start_date']      ?? null,
        ]);
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $values = [];

        $allowed = [
            'full_name', 'phone', 'date_of_birth', 'gender',
            'department', 'position', 'employment_type',
            'base_salary', 'hourly_rate', 'start_date',
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $fields[] = "updated_at = NOW()";
        $values[] = $id;

        $this->execute(
            "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        );
        return true;
    }

    public function updateStatus(int $id, string $status): void {
        $this->execute(
            "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $id]
        );
    }

    public function updatePassword(int $id, string $newPassword): void {
        $this->execute(
            "UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?",
            [password_hash($newPassword, PASSWORD_BCRYPT), $id]
        );
    }

    public function softDelete(int $id): void {
        $this->execute(
            "UPDATE users SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    public function recordLastLogin(int $id): void {
        $this->execute(
            "UPDATE users SET last_login_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    // Auth helper

    public function verifyPassword(string $plainPassword, string $hash): bool {
        return password_verify($plainPassword, $hash);
    }

    public function generateEmployeeCode(): string {
        $last = $this->queryOne(
            "SELECT employee_code FROM users ORDER BY id DESC LIMIT 1"
        );
        if ($last && preg_match('/EMP(\d+)/', $last['employee_code'], $m)) {
            return 'EMP' . str_pad((int)$m[1] + 1, 4, '0', STR_PAD_LEFT);
        }
        return 'EMP0001';
    }
}

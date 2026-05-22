<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/UserModel.php';
require_once APP_PATH . '/models/LeaveRequestModel.php';
require_once APP_PATH . '/models/WorkRecordModel.php';
require_once APP_PATH . '/models/SystemRecordModel.php';

/**
 * AjaxController - Dedicated controller to handle AJAX requests returning JSON
 */
class AjaxController extends Controller {

    private UserModel         $userModel;
    private LeaveRequestModel $leaveModel;
    private WorkRecordModel   $workModel;
    private SystemRecordModel $sysModel;

    public function __construct() {
        $this->userModel  = new UserModel();
        $this->leaveModel = new LeaveRequestModel();
        $this->workModel  = new WorkRecordModel();
        $this->sysModel   = new SystemRecordModel();
    }

    /**
     * AJAX: Live search employees — returns JSON list
     * Route: GET /ajax/employees/search
     */
    public function employeeSearch(): void {
        $q          = trim($this->get('q', ''));
        $department = trim($this->get('department', ''));
        $role       = trim($this->get('role', ''));
        $status     = trim($this->get('status', ''));

        $filters = compact('q', 'department', 'role', 'status');
        $employees = $this->userModel->findFiltered($filters, 1, 50);

        $roleMap   = ['ADMIN' => 'Quản trị viên', 'EMPLOYEE' => 'Nhân viên'];
        $statusMap = ['ACTIVE' => 'Hoạt động', 'LOCKED' => 'Tạm khóa'];

        $result = array_map(function ($emp) use ($roleMap, $statusMap) {
            return [
                'id'         => $emp['id'],
                'full_name'  => $emp['full_name'],
                'email'      => $emp['email'],
                'role'       => $emp['role'],
                'role_label' => $roleMap[$emp['role']] ?? $emp['role'],
                'status'     => $emp['status'],
                'status_label' => $statusMap[$emp['status']] ?? $emp['status'],
                'department' => $emp['department'] ?? '',
                'position'   => $emp['position'] ?? '',
                'initials'   => mb_strtoupper(mb_substr($emp['full_name'], 0, 1)),
            ];
        }, $employees);

        $this->json(['success' => true, 'data' => $result, 'total' => count($result)]);
    }

    /**
     * AJAX: Toggle employee active/locked status — returns JSON
     * Route: POST /ajax/employees/toggle-status
     */
    public function employeeToggleStatus(): void {
        $id = (int)$this->post('id');

        if ($id === Auth::id()) {
            $this->json(['success' => false, 'message' => 'Không thể tự khóa tài khoản của chính mình.']);
        }

        $employee = $this->userModel->findById($id);
        if (!$employee) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy nhân viên.']);
        }

        $newStatus = $employee['status'] === 'ACTIVE' ? 'LOCKED' : 'ACTIVE';
        $this->userModel->updateStatus($id, $newStatus);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'employee_id'         => $id,
            'related_entity_type' => 'users',
            'related_entity_id'   => $id,
            'action'              => "SET_STATUS_{$newStatus}",
            'old_value'           => ['status' => $employee['status']],
            'new_value'           => ['status' => $newStatus],
            'description'         => "Admin thay đổi trạng thái tài khoản ID {$id} -> {$newStatus}",
        ]);

        $label = $newStatus === 'LOCKED' ? 'Tạm khóa' : 'Hoạt động';
        $this->json(['success' => true, 'new_status' => $newStatus, 'label' => $label]);
    }

    /**
     * AJAX: Approve leave request — returns JSON
     * Route: POST /ajax/leave/approve
     */
    public function leaveApprove(): void {
        $id    = (int)$this->post('id');
        $leave = $this->leaveModel->findById($id);

        if (!$leave || $leave['status'] !== 'PENDING') {
            $this->json(['success' => false, 'message' => 'Đơn không hợp lệ hoặc đã được xử lý.']);
        }

        $employeeId = (int)$leave['employee_id'];
        $this->leaveModel->approve($id, Auth::id());
        $removed = $this->workModel->removeAssignmentsByEmployeeInRange(
            $employeeId, $leave['start_date'], $leave['end_date'],
            'AUTO_REMOVED_DUE_TO_APPROVED_LEAVE'
        );

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'employee_id'         => $employeeId,
            'related_entity_type' => 'leave_requests',
            'related_entity_id'   => $id,
            'action'              => 'APPROVE_LEAVE',
            'old_value'           => ['status' => 'PENDING'],
            'new_value'           => ['status' => 'APPROVED'],
            'description'         => "Duyệt đơn nghỉ phép, gỡ " . count($removed) . " ca đã phân công.",
        ]);

        $this->json(['success' => true, 'message' => 'Đã duyệt đơn nghỉ phép.']);
    }

    /**
     * AJAX: Reject leave request — returns JSON
     * Route: POST /ajax/leave/reject
     */
    public function leaveReject(): void {
        $id     = (int)$this->post('id');
        $reason = trim($this->post('rejection_reason', ''));
        $leave  = $this->leaveModel->findById($id);

        if (!$leave) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy đơn.']);
        }
        if (empty($reason)) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập lý do từ chối.']);
        }

        $this->leaveModel->reject($id, Auth::id(), $reason);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'employee_id'         => (int)$leave['employee_id'],
            'related_entity_type' => 'leave_requests',
            'related_entity_id'   => $id,
            'action'              => 'REJECT_LEAVE',
            'old_value'           => ['status' => 'PENDING'],
            'new_value'           => ['status' => 'REJECTED', 'reason' => $reason],
        ]);

        $this->json(['success' => true, 'message' => 'Đã từ chối đơn nghỉ phép.']);
    }

    /**
     * AJAX: Update attendance record — returns JSON
     * Route: POST /ajax/attendance/update
     */
    public function attendanceUpdate(): void {
        $id  = (int)$this->post('id');
        $row = $this->workModel->getAttendanceById($id);

        if (!$row) {
            $this->json(['success' => false, 'message' => 'Không tìm thấy bản ghi chấm công.']);
        }

        $checkIn  = $this->post('check_in_at');
        $checkOut = $this->post('check_out_at');
        $note     = trim($this->post('note', ''));

        if (!$checkIn || !$checkOut) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập đầy đủ giờ vào/ra.']);
        }
        if (empty($note)) {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập lý do chỉnh sửa.']);
        }

        $workedMin = (int)((strtotime($checkOut) - strtotime($checkIn)) / 60 - ($row['break_minutes'] ?? 30));
        $workedMin = max(0, $workedMin);

        $this->workModel->updateAttendance($id, [
            'check_in_at'      => $checkIn,
            'check_out_at'     => $checkOut,
            'worked_minutes'   => $workedMin,
            'late_minutes'     => (int)$this->post('late_minutes', 0),
            'overtime_minutes' => (int)$this->post('overtime_minutes', 0),
            'note'             => $note,
        ]);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'employee_id'         => (int)$row['employee_id'],
            'related_entity_type' => 'work_records',
            'related_entity_id'   => $id,
            'action'              => 'UPDATE_ATTENDANCE',
            'old_value'           => $row,
            'new_value'           => ['check_in_at' => $checkIn, 'check_out_at' => $checkOut, 'note' => $note],
            'description'         => "Admin chỉnh sửa chấm công ID {$id}. Lý do: {$note}",
        ]);

        $this->json([
            'success'      => true,
            'message'      => 'Đã cập nhật chấm công.',
            'check_in'     => date('H:i', strtotime($checkIn)),
            'check_out'    => date('H:i', strtotime($checkOut)),
            'worked_hours' => round($workedMin / 60, 1),
        ]);
    }
}

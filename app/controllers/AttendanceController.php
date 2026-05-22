<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/WorkRecordModel.php';
require_once APP_PATH . '/models/UserModel.php';
require_once APP_PATH . '/models/SystemRecordModel.php';

/**
 * AttendanceController - view and admin-edit attendance records
 */
class AttendanceController extends Controller {

    private WorkRecordModel   $workModel;
    private UserModel         $userModel;
    private SystemRecordModel $sysModel;

    public function __construct() {
        $this->workModel = new WorkRecordModel();
        $this->userModel = new UserModel();
        $this->sysModel  = new SystemRecordModel();
    }

    public function index(): void {
        $month     = $this->get('month', date('Y-m'));
        $startDate = $month . '-01';
        $endDate   = date('Y-m-t', strtotime($startDate));

        if (Auth::isAdmin()) {
            $employeeId = (int)$this->get('employee', 0);
            $employees  = $this->userModel->findAll();

            $attendance = $employeeId
                ? $this->workModel->getAttendanceByEmployee($employeeId, $startDate, $endDate)
                : [];
        } else {
            $employeeId = Auth::id();
            $employees  = [];
            $attendance = $this->workModel->getAttendanceByEmployee($employeeId, $startDate, $endDate);
        }

        $this->render('attendance/index', [
            'attendance'  => $attendance,
            'employees'   => $employees,
            'employeeId'  => $employeeId,
            'month'       => $month,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'pageCSS'     => 'pages/attendance.css',
        ]);
    }

    public function update(): void {
        $id  = (int)$this->post('id');
        $row = $this->workModel->getAttendanceById($id);
        if (!$row) $this->abort(404);

        $checkIn  = $this->post('check_in_at');
        $checkOut = $this->post('check_out_at');
        $note     = $this->post('note');

        if (!$checkIn || !$checkOut) {
            Session::flash('error', 'Vui lòng nhập đầy đủ giờ vào/ra.');
            $this->redirect('/attendance');
        }

        // Recalculate minutes
        $workedMin   = (int)((strtotime($checkOut) - strtotime($checkIn)) / 60 - ($row['break_minutes'] ?? 30));
        $workedMin   = max(0, $workedMin);
        $lateMin     = 0; // simplified: admin can fill
        $overtimeMin = 0;

        $old = $row;

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
            'old_value'           => $old,
            'new_value'           => ['check_in_at' => $checkIn, 'check_out_at' => $checkOut, 'note' => $note],
            'description'         => "Admin chỉnh sửa chấm công ID {$id}. Lý do: {$note}",
        ]);

        Session::flash('success', 'Đã cập nhật chấm công.');
        $this->redirect('/attendance');
    }
}


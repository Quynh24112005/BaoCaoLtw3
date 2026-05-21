<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/WorkRecordModel.php';
require_once APP_PATH . '/models/LeaveRequestModel.php';
require_once APP_PATH . '/models/UserModel.php';
require_once APP_PATH . '/models/SystemRecordModel.php';

/**
 * ScheduleController - manages shift periods, slots, registrations and assignments
 */
class ScheduleController extends Controller {

    private WorkRecordModel   $workModel;
    private LeaveRequestModel $leaveModel;
    private UserModel         $userModel;
    private SystemRecordModel $sysModel;

    public function __construct() {
        $this->workModel  = new WorkRecordModel();
        $this->leaveModel = new LeaveRequestModel();
        $this->userModel  = new UserModel();
        $this->sysModel   = new SystemRecordModel();
    }

    // -------------------------------------------------------------------------
    // List schedule periods
    // -------------------------------------------------------------------------
    public function index(): void {
        $periods = $this->workModel->getAllPeriods();

        $activePeriodId = (int)$this->get('period');
        $slots          = [];
        $slotDetails    = [];

        if ($activePeriodId) {
            $slots = $this->workModel->getSlotsByPeriod($activePeriodId);
            foreach ($slots as $slot) {
                $slotDetails[$slot['id']] = [
                    'assignments' => $this->workModel->getAssignmentsBySlot((int)$slot['id']),
                    'registrations' => Auth::isAdmin()
                        ? $this->workModel->getRegistrationsBySlot((int)$slot['id'])
                        : [],
                ];
            }
        }

        $employees = Auth::isAdmin() ? $this->userModel->findAll() : [];

        $this->render('schedule/index', [
            'periods'        => $periods,
            'slots'          => $slots,
            'slotDetails'    => $slotDetails,
            'activePeriodId' => $activePeriodId,
            'employees'      => $employees,
            'pageCSS'        => 'pages/schedule.css',
        ]);
    }

    // -------------------------------------------------------------------------
    // Create period form
    // -------------------------------------------------------------------------
    public function createPeriod(): void {
        $this->render('schedule/create_period', ['errors' => []]);
    }

    public function storePeriod(): void {
        $weekStart = $this->post('week_start_date');
        $weekEnd   = $this->post('week_end_date');
        $note      = $this->post('note');
        $errors    = [];

        if (!$weekStart || !$weekEnd)     $errors[] = 'Vui lòng chọn đầy đủ ngày bắt đầu và kết thúc tuần.';
        if ($weekStart > $weekEnd)        $errors[] = 'Ngày bắt đầu phải trước ngày kết thúc.';

        if (!empty($errors)) {
            $this->render('schedule/create_period', ['errors' => $errors]);
            return;
        }

        $periodId = $this->workModel->createPeriod([
            'week_start_date' => $weekStart,
            'week_end_date'   => $weekEnd,
            'note'            => $note,
            'created_by'      => Auth::id(),
        ]);

        // Auto-generate default shift slots
        $this->generateDefaultSlots($periodId, $weekStart, $weekEnd);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'related_entity_type' => 'work_records',
            'related_entity_id'   => $periodId,
            'action'              => 'CREATE_SCHEDULE_PERIOD',
            'description'         => "Tạo kỳ lịch tuần {$weekStart} - {$weekEnd}",
        ]);

        Session::flash('success', 'Tạo kỳ lịch thành công.');
        $this->redirect("/schedule?period={$periodId}");
    }

    // -------------------------------------------------------------------------
    // Open registration
    // -------------------------------------------------------------------------
    public function openRegistration(): void {
        $id     = (int)$this->post('period_id');
        $period = $this->workModel->getPeriodById($id);
        if (!$period) $this->abort(404);

        $this->workModel->updatePeriodStatus($id, 'REGISTRATION_OPEN', [
            'registration_open_at'  => date('Y-m-d H:i:s'),
            'registration_close_at' => $this->post('close_at'),
        ]);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'related_entity_type' => 'work_records',
            'related_entity_id'   => $id,
            'action'              => 'OPEN_REGISTRATION',
        ]);

        Session::flash('success', 'Đã mở đăng ký ca.');
        $this->redirect("/schedule?period={$id}");
    }

    // -------------------------------------------------------------------------
    // Publish schedule
    // -------------------------------------------------------------------------
    public function publish(): void {
        $id     = (int)$this->post('period_id');
        $period = $this->workModel->getPeriodById($id);
        if (!$period) $this->abort(404);

        $this->workModel->updatePeriodStatus($id, 'PUBLISHED', [
            'published_at' => date('Y-m-d H:i:s'),
        ]);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'related_entity_type' => 'work_records',
            'related_entity_id'   => $id,
            'action'              => 'PUBLISH_SCHEDULE',
            'description'         => "Admin publish lịch kỳ ID {$id}",
        ]);

        Session::flash('success', 'Lịch đã được publish.');
        $this->redirect("/schedule?period={$id}");
    }

    // -------------------------------------------------------------------------
    // Employee registers for a slot
    // -------------------------------------------------------------------------
    public function registerSlot(): void {
        $slotId = (int)$this->post('slot_id');
        $slot   = $this->workModel->getSlotById($slotId);

        if (!$slot || $slot['record_status'] !== 'OPEN') {
            Session::flash('error', 'Ca này không còn mở đăng ký.');
            $this->redirect('/schedule');
        }

        $period = $this->workModel->getPeriodById((int)$slot['parent_id']);
        if (!$period || $period['record_status'] !== 'REGISTRATION_OPEN') {
            Session::flash('error', 'Thời gian đăng ký đã đóng.');
            $this->redirect('/schedule');
        }

        $employeeId   = Auth::id();
        $blockedDates = $this->leaveModel->getBlockedDates($employeeId);

        if (in_array($slot['work_date'], $blockedDates, true)) {
            Session::flash('error', 'Bạn có đơn nghỉ phép trong ngày này.');
            $this->redirect("/schedule?period={$slot['parent_id']}");
        }

        $existing = $this->workModel->getRegistrationByEmployeeAndSlot($employeeId, $slotId);
        if ($existing) {
            Session::flash('error', 'Bạn đã đăng ký ca này rồi.');
            $this->redirect("/schedule?period={$slot['parent_id']}");
        }

        $this->workModel->createRegistration([
            'slot_id'     => $slotId,
            'employee_id' => $employeeId,
            'note'        => $this->post('note'),
        ]);

        Session::flash('success', 'Đăng ký ca thành công.');
        $this->redirect("/schedule?period={$slot['parent_id']}");
    }

    // -------------------------------------------------------------------------
    // Admin assigns an employee to a slot
    // -------------------------------------------------------------------------
    public function assign(): void {
        $slotId     = (int)$this->post('slot_id');
        $employeeId = (int)$this->post('employee_id');
        $slot       = $this->workModel->getSlotById($slotId);
        $employee   = $this->userModel->findById($employeeId);

        if (!$slot || !$employee) $this->abort(404);

        // Check leave conflict
        $blockedDates = $this->leaveModel->getBlockedDates($employeeId);
        if (in_array($slot['work_date'], $blockedDates, true)) {
            Session::flash('error', "Nhân viên {$employee['full_name']} có đơn nghỉ phép trong ngày này.");
            $this->redirect("/schedule?period={$slot['parent_id']}");
        }

        // Check time overlap
        if ($this->workModel->hasConflictingAssignment(
            $employeeId, $slot['work_date'], $slot['start_time'], $slot['end_time']
        )) {
            Session::flash('error', "Nhân viên {$employee['full_name']} đã có ca trùng giờ.");
            $this->redirect("/schedule?period={$slot['parent_id']}");
        }

        $this->workModel->createAssignment([
            'slot_id'     => $slotId,
            'employee_id' => $employeeId,
            'work_date'   => $slot['work_date'],
            'shift_name'  => $slot['shift_name'],
            'start_time'  => $slot['start_time'],
            'end_time'    => $slot['end_time'],
            'created_by'  => Auth::id(),
        ]);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'employee_id'         => $employeeId,
            'related_entity_type' => 'work_records',
            'related_entity_id'   => $slotId,
            'action'              => 'ASSIGN_SHIFT',
            'description'         => "Phân ca {$slot['shift_name']} ngày {$slot['work_date']} cho nhân viên ID {$employeeId}",
        ]);

        Session::flash('success', 'Phân ca thành công.');
        $this->redirect("/schedule?period={$slot['parent_id']}");
    }

    // -------------------------------------------------------------------------
    // Admin removes an assignment
    // -------------------------------------------------------------------------
    public function removeAssignment(): void {
        $id     = (int)$this->post('assignment_id');
        $reason = $this->post('reason', 'Gỡ thủ công bởi Admin');

        $this->workModel->removeAssignment($id, $reason);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'related_entity_type' => 'work_records',
            'related_entity_id'   => $id,
            'action'              => 'REMOVE_ASSIGNMENT',
            'description'         => $reason,
        ]);

        Session::flash('success', 'Đã gỡ phân ca.');
        $this->redirect('/schedule');
    }

    // -------------------------------------------------------------------------
    // Helper: generate 3 default shifts per day for a week period
    // -------------------------------------------------------------------------
    private function generateDefaultSlots(int $periodId, string $weekStart, string $weekEnd): void {
        $defaultShifts = [
            ['shift_name' => 'Ca Sáng',  'start_time' => '06:00:00', 'end_time' => '14:00:00', 'is_night_shift' => 0],
            ['shift_name' => 'Ca Chiều', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'is_night_shift' => 0],
            ['shift_name' => 'Ca Đêm',   'start_time' => '22:00:00', 'end_time' => '06:00:00', 'is_night_shift' => 1],
        ];

        $current = new DateTime($weekStart);
        $end     = new DateTime($weekEnd);
        $end->modify('+1 day');

        while ($current < $end) {
            $date = $current->format('Y-m-d');
            foreach ($defaultShifts as $shift) {
                $this->workModel->createSlot(array_merge($shift, [
                    'period_id'         => $periodId,
                    'work_date'         => $date,
                    'break_minutes'     => 30,
                    'required_headcount' => 2,
                    'created_by'        => Auth::id(),
                ]));
            }
            $current->modify('+1 day');
        }
    }
}

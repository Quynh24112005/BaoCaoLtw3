<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/LeaveRequestModel.php';
require_once APP_PATH . '/models/WorkRecordModel.php';
require_once APP_PATH . '/models/SystemRecordModel.php';

/**
 * LeaveController - employee leave request lifecycle
 */
class LeaveController extends Controller {

    private LeaveRequestModel $leaveModel;
    private WorkRecordModel   $workModel;
    private SystemRecordModel $sysModel;

    public function __construct() {
        $this->leaveModel = new LeaveRequestModel();
        $this->workModel  = new WorkRecordModel();
        $this->sysModel   = new SystemRecordModel();
    }

    public function index(): void {
        $page = max(1, (int)$this->get('page', 1));

        if (Auth::isAdmin()) {
            $status  = $this->get('status', '');
            $leaves  = $this->leaveModel->findAll($status, $page);
            $total   = $this->leaveModel->countAll($status);
        } else {
            $leaves = $this->leaveModel->findByEmployee(Auth::id(), $page);
            $total  = count($leaves);
            $status = '';
        }

        $this->render('leave/index', [
            'leaves'  => $leaves,
            'total'   => $total,
            'page'    => $page,
            'perPage' => 20,
            'status'  => $status,
            'pageCSS' => 'pages/leave.css',
        ]);
    }

    public function create(): void {
        $this->render('leave/create', ['errors' => [], 'old' => [], 'pageCSS' => 'pages/leave.css']);
    }

    public function store(): void {
        $employeeId = Auth::id();
        $startDate  = $this->post('start_date');
        $endDate    = $this->post('end_date');
        $leaveType  = $this->post('leave_type');
        $reason     = trim($this->post('reason', ''));
        $errors     = [];

        if (!$startDate || !$endDate)          $errors[] = 'Vui lòng chọn ngày bắt đầu và kết thúc.';
        if ($startDate > $endDate)             $errors[] = 'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.';
        if (empty($leaveType))                 $errors[] = 'Vui lòng chọn loại nghỉ phép.';
        if (strlen($reason) < 5)               $errors[] = 'Lý do nghỉ phải ít nhất 5 ký tự.';

        if (empty($errors) && $this->leaveModel->hasOverlap($employeeId, $startDate, $endDate)) {
            $errors[] = 'Bạn đã có đơn nghỉ trùng khoảng thời gian này.';
        }

        if (!empty($errors)) {
            $this->render('leave/create', [
                'errors' => $errors,
                'old'    => $_POST,
            ]);
            return;
        }

        $this->leaveModel->create([
            'employee_id' => $employeeId,
            'leave_type'  => $leaveType,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'reason'      => $reason,
        ]);

        Session::flash('success', 'Gửi đơn nghỉ phép thành công. Chờ duyệt.');
        $this->redirect('/leave');
    }

    public function cancel(): void {
        $id    = (int)$this->post('id');
        $leave = $this->leaveModel->findById($id);

        if (!$leave) $this->abort(404);

        // Ownership check: employee can only cancel their own PENDING request
        if (!Auth::isAdmin() && (int)$leave['employee_id'] !== Auth::id()) {
            $this->abort(403);
        }

        if ($leave['status'] !== 'PENDING') {
            Session::flash('error', 'Chỉ có thể hủy đơn đang chờ duyệt.');
            $this->redirect('/leave');
        }

        $this->leaveModel->cancel($id);

        Session::flash('success', 'Đã hủy đơn nghỉ phép.');
        $this->redirect('/leave');
    }

    public function approve(): void {
        $id    = (int)$this->post('id');
        $leave = $this->leaveModel->findById($id);
        if (!$leave || $leave['status'] !== 'PENDING') $this->abort(404);

        $employeeId = (int)$leave['employee_id'];

        // Use transaction: approve + remove conflicting assignments
        $this->leaveModel->approve($id, Auth::id());

        $removedAssignments = $this->workModel->removeAssignmentsByEmployeeInRange(
            $employeeId,
            $leave['start_date'],
            $leave['end_date'],
            'AUTO_REMOVED_DUE_TO_APPROVED_LEAVE'
        );

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'employee_id'         => $employeeId,
            'related_entity_type' => 'leave_requests',
            'related_entity_id'   => $id,
            'action'              => 'APPROVE_LEAVE',
            'old_value'           => ['status' => 'PENDING'],
            'new_value'           => ['status' => 'APPROVED', 'removed_assignments' => count($removedAssignments)],
            'description'         => "Duyệt đơn nghỉ phép, gỡ " . count($removedAssignments) . " ca đã phân công.",
        ]);

        Session::flash('success', 'Đã duyệt đơn nghỉ phép.');
        $this->redirect('/leave');
    }

    public function reject(): void {
        $id     = (int)$this->post('id');
        $reason = trim($this->post('rejection_reason', ''));
        $leave  = $this->leaveModel->findById($id);

        if (!$leave) $this->abort(404);

        if (empty($reason)) {
            Session::flash('error', 'Vui lòng nhập lý do từ chối.');
            $this->redirect('/leave');
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

        Session::flash('success', 'Đã từ chối đơn nghỉ phép.');
        $this->redirect('/leave');
    }

    /**
     * AJAX: Approve leave request — returns JSON
     */
    public function ajaxApprove(): void {
        header('Content-Type: application/json; charset=utf-8');
        $id    = (int)$this->post('id');
        $leave = $this->leaveModel->findById($id);

        if (!$leave || $leave['status'] !== 'PENDING') {
            echo json_encode(['success' => false, 'message' => 'Đơn không hợp lệ hoặc đã được xử lý.']);
            exit;
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

        echo json_encode(['success' => true, 'message' => 'Đã duyệt đơn nghỉ phép.']);
        exit;
    }

    /**
     * AJAX: Reject leave request — returns JSON
     */
    public function ajaxReject(): void {
        header('Content-Type: application/json; charset=utf-8');
        $id     = (int)$this->post('id');
        $reason = trim($this->post('rejection_reason', ''));
        $leave  = $this->leaveModel->findById($id);

        if (!$leave) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn.']);
            exit;
        }
        if (empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập lý do từ chối.']);
            exit;
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

        echo json_encode(['success' => true, 'message' => 'Đã từ chối đơn nghỉ phép.']);
        exit;
    }
}

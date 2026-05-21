<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/UserModel.php';
require_once APP_PATH . '/models/SystemRecordModel.php';

/**
 * EmployeeController - Admin manages employee accounts
 */
class EmployeeController extends Controller {

    private UserModel         $userModel;
    private SystemRecordModel $sysModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->sysModel  = new SystemRecordModel();
    }

    public function index(): void {
        $page = max(1, (int)$this->get('page', 1));
        $perPage = 20;

        $filters = [
            'q'          => trim($this->get('q', '')),
            'department' => trim($this->get('department', '')),
            'role'       => trim($this->get('role', '')),
            'status'     => trim($this->get('status', '')),
        ];

        $employees = $this->userModel->findFiltered($filters, $page, $perPage);
        $total = $this->userModel->countFiltered($filters);
        
        $departments = $this->userModel->getDepartments();
        $stats = $this->userModel->getDashboardStats();

        $this->render('employees/index', [
            'employees'   => $employees,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'filters'     => $filters,
            'keyword'     => $filters['q'],
            'departments' => $departments,
            'stats'       => $stats,
            'pageCSS'     => 'pages/employees.css',
        ]);
    }

    public function create(): void {
        $this->render('employees/create', ['errors' => [], 'old' => []]);
    }

    public function store(): void {
        $data   = $_POST;
        $errors = $this->validateEmployee($data);

        // Check email uniqueness
        if (empty($errors) && $this->userModel->findByEmail($data['email'])) {
            $errors[] = 'Email này đã được sử dụng.';
        }

        if (!empty($errors)) {
            $this->render('employees/create', ['errors' => $errors, 'old' => $data]);
            return;
        }

        $data['employee_code'] = $this->userModel->generateEmployeeCode();
        $newId = $this->userModel->create($data);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'related_entity_type' => 'users',
            'related_entity_id'   => $newId,
            'action'              => 'CREATE_EMPLOYEE',
            'new_value'           => ['email' => $data['email'], 'role' => $data['role']],
            'description'         => "Admin tạo tài khoản nhân viên: {$data['full_name']}",
        ]);

        Session::flash('success', 'Tạo nhân viên thành công.');
        $this->redirect('/employees');
    }

    public function edit(): void {
        $id       = (int)$this->get('id');
        $employee = $this->userModel->findById($id);
        if (!$employee) $this->abort(404, 'Không tìm thấy nhân viên.');

        $this->render('employees/edit', ['employee' => $employee, 'errors' => []]);
    }

    public function update(): void {
        $id       = (int)$this->post('id');
        $employee = $this->userModel->findById($id);
        if (!$employee) $this->abort(404);

        $old    = $employee;
        $data   = $_POST;
        $errors = [];

        if (empty($data['full_name'])) $errors[] = 'Họ tên không được để trống.';

        if (!empty($errors)) {
            $this->render('employees/edit', ['employee' => $employee, 'errors' => $errors]);
            return;
        }

        $this->userModel->update($id, $data);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'employee_id'         => $id,
            'related_entity_type' => 'users',
            'related_entity_id'   => $id,
            'action'              => 'UPDATE_EMPLOYEE',
            'old_value'           => $old,
            'new_value'           => $data,
            'description'         => "Admin cập nhật hồ sơ nhân viên ID {$id}",
        ]);

        Session::flash('success', 'Cập nhật thành công.');
        $this->redirect('/employees');
    }

    public function toggleStatus(): void {
        $id       = (int)$this->post('id');
        $employee = $this->userModel->findById($id);
        if (!$employee) $this->abort(404);

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

        Session::flash('success', "Tài khoản đã được " . ($newStatus === 'LOCKED' ? 'khóa' : 'mở khóa') . ".");
        $this->redirect('/employees');
    }

    public function delete(): void {
        $id = (int)$this->post('id');
        
        if ($id === Auth::id()) {
            Session::flash('error', 'Bạn không thể tự xóa tài khoản của chính mình.');
            $this->redirect('/employees');
            return;
        }

        $employee = $this->userModel->findById($id);
        if (!$employee) $this->abort(404);

        $this->userModel->softDelete($id);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'employee_id'         => $id,
            'related_entity_type' => 'users',
            'related_entity_id'   => $id,
            'action'              => 'DELETE_EMPLOYEE',
            'old_value'           => $employee,
            'description'         => "Admin xóa nhân viên ID {$id} ({$employee['full_name']})",
        ]);

        Session::flash('success', "Đã xóa nhân viên {$employee['full_name']} thành công.");
        $this->redirect('/employees');
    }

    /**
     * AJAX: Live search employees — returns JSON list
     */
    public function ajaxSearch(): void {
        header('Content-Type: application/json; charset=utf-8');
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

        echo json_encode(['success' => true, 'data' => $result, 'total' => count($result)]);
        exit;
    }

    /**
     * AJAX: Toggle employee active/locked status — returns JSON
     */
    public function ajaxToggleStatus(): void {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)$this->post('id');

        if ($id === Auth::id()) {
            echo json_encode(['success' => false, 'message' => 'Không thể tự khóa tài khoản của chính mình.']);
            exit;
        }

        $employee = $this->userModel->findById($id);
        if (!$employee) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy nhân viên.']);
            exit;
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
        echo json_encode(['success' => true, 'new_status' => $newStatus, 'label' => $label]);
        exit;
    }

    private function validateEmployee(array $data): array {
        $errors = [];
        if (empty($data['full_name']))   $errors[] = 'Họ tên không được để trống.';
        if (empty($data['email']))       $errors[] = 'Email không được để trống.';
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = 'Mật khẩu ít nhất 8 ký tự.';
        }
        return $errors;
    }
}

<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/UserModel.php';
require_once APP_PATH . '/models/SystemRecordModel.php';

/**
 * ProfileController - employees view/edit their own profile & change password
 */
class ProfileController extends Controller {

    private UserModel         $userModel;
    private SystemRecordModel $sysModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->sysModel  = new SystemRecordModel();
    }

    public function index(): void {
        $user = $this->userModel->findById(Auth::id());
        $this->render('profile/index', ['user' => $user, 'errors' => [], 'success' => '', 'pageCSS' => 'pages/profile.css']);
    }

    public function edit(): void {
        $user = $this->userModel->findById(Auth::id());

        if (!$this->isPost()) {
            $this->render('profile/edit', ['user' => $user, 'errors' => $errors, 'pageCSS' => 'pages/profile.css']);
            return;
        }

        $errors = [];

        // Allowed fields for self-update
        $updatable = ['full_name', 'phone', 'date_of_birth', 'gender'];
        $data = array_intersect_key($_POST, array_flip($updatable));

        if (empty($data['full_name'])) {
            $errors[] = 'Họ tên không được để trống.';
        }

        // Password change (optional)
        $currentPwd = $this->post('current_password', '');
        $newPwd     = $this->post('new_password', '');
        $confirmPwd = $this->post('confirm_password', '');

        $changePwd = !empty($currentPwd) || !empty($newPwd);
        if ($changePwd) {
            if (!$this->userModel->verifyPassword($currentPwd, $user['password_hash'])) {
                $errors[] = 'Mật khẩu hiện tại không đúng.';
            } elseif (strlen($newPwd) < 8) {
                $errors[] = 'Mật khẩu mới ít nhất 8 ký tự.';
            } elseif ($newPwd !== $confirmPwd) {
                $errors[] = 'Xác nhận mật khẩu không khớp.';
            }
        }

        if (!empty($errors)) {
            $this->render('profile/edit', ['user' => $user, 'errors' => $errors]);
            return;
        }

        $this->userModel->update(Auth::id(), $data);

        if ($changePwd) {
            $this->userModel->updatePassword(Auth::id(), $newPwd);
        }

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'employee_id'         => Auth::id(),
            'related_entity_type' => 'users',
            'related_entity_id'   => Auth::id(),
            'action'              => 'UPDATE_PROFILE',
            'description'         => 'Nhân viên tự cập nhật hồ sơ',
        ]);

        Session::flash('success', 'Cập nhật hồ sơ thành công.');
        $this->redirect('/profile');
    }
}

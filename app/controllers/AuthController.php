<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/UserModel.php';

/**
 * AuthController - login / logout
 */
class AuthController extends Controller {

    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login(): void {
        // Already logged in -> redirect to home
        if (Auth::check()) {
            $this->redirect('/home');
        }

        $errors = [];

        if ($this->isPost()) {
            $email    = trim($this->post('email', ''));
            $password = $this->post('password', '');

            if (empty($email) || empty($password)) {
                $errors[] = 'Vui lòng nhập email và mật khẩu.';
            } else {
                $user = $this->userModel->findByEmail($email);

                if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
                    $errors[] = 'Email hoặc mật khẩu không chính xác.';
                } elseif ($user['status'] !== 'ACTIVE') {
                    $errors[] = 'Tài khoản của bạn đã bị khóa hoặc vô hiệu hóa.';
                } else {
                    Auth::login($user);
                    $this->userModel->recordLastLogin((int)$user['id']);
                    $this->redirect('/home');
                }
            }
        }

        $this->render('auth/login', ['errors' => $errors], 'auth');
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/auth/login');
    }
}

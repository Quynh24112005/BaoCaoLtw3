<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/UserModel.php';
require_once APP_PATH . '/models/WorkRecordModel.php';
require_once APP_PATH . '/models/LeaveRequestModel.php';
require_once APP_PATH . '/models/SystemRecordModel.php';

/**
 * HomeController - dashboard for both EMPLOYEE and ADMIN
 */
class HomeController extends Controller {

    public function index(): void {
        Auth::requireLogin();

        $userId = Auth::id();
        $role   = Auth::role();

        $userModel   = new UserModel();
        $leaveModel  = new LeaveRequestModel();

        $data = [];

        if ($role === 'ADMIN') {
            $data['totalEmployees']  = $userModel->countAll();
            $data['pendingLeaves']   = $leaveModel->countAll('PENDING');
            $data['deptStats']       = $userModel->getStaffCountByDepartment();
        } else {
            $data['myLeaves']   = $leaveModel->findByEmployee($userId, 1, 5);
        }

        $data['pageCSS'] = 'pages/home.css';
        $this->render('home/index', $data);
    }
}

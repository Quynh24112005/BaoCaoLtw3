<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/UserModel.php';
require_once APP_PATH . '/models/WorkRecordModel.php';
require_once APP_PATH . '/models/LeaveRequestModel.php';
require_once APP_PATH . '/models/PayrollModel.php';
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
        $workModel   = new WorkRecordModel();
        $leaveModel  = new LeaveRequestModel();
        $payModel    = new PayrollModel();
        $sysModel    = new SystemRecordModel();

        $data = [];

        if ($role === 'ADMIN') {
            $data['totalEmployees']  = $userModel->countAll();
            $data['pendingLeaves']   = $leaveModel->countAll('PENDING');
            $data['openTickets']     = count($sysModel->getAllTickets('OPEN'));
            $data['payPeriods']      = $payModel->getAllPeriods();
            $data['deptStats']       = $userModel->getStaffCountByDepartment();
        } else {
            $weekStart = date('Y-m-d', strtotime('monday this week'));
            $weekEnd   = date('Y-m-d', strtotime('sunday this week'));

            $data['myShifts']   = $workModel->getAssignmentsByEmployee($userId, $weekStart);
            $data['myLeaves']   = $leaveModel->findByEmployee($userId, 1, 5);
            $data['myPayslips'] = $payModel->getPublishedItemsByEmployee($userId);
            $data['myTickets']  = $sysModel->getTicketsByEmployee($userId);
        }

        $data['pageCSS'] = 'pages/home.css';
        $this->render('home/index', $data);
    }
}

<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/PayrollModel.php';
require_once APP_PATH . '/models/WorkRecordModel.php';
require_once APP_PATH . '/models/UserModel.php';
require_once APP_PATH . '/models/SystemRecordModel.php';

/**
 * PayrollController - payroll period lifecycle and calculation
 */
class PayrollController extends Controller {

    private PayrollModel      $payModel;
    private WorkRecordModel   $workModel;
    private UserModel         $userModel;
    private SystemRecordModel $sysModel;

    public function __construct() {
        $this->payModel  = new PayrollModel();
        $this->workModel = new WorkRecordModel();
        $this->userModel = new UserModel();
        $this->sysModel  = new SystemRecordModel();
    }

    public function index(): void {
        if (Auth::isAdmin()) {
            $periods = $this->payModel->getAllPeriods();
            $this->render('payroll/index', ['periods' => $periods, 'pageCSS' => 'pages/payroll.css']);
        } else {
            $items = $this->payModel->getPublishedItemsByEmployee(Auth::id());
            $this->render('payroll/my_payslips', ['items' => $items, 'pageCSS' => 'pages/payroll.css']);
        }
    }

    public function createPeriod(): void {
        $this->render('payroll/create_period', ['errors' => []]);
    }

    public function storePeriod(): void {
        $name  = trim($this->post('name', ''));
        $start = $this->post('period_start');
        $end   = $this->post('period_end');
        $errors = [];

        if (empty($name))   $errors[] = 'Tên kỳ lương không được để trống.';
        if (!$start || !$end) $errors[] = 'Vui lòng chọn ngày bắt đầu và kết thúc.';
        if ($start > $end)    $errors[] = 'Ngày bắt đầu phải trước ngày kết thúc.';

        if (!empty($errors)) {
            $this->render('payroll/create_period', ['errors' => $errors]);
            return;
        }

        $periodId = $this->payModel->createPeriod([
            'name'         => $name,
            'period_start' => $start,
            'period_end'   => $end,
            'created_by'   => Auth::id(),
        ]);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'related_entity_type' => 'payroll_records',
            'related_entity_id'   => $periodId,
            'action'              => 'CREATE_PAYROLL_PERIOD',
            'description'         => "Tạo kỳ lương: {$name}",
        ]);

        Session::flash('success', 'Tạo kỳ lương thành công.');
        $this->redirect('/payroll');
    }

    // -------------------------------------------------------------------------
    // Calculate payroll for all active employees in the period
    // -------------------------------------------------------------------------
    public function calculate(): void {
        $periodId = (int)$this->post('period_id');
        $period   = $this->payModel->getPeriodById($periodId);
        if (!$period) $this->abort(404);

        $employees = $this->userModel->findAll();

        foreach ($employees as $emp) {
            $summary = $this->workModel->getAttendanceSummary(
                (int)$emp['id'],
                $period['period_start'],
                $period['period_end']
            );

            // ---- Payroll Formula ----
            // Normalize: base_salary is monthly, convert to per-minute rate
            $baseSalary    = (float)$emp['base_salary'];
            $hourlyRate    = (float)$emp['hourly_rate'];
            $workedMin     = (int)$summary['total_worked'];
            $overtimeMin   = (int)$summary['total_overtime'];
            $lateMin       = (int)$summary['total_late'];

            // Base amount: hourly rate * worked hours
            $baseAmount      = round(($workedMin / 60) * $hourlyRate, 2);
            $overtimeAmount  = round(($overtimeMin / 60) * $hourlyRate * 1.5, 2);
            $allowanceAmount = 0.0;   // configurable per employee later
            $deductionAmount = round(($lateMin / 60) * $hourlyRate * 0.5, 2);
            $finalAmount     = $baseAmount + $overtimeAmount + $allowanceAmount - $deductionAmount;

            $snapshot = [
                'employee_id'        => $emp['id'],
                'base_salary'        => $baseSalary,
                'hourly_rate'        => $hourlyRate,
                'worked_minutes'     => $workedMin,
                'overtime_minutes'   => $overtimeMin,
                'late_minutes'       => $lateMin,
                'calculated_at'      => date('Y-m-d H:i:s'),
            ];

            $this->payModel->upsertItem([
                'period_id'        => $periodId,
                'employee_id'      => (int)$emp['id'],
                'period_start'     => $period['period_start'],
                'period_end'       => $period['period_end'],
                'base_amount'      => $baseAmount,
                'overtime_amount'  => $overtimeAmount,
                'allowance_amount' => $allowanceAmount,
                'deduction_amount' => $deductionAmount,
                'final_amount'     => $finalAmount,
                'snapshot'         => $snapshot,
                'created_by'       => Auth::id(),
            ]);
        }

        $this->payModel->updatePeriodStatus($periodId, 'CALCULATED');

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'related_entity_type' => 'payroll_records',
            'related_entity_id'   => $periodId,
            'action'              => 'CALCULATE_PAYROLL',
            'description'         => "Tính lương kỳ ID {$periodId} cho " . count($employees) . " nhân viên.",
        ]);

        Session::flash('success', 'Đã tính xong lương. Vui lòng kiểm tra trước khi publish.');
        $this->redirect('/payroll');
    }

    // -------------------------------------------------------------------------
    // Publish payroll period
    // -------------------------------------------------------------------------
    public function publish(): void {
        $periodId = (int)$this->post('period_id');
        $period   = $this->payModel->getPeriodById($periodId);
        if (!$period) $this->abort(404);

        // Business rule: cannot publish if open tickets exist
        if ($this->sysModel->hasOpenTicketsForPeriod($periodId)) {
            Session::flash('error', 'Không thể publish lương khi còn ticket đang mở liên quan đến kỳ này.');
            $this->redirect('/payroll');
        }

        $this->payModel->publishItems($periodId);
        $this->payModel->updatePeriodStatus($periodId, 'PUBLISHED');

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'related_entity_type' => 'payroll_records',
            'related_entity_id'   => $periodId,
            'action'              => 'PUBLISH_PAYROLL',
            'description'         => "Admin publish lương kỳ ID {$periodId}",
        ]);

        Session::flash('success', 'Đã publish phiếu lương cho nhân viên.');
        $this->redirect('/payroll');
    }

    // -------------------------------------------------------------------------
    // Employee views their own payslip details
    // -------------------------------------------------------------------------
    public function view(): void {
        $periodId = (int)$this->get('period');
        $period   = $this->payModel->getPeriodById($periodId);
        if (!$period) $this->abort(404);

        if (Auth::isAdmin()) {
            $items = $this->payModel->getItemsByPeriod($periodId);
            $this->render('payroll/view_period', ['period' => $period, 'items' => $items, 'pageCSS' => 'pages/payroll.css']);
        } else {
            $item = $this->payModel->getItemByEmployeeAndPeriod(Auth::id(), $periodId);
            if (!$item || $item['period_status'] !== 'PUBLISHED') {
                $this->abort(403, 'Phiếu lương chưa được công bố.');
            }
            $this->render('payroll/view_payslip', ['period' => $period, 'item' => $item, 'pageCSS' => 'pages/payroll.css']);
        }
    }
}

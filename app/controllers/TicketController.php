<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/SystemRecordModel.php';

/**
 * TicketController - complaint/grievance ticket system
 */
class TicketController extends Controller {

    private SystemRecordModel $sysModel;

    public function __construct() {
        $this->sysModel = new SystemRecordModel();
    }

    public function index(): void {
        if (Auth::isAdmin()) {
            $status  = $this->get('status', '');
            $tickets = $this->sysModel->getAllTickets($status);
        } else {
            $tickets = $this->sysModel->getTicketsByEmployee(Auth::id());
            $status  = '';
        }

        $this->render('tickets/index', ['tickets' => $tickets, 'status' => $status, 'pageCSS' => 'pages/tickets.css']);
    }

    public function create(): void {
        $this->render('tickets/create', ['errors' => [], 'old' => [], 'pageCSS' => 'pages/tickets.css']);
    }

    public function store(): void {
        $title       = trim($this->post('title', ''));
        $description = trim($this->post('description', ''));
        $entityType  = $this->post('related_entity_type', '');
        $entityId    = (int)$this->post('related_entity_id', 0);
        $errors      = [];

        if (strlen($title) < 5)           $errors[] = 'Tiêu đề phải ít nhất 5 ký tự.';
        if (strlen($description) < 10)    $errors[] = 'Nội dung khiếu nại phải ít nhất 10 ký tự.';
        if (empty($entityType))           $errors[] = 'Vui lòng chọn đối tượng khiếu nại.';

        if (!empty($errors)) {
            $this->render('tickets/create', ['errors' => $errors, 'old' => $_POST]);
            return;
        }

        $ticketId = $this->sysModel->createTicket([
            'employee_id'          => Auth::id(),
            'related_entity_type'  => $entityType,
            'related_entity_id'    => $entityId,
            'title'                => $title,
            'description'          => $description,
        ]);

        Session::flash('success', 'Gửi khiếu nại thành công. Mã ticket: #' . $ticketId);
        $this->redirect('/tickets');
    }

    public function view(): void {
        $id     = (int)$this->get('id');
        $ticket = $this->sysModel->findTicketById($id);

        if (!$ticket) $this->abort(404);

        // Ownership check: employees can only view their own tickets
        if (!Auth::isAdmin() && (int)$ticket['employee_id'] !== Auth::id()) {
            $this->abort(403);
        }

        $this->render('tickets/view', ['ticket' => $ticket, 'pageCSS' => 'pages/tickets.css']);
    }

    public function updateStatus(): void {
        $id     = (int)$this->post('id');
        $status = $this->post('status');
        $note   = trim($this->post('note', ''));

        $allowed = ['IN_PROGRESS', 'RESOLVED', 'REJECTED', 'CLOSED'];
        if (!in_array($status, $allowed, true)) {
            Session::flash('error', 'Trạng thái không hợp lệ.');
            $this->redirect('/tickets');
        }

        $this->sysModel->updateTicketStatus($id, $status, Auth::id(), $note ?: null);

        $this->sysModel->writeAudit([
            'actor_user_id'       => Auth::id(),
            'related_entity_type' => 'system_records',
            'related_entity_id'   => $id,
            'action'              => "TICKET_{$status}",
            'description'         => "Admin cập nhật ticket #{$id} -> {$status}. {$note}",
        ]);

        Session::flash('success', "Đã cập nhật ticket sang trạng thái {$status}.");
        $this->redirect('/tickets');
    }
}

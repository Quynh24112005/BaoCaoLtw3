<?php 
$pageTitle = 'Chi tiết khiếu nại #' . $ticket['id']; 
$statusMap = [
    'OPEN' => 'Mở',
    'IN_PROGRESS' => 'Đang xử lý',
    'RESOLVED' => 'Đã giải quyết',
    'REJECTED' => 'Từ chối',
    'CLOSED' => 'Đóng'
];
$entityTypeMap = [
    'payroll_period' => 'Phiếu lương',
    'work_records' => 'Lịch / Chấm công',
    'general' => 'Khác'
];
?>
<div class="page-header">
    <div><h1 class="page-title"><i data-lucide="ticket" class="header-icon"></i> Khiếu nại #<?= $ticket['id'] ?></h1>
    <p class="page-subtitle"><a href="<?= BASE_URL ?>/tickets"><i data-lucide="arrow-left" style="width: 14px; height: 14px; vertical-align: middle;"></i> Quay lại</a></p></div>
</div>
<div class="card">
    <div class="card-body">
        <div class="ticket-detail">
            <div class="ticket-row">
                <span class="ticket-key">Trạng thái</span>
                <span class="badge badge-ticket-<?= strtolower($ticket['status']) ?>"><?= $statusMap[$ticket['status']] ?? $ticket['status'] ?></span>
            </div>
            <div class="ticket-row">
                <span class="ticket-key">Người gửi</span>
                <span><?= htmlspecialchars($ticket['full_name']) ?> (<?= htmlspecialchars($ticket['employee_code']) ?>)</span>
            </div>
            <div class="ticket-row">
                <span class="ticket-key">Đối tượng</span>
                <span><?= $entityTypeMap[$ticket['related_entity_type']] ?? htmlspecialchars($ticket['related_entity_type'] ?? '—') ?> #<?= $ticket['related_entity_id'] ?? '' ?></span>
            </div>
            <div class="ticket-row">
                <span class="ticket-key">Tiêu đề</span>
                <span class="font-medium"><?= htmlspecialchars($ticket['title']) ?></span>
            </div>
            <div class="ticket-row">
                <span class="ticket-key">Nội dung</span>
                <div class="ticket-desc"><?= nl2br(htmlspecialchars($ticket['description'])) ?></div>
            </div>
            <div class="ticket-row">
                <span class="ticket-key">Ngày tạo</span>
                <span><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></span>
            </div>
            <?php if ($ticket['handled_at']): ?>
            <div class="ticket-row">
                <span class="ticket-key">Xử lý lúc</span>
                <span><?= date('d/m/Y H:i', strtotime($ticket['handled_at'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
$pageTitle = 'Khiếu nại'; 
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
    <div><h1 class="page-title"><i data-lucide="ticket" class="header-icon"></i> Hệ thống khiếu nại</h1></div>
</div>

<?php if (Auth::isAdmin()): ?>
<div class="filter-bar">
    <?php $statuses = ['' => 'Tất cả', 'OPEN' => 'Mở', 'IN_PROGRESS' => 'Đang xử lý', 'RESOLVED' => 'Đã giải quyết', 'REJECTED' => 'Từ chối', 'CLOSED' => 'Đóng']; ?>
    <?php foreach ($statuses as $val => $label): ?>
        <a href="<?= BASE_URL ?>/tickets<?= $val ? '?status='.$val : '' ?>" class="filter-btn <?= $status === $val ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <?php if (Auth::isAdmin()): ?><th>Nhân viên</th><?php endif; ?>
                    <th>Tiêu đề</th>
                    <th>Đối tượng</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr><td colspan="7" class="empty-state text-center">Không có khiếu nại nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td><code>#<?= $ticket['id'] ?></code></td>
                        <?php if (Auth::isAdmin()): ?>
                        <td>
                            <div><?= htmlspecialchars($ticket['full_name']) ?></div>
                            <div class="text-sm text-muted"><?= htmlspecialchars($ticket['employee_code']) ?></div>
                        </td>
                        <?php endif; ?>
                        <td class="font-medium"><?= htmlspecialchars($ticket['title']) ?></td>
                        <td><?= $entityTypeMap[$ticket['related_entity_type']] ?? htmlspecialchars($ticket['related_entity_type'] ?? '—') ?></td>
                        <td><span class="badge badge-ticket-<?= strtolower($ticket['status']) ?>"><?= $statusMap[$ticket['status']] ?? $ticket['status'] ?></span></td>
                        <td><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= BASE_URL ?>/tickets/view?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-ghost"><i data-lucide="eye"></i> Xem</a>
                                <?php if (Auth::isAdmin() && in_array($ticket['status'], ['OPEN', 'IN_PROGRESS'], true)): ?>
                                    <button class="btn btn-sm btn-primary" onclick="openStatusModal(<?= $ticket['id'] ?>)"><i data-lucide="settings"></i> Xử lý</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    <a href="<?= BASE_URL ?>/tickets/create" class="btn btn-primary btn-full" style="padding: 14px; font-size: 1rem; border-radius: 99px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: var(--shadow-glow);">
        <i data-lucide="plus"></i> Tạo khiếu nại
    </a>
</div>

<?php if (Auth::isAdmin()): ?>
<!-- Status modal -->
<div id="statusModal" class="modal" style="display:none">
    <div class="modal-backdrop" onclick="closeStatusModal()"></div>
    <div class="modal-box">
        <h3><i data-lucide="settings" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 6px;"></i> Cập nhật trạng thái khiếu nại</h3>
        <form method="POST" action="<?= BASE_URL ?>/tickets/update-status">
            <input type="hidden" name="id" id="statusTicketId">
            <div class="form-group">
                <label class="form-label">Trạng thái mới *</label>
                <select name="status" class="form-select" required>
                    <option value="IN_PROGRESS">Đang xử lý (IN_PROGRESS)</option>
                    <option value="RESOLVED">Đã giải quyết (RESOLVED)</option>
                    <option value="REJECTED">Từ chối (REJECTED)</option>
                    <option value="CLOSED">Đóng (CLOSED)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Ghi chú xử lý</label>
                <textarea name="note" class="form-input" rows="3" placeholder="Nhập kết quả xử lý..."></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-ghost" onclick="closeStatusModal()">Hủy</button>
                <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Cập nhật</button>
            </div>
        </form>
    </div>
</div>
<script>
function openStatusModal(id) {
    document.getElementById('statusTicketId').value = id;
    document.getElementById('statusModal').style.display = 'flex';
}
function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}
</script>
<?php endif; ?>

<?php 
$pageTitle = 'Nghỉ phép'; 
$statusMap = [
    'PENDING' => 'Chờ duyệt',
    'APPROVED' => 'Đã duyệt',
    'REJECTED' => 'Từ chối',
    'CANCELLED' => 'Đã hủy'
];
$leaveTypeMap = [
    'ANNUAL' => 'Phép năm',
    'SICK' => 'Nghỉ ốm',
    'UNPAID' => 'Không lương',
    'OTHER' => 'Khác'
];
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><i data-lucide="palmtree" class="header-icon"></i> Quản lý nghỉ phép</h1>
        <p class="page-subtitle">Tổng <?= $total ?> đơn</p>
    </div>
    <?php if (!Auth::isAdmin()): ?>
        <a href="<?= BASE_URL ?>/leave/create" class="btn btn-primary"><i data-lucide="plus"></i> Tạo đơn nghỉ</a>
    <?php endif; ?>
</div>

<?php if (Auth::isAdmin()): ?>
<div class="filter-bar">
    <?php $statuses = ['' => 'Tất cả', 'PENDING' => 'Chờ duyệt', 'APPROVED' => 'Đã duyệt', 'REJECTED' => 'Từ chối', 'CANCELLED' => 'Đã hủy']; ?>
    <?php foreach ($statuses as $val => $label): ?>
        <a href="<?= BASE_URL ?>/leave<?= $val ? '?status='.$val : '' ?>" class="filter-btn <?= $status === $val ? 'active' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <?php if (Auth::isAdmin()): ?><th>Nhân viên</th><?php endif; ?>
                    <th>Loại nghỉ</th>
                    <th>Từ ngày</th>
                    <th>Đến ngày</th>
                    <th>Lý do</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaves)): ?>
                    <tr><td colspan="8" class="empty-state text-center">Không có dữ liệu.</td></tr>
                <?php else: ?>
                    <?php foreach ($leaves as $leave): ?>
                    <tr>
                        <?php if (Auth::isAdmin()): ?>
                        <td>
                            <div class="font-medium"><?= htmlspecialchars($leave['full_name']) ?></div>
                            <div class="text-sm text-muted"><?= htmlspecialchars($leave['employee_code']) ?></div>
                        </td>
                        <?php endif; ?>
                        <td><span class="badge badge-info"><?= $leaveTypeMap[$leave['leave_type']] ?? $leave['leave_type'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($leave['start_date'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($leave['end_date'])) ?></td>
                        <td><?= htmlspecialchars(mb_substr($leave['reason'], 0, 50)) ?><?= strlen($leave['reason']) > 50 ? '...' : '' ?></td>
                        <td><span class="badge badge-<?= strtolower($leave['status']) ?>"><?= $statusMap[$leave['status']] ?? $leave['status'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($leave['created_at'])) ?></td>
                        <td>
                            <?php if (Auth::isAdmin() && $leave['status'] === 'PENDING'): ?>
                                <button class="btn btn-sm btn-success btn-ajax-approve"
                                    data-id="<?= $leave['id'] ?>">
                                    <i data-lucide="check"></i> Duyệt
                                </button>
                                <button class="btn btn-sm btn-danger btn-ajax-reject"
                                    data-id="<?= $leave['id'] ?>" onclick="showRejectForm(<?= $leave['id'] ?>)">
                                    <i data-lucide="x"></i> Từ chối
                                </button>
                            <?php elseif (!Auth::isAdmin() && $leave['status'] === 'PENDING'): ?>
                                <form method="POST" action="<?= BASE_URL ?>/leave/cancel" style="display:inline">
                                    <input type="hidden" name="id" value="<?= $leave['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Hủy đơn này?')">Hủy đơn</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted text-sm">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reject modal -->
<div id="rejectModal" class="modal" style="display:none">
    <div class="modal-backdrop" onclick="hideRejectForm()"></div>
    <div class="modal-box">
        <h3>Từ chối đơn nghỉ phép</h3>
        <div class="form-group" style="margin-top:12px;">
            <label class="form-label">Lý do từ chối *</label>
            <textarea id="rejectReason" class="form-input" rows="3" placeholder="Nhập lý do từ chối..."></textarea>
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-ghost" onclick="hideRejectForm()">Hủy</button>
            <button type="button" class="btn btn-danger" id="btnConfirmReject">Xác nhận từ chối</button>
        </div>
    </div>
</div>

<script>
const LEAVE_BASE = '<?= BASE_URL ?>';
let rejectLeaveId = null;

function showRejectForm(id) {
    rejectLeaveId = id;
    $('#rejectReason').val('');
    $('#rejectModal').css('display', 'flex');
}
function hideRejectForm() {
    $('#rejectModal').hide();
    rejectLeaveId = null;
}

$(function() {
    // =========================================================
    // AJAX: Duyệt đơn nghỉ phép
    // =========================================================
    $(document).on('click', '.btn-ajax-approve', function() {
        const btn = $(this);
        const id  = btn.data('id');
        if (!confirm('Duyệt đơn nghỉ phép này?')) return;
        btn.prop('disabled', true).text('...');

        $.ajax({
            url: LEAVE_BASE + '/ajax/leave/approve',
            method: 'POST',
            data: { id },
            success: function(res) {
                if (!res.success) { alert(res.message); btn.prop('disabled', false).html('<i data-lucide="check"></i> Duyệt'); lucide.createIcons(); return; }
                // Update row in-place
                const row = btn.closest('tr');
                row.find('td:nth-last-child(3) .badge').removeClass().addClass('badge badge-approved').text('Đã duyệt');
                row.find('td:last-child').html('<span class="text-muted text-sm">—</span>');
                showLeaveToast('✓ Đã duyệt đơn nghỉ phép thành công.', 'success');
            },
            error: function() { alert('Lỗi kết nối.'); btn.prop('disabled', false).html('<i data-lucide="check"></i> Duyệt'); lucide.createIcons(); }
        });
    });

    // =========================================================
    // AJAX: Từ chối đơn nghỉ phép
    // =========================================================
    $('#btnConfirmReject').on('click', function() {
        const reason = $('#rejectReason').val().trim();
        if (!reason) { alert('Vui lòng nhập lý do từ chối.'); return; }

        $(this).prop('disabled', true).text('...');
        $.ajax({
            url: LEAVE_BASE + '/ajax/leave/reject',
            method: 'POST',
            data: { id: rejectLeaveId, rejection_reason: reason },
            success: function(res) {
                hideRejectForm();
                $('#btnConfirmReject').prop('disabled', false).text('Xác nhận từ chối');
                if (!res.success) { alert(res.message); return; }
                // Update row in-place
                $(`button.btn-ajax-approve[data-id="${rejectLeaveId}"]`).closest('tr')
                    .find('td:nth-last-child(3) .badge').removeClass().addClass('badge badge-rejected').text('Từ chối');
                $(`button.btn-ajax-reject[data-id="${rejectLeaveId}"]`).closest('tr')
                    .find('td:last-child').html('<span class="text-muted text-sm">—</span>');
                showLeaveToast('✗ Đã từ chối đơn nghỉ phép.', 'danger');
            },
            error: function() {
                alert('Lỗi kết nối.');
                $('#btnConfirmReject').prop('disabled', false).text('Xác nhận từ chối');
            }
        });
    });
});

function showLeaveToast(msg, type) {
    const colors = { success: 'var(--success)', danger: 'var(--danger)', warning: 'var(--warning)' };
    const color  = colors[type] || colors.success;
    const toast  = $(`<div style="position:fixed;top:20px;right:24px;z-index:9999;background:#fff;border-left:4px solid ${color};padding:12px 20px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);font-weight:600;font-size:0.9rem;min-width:240px;">${msg}</div>`);
    $('body').append(toast);
    setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3000);
}
</script>


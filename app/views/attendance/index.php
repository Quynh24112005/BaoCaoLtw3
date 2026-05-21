<?php 
$pageTitle = 'Chấm công'; 
$days = [
    'Monday' => 'Thứ Hai',
    'Tuesday' => 'Thứ Ba',
    'Wednesday' => 'Thứ Tư',
    'Thursday' => 'Thứ Năm',
    'Friday' => 'Thứ Sáu',
    'Saturday' => 'Thứ Bảy',
    'Sunday' => 'Chủ Nhật'
];
$attendanceStatusMap = [
    'PRESENT' => 'Đi làm',
    'LATE' => 'Đi muộn',
    'ABSENT' => 'Vắng mặt',
    'LEAVE' => 'Nghỉ phép',
    'HOLIDAY' => 'Ngày lễ'
];
?>
<div class="page-header">
    <div><h1 class="page-title"><i data-lucide="clock" class="header-icon"></i> Bảng chấm công</h1></div>
</div>

<!-- Filters -->
<form method="GET" class="filter-form">
    <div class="form-group-inline">
        <label class="form-label">Tháng:</label>
        <input type="month" name="month" class="form-input" value="<?= $month ?>">
    </div>
    <?php if (Auth::isAdmin()): ?>
    <div class="form-group-inline">
        <label class="form-label">Nhân viên:</label>
        <select name="employee" class="form-select">
            <option value="">-- Chọn nhân viên --</option>
            <?php foreach ($employees as $emp): ?>
                <option value="<?= $emp['id'] ?>" <?= $employeeId === (int)$emp['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($emp['full_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <button type="submit" class="btn btn-primary"><i data-lucide="filter"></i> Lọc</button>
</form>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Giờ làm</th>
                    <th>Đi muộn</th>
                    <th>Tăng ca</th>
                    <th>Trạng thái</th>
                    <th>Ghi chú</th>
                    <?php if (Auth::isAdmin()): ?><th>Sửa</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($attendance)): ?>
                    <tr><td colspan="9" class="empty-state text-center">Không có dữ liệu chấm công.</td></tr>
                <?php else: ?>
                    <?php foreach ($attendance as $row): ?>
                    <tr data-att-id="<?= $row['id'] ?>">
                        <?php 
                        $dayEng = date('l', strtotime($row['work_date']));
                        $dayViet = $days[$dayEng] ?? $dayEng;
                        ?>
                        <td><?= $dayViet ?> <?= date('d/m', strtotime($row['work_date'])) ?></td>
                        <td class="att-checkin"><?= $row['check_in_at'] ? date('H:i', strtotime($row['check_in_at'])) : '—' ?></td>
                        <td class="att-checkout"><?= $row['check_out_at'] ? date('H:i', strtotime($row['check_out_at'])) : '—' ?></td>
                        <td class="att-worked"><?= round((int)$row['worked_minutes'] / 60, 1) ?> giờ</td>
                        <td><?= $row['late_minutes'] > 0 ? $row['late_minutes'] . ' phút' : '—' ?></td>
                        <td><?= $row['overtime_minutes'] > 0 ? $row['overtime_minutes'] . ' phút' : '—' ?></td>
                        <td><span class="badge badge-<?= strtolower($row['record_status']) ?>"><?= $attendanceStatusMap[$row['record_status']] ?? $row['record_status'] ?></span></td>
                        <td><?= htmlspecialchars($row['note'] ?? '—') ?></td>
                        <?php if (Auth::isAdmin()): ?>
                        <td>
                            <button class="btn btn-sm btn-ghost" style="padding: 4px 8px;" onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)"><i data-lucide="edit-3" style="width: 14px; height: 14px;"></i></button>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (Auth::isAdmin()): ?>
<!-- Edit modal -->
<div id="editModal" class="modal" style="display:none">
    <div class="modal-backdrop" onclick="closeEditModal()"></div>
    <div class="modal-box">
        <h3 style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;"><i data-lucide="edit-3" style="width: 20px; height: 20px;"></i> Chỉnh sửa chấm công</h3>
        <p class="text-muted text-sm" style="margin-bottom: 16px;">Lý do sửa là bắt buộc và sẽ ghi vào audit log.</p>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Check-in *</label>
                <input type="datetime-local" id="editCheckIn" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Check-out *</label>
                <input type="datetime-local" id="editCheckOut" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Đi muộn (phút)</label>
                <input type="number" id="editLate" class="form-input" min="0">
            </div>
            <div class="form-group">
                <label class="form-label">Tăng ca (phút)</label>
                <input type="number" id="editOvertime" class="form-input" min="0">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Lý do chỉnh sửa *</label>
            <textarea id="editNote" class="form-input" rows="3" placeholder="Bắt buộc nhập lý do..."></textarea>
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-ghost" onclick="closeEditModal()">Hủy</button>
            <button type="button" class="btn btn-primary" id="btnSaveAttendance"><i data-lucide="save"></i> Lưu</button>
        </div>
    </div>
</div>

<script>
const ATT_BASE  = '<?= BASE_URL ?>';
let currentAttId = null;
let currentAttRow = null;

function openEditModal(row) {
    currentAttId  = row.id;
    currentAttRow = row;
    $('#editCheckIn').val(row.check_in_at  ? row.check_in_at.replace(' ', 'T').substr(0,16)  : '');
    $('#editCheckOut').val(row.check_out_at ? row.check_out_at.replace(' ', 'T').substr(0,16) : '');
    $('#editLate').val(row.late_minutes || 0);
    $('#editOvertime').val(row.overtime_minutes || 0);
    $('#editNote').val('');
    $('#editModal').css('display', 'flex');
    lucide.createIcons();
}

function closeEditModal() {
    $('#editModal').hide();
    currentAttId = null;
}

$(function() {
    $('#btnSaveAttendance').on('click', function() {
        const checkIn  = $('#editCheckIn').val();
        const checkOut = $('#editCheckOut').val();
        const note     = $('#editNote').val().trim();

        if (!checkIn || !checkOut) { alert('Vui lòng nhập đầy đủ giờ vào/ra.'); return; }
        if (!note)                 { alert('Vui lòng nhập lý do chỉnh sửa.'); return; }

        const btn = $(this).prop('disabled', true).text('Đang lưu...');

        $.ajax({
            url: ATT_BASE + '/ajax/attendance/update',
            method: 'POST',
            data: {
                id:               currentAttId,
                check_in_at:      checkIn,
                check_out_at:     checkOut,
                late_minutes:     $('#editLate').val() || 0,
                overtime_minutes: $('#editOvertime').val() || 0,
                note:             note
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i data-lucide="save"></i> Lưu');
                lucide.createIcons();
                if (!res.success) { alert(res.message); return; }

                // Update cells in-place using the row id
                const tr = $(`tr[data-att-id="${currentAttId}"]`);
                if (tr.length) {
                    tr.find('.att-checkin').text(res.check_in);
                    tr.find('.att-checkout').text(res.check_out);
                    tr.find('.att-worked').text(res.worked_hours + ' giờ');
                }
                closeEditModal();
                showAttToast('✓ Đã cập nhật chấm công thành công.', 'success');
            },
            error: function() {
                btn.prop('disabled', false).html('<i data-lucide="save"></i> Lưu');
                lucide.createIcons();
                alert('Lỗi kết nối. Vui lòng thử lại.');
            }
        });
    });
});

function showAttToast(msg, type) {
    const colors = { success: 'var(--success)', danger: 'var(--danger)' };
    const color  = colors[type] || colors.success;
    const toast  = $(`<div style="position:fixed;top:20px;right:24px;z-index:9999;background:#fff;border-left:4px solid ${color};padding:12px 20px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);font-weight:600;font-size:0.9rem;min-width:240px;">${msg}</div>`);
    $('body').append(toast);
    setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3000);
}
</script>
<?php endif; ?>


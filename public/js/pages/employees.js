$(function() {
    // =========================================================
    // 1. AJAX LIVE SEARCH — gõ là ra kết quả ngay
    // =========================================================
    let searchTimer;
    $('#liveSearch').on('input', function() {
        clearTimeout(searchTimer);
        const q      = $(this).val().trim();
        const dept   = $('select[name="department"]').val();
        const role   = $('select[name="role"]').val();
        const status = $('select[name="status"]').val();

        searchTimer = setTimeout(function() {
            $.ajax({
                url: BASE + '/ajax/employees/search',
                method: 'GET',
                data: { q, department: dept, role, status },
                success: function(res) {
                    if (!res.success) return;
                    renderEmployeeRows(res.data);
                    lucide.createIcons();
                }
            });
        }, 300); // debounce 300ms
    });

    // Re-search khi thay đổi dropdown bộ lọc
    $('select[name="department"], select[name="role"], select[name="status"]').off('change').on('change', function() {
        $('#liveSearch').trigger('input');
    });

    function renderEmployeeRows(employees) {
        const tbody = $('table.table tbody');
        if (employees.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center" style="padding:48px 16px;color:var(--text-muted);"><p style="font-weight:600;">Không tìm thấy nhân viên nào</p></td></tr>');
            return;
        }
        let html = '';
        employees.forEach(function(emp) {
            const isActive  = emp.status === 'ACTIVE';
            const dotClass  = isActive ? 'active' : 'inactive';
            const lockIcon  = isActive ? 'lock' : 'unlock';
            const lockTitle = isActive ? 'Khóa tài khoản' : 'Mở khóa';
            const roleBadge = emp.role === 'ADMIN' ? 'admin' : 'employee';
            html += `
            <tr id="emp-row-${emp.id}">
                <td><div class="emp-column">
                    <div class="emp-avatar">${emp.initials}</div>
                    <div class="emp-info">
                        <span class="emp-name">${escHtml(emp.full_name)}</span>
                        <span class="emp-email">${escHtml(emp.email)}</span>
                    </div>
                </div></td>
                <td><span class="badge badge-${roleBadge}">${escHtml(emp.role_label)}</span></td>
                <td>
                    <div class="dept-title">${escHtml(emp.department || 'Chưa phân bổ')}</div>
                    <div class="dept-subtitle">${escHtml(emp.position || 'Chưa có chức danh')}</div>
                </td>
                <td>
                    <div class="status-dot-container" id="status-cell-${emp.id}">
                        <span class="status-dot ${dotClass}"></span>
                        <span>${escHtml(emp.status_label)}</span>
                    </div>
                </td>
                <td class="text-right">
                    <div class="action-btn-group" style="justify-content:flex-end">
                        <a href="${BASE}/employees/edit?id=${emp.id}" class="action-icon-btn" title="Chỉnh sửa">
                            <i data-lucide="edit-3" style="width:15px;height:15px"></i>
                        </a>
                        <button type="button" class="action-icon-btn btn-ajax-toggle"
                            data-id="${emp.id}" data-status="${emp.status}" title="${lockTitle}">
                            <i data-lucide="${lockIcon}" style="width:15px;height:15px"></i>
                        </button>
                        <form method="POST" action="${BASE}/employees/delete" class="delete-employee-form" style="display:inline">
                            <input type="hidden" name="id" value="${emp.id}">
                            <button type="submit" class="action-icon-btn delete" title="Xóa nhân viên">
                                <i data-lucide="trash-2" style="width:15px;height:15px"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>`;
        });
        tbody.html(html);
    }

    // =========================================================
    // 2. AJAX TOGGLE STATUS (Khóa / Mở khóa) — có Modal Xác nhận tùy chỉnh
    // =========================================================
    let statusBtnToSubmit = null;
    let statusIdToSubmit = null;
    let statusTargetToSubmit = null;

    $(document).on('click', '.btn-ajax-toggle', function() {
        const btn           = $(this);
        const id            = btn.data('id');
        const currentStatus = btn.data('status');
        const isLocking     = currentStatus === 'ACTIVE';

        statusBtnToSubmit = btn;
        statusIdToSubmit = id;
        statusTargetToSubmit = isLocking ? 'LOCKED' : 'ACTIVE';

        // Cập nhật nội dung Modal động dựa trên hành động Khóa hay Mở khóa
        const modal = $('#statusConfirmModal');
        const iconContainer = $('#statusModalIconContainer');
        const icon = $('#statusModalIcon');
        const title = $('#statusModalTitle');
        const message = $('#statusModalMessage');
        const subtext = $('#statusModalSubtext');
        const confirmBtn = $('#btnConfirmStatus');

        // Reset classes
        iconContainer.removeClass('warning success');
        confirmBtn.removeClass('modal-btn-warning modal-btn-success');

        if (isLocking) {
            iconContainer.addClass('warning');
            icon.attr('data-lucide', 'lock');
            title.text('Xác nhận khóa tài khoản');
            message.text('Bạn có chắc chắn muốn khóa tài khoản nhân sự này?');
            subtext.text('Nhân viên bị khóa sẽ không thể đăng nhập vào hệ thống.');
            confirmBtn.text('Xác nhận khóa').addClass('modal-btn-warning');
        } else {
            iconContainer.addClass('success');
            icon.attr('data-lucide', 'unlock');
            title.text('Xác nhận mở khóa tài khoản');
            message.text('Bạn có chắc chắn muốn mở khóa tài khoản nhân sự này?');
            subtext.text('Tài khoản sau khi mở khóa sẽ hoạt động bình thường.');
            confirmBtn.text('Xác nhận mở khóa').addClass('modal-btn-success');
        }

        lucide.createIcons();
        modal.css('display', 'flex').hide().fadeIn(200);
    });

    // Bấm nút Hủy trên modal thay đổi trạng thái
    $('#btnCancelStatus').on('click', function() {
        $('#statusConfirmModal').fadeOut(150);
        statusBtnToSubmit = null;
        statusIdToSubmit = null;
        statusTargetToSubmit = null;
    });

    // Bấm nút Xác nhận trên modal thay đổi trạng thái
    $('#btnConfirmStatus').on('click', function() {
        if (!statusBtnToSubmit || !statusIdToSubmit) return;

        const btn = statusBtnToSubmit;
        const id = statusIdToSubmit;

        // Đóng modal trước
        $('#statusConfirmModal').fadeOut(150);

        btn.prop('disabled', true);
        $.ajax({
            url: BASE + '/ajax/employees/toggle-status',
            method: 'POST',
            data: { id },
            success: function(res) {
                if (!res.success) {
                    alert(res.message);
                    btn.prop('disabled', false);
                    return;
                }
                const isNowActive = res.new_status === 'ACTIVE';
                const newDot  = isNowActive ? 'active' : 'inactive';
                const newIcon = isNowActive ? 'lock' : 'unlock';
                const newTitle = isNowActive ? 'Khóa tài khoản' : 'Mở khóa tài khoản';

                $(`#status-cell-${id}`).html(
                    `<span class="status-dot ${newDot}"></span><span>${res.label}</span>`
                );
                btn.data('status', res.new_status).attr('title', newTitle);
                btn.find('i').attr('data-lucide', newIcon);
                btn.prop('disabled', false);
                lucide.createIcons();
                showToast(isNowActive ? '✓ Đã mở khóa tài khoản.' : '🔒 Đã khóa tài khoản.', isNowActive ? 'success' : 'warning');
            },
            error: function() {
                alert('Lỗi kết nối. Vui lòng thử lại.');
                btn.prop('disabled', false);
            }
        });
    });

    // =========================================================
    // 3. XỬ LÝ MODAL XÁC NHẬN XÓA TỰ DỰNG (ĐẸP HƠN ALERT)
    // =========================================================
    let formToSubmit = null;

    // Lắng nghe sự kiện submit của form xóa
    $(document).on('submit', '.delete-employee-form', function(e) {
        e.preventDefault();
        formToSubmit = this;
        $('#deleteConfirmModal').css('display', 'flex').hide().fadeIn(200);
    });

    // Bấm nút Hủy trên modal xóa
    $('#btnCancelDelete').on('click', function() {
        $('#deleteConfirmModal').fadeOut(150);
        formToSubmit = null;
    });

    // Bấm vùng tối overlay để tắt modal xóa/khóa
    $('.modal-overlay').on('click', function() {
        $('#deleteConfirmModal').fadeOut(150);
        $('#statusConfirmModal').fadeOut(150);
        formToSubmit = null;
        statusBtnToSubmit = null;
        statusIdToSubmit = null;
        statusTargetToSubmit = null;
    });

    // Bấm nút Xác nhận xóa trên modal
    $('#btnConfirmDelete').on('click', function() {
        if (formToSubmit) {
            formToSubmit.submit();
        }
    });
});

function escHtml(str) {
    return $('<div>').text(str || '').html();
}

function showToast(msg, type) {
    const colors = { success: 'var(--success)', warning: 'var(--warning)', danger: 'var(--danger)' };
    const color  = colors[type] || colors.success;
    const toast  = $(`<div style="position:fixed;top:20px;right:24px;z-index:9999;background:#fff;border-left:4px solid ${color};padding:12px 20px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);font-weight:600;font-size:0.9rem;min-width:240px;">${msg}</div>`);
    $('body').append(toast);
    setTimeout(() => toast.fadeOut(400, () => toast.remove()), 3000);
}

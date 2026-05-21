<?php 
$pageTitle = 'Quản lý nhân viên'; 
$statusMap = [
    'ACTIVE' => 'Hoạt động',
    'LOCKED' => 'Tạm khóa'
];
$roleMap = [
    'ADMIN' => 'Quản trị viên',
    'EMPLOYEE' => 'Nhân viên'
];

// Calculate pagination stats
$totalPages = ceil($total / $perPage);
$startEntry = ($page - 1) * $perPage + 1;
$endEntry = min($page * $perPage, $total);
if ($total == 0) {
    $startEntry = 0;
    $endEntry = 0;
}
?>


<div class="page-header" style="flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 class="page-title" style="display: flex; align-items: center; gap: 8px;">
            <i data-lucide="users" class="header-icon" style="stroke-width: 2.5px;"></i> Danh sách nhân viên
        </h1>
        <p class="page-subtitle">Quản lý sơ đồ tổ chức, chức vụ và tài khoản nhân sự hệ thống.</p>
    </div>
    <a href="<?= BASE_URL ?>/employees/create" class="btn btn-primary" style="padding: 12px 24px; border-radius: 12px;">
        <i data-lucide="user-plus" style="width: 18px; height: 18px"></i> Thêm nhân viên mới
    </a>
</div>

<!-- KPI Stats Dashboard Section -->
<div class="stats-grid mb-4">
    <!-- Card 1: Total staff -->
    <div class="stat-card stat-blue">
        <div class="stat-icon">
            <i data-lucide="users" style="color:var(--info); stroke-width: 2.2px;"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= number_format($stats['total_staff'] ?? 0) ?></div>
            <div class="stat-label">Tổng nhân sự</div>
        </div>
    </div>
    
    <!-- Card 2: Departments -->
    <div class="stat-card stat-green">
        <div class="stat-icon">
            <i data-lucide="building" style="color:var(--success); stroke-width: 2.2px;"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= number_format($stats['total_departments'] ?? 0) ?></div>
            <div class="stat-label">Số phòng ban</div>
        </div>
    </div>
    
    <!-- Card 3: On Leave -->
    <div class="stat-card stat-orange">
        <div class="stat-icon">
            <i data-lucide="calendar" style="color:var(--warning); stroke-width: 2.2px;"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= number_format($stats['on_leave'] ?? 0) ?></div>
            <div class="stat-label">Nghỉ phép hôm nay</div>
        </div>
    </div>
    
    <!-- Card 4: New Hires -->
    <div class="stat-card stat-red">
        <div class="stat-icon">
            <i data-lucide="user-plus" style="color:var(--danger); stroke-width: 2.2px;"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?= number_format($stats['new_hires'] ?? 0) ?></div>
            <div class="stat-label">Nhân sự mới (Tháng)</div>
        </div>
    </div>
</div>

<!-- Search & Advanced Filters Bar -->
<form method="GET" action="<?= BASE_URL ?>/employees" id="filterForm">
    <div class="filter-row">
        <!-- Search field -->
        <div class="filter-search-group">
            <i data-lucide="search" class="filter-search-icon"></i>
            <input type="text" id="liveSearch" name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" placeholder="Tìm kiếm nhân viên, email, mã số..." class="filter-search-input" autocomplete="off">
        </div>
        
        <!-- Department filter -->
        <select name="department" class="filter-select" onchange="document.getElementById('filterForm').submit()">
            <option value="">Tất cả phòng ban</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?= htmlspecialchars($dept) ?>" <?= ($filters['department'] ?? '') === $dept ? 'selected' : '' ?>>
                    <?= htmlspecialchars($dept) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <!-- Role filter -->
        <select name="role" class="filter-select" onchange="document.getElementById('filterForm').submit()">
            <option value="">Tất cả vai trò</option>
            <option value="ADMIN" <?= ($filters['role'] ?? '') === 'ADMIN' ? 'selected' : '' ?>>Quản trị viên</option>
            <option value="EMPLOYEE" <?= ($filters['role'] ?? '') === 'EMPLOYEE' ? 'selected' : '' ?>>Nhân viên</option>
        </select>
        
        <!-- Status filter -->
        <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="ACTIVE" <?= ($filters['status'] ?? '') === 'ACTIVE' ? 'selected' : '' ?>>Hoạt động</option>
            <option value="LOCKED" <?= ($filters['status'] ?? '') === 'LOCKED' ? 'selected' : '' ?>>Tạm khóa</option>
        </select>
        
        <!-- Clear Filters button -->
        <?php if (!empty($filters['q']) || !empty($filters['department']) || !empty($filters['role']) || !empty($filters['status'])): ?>
            <a href="<?= BASE_URL ?>/employees" class="filter-clear">
                <i data-lucide="x-circle" style="width: 16px; height: 16px"></i> Xóa bộ lọc
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- Table Card -->
<div class="table-card">
    <table class="table">
        <thead>
            <tr>
                <th>Nhân viên</th>
                <th>Vai trò</th>
                <th>Phòng ban & Chức vụ</th>
                <th>Trạng thái</th>
                <th class="text-right">Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($employees)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 48px 16px; color: var(--text-muted);">
                        <i data-lucide="users-round" style="width: 48px; height: 48px; stroke-width: 1.5; color: var(--text-muted); opacity: 0.5; margin-bottom: 12px;"></i>
                        <p style="font-weight: 600; font-size: 1rem;">Không tìm thấy nhân viên nào</p>
                        <p style="font-size: 0.85rem; margin-top: 4px;">Thử thay đổi từ khóa hoặc bộ lọc của bạn.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($employees as $emp): ?>
                    <tr id="emp-row-<?= $emp['id'] ?>">
                        <td>
                            <div class="emp-column">
                                <div class="emp-avatar">
                                    <?= strtoupper(mb_substr($emp['full_name'], 0, 1)) ?>
                                </div>
                                <div class="emp-info">
                                    <span class="emp-name"><?= htmlspecialchars($emp['full_name']) ?></span>
                                    <span class="emp-email"><?= htmlspecialchars($emp['email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?= strtolower($emp['role']) ?>">
                                <?= $roleMap[$emp['role']] ?? $emp['role'] ?>
                            </span>
                        </td>
                        <td>
                            <div class="dept-title"><?= htmlspecialchars($emp['department'] ?? 'Chưa phân bổ') ?></div>
                            <div class="dept-subtitle"><?= htmlspecialchars($emp['position'] ?? 'Chưa có chức danh') ?></div>
                        </td>
                        <td>
                            <div class="status-dot-container" id="status-cell-<?= $emp['id'] ?>">
                                <span class="status-dot <?= $emp['status'] === 'ACTIVE' ? 'active' : 'inactive' ?>"></span>
                                <span><?= $statusMap[$emp['status']] ?? $emp['status'] ?></span>
                            </div>
                        </td>
                        <td class="text-right">
                            <div class="action-btn-group" style="justify-content: flex-end;">
                                <a href="<?= BASE_URL ?>/employees/edit?id=<?= $emp['id'] ?>" class="action-icon-btn" title="Chỉnh sửa">
                                    <i data-lucide="edit-3" style="width:15px;height:15px"></i>
                                </a>

                                <button type="button"
                                    class="action-icon-btn btn-ajax-toggle"
                                    data-id="<?= $emp['id'] ?>"
                                    data-status="<?= $emp['status'] ?>"
                                    title="<?= $emp['status'] === 'ACTIVE' ? 'Khóa tài khoản' : 'Mở khóa tài khoản' ?>">
                                    <i data-lucide="<?= $emp['status'] === 'ACTIVE' ? 'lock' : 'unlock' ?>" style="width:15px;height:15px"></i>
                                </button>

                                <form method="POST" action="<?= BASE_URL ?>/employees/delete" style="display:inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân viên này khỏi hệ thống? Hành động này không thể hoàn tác!')">
                                    <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                                    <button type="submit" class="action-icon-btn delete" title="Xóa nhân viên">
                                        <i data-lucide="trash-2" style="width:15px;height:15px"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Table Footer with stats and pagination -->
    <div class="table-footer">
        <div class="table-footer-text">
            Hiển thị <?= $startEntry ?> đến <?= $endEntry ?> trong tổng số <?= $total ?> nhân sự
        </div>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination-controls">
                <!-- Previous Page Button -->
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>" class="page-btn" title="Trang trước">
                        <i data-lucide="chevron-left" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                    </a>
                <?php endif; ?>
                
                <!-- Page Number Links -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <!-- Next Page Button -->
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>" class="page-btn" title="Trang tiếp">
                        <i data-lucide="chevron-right" style="width: 16px; height: 16px; vertical-align: middle;"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const BASE = '<?= BASE_URL ?>';

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
                    </div>
                </td>
            </tr>`;
        });
        tbody.html(html);
    }

    // =========================================================
    // 2. AJAX TOGGLE STATUS (Khóa / Mở khóa) — không reload
    // =========================================================
    $(document).on('click', '.btn-ajax-toggle', function() {
        const btn           = $(this);
        const id            = btn.data('id');
        const currentStatus = btn.data('status');
        const action        = currentStatus === 'ACTIVE' ? 'khóa' : 'mở khóa';
        if (!confirm(`Xác nhận ${action} tài khoản này?`)) return;

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
</script>

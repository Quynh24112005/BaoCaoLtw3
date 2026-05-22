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

                                <form method="POST" action="<?= BASE_URL ?>/employees/delete" class="delete-employee-form" style="display:inline">
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
</script>

<!-- HTML Markup Modal Xác nhận Xóa -->
<div id="deleteConfirmModal" class="custom-modal">
    <div class="modal-overlay"></div>
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-icon-container">
                <i data-lucide="alert-triangle" class="modal-alert-icon"></i>
            </div>
            <h3>Xác nhận xóa nhân viên</h3>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa nhân sự này khỏi hệ thống?</p>
            <p class="modal-subtext">Hành động này sẽ xóa mềm tài khoản và dữ liệu liên quan.</p>
        </div>
        <div class="modal-footer">
            <button type="button" id="btnCancelDelete" class="modal-btn modal-btn-secondary">Hủy bỏ</button>
            <button type="button" id="btnConfirmDelete" class="modal-btn modal-btn-danger">Xác nhận xóa</button>
        </div>
    </div>
</div>

<!-- HTML Markup Modal Xác nhận Trạng thái (Khóa / Mở khóa) -->
<div id="statusConfirmModal" class="custom-modal">
    <div class="modal-overlay"></div>
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-icon-container" id="statusModalIconContainer">
                <i data-lucide="lock" class="modal-alert-icon" id="statusModalIcon"></i>
            </div>
            <h3 id="statusModalTitle">Xác nhận thay đổi trạng thái</h3>
        </div>
        <div class="modal-body">
            <p id="statusModalMessage">Bạn có chắc chắn muốn thay đổi trạng thái tài khoản nhân viên này?</p>
            <p class="modal-subtext" id="statusModalSubtext">Mô tả hành động...</p>
        </div>
        <div class="modal-footer">
            <button type="button" id="btnCancelStatus" class="modal-btn modal-btn-secondary">Hủy bỏ</button>
            <button type="button" id="btnConfirmStatus" class="modal-btn">Xác nhận</button>
        </div>
    </div>
</div>

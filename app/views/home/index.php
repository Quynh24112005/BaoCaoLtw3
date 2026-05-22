<?php 
$pageTitle = 'Bảng điều khiển'; 
$days = [
    'Monday' => 'Thứ Hai',
    'Tuesday' => 'Thứ Ba',
    'Wednesday' => 'Thứ Tư',
    'Thursday' => 'Thứ Năm',
    'Friday' => 'Thứ Sáu',
    'Saturday' => 'Thứ Bảy',
    'Sunday' => 'Chủ Nhật'
];
$dayEng = date('l');
$dayViet = $days[$dayEng] ?? $dayEng;

$statusMap = [
    'DRAFT' => 'Nháp',
    'PUBLISHED' => 'Đã công bố',
    'PENDING' => 'Chờ duyệt',
    'APPROVED' => 'Đã duyệt',
    'REJECTED' => 'Từ chối',
    'OPEN' => 'Đang mở',
    'CLOSED' => 'Đã đóng'
];
?>

<div class="page-header">
    <h1 class="page-title">
        <?= Auth::isAdmin() ? '<i data-lucide="bar-chart-3" class="header-icon"></i> Bảng điều khiển Admin' : '<i data-lucide="home" class="header-icon"></i> Trang chủ' ?>
    </h1>
    <p class="page-subtitle">Xin chào, <strong><?= htmlspecialchars(Session::get('user_name')) ?></strong>! Hôm nay là <?= $dayViet ?>, <?= date('d/m/Y') ?></p>
</div>

<div class="dashboard-layout">
    <?php if (Auth::isAdmin()): ?>
    <!-- ====== ADMIN VIEW ====== -->
    <div class="dashboard-main">
        <!-- Stats grid -->
        <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
            <div class="stat-card stat-blue">
                <div class="stat-icon"><i data-lucide="users"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= $totalEmployees ?></div>
                    <div class="stat-label">Nhân viên</div>
                </div>
            </div>
            <div class="stat-card stat-orange">
                <div class="stat-icon"><i data-lucide="palmtree"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= $pendingLeaves ?></div>
                    <div class="stat-label">Đơn chờ duyệt</div>
                </div>
            </div>
        </div>

        <!-- Department Distribution -->
        <?php if (!empty($deptStats)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title"><i data-lucide="pie-chart" class="card-title-icon"></i> Cơ cấu phòng ban</h2>
            </div>
            <div class="card-body">
                <div class="dept-list" style="display: flex; flex-direction: column; gap: 14px;">
                    <?php foreach ($deptStats as $dept): ?>
                        <div class="dept-item">
                            <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 5px; font-weight: 600;">
                                <span><?= htmlspecialchars($dept['department']) ?></span>
                                <span class="text-muted" style="font-weight: 500; font-size: 0.8rem;"><?= $dept['count'] ?> nhân sự</span>
                            </div>
                            <div class="progress-bar-bg" style="background: rgba(99, 102, 241, 0.08); height: 8px; border-radius: 99px; overflow: hidden; position: relative;">
                                <?php 
                                $pct = $totalEmployees > 0 ? round(($dept['count'] / $totalEmployees) * 100) : 0;
                                ?>
                                <div class="progress-bar-fill" style="background: var(--primary-gradient); width: <?= $pct ?>%; height: 100%; border-radius: 99px; transition: width 0.5s ease-in-out;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="dashboard-sidebar">
        <!-- Quick actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><i data-lucide="zap" class="card-title-icon"></i> Thao tác nhanh</h2>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="<?= BASE_URL ?>/employees/create" class="quick-btn"><i data-lucide="user-plus"></i><span>Thêm nhân viên</span></a>
                    <a href="<?= BASE_URL ?>/leave?status=PENDING" class="quick-btn"><i data-lucide="check-square"></i><span>Duyệt nghỉ phép</span></a>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ====== EMPLOYEE VIEW ====== -->
    <div class="dashboard-main" style="flex: 1 1 100%;">
        <!-- Leave requests -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i data-lucide="palmtree" class="card-title-icon"></i> Đơn nghỉ phép của tôi</h2>
                <a href="<?= BASE_URL ?>/leave/create" class="card-link">+ Tạo đơn</a>
            </div>
            <div class="card-body">
                <?php if (empty($myLeaves)): ?>
                    <p class="empty-state">Chưa có đơn nghỉ phép nào.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($myLeaves as $leave): ?>
                            <div class="leave-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(0,0,0,0.02); border-radius: 8px;">
                                <span class="leave-type" style="font-weight: 600;"><?= $leave['leave_type'] ?></span>
                                <span>Từ <?= date('d/m/Y', strtotime($leave['start_date'])) ?> đến <?= date('d/m/Y', strtotime($leave['end_date'])) ?></span>
                                <span class="badge badge-<?= strtolower($leave['status']) ?>"><?= $statusMap[$leave['status']] ?? $leave['status'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

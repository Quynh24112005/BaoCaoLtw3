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
        <div class="stats-grid">
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
            <div class="stat-card stat-red">
                <div class="stat-icon"><i data-lucide="ticket"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= $openTickets ?></div>
                    <div class="stat-label">Ticket mở</div>
                </div>
            </div>
            <div class="stat-card stat-green">
                <div class="stat-icon"><i data-lucide="credit-card"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= count($payPeriods) ?></div>
                    <div class="stat-label">Kỳ lương</div>
                </div>
            </div>
        </div>

        <!-- Payroll periods -->
        <?php if (!empty($payPeriods)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title"><i data-lucide="credit-card" class="card-title-icon"></i> Kỳ lương gần đây</h2>
                <a href="<?= BASE_URL ?>/payroll" class="card-link">Quản lý &rarr;</a>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tên kỳ</th>
                            <th>Từ ngày</th>
                            <th>Đến ngày</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($payPeriods, 0, 5) as $pp): ?>
                        <tr>
                            <td><?= htmlspecialchars($pp['name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($pp['period_start'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($pp['period_end'])) ?></td>
                            <td><span class="badge badge-<?= strtolower($pp['status']) ?>"><?= $statusMap[$pp['status']] ?? $pp['status'] ?></span></td>
                            <td><a href="<?= BASE_URL ?>/payroll/view?period=<?= $pp['id'] ?>" class="btn btn-sm btn-ghost">Xem</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                    <a href="<?= BASE_URL ?>/schedule/create-period" class="quick-btn"><i data-lucide="calendar-plus"></i><span>Tạo kỳ lịch</span></a>
                    <a href="<?= BASE_URL ?>/payroll/create-period" class="quick-btn"><i data-lucide="wallet"></i><span>Tạo kỳ lương</span></a>
                    <a href="<?= BASE_URL ?>/leave?status=PENDING" class="quick-btn"><i data-lucide="check-square"></i><span>Duyệt nghỉ phép</span></a>
                    <a href="<?= BASE_URL ?>/tickets?status=OPEN" class="quick-btn"><i data-lucide="message-square"></i><span>Xử lý ticket</span></a>
                </div>
            </div>
        </div>

        <!-- Department Distribution -->
        <?php if (!empty($deptStats)): ?>
        <div class="card">
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

    <?php else: ?>
    <!-- ====== EMPLOYEE VIEW ====== -->
    <div class="dashboard-main">
        <!-- This week's shifts -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i data-lucide="calendar" class="card-title-icon"></i> Ca làm tuần này</h2>
                <a href="<?= BASE_URL ?>/schedule" class="card-link">Xem lịch &rarr;</a>
            </div>
            <div class="card-body">
                <?php if (empty($myShifts)): ?>
                    <p class="empty-state">Chưa có ca nào được phân công tuần này.</p>
                <?php else: ?>
                    <?php foreach ($myShifts as $shift): ?>
                        <div class="shift-item">
                            <span class="shift-date"><?= date('l d/m', strtotime($shift['work_date'])) ?></span>
                            <span class="shift-name"><?= htmlspecialchars($shift['shift_name']) ?></span>
                            <span class="shift-time"><?= substr($shift['start_time'], 0, 5) ?> - <?= substr($shift['end_time'], 0, 5) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- My payslips -->
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title"><i data-lucide="credit-card" class="card-title-icon"></i> Phiếu lương gần đây</h2>
                <a href="<?= BASE_URL ?>/payroll" class="card-link">Xem tất cả &rarr;</a>
            </div>
            <div class="card-body">
                <?php if (empty($myPayslips)): ?>
                    <p class="empty-state">Chưa có phiếu lương nào được công bố.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr><th>Kỳ lương</th><th>Từ</th><th>Đến</th><th>Thực lĩnh</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($myPayslips, 0, 5) as $slip): ?>
                            <tr>
                                <td><?= htmlspecialchars($slip['period_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($slip['period_start'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($slip['period_end'])) ?></td>
                                <td class="text-success font-bold"><?= number_format((float)$slip['final_amount'], 0, ',', '.') ?> đ</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="dashboard-sidebar">
        <!-- Leave requests -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i data-lucide="palmtree" class="card-title-icon"></i> Đơn nghỉ phép</h2>
                <a href="<?= BASE_URL ?>/leave/create" class="card-link">+ Tạo đơn</a>
            </div>
            <div class="card-body">
                <?php if (empty($myLeaves)): ?>
                    <p class="empty-state">Chưa có đơn nghỉ phép nào.</p>
                <?php else: ?>
                    <?php foreach ($myLeaves as $leave): ?>
                        <div class="leave-item">
                            <span class="leave-type"><?= $leave['leave_type'] ?></span>
                            <span><?= date('d/m', strtotime($leave['start_date'])) ?> - <?= date('d/m', strtotime($leave['end_date'])) ?></span>
                            <span class="badge badge-<?= strtolower($leave['status']) ?>"><?= $statusMap[$leave['status']] ?? $leave['status'] ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

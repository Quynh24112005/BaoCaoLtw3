<?php 
$pageTitle = 'Lịch làm việc'; 
$days = [
    'Monday' => 'Thứ Hai',
    'Tuesday' => 'Thứ Ba',
    'Wednesday' => 'Thứ Tư',
    'Thursday' => 'Thứ Năm',
    'Friday' => 'Thứ Sáu',
    'Saturday' => 'Thứ Bảy',
    'Sunday' => 'Chủ Nhật'
];
$statusMap = [
    'DRAFT' => 'Bản nháp',
    'REGISTRATION_OPEN' => 'Mở đăng ký ca',
    'REVIEWING' => 'Đang xét duyệt',
    'PUBLISHED' => 'Đã công bố'
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i data-lucide="calendar" class="header-icon"></i> Lịch làm việc</h1>
    </div>
</div>

<!-- Period selector -->
<?php if (!empty($periods)): ?>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="period-selector">
            <label class="form-label">Chọn kỳ lịch:</label>
            <select name="period" class="form-select" onchange="this.form.submit()">
                <option value="">-- Chọn tuần --</option>
                <?php foreach ($periods as $period): ?>
                    <option value="<?= $period['id'] ?>" <?= $activePeriodId === (int)$period['id'] ? 'selected' : '' ?>>
                        Tuần <?= date('d/m', strtotime($period['week_start_date'])) ?> - <?= date('d/m/Y', strtotime($period['week_end_date'])) ?>
                        [<?= $statusMap[$period['record_status']] ?? $period['record_status'] ?>]
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($activePeriodId):
    $activePeriod = null;
    foreach ($periods as $p) {
        if ((int)$p['id'] === $activePeriodId) { $activePeriod = $p; break; }
    }
?>

<?php if ($activePeriod): ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="period-info">
            <div>
                <span class="font-bold">Tuần:</span>
                <?= date('d/m/Y', strtotime($activePeriod['week_start_date'])) ?> — <?= date('d/m/Y', strtotime($activePeriod['week_end_date'])) ?>
            </div>
             <div>
                <span class="font-bold">Trạng thái:</span>
                <span class="badge badge-<?= strtolower($activePeriod['record_status']) ?>"><?= $statusMap[$activePeriod['record_status']] ?? $activePeriod['record_status'] ?></span>
            </div>
        </div>

        <?php if (Auth::isAdmin()): ?>
        <div class="period-actions mt-3">
            <?php if ($activePeriod['record_status'] === 'DRAFT'): ?>
                <form method="POST" action="<?= BASE_URL ?>/schedule/open-registration" style="display:inline">
                    <input type="hidden" name="period_id" value="<?= $activePeriodId ?>">
                    <input type="datetime-local" name="close_at" class="form-input" style="width:auto;display:inline">
                     <button type="submit" class="btn btn-primary"><i data-lucide="mail"></i> Mở đăng ký</button>
                </form>
            <?php elseif ($activePeriod['record_status'] === 'REGISTRATION_OPEN'): ?>
                <span class="text-muted">Đang mở đăng ký...</span>
            <?php endif; ?>

            <?php if (in_array($activePeriod['record_status'], ['REGISTRATION_OPEN', 'REVIEWING'], true)): ?>
                <form method="POST" action="<?= BASE_URL ?>/schedule/publish" style="display:inline">
                    <input type="hidden" name="period_id" value="<?= $activePeriodId ?>">
                     <button type="submit" class="btn btn-success" onclick="return confirm('Publish lịch chính thức?')"><i data-lucide="check-circle"></i> Publish lịch</button>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Slots by day -->
<?php
$slotsByDay = [];
foreach ($slots as $slot) {
    $slotsByDay[$slot['work_date']][] = $slot;
}
ksort($slotsByDay);
?>

<?php foreach ($slotsByDay as $date => $daySlots): 
    $dayEng = date('l', strtotime($date));
    $dayViet = $days[$dayEng] ?? $dayEng;
?>
<div class="card mb-3">
    <div class="card-header">
        <h2 class="card-title" style="display: flex; align-items: center; gap: 8px;"><i data-lucide="calendar-days" style="width: 18px; height: 18px;"></i> <?= $dayViet ?>, <?= date('d/m/Y', strtotime($date)) ?></h2>
    </div>
    <div class="card-body p-0">
        <div class="shift-grid">
            <?php foreach ($daySlots as $slot): ?>
            <div class="shift-card <?= $slot['record_status'] === 'UNDERSTAFFED' ? 'shift-understaffed' : '' ?>">
                <div class="shift-card-header">
                    <span class="shift-card-name"><?= htmlspecialchars($slot['shift_name']) ?></span>
                    <span class="shift-card-time"><?= substr($slot['start_time'], 0, 5) ?> - <?= substr($slot['end_time'], 0, 5) ?></span>
                    <?php if ($slot['is_night_shift']): ?><span class="badge-night" style="display: flex; align-items: center; gap: 4px;"><i data-lucide="moon" style="width: 14px; height: 14px;"></i> Đêm</span><?php endif; ?>
                </div>

                <!-- Assigned employees -->
                <div class="shift-card-body">
                    <div class="shift-assigned">
                        <?php $detail = $slotDetails[$slot['id']] ?? []; ?>
                        <?php if (!empty($detail['assignments'])): ?>
                            <?php foreach ($detail['assignments'] as $asgn): ?>
                                 <div class="assigned-person" style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 4px;">
                                    <span style="display: flex; align-items: center; gap: 6px;"><i data-lucide="user" style="width: 14px; height: 14px;"></i> <?= htmlspecialchars($asgn['full_name']) ?></span>
                                    <?php if (Auth::isAdmin()): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/schedule/remove-assignment" style="display:inline">
                                        <input type="hidden" name="assignment_id" value="<?= $asgn['id'] ?>">
                                        <input type="hidden" name="reason" value="Admin gỡ thủ công">
                                        <button type="submit" class="btn-icon" style="padding: 2px;" onclick="return confirm('Gỡ phân ca?')"><i data-lucide="x" style="width: 14px; height: 14px;"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted text-sm">Chưa có ai được phân ca</span>
                        <?php endif; ?>
                    </div>

                    <div class="shift-capacity">
                        <?= count($detail['assignments'] ?? []) ?>/<?= $slot['required_headcount'] ?> người
                    </div>
                </div>

                <!-- Actions -->
                <div class="shift-card-footer">
                    <?php if (Auth::isAdmin()): ?>
                        <!-- Admin: assign employee -->
                        <?php if (!empty($employees) && in_array($activePeriod['record_status'], ['REGISTRATION_OPEN', 'REVIEWING', 'PUBLISHED'], true)): ?>
                        <form method="POST" action="<?= BASE_URL ?>/schedule/assign" class="assign-form">
                            <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                            <select name="employee_id" class="form-select form-select-sm">
                                <option value="">Chọn nhân viên...</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Phân ca</button>
                        </form>
                        <?php endif; ?>

                        <!-- Registrations list -->
                        <?php if (!empty($detail['registrations'])): ?>
                        <div class="registrations-list">
                            <small class="text-muted">Đơn đăng ký:</small>
                            <?php foreach ($detail['registrations'] as $reg): ?>
                                <span class="reg-item"><?= htmlspecialchars($reg['full_name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Employee: register -->
                        <?php if ($activePeriod['record_status'] === 'REGISTRATION_OPEN'): ?>
                        <form method="POST" action="<?= BASE_URL ?>/schedule/register-slot">
                            <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                             <button type="submit" class="btn btn-sm btn-success"><i data-lucide="hand"></i> Đăng ký</button>
                        </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; // $activePeriod ?>
<?php else: ?>
<div class="card">
    <div class="card-body empty-state">
        <p>Chọn một kỳ lịch để xem chi tiết.</p>
        <?php if (empty($periods) && Auth::isAdmin()): ?>
             <a href="<?= BASE_URL ?>/schedule/create-period" class="btn btn-primary mt-3"><i data-lucide="plus"></i> Tạo kỳ lịch đầu tiên</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (Auth::isAdmin()): ?>
<div style="margin-top: 24px;">
    <a href="<?= BASE_URL ?>/schedule/create-period" class="btn btn-primary btn-full" style="padding: 14px; font-size: 1rem; border-radius: 99px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: var(--shadow-glow);">
        <i data-lucide="plus"></i> Tạo kỳ lịch
    </a>
</div>
<?php endif; ?>

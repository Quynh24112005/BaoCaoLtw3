<?php $pageTitle = 'Phiếu lương'; ?>
<div class="page-header">
    <div><h1 class="page-title"><i data-lucide="wallet" class="header-icon"></i> Phiếu lương cá nhân</h1>
    <p class="page-subtitle"><?= htmlspecialchars($period['name']) ?></p></div>
    <a href="<?= BASE_URL ?>/payroll" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Quay lại</a>
</div>

<div class="payslip-card">
    <div class="payslip-header">
        <div class="payslip-logo"><i data-lucide="zap" style="color: var(--primary); width: 20px; height: 20px; vertical-align: middle; margin-right: 4px;"></i> HR<span>AI</span></div>
        <div class="payslip-period">
            <div>Kỳ lương: <strong><?= htmlspecialchars($period['name']) ?></strong></div>
            <div><?= date('d/m/Y', strtotime($period['period_start'])) ?> — <?= date('d/m/Y', strtotime($period['period_end'])) ?></div>
        </div>
    </div>

    <div class="payslip-body">
        <table class="payslip-table">
            <tr><th>Lương cơ bản</th><td><?= number_format((float)$item['base_amount'], 0, ',', '.') ?> đ</td></tr>
            <tr><th>Tăng ca</th><td>+ <?= number_format((float)$item['overtime_amount'], 0, ',', '.') ?> đ</td></tr>
            <tr><th>Phụ cấp</th><td>+ <?= number_format((float)$item['allowance_amount'], 0, ',', '.') ?> đ</td></tr>
            <tr class="deduction"><th>Khấu trừ</th><td>- <?= number_format((float)$item['deduction_amount'], 0, ',', '.') ?> đ</td></tr>
            <tr class="total"><th><i data-lucide="wallet" style="width: 16px; height: 16px; margin-right: 6px; vertical-align: middle;"></i> Thực lĩnh</th><td><?= number_format((float)$item['final_amount'], 0, ',', '.') ?> đ</td></tr>
        </table>
    </div>

    <?php
    $snapshot = json_decode($item['calculation_snapshot_json'] ?? '{}', true);
    if (!empty($snapshot)):
    ?>
    <div class="payslip-snapshot">
        <h4>Chi tiết tính toán</h4>
        <ul>
            <li>Số giờ làm: <?= round(($snapshot['worked_minutes'] ?? 0) / 60, 1) ?> giờ</li>
            <li>Số phút tăng ca: <?= $snapshot['overtime_minutes'] ?? 0 ?> phút</li>
            <li>Số phút đi muộn: <?= $snapshot['late_minutes'] ?? 0 ?> phút</li>
            <li>Đơn giá giờ: <?= number_format($snapshot['hourly_rate'] ?? 0, 0, ',', '.') ?> đ/giờ</li>
        </ul>
    </div>
    <?php endif; ?>

    <div class="payslip-footer">
        <span class="badge badge-published"><i data-lucide="check" style="width: 14px; height: 14px; margin-right: 4px; vertical-align: middle;"></i> Đã công bố</span>
        <a href="<?= BASE_URL ?>/tickets/create" class="btn btn-sm btn-ghost"><i data-lucide="ticket" style="width: 14px; height: 14px; margin-right: 4px; vertical-align: middle;"></i> Khiếu nại</a>
    </div>
</div>

<?php 
$pageTitle = 'Chi tiết kỳ lương'; 
$statusMap = [
    'DRAFT' => 'Bản nháp',
    'CALCULATED' => 'Đã tính toán',
    'REVIEWING' => 'Đang duyệt',
    'PUBLISHED' => 'Đã công bố'
];
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><i data-lucide="wallet" class="header-icon"></i> <?= htmlspecialchars($period['name']) ?></h1>
        <p class="page-subtitle"><?= date('d/m/Y', strtotime($period['period_start'])) ?> — <?= date('d/m/Y', strtotime($period['period_end'])) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/payroll" class="btn btn-ghost"><i data-lucide="arrow-left"></i> Quay lại</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Nhân viên</th>
                    <th>Phòng ban</th>
                    <th>Lương cơ bản</th>
                    <th>Tăng ca</th>
                    <th>Phụ cấp</th>
                    <th>Khấu trừ</th>
                    <th class="text-success">Thực lĩnh</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="8" class="empty-state text-center">Chưa có dữ liệu. Hãy chạy tính lương.</td></tr>
                <?php else: ?>
                    <?php
                    $totalFinal = 0;
                    foreach ($items as $item):
                        $totalFinal += (float)$item['final_amount'];
                    ?>
                    <tr>
                        <td>
                            <div class="font-medium"><?= htmlspecialchars($item['full_name']) ?></div>
                            <div class="text-sm text-muted"><?= htmlspecialchars($item['employee_code']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($item['department'] ?? '—') ?></td>
                        <td><?= number_format((float)$item['base_amount'], 0, ',', '.') ?> đ</td>
                        <td><?= number_format((float)$item['overtime_amount'], 0, ',', '.') ?> đ</td>
                        <td><?= number_format((float)$item['allowance_amount'], 0, ',', '.') ?> đ</td>
                        <td class="text-danger">-<?= number_format((float)$item['deduction_amount'], 0, ',', '.') ?> đ</td>
                        <td class="text-success font-bold"><?= number_format((float)$item['final_amount'], 0, ',', '.') ?> đ</td>
                        <td><span class="badge badge-<?= strtolower($item['status']) ?>"><?= $statusMap[$item['status']] ?? $item['status'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-summary">
                        <td colspan="6" class="text-right font-bold">Tổng chi lương:</td>
                        <td class="text-success font-bold"><?= number_format($totalFinal, 0, ',', '.') ?> đ</td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $pageTitle = 'Phiếu lương của tôi'; ?>
<div class="page-header">
    <div><h1 class="page-title"><i data-lucide="wallet" class="header-icon"></i> Phiếu lương của tôi</h1></div>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr><th>Kỳ lương</th><th>Từ ngày</th><th>Đến ngày</th><th>Thực lĩnh</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="5" class="empty-state text-center">Chưa có phiếu lương nào được công bố.</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['period_name']) ?></td>
                        <td><?= date('d/m/Y', strtotime($item['period_start'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($item['period_end'])) ?></td>
                        <td class="text-success font-bold"><?= number_format((float)$item['final_amount'], 0, ',', '.') ?> đ</td>
                        <td><a href="<?= BASE_URL ?>/payroll/view?period=<?= $item['parent_id'] ?>" class="btn btn-sm btn-ghost"><i data-lucide="eye"></i> Xem</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

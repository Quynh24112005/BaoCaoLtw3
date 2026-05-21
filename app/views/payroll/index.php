<?php 
$pageTitle = 'Lương'; 
$statusMap = [
    'DRAFT' => 'Bản nháp',
    'CALCULATED' => 'Đã tính toán',
    'REVIEWING' => 'Đang duyệt',
    'PUBLISHED' => 'Đã công bố'
];
?>
<div class="page-header">
    <div><h1 class="page-title"><i data-lucide="wallet" class="header-icon"></i> Quản lý lương</h1></div>
</div>

<?php if (!empty($periods)): ?>
<div class="card">
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Tên kỳ</th>
                    <th>Từ ngày</th>
                    <th>Đến ngày</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($periods as $pp): ?>
                <tr>
                    <td class="font-medium"><?= htmlspecialchars($pp['name']) ?></td>
                    <td><?= date('d/m/Y', strtotime($pp['period_start'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($pp['period_end'])) ?></td>
                    <td><span class="badge badge-<?= strtolower($pp['status']) ?>"><?= $statusMap[$pp['status']] ?? $pp['status'] ?></span></td>
                    <td><?= date('d/m/Y', strtotime($pp['created_at'])) ?></td>
                    <td>
                        <div class="action-btns">
                             <a href="<?= BASE_URL ?>/payroll/view?period=<?= $pp['id'] ?>" class="btn btn-sm btn-ghost"><i data-lucide="eye"></i> Xem</a>
                             <?php if ($pp['status'] === 'DRAFT'): ?>
                                 <form method="POST" action="<?= BASE_URL ?>/payroll/calculate" style="display:inline">
                                     <input type="hidden" name="period_id" value="<?= $pp['id'] ?>">
                                     <button class="btn btn-sm btn-primary" onclick="return confirm('Chạy tính lương?')"><i data-lucide="cpu"></i> Tính lương</button>
                                 </form>
                             <?php elseif (in_array($pp['status'], ['CALCULATED', 'REVIEWING'], true)): ?>
                                 <form method="POST" action="<?= BASE_URL ?>/payroll/publish" style="display:inline">
                                     <input type="hidden" name="period_id" value="<?= $pp['id'] ?>">
                                     <button class="btn btn-sm btn-success" onclick="return confirm('Publish phiếu lương?')"><i data-lucide="check-circle"></i> Công bố</button>
                                 </form>
                             <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card"><div class="card-body empty-state">Chưa có kỳ lương nào. <a href="<?= BASE_URL ?>/payroll/create-period">Tạo ngay</a></div></div>
<?php endif; ?>

<?php if (Auth::isAdmin()): ?>
<div style="margin-top: 24px;">
    <a href="<?= BASE_URL ?>/payroll/create-period" class="btn btn-primary btn-full" style="padding: 14px; font-size: 1rem; border-radius: 99px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: var(--shadow-glow);">
        <i data-lucide="plus"></i> Tạo kỳ lương
    </a>
</div>
<?php endif; ?>

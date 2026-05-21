<?php $pageTitle = 'Tạo kỳ lương'; ?>
<div style="max-width: 500px; margin: 0 auto;">
    <div class="page-header">
        <div><h1 class="page-title"><i data-lucide="wallet" class="header-icon"></i> Tạo kỳ lương</h1>
        <p class="page-subtitle"><a href="<?= BASE_URL ?>/payroll">← Quay lại</a></p></div>
    </div>
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px;"><?php foreach ($errors as $e): ?><p style="display: flex; align-items: center; gap: 8px; margin: 0;"><i data-lucide="alert-triangle" style="width: 16px; height: 16px; flex-shrink: 0;"></i> <span><?= htmlspecialchars($e) ?></span></p><?php endforeach; ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/payroll/store-period">
                <div class="form-group">
                    <label class="form-label">Tên kỳ lương *</label>
                    <input type="text" name="name" class="form-input" required placeholder="Ví dụ: Lương tháng 5/2025">
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Từ ngày *</label>
                        <input type="date" name="period_start" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Đến ngày *</label>
                        <input type="date" name="period_end" class="form-input" required>
                    </div>
                </div>
                <div class="form-actions" style="margin-top: 20px;">
                    <a href="<?= BASE_URL ?>/payroll" class="btn btn-ghost">Hủy</a>
                    <button type="submit" class="btn btn-primary"><i data-lucide="plus-circle"></i> Tạo kỳ lương</button>
                </div>
            </form>
        </div>
    </div>
</div>


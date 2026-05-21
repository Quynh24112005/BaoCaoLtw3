<?php $pageTitle = 'Tạo kỳ lịch mới'; ?>
<div style="max-width: 500px; margin: 0 auto;">
    <div class="page-header">
        <div><h1 class="page-title"><i data-lucide="calendar" class="header-icon"></i> Tạo kỳ lịch mới</h1>
        <p class="page-subtitle"><a href="<?= BASE_URL ?>/schedule">← Quay lại</a></p></div>
    </div>
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px;"><?php foreach ($errors as $e): ?><p style="display: flex; align-items: center; gap: 8px; margin: 0;"><i data-lucide="alert-triangle" style="width: 16px; height: 16px; flex-shrink: 0;"></i> <span><?= htmlspecialchars($e) ?></span></p><?php endforeach; ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/schedule/store-period">
                <div class="form-group">
                    <label class="form-label">Ngày bắt đầu tuần *</label>
                    <input type="date" name="week_start_date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ngày kết thúc tuần *</label>
                    <input type="date" name="week_end_date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-input" rows="3" placeholder="Ghi chú về kỳ lịch này..."></textarea>
                </div>
                <p class="text-muted text-sm" style="margin-bottom: 16px;">Hệ thống sẽ tự tạo 3 ca (Sáng/Chiều/Đêm) cho mỗi ngày trong tuần.</p>
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/schedule" class="btn btn-ghost">Hủy</a>
                    <button type="submit" class="btn btn-primary"><i data-lucide="plus-circle"></i> Tạo kỳ lịch</button>
                </div>
            </form>
        </div>
    </div>
</div>


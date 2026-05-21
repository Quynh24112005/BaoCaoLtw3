<?php $pageTitle = 'Tạo đơn nghỉ phép'; ?>
<div style="max-width: 560px; margin: 0 auto;">
    <div class="page-header">
        <div><h1 class="page-title"><i data-lucide="palmtree" class="header-icon"></i> Tạo đơn xin nghỉ</h1>
        <p class="page-subtitle"><a href="<?= BASE_URL ?>/leave">← Quay lại</a></p></div>
    </div>
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px;"><?php foreach ($errors as $e): ?><p style="display: flex; align-items: center; gap: 8px; margin: 0;"><i data-lucide="alert-triangle" style="width: 16px; height: 16px; flex-shrink: 0;"></i> <span><?= htmlspecialchars($e) ?></span></p><?php endforeach; ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/leave/store">
                <div class="form-group">
                    <label class="form-label">Loại nghỉ phép *</label>
                    <select name="leave_type" class="form-select" required>
                        <option value="">-- Chọn --</option>
                        <option value="ANNUAL"  <?= ($old['leave_type'] ?? '') === 'ANNUAL'  ? 'selected' : '' ?>>Nghỉ phép năm</option>
                        <option value="SICK"    <?= ($old['leave_type'] ?? '') === 'SICK'    ? 'selected' : '' ?>>Nghỉ ốm</option>
                        <option value="UNPAID"  <?= ($old['leave_type'] ?? '') === 'UNPAID'  ? 'selected' : '' ?>>Nghỉ không lương</option>
                        <option value="OTHER"   <?= ($old['leave_type'] ?? '') === 'OTHER'   ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Từ ngày *</label>
                        <input type="date" name="start_date" class="form-input" required value="<?= $old['start_date'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Đến ngày *</label>
                        <input type="date" name="end_date" class="form-input" required value="<?= $old['end_date'] ?? '' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Lý do nghỉ *</label>
                    <textarea name="reason" class="form-input" rows="4" required placeholder="Nêu rõ lý do xin nghỉ phép..."><?= htmlspecialchars($old['reason'] ?? '') ?></textarea>
                </div>
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/leave" class="btn btn-ghost">Hủy</a>
                    <button type="submit" class="btn btn-primary"><i data-lucide="send"></i> Gửi đơn</button>
                </div>
            </form>
        </div>
    </div>
</div>


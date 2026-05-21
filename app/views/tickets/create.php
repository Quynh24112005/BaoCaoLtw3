<?php $pageTitle = 'Tạo khiếu nại'; ?>
<div style="max-width: 600px; margin: 0 auto;">
    <div class="page-header">
        <div><h1 class="page-title"><i data-lucide="ticket" class="header-icon"></i> Gửi khiếu nại</h1>
        <p class="page-subtitle"><a href="<?= BASE_URL ?>/tickets"><i data-lucide="arrow-left" style="width: 14px; height: 14px; vertical-align: middle;"></i> Quay lại</a></p></div>
    </div>
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px;"><?php foreach ($errors as $e): ?><p style="display: flex; align-items: center; gap: 8px; margin: 0;"><i data-lucide="alert-triangle" style="width: 16px; height: 16px; flex-shrink: 0;"></i> <span><?= htmlspecialchars($e) ?></span></p><?php endforeach; ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/tickets/store">
                <div class="form-group">
                    <label class="form-label">Tiêu đề *</label>
                    <input type="text" name="title" class="form-input" required minlength="5"
                           placeholder="Tóm tắt nội dung khiếu nại..." value="<?= htmlspecialchars($old['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Đối tượng khiếu nại *</label>
                    <select name="related_entity_type" class="form-select" required>
                        <option value="">-- Chọn --</option>
                        <option value="payroll_period" <?= ($old['related_entity_type'] ?? '') === 'payroll_period' ? 'selected' : '' ?>>Phiếu lương</option>
                        <option value="work_records"   <?= ($old['related_entity_type'] ?? '') === 'work_records'   ? 'selected' : '' ?>>Lịch / Chấm công</option>
                        <option value="general"        <?= ($old['related_entity_type'] ?? '') === 'general'        ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ID đối tượng (nếu có)</label>
                    <input type="number" name="related_entity_id" class="form-input" min="0"
                           placeholder="ID của phiếu lương / bản ghi liên quan..." value="<?= htmlspecialchars($old['related_entity_id'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Nội dung khiếu nại *</label>
                    <textarea name="description" class="form-input" rows="6" required minlength="10"
                              placeholder="Mô tả chi tiết vấn đề bạn gặp phải..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                </div>
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/tickets" class="btn btn-ghost">Hủy</a>
                    <button type="submit" class="btn btn-primary"><i data-lucide="send"></i> Gửi khiếu nại</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php $pageTitle = 'Cập nhật hồ sơ'; ?>
<div style="max-width: 600px; margin: 0 auto;">
    <div class="page-header">
        <div><h1 class="page-title"><i data-lucide="edit" class="header-icon"></i> Cập nhật hồ sơ</h1>
        <p class="page-subtitle"><a href="<?= BASE_URL ?>/profile"><i data-lucide="arrow-left" style="width: 14px; height: 14px; vertical-align: middle;"></i> Quay lại</a></p></div>
    </div>
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px;"><?php foreach($errors as $e): ?><p style="display: flex; align-items: center; gap: 8px; margin: 0;"><i data-lucide="alert-triangle" style="width: 16px; height: 16px; flex-shrink: 0;"></i> <span><?= htmlspecialchars($e) ?></span></p><?php endforeach; ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/profile/edit">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Họ và tên *</label>
                        <input type="text" name="full_name" class="form-input" required value="<?= htmlspecialchars($user['full_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Số điện thoại</label>
                        <input type="tel" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ngày sinh</label>
                        <input type="date" name="date_of_birth" class="form-input" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giới tính</label>
                        <select name="gender" class="form-select">
                            <option value="">--</option>
                            <option value="MALE"   <?= ($user['gender']??'')=='MALE'   ?'selected':'' ?>>Nam</option>
                            <option value="FEMALE" <?= ($user['gender']??'')=='FEMALE' ?'selected':'' ?>>Nữ</option>
                            <option value="OTHER"  <?= ($user['gender']??'')=='OTHER'  ?'selected':'' ?>>Khác</option>
                        </select>
                    </div>
                </div>
                <hr style="border-color:var(--border);margin:16px 0">
                <p class="text-muted text-sm" style="margin-bottom:12px">Đổi mật khẩu (để trống nếu không muốn đổi)</p>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mật khẩu mới (≥6 ký tự)</label>
                        <input type="password" name="new_password" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" class="form-input">
                    </div>
                </div>
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/profile" class="btn btn-ghost">Hủy</a>
                    <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>


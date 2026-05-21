<?php $pageTitle = 'Thêm nhân viên'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i data-lucide="user-plus" class="header-icon"></i> Thêm nhân viên</h1>
        <p class="page-subtitle"><a href="<?= BASE_URL ?>/employees">← Quay lại danh sách</a></p>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger" style="display: flex; flex-direction: column; gap: 4px;">
    <?php foreach ($errors as $e): ?><p style="display: flex; align-items: center; gap: 8px; margin: 0;"><i data-lucide="alert-triangle" style="width: 16px; height: 16px; flex-shrink: 0;"></i> <span><?= htmlspecialchars($e) ?></span></p><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/employees/store" id="createForm">
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="full_name">Họ và tên *</label>
                    <input type="text" id="full_name" name="full_name" class="form-input"
                           value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-input"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Mật khẩu *</label>
                    <input type="password" id="password" name="password" class="form-input" minlength="6" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" class="form-input"
                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="date_of_birth">Ngày sinh</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-input"
                           value="<?= htmlspecialchars($old['date_of_birth'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="gender">Giới tính</label>
                    <select id="gender" name="gender" class="form-select">
                        <option value="">-- Chọn --</option>
                        <option value="MALE" <?= ($old['gender'] ?? '') === 'MALE' ? 'selected' : '' ?>>Nam</option>
                        <option value="FEMALE" <?= ($old['gender'] ?? '') === 'FEMALE' ? 'selected' : '' ?>>Nữ</option>
                        <option value="OTHER" <?= ($old['gender'] ?? '') === 'OTHER' ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="department">Phòng ban</label>
                    <input type="text" id="department" name="department" class="form-input"
                           value="<?= htmlspecialchars($old['department'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="position">Chức vụ</label>
                    <input type="text" id="position" name="position" class="form-input"
                           value="<?= htmlspecialchars($old['position'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="role">Vai trò *</label>
                    <select id="role" name="role" class="form-select">
                        <option value="EMPLOYEE" <?= ($old['role'] ?? 'EMPLOYEE') === 'EMPLOYEE' ? 'selected' : '' ?>>Nhân viên</option>
                        <option value="ADMIN" <?= ($old['role'] ?? '') === 'ADMIN' ? 'selected' : '' ?>>Quản trị viên</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="employment_type">Loại hợp đồng</label>
                    <select id="employment_type" name="employment_type" class="form-select">
                        <option value="FULL_TIME">Full-time</option>
                        <option value="PART_TIME">Part-time</option>
                        <option value="CONTRACT">Hợp đồng</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="base_salary">Lương cơ bản (VNĐ/tháng)</label>
                    <input type="number" id="base_salary" name="base_salary" class="form-input" min="0" step="100000"
                           value="<?= htmlspecialchars($old['base_salary'] ?? '0') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="hourly_rate">Lương theo giờ (VNĐ/giờ)</label>
                    <input type="number" id="hourly_rate" name="hourly_rate" class="form-input" min="0" step="1000"
                           value="<?= htmlspecialchars($old['hourly_rate'] ?? '0') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="start_date">Ngày bắt đầu</label>
                    <input type="date" id="start_date" name="start_date" class="form-input"
                           value="<?= htmlspecialchars($old['start_date'] ?? '') ?>">
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>/employees" class="btn btn-ghost">Hủy</a>
                <button type="submit" class="btn btn-primary">Tạo nhân viên</button>
            </div>
        </form>
    </div>
</div>

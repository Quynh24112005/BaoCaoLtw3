<?php $pageTitle = 'Sửa nhân viên'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i data-lucide="edit-3" class="header-icon"></i> Sửa hồ sơ nhân viên</h1>
        <p class="page-subtitle"><a href="<?= BASE_URL ?>/employees">← Quay lại</a></p>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger" style="display: flex; flex-direction: column; gap: 4px;">
    <?php foreach ($errors as $e): ?><p style="display: flex; align-items: center; gap: 8px; margin: 0;"><i data-lucide="alert-triangle" style="width: 16px; height: 16px; flex-shrink: 0;"></i> <span><?= htmlspecialchars($e) ?></span></p><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/employees/update">
            <input type="hidden" name="id" value="<?= $employee['id'] ?>">

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="full_name">Họ và tên *</label>
                    <input type="text" id="full_name" name="full_name" class="form-input"
                           value="<?= htmlspecialchars($employee['full_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email (không thể đổi)</label>
                    <input type="email" class="form-input" value="<?= htmlspecialchars($employee['email']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" class="form-input"
                           value="<?= htmlspecialchars($employee['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="date_of_birth">Ngày sinh</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-input"
                           value="<?= htmlspecialchars($employee['date_of_birth'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="gender">Giới tính</label>
                    <select id="gender" name="gender" class="form-select">
                        <option value="">-- Chọn --</option>
                        <option value="MALE"   <?= ($employee['gender'] ?? '') === 'MALE'   ? 'selected' : '' ?>>Nam</option>
                        <option value="FEMALE" <?= ($employee['gender'] ?? '') === 'FEMALE' ? 'selected' : '' ?>>Nữ</option>
                        <option value="OTHER"  <?= ($employee['gender'] ?? '') === 'OTHER'  ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="department">Phòng ban</label>
                    <input type="text" id="department" name="department" class="form-input"
                           value="<?= htmlspecialchars($employee['department'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="position">Chức vụ</label>
                    <input type="text" id="position" name="position" class="form-input"
                           value="<?= htmlspecialchars($employee['position'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="employment_type">Loại hợp đồng</label>
                    <select id="employment_type" name="employment_type" class="form-select">
                        <option value="FULL_TIME" <?= $employee['employment_type'] === 'FULL_TIME' ? 'selected' : '' ?>>Full-time</option>
                        <option value="PART_TIME" <?= $employee['employment_type'] === 'PART_TIME' ? 'selected' : '' ?>>Part-time</option>
                        <option value="CONTRACT"  <?= $employee['employment_type'] === 'CONTRACT'  ? 'selected' : '' ?>>Hợp đồng</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="base_salary">Lương cơ bản (VNĐ/tháng)</label>
                    <input type="number" id="base_salary" name="base_salary" class="form-input" min="0"
                           value="<?= htmlspecialchars($employee['base_salary'] ?? '0') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="hourly_rate">Lương theo giờ (VNĐ/giờ)</label>
                    <input type="number" id="hourly_rate" name="hourly_rate" class="form-input" min="0"
                           value="<?= htmlspecialchars($employee['hourly_rate'] ?? '0') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="start_date">Ngày bắt đầu</label>
                    <input type="date" id="start_date" name="start_date" class="form-input"
                           value="<?= htmlspecialchars($employee['start_date'] ?? '') ?>">
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>/employees" class="btn btn-ghost">Hủy</a>
                <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

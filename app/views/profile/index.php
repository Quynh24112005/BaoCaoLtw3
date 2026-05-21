<?php 
$pageTitle = 'Hồ sơ cá nhân'; 
$roleMap = [
    'ADMIN' => 'Quản trị viên',
    'EMPLOYEE' => 'Nhân viên'
];
$genderMap = [
    'MALE' => 'Nam',
    'FEMALE' => 'Nữ',
    'OTHER' => 'Khác'
];
$statusMap = [
    'ACTIVE' => 'Hoạt động',
    'INACTIVE' => 'Ngừng hoạt động',
    'SUSPENDED' => 'Tạm đình chỉ'
];
$employmentTypeMap = [
    'FULL_TIME' => 'Toàn thời gian',
    'PART_TIME' => 'Bán thời gian',
    'CONTRACT' => 'Hợp đồng',
    'INTERN' => 'Thực tập sinh'
];
?>
<div class="page-header">
    <div><h1 class="page-title"><i data-lucide="user" class="header-icon"></i> Hồ sơ cá nhân</h1></div>
</div>
<div class="grid-2">
<div class="card">
    <div class="card-body" style="text-align:center">
        <div class="profile-avatar"><?= strtoupper(mb_substr($user['full_name'],0,1)) ?></div>
        <h2 style="margin-bottom:4px"><?= htmlspecialchars($user['full_name']) ?></h2>
        <p class="text-muted"><?= htmlspecialchars($user['position'] ?? '') ?></p>
        <span class="badge badge-<?= strtolower($user['role']) ?>" style="margin-top:8px"><?= $roleMap[$user['role']] ?? $user['role'] ?></span>
    </div>
</div>
<div class="card">
    <div class="card-header"><h2 class="card-title">Thông tin chi tiết</h2></div>
    <div class="card-body">
        <div class="profile-info-list">
            <?php $fields = ['employee_code'=>'Mã NV','email'=>'Email','phone'=>'Điện thoại','date_of_birth'=>'Ngày sinh','gender'=>'Giới tính','department'=>'Phòng ban','employment_type'=>'Loại HĐ','start_date'=>'Ngày vào','status'=>'Trạng thái']; ?>
            <?php foreach ($fields as $key => $label): ?>
            <div class="profile-row">
                <span class="profile-key"><?= $label ?></span>
                <span>
                    <?php
                    $val = $user[$key];
                    if ($key === 'gender') {
                        echo $genderMap[$val] ?? $val ?? '—';
                    } elseif ($key === 'status') {
                        echo $statusMap[$val] ?? $val ?? '—';
                    } elseif ($key === 'employment_type') {
                        echo $employmentTypeMap[$val] ?? $val ?? '—';
                    } elseif ($key === 'date_of_birth' || $key === 'start_date') {
                        echo $val ? date('d/m/Y', strtotime($val)) : '—';
                    } else {
                        echo htmlspecialchars($val ?? '—');
                    }
                    ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>

<div style="margin-top: 24px;">
    <a href="<?= BASE_URL ?>/profile/edit" class="btn btn-primary btn-full" style="padding: 14px; font-size: 1rem; border-radius: 99px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: var(--shadow-glow);">
        <i data-lucide="edit"></i> Chỉnh sửa
    </a>
</div>

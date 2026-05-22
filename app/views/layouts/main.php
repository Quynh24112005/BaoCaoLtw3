<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HR Management System - Hệ thống quản lý nhân sự">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — HR System' : 'HR Management System' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/app.css">
    <?php if (!empty($pageCSS)): foreach ((array)$pageCSS as $_css): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/<?= htmlspecialchars($_css) ?>">
    <?php endforeach; endif; ?>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
</head>
<body>

<!-- Sidebar Dock -->
<aside class="sidebar" id="sidebar">
    <!-- Header (Brand logo) inside sidebar -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="logo-icon"><i data-lucide="zap"></i></span>
            <span class="logo-text">Quản lý nhân viên</span></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-label">Tổng quan</span>
            <a href="<?= BASE_URL ?>/home" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/home') !== false ? 'active' : '' ?>">
                <i data-lucide="layout-dashboard" class="nav-icon"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= BASE_URL ?>/profile" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/profile') !== false ? 'active' : '' ?>">
                <i data-lucide="user" class="nav-icon"></i>
                <span>Hồ sơ cá nhân</span>
            </a>
        </div>

        <div class="nav-section">
            <span class="nav-label">Nghiệp vụ</span>
            <a href="<?= BASE_URL ?>/leave" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/leave') !== false ? 'active' : '' ?>">
                <i data-lucide="palmtree" class="nav-icon"></i>
                <span>Nghỉ phép</span>
            </a>
            <a href="<?= BASE_URL ?>/attendance" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/attendance') !== false ? 'active' : '' ?>">
                <i data-lucide="clock" class="nav-icon"></i>
                <span>Chấm công</span>
            </a>
        </div>

        <?php if (Auth::isAdmin()): ?>
        <div class="nav-section">
            <span class="nav-label">Quản trị</span>
            <a href="<?= BASE_URL ?>/employees" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/employees') !== false ? 'active' : '' ?>">
                <i data-lucide="users" class="nav-icon"></i>
                <span>Quản lý nhân viên</span>
            </a>

        </div>
        <?php endif; ?>
    </nav>

    <!-- Footer (User & Logout) inside sidebar -->
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar"><?= strtoupper(mb_substr(Session::get('user_name', 'U'), 0, 1)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars(Session::get('user_name', '')) ?></div>
                <div class="user-role"><?= Auth::role() === 'ADMIN' ? 'Quản trị viên' : 'Nhân viên' ?></div>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/auth/logout" class="btn-logout">
            <i data-lucide="log-out"></i>
            <span>Đăng xuất</span>
        </a>
    </div>
</aside>

<!-- Main content -->
<main class="main-content" id="main">

    <!-- Top bar -->
    <header class="topbar">
        <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">☰</button>
        <div class="topbar-right">
            <?php $flash = Session::flash('success'); if ($flash): ?>
                <div class="flash-msg flash-success">✓ <?= htmlspecialchars($flash) ?></div>
            <?php endif; ?>
            <?php $flashErr = Session::flash('error'); if ($flashErr): ?>
                <div class="flash-msg flash-error">✕ <?= htmlspecialchars($flashErr) ?></div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Page body -->
    <div class="page-body">
        <?= $content ?>
    </div>
</main>

<script src="<?= BASE_URL ?>/public/js/app.js"></script>
<?php if (!empty($pageJS)): foreach ((array)$pageJS as $_js): ?>
<script src="<?= BASE_URL ?>/public/js/<?= htmlspecialchars($_js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>

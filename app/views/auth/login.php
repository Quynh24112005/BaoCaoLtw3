<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <span class="logo-icon-lg" style="color: var(--primary); display: flex; align-items: center; justify-content: center; margin-bottom: 12px;"><i data-lucide="zap" style="width: 48px; height: 48px; stroke-width: 2.5px;"></i></span>
            <h1 class="auth-title">HR<span class="logo-accent">Core</span></h1>
            <p class="auth-subtitle">HR Management System</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="display: flex; flex-direction: column; gap: 4px;">
                <?php foreach ($errors as $err): ?>
                    <p style="display: flex; align-items: center; gap: 8px; margin: 0;"><i data-lucide="alert-triangle" style="width: 16px; height: 16px; flex-shrink: 0;"></i> <span><?= htmlspecialchars($err) ?></span></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/auth/login" class="auth-form" id="loginForm">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    placeholder="name@company.com"
                    required
                    autocomplete="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mật khẩu</label>
                <div class="input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="btn-eye" onclick="togglePassword()" aria-label="Hiển thị mật khẩu"><i data-lucide="eye" style="width: 18px; height: 18px;"></i></button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full" id="loginBtn">
                <span>Đăng nhập</span>
            </button>
        </form>

        <div class="auth-hint">
            <small>Demo: <code>admin@hr.vn</code> / <code>123456</code></small>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const pw = document.getElementById('password');
    const eyeBtn = document.querySelector('.btn-eye');
    if (pw.type === 'password') {
        pw.type = 'text';
        eyeBtn.innerHTML = '<i data-lucide="eye-off" style="width: 18px; height: 18px;"></i>';
    } else {
        pw.type = 'password';
        eyeBtn.innerHTML = '<i data-lucide="eye" style="width: 18px; height: 18px;"></i>';
    }
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('loginBtn').textContent = 'Đang xử lý...';
});
</script>

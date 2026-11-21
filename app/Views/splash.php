<div class="splash-page">
    <div class="splash-card">

        <div class="splash-logo">
            <img src="<?= url('/assets/img/logo-mark.svg') ?>" alt="Kodafirca POS">
        </div>

        <h1 class="splash-brand">Kodafirca <span>POS</span></h1>
        <p class="splash-tagline">Point of Sale System</p>

        <div class="splash-divider"></div>

        <?php if ($employeeCount === 0): ?>
            <p style="color:#8892B0;font-size:14px;margin:0 0 14px">Welcome! Let's set up your business.</p>
            <div class="splash-actions">
                <a href="<?= url('/register') ?>" class="splash-btn primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Create Owner Account
                </a>
            </div>
        <?php else: ?>
            <p style="color:#8892B0;font-size:14px;margin:0 0 14px">Welcome back — sign in to continue.</p>
            <div class="splash-actions">
                <a href="<?= url('/login') ?>" class="splash-btn primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    Sign In
                </a>
                <a href="<?= url('/register') ?>" class="splash-btn ghost">New here? Register</a>
            </div>
        <?php endif; ?>

    </div>

    <div class="splash-footer">v1.0 · © KODAFIRCA <?= date('Y') ?></div>
</div>

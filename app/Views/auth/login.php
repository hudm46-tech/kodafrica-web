<h1 class="auth-title">Kodafirca <span>POS</span></h1>
<p class="auth-sub">Sign in to continue</p>

<?php foreach (\App\Core\Flash::all() as $f): ?>
    <div class="flash <?= e($f['type']) ?>" style="margin-top:20px"><?= e($f['message']) ?></div>
<?php endforeach; ?>

<form method="post" action="<?= url('/login') ?>" style="margin-top:24px">
    <?= csrf_field() ?>

    <div class="field">
        <label>Phone Number</label>
        <input type="tel" name="phone" value="<?= e(old('phone')) ?>" placeholder="07XX XXX XXX" autofocus required>
    </div>

    <div class="field">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter password" required>
    </div>

    <button type="submit" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
        Sign In
    </button>
</form>

<div style="text-align:center;margin-top:20px;font-size:11px;color:var(--faint)">
    Contact your administrator if you don't have an account
</div>

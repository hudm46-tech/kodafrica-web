<h1 class="auth-title">Create <span>Account</span></h1>
<p class="auth-sub">Set up your business owner profile</p>

<?php foreach (\App\Core\Flash::all() as $f): ?>
    <div class="flash <?= e($f['type']) ?>" style="margin-top:20px"><?= e($f['message']) ?></div>
<?php endforeach; ?>

<form method="post" action="<?= url('/register') ?>" style="margin-top:24px">
    <?= csrf_field() ?>

    <div class="field">
        <label>Business Name</label>
        <input type="text" name="business_name" value="<?= e(old('business_name')) ?>" placeholder="e.g., Kodafirca Hardware" required>
    </div>

    <div class="field">
        <label>Your Full Name</label>
        <input type="text" name="full_name" value="<?= e(old('full_name')) ?>" placeholder="John Mwangi" required>
    </div>

    <div class="field">
        <label>Phone Number (used to login)</label>
        <input type="tel" name="phone" value="<?= e(old('phone')) ?>" placeholder="07XX XXX XXX" required>
    </div>

    <div class="field">
        <label>Email (optional)</label>
        <input type="email" name="email" value="<?= e(old('email')) ?>" placeholder="you@example.com">
    </div>

    <div class="field-row">
        <div class="field">
            <label>Password</label>
            <input type="password" name="password" placeholder="Min 6 chars" required>
        </div>
        <div class="field">
            <label>Confirm</label>
            <input type="password" name="confirm_password" placeholder="Repeat" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        Create Account
    </button>
</form>

<div class="link-row">
    Already have an account? <a href="<?= url('/login') ?>">Sign In</a>
</div>

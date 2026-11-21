<h1 class="auth-title">Add <span>Branch</span></h1>
<p class="auth-sub">You can add more branches later</p>

<?php foreach (\App\Core\Flash::all() as $f): ?>
    <div class="flash <?= e($f['type']) ?>" style="margin-top:20px"><?= e($f['message']) ?></div>
<?php endforeach; ?>

<form method="post" action="<?= url('/setup/branch') ?>" style="margin-top:24px">
    <?= csrf_field() ?>

    <div class="field">
        <label>Branch Name</label>
        <input type="text" name="branch_name" value="<?= e(old('branch_name')) ?>" placeholder="e.g., Msata Main" required>
    </div>

    <div class="field">
        <label>Location</label>
        <input type="text" name="location" value="<?= e(old('location')) ?>" placeholder="Msata Town, Bagamoyo Road" required>
    </div>

    <div class="field">
        <label>Phone Number</label>
        <input type="tel" name="phone_number" value="<?= e(old('phone_number')) ?>" placeholder="07XX XXX XXX" required>
    </div>

    <div class="field">
        <label>TIN Number</label>
        <input type="text" name="tin_number" value="<?= e(old('tin_number')) ?>" placeholder="123-456-789">
    </div>

    <div class="field-row">
        <div class="field">
            <label>License Number</label>
            <input type="text" name="license_number" value="<?= e(old('license_number')) ?>" placeholder="e.g., BRELA-12345" required>
        </div>
        <div class="field">
            <label>License Expiry</label>
            <input type="date" name="license_expiry_date" value="<?= e(old('license_expiry_date')) ?>" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-green">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
        Save Branch &amp; Continue
    </button>
</form>

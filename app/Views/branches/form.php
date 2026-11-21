<?php
$isEdit = $branch !== null;
$action = $isEdit ? url('/branches/' . $branch['branch_id']) : url('/branches');

$val = fn(string $k, $fallback = '') => $isEdit ? ($branch[$k] ?? $fallback) : old($k, $fallback);

$currentName     = $val('branch_name');
$currentLocation = $val('location');
$currentPhone    = $val('phone_number');
$currentTin      = $val('tin_number');
$currentLicense  = $val('license_number');
$currentExpiry   = $val('license_expiry_date');
?>

<form method="post" action="<?= $action ?>" class="card">
    <?= csrf_field() ?>

    <div style="margin-bottom:16px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Branch Name *</label>
        <input type="text" name="branch_name" value="<?= e($currentName) ?>" placeholder="e.g., Msata Main" required autofocus style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
    </div>

    <div style="margin-bottom:16px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Location *</label>
        <input type="text" name="location" value="<?= e($currentLocation) ?>" placeholder="e.g., Msata Town, Bagamoyo Road" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
    </div>

    <div class="branch-form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Phone Number *</label>
            <input type="tel" name="phone_number" value="<?= e($currentPhone) ?>" placeholder="07XX XXX XXX" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">TIN Number</label>
            <input type="text" name="tin_number" value="<?= e($currentTin) ?>" placeholder="Optional" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <div class="branch-form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">License Number *</label>
            <input type="text" name="license_number" value="<?= e($currentLicense) ?>" placeholder="e.g., BRELA-12345" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">License Expiry *</label>
            <input type="date" name="license_expiry_date" value="<?= e($currentExpiry) ?>" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1">
            <?= $isEdit ? 'Update Branch' : 'Create Branch' ?>
        </button>
        <a href="<?= url($isEdit ? '/branches' : '/select-branch') ?>" class="btn" style="background:rgba(255,255,255,.05);color:var(--text);text-decoration:none;padding:15px 24px">Cancel</a>
    </div>
</form>

<style>
@media (max-width: 640px) {
    .branch-form-grid { grid-template-columns: 1fr !important; }
}
</style>

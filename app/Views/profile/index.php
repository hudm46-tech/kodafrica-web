<?php
$initial = strtoupper(substr($user['full_name'] ?? 'U', 0, 1));
$roleColor = $user['role'] === 'ADMIN' ? 'var(--red)' : ($user['role'] === 'MANAGER' ? 'var(--orange)' : 'var(--cyan)');
$roleBg = $user['role'] === 'ADMIN' ? 'rgba(255,23,68,.15)' : ($user['role'] === 'MANAGER' ? 'rgba(255,145,0,.15)' : 'rgba(0,180,216,.15)');
?>

<!-- Profile header -->
<div class="card" style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
    <div style="width:64px;height:64px;border-radius:16px;background:<?= $roleBg ?>;color:<?= $roleColor ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:26px;flex-shrink:0">
        <?= e($initial) ?>
    </div>
    <div style="flex:1;min-width:0">
        <div style="font-size:18px;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= e($user['full_name']) ?>
        </div>
        <div style="font-size:11px;color:<?= $roleColor ?>;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-top:3px">
            <?= e($user['role']) ?>
        </div>
        <div style="font-size:12px;color:var(--muted);margin-top:4px"><?= e($user['phone_number']) ?></div>
    </div>
</div>

<!-- Account info -->
<div class="section-title">Account Information</div>
<form method="post" action="<?= url('/profile/info') ?>" class="card">
    <?= csrf_field() ?>

    <div style="margin-bottom:14px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Full Name</label>
        <input type="text" name="full_name" value="<?= e($user['full_name']) ?>" required
               style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:14px">
    </div>

    <div class="profile-info-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Phone (Login)</label>
            <input type="tel" name="phone_number" value="<?= e($user['phone_number']) ?>" required
                   style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:14px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Email</label>
            <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" placeholder="Optional"
                   style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Profile</button>
</form>

<!-- Change password -->
<div class="section-title">Change Password</div>
<form method="post" action="<?= url('/profile/password') ?>" class="card">
    <?= csrf_field() ?>

    <div style="margin-bottom:16px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Current Password</label>
        <input type="password" name="current_password" required autocomplete="current-password"
               style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:14px">
    </div>

    <div class="profile-info-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">New Password</label>
            <input type="password" name="new_password" required minlength="6" autocomplete="new-password"
                   style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:14px">
            <div style="font-size:10px;color:var(--muted);margin-top:6px">Minimum 6 characters</div>
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Confirm New Password</label>
            <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password"
                   style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Change Password</button>
</form>

<!-- Account details -->
<div class="section-title">Account Details</div>
<div class="card">
    <div class="profile-info-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13px">
        <div>
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;font-weight:700">Hire Date</div>
            <div style="margin-top:4px"><?= e(date('d M Y', strtotime($user['hire_date']))) ?></div>
        </div>
        <div>
            <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;font-weight:700">Employment Type</div>
            <div style="margin-top:4px"><?= e($user['employment_type']) ?></div>
        </div>
    </div>

    <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--divider)">
        <form method="post" action="<?= url('/logout') ?>" style="margin:0">
            <?= csrf_field() ?>
            <button type="submit" style="width:100%;padding:12px;border-radius:10px;background:rgba(255,23,68,.1);color:var(--red);border:1px solid rgba(255,23,68,.3);font-weight:700;cursor:pointer;font-family:inherit;font-size:13px">
                Sign Out
            </button>
        </form>
    </div>
</div>

<style>
@media (max-width: 640px) {
    .profile-info-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php $logo = $business['business_logo_path'] ?? null; ?>

<div class="mini-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:20px">
    <div class="mini">
        <div class="mini-label">CURRENT BUSINESS</div>
        <div style="font-size:14px;font-weight:700;margin-top:6px"><?= e($business['business_name'] ?? '-') ?></div>
        <?php if (!empty($business['business_tin'])): ?>
            <div style="font-size:11px;color:var(--muted);margin-top:2px">TIN: <?= e($business['business_tin']) ?></div>
        <?php endif; ?>
    </div>
    <div class="mini">
        <div class="mini-label">ENTITY TYPE</div>
        <div style="font-size:14px;font-weight:700;margin-top:6px">
            <?= $tax['entity_type'] === 'LIMITED_COMPANY' ? 'Limited Company' : 'Sole Proprietor' ?>
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:2px">VAT: <?= number_format((float)($tax['vat_rate'] ?? 18), 1) ?>%</div>
    </div>
</div>

<?php if (!$isAdmin): ?>
    <div class="card" style="text-align:center;padding:40px 20px">
        <div style="font-size:36px;margin-bottom:12px">🔒</div>
        <div style="font-size:15px;font-weight:700">Admin Only</div>
        <div style="font-size:12px;color:var(--muted);margin-top:6px">Only owners can change business settings</div>
        <a href="<?= url('/dashboard') ?>" class="btn btn-primary" style="display:inline-flex;width:auto;margin-top:16px;padding:12px 24px">Back to Dashboard</a>
    </div>
<?php else: ?>

<!-- ===== BUSINESS INFO ===== -->
<div class="section-title">Business Information</div>
<form method="post" action="<?= url('/settings/business') ?>" class="card">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Business Name *</label>
            <input type="text" name="business_name" value="<?= e($business['business_name'] ?? '') ?>" required style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Tagline</label>
            <input type="text" name="business_tagline" value="<?= e($business['business_tagline'] ?? '') ?>" placeholder="Quality Hardware" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
    </div>

    <div style="margin-bottom:14px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Address</label>
        <input type="text" name="business_address" value="<?= e($business['business_address'] ?? '') ?>" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Phone</label>
            <input type="tel" name="business_phone" value="<?= e($business['business_phone'] ?? '') ?>" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Email</label>
            <input type="email" name="business_email" value="<?= e($business['business_email'] ?? '') ?>" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">TIN</label>
            <input type="text" name="business_tin" value="<?= e($business['business_tin'] ?? '') ?>" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">VRN (VAT)</label>
            <input type="text" name="business_vrn" value="<?= e($business['business_vrn'] ?? '') ?>" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Business Info</button>
</form>

<!-- ===== LOGO ===== -->
<div class="section-title">Logo</div>
<div class="card">
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:16px">
        <div style="width:80px;height:80px;border-radius:16px;background:#0A1929;border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;overflow:hidden">
            <?php if ($logo): ?>
                <img src="<?= url('/uploads/' . $logo) ?>" alt="Logo" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
                <span style="font-size:36px">🏪</span>
            <?php endif; ?>
        </div>
        <form method="post" action="<?= url('/settings/logo') ?>" enctype="multipart/form-data" style="flex:1">
            <?= csrf_field() ?>
            <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" required id="logoInput" style="display:none" onchange="this.form.submit()">
            <label for="logoInput" style="display:inline-block;padding:10px 20px;background:rgba(0,180,216,.15);color:var(--cyan);border-radius:10px;font-size:13px;font-weight:700;cursor:pointer">Choose Logo Image</label>
            <div style="font-size:11px;color:var(--muted);margin-top:6px">JPG · PNG · WEBP · max 2 MB</div>
        </form>
    </div>
</div>

<!-- ===== TAX SETTINGS ===== -->
<div class="section-title">Tax Settings</div>
<form method="post" action="<?= url('/settings/tax') ?>" class="card">
    <?= csrf_field() ?>

    <div style="margin-bottom:16px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Entity Type</label>
        <select name="entity_type" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
            <option value="SOLE_PROPRIETOR" <?= ($tax['entity_type'] ?? '') === 'SOLE_PROPRIETOR' ? 'selected' : '' ?>>Sole Proprietor (Presumptive Tax)</option>
            <option value="LIMITED_COMPANY" <?= ($tax['entity_type'] ?? '') === 'LIMITED_COMPANY' ? 'selected' : '' ?>>Limited Company (Corporate Tax)</option>
        </select>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Corporate Rate (%)</label>
            <input type="number" step="0.1" name="corporate_rate" value="<?= e($tax['corporate_rate'] ?? 30) ?>" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">VAT Rate (%)</label>
            <input type="number" step="0.1" name="vat_rate" value="<?= e($tax['vat_rate'] ?? 18) ?>" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">VAT Threshold (TZS)</label>
            <input type="number" step="1000000" name="vat_threshold" value="<?= e($tax['vat_registration_threshold'] ?? 100000000) ?>" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Tax Settings</button>
</form>

<!-- ===== SECURITY ===== -->
<div class="section-title">Change Password</div>
<form method="post" action="<?= url('/settings/password') ?>" class="card">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Current Password</label>
            <input type="password" name="current_password" required style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
        <div></div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">New Password</label>
            <input type="password" name="new_password" required minlength="6" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Confirm New</label>
            <input type="password" name="confirm_password" required minlength="6" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Change Password</button>
</form>

<!-- ===== BACKUP ===== -->
<div class="section-title">Database Backup</div>
<div class="card" style="display:flex;justify-content:space-between;align-items:center">
    <div>
        <div style="font-size:14px;font-weight:700">Download Backup</div>
        <div style="font-size:12px;color:var(--muted);margin-top:4px">Get a full SQL dump of your business data</div>
    </div>
    <a href="<?= url('/settings/backup') ?>" class="btn btn-primary" style="width:auto;padding:12px 24px;text-decoration:none">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:6px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Download .sql
    </a>
</div>

<?php endif; ?>

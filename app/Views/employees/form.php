<?php
$isEdit = $employee !== null;
$action = $isEdit ? url('/employees/' . $employee['employee_id']) : url('/employees');

$val = fn(string $k, $fallback = '') => $isEdit ? ($employee[$k] ?? $fallback) : old($k, $fallback);

$currentName    = $val('full_name');
$currentPhone   = $val('phone_number');
$currentEmail   = $val('email');
$currentRole    = $val('role', 'CASHIER');
$currentType    = $val('employment_type', 'PERMANENT');
$currentSalary  = $val('monthly_salary', 0);
$currentRate    = $val('daily_rate', 0);
$currentHire    = $isEdit ? date('Y-m-d', strtotime($employee['hire_date'])) : old('hire_date', date('Y-m-d'));
$currentNssf    = $val('nssf_number');
$currentNotes   = $val('notes');
$isActive       = $isEdit ? (int)$employee['is_active'] === 1 : true;
?>

<form method="post" action="<?= $action ?>" class="card">
    <?= csrf_field() ?>

    <div style="margin-bottom:18px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Full Name *</label>
        <input type="text" name="full_name" value="<?= e($currentName) ?>" placeholder="John Mwangi" required autofocus style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Phone (Login) *</label>
            <input type="tel" name="phone_number" value="<?= e($currentPhone) ?>" placeholder="0712345678" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Email (optional)</label>
            <input type="email" name="email" value="<?= e($currentEmail) ?>" placeholder="email@example.com" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <div style="margin-bottom:18px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">
            <?= $isEdit ? 'New Password (leave blank to keep current)' : 'Password *' ?>
        </label>
        <input type="password" name="password" placeholder="<?= $isEdit ? 'Leave blank to keep current' : 'Min 6 characters' ?>" <?= $isEdit ? '' : 'required' ?> style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Role *</label>
            <select name="role" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
                <?php foreach ($roles as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $currentRole === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Employment</label>
            <select name="employment_type" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
                <option value="PERMANENT" <?= $currentType === 'PERMANENT' ? 'selected' : '' ?>>Permanent</option>
                <option value="CASUAL" <?= $currentType === 'CASUAL' ? 'selected' : '' ?>>Casual (Posho)</option>
            </select>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Monthly Salary (TZS)</label>
            <input type="number" name="monthly_salary" value="<?= e($currentSalary) ?>" placeholder="0" min="0" step="1000" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Daily Rate (TZS)</label>
            <input type="number" name="daily_rate" value="<?= e($currentRate) ?>" placeholder="0" min="0" step="500" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Hire Date</label>
            <input type="date" name="hire_date" value="<?= e($currentHire) ?>" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">NSSF Number</label>
            <input type="text" name="nssf_number" value="<?= e($currentNssf) ?>" placeholder="Optional" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <div style="margin-bottom:18px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Notes</label>
        <textarea name="notes" rows="2" placeholder="Any notes…" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px;resize:vertical"><?= e($currentNotes) ?></textarea>
    </div>

    <?php if ($isEdit): ?>
    <div style="margin-bottom:22px;padding:14px;background:#0A1929;border-radius:12px;display:flex;align-items:center;gap:10px">
        <input type="checkbox" id="isActive" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:var(--cyan)">
        <label for="isActive" style="font-size:13px;cursor:pointer">Employee is active (can log in)</label>
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?= $isEdit ? 'Update Employee' : 'Save Employee' ?>
        </button>
        <a href="<?= url('/employees') ?>" class="btn" style="background:rgba(255,255,255,.05);color:var(--text);text-decoration:none;padding:15px 24px">Cancel</a>
    </div>
</form>

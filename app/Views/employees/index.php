<div class="mini-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:16px">
    <div class="mini">
        <div class="mini-label">ACTIVE EMPLOYEES</div>
        <div class="mini-value"><?= $activeCount ?></div>
    </div>
    <div class="mini">
        <div class="mini-label">VIEW</div>
        <div style="display:flex;gap:6px;margin-top:8px">
            <a href="<?= url('/employees') ?>" style="padding:6px 12px;border-radius:7px;font-size:12px;text-decoration:none;font-weight:600;<?= !$showInactive ? 'background:var(--cyan);color:#fff' : 'background:rgba(255,255,255,.05);color:var(--text)' ?>">Active</a>
            <a href="<?= url('/employees?inactive=1') ?>" style="padding:6px 12px;border-radius:7px;font-size:12px;text-decoration:none;font-weight:600;<?= $showInactive ? 'background:var(--cyan);color:#fff' : 'background:rgba(255,255,255,.05);color:var(--text)' ?>">All</a>
        </div>
    </div>
</div>

<?php if (empty($employees)): ?>
<div class="card" style="text-align:center;padding:48px 20px">
    <div style="font-size:40px;margin-bottom:12px">👥</div>
    <div style="font-size:15px;font-weight:700;margin-bottom:6px">No employees yet</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:18px">Add cashiers and managers to your team</div>
    <?php if (auth_role() === 'ADMIN'): ?>
        <a href="<?= url('/employees/create') ?>" class="btn btn-primary" style="display:inline-flex;width:auto;padding:12px 24px">Add Employee</a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
    <?php foreach ($employees as $i => $e):
        $initial = strtoupper(substr($e['full_name'], 0, 1));
        $roleColor = $e['role'] === 'ADMIN' ? 'var(--red)' : ($e['role'] === 'MANAGER' ? 'var(--orange)' : 'var(--cyan)');
        $roleBg = $e['role'] === 'ADMIN' ? 'rgba(255,23,68,.15)' : ($e['role'] === 'MANAGER' ? 'rgba(255,145,0,.15)' : 'rgba(0,180,216,.15)');
        $isActive = (int)$e['is_active'] === 1;
    ?>
        <div style="display:grid;grid-template-columns:auto 1fr auto auto;gap:12px;align-items:center;padding:14px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>;<?= !$isActive ? 'opacity:.5' : '' ?>">
            <div style="width:44px;height:44px;border-radius:11px;background:<?= $roleBg ?>;color:<?= $roleColor ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;flex-shrink:0"><?= e($initial) ?></div>
            <div style="min-width:0">
                <div style="font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= e($e['full_name']) ?>
                    <?php if ((int)$e['employee_id'] === auth_id()): ?>
                        <span style="font-size:10px;color:var(--cyan);font-weight:700"> · YOU</span>
                    <?php endif; ?>
                    <?php if (!$isActive): ?>
                        <span style="font-size:10px;color:var(--red);font-weight:700"> · INACTIVE</span>
                    <?php endif; ?>
                </div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;gap:8px;flex-wrap:wrap">
                    <span style="color:<?= $roleColor ?>;font-weight:700"><?= e($roles[$e['role']] ?? $e['role']) ?></span>
                    <span>·</span>
                    <span><?= e($e['phone_number']) ?></span>
                    <span>·</span>
                    <span><?= e($e['employment_type']) ?></span>
                    <?php if ((int)$e['monthly_salary'] > 0): ?>
                        <span>·</span>
                        <span><?= money($e['monthly_salary']) ?>/mo</span>
                    <?php elseif ((int)$e['daily_rate'] > 0): ?>
                        <span>·</span>
                        <span><?= money($e['daily_rate']) ?>/day</span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:flex;gap:4px">
                <?php if (auth_role() === 'ADMIN' && (int)$e['employee_id'] !== auth_id()): ?>
                    <form method="post" action="<?= url('/employees/' . $e['employee_id'] . '/toggle') ?>" style="margin:0" onsubmit="return confirm('<?= $isActive ? 'Deactivate' : 'Activate' ?> this employee?')">
                        <?= csrf_field() ?>
                        <button type="submit" title="<?= $isActive ? 'Deactivate' : 'Activate' ?>" style="width:32px;height:32px;border-radius:8px;background:<?= $isActive ? 'rgba(255,145,0,.15)' : 'rgba(0,200,83,.15)' ?>;color:<?= $isActive ? 'var(--orange)' : 'var(--green)' ?>;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $isActive ? '<path d="M18.36 6.64a9 9 0 1 1-12.73 0M12 2v10"/>' : '<path d="M5 12h14M12 5l7 7-7 7"/>' ?></svg>
                        </button>
                    </form>
                <?php endif; ?>
                <?php if (auth_role() === 'ADMIN'): ?>
                    <a href="<?= url('/employees/' . $e['employee_id'] . '/edit') ?>" title="Edit" style="width:32px;height:32px;border-radius:8px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (auth_role() === 'ADMIN' && !empty($employees)): ?>
<div class="card" style="margin-top:20px;background:rgba(0,180,216,.05);border:1px solid rgba(0,180,216,.2)">
    <div style="font-size:12px;color:var(--muted);line-height:1.6">
        <strong style="color:var(--cyan)">💡 Tip:</strong> Cashiers can log in with their phone + password. They will only see POS, Sales History, and Stock (read-only). Managers can also access Expenses and Reports. Only Admins can manage Staff, Branches, and Settings.
    </div>
</div>
<?php endif; ?>

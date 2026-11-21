<form method="get" action="<?= url('/salaries') ?>" class="card" style="margin-bottom:16px;padding:14px">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Month</label>
            <select name="month" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
                <?php foreach ($months as $m): ?>
                    <option value="<?= e($m) ?>" <?= $month === $m ? 'selected' : '' ?>><?= e($m) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Year</label>
            <input type="number" name="year" value="<?= $year ?>" min="2020" max="2100" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Apply</button>
    </div>
</form>

<div class="mini-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr));margin-bottom:20px">
    <div class="mini"><div class="mini-label">TOTAL PAID</div><div class="mini-value" style="font-size:16px"><?= money($totals['total']) ?></div></div>
    <div class="mini"><div class="mini-label">SALARIES</div><div class="mini-value green" style="font-size:16px"><?= money($totals['salary']) ?></div></div>
    <div class="mini"><div class="mini-label">POSHO</div><div class="mini-value orange" style="font-size:16px"><?= money($totals['posho']) ?></div></div>
    <div class="mini"><div class="mini-label">NSSF</div><div class="mini-value" style="font-size:16px;color:var(--cyan)"><?= money($totals['nssf']) ?></div></div>
</div>

<div class="section-title"><?= e($month) ?> <?= $year ?> Payments</div>

<?php if (empty($payments)): ?>
<div class="card" style="text-align:center;padding:48px 20px">
    <div style="font-size:40px;margin-bottom:12px">💰</div>
    <div style="font-size:15px;font-weight:700;margin-bottom:6px">No payments yet</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:18px">Record a salary or posho payment</div>
    <a href="<?= url('/salaries/create') ?>" class="btn btn-primary" style="display:inline-flex;width:auto;padding:12px 24px">Record Payment</a>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
    <?php foreach ($payments as $i => $p): ?>
        <div style="display:grid;grid-template-columns:auto 1fr auto auto;gap:12px;align-items:center;padding:14px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(0,200,83,.15);color:var(--green);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div style="min-width:0">
                <div style="font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($p['employee_name']) ?></div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;gap:8px;flex-wrap:wrap">
                    <span style="color:var(--cyan);font-weight:700"><?= e($types[$p['payment_type']] ?? $p['payment_type']) ?></span>
                    <span>·</span>
                    <span><?= e(date('d M Y', strtotime($p['payment_date']))) ?></span>
                    <span>·</span>
                    <span><?= e($p['payment_method']) ?></span>
                </div>
            </div>
            <div style="font-size:15px;font-weight:800;color:var(--green);white-space:nowrap"><?= money($p['amount_paid']) ?></div>
            <div style="display:flex;gap:6px">
                <a href="<?= url('/salaries/' . $p['payment_id']) ?>" title="Payslip" style="width:32px;height:32px;border-radius:8px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <?php if (auth_role() === 'ADMIN'): ?>
                <form method="post" action="<?= url('/salaries/' . $p['payment_id'] . '/delete') ?>" onsubmit="return confirm('Delete this payment?')" style="margin:0">
                    <?= csrf_field() ?>
                    <button type="submit" style="width:32px;height:32px;border-radius:8px;background:rgba(255,23,68,.15);color:var(--red);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

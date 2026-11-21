<form method="get" action="<?= url('/reports/expenses') ?>" class="card" style="margin-bottom:16px;padding:14px">
    <div class="filter-grid" style="display:grid;grid-template-columns:1fr 1fr auto auto;gap:10px;align-items:end">
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">From</label>
            <input type="date" name="from" value="<?= e($from) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">To</label>
            <input type="date" name="to" value="<?= e($to) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Apply</button>
        <a href="<?= url('/reports/export?type=expenses&from='.urlencode($from).'&to='.urlencode($to)) ?>" class="btn" style="padding:10px 18px;font-size:13px;background:rgba(0,180,216,.15);color:var(--cyan);text-decoration:none">⬇ CSV</a>
    </div>
</form>

<div class="hero" style="background:linear-gradient(135deg,#FF9100,#E65100);margin-bottom:16px">
    <div class="hero-label">Total Expenses</div>
    <div class="hero-value"><?= money($total) ?></div>
</div>

<!-- EFD Breakdown -->
<div class="mini-grid" style="grid-template-columns:1fr 1fr;margin-bottom:20px">
    <div class="mini">
        <div class="mini-label" style="color:var(--green)">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:3px"><path d="M20 6L9 17l-5-5"/></svg>
            WITH EFD
        </div>
        <div class="mini-value green" style="font-size:16px"><?= money(\App\Models\Expense::sumWithEfd(auth_branch(), $from . ' 00:00:00', $to . ' 23:59:59')) ?></div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px"><?= \App\Models\Expense::countWithEfd(auth_branch(), $from . ' 00:00:00', $to . ' 23:59:59') ?> entr<?= \App\Models\Expense::countWithEfd(auth_branch(), $from . ' 00:00:00', $to . ' 23:59:59') === 1 ? 'y' : 'ies' ?></div>
    </div>
    <div class="mini">
        <div class="mini-label" style="color:var(--orange)">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:3px"><path d="M18 6L6 18M6 6l12 12"/></svg>
            WITHOUT EFD
        </div>
        <div class="mini-value orange" style="font-size:16px"><?= money(\App\Models\Expense::sumWithoutEfd(auth_branch(), $from . ' 00:00:00', $to . ' 23:59:59')) ?></div>
        <div style="font-size:11px;color:var(--muted);margin-top:4px"><?= \App\Models\Expense::countWithoutEfd(auth_branch(), $from . ' 00:00:00', $to . ' 23:59:59') ?> entr<?= \App\Models\Expense::countWithoutEfd(auth_branch(), $from . ' 00:00:00', $to . ' 23:59:59') === 1 ? 'y' : 'ies' ?></div>
    </div>
</div>

<?php if (!empty($groups)): foreach ($groups as $gName => $gData): ?>
    <div class="card" style="margin-bottom:12px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div style="font-size:15px;font-weight:700"><?= e(ucfirst(strtolower($gName))) ?></div>
            <div style="font-size:16px;font-weight:800;color:var(--orange)"><?= money($gData['total']) ?></div>
        </div>
        <div style="border-top:1px solid var(--divider);padding-top:8px">
            <?php foreach ($gData['items'] as $item): ?>
                <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12px">
                    <span style="color:var(--muted)"><?= e($item['label']) ?></span>
                    <span><?= money($item['total']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; else: ?>
    <div class="card" style="text-align:center;padding:40px;color:var(--muted)">No expenses in this period</div>
<?php endif; ?>

<!-- Filter -->
<form method="get" action="<?= url('/expenses/summary') ?>" class="card" style="margin-bottom:16px">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">From</label>
            <input type="date" name="from" value="<?= e($from) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">To</label>
            <input type="date" name="to" value="<?= e($to) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Apply</button>
    </div>
</form>

<h2 style="font-size:18px;font-weight:800;margin:0 0 16px">Summary: <?= e(date('d M', strtotime($from))) ?> – <?= e(date('d M Y', strtotime($to))) ?></h2>

<!-- EFD totals -->
<div class="mini-grid" style="grid-template-columns:1fr 1fr;margin-bottom:20px">
    <div class="mini">
        <div class="mini-label" style="color:var(--green)">WITH EFD</div>
        <div class="mini-value green" style="font-size:18px"><?= money($efdWith) ?></div>
    </div>
    <div class="mini">
        <div class="mini-label" style="color:var(--orange)">WITHOUT EFD</div>
        <div class="mini-value orange" style="font-size:18px"><?= money($efdWithout) ?></div>
    </div>
</div>

<?php if (empty($groups)): ?>
<div class="card" style="text-align:center;padding:48px 20px">
    <div style="font-size:40px;margin-bottom:12px">📊</div>
    <div style="font-size:15px;font-weight:700">No expenses for this period</div>
</div>
<?php else: ?>
<?php foreach ($groups as $groupName => $data): ?>
<div class="card" style="margin-bottom:16px">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
        <div style="width:36px;height:36px;border-radius:9px;background:rgba(255,145,0,.15);color:var(--orange);display:flex;align-items:center;justify-content:center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div style="flex:1">
            <div style="font-size:15px;font-weight:700"><?= e(ucfirst(strtolower($groupName))) ?></div>
            <div style="font-size:11px;color:var(--muted)"><?= $data['count'] ?> entr<?= $data['count'] === 1 ? 'y' : 'ies' ?></div>
        </div>
        <div style="font-size:18px;font-weight:800;color:var(--orange)"><?= money($data['total']) ?></div>
    </div>

    <div style="border-top:1px solid var(--divider);padding-top:12px">
        <?php foreach ($data['items'] as $item): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;font-size:13px">
                <span style="color:var(--muted)"><?= e($item['label']) ?></span>
                <span style="font-weight:600"><?= money($item['total']) ?> <span style="color:var(--faint);font-size:11px">(<?= $item['cnt'] ?>)</span></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<div style="margin-top:24px">
    <a href="<?= url('/expenses') ?>" style="color:var(--cyan);text-decoration:none;font-size:13px;font-weight:600">← Back to expenses</a>
</div>

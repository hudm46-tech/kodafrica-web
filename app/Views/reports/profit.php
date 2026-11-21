<form method="get" action="<?= url('/reports/profit') ?>" class="card" style="margin-bottom:16px;padding:14px">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end">
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">From</label>
            <input type="date" name="from" value="<?= e($from) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">To</label>
            <input type="date" name="to" value="<?= e($to) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Apply</button>
    </div>
</form>

<div class="mini-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:20px">
    <div class="mini"><div class="mini-label">REVENUE</div><div class="mini-value" style="font-size:18px"><?= money($revenue) ?></div></div>
    <div class="mini"><div class="mini-label">GROSS PROFIT</div><div class="mini-value green" style="font-size:18px"><?= money($gross) ?></div></div>
    <div class="mini"><div class="mini-label">EXPENSES</div><div class="mini-value orange" style="font-size:18px"><?= money($expenses) ?></div></div>
    <div class="mini"><div class="mini-label">NET PROFIT</div><div class="mini-value <?= $net >= 0 ? 'green' : 'red' ?>" style="font-size:18px"><?= money($net) ?></div></div>
</div>

<div class="section-title">Daily Trend</div>
<div class="card" style="padding:0;overflow:hidden">
    <?php if (empty($daily)): ?>
        <div style="padding:30px;text-align:center;color:var(--muted);font-size:13px">No data</div>
    <?php else: foreach ($daily as $i => $d): ?>
        <div style="display:grid;grid-template-columns:1fr auto auto;gap:12px;padding:12px 16px;align-items:center;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <span style="font-size:13px"><?= e(date('d M Y', strtotime($d['day']))) ?></span>
            <span style="font-size:12px;color:var(--muted)">Rev: <?= money($d['revenue']) ?></span>
            <span style="font-weight:700;color:var(--green);font-size:13px">+<?= money($d['gross_profit']) ?></span>
        </div>
    <?php endforeach; endif; ?>
</div>

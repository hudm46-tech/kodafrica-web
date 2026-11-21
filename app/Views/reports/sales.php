<form method="get" action="<?= url('/reports/sales') ?>" class="card" style="margin-bottom:16px;padding:14px">
    <div style="display:grid;grid-template-columns:1fr 1fr auto auto;gap:10px;align-items:end">
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">From</label>
            <input type="date" name="from" value="<?= e($from) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">To</label>
            <input type="date" name="to" value="<?= e($to) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Apply</button>
        <a href="<?= url('/reports/export?type=sales&from='.urlencode($from).'&to='.urlencode($to)) ?>" class="btn" style="padding:10px 18px;font-size:13px;background:rgba(0,180,216,.15);color:var(--cyan);text-decoration:none">⬇ CSV</a>
    </div>
</form>

<div class="hero" style="margin-bottom:16px">
    <div class="hero-label">Total Sales (<?= e(date('d M', strtotime($from))) ?> – <?= e(date('d M Y', strtotime($to))) ?>)</div>
    <div class="hero-value"><?= money($totalSales) ?></div>
    <div class="hero-meta"><?= $totalItems ?> items sold</div>
</div>

<div class="section-title">By Payment Method</div>
<div class="mini-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr));margin-bottom:24px">
    <?php foreach ($paymentMethods as $key => $label):
        $data = $byMethod[$key] ?? null;
        if (!$data) continue; ?>
        <div class="mini">
            <div class="mini-label"><?= e($label) ?></div>
            <div class="mini-value" style="font-size:14px;color:var(--cyan)"><?= money($data['total']) ?></div>
            <div style="font-size:10px;color:var(--muted);margin-top:4px"><?= $data['count'] ?> sale<?= $data['count'] === 1 ? '' : 's' ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="section-title">By Cashier</div>
<div class="card" style="padding:0;overflow:hidden;margin-bottom:24px">
    <?php if (empty($byCashier)): ?>
        <div style="padding:30px;text-align:center;color:var(--muted);font-size:13px">No sales data</div>
    <?php else: foreach ($byCashier as $i => $c): ?>
        <div style="display:flex;justify-content:space-between;padding:12px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <span style="font-size:13px;font-weight:600"><?= e($c['full_name']) ?></span>
            <span style="color:var(--green);font-weight:700"><?= money($c['total']) ?> <span style="font-size:11px;color:var(--muted);font-weight:400">(<?= $c['items'] ?>)</span></span>
        </div>
    <?php endforeach; endif; ?>
</div>

<div class="section-title">Daily Breakdown</div>
<div class="card" style="padding:0;overflow:hidden">
    <?php if (empty($daily)): ?>
        <div style="padding:30px;text-align:center;color:var(--muted);font-size:13px">No sales in this period</div>
    <?php else: foreach ($daily as $i => $d): ?>
        <div style="display:grid;grid-template-columns:1fr auto auto;gap:12px;align-items:center;padding:12px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <span style="font-size:13px;font-weight:600"><?= e(date('l, d M Y', strtotime($d['day']))) ?></span>
            <span style="font-size:11px;color:var(--muted)"><?= $d['items'] ?> item<?= $d['items'] == 1 ? '' : 's' ?></span>
            <span style="font-weight:700;color:var(--green)"><?= money($d['total']) ?></span>
        </div>
    <?php endforeach; endif; ?>
</div>

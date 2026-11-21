<form method="get" action="<?= url('/reports/top-products') ?>" class="card" style="margin-bottom:16px;padding:14px">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end">
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">From</label>
            <input type="date" name="from" value="<?= e($from) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">To</label>
            <input type="date" name="to" value="<?= e($to) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Apply</button>
    </div>
</form>

<div class="card" style="padding:0;overflow:hidden">
    <?php if (empty($products)): ?>
        <div style="padding:40px;text-align:center;color:var(--muted)">No sales data</div>
    <?php else: foreach ($products as $i => $p): ?>
        <div style="display:grid;grid-template-columns:auto 1fr auto auto;gap:12px;align-items:center;padding:14px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <div style="width:32px;height:32px;border-radius:8px;background:<?= $i < 3 ? 'rgba(255,193,7,.15)' : 'rgba(255,255,255,.05)' ?>;color:<?= $i < 3 ? 'var(--amber)' : 'var(--muted)' ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px"><?= $i + 1 ?></div>
            <div style="min-width:0">
                <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($p['product_name']) ?></div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px"><?= (int)$p['qty'] ?> sold · <?= (int)$p['times_sold'] ?> txn</div>
            </div>
            <div style="font-size:12px;color:var(--muted)"><?= money($p['revenue']) ?></div>
            <div style="font-weight:700;color:var(--green);font-size:13px;white-space:nowrap">+<?= money($p['profit']) ?></div>
        </div>
    <?php endforeach; endif; ?>
</div>

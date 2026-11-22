<?php
$lbl = 'display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px';
$inp = 'width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px';
?>
<form method="get" action="<?= url('/purchases') ?>" class="card" style="margin-bottom:16px">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
        <div><label style="<?= $lbl ?>">From</label><input type="date" name="from" value="<?= e($from) ?>" style="<?= $inp ?>"></div>
        <div><label style="<?= $lbl ?>">To</label><input type="date" name="to" value="<?= e($to) ?>" style="<?= $inp ?>"></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end">
        <div><label style="<?= $lbl ?>">Search</label><input type="text" name="q" value="<?= e($q) ?>" placeholder="Product, supplier or receipt" style="<?= $inp ?>"></div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Search</button>
    </div>
</form>

<div class="hero" style="margin-bottom:16px">
    <div class="hero-label">Total Purchases (<?= e(date('d M', strtotime($from))) ?> – <?= e(date('d M Y', strtotime($to))) ?>)</div>
    <div class="hero-value"><?= money($total) ?></div>
    <div class="hero-meta"><?= (int)$count ?> purchase<?= $count === 1 ? '' : 's' ?></div>
</div>

<?php if (empty($purchases)): ?>
    <div class="card" style="text-align:center;padding:36px 16px;color:var(--muted)">
        No purchases found for this period.<br>
        <span style="font-size:12px">To record one, open <a href="<?= url('/products') ?>" style="color:var(--cyan)">Stock</a> and tap + on a product.</span>
    </div>
<?php else: ?>
    <?php foreach ($purchases as $p): $void = $p['status'] === 'VOIDED'; ?>
    <div class="card" style="margin-bottom:10px;<?= $void ? 'opacity:.55' : '' ?>">
        <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start">
            <div style="min-width:0">
                <div style="font-weight:800;font-size:14px;<?= $void ? 'text-decoration:line-through' : '' ?>"><?= e($p['product_name']) ?></div>
                <div style="font-size:12px;color:var(--muted);margin-top:3px">
                    <?= e(date('d M Y', strtotime($p['purchase_date']))) ?> · <?= e($p['supplier_name']) ?>
                    <?php if ($p['receipt_number']): ?> · #<?= e($p['receipt_number']) ?><?php endif; ?>
                </div>
                <div style="font-size:12px;margin-top:6px">
                    <?= (int)$p['quantity'] ?> <?= e($p['unit_of_measure']) ?> × <?= money($p['purchase_price']) ?>
                    <span style="color:var(--muted)">· sells at <?= money($p['selling_price']) ?></span>
                </div>
                <?php if ($p['notes']): ?><div style="font-size:11px;color:var(--muted);margin-top:4px"><?= e($p['notes']) ?></div><?php endif; ?>
                <?php if ($void): ?><div style="font-size:11px;color:var(--red);margin-top:4px">VOIDED: <?= e($p['void_reason']) ?></div><?php endif; ?>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-weight:800;color:var(--orange)"><?= money($p['total_cost']) ?></div>
                <?php if ($canManage && !$void): ?>
                <form method="post" action="<?= url('/purchases/' . (int)$p['purchase_id'] . '/void') ?>" style="margin:8px 0 0"
                      onsubmit="var r = prompt('Reason for voiding this purchase?'); if (!r || r.trim().length < 3) return false; this.reason.value = r; return confirm('Void this purchase? Stock will be reduced.');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="reason" value="">
                    <button type="submit" style="background:rgba(255,23,68,.15);color:var(--red);border:none;border-radius:8px;padding:6px 10px;font-size:11px;font-weight:700;cursor:pointer;font-family:inherit">Void</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

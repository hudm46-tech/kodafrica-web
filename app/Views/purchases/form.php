<?php
$lbl = 'display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px';
$inp = 'width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px';
$val = fn(string $k, $fallback = '') => old($k, $fallback);
?>
<form method="post" action="<?= url('/products/' . (int)$product['product_id'] . '/purchase') ?>" class="card" id="purchaseForm">
    <?= csrf_field() ?>

    <div style="background:rgba(0,180,216,.08);border:1px solid rgba(0,180,216,.2);border-radius:12px;padding:12px 14px;margin-bottom:18px">
        <div style="font-size:15px;font-weight:800"><?= e($product['name']) ?></div>
        <div style="font-size:12px;color:var(--muted);margin-top:3px">
            Current stock: <b id="curStock"><?= (int)$product['quantity'] ?></b> <?= e($product['unit_of_measure']) ?>
        </div>
    </div>

    <div style="margin-bottom:18px">
        <label style="<?= $lbl ?>">Quantity bought *</label>
        <input type="number" name="quantity" id="qty" min="1" step="1" inputmode="numeric" value="<?= e($val('quantity', '1')) ?>" required autofocus style="<?= $inp ?>;font-size:20px;font-weight:800">
        <div style="font-size:12px;color:var(--muted);margin-top:6px">New stock: <b id="newStock" style="color:var(--cyan)"></b></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:6px">
        <div>
            <label style="<?= $lbl ?>">Purchase price *</label>
            <input type="number" name="purchase_price" id="buy" min="0" step="0.01" inputmode="decimal" value="<?= e($val('purchase_price', (float)$product['purchase_price'])) ?>" required style="<?= $inp ?>">
        </div>
        <div>
            <label style="<?= $lbl ?>">Selling price *</label>
            <input type="number" name="selling_price" id="sell" min="0" step="0.01" inputmode="decimal" value="<?= e($val('selling_price', (float)$product['selling_price'])) ?>" required style="<?= $inp ?>">
        </div>
    </div>
    <div style="font-size:12px;margin-bottom:18px;color:var(--muted)">
        Total cost: <b id="totalCost" style="color:var(--text)"></b>
        <span id="marginNote" style="margin-left:8px"></span>
    </div>

    <div style="margin-bottom:18px">
        <label style="<?= $lbl ?>">Supplier *</label>
        <input type="text" name="supplier_name" list="supplierList" value="<?= e($val('supplier_name', $defaultSupplier)) ?>" placeholder="Supplier name" required maxlength="150" style="<?= $inp ?>">
        <datalist id="supplierList">
            <?php foreach ($suppliers as $s): ?><option value="<?= e($s) ?>"><?php endforeach; ?>
        </datalist>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="<?= $lbl ?>">Date *</label>
            <input type="date" name="purchase_date" value="<?= e($val('purchase_date', date('Y-m-d'))) ?>" max="<?= date('Y-m-d') ?>" required style="<?= $inp ?>">
        </div>
        <div>
            <label style="<?= $lbl ?>">Receipt no. (optional)</label>
            <input type="text" name="receipt_number" value="<?= e($val('receipt_number')) ?>" maxlength="60" style="<?= $inp ?>">
        </div>
    </div>

    <div style="margin-bottom:22px">
        <label style="<?= $lbl ?>">Notes (optional)</label>
        <input type="text" name="notes" value="<?= e($val('notes')) ?>" maxlength="255" style="<?= $inp ?>">
    </div>

    <div style="display:flex;gap:10px">
        <a href="<?= url('/products') ?>" style="flex:1;text-align:center;padding:13px;border-radius:11px;background:rgba(255,255,255,.05);color:var(--text);text-decoration:none;font-weight:700">Cancel</a>
        <button type="submit" id="saveBtn" style="flex:2;padding:13px;border-radius:11px;background:linear-gradient(135deg,var(--green),#00897B);color:#fff;border:none;font-weight:800;cursor:pointer;font-family:inherit;font-size:14px">Save purchase</button>
    </div>
</form>

<script>
(function () {
    var cur = <?= (int)$product['quantity'] ?>;
    var q = document.getElementById('qty'), b = document.getElementById('buy'), s = document.getElementById('sell');
    function fmt(n) { return 'TZS ' + Math.round(n).toLocaleString('en-US'); }
    function upd() {
        var qty = parseInt(q.value, 10) || 0, buy = parseFloat(b.value) || 0, sell = parseFloat(s.value) || 0;
        document.getElementById('newStock').textContent = (cur + qty) + ' <?= e($product['unit_of_measure']) ?>';
        document.getElementById('totalCost').textContent = fmt(qty * buy);
        var m = document.getElementById('marginNote');
        if (buy > 0 && sell > 0 && sell < buy) { m.textContent = 'Selling price is below purchase price'; m.style.color = 'var(--red)'; }
        else if (buy > 0 && sell > 0) { m.textContent = 'Margin +' + Math.round((sell - buy) / buy * 100) + '%'; m.style.color = 'var(--green)'; }
        else { m.textContent = ''; }
    }
    [q, b, s].forEach(function (el) { el.addEventListener('input', upd); });
    document.getElementById('purchaseForm').addEventListener('submit', function () {
        var btn = document.getElementById('saveBtn'); btn.disabled = true; btn.textContent = 'Saving...';
    });
    upd();
})();
</script>

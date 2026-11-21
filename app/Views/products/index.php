<!-- Stats -->
<div class="mini-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:16px">
    <div class="mini">
        <div class="mini-label">TOTAL PRODUCTS</div>
        <div class="mini-value"><?= $stats['total'] ?></div>
    </div>
    <div class="mini">
        <div class="mini-label">LOW STOCK</div>
        <div class="mini-value <?= $stats['low'] > 0 ? 'orange' : 'green' ?>"><?= $stats['low'] ?></div>
    </div>
    <div class="mini">
        <div class="mini-label">STOCK VALUE</div>
        <div class="mini-value green" style="font-size:15px"><?= money($stats['value']) ?></div>
    </div>
</div>

<!-- Filter bar -->
<form method="get" action="<?= url('/products') ?>" class="card" style="margin-bottom:16px;padding:14px">
    <div style="display:grid;grid-template-columns:1fr 1fr auto auto;gap:10px;align-items:center">
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search product…" style="padding:10px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;width:100%">
        <select name="category" style="padding:10px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px;width:100%">
            <option value="">All categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= e($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
        <label style="display:flex;align-items:center;gap:6px;padding:10px 14px;background:<?= $lowOnly ? 'rgba(255,145,0,.15)' : '#0A1929' ?>;border:1px solid <?= $lowOnly ? 'var(--orange)' : 'rgba(255,255,255,.1)' ?>;border-radius:10px;font-size:13px;cursor:pointer;color:<?= $lowOnly ? 'var(--orange)' : '#fff' ?>">
            <input type="checkbox" name="low" value="1" <?= $lowOnly ? 'checked' : '' ?> onchange="this.form.submit()" style="margin:0">
            Low stock only
        </label>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Filter</button>
    </div>
</form>

<?php if (empty($products)): ?>
<div class="card" style="text-align:center;padding:48px 20px">
    <div style="font-size:40px;margin-bottom:12px">📦</div>
    <div style="font-size:15px;font-weight:700;margin-bottom:6px">No products found</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:18px">
        <?= ($search || $category || $lowOnly) ? 'Try adjusting your filters' : 'Add your first product' ?>
    </div>
    <?php if (!$search && !$category && !$lowOnly): ?>
        <a href="<?= url('/products/create') ?>" class="btn btn-primary" style="display:inline-flex;width:auto;padding:12px 24px">Add Product</a>
    <?php endif; ?>
</div>
<?php else: ?>

<div class="card" style="padding:0;overflow:hidden">
    <?php foreach ($products as $i => $p): ?>
        <?php
            $isLow = (int)$p['quantity'] <= (int)$p['minimum_stock'];
            $margin = $p['selling_price'] > 0
                ? (($p['selling_price'] - $p['purchase_price']) / $p['selling_price']) * 100
                : 0;
        ?>
        <div style="display:grid;grid-template-columns:auto 1fr auto auto;gap:12px;align-items:center;padding:14px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <div style="width:44px;height:44px;border-radius:11px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;flex-shrink:0">
                <?= e(strtoupper(substr($p['name'], 0, 1))) ?>
            </div>
            <div style="min-width:0">
                <div style="font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($p['name']) ?></div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;gap:8px;flex-wrap:wrap">
                    <span><?= money($p['selling_price']) ?></span>
                    <span style="color:<?= $isLow ? 'var(--orange)' : 'var(--muted)' ?>;font-weight:600">· <?= $p['quantity'] ?> <?= e($p['unit_of_measure']) ?></span>
                    <?php if ($p['category']): ?><span>· <?= e($p['category']) ?></span><?php endif; ?>
                    <?php if ($margin > 0): ?><span style="color:var(--green)">· +<?= number_format($margin, 0) ?>%</span><?php endif; ?>
                </div>
            </div>
            <div style="display:flex;gap:6px;align-items:center">
                <button type="button"
                        data-pid="<?= (int)$p['product_id'] ?>"
                        data-name="<?= e($p['name']) ?>"
                        data-stock="<?= (int)$p['quantity'] ?>"
                        data-unit="<?= e($p['unit_of_measure']) ?>"
                        data-mode="remove"
                        onclick="openStockModal(this)"
                        title="Remove stock"
                        style="width:34px;height:34px;border-radius:8px;background:rgba(255,23,68,.15);color:var(--red);border:none;cursor:pointer;font-weight:800;font-size:18px;line-height:1">−</button>
                <button type="button"
                        data-pid="<?= (int)$p['product_id'] ?>"
                        data-name="<?= e($p['name']) ?>"
                        data-stock="<?= (int)$p['quantity'] ?>"
                        data-unit="<?= e($p['unit_of_measure']) ?>"
                        data-mode="add"
                        onclick="openStockModal(this)"
                        title="Add stock"
                        style="width:34px;height:34px;border-radius:8px;background:rgba(0,200,83,.15);color:var(--green);border:none;cursor:pointer;font-weight:800;font-size:18px;line-height:1">+</button>
            </div>
            <div style="display:flex;gap:6px">
                <a href="<?= url('/products/' . $p['product_id'] . '/edit') ?>" style="width:32px;height:32px;border-radius:8px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <?php if (auth_role() === 'ADMIN'): ?>
                <form method="post" action="<?= url('/products/' . $p['product_id'] . '/delete') ?>" onsubmit="return confirm('Delete <?= e($p['name']) ?>?')" style="margin:0">
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

<div style="text-align:center;margin-top:16px;font-size:12px;color:var(--muted)">
    Showing <?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?>
</div>
<?php endif; ?>

<!-- ============ STOCK ADJUSTMENT MODAL ============ -->
<div id="stockModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:999;align-items:center;justify-content:center;padding:16px">
    <div style="background:var(--card);border-radius:20px;width:100%;max-width:360px;padding:22px;box-shadow:0 20px 60px rgba(0,0,0,.5)">

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
            <div id="stockModalIcon" style="width:44px;height:44px;border-radius:11px;background:rgba(0,200,83,.15);color:var(--green);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            </div>
            <div style="flex:1;min-width:0">
                <div id="stockModalTitle" style="font-size:15px;font-weight:800">Add Stock</div>
                <div id="stockModalName" style="font-size:12px;color:var(--muted);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Product</div>
            </div>
            <button type="button" onclick="closeStockModal()" style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.05);border:none;color:var(--muted);cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;line-height:1">×</button>
        </div>

        <div style="background:#0A1929;border-radius:12px;padding:12px;margin-bottom:16px;text-align:center">
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700">Current Stock</div>
            <div style="font-size:15px;font-weight:700;margin-top:3px" id="stockModalCurrent">0 PCS</div>
        </div>

        <div style="text-align:center;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Quantity</div>

        <div style="display:grid;grid-template-columns:52px 1fr 52px;gap:10px;align-items:center;margin-bottom:12px">
            <button type="button" class="stock-step-btn" onclick="stepStock(-1)"
                    style="width:52px;height:48px;border-radius:11px;border:none;cursor:pointer;font-size:24px;font-weight:800;background:rgba(255,23,68,.15);color:var(--red)">−</button>
            <input type="number" id="stockModalQty" value="1" min="1"
                   oninput="updateStockPreview()"
                   style="width:100%;height:48px;text-align:center;background:#0A1929;border:2px solid rgba(255,255,255,.1);border-radius:11px;color:#fff;font-size:24px;font-weight:800;font-family:inherit;-moz-appearance:textfield">
            <button type="button" class="stock-step-btn" onclick="stepStock(1)"
                    style="width:52px;height:48px;border-radius:11px;border:none;cursor:pointer;font-size:24px;font-weight:800;background:rgba(0,200,83,.15);color:var(--green)">+</button>
        </div>

        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:16px">
            <button type="button" onclick="setStock(1)"  class="stock-quick">1</button>
            <button type="button" onclick="setStock(5)"  class="stock-quick">5</button>
            <button type="button" onclick="setStock(10)" class="stock-quick">10</button>
            <button type="button" onclick="setStock(50)" class="stock-quick">50</button>
        </div>

        <div style="background:rgba(0,180,216,.08);border:1px solid rgba(0,180,216,.2);border-radius:11px;padding:12px;margin-bottom:18px;text-align:center">
            <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700">New Stock</div>
            <div id="stockModalPreview" style="font-size:16px;font-weight:800;color:var(--cyan);margin-top:3px">1 PCS</div>
        </div>

        <form method="post" id="stockModalForm" style="margin:0">
            <?= csrf_field() ?>
            <input type="hidden" name="mode" id="stockModalMode" value="add">
            <input type="hidden" name="qty"  id="stockModalQtySubmit" value="1">
            <div style="display:flex;gap:10px">
                <button type="button" onclick="closeStockModal()" style="flex:1;padding:13px;border-radius:11px;background:rgba(255,255,255,.05);color:var(--text);border:none;font-weight:700;cursor:pointer;font-family:inherit;font-size:13px">Cancel</button>
                <button type="submit" id="stockModalSubmit" style="flex:2;padding:13px;border-radius:11px;background:linear-gradient(135deg,var(--green),#00897B);color:#fff;border:none;font-weight:800;cursor:pointer;font-family:inherit;font-size:13px;box-shadow:0 6px 16px -6px rgba(0,200,83,.5)">Confirm</button>
            </div>
        </form>
    </div>
</div>

<style>
.stock-quick {
    padding: 9px;
    border-radius: 8px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    color: var(--text);
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;
    font-family: inherit;
    transition: all .12s;
}
.stock-quick:hover { background: rgba(0,180,216,.15); border-color: rgba(0,180,216,.3); }
.stock-quick:active { transform: scale(.95); }

#stockModalQty::-webkit-outer-spin-button,
#stockModalQty::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
#stockModalQty:focus { outline: none; border-color: var(--cyan); }
.stock-step-btn:active { transform: scale(.95); }
.stock-step-btn:disabled { opacity: .35; cursor: not-allowed; }

@media (max-width: 480px) {
    #stockModal > div { padding: 18px !important; }
    #stockModalQty { font-size: 22px !important; }
}
</style>

<script>
let stockState = { pid: 0, name: '', stock: 0, unit: 'PCS', mode: 'add', qty: 1 };

function openStockModal(btn) {
    stockState.pid    = parseInt(btn.dataset.pid, 10);
    stockState.name   = btn.dataset.name;
    stockState.stock  = parseInt(btn.dataset.stock, 10);
    stockState.unit   = btn.dataset.unit || 'PCS';
    stockState.mode   = btn.dataset.mode;
    stockState.qty    = 1;

    document.getElementById('stockModalName').textContent    = stockState.name;
    document.getElementById('stockModalCurrent').textContent = stockState.stock + ' ' + stockState.unit;
    document.getElementById('stockModalMode').value          = stockState.mode;

    const isAdd = stockState.mode === 'add';
    document.getElementById('stockModalTitle').textContent = isAdd ? 'Add Stock' : 'Remove Stock';
    document.getElementById('stockModalIcon').style.background = isAdd ? 'rgba(0,200,83,.15)' : 'rgba(255,23,68,.15)';
    document.getElementById('stockModalIcon').style.color      = isAdd ? 'var(--green)'        : 'var(--red)';
    document.getElementById('stockModalIcon').innerHTML        = isAdd
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>'
        : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>';

    const submitBtn = document.getElementById('stockModalSubmit');
    submitBtn.style.background = isAdd ? 'linear-gradient(135deg,#00C853,#00897B)' : 'linear-gradient(135deg,#FF1744,#B71C1C)';
    submitBtn.style.boxShadow  = isAdd ? '0 6px 16px -6px rgba(0,200,83,.5)'       : '0 6px 16px -6px rgba(255,23,68,.5)';

    document.getElementById('stockModalForm').action = '<?= url('/products/') ?>' + stockState.pid + '/adjust';

    setStock(1);
    document.getElementById('stockModal').style.display = 'flex';
    setTimeout(() => document.getElementById('stockModalQty').select(), 80);
}

function closeStockModal() {
    document.getElementById('stockModal').style.display = 'none';
}

function setStock(n) {
    let v = parseInt(n, 10);
    if (isNaN(v) || v < 1) v = 1;

    // If removing, cap at current stock
    if (stockState.mode === 'remove' && v > stockState.stock) v = stockState.stock;
    if (v < 1) v = 1;

    stockState.qty = v;
    document.getElementById('stockModalQty').value = v;
    updateStockPreview();
}

function stepStock(delta) {
    setStock(stockState.qty + delta);
}

function updateStockPreview() {
    const input = document.getElementById('stockModalQty');
    let v = parseInt(input.value, 10);
    if (isNaN(v) || v < 1) v = 1;
    if (stockState.mode === 'remove' && v > stockState.stock) v = stockState.stock;
    if (v < 1) v = 1;

    stockState.qty = v;
    input.value = v;
    document.getElementById('stockModalQtySubmit').value = v;

    const newStock = stockState.mode === 'add'
        ? stockState.stock + v
        : stockState.stock - v;

    const previewEl = document.getElementById('stockModalPreview');
    previewEl.textContent = newStock + ' ' + stockState.unit;
    previewEl.style.color = newStock < 0
        ? 'var(--red)'
        : (stockState.mode === 'add' ? 'var(--green)' : 'var(--cyan)');
}

// Close on backdrop click
document.getElementById('stockModal').addEventListener('click', function(e) {
    if (e.target === this) closeStockModal();
});

// Keyboard: Enter = confirm, Escape = close
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('stockModal');
    if (modal.style.display !== 'flex') return;
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('stockModalForm').submit();
    } else if (e.key === 'Escape') {
        closeStockModal();
    }
});
</script>

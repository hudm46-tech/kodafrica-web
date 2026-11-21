<?php
$isEdit = $product !== null;
$action = $isEdit ? url('/products/' . $product['product_id']) : url('/products');

$val = fn(string $k, $fallback = '') => $isEdit ? ($product[$k] ?? $fallback) : old($k, $fallback);

$currentName     = $val('name');
$currentUnit     = $val('unit_of_measure', 'PCS');
$currentQty      = $val('quantity', 0);
$currentMin      = $val('minimum_stock', 5);
$currentPurchase = $val('purchase_price', 0);
$currentSelling  = $val('selling_price', 0);
$currentCategory = $val('category');
$currentSupplier = $val('supplier');
$currentRemarks  = $val('remarks');
?>

<form method="post" action="<?= $action ?>" class="card">
    <?= csrf_field() ?>

    <div style="margin-bottom:18px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Product Name *</label>
        <input type="text" name="name" value="<?= e($currentName) ?>" placeholder="e.g., RED OXIDE 1LTR" required autofocus style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Category</label>
            <input type="text" name="category" value="<?= e($currentCategory) ?>" list="categoryList" placeholder="e.g., Paints" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
            <datalist id="categoryList">
                <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c) ?>">
                <?php endforeach; ?>
                <option value="Paints"><option value="Electrical"><option value="Plumbing">
                <option value="Tools"><option value="Hardware"><option value="Roofing">
                <option value="Cleaning"><option value="Safety"><option value="Other">
            </datalist>
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Unit</label>
            <select name="unit_of_measure" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
                <?php foreach (['PCS','PAIR','KG','LTR','METRE','BOX','SET','ROLL'] as $u): ?>
                    <option value="<?= $u ?>" <?= $currentUnit === $u ? 'selected' : '' ?>><?= $u ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Purchase Price (TZS)</label>
            <input type="number" name="purchase_price" value="<?= e($currentPurchase) ?>" placeholder="0" min="0" step="1" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Selling Price (TZS) *</label>
            <input type="number" name="selling_price" value="<?= e($currentSelling) ?>" placeholder="0" min="1" step="1" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">
                <?= $isEdit ? 'Current Quantity' : 'Initial Quantity' ?>
            </label>
            <input type="number" name="quantity" value="<?= e($currentQty) ?>" placeholder="0" min="0" step="1" <?= $isEdit ? 'readonly' : '' ?> style="width:100%;padding:12px 14px;background:<?= $isEdit ? 'rgba(255,255,255,.02)' : '#0A1929' ?>;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:<?= $isEdit ? 'var(--muted)' : '#fff' ?>;font-family:inherit;font-size:14px">
            <?php if ($isEdit): ?>
                <div style="font-size:10px;color:var(--muted);margin-top:6px">Use +/− buttons on the list to adjust stock</div>
            <?php endif; ?>
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Low-Stock Threshold</label>
            <input type="number" name="minimum_stock" value="<?= e($currentMin) ?>" placeholder="5" min="0" step="1" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <div style="margin-bottom:18px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Supplier (optional)</label>
        <input type="text" name="supplier" value="<?= e($currentSupplier) ?>" placeholder="Supplier name" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
    </div>

    <div style="margin-bottom:22px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Remarks (optional)</label>
        <textarea name="remarks" rows="2" placeholder="Notes about this product…" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px;resize:vertical"><?= e($currentRemarks) ?></textarea>
    </div>

    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?= $isEdit ? 'Update Product' : 'Save Product' ?>
        </button>
        <a href="<?= url('/products') ?>" class="btn" style="background:rgba(255,255,255,.05);color:var(--text);text-decoration:none;padding:15px 24px;flex:0">Cancel</a>
    </div>
</form>

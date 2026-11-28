<?php
$cartTotal = 0; $cartCount = 0;
foreach ($cartDetail as $c) { $cartTotal += $c['line_total']; $cartCount += $c['qty']; }

$cartItem = function (array $c): void {
    $pid = (int)$c['product_id'];
    ?>
                    <div class="cart-item" data-pid="<?= $pid ?>">
                        <div>
                            <div class="name"><?= e($c['name']) ?></div>
                            <div class="sub">
                                <button type="button" class="price-edit" onclick="openPriceModal(<?= $pid ?>)" title="Adjust price">
                                    <?php if (!empty($c['custom'])): ?><s class="was"><?= money($c['list_price']) ?></s> <?php endif; ?><?= money($c['price']) ?> × <?= (int)$c['qty'] ?> <?= e($c['unit']) ?> ✎
                                </button>
                                <?php if (!empty($c['custom'])): ?><span class="disc-chip">−<?= money(($c['list_price'] - $c['price']) * $c['qty']) ?></span><?php endif; ?>
                            </div>
                        </div>
                        <button class="qty-btn minus" onclick="updateQty(<?= $pid ?>, <?= $c['qty'] - 1 ?>)">−</button>
                        <div class="qty-display"><?= (int)$c['qty'] ?></div>
                        <button class="qty-btn plus" onclick="updateQty(<?= $pid ?>, <?= $c['qty'] + 1 ?>)">+</button>
                    </div>
    <?php
};
?>

<style>
.pos-grid { display: grid; grid-template-columns: 1fr; gap: 16px; padding-bottom: 90px; }
@media (min-width: 900px) {
    .pos-grid { grid-template-columns: 1fr 380px; padding-bottom: 0; }
    .mobile-cart-bar { display: none !important; }
    .mobile-cart-sheet { display: none !important; }
}
@media (max-width: 899px) { .desktop-cart { display: none !important; } }

.pos-search { display: flex; gap: 8px; margin-bottom: 14px; }
.pos-search input { flex: 1; padding: 12px 14px; background: #0A1929; border: 1px solid rgba(255,255,255,.1); border-radius: 12px; color: #fff; font-family: inherit; font-size: 14px; }
.pos-search button { padding: 12px 20px; border-radius: 12px; background: var(--cyan); color: #fff; border: none; font-weight: 700; cursor: pointer; }

.product-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
@media (min-width: 600px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1200px) { .product-grid { grid-template-columns: repeat(4, 1fr); } }

.product-card {
  background: var(--card); border: 1px solid var(--divider);
  border-radius: 12px; padding: 12px; cursor: pointer;
  transition: transform .12s, border-color .12s;
  display: flex; flex-direction: column; gap: 6px;
}
.product-card:hover { transform: translateY(-2px); border-color: rgba(0,180,216,.4); }
.product-card:active { transform: translateY(0); }
.product-card .initial { width: 36px; height: 36px; border-radius: 9px; background: rgba(0,180,216,.15); color: var(--cyan); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; }
.product-card .name { font-size: 12.5px; font-weight: 600; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; min-height: 32px; }
.product-card .meta { display: flex; justify-content: space-between; align-items: center; gap: 6px; }
.product-card .price { font-size: 13px; font-weight: 800; color: var(--cyan); }
.product-card .stock { font-size: 10px; color: var(--muted); }
.product-card .stock.low { color: var(--orange); font-weight: 600; }

.cart-panel { position: sticky; top: 20px; height: fit-content; max-height: calc(100vh - 120px); display: flex; flex-direction: column; }
.cart-head { padding: 14px 16px; border-bottom: 1px solid var(--divider); display: flex; align-items: center; gap: 10px; }
.cart-body { flex: 1; overflow-y: auto; max-height: 45vh; }
.cart-item { display: grid; grid-template-columns: 1fr auto auto auto; gap: 10px; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--divider); }
.cart-item .name { font-size: 13px; font-weight: 600; }
.cart-item .sub { font-size: 11px; color: var(--muted); margin-top: 2px; }
.price-edit { background: none; border: none; padding: 0; margin: 0; font: inherit; font-size: 11px; color: var(--cyan); cursor: pointer; text-align: left; }
.price-edit .was { color: var(--muted); opacity: .8; }
.disc-chip { display: inline-block; margin-left: 4px; padding: 1px 6px; border-radius: 6px; background: rgba(255,145,0,.15); color: var(--orange); font-size: 10px; font-weight: 800; }
.qty-btn { width: 26px; height: 26px; border-radius: 7px; border: none; cursor: pointer; font-weight: 800; font-size: 14px; display: flex; align-items: center; justify-content: center; }
.qty-btn.minus { background: rgba(255,23,68,.15); color: var(--red); }
.qty-btn.plus  { background: rgba(0,200,83,.15); color: var(--green); }
.qty-display { min-width: 28px; text-align: center; font-weight: 700; font-size: 13px; }
.cart-empty { padding: 40px 20px; text-align: center; color: var(--muted); }

.cart-footer { border-top: 1px solid var(--divider); padding: 16px; }
.totals-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 13px; color: var(--muted); }
.totals-row.total { font-size: 20px; font-weight: 800; color: var(--text); margin-bottom: 16px; }
.totals-row.total span:last-child { color: var(--green); }

.checkout-btn { width: 100%; padding: 15px; border-radius: 12px; background: linear-gradient(135deg, var(--green) 0%, #00897B 100%); color: #fff; border: none; font-weight: 800; font-size: 15px; cursor: pointer; box-shadow: 0 8px 20px -8px rgba(0,200,83,.5); display: flex; align-items: center; justify-content: center; gap: 10px; }
.checkout-btn:disabled { opacity: .5; cursor: not-allowed; }

.mobile-cart-bar {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: var(--card); border-top: 1px solid var(--divider);
    padding: 12px 16px; z-index: 90;
    display: flex; align-items: center; gap: 12px;
    box-shadow: 0 -8px 24px rgba(0,0,0,.4);
    transition: transform .2s ease;
}
.mobile-cart-bar.hidden { transform: translateY(100%); }
.mobile-cart-bar .cart-icon-wrap { position: relative; width: 44px; height: 44px; border-radius: 12px; background: rgba(0,180,216,.15); color: var(--cyan); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mobile-cart-bar .cart-badge { position: absolute; top: -6px; right: -6px; min-width: 20px; height: 20px; padding: 0 6px; background: var(--red); color: #fff; border-radius: 10px; font-size: 11px; font-weight: 800; display: none; align-items: center; justify-content: center; border: 2px solid var(--card); }
.mobile-cart-bar .cart-badge.show { display: flex; }
.mobile-cart-bar .cart-info { flex: 1; min-width: 0; }
.mobile-cart-bar .cart-info .label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .08em; font-weight: 700; }
.mobile-cart-bar .cart-info .total { font-size: 17px; font-weight: 800; color: var(--green); margin-top: 1px; }
.mobile-cart-bar .view-btn { padding: 12px 18px; border-radius: 12px; background: linear-gradient(135deg, var(--green) 0%, #00897B 100%); color: #fff; border: none; font-weight: 800; font-size: 13px; cursor: pointer; white-space: nowrap; box-shadow: 0 6px 16px -6px rgba(0,200,83,.6); }
.mobile-cart-bar .view-btn:disabled { opacity: .4; cursor: not-allowed; box-shadow: none; }

.mobile-cart-sheet { position: fixed; inset: 0; z-index: 200; display: none; }
.mobile-cart-sheet.show { display: block; }
.mobile-cart-sheet .backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.7); animation: fadeIn .2s ease; }
.mobile-cart-sheet .sheet { position: absolute; bottom: 0; left: 0; right: 0; background: var(--card); border-radius: 20px 20px 0 0; max-height: 85vh; display: flex; flex-direction: column; animation: slideUp .25s ease; }
@keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.mobile-cart-sheet .sheet-handle { width: 44px; height: 4px; background: rgba(255,255,255,.2); border-radius: 2px; margin: 10px auto 0; }
.mobile-cart-sheet .sheet-head { padding: 14px 20px; border-bottom: 1px solid var(--divider); display: flex; align-items: center; gap: 12px; }
.mobile-cart-sheet .sheet-body { flex: 1; overflow-y: auto; min-height: 100px; }
.mobile-cart-sheet .sheet-foot { padding: 16px 20px 20px; border-top: 1px solid var(--divider); }
.mobile-cart-sheet .cart-item { padding: 14px 20px; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.8); z-index: 999; display: none; align-items: flex-end; justify-content: center; }
@media (min-width: 700px) { .modal-overlay { align-items: center; padding: 20px; } }
.modal-overlay.show { display: flex; }
.modal-content { background: var(--card); border-radius: 20px 20px 0 0; width: 100%; max-width: 560px; max-height: 92vh; overflow-y: auto; padding: 24px; }
@media (min-width: 700px) { .modal-content { border-radius: 20px; } }
.payment-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 20px; }
.payment-btn { padding: 14px; background: #0A1929; border: 2px solid rgba(255,255,255,.08); border-radius: 12px; color: var(--text); cursor: pointer; font-family: inherit; font-weight: 600; font-size: 13px; text-align: left; transition: border-color .12s; }
.payment-btn:hover { border-color: rgba(0,180,216,.3); }
.payment-btn.selected { border-color: var(--cyan); background: rgba(0,180,216,.08); }

/* ---- Quantity modal ---- */
.qty-modal .modal-content { max-width: 400px; }
.qty-display-big {
    font-size: 64px; font-weight: 800; text-align: center;
    color: var(--cyan); margin: 12px 0;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.qty-controls {
    display: grid; grid-template-columns: 64px 1fr 64px;
    gap: 12px; align-items: center; margin: 20px 0;
}
.qty-step-btn {
    width: 64px; height: 64px; border-radius: 16px;
    border: none; cursor: pointer;
    font-size: 32px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    transition: transform .1s;
}
.qty-step-btn:active { transform: scale(.95); }
.qty-step-btn.minus { background: rgba(255,23,68,.15); color: var(--red); }
.qty-step-btn.plus  { background: rgba(0,200,83,.15); color: var(--green); }
.qty-step-btn:disabled { opacity: .3; cursor: not-allowed; }
.qty-input-big {
    width: 100%; height: 64px; text-align: center;
    background: #0A1929; border: 2px solid rgba(255,255,255,.1);
    border-radius: 16px; color: #fff;
    font-size: 24px; font-weight: 800;
    font-family: inherit;
}
.qty-input-big:focus { outline: none; border-color: var(--cyan); }
.quick-qty-row {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 6px; margin-top: 8px;
}
.quick-qty-btn {
    padding: 10px; border-radius: 8px;
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
    color: var(--text); font-weight: 700; font-size: 13px;
    cursor: pointer; font-family: inherit;
    transition: background .12s;
}
.quick-qty-btn:hover { background: rgba(0,180,216,.15); border-color: rgba(0,180,216,.3); }
</style>

<div class="pos-grid">

    <!-- PRODUCT GRID -->
    <div>
        <form method="get" action="<?= url('/pos') ?>" class="pos-search">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search products…" autofocus>
            <button type="submit">Search</button>
        </form>

        <?php if (empty($products)): ?>
            <div class="card" style="text-align:center;padding:48px 20px">
                <div style="font-size:40px;margin-bottom:12px">🔍</div>
                <div style="font-size:15px;font-weight:700">No products found</div>
                <div style="font-size:12px;color:var(--muted);margin-top:6px"><?= $search ? 'Try a different search' : 'Add products in Stock menu first' ?></div>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                    <?php $isLow = (int)$p['quantity'] <= (int)$p['minimum_stock']; ?>
                    <div class="product-card"
                         data-id="<?= $p['product_id'] ?>"
                         data-name="<?= e($p['name']) ?>"
                         data-price="<?= (float)$p['selling_price'] ?>"
                         data-stock="<?= (int)$p['quantity'] ?>"
                         data-unit="<?= e($p['unit_of_measure']) ?>"
                         onclick="openQuantityModal(this)">
                        <div class="initial"><?= e(strtoupper(substr($p['name'], 0, 1))) ?></div>
                        <div class="name"><?= e($p['name']) ?></div>
                        <div class="meta">
                            <span class="price"><?= money($p['selling_price']) ?></span>
                            <span class="stock <?= $isLow ? 'low' : '' ?>"><?= $p['quantity'] ?> <?= e($p['unit_of_measure']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:14px;font-size:11px;color:var(--muted)">
                Showing <?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?> · tap to add to cart
            </div>
        <?php endif; ?>
    </div>

    <!-- DESKTOP CART -->
    <div class="cart-panel card desktop-cart" style="padding:0">
        <div class="cart-head">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <div style="font-weight:700;font-size:15px">Cart</div>
            <div style="color:var(--muted);font-size:12px" id="cartCount"><?= $cartCount ?> item<?= $cartCount === 1 ? '' : 's' ?></div>
            <div style="flex:1"></div>
            <?php if (!empty($cartDetail)): ?>
            <button type="button" onclick="clearCart()" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:11px;font-weight:700;padding:4px 8px">Clear</button>
            <?php endif; ?>
        </div>
        <div class="cart-body" id="cartBody">
            <?php if (empty($cartDetail)): ?>
                <div class="cart-empty"><div style="font-size:36px;margin-bottom:10px">🛒</div><div style="font-size:13px">Cart is empty</div><div style="font-size:11px;margin-top:4px">Tap products to add</div></div>
            <?php else: ?>
                <?php foreach ($cartDetail as $c) { $cartItem($c); } ?>
            <?php endif; ?>
        </div>
        <div class="cart-footer">
            <div class="totals-row total"><span>Total</span><span id="cartTotal"><?= money($cartTotal) ?></span></div>
            <button type="button" class="checkout-btn" id="checkoutBtn" onclick="openCheckout()" <?= empty($cartDetail) ? 'disabled' : '' ?>>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                Checkout
            </button>
        </div>
    </div>
</div>

<!-- MOBILE CART BAR -->
<div class="mobile-cart-bar <?= empty($cartDetail) ? 'hidden' : '' ?>" id="mobileCartBar">
    <div class="cart-icon-wrap">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="cart-badge <?= $cartCount > 0 ? 'show' : '' ?>" id="mobileCartBadge"><?= $cartCount ?></span>
    </div>
    <div class="cart-info">
        <div class="label">Cart Total</div>
        <div class="total" id="mobileCartTotal"><?= money($cartTotal) ?></div>
    </div>
    <button class="view-btn" id="mobileViewBtn" onclick="openMobileCart()" <?= empty($cartDetail) ? 'disabled' : '' ?>>View Cart</button>
</div>

<!-- MOBILE CART SHEET -->
<div class="mobile-cart-sheet" id="mobileCartSheet">
    <div class="backdrop" onclick="closeMobileCart()"></div>
    <div class="sheet">
        <div class="sheet-handle"></div>
        <div class="sheet-head">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            <div style="font-weight:700;font-size:16px">Cart</div>
            <div style="color:var(--muted);font-size:13px" id="mobileSheetCount"><?= $cartCount ?> item<?= $cartCount === 1 ? '' : 's' ?></div>
            <div style="flex:1"></div>
            <?php if (!empty($cartDetail)): ?>
            <button type="button" onclick="clearCart()" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:12px;font-weight:700;padding:4px 8px">Clear</button>
            <?php endif; ?>
            <button type="button" onclick="closeMobileCart()" style="background:none;border:none;color:var(--muted);cursor:pointer;font-size:20px;padding:4px 8px;line-height:1">×</button>
        </div>
        <div class="sheet-body" id="mobileCartBody">
            <?php if (empty($cartDetail)): ?>
                <div class="cart-empty"><div style="font-size:36px;margin-bottom:10px">🛒</div><div style="font-size:13px">Cart is empty</div></div>
            <?php else: ?>
                <?php foreach ($cartDetail as $c) { $cartItem($c); } ?>
            <?php endif; ?>
        </div>
        <div class="sheet-foot">
            <div class="totals-row total"><span>Total</span><span id="mobileSheetTotal"><?= money($cartTotal) ?></span></div>
            <button type="button" class="checkout-btn" id="mobileCheckoutBtn" onclick="openCheckout()" <?= empty($cartDetail) ? 'disabled' : '' ?>>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                Checkout
            </button>
        </div>
    </div>
</div>

<!-- ============ QUANTITY MODAL ============ -->
<div class="modal-overlay qty-modal" id="qtyModal">
    <div class="modal-content">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
            <div id="qtyModalInitial" style="width:44px;height:44px;border-radius:11px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;flex-shrink:0">?</div>
            <div style="flex:1;min-width:0">
                <div id="qtyModalName" style="font-size:15px;font-weight:700;line-height:1.2">Product</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px">
                    <span id="qtyModalPrice">TZS 0</span> · <span id="qtyModalStock">0 PCS</span> available
                </div>
            </div>
            <button type="button" onclick="closeQuantityModal()" style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.05);border:none;color:var(--muted);cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center">×</button>
        </div>

        <div class="qty-display-big" id="qtyBigDisplay">1</div>

        <div class="qty-controls">
            <button type="button" class="qty-step-btn minus" onclick="adjustQty(-1)">−</button>
            <input type="number" class="qty-input-big" id="qtyInput" value="1" min="1" inputmode="numeric" oninput="onQtyInput(this)">
            <button type="button" class="qty-step-btn plus" onclick="adjustQty(1)">+</button>
        </div>

        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Quick select</div>
        <div class="quick-qty-row">
            <button type="button" class="quick-qty-btn" onclick="setQty(1)">1</button>
            <button type="button" class="quick-qty-btn" onclick="setQty(2)">2</button>
            <button type="button" class="quick-qty-btn" onclick="setQty(5)">5</button>
            <button type="button" class="quick-qty-btn" onclick="setQty(10)">10</button>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 0 0;margin-top:16px;border-top:1px solid var(--divider)">
            <div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700">Subtotal</div>
                <div id="qtySubtotal" style="font-size:18px;font-weight:800;color:var(--green);margin-top:2px">TZS 0</div>
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px">
            <button type="button" onclick="closeQuantityModal()" class="btn" style="flex:1;background:rgba(255,255,255,.05);color:var(--text)">Cancel</button>
            <button type="button" onclick="confirmAddToCart()" class="btn btn-primary" style="flex:2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-3px;margin-right:4px"><path d="M12 5v14M5 12h14"/></svg>
                Add to Cart
            </button>
        </div>
    </div>
</div>

<!-- ============ CHECKOUT MODAL ============ -->
<div class="modal-overlay" id="checkoutModal">
    <div class="modal-content">
        <h2 style="margin:0 0 6px;font-size:20px;font-weight:800">Checkout</h2>
        <p style="color:var(--muted);font-size:13px;margin:0 0 20px">Total: <strong id="modalTotal" style="color:var(--green);font-size:16px"><?= money($cartTotal) ?></strong></p>

        <form method="post" action="<?= url('/pos/checkout') ?>" id="checkoutForm">
            <?= csrf_field() ?>

            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:10px">Payment Method</label>
            <div class="payment-grid" id="paymentGrid">
                <?php foreach (\App\Models\Sale::PAYMENT_METHODS as $key => $label): ?>
                    <button type="button" class="payment-btn <?= $key === 'CASH' ? 'selected' : '' ?>" data-method="<?= $key ?>" onclick="selectPayment(this)"><?= e($label) ?></button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="payment_method" id="paymentMethodInput" value="CASH">

            <div id="nonCashFields" style="display:none">
                <div style="margin-bottom:14px">
                    <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Transaction Reference</label>
                    <input type="text" name="payment_reference" placeholder="e.g., M-Pesa code" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
                </div>
            </div>

            <div id="creditFields" style="display:none">
                <div style="margin-bottom:14px">
                    <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Customer Name *</label>
                    <input type="text" name="customer_name" placeholder="Full name" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
                </div>
                <div style="margin-bottom:14px">
                    <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Customer Phone</label>
                    <input type="tel" name="customer_phone" placeholder="07XX XXX XXX" style="width:100%;padding:11px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px">
                <button type="button" onclick="closeCheckout()" class="btn" style="flex:1;background:rgba(255,255,255,.05);color:var(--text)">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:2">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- ============ ADJUST PRICE MODAL ============ -->
<div class="modal-overlay" id="priceModal" style="z-index:1001">
    <div class="modal-content">
        <h2 style="margin:0 0 4px;font-size:18px;font-weight:800" id="priceModalName">Adjust price</h2>
        <p style="color:var(--muted);font-size:12px;margin:0 0 16px">
            Normal price <b id="priceModalList" style="color:var(--text)"></b>
            · Lowest allowed <b id="priceModalMin" style="color:var(--text)"></b>
        </p>
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">New price per unit (TZS)</label>
        <input type="number" id="priceInput" min="0" step="0.01" inputmode="decimal" oninput="onPriceInput()"
               style="width:100%;padding:14px;background:#0A1929;border:1px solid rgba(255,255,255,.12);border-radius:12px;color:#fff;font-family:inherit;font-size:22px;font-weight:800">
        <div id="priceModalInfo" style="font-size:12px;color:var(--muted);margin-top:10px;min-height:18px"></div>
        <div id="priceModalError" style="font-size:12px;color:var(--red);margin-top:6px;min-height:16px;font-weight:600"></div>
        <div style="display:flex;gap:10px;margin-top:14px">
            <button type="button" onclick="closePriceModal()" class="btn" style="flex:1;background:rgba(255,255,255,.05);color:var(--text)">Cancel</button>
            <button type="button" onclick="resetPrice()" class="btn" style="flex:1;background:rgba(255,145,0,.12);color:var(--orange)">Reset</button>
            <button type="button" onclick="applyPrice()" class="btn btn-primary" style="flex:2">Apply</button>
        </div>
    </div>
</div>

<script>
// ============ STATE ============
window.CART = <?= json_encode($cartDetail, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let currentProduct = null;
let currentQty = 1;

// ============ UTILS ============
function csrfToken() { return document.querySelector('input[name="_csrf"]').value; }
function money(n) { return 'TZS ' + Math.round(n).toLocaleString('en-US'); }
function escapeHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

async function postForm(url, data) {
    const fd = new FormData();
    fd.append('_csrf', csrfToken());
    for (const [k, v] of Object.entries(data)) fd.append(k, v);
    const res = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' });
    return res.json();
}

// ============ QUANTITY MODAL ============
function openQuantityModal(card) {
    const stock = parseInt(card.dataset.stock, 10);
    if (stock <= 0) { showToast('Out of stock', 'error'); return; }

    currentProduct = {
        id:    parseInt(card.dataset.id, 10),
        name:  card.dataset.name,
        price: parseFloat(card.dataset.price),
        stock: stock,
        unit:  card.dataset.unit,
    };
    currentQty = 1;

    document.getElementById('qtyModalInitial').textContent = currentProduct.name.charAt(0).toUpperCase();
    document.getElementById('qtyModalName').textContent = currentProduct.name;
    document.getElementById('qtyModalPrice').textContent = money(currentProduct.price);
    document.getElementById('qtyModalStock').textContent = stock + ' ' + currentProduct.unit;

    setQty(1);
    document.getElementById('qtyModal').classList.add('show');
    setTimeout(() => document.getElementById('qtyInput').select(), 100);
}

function closeQuantityModal() {
    document.getElementById('qtyModal').classList.remove('show');
    currentProduct = null;
}

function adjustQty(delta) {
    if (!currentProduct) return;
    setQty(currentQty + delta);
}

function setQty(n) {
    if (!currentProduct) return;
    let v = parseInt(n, 10);
    if (isNaN(v) || v < 1) v = 1;
    if (v > currentProduct.stock) v = currentProduct.stock;
    currentQty = v;

    document.getElementById('qtyInput').value = v;
    document.getElementById('qtyBigDisplay').textContent = v;
    document.getElementById('qtySubtotal').textContent = money(currentProduct.price * v);

    // Disable + when at max
    const plusBtn = document.querySelector('.qty-step-btn.plus');
    const minusBtn = document.querySelector('.qty-step-btn.minus');
    if (plusBtn) plusBtn.disabled = (v >= currentProduct.stock);
    if (minusBtn) minusBtn.disabled = (v <= 1);
}

function onQtyInput(input) {
    const v = parseInt(input.value, 10);
    if (isNaN(v) || v < 1) return;
    setQty(v);
}

async function confirmAddToCart() {
    if (!currentProduct) return;
    const pid = currentProduct.id;
    const qty = currentQty;
    closeQuantityModal();

    const json = await postForm('<?= url('/pos/add') ?>', { product_id: pid, qty: qty });
    if (json.error) { showToast(json.error, 'error'); return; }
    renderCart(json.cart, json.count);
    showToast('Added ' + qty + ' to cart', 'success');
}

// ============ CART OPERATIONS ============
async function updateQty(pid, qty) {
    const json = await postForm('<?= url('/pos/update') ?>', { product_id: pid, qty: qty });
    if (json.error) { showToast(json.error, 'error'); return; }
    renderCart(json.cart, json.count);
}

async function clearCart() {
    if (!confirm('Clear all items from cart?')) return;
    const json = await postForm('<?= url('/pos/clear') ?>', {});
    renderCart(json.cart || [], 0);
}

function cartItemHtml(c) {
    const was = c.custom ? '<s class="was">' + money(c.list_price) + '</s> ' : '';
    const chip = c.custom ? '<span class="disc-chip">−' + money((c.list_price - c.price) * c.qty) + '</span>' : '';
    return `<div class="cart-item" data-pid="${c.product_id}">
            <div><div class="name">${escapeHtml(c.name)}</div><div class="sub"><button type="button" class="price-edit" onclick="openPriceModal(${c.product_id})" title="Adjust price">${was}${money(c.price)} × ${c.qty} ${escapeHtml(c.unit)} ✎</button>${chip}</div></div>
            <button class="qty-btn minus" onclick="updateQty(${c.product_id}, ${c.qty - 1})">−</button>
            <div class="qty-display">${c.qty}</div>
            <button class="qty-btn plus" onclick="updateQty(${c.product_id}, ${c.qty + 1})">+</button>
        </div>`;
}

function renderCart(cart, count) {
    window.CART = cart || [];
    const desktopBody = document.getElementById('cartBody');
    const mobileBody = document.getElementById('mobileCartBody');
    const desktopTotal = document.getElementById('cartTotal');
    const mobileTotal = document.getElementById('mobileCartTotal');
    const mobileSheetTotal = document.getElementById('mobileSheetTotal');
    const modalTotal = document.getElementById('modalTotal');
    const desktopCount = document.getElementById('cartCount');
    const mobileSheetCount = document.getElementById('mobileSheetCount');
    const mobileBadge = document.getElementById('mobileCartBadge');
    const mobileBar = document.getElementById('mobileCartBar');
    const desktopCheckout = document.getElementById('checkoutBtn');
    const mobileCheckout = document.getElementById('mobileCheckoutBtn');
    const mobileViewBtn = document.getElementById('mobileViewBtn');

    const totalStr = money(cart.reduce((s, c) => s + c.line_total, 0));

    desktopCount.textContent = count + ' item' + (count === 1 ? '' : 's');
    mobileSheetCount.textContent = count + ' item' + (count === 1 ? '' : 's');
    mobileBadge.textContent = count;

    if (!cart || cart.length === 0) {
        const empty = '<div class="cart-empty"><div style="font-size:36px;margin-bottom:10px">🛒</div><div style="font-size:13px">Cart is empty</div><div style="font-size:11px;margin-top:4px">Tap products to add</div></div>';
        desktopBody.innerHTML = empty;
        mobileBody.innerHTML = empty;
        desktopTotal.textContent = 'TZS 0';
        mobileTotal.textContent = 'TZS 0';
        mobileSheetTotal.textContent = 'TZS 0';
        modalTotal.textContent = 'TZS 0';
        desktopCheckout.disabled = true;
        mobileCheckout.disabled = true;
        mobileViewBtn.disabled = true;
        mobileBar.classList.add('hidden');
        mobileBadge.classList.remove('show');
        return;
    }

    mobileBar.classList.remove('hidden');
    mobileBadge.classList.add('show');
    desktopCheckout.disabled = false;
    mobileCheckout.disabled = false;
    mobileViewBtn.disabled = false;

    let html = '';
    for (const c of cart) html += cartItemHtml(c);
    desktopBody.innerHTML = html;
    mobileBody.innerHTML = html;
    desktopTotal.textContent = totalStr;
    mobileTotal.textContent = totalStr;
    mobileSheetTotal.textContent = totalStr;
    modalTotal.textContent = totalStr;
}

// ============ MOBILE SHEET ============
function openMobileCart() { document.getElementById('mobileCartSheet').classList.add('show'); }
function closeMobileCart() { document.getElementById('mobileCartSheet').classList.remove('show'); }

// ============ CHECKOUT ============
function openCheckout() {
    closeMobileCart();
    document.getElementById('checkoutModal').classList.add('show');
}
function closeCheckout() { document.getElementById('checkoutModal').classList.remove('show'); }

function selectPayment(btn) {
    document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    const method = btn.dataset.method;
    document.getElementById('paymentMethodInput').value = method;
    document.getElementById('nonCashFields').style.display = (method === 'CASH') ? 'none' : 'block';
    document.getElementById('creditFields').style.display = (method === 'CREDIT') ? 'block' : 'none';
}

// ============ ADJUST PRICE ============
let priceTarget = null;
function openPriceModal(pid) {
    const c = (window.CART || []).find(x => x.product_id === pid);
    if (!c) return;
    priceTarget = c;
    document.getElementById('priceModalName').textContent = c.name;
    document.getElementById('priceModalList').textContent = money(c.list_price);
    document.getElementById('priceModalMin').textContent = c.min_price <= 0.01 ? 'any price' : money(c.min_price);
    document.getElementById('priceModalError').textContent = '';
    const input = document.getElementById('priceInput');
    input.value = c.price;
    document.getElementById('priceModal').classList.add('show');
    onPriceInput();
    setTimeout(() => { input.focus(); input.select(); }, 50);
}
function closePriceModal() { document.getElementById('priceModal').classList.remove('show'); priceTarget = null; }
function onPriceInput() {
    if (!priceTarget) return;
    const v = parseFloat(document.getElementById('priceInput').value);
    const info = document.getElementById('priceModalInfo');
    document.getElementById('priceModalError').textContent = '';
    if (!(v > 0)) { info.textContent = ''; return; }
    const off = priceTarget.list_price - v;
    if (off > 0.004) {
        info.textContent = 'Discount ' + money(off) + ' per unit (' + (off / priceTarget.list_price * 100).toFixed(1) + '%) · line total ' + money(v * priceTarget.qty);
    } else {
        info.textContent = 'Line total ' + money(v * priceTarget.qty);
    }
}
async function applyPrice() {
    if (!priceTarget) return;
    const v = document.getElementById('priceInput').value.trim();
    const json = await postForm('<?= url('/pos/price') ?>', { product_id: priceTarget.product_id, price: v });
    if (json.error) { document.getElementById('priceModalError').textContent = json.error; return; }
    renderCart(json.cart, json.count);
    closePriceModal();
    showToast('Price updated', 'success');
}
async function resetPrice() {
    if (!priceTarget) return;
    const json = await postForm('<?= url('/pos/price') ?>', { product_id: priceTarget.product_id, price: '' });
    if (json.error) { document.getElementById('priceModalError').textContent = json.error; return; }
    renderCart(json.cart, json.count);
    closePriceModal();
    showToast('Price reset to normal', 'info');
}

function showToast(msg, type = 'info') {
    const div = document.createElement('div');
    div.textContent = msg;
    div.style.cssText = 'position:fixed;bottom:100px;left:50%;transform:translateX(-50%);padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:1000;box-shadow:0 8px 24px rgba(0,0,0,.4);background:' + (type === 'error' ? '#FF1744' : '#00C853') + ';color:#fff';
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 2000);
}

// Enter key = confirm
document.addEventListener('keydown', (e) => {
    if (document.getElementById('priceModal').classList.contains('show')) {
        if (e.key === 'Enter') { e.preventDefault(); applyPrice(); }
        if (e.key === 'Escape') closePriceModal();
        return;
    }
    if (e.key === 'Enter' && document.getElementById('qtyModal').classList.contains('show')) {
        e.preventDefault();
        confirmAddToCart();
    }
    if (e.key === 'Escape') {
        if (document.getElementById('qtyModal').classList.contains('show')) closeQuantityModal();
        if (document.getElementById('checkoutModal').classList.contains('show')) closeCheckout();
        if (document.getElementById('mobileCartSheet').classList.contains('show')) closeMobileCart();
    }
});
</script>

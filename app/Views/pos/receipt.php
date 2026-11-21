<div class="receipt-actions">
    <button onclick="window.print()" class="receipt-btn ghost">🖨️ Print</button>
    <button onclick="downloadPDF('receipt-<?= date('Ymd-His') ?>.pdf')" class="receipt-btn primary">📥 Download PDF</button>
    <a href="<?= url('/pos') ?>" class="receipt-btn ghost">New Sale</a>
</div>

<div class="receipt-paper">
    <h1><?= e($business['business_name'] ?? 'KODAFIRCA') ?></h1>
    <div class="sub">
        <?= e($branch['branch_name'] ?? '') ?><br>
        <?= e($branch['location'] ?? '') ?><br>
        <?php if (!empty($branch['phone_number'])): ?>Tel: <?= e($branch['phone_number']) ?><br><?php endif; ?>
        <?php if (!empty($branch['tin_number'])): ?>TIN: <?= e($branch['tin_number']) ?><?php endif; ?>
    </div>

    <div class="receipt-divider"></div>

    <div class="receipt-row"><span>Receipt #</span><span><?= e(date('ymdHis', strtotime($saleTs))) ?></span></div>
    <div class="receipt-row"><span>Date</span><span><?= e(date('d/m/Y H:i', strtotime($saleTs))) ?></span></div>
    <div class="receipt-row"><span>Cashier</span><span><?= e($cashier['full_name'] ?? '-') ?></span></div>
    <div class="receipt-row"><span>Payment</span><span><?= e(\App\Models\Sale::PAYMENT_METHODS[$paymentMethod] ?? $paymentMethod) ?></span></div>
    <?php if (!empty($reference)): ?>
    <div class="receipt-row"><span>Reference</span><span><?= e($reference) ?></span></div>
    <?php endif; ?>
    <?php if (!empty($customerName)): ?>
    <div class="receipt-row"><span>Customer</span><span><?= e($customerName) ?></span></div>
    <?php endif; ?>
    <?php if (!empty($customerPhone)): ?>
    <div class="receipt-row"><span>Phone</span><span><?= e($customerPhone) ?></span></div>
    <?php endif; ?>

    <div class="receipt-divider"></div>

    <?php foreach ($sales as $s): ?>
        <div class="receipt-item">
            <div class="name"><?= e($s['product_name']) ?></div>
            <div class="line">
                <span><?= $s['quantity_sold'] ?> × <?= number_format((float)$s['selling_price_at_sale'], 0, '.', ',') ?></span>
                <span><?= number_format((float)$s['total_amount'], 0, '.', ',') ?></span>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="receipt-divider"></div>

    <div class="receipt-row"><span>Items</span><span><?= $itemCount ?></span></div>
    <div class="receipt-row total"><span>TOTAL (TZS)</span><span><?= number_format($total, 0, '.', ',') ?></span></div>

    <?php if ($paymentMethod === 'CREDIT'): ?>
        <div class="receipt-divider"></div>
        <div class="receipt-row" style="color:#c00;font-weight:700"><span>CREDIT SALE</span></div>
        <div class="receipt-row" style="color:#c00"><span>Payment pending</span></div>
    <?php endif; ?>

    <div class="receipt-footer">
        <div style="margin-bottom:6px">Thank you for your business!</div>
        <div>Karibu tena 🙏</div>
        <div style="margin-top:8px">Powered by Kodafirca POS</div>
    </div>
</div>

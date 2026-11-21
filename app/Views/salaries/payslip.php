<div class="receipt-actions">
    <button onclick="window.print()" class="receipt-btn ghost">🖨️ Print</button>
    <button onclick="downloadPDF('receipt-<?= date('Ymd-His') ?>.pdf')" class="receipt-btn primary">📥 Download PDF</button>
    <a href="<?= url('/salaries') ?>" class="receipt-btn ghost">← Back</a>
</div>

<div class="receipt-paper">
    <h1><?= e($business['business_name'] ?? 'KODAFIRCA') ?></h1>
    <div class="sub">
        <?= e($branch['branch_name'] ?? '') ?><br>
        <?= e($branch['location'] ?? '') ?>
    </div>

    <div class="receipt-divider"></div>

    <div style="text-align:center;font-weight:800;font-size:14px;letter-spacing:1px;margin-bottom:12px">PAYSLIP</div>

    <div class="receipt-row"><span>Payslip #</span><span><?= str_pad((string)$payment['payment_id'], 6, '0', STR_PAD_LEFT) ?></span></div>
    <div class="receipt-row"><span>Period</span><span><?= e($payment['period_month']) ?> <?= e($payment['period_year']) ?></span></div>
    <div class="receipt-row"><span>Date</span><span><?= e(date('d/m/Y', strtotime($payment['payment_date']))) ?></span></div>

    <div class="receipt-divider"></div>

    <div class="receipt-row"><span>Employee</span><span><?= e($payment['employee_name']) ?></span></div>
    <div class="receipt-row"><span>Role</span><span><?= e($payment['role']) ?></span></div>
    <div class="receipt-row"><span>Type</span><span><?= e($payment['employment_type']) ?></span></div>
    <?php if (!empty($payment['employee_phone'])): ?>
    <div class="receipt-row"><span>Phone</span><span><?= e($payment['employee_phone']) ?></span></div>
    <?php endif; ?>

    <div class="receipt-divider"></div>

    <div class="receipt-row" style="font-weight:800;margin-bottom:8px"><span>PAYMENT DETAILS</span></div>

    <?php if ($payment['payment_type'] === 'POSHO'): ?>
        <div class="receipt-row"><span>Days Worked</span><span><?= (int)$payment['days_worked'] ?></span></div>
        <div class="receipt-row"><span>Daily Rate</span><span><?= number_format((float)$payment['daily_rate'], 0, '.', ',') ?></span></div>
    <?php endif; ?>

    <div class="receipt-row"><span>Base Amount</span><span><?= number_format((float)$payment['base_amount'], 0, '.', ',') ?></span></div>

    <?php if ((float)$payment['overtime_amount'] > 0): ?>
        <div class="receipt-row"><span>Overtime (<?= $payment['overtime_hours'] ?>h × <?= number_format((float)$payment['overtime_rate'], 0) ?>)</span><span>+ <?= number_format((float)$payment['overtime_amount'], 0, '.', ',') ?></span></div>
    <?php endif; ?>

    <?php if ((float)$payment['bonus'] > 0): ?>
        <div class="receipt-row"><span>Bonus</span><span>+ <?= number_format((float)$payment['bonus'], 0, '.', ',') ?></span></div>
    <?php endif; ?>

    <?php if ((float)$payment['deductions'] > 0): ?>
        <div class="receipt-row"><span>Deductions</span><span>− <?= number_format((float)$payment['deductions'], 0, '.', ',') ?></span></div>
    <?php endif; ?>

    <div class="receipt-divider"></div>

    <div class="receipt-row total"><span>NET PAY</span><span><?= number_format((float)$payment['amount_paid'], 0, '.', ',') ?></span></div>

    <div class="receipt-row" style="margin-top:12px"><span>Payment Method</span><span><?= e($payment['payment_method']) ?></span></div>
    <?php if (!empty($payment['payment_reference'])): ?>
        <div class="receipt-row"><span>Reference</span><span><?= e($payment['payment_reference']) ?></span></div>
    <?php endif; ?>

    <?php if (!empty($payment['notes'])): ?>
        <div class="receipt-divider"></div>
        <div style="font-size:10px;color:#666;font-style:italic"><?= e($payment['notes']) ?></div>
    <?php endif; ?>

    <div class="receipt-divider"></div>

    <div class="receipt-footer">
        <div style="margin-top:16px">_______________________</div>
        <div style="font-size:9px;margin-top:4px">Employee Signature</div>
        <div style="margin-top:16px;font-size:10px;color:#666">Powered by Kodafirca POS</div>
    </div>
</div>

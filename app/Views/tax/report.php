<div class="receipt-actions">
    <button onclick="window.print()" class="receipt-btn ghost">🖨️ Print</button>
    <button onclick="downloadPDF('receipt-<?= date('Ymd-His') ?>.pdf')" class="receipt-btn primary">📥 Download PDF</button>
    <a href="<?= url('/tax') ?>" class="receipt-btn ghost">← Back</a>
</div>

<div class="receipt-paper" style="max-width:480px">
    <h1><?= e($business['business_name'] ?? 'KODAFIRCA') ?></h1>
    <div class="sub">
        TIN: <?= e($business['business_tin'] ?? '-') ?><br>
        <?php if (!empty($business['business_vrn'])): ?>VRN: <?= e($business['business_vrn']) ?><?php endif; ?>
    </div>

    <div class="receipt-divider"></div>

    <div style="text-align:center;font-weight:800;font-size:14px;letter-spacing:1px;margin-bottom:12px">TAX REPORT</div>

    <div class="receipt-row"><span>Period</span><span>
        <?php if ((int)$report['report_month'] > 0): ?>
            <?= e(date('F', mktime(0,0,0,$report['report_month'],1))) ?> <?= (int)$report['report_year'] ?>
        <?php else: ?>
            Year <?= (int)$report['report_year'] ?>
        <?php endif; ?>
    </span></div>
    <div class="receipt-row"><span>Generated</span><span><?= e(date('d/m/Y H:i', strtotime($report['generated_date']))) ?></span></div>
    <div class="receipt-row"><span>By</span><span><?= e($report['generated_by'] ?? '-') ?></span></div>
    <div class="receipt-row"><span>Regime</span><span><?= e($report['regime_used']) ?></span></div>
    <div class="receipt-row"><span>Entity</span><span><?= e($report['entity_type'] === 'LIMITED_COMPANY' ? 'Limited Company' : 'Sole Proprietor') ?></span></div>

    <div class="receipt-divider"></div>

    <div class="receipt-row" style="font-weight:800;margin-bottom:8px"><span>FINANCIAL SUMMARY</span></div>
    <div class="receipt-row"><span>Total Sales</span><span><?= number_format((float)$report['total_sales'], 0, '.', ',') ?></span></div>
    <div class="receipt-row"><span>Cost of Goods Sold</span><span>(<?= number_format((float)$report['cost_of_goods_sold'], 0, '.', ',') ?>)</span></div>
    <div class="receipt-row"><span>Gross Profit</span><span><?= number_format((float)$report['gross_profit'], 0, '.', ',') ?></span></div>
    <div class="receipt-row"><span>Operating Expenses</span><span>(<?= number_format((float)$report['total_expenses'], 0, '.', ',') ?>)</span></div>
    <div class="receipt-row" style="font-weight:700;border-top:1px solid #999;padding-top:6px;margin-top:6px"><span>Net Profit</span><span><?= number_format((float)$report['net_profit'], 0, '.', ',') ?></span></div>

    <div class="receipt-divider"></div>

    <div class="receipt-row" style="font-weight:800;margin-bottom:8px"><span>TAX CALCULATION</span></div>

    <?php if ($report['regime_used'] === 'PRESUMPTIVE'): ?>
        <div class="receipt-row"><span>Annual Turnover</span><span><?= number_format((float)$report['annual_turnover'], 0, '.', ',') ?></span></div>
        <div class="receipt-row"><span>Bracket Used</span><span><?= (int)$report['presumptive_bracket_used'] ?>/4</span></div>
        <?php if (!empty($report['presumptive_rate_used'])): ?>
        <div class="receipt-row"><span>Rate Applied</span><span><?= number_format((float)$report['presumptive_rate_used'], 1) ?>%</span></div>
        <?php endif; ?>
    <?php elseif (strpos($report['regime_used'], 'CORPORATE') !== false): ?>
        <div class="receipt-row"><span>Corporate Rate</span><span><?= number_format((float)$report['corporate_rate_used'], 1) ?>%</span></div>
    <?php endif; ?>

    <div class="receipt-row total"><span>ESTIMATED TAX</span><span><?= number_format((float)$report['estimated_tax'], 0, '.', ',') ?></span></div>

    <?php if ((int)$report['vat_required']): ?>
        <div class="receipt-divider"></div>
        <div class="receipt-row" style="color:#c00;font-weight:700"><span>VAT REGISTRATION TRIGGERED</span></div>
        <div class="receipt-row"><span>VAT Rate</span><span><?= number_format((float)$report['vat_rate_used'], 1) ?>%</span></div>
        <div class="receipt-row"><span>VAT Amount (extracted)</span><span><?= number_format((float)$report['vat_amount'], 0, '.', ',') ?></span></div>
        <div class="receipt-row" style="font-size:10px;color:#666;margin-top:6px">
            <span>You must remit VAT to TRA by the 20th</span>
        </div>
    <?php else: ?>
        <div class="receipt-row" style="font-size:10px;color:#666;margin-top:6px"><span>Below VAT threshold</span></div>
    <?php endif; ?>

    <div class="receipt-divider"></div>

    <div class="receipt-footer">
        <div style="font-size:9px;color:#666;margin-top:8px">Report snapshot — rates may change over time</div>
        <div style="margin-top:12px;font-size:10px">Powered by Kodafirca POS</div>
    </div>
</div>

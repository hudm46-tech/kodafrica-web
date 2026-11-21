<form method="post" action="<?= url('/tax/generate') ?>" class="card">
    <?= csrf_field() ?>

    <h2 style="margin:0 0 6px;font-size:18px;font-weight:800">Generate Tax Report</h2>
    <p style="margin:0 0 20px;font-size:12px;color:var(--muted)">
        This will compute taxes based on sales, expenses, and your entity type.
    </p>

    <div style="margin-bottom:16px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:8px">Year *</label>
        <input type="number" name="year" value="<?= date('Y') ?>" min="2020" max="2100" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
    </div>

    <div style="margin-bottom:20px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:8px">Period</label>
        <select name="month" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
            <option value="0">Full Year</option>
            <?php foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m): ?>
                <option value="<?= $i + 1 ?>" <?= date('n') === (string)($i + 1) ? 'selected' : '' ?>><?= $m ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="background:rgba(0,180,216,.08);padding:14px;border-radius:12px;font-size:12px;color:var(--muted);margin-bottom:20px;line-height:1.6">
        <strong style="color:var(--cyan)">📊 Calculation:</strong><br>
        <?php if (($settings['entity_type'] ?? '') === 'LIMITED_COMPANY'): ?>
            Corporate Tax = <?= e($settings['corporate_rate'] ?? 30) ?>% × Net Profit<br>
        <?php else: ?>
            Presumptive Tax based on turnover brackets<br>
        <?php endif; ?>
        VAT <?= e($settings['vat_rate'] ?? 18) ?>% checked against registration threshold of TZS <?= number_format((float)($settings['vat_registration_threshold'] ?? 100000000)) ?>
    </div>

    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Generate Report
        </button>
        <a href="<?= url('/tax') ?>" class="btn" style="background:rgba(255,255,255,.05);color:var(--text);text-decoration:none;padding:15px 24px">Cancel</a>
    </div>
</form>

<?php
$entityLabel = ($settings['entity_type'] ?? '') === 'LIMITED_COMPANY' ? 'Limited Company' : 'Sole Proprietor';
?>

<div class="hero" style="margin-bottom:20px">
    <div class="hero-label">TAX REGIME</div>
    <div class="hero-value" style="font-size:24px"><?= e($entityLabel) ?></div>
    <div class="hero-meta">
        <?php if ($entityLabel === 'Sole Proprietor'): ?>
            Presumptive Tax · Corporate rate: <?= e($settings['corporate_rate'] ?? 30) ?>%
        <?php else: ?>
            Corporate Tax · Rate: <?= e($settings['corporate_rate'] ?? 30) ?>%
        <?php endif; ?>
        · VAT: <?= e($settings['vat_rate'] ?? 18) ?>%
    </div>
</div>

<?php if ($isAdmin): ?>
<div style="text-align:right;margin-bottom:12px;font-size:12px">
    <a href="<?= url('/settings') ?>" style="color:var(--cyan);text-decoration:none">⚙️ Edit tax settings</a>
</div>
<?php endif; ?>

<div class="section-title">Report History</div>

<?php if (empty($history)): ?>
<div class="card" style="text-align:center;padding:48px 20px">
    <div style="font-size:40px;margin-bottom:12px">📄</div>
    <div style="font-size:15px;font-weight:700;margin-bottom:6px">No tax reports yet</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:18px">Generate your first TRA tax report</div>
    <a href="<?= url('/tax/generate') ?>" class="btn btn-primary" style="display:inline-flex;width:auto;padding:12px 24px">Generate Report</a>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
    <?php foreach ($history as $i => $r): ?>
        <div style="display:grid;grid-template-columns:auto 1fr auto auto;gap:12px;align-items:center;padding:14px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,193,7,.15);color:var(--amber);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div style="min-width:0">
                <div style="font-size:14px;font-weight:600">
                    <?php if ((int)$r['report_month'] > 0): ?>
                        <?= e(date('F', mktime(0,0,0,$r['report_month'],1))) ?> <?= (int)$r['report_year'] ?>
                    <?php else: ?>
                        Year <?= (int)$r['report_year'] ?>
                    <?php endif; ?>
                    <span style="font-size:11px;color:var(--muted);font-weight:400"> · <?= e($r['regime_used']) ?></span>
                </div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;gap:8px;flex-wrap:wrap">
                    <span>Sales: <?= money($r['total_sales']) ?></span>
                    <span>·</span>
                    <span>Tax: <?= money($r['estimated_tax']) ?></span>
                    <?php if ((int)$r['vat_required']): ?>
                        <span>·</span>
                        <span style="color:var(--orange)">VAT: <?= money($r['vat_amount']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="font-size:15px;font-weight:800;color:var(--amber);white-space:nowrap"><?= money($r['estimated_tax']) ?></div>
            <div style="display:flex;gap:6px">
                <a href="<?= url('/tax/' . $r['report_id']) ?>" style="width:32px;height:32px;border-radius:8px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center" title="View">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <?php if ($isAdmin): ?>
                <form method="post" action="<?= url('/tax/' . $r['report_id'] . '/delete') ?>" onsubmit="return confirm('Delete this report?')" style="margin:0">
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
<?php endif; ?>

<!-- Filter bar -->
<form method="get" action="<?= url('/expenses') ?>" class="card" style="margin-bottom:16px">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">From</label>
            <input type="date" name="from" value="<?= e($from) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">To</label>
            <input type="date" name="to" value="<?= e($to) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Group</label>
            <select name="group" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
                <option value="">All groups</option>
                <?php foreach ($categoryGroups as $g => $_): ?>
                    <option value="<?= e($g) ?>" <?= $activeGroup === $g ? 'selected' : '' ?>><?= e(ucfirst(strtolower($g))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Filter</button>
    </div>
</form>

<!-- Total card -->
<div class="hero" style="margin-bottom:16px">
    <div class="hero-label">Total Expenses (<?= e(date('d M', strtotime($from))) ?> – <?= e(date('d M Y', strtotime($to))) ?>)</div>
    <div class="hero-value"><?= money($total) ?></div>
    <div class="hero-meta"><?= $count ?> entr<?= $count === 1 ? 'y' : 'ies' ?></div>
</div>

<!-- Group totals mini-cards -->
<?php if (!empty($groups)): ?>
<div class="mini-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
    <?php foreach ($groups as $gKey => $gData): ?>
        <div class="mini">
            <div class="mini-label"><?= e(ucfirst(strtolower($gKey))) ?></div>
            <div class="mini-value orange" style="font-size:15px"><?= money($gData['total']) ?></div>
            <div style="font-size:11px;color:var(--muted);margin-top:4px"><?= $gData['count'] ?> item<?= $gData['count'] === 1 ? '' : 's' ?></div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- EFD Breakdown -->
<div class="section-title">EFD Receipt Status</div>
<div class="mini-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:20px">
    <a href="<?= url('/expenses?from=' . urlencode($from) . '&to=' . urlencode($to) . '&efd=with') ?>" style="text-decoration:none;color:inherit">
        <div class="mini" style="border:<?= $activeEfd === 'with' ? '2px solid var(--green)' : '1px solid var(--divider)' ?>;cursor:pointer">
            <div class="mini-label" style="color:var(--green)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:3px"><path d="M20 6L9 17l-5-5"/></svg>
                WITH EFD
            </div>
            <div class="mini-value green" style="font-size:16px"><?= money($efdWithTotal) ?></div>
            <div style="font-size:11px;color:var(--muted);margin-top:4px"><?= $efdWithCount ?> entr<?= $efdWithCount === 1 ? 'y' : 'ies' ?></div>
        </div>
    </a>
    <a href="<?= url('/expenses?from=' . urlencode($from) . '&to=' . urlencode($to) . '&efd=without') ?>" style="text-decoration:none;color:inherit">
        <div class="mini" style="border:<?= $activeEfd === 'without' ? '2px solid var(--orange)' : '1px solid var(--divider)' ?>;cursor:pointer">
            <div class="mini-label" style="color:var(--orange)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:3px"><path d="M18 6L6 18M6 6l12 12"/></svg>
                WITHOUT EFD
            </div>
            <div class="mini-value orange" style="font-size:16px"><?= money($efdWithoutTotal) ?></div>
            <div style="font-size:11px;color:var(--muted);margin-top:4px"><?= $efdWithoutCount ?> entr<?= $efdWithoutCount === 1 ? 'y' : 'ies' ?></div>
        </div>
    </a>
</div>

<?php if ($activeEfd): ?>
<div style="margin-bottom:12px;text-align:center">
    <a href="<?= url('/expenses?from=' . urlencode($from) . '&to=' . urlencode($to)) ?>" style="display:inline-block;padding:8px 16px;background:rgba(255,255,255,.05);border-radius:8px;color:var(--muted);text-decoration:none;font-size:12px;font-weight:600">✕ Clear EFD filter</a>
</div>
<?php endif; ?>

<div style="display:flex;gap:12px;align-items:center;margin:24px 0 12px">
    <div class="section-title" style="margin:0">Recent Expenses</div>
    <div style="flex:1"></div>
    <a href="<?= url('/expenses/summary?from=' . urlencode($from) . '&to=' . urlencode($to)) ?>" style="font-size:12px;color:var(--cyan);font-weight:600">View summary →</a>
</div>

<?php if (empty($expenses)): ?>
<div class="card" style="text-align:center;padding:48px 20px">
    <div style="font-size:40px;margin-bottom:12px">💸</div>
    <div style="font-size:15px;font-weight:700;margin-bottom:6px">No expenses yet</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:18px">Start by recording your first expense</div>
    <a href="<?= url('/expenses/create') ?>" class="btn btn-primary" style="display:inline-flex;width:auto;padding:12px 24px">Record Expense</a>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
    <?php foreach ($expenses as $i => $e): ?>
        <?php $hasReceipt = !empty($e['receipt_image_path']); ?>
        <div style="display:grid;grid-template-columns:auto 1fr auto auto;gap:12px;align-items:center;padding:14px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,145,0,.15);color:var(--orange);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div style="min-width:0">
                <div style="font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($e['description']) ?></div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                    <span><?= e(\App\Models\Expense::categoryLabel($e['category'])) ?></span>
                    <span>·</span>
                    <span><?= e(date('d M Y', strtotime($e['expense_date']))) ?></span>
                    <?php if ($hasReceipt): ?>
                        <a href="<?= url('/uploads/' . $e['receipt_image_path']) ?>" target="_blank" style="display:inline-flex;align-items:center;gap:3px;color:var(--cyan);font-size:10px;font-weight:600;text-decoration:none">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            receipt
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div style="font-size:15px;font-weight:800;color:var(--orange);white-space:nowrap"><?= money($e['amount']) ?></div>
            <div style="display:flex;gap:6px">
                <a href="<?= url('/expenses/' . $e['expense_id'] . '/edit') ?>" style="width:32px;height:32px;border-radius:8px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <?php if (auth_role() === 'ADMIN' || auth_role() === 'MANAGER'): ?>
                <form method="post" action="<?= url('/expenses/' . $e['expense_id'] . '/delete') ?>" onsubmit="return confirm('Delete this expense and its receipt?')" style="margin:0">
                    <?= csrf_field() ?>
                    <button type="submit" style="width:32px;height:32px;border-radius:8px;background:rgba(255,23,68,.15);color:var(--red);display:flex;align-items:center;justify-content:center;border:none;cursor:pointer">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

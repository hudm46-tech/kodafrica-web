<form method="get" action="<?= url('/reports/staff-costs') ?>" class="card" style="margin-bottom:16px;padding:14px">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end">
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">Month</label>
            <select name="month" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit">
                <?php foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $m): ?>
                    <option <?= $month === $m ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">Year</label>
            <input type="number" name="year" value="<?= $year ?>" min="2020" max="2100" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Apply</button>
    </div>
</form>

<div class="mini-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:20px">
    <div class="mini"><div class="mini-label">SALARIES</div><div class="mini-value" style="font-size:16px;color:var(--cyan)"><?= money($salaryTotal) ?></div></div>
    <div class="mini"><div class="mini-label">POSHO</div><div class="mini-value orange" style="font-size:16px"><?= money($poshoTotal) ?></div></div>
</div>

<div class="section-title">Staff-Related Expenses (<?= e($month) ?> <?= $year ?>)</div>
<div class="card" style="padding:0;overflow:hidden">
    <?php if (empty($staffExpenses)): ?>
        <div style="padding:30px;text-align:center;color:var(--muted);font-size:13px">No staff-related expenses</div>
    <?php else: foreach ($staffExpenses as $i => $e): ?>
        <div style="display:flex;justify-content:space-between;padding:12px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <span style="font-size:13px"><?= e(\App\Models\Expense::categoryLabel($e['category'])) ?></span>
            <span style="font-weight:700;color:var(--orange)"><?= money($e['total']) ?></span>
        </div>
    <?php endforeach; endif; ?>
</div>

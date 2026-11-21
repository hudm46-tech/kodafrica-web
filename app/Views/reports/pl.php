<form method="get" action="<?= url('/reports/pl') ?>" class="card" style="margin-bottom:16px;padding:14px">
    <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px;align-items:end">
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">From</label>
            <input type="date" name="from" value="<?= e($from) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <div><label style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:700;letter-spacing:.1em;display:block;margin-bottom:6px">To</label>
            <input type="date" name="to" value="<?= e($to) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit"></div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Apply</button>
    </div>
</form>

<div class="card">
    <h2 style="margin:0 0 20px;font-size:18px;font-weight:800">Profit &amp; Loss Statement</h2>
    <div style="font-size:12px;color:var(--muted);margin-bottom:20px"><?= e(date('d M Y', strtotime($from))) ?> – <?= e(date('d M Y', strtotime($to))) ?></div>

    <div style="border-top:1px solid var(--divider);padding-top:16px">
        <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:14px">
            <span>Revenue (Sales)</span>
            <span style="font-weight:700;color:var(--green)"><?= money($revenue) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:13px;color:var(--muted)">
            <span>− Cost of Goods Sold (COGS)</span>
            <span>(<?= money($cogs) ?>)</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:12px 0;border-top:2px solid rgba(0,180,216,.3);margin-top:8px;font-size:15px;font-weight:700">
            <span>Gross Profit</span>
            <span style="color:var(--cyan)"><?= money($grossProfit) ?></span>
        </div>
    </div>

    <div style="border-top:1px solid var(--divider);margin-top:16px;padding-top:16px">
        <div style="font-size:13px;font-weight:700;color:var(--muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:.12em">Operating Expenses</div>
        <?php if (empty($expensesByGroup)): ?>
            <div style="font-size:13px;color:var(--muted);padding:8px 0">No expenses</div>
        <?php else: foreach ($expensesByGroup as $gName => $gData): ?>
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px">
                <span style="color:var(--muted)"><?= e(ucfirst(strtolower($gName))) ?></span>
                <span><?= money($gData['total']) ?></span>
            </div>
        <?php endforeach; endif; ?>
        <div style="display:flex;justify-content:space-between;padding:12px 0;border-top:1px solid var(--divider);margin-top:8px;font-size:14px;font-weight:700">
            <span>Total Expenses</span>
            <span style="color:var(--orange)">(<?= money($totalExpenses) ?>)</span>
        </div>
    </div>

    <div style="border-top:3px solid <?= $netProfit >= 0 ? 'var(--green)' : 'var(--red)' ?>;margin-top:20px;padding-top:16px;display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:17px;font-weight:800">NET PROFIT</span>
        <span style="font-size:24px;font-weight:800;color:<?= $netProfit >= 0 ? 'var(--green)' : 'var(--red)' ?>"><?= money($netProfit) ?></span>
    </div>
</div>

<!-- Quick stats -->
<div class="mini-grid" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));margin-bottom:24px">
    <div class="mini">
        <div class="mini-label">TODAY</div>
        <div class="mini-value green" style="font-size:16px"><?= money($stats['today_sales']) ?></div>
    </div>
    <div class="mini">
        <div class="mini-label">THIS MONTH</div>
        <div class="mini-value green" style="font-size:16px"><?= money($stats['month_sales']) ?></div>
    </div>
    <div class="mini">
        <div class="mini-label">MONTH PROFIT</div>
        <div class="mini-value" style="font-size:16px;color:var(--cyan)"><?= money($stats['month_profit']) ?></div>
    </div>
    <div class="mini">
        <div class="mini-label">MONTH EXPENSES</div>
        <div class="mini-value orange" style="font-size:16px"><?= money($stats['month_expense']) ?></div>
    </div>
</div>

<!-- Report cards -->
<div class="section-title">Available Reports</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px">

    <a href="<?= url('/reports/sales') ?>" class="card" style="padding:20px;text-decoration:none;color:inherit;transition:transform .12s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <div style="width:44px;height:44px;border-radius:11px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center;margin-bottom:14px">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div style="font-size:15px;font-weight:700;margin-bottom:4px">Sales Report</div>
        <div style="font-size:12px;color:var(--muted)">Daily breakdown, payment methods, cashier performance</div>
    </a>

    <a href="<?= url('/reports/profit') ?>" class="card" style="padding:20px;text-decoration:none;color:inherit;transition:transform .12s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <div style="width:44px;height:44px;border-radius:11px;background:rgba(0,200,83,.15);color:var(--green);display:flex;align-items:center;justify-content:center;margin-bottom:14px">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div style="font-size:15px;font-weight:700;margin-bottom:4px">Profit Report</div>
        <div style="font-size:12px;color:var(--muted)">Gross profit, daily trends, expense impact</div>
    </a>

    <a href="<?= url('/reports/expenses') ?>" class="card" style="padding:20px;text-decoration:none;color:inherit;transition:transform .12s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <div style="width:44px;height:44px;border-radius:11px;background:rgba(255,145,0,.15);color:var(--orange);display:flex;align-items:center;justify-content:center;margin-bottom:14px">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div style="font-size:15px;font-weight:700;margin-bottom:4px">Expense Report</div>
        <div style="font-size:12px;color:var(--muted)">Category breakdown, daily totals, trend</div>
    </a>

    <a href="<?= url('/reports/top-products') ?>" class="card" style="padding:20px;text-decoration:none;color:inherit;transition:transform .12s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <div style="width:44px;height:44px;border-radius:11px;background:rgba(255,193,7,.15);color:var(--amber);display:flex;align-items:center;justify-content:center;margin-bottom:14px">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div style="font-size:15px;font-weight:700;margin-bottom:4px">Top Products</div>
        <div style="font-size:12px;color:var(--muted)">Best sellers by quantity & revenue</div>
    </a>

    <a href="<?= url('/reports/staff-costs') ?>" class="card" style="padding:20px;text-decoration:none;color:inherit;transition:transform .12s" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <div style="width:44px;height:44px;border-radius:11px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center;margin-bottom:14px">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div style="font-size:15px;font-weight:700;margin-bottom:4px">Staff Costs</div>
        <div style="font-size:12px;color:var(--muted)">Salaries, posho, staff-related expenses</div>
    </a>

    <a href="<?= url('/reports/pl') ?>" class="card" style="padding:20px;text-decoration:none;color:inherit;transition:transform .12s;border:1px solid rgba(0,200,83,.3)" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <div style="width:44px;height:44px;border-radius:11px;background:rgba(0,200,83,.15);color:var(--green);display:flex;align-items:center;justify-content:center;margin-bottom:14px">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div style="font-size:15px;font-weight:700;margin-bottom:4px">Profit &amp; Loss</div>
        <div style="font-size:12px;color:var(--muted)">Complete P&amp;L statement for the period</div>
    </a>

</div>

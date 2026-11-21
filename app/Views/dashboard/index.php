<!-- Hero: Today's Sales -->
<div class="hero">
    <div class="hero-label">Today's Sales</div>
    <div class="hero-value"><?= money($todaySales) ?></div>
    <div class="hero-meta">
        <?= $todayTxnCount ?> transaction<?= $todayTxnCount === 1 ? '' : 's' ?> today
    </div>
</div>

<!-- Mini cards -->
<div class="mini-grid">
    <div class="mini">
        <div class="mini-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            PROFIT
        </div>
        <div class="mini-value green"><?= money($todayProfit) ?></div>
    </div>
    <div class="mini">
        <div class="mini-label">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            EXPENSES
        </div>
        <div class="mini-value orange"><?= money($todayExpenses) ?></div>
    </div>
</div>

<!-- Alerts -->
<div class="section-title">Alerts</div>

<?php if (count($lowStock) > 0): ?>
<a href="<?= url('/products?filter=low') ?>" style="text-decoration:none;color:inherit">
<div class="alert-row">
    <div class="alert-icon warn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 2L4 2c-1.1 0-2 .9-2 2v3c0 .7.4 1.3 1 1.6V21c0 .6.4 1 1 1h16c.6 0 1-.4 1-1V8.6c.6-.3 1-.9 1-1.6V4c0-1.1-.9-2-2-2zM13 18h-2v-4H7l5-5 5 5h-4v4z"/></svg>
    </div>
    <div>
        <div class="alert-title">Low Stock Warning</div>
        <div class="alert-sub"><?= count($lowStock) ?> item<?= count($lowStock) === 1 ? '' : 's' ?> running low</div>
    </div>
    <div class="alert-arrow">›</div>
</div>
</a>
<?php else: ?>
<div class="alert-row">
    <div class="alert-icon info">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <div>
        <div class="alert-title">Stock Levels Healthy</div>
        <div class="alert-sub">All items are well stocked</div>
    </div>
</div>
<?php endif; ?>

<?php if ($overdueBills > 0): ?>
<a href="<?= url('/expenses?filter=overdue') ?>" style="text-decoration:none;color:inherit">
<div class="alert-row">
    <div class="alert-icon danger">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div>
        <div class="alert-title">Overdue Utility Bills</div>
        <div class="alert-sub"><?= $overdueBills ?> bill<?= $overdueBills === 1 ? '' : 's' ?> past due date</div>
    </div>
    <div class="alert-arrow">›</div>
</div>
</a>
<?php elseif ($unpaidBills > 0): ?>
<a href="<?= url('/expenses?filter=unpaid') ?>" style="text-decoration:none;color:inherit">
<div class="alert-row">
    <div class="alert-icon warn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <div>
        <div class="alert-title">Unpaid Utility Bills</div>
        <div class="alert-sub"><?= $unpaidBills ?> bill<?= $unpaidBills === 1 ? '' : 's' ?> pending</div>
    </div>
    <div class="alert-arrow">›</div>
</div>
</a>
<?php else: ?>
<div class="alert-row">
    <div class="alert-icon info">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <div>
        <div class="alert-title">Bills Up to Date</div>
        <div class="alert-sub">No utility bills pending</div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="section-title">Quick Actions</div>
<div class="quick-grid">
    <a href="<?= url('/pos') ?>" class="quick">
        <div class="quick-icon cyan">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        </div>
        <div class="quick-label">POS</div>
    </a>
    <a href="<?= url('/expenses/create') ?>" class="quick">
        <div class="quick-icon orange">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div class="quick-label">Expense</div>
    </a>
    <a href="<?= url('/products') ?>" class="quick">
        <div class="quick-icon green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 2L4 2c-1.1 0-2 .9-2 2v3c0 .7.4 1.3 1 1.6V21c0 .6.4 1 1 1h16c.6 0 1-.4 1-1V8.6c.6-.3 1-.9 1-1.6V4c0-1.1-.9-2-2-2zM13 18h-2v-4H7l5-5 5 5h-4v4z"/></svg>
        </div>
        <div class="quick-label">Stock</div>
    </a>
    <a href="<?= url('/reports') ?>" class="quick">
        <div class="quick-icon red">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div class="quick-label">Reports</div>
    </a>
</div>

<!-- Stock summary -->
<div class="section-title">Inventory</div>
<div class="mini-grid">
    <div class="mini">
        <div class="mini-label">PRODUCTS</div>
        <div class="mini-value"><?= $productCount ?></div>
    </div>
    <div class="mini">
        <div class="mini-label">STOCK VALUE</div>
        <div class="mini-value green"><?= money($stockValue) ?></div>
    </div>
</div>

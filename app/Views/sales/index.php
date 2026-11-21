<!-- Filter -->
<form method="get" action="<?= url('/sales') ?>" class="card" style="margin-bottom:16px">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:10px;align-items:end">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">From</label>
            <input type="date" name="from" value="<?= e($from) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">To</label>
            <input type="date" name="to" value="<?= e($to) ?>" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Payment</label>
            <select name="method" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
                <option value="">All</option>
                <?php foreach ($paymentMethods as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $method === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;font-weight:700;margin-bottom:6px">Cashier</label>
            <select name="cashier" style="width:100%;padding:10px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
                <option value="">All</option>
                <?php foreach ($cashiers as $c): ?>
                    <option value="<?= (int)$c['employee_id'] ?>" <?= $cashier === (int)$c['employee_id'] ? 'selected' : '' ?>><?= e($c['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 18px;font-size:13px">Filter</button>
    </div>
</form>

<!-- Hero -->
<div class="hero" style="margin-bottom:16px">
    <div class="hero-label">Sales (<?= e(date('d M', strtotime($from))) ?> – <?= e(date('d M Y', strtotime($to))) ?>)</div>
    <div class="hero-value"><?= money($totalAmount) ?></div>
    <div class="hero-meta"><?= count($groups) ?> sale<?= count($groups) === 1 ? '' : 's' ?> · <?= $totalItems ?> item<?= $totalItems === 1 ? '' : 's' ?></div>
</div>

<!-- Payment breakdown -->
<?php if (!empty($methodTotals)): ?>
<div class="mini-grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr));margin-bottom:20px">
    <?php foreach ($methodTotals as $mKey => $mData): ?>
        <div class="mini">
            <div class="mini-label"><?= e($paymentMethods[$mKey] ?? $mKey) ?></div>
            <div class="mini-value" style="font-size:14px;color:var(--cyan)"><?= money($mData['total']) ?></div>
            <div style="font-size:10px;color:var(--muted);margin-top:4px"><?= $mData['count'] ?> item<?= $mData['count'] === 1 ? '' : 's' ?></div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Unpaid credits -->
<?php if (!empty($unpaidCredits)): ?>
<div class="section-title" style="color:var(--orange)">Unpaid Credits — <?= money($creditTotal) ?></div>
<div class="card" style="padding:0;overflow:hidden;margin-bottom:20px">
    <?php foreach ($unpaidCredits as $i => $c): ?>
        <div style="display:grid;grid-template-columns:1fr auto auto;gap:12px;align-items:center;padding:14px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <div style="min-width:0">
                <div style="font-size:14px;font-weight:700"><?= e($c['customer_name'] ?: 'Walk-in customer') ?></div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;gap:8px;flex-wrap:wrap">
                    <?php if (!empty($c['customer_phone'])): ?><span><?= e($c['customer_phone']) ?></span><span>·</span><?php endif; ?>
                    <span><?= (int)$c['items'] ?> item<?= (int)$c['items'] === 1 ? '' : 's' ?></span>
                    <span>·</span>
                    <span style="color:var(--orange);font-weight:600"><?= (int)$c['days_ago'] ?> day<?= (int)$c['days_ago'] === 1 ? '' : 's' ?> ago</span>
                </div>
            </div>
            <div style="font-size:15px;font-weight:800;color:var(--orange)"><?= money($c['total']) ?></div>
            <div style="display:flex;gap:6px">
                <a href="<?= url('/sales/' . urlencode($c['group_ts'])) ?>" style="width:34px;height:34px;border-radius:8px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <form method="post" action="<?= url('/sales/mark-paid') ?>" onsubmit="return confirm('Mark this credit as paid?')" style="margin:0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="group_ts" value="<?= e($c['group_ts']) ?>">
                    <button type="submit" style="padding:0 14px;height:34px;border-radius:8px;background:rgba(0,200,83,.15);color:var(--green);border:none;cursor:pointer;font-weight:700;font-size:12px">Mark Paid</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Sales list -->
<div class="section-title">Sales in Period</div>

<?php if (empty($groups)): ?>
<div class="card" style="text-align:center;padding:48px 20px">
    <div style="font-size:40px;margin-bottom:12px">📊</div>
    <div style="font-size:15px;font-weight:700;margin-bottom:6px">No sales in this period</div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:18px">Try adjusting the date range or filters</div>
    <a href="<?= url('/pos') ?>" class="btn btn-primary" style="display:inline-flex;width:auto;padding:12px 24px">Go to POS</a>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
    <?php foreach ($groups as $i => $g): ?>
        <div style="display:grid;grid-template-columns:auto 1fr auto auto;gap:12px;align-items:center;padding:14px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <div style="min-width:0">
                <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= e($paymentMethods[$g['payment_method']] ?? $g['payment_method']) ?>
                    <?php if (!empty($g['customer_name'])): ?><span style="color:var(--muted);font-weight:400"> · <?= e($g['customer_name']) ?></span><?php endif; ?>
                </div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;gap:8px;flex-wrap:wrap">
                    <span><?= e(date('d M Y · H:i', strtotime($g['first_ts']))) ?></span>
                    <span>·</span>
                    <span><?= (int)$g['items'] ?> item<?= (int)$g['items'] === 1 ? '' : 's' ?></span>
                    <span>·</span>
                    <span><?= e($g['cashier_name'] ?? '-') ?></span>
                    <?php if ($g['payment_method'] === 'CREDIT' && (int)$g['all_paid'] === 0): ?>
                        <span style="color:var(--orange);font-weight:700">· UNPAID</span>
                    <?php elseif ($g['payment_method'] === 'CREDIT'): ?>
                        <span style="color:var(--green);font-weight:700">· paid</span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="font-size:15px;font-weight:800;color:var(--green)"><?= money($g['total']) ?></div>
            <div style="display:flex;gap:6px">
                <a href="<?= url('/sales/' . urlencode($g['group_ts'])) ?>" style="width:34px;height:34px;border-radius:8px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <?php if ($g['payment_method'] === 'CREDIT' && (int)$g['all_paid'] === 0): ?>
                <form method="post" action="<?= url('/sales/mark-paid') ?>" onsubmit="return confirm('Mark this credit as paid?')" style="margin:0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="group_ts" value="<?= e($g['group_ts']) ?>">
                    <button type="submit" style="padding:0 14px;height:34px;border-radius:8px;background:rgba(0,200,83,.15);color:var(--green);border:none;cursor:pointer;font-weight:700;font-size:12px">Pay</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
$defaultMonth = date('F');
$defaultYear  = (int)date('Y');
?>

<form method="post" action="<?= url('/salaries') ?>" class="card" id="salaryForm">
    <?= csrf_field() ?>

    <div style="margin-bottom:18px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Employee *</label>
        <select name="employee_id" id="employeeSelect" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px" onchange="onEmployeeChange()">
            <option value="">— Select employee —</option>
            <?php foreach ($employees as $e): ?>
                <option value="<?= (int)$e['employee_id'] ?>"
                        data-salary="<?= (float)$e['monthly_salary'] ?>"
                        data-rate="<?= (float)$e['daily_rate'] ?>"
                        data-role="<?= e($e['role']) ?>"
                        data-type="<?= e($e['employment_type']) ?>">
                    <?= e($e['full_name']) ?> — <?= e($e['role']) ?> (<?= e($e['employment_type']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Type *</label>
            <select name="payment_type" id="paymentType" onchange="onTypeChange()" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
                <?php foreach ($types as $key => $label): ?>
                    <option value="<?= e($key) ?>"><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Month</label>
            <select name="period_month" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
                <?php foreach ($months as $m): ?>
                    <option value="<?= e($m) ?>" <?= $m === $defaultMonth ? 'selected' : '' ?>><?= e($m) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Year</label>
            <input type="number" name="period_year" value="<?= $defaultYear ?>" min="2020" max="2100" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <!-- SALARY fields -->
    <div id="salaryFields" style="display:none">
        <div style="margin-bottom:18px;padding:14px;background:rgba(0,200,83,.08);border-radius:12px;border:1px solid rgba(0,200,83,.2)">
            <div style="font-size:12px;color:var(--green);font-weight:700;margin-bottom:8px">MONTHLY SALARY</div>
            <input type="number" name="base_amount" id="salaryAmount" value="0" min="0" step="1000" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:18px;font-weight:700;text-align:right">
        </div>
    </div>

    <!-- POSHO fields -->
    <div id="poshoFields" style="display:none">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
            <div>
                <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Days Worked</label>
                <input type="number" name="days_worked" id="daysWorked" value="0" min="0" step="1" oninput="calcPosho()" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
            </div>
            <div>
                <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Daily Rate</label>
                <input type="number" name="daily_rate" id="dailyRate" value="0" min="0" step="500" oninput="calcPosho()" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
            </div>
        </div>
        <div style="margin-bottom:18px;padding:14px;background:rgba(255,145,0,.08);border-radius:12px;border:1px solid rgba(255,145,0,.2);text-align:center">
            <div style="font-size:11px;color:var(--orange);font-weight:700;text-transform:uppercase;letter-spacing:.1em">Base Amount</div>
            <div id="poshoBaseDisplay" style="font-size:22px;font-weight:800;color:var(--orange);margin-top:4px">TZS 0</div>
        </div>
    </div>

    <!-- Manual fields (OTHER types) -->
    <div id="otherFields" style="display:none">
        <div style="margin-bottom:18px;padding:14px;background:rgba(0,180,216,.08);border-radius:12px;border:1px solid rgba(0,180,216,.2)">
            <div style="font-size:12px;color:var(--cyan);font-weight:700;margin-bottom:8px">BASE AMOUNT</div>
            <input type="number" name="base_amount_other" id="otherAmount" value="0" min="0" step="500" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:18px;font-weight:700;text-align:right">
        </div>
    </div>

    <!-- Overtime (always visible) -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Overtime Hours</label>
            <input type="number" name="overtime_hours" value="0" min="0" step="0.5" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Overtime Rate (TZS/hr)</label>
            <input type="number" name="overtime_rate" value="0" min="0" step="500" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Bonus</label>
            <input type="number" name="bonus" value="0" min="0" step="1000" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Deductions</label>
            <input type="number" name="deductions" value="0" min="0" step="500" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Payment Method</label>
            <select name="payment_method" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
                <option>CASH</option>
                <option>MPESA</option>
                <option>TIGO</option>
                <option>AIRTEL</option>
                <option>BANK</option>
            </select>
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Reference (optional)</label>
            <input type="text" name="payment_reference" placeholder="e.g., M-Pesa code" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <div style="margin-bottom:22px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Notes</label>
        <textarea name="notes" rows="2" placeholder="Optional notes" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px;resize:vertical"></textarea>
    </div>

    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/></svg>
            Save Payment
        </button>
        <a href="<?= url('/salaries') ?>" class="btn" style="background:rgba(255,255,255,.05);color:var(--text);text-decoration:none;padding:15px 24px">Cancel</a>
    </div>
</form>

<script>
function onEmployeeChange() {
    const sel = document.getElementById('employeeSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;

    const salary = parseFloat(opt.dataset.salary || 0);
    const rate = parseFloat(opt.dataset.rate || 0);

    document.getElementById('salaryAmount').value = salary > 0 ? salary : 0;
    document.getElementById('dailyRate').value = rate > 0 ? rate : 0;
    calcPosho();
}

function onTypeChange() {
    const type = document.getElementById('paymentType').value;
    const salDiv = document.getElementById('salaryFields');
    const posDiv = document.getElementById('poshoFields');
    const othDiv = document.getElementById('otherFields');

    salDiv.style.display = (type === 'SALARY') ? 'block' : 'none';
    posDiv.style.display = (type === 'POSHO') ? 'block' : 'none';
    othDiv.style.display = (['OVERTIME','ADVANCE','BONUS','NSSF'].includes(type)) ? 'block' : 'none';
}

function calcPosho() {
    const days = parseFloat(document.getElementById('daysWorked').value || 0);
    const rate = parseFloat(document.getElementById('dailyRate').value || 0);
    const total = days * rate;
    document.getElementById('poshoBaseDisplay').textContent = 'TZS ' + Math.round(total).toLocaleString('en-US');
}

// Init on page load
document.addEventListener('DOMContentLoaded', function() {
    onTypeChange();
});
</script>

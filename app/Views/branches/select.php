<style>
  .auth-card { max-width: 720px !important; padding: 32px !important; }
  .auth-head { text-align: left !important; }

  .pick-header { text-align: center; margin-bottom: 24px; }
  .pick-title { font-size: 22px; font-weight: 800; margin: 0 0 6px; }
  .pick-sub { color: var(--muted); font-size: 13px; margin: 0; }

  .pick-list { display: grid; gap: 10px; }

  .pick-card {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 14px;
    align-items: center;
    padding: 16px;
    background: #0A1929;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px;
    cursor: pointer;
    transition: all .15s;
    text-align: left;
    width: 100%;
    font-family: inherit;
    color: var(--text);
  }
  .pick-card:hover {
    border-color: var(--cyan);
    background: rgba(0,180,216,.05);
    transform: translateY(-1px);
  }
  .pick-card.inactive {
    opacity: .55;
    cursor: not-allowed;
  }
  .pick-card.inactive:hover {
    border-color: rgba(255,23,68,.3);
    background: rgba(255,23,68,.03);
    transform: none;
  }

  .pick-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    background: rgba(0,180,216,.15);
    color: var(--cyan);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .pick-card.inactive .pick-icon {
    background: rgba(255,23,68,.1);
    color: var(--red);
  }

  .pick-name { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
  .pick-loc { font-size: 12px; color: var(--muted); margin-bottom: 6px; }
  .pick-stats {
    display: flex; gap: 10px; flex-wrap: wrap;
    font-size: 11px;
  }
  .pick-stat { display: inline-flex; align-items: center; gap: 4px; }
  .pick-stat .dot { width: 5px; height: 5px; border-radius: 50%; background: var(--green); }

  .pick-arrow {
    color: var(--cyan);
    font-size: 20px;
    font-weight: 800;
  }
  .pick-card.inactive .pick-arrow {
    color: var(--red);
  }

  .pick-add {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px;
    margin-top: 16px;
    background: transparent;
    border: 2px dashed rgba(0,180,216,.35);
    border-radius: 12px;
    color: var(--cyan);
    text-decoration: none;
    font-weight: 700;
    font-size: 13px;
    transition: all .15s;
  }
  .pick-add:hover {
    background: rgba(0,180,216,.05);
    border-color: var(--cyan);
  }

  .pick-empty {
    text-align: center;
    padding: 40px 20px;
    color: var(--muted);
  }

  @media (max-width: 640px) {
    .auth-card { padding: 20px !important; }
    .pick-icon { width: 42px; height: 42px; }
    .pick-name { font-size: 14px; }
    .pick-stats { font-size: 10px; }
  }
</style>

<div class="pick-header">
    <h2 class="pick-title">Choose a Branch</h2>
    <p class="pick-sub">Welcome back, <?= e($user['full_name']) ?></p>
</div>

<?php if (empty($branches)): ?>
    <div class="pick-empty">
        <div style="font-size:36px;margin-bottom:12px;opacity:.4">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        </div>
        <div style="font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px">No branches yet</div>
        <div style="font-size:13px;margin-bottom:20px">Add your first branch to get started</div>
        <a href="<?= url('/branches/create') ?>" class="pick-add" style="border-style:solid">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Add First Branch
        </a>
    </div>
<?php else: ?>
    <div class="pick-list">
        <?php foreach ($branches as $b):
            $isActive = (int)$b['is_active'] === 1;
        ?>
            <form method="post" action="<?= url('/select-branch/' . $b['branch_id']) ?>" style="margin:0">
                <?= csrf_field() ?>
                <button type="submit" class="pick-card <?= $isActive ? '' : 'inactive' ?>" <?= $isActive ? '' : 'disabled' ?>>
                    <div class="pick-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <div style="min-width:0">
                        <div class="pick-name">
                            <?= e($b['branch_name']) ?>
                            <?php if (!$isActive): ?>
                                <span style="color:var(--red);font-size:10px;font-weight:700"> INACTIVE</span>
                            <?php endif; ?>
                        </div>
                        <div class="pick-loc"><?= e($b['location']) ?></div>
                        <div class="pick-stats">
                            <span class="pick-stat">
                                <span class="dot"></span>
                                Today: <?= money($b['today_sales']) ?>
                            </span>
                            <span class="pick-stat">
                                <span class="dot" style="background:var(--cyan)"></span>
                                <?= $b['product_count'] ?> products
                            </span>
                        </div>
                    </div>
                    <div class="pick-arrow"><?= $isActive ? '&rarr;' : '&#x2715;' ?></div>
                </button>
            </form>
        <?php endforeach; ?>
    </div>

    <a href="<?= url('/branches/create') ?>" class="pick-add">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        Add New Branch
    </a>
<?php endif; ?>

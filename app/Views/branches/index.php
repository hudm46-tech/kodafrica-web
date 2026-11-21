<?php if (empty($branches)): ?>
    <div class="card" style="text-align:center;padding:48px 20px">
        <div style="font-size:36px;margin-bottom:12px;opacity:.4">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        </div>
        <div style="font-size:15px;font-weight:700;margin-bottom:6px">No branches yet</div>
        <div style="font-size:13px;color:var(--muted);margin-bottom:18px">Add your first branch</div>
        <a href="<?= url('/branches/create') ?>" class="btn btn-primary" style="display:inline-flex;width:auto;padding:12px 24px">Add Branch</a>
    </div>
<?php else: ?>
    <div class="card" style="padding:0;overflow:hidden">
        <?php foreach ($branches as $i => $b):
            $isActive = (int)$b['is_active'] === 1;
            $isCurrent = (int)$b['branch_id'] === $currentBranch;
        ?>
            <div style="display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center;padding:14px 16px;<?= $i > 0 ? 'border-top:1px solid var(--divider)' : '' ?>;<?= !$isActive ? 'opacity:.55' : '' ?>">
                <div style="width:44px;height:44px;border-radius:11px;background:<?= $isActive ? 'rgba(0,180,216,.15)' : 'rgba(255,23,68,.1)' ?>;color:<?= $isActive ? 'var(--cyan)' : 'var(--red)' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                </div>
                <div style="min-width:0">
                    <div style="font-size:14px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= e($b['branch_name']) ?>
                        <?php if ($isCurrent): ?>
                            <span style="font-size:10px;color:var(--cyan);font-weight:700"> CURRENT</span>
                        <?php endif; ?>
                        <?php if (!$isActive): ?>
                            <span style="font-size:10px;color:var(--red);font-weight:700"> INACTIVE</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;gap:8px;flex-wrap:wrap">
                        <span><?= e($b['location']) ?></span>
                        <span>·</span>
                        <span><?= e($b['phone_number']) ?></span>
                        <?php if (!empty($b['license_expiry_date'])): ?>
                            <span>·</span>
                            <span>License: <?= e($b['license_expiry_date']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="display:flex;gap:4px">
                    <?php if ($isActive && !$isCurrent): ?>
                        <form method="post" action="<?= url('/select-branch/' . $b['branch_id']) ?>" style="margin:0">
                            <?= csrf_field() ?>
                            <button type="submit" title="Switch to this branch" style="width:32px;height:32px;border-radius:8px;background:rgba(0,200,83,.15);color:var(--green);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </button>
                        </form>
                    <?php endif; ?>

                    <a href="<?= url('/branches/' . $b['branch_id'] . '/edit') ?>" title="Edit" style="width:32px;height:32px;border-radius:8px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>

                    <form method="post" action="<?= url('/branches/' . $b['branch_id'] . '/toggle') ?>" style="margin:0" onsubmit="return confirm('<?= $isActive ? 'Deactivate' : 'Activate' ?> this branch?')">
                        <?= csrf_field() ?>
                        <button type="submit" title="<?= $isActive ? 'Deactivate' : 'Activate' ?>" style="width:32px;height:32px;border-radius:8px;background:<?= $isActive ? 'rgba(255,145,0,.15)' : 'rgba(0,200,83,.15)' ?>;color:<?= $isActive ? 'var(--orange)' : 'var(--green)' ?>;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center">
                            <?php if ($isActive): ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.36 6.64a9 9 0 1 1-12.73 0M12 2v10"/></svg>
                            <?php else: ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            <?php endif; ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

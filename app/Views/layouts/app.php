<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Kodafirca POS') ?> · Kodafirca</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:#0A1929; --card:#112240; --card-2:#162B4E;
    --cyan:#00B4D8; --blue:#0077B6; --light-blue:#90E0EF;
    --green:#00C853; --orange:#FF9100; --red:#FF1744; --amber:#FFC107;
    --text:#FFFFFF; --muted:#8892B0; --faint:#5A6784;
    --divider: rgba(255,255,255,.06);
    --sidebar-w: 240px;
  }
  * { box-sizing: border-box; }
  html, body {
    margin:0; padding:0;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: var(--bg); color: var(--text);
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
  }
  a { color: var(--cyan); text-decoration: none; }
  button { font-family: inherit; }

  /* ---- Layout ---- */
  .app-shell { display: flex; min-height: 100vh; }
  .app-main  { flex: 1; min-width: 0; padding: 20px; padding-bottom: 40px; }

  @media (min-width: 900px) {
    .app-main { padding: 28px 32px; }
  }

  /* ---- Sidebar ---- */
  .sidebar {
    width: var(--sidebar-w);
    background: var(--card);
    border-right: 1px solid var(--divider);
    display: flex; flex-direction: column;
    position: fixed; top: 0; bottom: 0; left: 0;
    transform: translateX(-100%);
    transition: transform .2s ease;
    z-index: 100;
  }
  .sidebar.open { transform: translateX(0); }
  @media (min-width: 900px) {
    .sidebar { position: sticky; top: 0; height: 100vh; transform: translateX(0); }
    .sidebar-overlay { display: none; }
    .menu-toggle { display: none; }
  }

  .sidebar-head {
    padding: 20px 18px;
    border-bottom: 1px solid var(--divider);
    display: flex; align-items: center; gap: 12px;
  }
  .sidebar-logo {
    width: 40px; height: 40px; flex-shrink: 0;
  }
  .sidebar-brand {
    font-weight: 800; font-size: 15px; letter-spacing: -.3px;
  }
  .sidebar-brand span { color: var(--cyan); }
  .sidebar-business {
    font-size: 10px; color: var(--muted); text-transform: uppercase;
    letter-spacing: .1em; margin-top: 2px;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
  }

  .sidebar-user {
    padding: 16px 18px; border-bottom: 1px solid var(--divider);
  }
  .sidebar-user-name { font-weight: 700; font-size: 14px; }
  .sidebar-user-role {
    font-size: 11px; color: var(--cyan); margin-top: 3px;
    text-transform: uppercase; letter-spacing: .08em; font-weight: 600;
  }

  .sidebar-nav {
    flex: 1; overflow-y: auto; padding: 12px 0;
  }
  .nav-section {
    font-size: 10px; color: var(--faint); padding: 16px 18px 6px;
    text-transform: uppercase; letter-spacing: .14em; font-weight: 700;
  }
  .nav-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 18px;
    color: var(--muted); font-size: 13.5px; font-weight: 500;
    transition: background .1s, color .1s;
    text-decoration: none;
    border-left: 3px solid transparent;
  }
  .nav-item:hover { background: rgba(255,255,255,.03); color: var(--text); }
  .nav-item.active {
    background: rgba(0,180,216,.08);
    color: var(--cyan);
    border-left-color: var(--cyan);
  }
  .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }

  /* ---- Top bar ---- */
  .topbar {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 24px;
  }
  .menu-toggle {
    width: 40px; height: 40px; border-radius: 10px;
    background: var(--card); border: 1px solid var(--divider);
    color: var(--text); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
  }
  .topbar-title {
    font-size: 22px; font-weight: 800; letter-spacing: -.5px;
    margin: 0;
  }
  .topbar-spacer { flex: 1; }
  .topbar-action {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 16px; border-radius: 12px;
    background: linear-gradient(135deg, #00B4D8, #0077B6);
    color: #fff; border: none; cursor: pointer;
    font-weight: 700; font-size: 13px;
    box-shadow: 0 8px 20px -8px rgba(0,180,216,.5);
  }
  .topbar-action:hover { transform: translateY(-1px); }

  /* ---- Cards ---- */
  .card {
    background: var(--card); border-radius: 16px;
    padding: 20px; box-shadow: 0 8px 24px -12px rgba(0,0,0,.5);
  }
  .hero {
    background: linear-gradient(135deg, #00B4D8 0%, #0077B6 100%);
    border-radius: 20px; padding: 28px;
    box-shadow: 0 12px 32px -12px rgba(0,180,216,.5);
    position: relative; overflow: hidden;
  }
  .hero::before {
    content:""; position: absolute; top:-40%; right:-20%;
    width: 60%; height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,.15), transparent 70%);
    pointer-events: none;
  }
  .hero-label {
    font-size: 11px; letter-spacing: .18em; font-weight: 700;
    color: rgba(255,255,255,.85); text-transform: uppercase;
  }
  .hero-value {
    font-size: 36px; font-weight: 800; color: #fff;
    letter-spacing: -1px; margin-top: 6px;
  }
  .hero-meta {
    margin-top: 8px; font-size: 13px; color: rgba(255,255,255,.85);
  }

  .mini-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
    margin-top: 14px;
  }
  .mini {
    background: var(--card); border-radius: 14px; padding: 16px;
    border: 1px solid var(--divider);
  }
  .mini-label {
    font-size: 10px; letter-spacing: .12em; font-weight: 700;
    color: var(--muted); text-transform: uppercase;
    display: flex; align-items: center; gap: 6px;
  }
  .mini-value {
    font-size: 20px; font-weight: 800; margin-top: 8px;
    letter-spacing: -.5px;
  }
  .mini-value.green  { color: var(--green); }
  .mini-value.orange { color: var(--orange); }
  .mini-value.red    { color: var(--red); }

  .section-title {
    font-size: 11px; letter-spacing: .18em; font-weight: 700;
    color: var(--muted); text-transform: uppercase;
    margin: 28px 0 12px;
  }

  .alert-row {
    display: flex; align-items: center; gap: 14px;
    background: var(--card); border-radius: 14px; padding: 14px 16px;
    margin-bottom: 8px; border: 1px solid var(--divider);
  }
  .alert-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .alert-icon.warn  { background: rgba(255,145,0,.15); color: var(--orange); }
  .alert-icon.info  { background: rgba(0,180,216,.15); color: var(--cyan); }
  .alert-icon.danger{ background: rgba(255,23,68,.15); color: var(--red); }
  .alert-title { font-size: 14px; font-weight: 700; }
  .alert-sub   { font-size: 12px; color: var(--muted); margin-top: 2px; }
  .alert-arrow { color: var(--faint); margin-left: auto; }

  .quick-grid {
    display: grid; grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  @media (min-width: 600px) {
    .quick-grid { grid-template-columns: repeat(4, 1fr); }
  }
  .quick {
    background: var(--card); border-radius: 14px; padding: 18px 12px;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    border: 1px solid var(--divider);
    text-decoration: none; color: var(--text);
    transition: transform .12s, border-color .12s;
    cursor: pointer;
  }
  .quick:hover { transform: translateY(-2px); border-color: rgba(0,180,216,.3); }
  .quick-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
  }
  .quick-icon.cyan   { background: rgba(0,180,216,.15); color: var(--cyan); }
  .quick-icon.orange { background: rgba(255,145,0,.15); color: var(--orange); }
  .quick-icon.green  { background: rgba(0,200,83,.15); color: var(--green); }
  .quick-icon.red    { background: rgba(255,23,68,.15); color: var(--red); }
  .quick-label { font-size: 12px; font-weight: 600; }

  /* ---- Flash ---- */
  .flash-container { margin-bottom: 18px; }
  .flash {
    padding: 12px 16px; border-radius: 12px; font-size: 13px;
    margin-bottom: 8px; display: flex; gap: 10px; align-items: center;
  }
  .flash.error   { background: rgba(255,23,68,.15); color: #FF8A9E; border: 1px solid rgba(255,23,68,.3); }
  .flash.success { background: rgba(0,200,83,.15); color: #66E89F; border: 1px solid rgba(0,200,83,.3); }
  .flash.info    { background: rgba(0,180,216,.15); color: #6ED7F5; border: 1px solid rgba(0,180,216,.3); }
  .flash.warning { background: rgba(255,145,0,.15); color: #FFB061; border: 1px solid rgba(255,145,0,.3); }

  /* ---- Sidebar overlay (mobile) ---- */
  .sidebar-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.6);
    opacity: 0; pointer-events: none;
    transition: opacity .2s; z-index: 99;
  }
  .sidebar-overlay.show { opacity: 1; pointer-events: auto; }

  /* ============================================================
     MOBILE RESPONSIVE FIXES
     ============================================================ */
  @media (max-width: 640px) {
    /* Stack all filter forms */
    form[method="get"] > div,
    .filter-grid {
      grid-template-columns: 1fr !important;
      gap: 8px !important;
    }

    /* Report mini-grids: always 2 cols on mobile */
    .mini-grid {
      grid-template-columns: 1fr 1fr !important;
      gap: 8px !important;
    }
    .mini { padding: 12px !important; }
    .mini-label { font-size: 9px !important; letter-spacing: .08em !important; }
    .mini-value { font-size: 15px !important; margin-top: 4px !important; }

    /* Tighter cards */
    .card { padding: 14px !important; border-radius: 14px !important; }
    .section-title { margin: 20px 0 10px !important; font-size: 10px !important; }

    /* Hero card smaller */
    .hero { padding: 20px !important; }
    .hero-value { font-size: 28px !important; }
    .hero-label { font-size: 10px !important; }
    .hero-meta { font-size: 11px !important; }

    /* Topbar tighter */
    .app-main { padding: 14px !important; }
    .topbar { gap: 8px !important; margin-bottom: 16px !important; }
    .topbar-title { font-size: 17px !important; }
    .menu-toggle { width: 36px !important; height: 36px !important; }
    .topbar-action { padding: 9px 14px !important; font-size: 12px !important; }

    /* Payment grid: single col on mobile */
    .payment-grid { grid-template-columns: 1fr 1fr !important; gap: 6px !important; }
    .payment-btn { padding: 11px !important; font-size: 12px !important; }

    /* Lists: reduce gap */
    .card > div[style*="grid-template-columns"] {
      gap: 8px !important;
      padding: 12px !important;
    }

    /* Touch-friendly buttons */
    .btn, .topbar-action, .checkout-btn {
      min-height: 42px !important;
    }

    /* Quick actions grid: 2x2 on small screens */
    .quick-grid { grid-template-columns: 1fr 1fr !important; }
    .quick { padding: 14px 8px !important; }
    .quick-label { font-size: 11px !important; }

    /* Flash messages */
    .flash { font-size: 12px !important; padding: 10px 12px !important; }

    /* Alert rows */
    .alert-row { padding: 12px 14px !important; }
    .alert-title { font-size: 13px !important; }
    .alert-sub { font-size: 11px !important; }

    /* Hide less critical text on phone */
    .hide-mobile { display: none !important; }

    /* Sidebar width on mobile */
    .sidebar { width: 260px !important; }

    /* Modals: full width */
    .modal-content { padding: 20px !important; border-radius: 16px 16px 0 0 !important; }

    /* EFD badges */
    .mini-grid a .mini { border-width: 2px !important; }
  }

  @media (max-width: 380px) {
    .mini-grid { grid-template-columns: 1fr !important; }
    .quick-grid { grid-template-columns: 1fr 1fr !important; }
    .hero-value { font-size: 24px !important; }
  }

  /* Branch pill in topbar */
  .branch-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    background: rgba(0,180,216,.12);
    color: var(--cyan);
    border: 1px solid rgba(0,180,216,.25);
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    max-width: 220px;
    margin-left: 8px;
    transition: background .15s;
  }
  .branch-pill:hover { background: rgba(0,180,216,.2); }
  .branch-pill .branch-pill-text {
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 140px;
  }
  @media (max-width: 640px) {
    .branch-pill { padding: 6px 8px; margin-left: 4px; }
    .branch-pill .branch-pill-text { display: none; }
  }
</style>
</head>
<body>

<div class="app-shell">
    <?= render_sidebar($user ?? []) ?>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="app-main">
        <div class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <h1 class="topbar-title"><?= e($pageTitle ?? '') ?></h1>
            <?php if (auth_role() === 'ADMIN' && (int)($_SESSION['branch_id'] ?? 0) > 0):
                $__cb = \App\Models\Branch::find((int)$_SESSION['branch_id']);
                if ($__cb): ?>
            <a href="<?= url('/select-branch') ?>" class="branch-pill" title="Switch branch">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                <span class="branch-pill-text"><?= e($__cb['branch_name']) ?></span>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </a>
            <?php endif; endif; ?>
            <div class="topbar-spacer"></div>
            <?php if (isset($topbarAction)): ?>
                <?= $topbarAction ?>
            <?php endif; ?>
        </div>

        <?php $flash = \App\Core\Flash::all(); if (!empty($flash)): ?>
            <div class="flash-container">
                <?php foreach ($flash as $f): ?>
                    <div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</div>

<script>
  const toggle = document.getElementById('menuToggle');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (toggle) {
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('show');
    });
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });
  }
</script>
</body>
</html>
<?php
function render_sidebar(array $user): string {
    $role = $user['role'] ?? 'CASHIER';
    $name = $user['full_name'] ?? 'User';
    $branchId = (int)($user['branch_id'] ?? 0);
    $businessName = (string) (\App\Core\Database::scalar("SELECT business_name FROM app_settings WHERE id=1") ?: 'Kodafirca POS');
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $isActive = fn(string $p) => $path === $p || str_starts_with($path, $p . '/') ? 'active' : '';

    $items = [
        ['/dashboard', 'Dashboard', '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
        ['/pos',       'Point of Sale', '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>'],
        ['/products',  'Stock', '<path d="M20 2L4 2c-1.1 0-2 .9-2 2v3c0 .7.4 1.3 1 1.6V21c0 .6.4 1 1 1h16c.6 0 1-.4 1-1V8.6c.6-.3 1-.9 1-1.6V4c0-1.1-.9-2-2-2zM13 18h-2v-4H7l5-5 5 5h-4v4z"/>'],
        ['/purchases', 'Purchases', '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
        ['/sales',     'Sales History', '<path d="M6 2c-1.1 0-2 .9-2 2v3c0 .7.4 1.3 1 1.6V21c0 .6.4 1 1 1h16c.6 0 1-.4 1-1V8.6c.6-.3 1-.9 1-1.6V4c0-1.1-.9-2-2-2H6z"/>'],
    ];
    $items2 = [
        ['/expenses',  'Expenses', '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>'],
        ['/employees', 'Staff', '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
    ];
    $items3 = [
        ['/reports',   'Reports', '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>'],
        ['/tax',       'Tax Settings', '<path d="M12 2L2 22h20z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'],
    ];
    $items4 = [
        ['/profile',   'My Profile', '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
        ['/settings',  'Settings', '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
    ];

    $out = '<aside class="sidebar">';
    $out .= '<div class="sidebar-head">';
    $out .= '<img src="' . url('/assets/img/logo-mark.svg') . '" class="sidebar-logo" alt="K">';
    $out .= '<div><div class="sidebar-brand">Kodafirca <span>POS</span></div>';
    $out .= '<div class="sidebar-business">' . e($businessName) . '</div></div>';
    $out .= '</div>';

    $out .= '<div class="sidebar-user">';
    $out .= '<div class="sidebar-user-name">' . e($name) . '</div>';
    $out .= '<div class="sidebar-user-role">' . e($role) . '</div>';
    $out .= '</div>';

    $out .= '<nav class="sidebar-nav">';
    foreach ($items as [$p, $label, $svg]) {
        $out .= '<a href="' . url($p) . '" class="nav-item ' . $isActive($p) . '">';
        $out .= '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $svg . '</svg>';
        $out .= '<span>' . e($label) . '</span></a>';
    }
    $out .= '<div class="nav-section">OPERATIONS</div>';
    foreach ($items2 as [$p, $label, $svg]) {
        $out .= '<a href="' . url($p) . '" class="nav-item ' . $isActive($p) . '">';
        $out .= '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $svg . '</svg>';
        $out .= '<span>' . e($label) . '</span></a>';
    }
    $out .= '<div class="nav-section">INSIGHTS</div>';
    foreach ($items3 as [$p, $label, $svg]) {
        $out .= '<a href="' . url($p) . '" class="nav-item ' . $isActive($p) . '">';
        $out .= '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $svg . '</svg>';
        $out .= '<span>' . e($label) . '</span></a>';
    }
    $out .= '<div class="nav-section">MANAGE</div>';
    foreach ($items4 as [$p, $label, $svg]) {
        $out .= '<a href="' . url($p) . '" class="nav-item ' . $isActive($p) . '">';
        $out .= '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $svg . '</svg>';
        $out .= '<span>' . e($label) . '</span></a>';
    }

    // Logout form
    $out .= '<form method="post" action="' . url('/logout') . '" style="padding: 12px 0; margin-top: 12px;">';
    $out .= csrf_field();
    $out .= '<button type="submit" class="nav-item" style="background:none;border:none;border-left:3px solid transparent;width:100%;text-align:left;cursor:pointer;color:var(--red)">';
    $out .= '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>';
    $out .= '<span>Logout</span></button></form>';

    $out .= '</nav>';
    $out .= '</aside>';
    return $out;
}
?>

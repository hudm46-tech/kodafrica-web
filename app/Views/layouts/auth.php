<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Kodafirca POS') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root { --bg:#0A1929; --card:#112240; --cyan:#00B4D8; --text:#fff; --muted:#8892B0; --faint:#5A6784; --err:#FF1744; --ok:#00C853; --orange:#FF9100; --divider: rgba(255,255,255,.08); }
  * { box-sizing: border-box; }
  html, body {
    margin:0; padding:0;
    font-family: 'Inter', system-ui, sans-serif;
    background:#0A1929; color:#fff; min-height:100vh;
    -webkit-font-smoothing: antialiased;
  }
  .auth-page {
    min-height:100vh;
    display: flex; align-items: center; justify-content: center;
    padding: 24px 16px;
    background: #0A1929;
  }
  .auth-card {
    width:100%; max-width: 440px;
    background: var(--card);
    border-radius: 20px;
    padding: 36px 28px;
    box-shadow: 0 20px 60px -20px rgba(0,180,216,.25);
  }
  .auth-head { text-align:center; margin-bottom: 28px; }
  .auth-logo {
    width: 56px; height: 56px; margin: 0 auto 12px;
  }
  .auth-logo img { width:100%; height:100%; }
  .auth-title {
    font-size: 26px; font-weight: 800; letter-spacing: -.5px; margin: 0;
  }
  .auth-title span {
    background: linear-gradient(135deg, #00B4D8, #90E0EF);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .auth-sub { color: var(--muted); font-size: 13px; margin-top: 6px; }
  .field { margin-bottom: 16px; }
  .field label {
    display: block; font-size: 12px; color: var(--muted);
    font-weight: 600; letter-spacing: .08em; text-transform: uppercase;
    margin-bottom: 6px;
  }
  .field input, .field select {
    width:100%; padding: 12px 14px;
    background: #0A1929; color: #fff;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 12px;
    font-size: 14px; font-family: inherit;
    transition: border-color .15s;
  }
  .field input:focus, .field select:focus {
    outline: none;
    border-color: var(--cyan);
    box-shadow: 0 0 0 3px rgba(0,180,216,.15);
  }
  .field input::placeholder { color: #5A6784; }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .btn {
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    width: 100%; padding: 15px 24px;
    border: none; border-radius: 12px;
    font-family: inherit; font-weight: 700; font-size: 15px;
    cursor: pointer; transition: transform .1s, box-shadow .15s;
    text-decoration: none;
  }
  .btn-primary {
    background: linear-gradient(135deg, #00B4D8 0%, #0077B6 100%);
    color: #fff;
    box-shadow: 0 10px 24px -8px rgba(0,180,216,.55);
    margin-top: 8px;
  }
  .btn-primary:hover { transform: translateY(-1px); }
  .btn-primary:disabled { opacity:.6; cursor: not-allowed; }
  .btn-green { background: linear-gradient(135deg, #00C853, #00897B); }
  .link-row { text-align:center; margin-top: 16px; font-size: 13px; }
  .link-row a { color: var(--cyan); text-decoration: none; font-weight: 600; }
  .link-row a:hover { text-decoration: underline; }
  .flash {
    padding: 12px 14px; border-radius: 12px; font-size: 13px;
    margin-bottom: 16px; display: flex; gap: 10px; align-items: flex-start;
  }
  .flash.error   { background: rgba(255,23,68,.15); color: #FF8A9E; border: 1px solid rgba(255,23,68,.3); }
  .flash.success { background: rgba(0,200,83,.15); color: #66E89F; border: 1px solid rgba(0,200,83,.3); }
  .flash.info    { background: rgba(0,180,216,.15); color: #6ED7F5; border: 1px solid rgba(0,180,216,.3); }
  .flash.warning { background: rgba(255,145,0,.15); color: #FFB061; border: 1px solid rgba(255,145,0,.3); }
</style>
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-head">
            <div class="auth-logo">
                <img src="<?= url('/assets/img/logo-mark.svg') ?>" alt="Kodafirca">
            </div>
            <?= $content ?>
        </div>
    </div>
</div>
</body>
</html>

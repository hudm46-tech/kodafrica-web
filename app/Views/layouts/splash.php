<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0A1929">
<title><?= e($pageTitle ?? 'Kodafirca POS') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0A1929;
    --cyan: #00B4D8;
    --text: #FFFFFF;
    --muted: #8892B0;
    --faint: #5A6784;
  }
  * { box-sizing: border-box; }
  html, body {
    margin: 0;
    padding: 0;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: var(--bg);
    color: var(--text);
    -webkit-font-smoothing: antialiased;
  }
  .splash-page {
    min-height: 100vh;
    min-height: 100dvh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: #0A1929;
  }
  .splash-card {
    text-align: center;
    max-width: 420px;
    width: 100%;
  }
  .splash-logo {
    width: 140px;
    height: 140px;
    margin: 0 auto 28px;
    animation: float 4s ease-in-out infinite;
  }
  .splash-logo img {
    width: 100%;
    height: 100%;
    display: block;
    filter: drop-shadow(0 8px 24px rgba(0,180,216,.4));
  }
  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-8px); }
  }
  .splash-brand {
    font-size: 40px;
    font-weight: 800;
    letter-spacing: -1.2px;
    color: #fff;
    margin: 0;
    line-height: 1.1;
  }
  .splash-brand span {
    background: linear-gradient(135deg, #00B4D8 0%, #90E0EF 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .splash-tagline {
    margin-top: 8px;
    font-size: 12px;
    color: var(--muted);
    font-weight: 600;
    letter-spacing: .22em;
    text-transform: uppercase;
  }
  .splash-divider {
    width: 48px;
    height: 3px;
    background: linear-gradient(90deg, transparent, #00B4D8, transparent);
    border-radius: 2px;
    margin: 28px auto;
  }
  .splash-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 12px;
  }
  .splash-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 16px 24px;
    border-radius: 14px;
    font-family: inherit;
    font-weight: 700;
    font-size: 15px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease;
  }
  .splash-btn.primary {
    background: linear-gradient(135deg, #00B4D8 0%, #0077B6 100%);
    color: #fff;
    box-shadow: 0 10px 28px -8px rgba(0,180,216,.6);
  }
  .splash-btn.primary:hover { transform: translateY(-2px); box-shadow: 0 14px 32px -8px rgba(0,180,216,.75); }
  .splash-btn.ghost {
    background: transparent;
    color: #E0E7FF;
    border: 1px solid rgba(255,255,255,.15);
  }
  .splash-btn.ghost:hover { background: rgba(255,255,255,.05); }
  .splash-footer {
    position: absolute;
    bottom: 18px;
    left: 0; right: 0;
    text-align: center;
    font-size: 11px;
    color: var(--faint);
    letter-spacing: .12em;
  }
</style>
</head>
<body>
<?= $content ?>
</body>
</html>

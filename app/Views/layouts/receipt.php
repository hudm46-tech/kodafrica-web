<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Receipt') ?></title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
  :root { --bg:#E0E7FF; --paper:#FFFFFF; --text:#000; --muted:#666; --cyan:#00B4D8; }
  * { box-sizing: border-box; }
  html, body {
    margin:0; padding:0;
    font-family: 'Courier New', Courier, monospace;
    background: var(--bg);
    min-height: 100vh;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  .receipt-wrapper { max-width:420px; margin:20px auto; padding:0 12px 60px; }

  .receipt-actions { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
  .receipt-btn {
    flex:1; min-width:100px;
    padding:13px 10px;
    border-radius:10px; border:none; cursor:pointer;
    font-weight:700; font-size:13px;
    font-family: system-ui, sans-serif;
    text-decoration:none; text-align:center;
    display:inline-flex; align-items:center; justify-content:center; gap:4px;
    transition: transform .1s;
  }
  .receipt-btn:active { transform: scale(.98); }
  .receipt-btn.primary { background:linear-gradient(135deg,#00B4D8,#0077B6); color:#fff; box-shadow:0 4px 12px rgba(0,180,216,.4); }
  .receipt-btn.green   { background:linear-gradient(135deg,#00C853,#00897B); color:#fff; box-shadow:0 4px 12px rgba(0,200,83,.4); }
  .receipt-btn.ghost   { background:#fff; color:#0A1929; border:1px solid #ccc; }

  .receipt-paper {
    background:var(--paper); color:var(--text);
    padding:24px 20px; border-radius:8px;
    box-shadow:0 8px 24px rgba(0,0,0,.12);
    font-size:12px; line-height:1.5;
    max-width:380px; margin:0 auto;
  }
  .receipt-paper h1 { font-size:17px; text-align:center; margin:0 0 4px; letter-spacing:1px; font-weight:800; font-family:system-ui, sans-serif; }
  .receipt-paper .sub { text-align:center; color:var(--muted); font-size:10px; margin-bottom:14px; line-height:1.6; }
  .receipt-divider { border-top:1px dashed #999; margin:12px 0; }
  .receipt-row { display:flex; justify-content:space-between; margin-bottom:4px; font-size:11px; gap:8px; }
  .receipt-row span:first-child { color:#444; }
  .receipt-row span:last-child { font-weight:600; text-align:right; }
  .receipt-row.total { font-size:15px; font-weight:800; margin-top:10px; border-top:2px solid #000; padding-top:8px; }
  .receipt-item { margin-bottom:10px; }
  .receipt-item .name { font-weight:700; font-size:12px; }
  .receipt-item .line { display:flex; justify-content:space-between; color:#444; font-size:11px; }
  .receipt-footer { text-align:center; font-size:10px; color:var(--muted); margin-top:16px; }

  /* Mobile */
  @media (max-width: 480px) {
    .receipt-wrapper { padding:0 8px 50px; }
    .receipt-paper { padding:18px 14px; }
    .receipt-paper h1 { font-size:15px; }
    .receipt-row { font-size:10.5px; }
    .receipt-row.total { font-size:13px; }
  }

  /* Print */
  @media print {
    @page { size:auto; margin:8mm; }
    html, body { background:#fff !important; }
    .receipt-wrapper { margin:0; padding:0; max-width:100%; }
    .receipt-actions { display:none !important; }
    .receipt-paper {
      box-shadow:none !important;
      border-radius:0 !important;
      padding:0 !important;
      max-width:100% !important;
      font-size:11px;
    }
    .receipt-divider { border-top:1px dashed #666; }
    .receipt-paper h1 { font-size:15px; }
    .receipt-row.total { font-size:14px; }
  }
</style>
</head>
<body>
<div class="receipt-wrapper">
    <?= $content ?>
</div>
<script>
function downloadPDF(filename) {
    if (typeof html2pdf === 'undefined') {
        alert('PDF library not loaded. Please check your internet connection.');
        return;
    }

    const element = document.querySelector('.receipt-paper');
    if (!element) return;

    const btn = event.target.closest('button');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Generating...';
    }

    html2pdf().from(element).set({
        margin: [8, 8, 8, 8],
        filename: filename || 'receipt.pdf',
        image: { type: 'jpeg', quality: 0.95 },
        html2canvas: { scale: 2, backgroundColor: '#ffffff', useCORS: true, logging: false },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).save().then(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }).catch(err => {
        alert('PDF error: ' + err.message);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
}
</script>
</body>
</html>

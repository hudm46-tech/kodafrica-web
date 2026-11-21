<?php
$isEdit = $expense !== null;
$action = $isEdit ? url('/expenses/' . $expense['expense_id']) : url('/expenses');
$currentCategory = $isEdit ? $expense['category'] : old('category');
$currentDesc     = $isEdit ? $expense['description'] : old('description');
$currentAmount   = $isEdit ? $expense['amount'] : old('amount');
$currentDate     = $isEdit ? date('Y-m-d', strtotime($expense['expense_date'])) : old('expense_date', date('Y-m-d'));
$currentNotes    = $isEdit ? $expense['notes'] : old('notes');
$currentReceipt  = $isEdit ? ($expense['receipt_image_path'] ?? null) : null;
$currentHasEfd   = $isEdit ? (int)($expense['has_efd_receipt'] ?? 0) : (int)old('has_efd_receipt', 0);
$currentEfdNum   = $isEdit ? ($expense['efd_receipt_number'] ?? '') : old('efd_receipt_number', '');
?>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="card">
    <?= csrf_field() ?>

    <div style="margin-bottom:18px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Category</label>
        <select name="category" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
            <option value="">— Select category —</option>
            <?php foreach ($categories as $group => $items): ?>
                <optgroup label="<?= e(ucfirst(strtolower($group))) ?>">
                    <?php foreach ($items as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $currentCategory === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom:18px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Description</label>
        <input type="text" name="description" value="<?= e($currentDesc) ?>" placeholder="e.g., TANESCO prepaid units for November" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Amount (TZS)</label>
            <input type="number" name="amount" value="<?= e($currentAmount) ?>" placeholder="0" min="1" step="1" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Date</label>
            <input type="date" name="expense_date" value="<?= e($currentDate) ?>" required style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px">
        </div>
    </div>

    <!-- ===== Receipt (File / Camera) ===== -->
    <div style="margin-bottom:18px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Receipt (optional)</label>

        <?php if ($currentReceipt): ?>
            <?php $isImg = preg_match('/\.(jpe?g|png|webp)$/i', $currentReceipt); ?>
            <div style="margin-bottom:10px;padding:12px;background:#0A1929;border:1px solid rgba(255,255,255,.08);border-radius:12px;display:flex;align-items:center;gap:12px">
                <?php if ($isImg): ?>
                    <img src="<?= url('/uploads/' . $currentReceipt) ?>" alt="Receipt" style="width:48px;height:48px;object-fit:cover;border-radius:8px">
                <?php else: ?>
                    <div style="width:48px;height:48px;border-radius:8px;background:rgba(255,23,68,.15);color:var(--red);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                <?php endif; ?>
                <div style="flex:1;min-width:0">
                    <div style="font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e(basename($currentReceipt)) ?></div>
                    <div style="font-size:10px;color:var(--muted);margin-top:2px">Attached</div>
                </div>
                <a href="<?= url('/uploads/' . $currentReceipt) ?>" target="_blank" style="font-size:11px;color:var(--cyan);font-weight:600">View</a>
            </div>
        <?php endif; ?>

        <!-- Two action buttons -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <label for="fileInput" style="display:flex;align-items:center;gap:10px;padding:14px;border:2px dashed rgba(0,180,216,.4);border-radius:12px;cursor:pointer;background:rgba(0,180,216,.03)">
                <div style="width:34px;height:34px;border-radius:8px;background:rgba(0,180,216,.15);color:var(--cyan);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div style="min-width:0">
                    <div style="font-size:12px;font-weight:700">Choose File</div>
                    <div style="font-size:10px;color:var(--muted);margin-top:1px">JPG · PNG · PDF</div>
                </div>
            </label>
            <input id="fileInput" type="file" name="receipt" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none" onchange="onFileChosen(this)">

            <button type="button" onclick="openCamera()" style="display:flex;align-items:center;gap:10px;padding:14px;border:2px dashed rgba(0,200,83,.4);border-radius:12px;cursor:pointer;background:rgba(0,200,83,.03);text-align:left;font-family:inherit">
                <div style="width:34px;height:34px;border-radius:8px;background:rgba(0,200,83,.15);color:var(--green);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                </div>
                <div style="min-width:0">
                    <div style="font-size:12px;font-weight:700;color:var(--text)">Take Photo</div>
                    <div style="font-size:10px;color:var(--muted);margin-top:1px">Auto → PDF</div>
                </div>
            </button>
        </div>

        <!-- Preview -->
        <div id="receiptPreview" style="margin-top:12px"></div>
    </div>


    <!-- EFD Receipt Toggle -->
    <div style="margin-bottom:18px;padding:14px;background:#0A1929;border:1px solid rgba(0,200,83,.25);border-radius:12px">
        <label style="display:flex;align-items:center;gap:12px;cursor:pointer">
            <input type="checkbox" name="has_efd_receipt" value="1" id="efdCheckbox" <?= $currentHasEfd ? 'checked' : '' ?> onchange="toggleEfdField()" style="width:20px;height:20px;accent-color:var(--green);cursor:pointer">
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--text)">Has EFD Receipt</div>
                <div style="font-size:11px;color:var(--muted)">Fiscal receipt from TRA-approved device</div>
            </div>
        </label>

        <div id="efdNumberField" style="margin-top:12px;display:<?= $currentHasEfd ? 'block' : 'none' ?>">
            <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:6px">EFD Receipt Number (optional)</label>
            <input type="text" name="efd_receipt_number" value="<?= e($currentEfdNum) ?>" placeholder="e.g., 001-2345678-2025" style="width:100%;padding:11px 14px;background:#112240;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fff;font-family:inherit;font-size:13px">
        </div>
    </div>

    <div style="margin-bottom:22px">
        <label style="display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.12em;font-weight:700;margin-bottom:8px">Notes (optional)</label>
        <textarea name="notes" rows="3" placeholder="Additional details…" style="width:100%;padding:12px 14px;background:#0A1929;border:1px solid rgba(255,255,255,.1);border-radius:12px;color:#fff;font-family:inherit;font-size:14px;resize:vertical"><?= e($currentNotes) ?></textarea>
    </div>

    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?= $isEdit ? 'Update Expense' : 'Save Expense' ?>
        </button>
        <a href="<?= url('/expenses') ?>" class="btn" style="background:rgba(255,255,255,.05);color:var(--text);text-decoration:none;padding:15px 24px;flex:0">Cancel</a>
    </div>
</form>

<!-- Hidden camera form (separate, submitted via JS after capture) -->
<input id="cameraInput" type="file" name="camera_image" accept="image/*" capture="environment" style="display:none" onchange="onCameraChosen(this)">

<!-- Modal for previewing captured photo before attaching -->
<div id="cameraModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:999;align-items:center;justify-content:center;padding:16px">
    <div style="background:var(--card);border-radius:16px;max-width:560px;width:100%;padding:20px">
        <h3 style="margin:0 0 12px;font-size:17px;font-weight:800">Review Photo</h3>
        <img id="cameraPreviewImg" style="width:100%;border-radius:10px;max-height:60vh;object-fit:contain;background:#000">
        <p style="font-size:12px;color:var(--muted);margin:12px 0 16px">This photo will be converted to PDF and attached as the receipt.</p>
        <div style="display:flex;gap:10px">
            <button type="button" onclick="retakePhoto()" class="btn" style="flex:1;background:rgba(255,255,255,.05);color:var(--text)">Retake</button>
            <button type="button" onclick="attachPhoto()" class="btn btn-primary" style="flex:2">Attach as PDF</button>
        </div>
    </div>
</div>

<script>
let pendingCameraFile = null;

function onFileChosen(input) {
    if (!input.files || !input.files[0]) return;
    // Clear pending camera
    pendingCameraFile = null;
    showPreview(input.files[0], false);
}

function openCamera() {
    document.getElementById('cameraInput').value = '';
    document.getElementById('cameraInput').click();
}

function onCameraChosen(input) {
    if (!input.files || !input.files[0]) return;
    pendingCameraFile = input.files[0];

    const modal = document.getElementById('cameraModal');
    const img = document.getElementById('cameraPreviewImg');
    img.src = URL.createObjectURL(pendingCameraFile);
    modal.style.display = 'flex';
}

function retakePhoto() {
    document.getElementById('cameraModal').style.display = 'none';
    pendingCameraFile = null;
    openCamera();
}

function attachPhoto() {
    const modal = document.getElementById('cameraModal');
    if (!pendingCameraFile) {
        modal.style.display = 'none';
        return;
    }

    // Move the file from cameraInput into the main form as hidden input
    // We'll build a DataTransfer to carry the file into the 'receipt' field,
    // OR keep it in cameraInput and let server prefer camera_image.
    // Server prefers camera_image → so we keep cameraInput intact.

    // But the cameraInput sits OUTSIDE the <form>. We need to move it IN.
    const form = document.querySelector('form');
    const camInput = document.getElementById('cameraInput');
    // Move into form
    form.appendChild(camInput);

    // Ensure regular file input is cleared (camera takes priority)
    document.getElementById('fileInput').value = '';

    // Show preview
    showPreview(pendingCameraFile, true);
    modal.style.display = 'none';
}

function showPreview(file, isCamera) {
    const preview = document.getElementById('receiptPreview');
    preview.innerHTML = '';

    const wrap = document.createElement('div');
    wrap.style.cssText = 'padding:12px;background:#0A1929;border:1px solid rgba(0,255,120,.25);border-radius:12px;display:flex;align-items:center;gap:12px';

    if (file.type.startsWith('image/')) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.cssText = 'width:56px;height:56px;object-fit:cover;border-radius:8px;flex-shrink:0';
        wrap.appendChild(img);
    } else {
        const icon = document.createElement('div');
        icon.style.cssText = 'width:56px;height:56px;border-radius:8px;background:rgba(255,23,68,.15);color:var(--red);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:22px';
        icon.textContent = '📄';
        wrap.appendChild(icon);
    }

    const info = document.createElement('div');
    info.style.cssText = 'flex:1;min-width:0';
    const name = document.createElement('div');
    name.style.cssText = 'font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap';
    name.textContent = file.name || 'camera.jpg';
    const meta = document.createElement('div');
    meta.style.cssText = 'font-size:10px;color:var(--muted);margin-top:2px';
    meta.textContent = (isCamera ? '📷 Camera · ' : '📁 File · ') + Math.round(file.size/1024) + ' KB' + (isCamera ? ' (will be converted to PDF)' : '');
    info.appendChild(name);
    info.appendChild(meta);
    wrap.appendChild(info);

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.textContent = '✕';
    remove.style.cssText = 'width:28px;height:28px;border-radius:8px;background:rgba(255,23,68,.15);color:var(--red);border:none;cursor:pointer;flex-shrink:0';
    remove.onclick = () => {
        preview.innerHTML = '';
        document.getElementById('fileInput').value = '';
        document.getElementById('cameraInput').value = '';
        pendingCameraFile = null;
    };
    wrap.appendChild(remove);

    preview.appendChild(wrap);
}

function toggleEfdField() {
    const checked = document.getElementById('efdCheckbox').checked;
    document.getElementById('efdNumberField').style.display = checked ? 'block' : 'none';
}
</script>

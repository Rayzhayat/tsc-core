<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title mb-0">
                            <i class="fas fa-robot text-primary me-2"></i> Prediksi Margin AI
                        </h1>
                        <small class="text-muted">Estimasi margin menggunakan model XGBoost yang ditraining dari data historis TSC</small>
                    </div>
                    <a href="<?= base_url('analytics') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-chart-bar me-1"></i> Analytics Dashboard
                    </a>
                </div>

                <!-- Model Status Cards -->
                <div class="row g-2 mb-4">
                    <?php foreach ($models as $m): ?>
                        <?php
                            $eval = $m['eval'];
                            $ok   = $m['available'];
                        ?>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card shadow-sm h-100 <?= $ok ? '' : 'opacity-50' ?>"
                                 style="border-left: 4px solid <?= $ok ? '#1cc88a' : '#adb5bd' ?>; cursor: <?= $ok ? 'pointer' : 'default' ?>"
                                 onclick="<?= $ok ? "selectSheet('{$m['sheet_type']}')" : '' ?>">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="small fw-bold text-truncate" style="max-width:100px">
                                            <?= str_replace('_', ' ', $m['sheet_type']) ?>
                                        </span>
                                        <?php if ($ok): ?>
                                            <i class="fas fa-check-circle text-success small"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle text-muted small"></i>
                                        <?php endif ?>
                                    </div>
                                    <?php if ($ok && $eval): ?>
                                        <div class="text-xs text-muted">R² <strong class="text-success"><?= $eval['r2'] ?></strong></div>
                                        <div class="text-xs text-muted">MAE Rp <?= number_format($eval['mae'], 0, ',', '.') ?></div>
                                    <?php else: ?>
                                        <div class="text-xs text-muted">Model belum ada</div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>

                <div class="row g-4">
                    <!-- Form Input -->
                    <div class="col-lg-5">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white py-3">
                                <h6 class="m-0 fw-bold"><i class="fas fa-sliders-h me-2"></i> Input Data Trip</h6>
                            </div>
                            <div class="card-body">

                                <!-- Pilih Sheet -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Sheet Type <span class="text-danger">*</span></label>
                                    <select id="sheetTypeSelect" class="form-select" onchange="loadFields(this.value)">
                                        <?php foreach ($models as $m): ?>
                                            <?php if ($m['available']): ?>
                                                <option value="<?= $m['sheet_type'] ?>"
                                                    <?= $selected_sheet == $m['sheet_type'] ? 'selected' : '' ?>>
                                                    <?= str_replace('_', ' ', $m['sheet_type']) ?>
                                                </option>
                                            <?php endif ?>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <!-- Dynamic Fields -->
                                <div id="dynamicFields">
                                    <?php if ($metadata): ?>
                                        <?php $this->load->view('prediction/_fields', ['metadata' => $metadata]) ?>
                                    <?php endif ?>
                                </div>

                                <!-- Tombol Prediksi -->
                                <button class="btn btn-primary w-100 mt-3" id="btnPredict" onclick="runPrediction()">
                                    <i class="fas fa-magic me-2"></i> Prediksi Margin
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- Hasil Prediksi -->
                    <div class="col-lg-7">

                        <!-- Placeholder -->
                        <div id="resultPlaceholder" class="card shadow h-100 d-flex align-items-center justify-content-center" style="min-height:300px">
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-robot fa-4x mb-3 d-block opacity-25"></i>
                                <p class="mb-1">Isi form di sebelah kiri</p>
                                <p class="small">Model AI akan memperkirakan margin berdasarkan data historis TSC</p>
                            </div>
                        </div>

                        <!-- Hasil -->
                        <div id="resultCard" class="d-none">

                            <!-- Angka Prediksi -->
                            <div class="card shadow mb-3" style="border-left: 5px solid #1cc88a">
                                <div class="card-body py-3 px-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <div class="text-muted small text-uppercase fw-semibold mb-1">Prediksi Margin</div>
                                            <div id="resultMargin" class="display-6 fw-bold text-success">Rp 0</div>
                                            <div class="text-muted small mt-1">
                                                Margin % dari revenue:
                                                <strong id="resultMarginPct" class="text-primary">—</strong>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span id="resultBadge" class="badge fs-6 px-3 py-2">POSITIF</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Akurasi Model -->
                            <div class="card shadow mb-3">
                                <div class="card-header py-2">
                                    <h6 class="m-0 fw-bold text-muted small"><i class="fas fa-chart-line me-2"></i> Akurasi Model</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 text-center">
                                        <div class="col-4">
                                            <div class="small text-muted">R² Score</div>
                                            <div class="fw-bold text-success fs-5" id="evalR2">—</div>
                                            <div class="text-xs text-muted">Semakin dekat 1 = makin akurat</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="small text-muted">MAE</div>
                                            <div class="fw-bold text-warning fs-6" id="evalMAE">—</div>
                                            <div class="text-xs text-muted">Rata-rata selisih prediksi</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="small text-muted">MAPE</div>
                                            <div class="fw-bold text-info fs-5" id="evalMAPE">—</div>
                                            <div class="text-xs text-muted">Error dalam persen</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Interpretasi -->
                            <div class="card shadow">
                                <div class="card-header py-2">
                                    <h6 class="m-0 fw-bold text-muted small"><i class="fas fa-lightbulb me-2 text-warning"></i> Interpretasi</h6>
                                </div>
                                <div class="card-body">
                                    <div id="resultInterpretation" class="small"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
// ── Load fields via AJAX saat sheet berganti ──
function loadFields(sheetType) {
    const container = document.getElementById('dynamicFields');
    container.innerHTML = '<div class="text-center py-3"><span class="spinner-border spinner-border-sm"></span> Loading fields...</div>';

    fetch(`<?= base_url('prediction/get_fields') ?>?sheet_type=${encodeURIComponent(sheetType)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                container.innerHTML = `<div class="alert alert-warning small">${data.message}</div>`;
                return;
            }
            renderFields(data.metadata);
        })
        .catch(e => {
            container.innerHTML = `<div class="alert alert-danger small">Gagal load fields: ${e.message}</div>`;
        });
}

// ── Render form fields dari metadata ──
function renderFields(meta) {
    const container = document.getElementById('dynamicFields');
    let html = '';

    meta.features.forEach(feat => {
        const isCat = meta.categorical.includes(feat);
        const label = feat.replace(/_/g, ' ');

        if (isCat) {
            const classes = (meta.label_encoders[feat]?.classes || []).filter(c => c !== 'UNKNOWN');
            html += `
            <div class="mb-2">
                <label class="form-label small fw-semibold mb-1">${label}</label>
                <select name="${feat}" class="form-select form-select-sm">
                    <option value="">— Pilih —</option>
                    ${classes.map(c => `<option value="${c}">${c}</option>`).join('')}
                </select>
            </div>`;
        } else {
            const median = meta.num_medians?.[feat] ?? 0;
            html += `
            <div class="mb-2">
                <label class="form-label small fw-semibold mb-1">${label}</label>
                <input type="number" name="${feat}" class="form-control form-control-sm"
                       placeholder="Default: ${Number(median).toLocaleString('id-ID')}" step="any">
            </div>`;
        }
    });

    container.innerHTML = html;
}

// ── Jalankan prediksi ──
function runPrediction() {
    const sheet_type = document.getElementById('sheetTypeSelect').value;
    const btn        = document.getElementById('btnPredict');

    // Kumpulkan semua input
    const inputs  = document.querySelectorAll('#dynamicFields input, #dynamicFields select');
    const payload = new FormData();
    payload.append('sheet_type', sheet_type);
    inputs.forEach(inp => {
        if (inp.name) payload.append(inp.name, inp.value);
    });

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';

    fetch('<?= base_url('prediction/predict') ?>', {
        method: 'POST',
        body: payload,
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic me-2"></i> Prediksi Margin';

        if (!data.success) {
            alert('Error: ' + data.message);
            return;
        }

        showResult(data, payload);
    })
    .catch(e => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic me-2"></i> Prediksi Margin';
        alert('Gagal koneksi ke server: ' + e.message);
    });
}

// ── Tampilkan hasil ──
function showResult(data, payload) {
    document.getElementById('resultPlaceholder').classList.add('d-none');
    document.getElementById('resultCard').classList.remove('d-none');

    const margin  = data.predicted;
    const isPos   = margin >= 0;
    const fmtRp   = v => 'Rp ' + Math.abs(v).toLocaleString('id-ID');

    document.getElementById('resultMargin').textContent  = (isPos ? '' : '-') + fmtRp(margin);
    document.getElementById('resultMargin').className    = 'display-6 fw-bold ' + (isPos ? 'text-success' : 'text-danger');

    // Hitung margin % kalau ada rate user
    const rateInput = document.querySelector('#dynamicFields input[name="Rate User-TSC"], #dynamicFields input[name="Trip Cost from User"]');
    if (rateInput && parseFloat(rateInput.value) > 0) {
        const pct = (margin / parseFloat(rateInput.value) * 100).toFixed(1);
        document.getElementById('resultMarginPct').textContent = pct + '%';
    } else {
        document.getElementById('resultMarginPct').textContent = '—';
    }

    // Badge
    const badge = document.getElementById('resultBadge');
    badge.textContent  = isPos ? '✅ POSITIF' : '❌ NEGATIF';
    badge.className    = 'badge fs-6 px-3 py-2 ' + (isPos ? 'bg-success' : 'bg-danger');

    // Eval metrics
    document.getElementById('evalR2').textContent   = data.r2 ?? '—';
    document.getElementById('evalMAE').textContent  = data.mae ? fmtRp(data.mae) : '—';
    document.getElementById('evalMAPE').textContent = data.mape ? data.mape + '%' : '—';

    // Interpretasi
    let interp = '';
    if (isPos) {
        if (margin > 500000) {
            interp = '🟢 <strong>Margin bagus.</strong> Trip ini diperkirakan menguntungkan secara signifikan.';
        } else {
            interp = '🟡 <strong>Margin tipis.</strong> Trip ini untung tapi selisihnya kecil, perhatikan biaya tambahan.';
        }
    } else {
        interp = '🔴 <strong>Margin negatif.</strong> Trip ini diperkirakan rugi. Pertimbangkan untuk negosiasi rate atau cari vendor yang lebih murah.';
    }
    interp += `<br><br><span class="text-muted">Model ditraining dari data historis TSC. Prediksi ini bersifat estimasi dengan error rata-rata ±Rp ${data.mae ? Number(data.mae).toLocaleString('id-ID') : '?'}.</span>`;
    document.getElementById('resultInterpretation').innerHTML = interp;
}

// ── Select sheet dari card klik ──
function selectSheet(sheet) {
    document.getElementById('sheetTypeSelect').value = sheet;
    loadFields(sheet);
}
</script>
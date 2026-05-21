<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="page-title mb-0"><i class="fas fa-file-import text-primary"></i> Import Data CSV</h1>
                        <small class="text-muted">Upload file CSV dari Google Sheets untuk update data analytics</small>
                    </div>
                    <a href="<?= base_url('analytics') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-chart-bar me-1"></i> Lihat Dashboard
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i> <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif ?>

                <?php if ($this->session->flashdata('import_errors')): ?>
                    <?php $import_errors = $this->session->flashdata('import_errors'); ?>
                    <div class="alert alert-warning alert-dismissible fade show mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong><i class="fas fa-exclamation-triangle me-2"></i>
                                <?= count($import_errors) ?> baris gagal diimport — detail di bawah</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <div class="table-responsive" style="max-height:300px;overflow-y:auto">
                            <table class="table table-sm table-bordered mb-0 bg-white">
                                <thead class="table-warning sticky-top">
                                    <tr>
                                        <th style="width:60px">Baris CSV</th>
                                        <th>Periode</th>
                                        <th>Origin</th>
                                        <th>Dest</th>
                                        <th>Alasan Gagal</th>
                                        <th>Data Mentah (8 kolom pertama)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($import_errors as $err): ?>
                                        <tr>
                                            <td class="text-center fw-bold text-danger"><?= $err['row'] ?></td>
                                            <td><?= htmlspecialchars($err['periode']) ?></td>
                                            <td><?= htmlspecialchars($err['origin']) ?></td>
                                            <td><?= htmlspecialchars($err['dest']) ?></td>
                                            <td class="text-danger small"><?= htmlspecialchars($err['alasan']) ?></td>
                                            <td class="small text-muted font-monospace" style="font-size:.7rem">
                                                <?= htmlspecialchars($err['raw']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif ?>

                <div class="row g-4">
                    <!-- Form Upload -->
                    <div class="col-lg-5">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white py-3">
                                <h6 class="m-0 fw-bold"><i class="fas fa-upload me-2"></i> Upload CSV Baru</h6>
                            </div>
                            <div class="card-body">
                                <form action="<?= base_url('analytics/do_import') ?>" method="post"
                                    enctype="multipart/form-data" id="importForm">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Pilih Sheet Type <span
                                                class="text-danger">*</span></label>
                                        <select name="sheet_type" class="form-select" required id="sheetTypeSelect">
                                            <option value="">-- Pilih Sheet --</option>
                                            <!-- SPX Sheets -->
                                            <optgroup label="─── SPX ───">
                                                <option value="FTL_COC_SPX">FTL COC SPX</option>
                                                <option value="FTL_Reguler_SPX">FTL Reguler SPX</option>
                                                <option value="FTL_A1_SPX">FTL A1 SPX</option>
                                                <option value="FTL_Dedicated">FTL Dedicated</option>
                                            </optgroup>
                                            <!-- Non-SPX Sheets -->
                                            <optgroup label="─── Non SPX ───">
                                                <option value="FTL_Non_SPX">FTL Non SPX</option>
                                            </optgroup>
                                            <!-- Daily Rent -->
                                            <optgroup label="─── Daily Rent ───">
                                                <option value="Dailyrent">Daily Rent</option>
                                            </optgroup>
                                        </select>
                                        <div class="form-text text-info fw-semibold" id="sheetInfo"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">File CSV <span
                                                class="text-danger">*</span></label>
                                        <div class="upload-area border-2 border-dashed rounded-3 p-4 text-center"
                                            id="uploadArea"
                                            style="border-color: #dee2e6; cursor:pointer; transition: all .2s">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                            <p class="mb-1 text-muted">Drag & drop file CSV di sini</p>
                                            <p class="small text-muted mb-2">atau</p>
                                            <input type="file" name="csv_file" id="csvFile" accept=".csv" class="d-none"
                                                required>
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                onclick="document.getElementById('csvFile').click()">
                                                <i class="fas fa-folder-open me-1"></i> Pilih File
                                            </button>
                                            <div id="fileInfo" class="mt-2 small text-success d-none">
                                                <i class="fas fa-file-csv me-1"></i> <span id="fileName"></span>
                                            </div>
                                        </div>
                                        <div class="form-text">Format: .csv | Max: 10MB</div>
                                    </div>

                                    <div class="alert alert-warning py-2 small">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        <strong>Perhatian:</strong> Upload akan <strong>mengganti</strong> data lama
                                        untuk sheet yang dipilih. Data sheet lain tidak terpengaruh.
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100" id="importBtn">
                                        <i class="fas fa-file-import me-2"></i> Import Sekarang
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Panduan -->
                        <div class="card shadow mt-3">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-muted"><i class="fas fa-info-circle text-info me-2"></i>
                                    Cara Export dari Google Sheets</h6>
                            </div>
                            <div class="card-body">
                                <ol class="small mb-0 ps-3">
                                    <li class="mb-2">Buka spreadsheet <strong>Monitoring TSC 2026</strong></li>
                                    <li class="mb-2">Klik tab sheet yang sesuai (misal: <em>FTL COC SPX</em>)</li>
                                    <li class="mb-2">Klik menu <strong>File → Download → CSV (.csv)</strong></li>
                                    <li class="mb-2">Upload file yang sudah di-download di sini</li>
                                    <li>Pilih sheet type yang sesuai lalu klik <strong>Import</strong></li>
                                </ol>

                                <!-- Tabel mapping sheet → nama tab -->
                                <hr class="my-2">
                                <p class="small fw-semibold mb-1 text-muted">Mapping sheet type ke nama tab Google
                                    Sheets:</p>
                                <table class="table table-sm table-bordered small mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sheet Type</th>
                                            <th>Nama Tab di GSheets</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>FTL COC SPX</td>
                                            <td>FTL COC SPX</td>
                                        </tr>
                                        <tr>
                                            <td>FTL Reguler SPX</td>
                                            <td>FTL Reguler SPX</td>
                                        </tr>
                                        <tr>
                                            <td>FTL A1 SPX</td>
                                            <td>FTL A1 SPX</td>
                                        </tr>
                                        <tr>
                                            <td>FTL Dedicated</td>
                                            <td>FTL Dedicated</td>
                                        </tr>
                                        <tr>
                                            <td>FTL Non SPX</td>
                                            <td>FTL Non SPX</td>
                                        </tr>
                                        <tr>
                                            <td>Daily Rent</td>
                                            <td>Dailyrent</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Import History -->
                    <div class="col-lg-7">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold"><i class="fas fa-history text-warning me-2"></i> Riwayat Import
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($import_logs)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Waktu Import</th>
                                                    <th>Sheet</th>
                                                    <th>File</th>
                                                    <th class="text-center">Total</th>
                                                    <th class="text-center">Sukses</th>
                                                    <th class="text-center">Gagal</th>
                                                    <th>Oleh</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($import_logs as $log): ?>
                                                    <tr>
                                                        <td class="small"><?= date('d/m/Y H:i', strtotime($log->imported_at)) ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $badge_color = [
                                                                'FTL_COC_SPX' => 'primary',
                                                                'FTL_Reguler_SPX' => 'info',
                                                                'FTL_A1_SPX' => 'cyan',
                                                                'FTL_Dedicated' => 'purple',
                                                                'FTL_Non_SPX' => 'secondary',
                                                                'Dailyrent' => 'success',
                                                            ][$log->sheet_type] ?? 'dark';
                                                            ?>
                                                            <span
                                                                class="badge bg-<?= $badge_color ?>"><?= $log->sheet_type ?></span>
                                                        </td>
                                                        <td class="small text-muted text-truncate" style="max-width:150px"
                                                            title="<?= htmlspecialchars($log->filename) ?>">
                                                            <?= htmlspecialchars($log->filename) ?>
                                                        </td>
                                                        <td class="text-center"><?= number_format($log->total_rows) ?></td>
                                                        <td class="text-center"><span
                                                                class="badge bg-success"><?= number_format($log->success_rows) ?></span>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if ($log->failed_rows > 0): ?>
                                                                <span class="badge bg-danger"><?= $log->failed_rows ?></span>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-muted">0</span>
                                                            <?php endif ?>
                                                        </td>
                                                        <td class="small">
                                                            <?= htmlspecialchars($log->imported_by_nama ?? 'System') ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        <p>Belum ada riwayat import</p>
                                        <p class="small">Upload CSV pertama lo di sebelah kiri!</p>
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    const sheetInfo = {
        'FTL_COC_SPX': '📦 Tab "FTL COC SPX" — Pengiriman FTL COC ke Shopee Express. Kolom biaya: Multidrop Inner + Outer.',
        'FTL_Reguler_SPX': '🔄 Tab "FTL Reguler SPX" — Pengiriman reguler harian ke SPX. Kolom Periode = Month, Customer = Division.',
        'FTL_A1_SPX': '🅰️ Tab "FTL A1 SPX" — Pengiriman FTL A1 Shopee Express.',
        'FTL_Dedicated': '🚛 Tab "FTL Dedicated" — Unit dedicated SPX. Customer otomatis diset ke "SPX".',
        'FTL_Non_SPX': '📋 Tab "FTL Non SPX" — Pengiriman FTL ke customer selain Shopee Express.',
        'Dailyrent': '📅 Tab "Dailyrent" — Data sewa harian unit. Ada kolom Start Date & End Date.',
    };

    document.getElementById('sheetTypeSelect').addEventListener('change', function () {
        const info = sheetInfo[this.value] || '';
        document.getElementById('sheetInfo').textContent = info;
    });

    document.getElementById('csvFile').addEventListener('change', function () {
        if (this.files[0]) {
            document.getElementById('fileName').textContent = this.files[0].name;
            document.getElementById('fileInfo').classList.remove('d-none');
            document.getElementById('uploadArea').style.borderColor = '#1cc88a';
        }
    });

    // Drag & drop
    const area = document.getElementById('uploadArea');
    area.addEventListener('dragover', e => { e.preventDefault(); area.style.borderColor = '#4e73df'; area.style.background = '#f0f4ff'; });
    area.addEventListener('dragleave', () => { area.style.borderColor = '#dee2e6'; area.style.background = ''; });
    area.addEventListener('drop', e => {
        e.preventDefault();
        area.style.borderColor = '#1cc88a';
        area.style.background = '';
        const file = e.dataTransfer.files[0];
        if (file && file.name.endsWith('.csv')) {
            document.getElementById('csvFile').files = e.dataTransfer.files;
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileInfo').classList.remove('d-none');
        } else {
            alert('Hanya file CSV yang diizinkan!');
        }
    });

    document.getElementById('importForm').addEventListener('submit', function () {
        const btn = document.getElementById('importBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sedang import...';
        btn.disabled = true;
    });
</script>
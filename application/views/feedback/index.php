<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl mt-3">

                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                    <div>
                        <h1 class="page-title mb-0">
                            <i class="fas fa-search text-warning"></i> Feedback SPX — Vendor Lookup
                        </h1>
                        <small class="text-muted">
                            Tabrakan LT Number dari sheet Leadtime SPX ke masterdata Dailyrent → ketauan vendornya
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('analytics') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-chart-bar me-1"></i> Analytics
                        </a>
                        <a href="<?= base_url('feedback/reset') ?>" class="btn btn-outline-danger btn-sm"
                            onclick="return confirm('Reset semua file & data?')">
                            <i class="fas fa-trash me-1"></i> Reset
                        </a>
                    </div>
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

                <div class="row g-4">

                    <!-- STEP 1: Upload Master Data (Multiple) -->
                    <div class="col-md-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3 bg-primary text-white">
                                <h6 class="m-0 fw-bold">
                                    <span class="badge bg-white text-primary me-2">1</span>
                                    Upload Masterdata Dailyrent
                                    <span class="badge bg-warning text-dark ms-1">bisa multiple</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3">
                                    File monitoring TSC (CSV atau Excel).<br>
                                    <strong>Bisa upload lebih dari 1 file</strong> — misal Jan-Apr + May sekaligus.<br>
                                    Lookup otomatis digabung saat proses.
                                </p>

                                <!-- Daftar master data yang sudah diupload -->
                                <?php if (!empty($master_infos)): ?>
                                    <div class="mb-3">
                                        <div class="fw-semibold small text-success mb-1">
                                            <i class="fas fa-database me-1"></i>
                                            <?= count($master_infos) ?> master data aktif
                                            (<?= number_format($lookup_total) ?> LT total gabungan):
                                        </div>
                                        <?php foreach ($master_infos as $idx => $info): ?>
                                            <div
                                                class="d-flex align-items-center justify-content-between bg-light rounded px-2 py-1 mb-1">
                                                <div class="small">
                                                    <i class="fas fa-file-excel text-success me-1"></i>
                                                    <strong><?= htmlspecialchars($info['name']) ?></strong>
                                                    <span class="text-muted ms-1">(<?= number_format($info['total_lt']) ?>
                                                        LT)</span>
                                                </div>
                                                <form action="<?= base_url('feedback/remove_master') ?>" method="post"
                                                    onsubmit="return confirm('Hapus master data ini?')" class="m-0">
                                                    <input type="hidden" name="idx" value="<?= $idx ?>">
                                                    <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-1"
                                                        title="Hapus">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                <?php endif ?>

                                <form action="<?= base_url('feedback/upload_csv') ?>" method="post"
                                    enctype="multipart/form-data" id="formUploadMaster">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold mb-1">
                                            <?= empty($master_infos) ? 'Tambah master data:' : 'Tambah master data lain:' ?>
                                        </label>
                                        <input type="file" name="csv_file" class="form-control form-control-sm"
                                            accept=".csv,.xlsx,.xls" required onchange="showUploadWarning()">
                                        <div class="form-text">Format: .csv / .xlsx / .xls | Max: 50MB</div>
                                    </div>
                                    <div class="alert alert-warning py-2 small mb-3 d-none" id="uploadWarning">
                                        <i class="fas fa-hourglass-half me-1"></i>
                                        File besar akan memakan waktu. Jangan tutup halaman.
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100" id="btnUploadMaster">
                                        <i class="fas fa-plus me-1"></i>
                                        <?= empty($master_infos) ? 'Upload Master Data' : 'Tambah Master Data' ?>
                                    </button>
                                </form>

                                <?php if (!empty($master_infos)): ?>
                                    <div class="mt-2">
                                        <div class="alert alert-info py-2 small mb-0">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Tip: kalau ada LT di Jan-Apr <em>dan</em> May, upload keduanya —
                                            sistem akan merge otomatis tanpa duplikat.
                                        </div>
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Upload Excel SPX -->
                    <div class="col-md-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3" style="background:#ed7d31;">
                                <h6 class="m-0 fw-bold text-white">
                                    <span class="badge bg-white me-2" style="color:#ed7d31;">2</span>
                                    Upload Feedback Issues SPX
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3">
                                    File Excel dari SPX (yang ada sheet Leadtime …).<br>
                                    <strong>LT Number</strong> di sini akan dicocokin ke masterdata.
                                    Spasi di LT Number otomatis dihapus sebelum dicocokin.
                                </p>

                                <?php if (!empty($xl_path) && file_exists($xl_path)): ?>
                                    <div class="alert alert-success py-2 small mb-3">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Excel sudah terupload.
                                        <a href="<?= base_url('feedback/reset') ?>" class="text-danger ms-1"
                                            onclick="return confirm('Reset semua?')">Ganti</a>
                                    </div>
                                <?php endif ?>

                                <form action="<?= base_url('feedback/upload_excel') ?>" method="post"
                                    enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <input type="file" name="xl_file" class="form-control form-control-sm"
                                            accept=".xlsx,.xls" required>
                                        <div class="form-text">Format: .xlsx / .xls | Max: 30MB</div>
                                    </div>
                                    <button type="submit" class="btn btn-sm w-100 text-white"
                                        style="background:#ed7d31;">
                                        <i class="fas fa-upload me-1"></i> Upload Excel
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Pilih Sheet & Proses -->
                    <div class="col-md-4">
                        <div class="card shadow h-100">
                            <div class="card-header py-3 bg-success text-white">
                                <h6 class="m-0 fw-bold">
                                    <span class="badge bg-white text-success me-2">3</span>
                                    Pilih Sheet & Proses
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small text-muted mb-3">
                                    Pilih sheet <strong>Leadtime</strong> dari file Excel SPX,
                                    lalu klik proses. Hasilnya bisa langsung didownload.
                                </p>

                                <?php if (empty($xl_path) || !file_exists($xl_path)): ?>
                                    <div class="alert alert-warning py-2 small">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Upload Excel SPX dulu di Step 2.
                                    </div>
                                <?php elseif (empty($sheet_names)): ?>
                                    <div class="alert alert-danger py-2 small">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Sheet tidak terbaca. Pastikan file valid.
                                    </div>
                                <?php else: ?>
                                    <form action="<?= base_url('feedback/process') ?>" method="post" id="processForm">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Sheet Leadtime:</label>
                                            <select name="sheet_name" class="form-select form-select-sm" required>
                                                <option value="">-- Pilih Sheet --</option>
                                                <?php foreach ($sheet_names as $s): ?>
                                                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?>
                                                    </option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>

                                        <?php if (!$lookup_ready): ?>
                                            <div class="alert alert-warning py-2 small mb-3">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Upload master data Dailyrent dulu di Step 1.
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-success py-2 small mb-3">
                                                <i class="fas fa-database me-1"></i>
                                                Lookup siap:
                                                <strong><?= number_format($lookup_total) ?> LT Number</strong>
                                                dari <?= $master_count ?> file master data
                                            </div>
                                        <?php endif ?>

                                        <button type="submit" class="btn btn-success btn-sm w-100" id="btnProcess"
                                            <?= !$lookup_ready ? 'disabled' : '' ?>>
                                            <i class="fas fa-bolt me-1"></i> Proses & Lihat Hasil
                                        </button>
                                    </form>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>

                </div><!-- /row -->

                <!-- Info cara kerja -->
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-semibold text-muted">
                            <i class="fas fa-info-circle text-info me-2"></i> Cara Kerja
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <span class="badge bg-primary rounded-circle"
                                        style="height:22px;width:22px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">1</span>
                                    <div class="small">
                                        <strong>Multiple Master Data</strong><br>
                                        Upload Jan-Apr <em>dan</em> May sekaligus. Lookup otomatis
                                        digabung — LT yang ada di salah satu file sudah cukup untuk ketemu vendornya.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <span class="badge bg-success rounded-circle"
                                        style="height:22px;width:22px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">2</span>
                                    <div class="small">
                                        <strong>Strip spasi otomatis</strong><br>
                                        LT Number kadang ada spasi nyasar.
                                        Sistem otomatis hapus semua spasi sebelum lookup — persis
                                        CTRL+H → replace spasi yang dilakukan manual di Excel.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <span class="badge bg-warning text-dark rounded-circle"
                                        style="height:22px;width:22px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">3</span>
                                    <div class="small">
                                        <strong>Vlookup LT → Nopol</strong><br>
                                        Coba cocokkan LT Number dulu. Kalau gagal,
                                        fallback ke Nopol (ditandai 🔶 di hasil).
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <span class="badge bg-danger rounded-circle"
                                        style="height:22px;width:22px;flex-shrink:0;display:flex;align-items:center;justify-content:center;">N/A</span>
                                    <div class="small">
                                        <strong>Tidak ditemukan?</strong><br>
                                        Wajar — LT bisa berasal dari master data lain
                                        (non-Dailyrent, non-FTL TSC). Upload file master lain
                                        di Step 1 kalau ada.
                                    </div>
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
    function showUploadWarning() {
        document.getElementById('uploadWarning').classList.remove('d-none');
    }

    document.getElementById('formUploadMaster')?.addEventListener('submit', function () {
        const btn = document.getElementById('btnUploadMaster');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Parsing... Jangan tutup halaman';
        btn.disabled = true;
        document.getElementById('uploadWarning').classList.remove('d-none');
    });

    document.getElementById('processForm')?.addEventListener('submit', function () {
        const btn = document.getElementById('btnProcess');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
        btn.disabled = true;
    });
</script>
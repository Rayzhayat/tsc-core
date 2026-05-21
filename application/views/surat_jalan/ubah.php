<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css"
        rel="stylesheet" />
    <style>
        .form-section {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f6c23e;
        }

        .section-header {
            background: linear-gradient(135deg, #f6c23e 0%, #e74a3b 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .info-box.success {
            background: #e8f5e9;
            border-left-color: #4CAF50;
        }

        .info-box.warning {
            background: #fff3e0;
            border-left-color: #FF9800;
        }

        .alert-warning-edit {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
        }

        .destination-list {
            list-style: none;
            padding-left: 0;
        }

        .destination-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e3e6f0;
        }

        .destination-list li:last-child {
            border-bottom: none;
        }

        .select2-container {
            width: 100% !important;
        }

        .calculation-box {
            background: linear-gradient(135deg, #f6c23e 0%, #e74a3b 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            position: sticky;
            top: 20px;
        }

        .calculation-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .calculation-row:last-child {
            border-bottom: none;
        }

        .nominal-display {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .grand-total {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }

        /* 🔥 Highlight for Select2 */
        .select2-results mark {
            background-color: #ffc107;
            color: #000;
            font-weight: bold;
            padding: 2px 4px;
            border-radius: 3px;
        }

        .select2-result-item {
            padding: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        .select2-result-item:hover {
            background-color: #f8f9fa;
        }

        .select2-container--bootstrap .select2-results__option--highlighted {
            background-color: #f6c23e;
            color: white;
        }

        .select2-container--bootstrap .select2-results__option--highlighted mark {
            background-color: #ffd700;
            color: #000;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>

                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-edit text-warning"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('surat_jalan') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- Warning Alert -->
                    <div class="alert alert-warning-edit alert-dismissible fade show">
                        <h5><i class="fas fa-exclamation-triangle"></i> <strong>PERHATIAN!</strong></h5>
                        <p class="mb-0">
                            Edit surat jalan akan mengupdate data perjalanan.
                            Pastikan data yang diinput sudah benar sebelum menyimpan.
                        </p>
                        <button type="button" class="close" data-dismiss="alert" style="color: white;">×</button>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Form -->
                    <form action="<?= base_url('surat_jalan/proses_ubah') ?>" method="post" id="suratJalanForm"
                        enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $surat_jalan->id ?>">

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-lg-8">

                                <!-- Current No. Surat Jalan -->
                                <div class="card shadow mb-4">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">No. Surat Jalan</h6>
                                        <span class="badge badge-warning"
                                            style="font-size: 1.2rem; padding: 10px 20px;">
                                            <?= htmlspecialchars($surat_jalan->no_surat_jalan) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            Status: <span
                                                class="badge badge-<?= $surat_jalan->status == 'draft' ? 'secondary' : 'primary' ?>">
                                                <?= strtoupper($surat_jalan->status) ?>
                                            </span>
                                        </small>
                                    </div>
                                </div>

                                <!-- Basic Info -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3"
                                        style="background: linear-gradient(135deg, #f6c23e 0%, #e74a3b 100%); color: white;">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-info-circle"></i> Informasi Dasar
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Tanggal <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" name="tanggal" id="tanggal" class="form-control"
                                                        value="<?= $surat_jalan->tanggal ?>" required>
                                                    <small class="text-muted">Tanggal surat jalan</small>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Status</label>
                                                    <select name="status" id="status" class="form-control" disabled>
                                                        <option value="<?= $surat_jalan->status ?>" selected>
                                                            <?= strtoupper($surat_jalan->status) ?>
                                                        </option>
                                                    </select>
                                                    <small class="text-muted">Status tidak bisa diubah di form
                                                        edit</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rute Selection -->
                                <!-- Rute Selection -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3"
                                        style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-route"></i> Rute
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                Rute <span class="text-danger">*</span>
                                            </label>
                                            <select name="kode_rute" id="kode_rute" class="form-control select2-ajax"
                                                required>
                                                <!-- 🔥 FIX: Add full data attributes to initial option -->
                                                <option value="<?= htmlspecialchars($surat_jalan->kode_rute) ?>"
                                                    selected
                                                    data-customer="<?= htmlspecialchars($surat_jalan->customer) ?>"
                                                    data-service="<?= htmlspecialchars($surat_jalan->service) ?>"
                                                    data-sla="<?= htmlspecialchars($surat_jalan->sla) ?>"
                                                    data-origin="<?= htmlspecialchars($surat_jalan->origin) ?>"
                                                    data-dest1="<?= htmlspecialchars($surat_jalan->dest1) ?>"
                                                    data-dest2="<?= htmlspecialchars($surat_jalan->dest2 ?? '') ?>"
                                                    data-dest3="<?= htmlspecialchars($surat_jalan->dest3 ?? '') ?>"
                                                    data-dest4="<?= htmlspecialchars($surat_jalan->dest4 ?? '') ?>"
                                                    data-harga="<?= $surat_jalan->biaya_sewa ?>">
                                                    <?= htmlspecialchars($surat_jalan->customer) ?> |
                                                    <?= htmlspecialchars($surat_jalan->origin) ?> →
                                                    <?= htmlspecialchars($surat_jalan->dest1) ?> |
                                                    <?= htmlspecialchars($surat_jalan->service) ?>
                                                    (<?= htmlspecialchars($surat_jalan->sla) ?>) |
                                                    Rp <?= number_format($surat_jalan->biaya_sewa, 0, ',', '.') ?>
                                                </option>
                                            </select>
                                            <small class="text-muted">
                                                <i class="fas fa-search"></i> Ketik untuk mencari rute lain
                                                (multi-keyword support)
                                            </small>
                                        </div>

                                        <!-- Rute Info Display -->
                                        <div id="ruteInfoDisplay">
                                            <div class="info-box">
                                                <h6 class="font-weight-bold mb-3">
                                                    <i class="fas fa-info-circle"></i> Informasi Rute
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="mb-2">
                                                            <strong>Customer:</strong>
                                                            <span
                                                                id="displayCustomer"><?= htmlspecialchars($surat_jalan->customer) ?></span>
                                                        </p>
                                                        <p class="mb-2">
                                                            <strong>Service:</strong>
                                                            <span class="badge badge-info"
                                                                id="displayService"><?= htmlspecialchars($surat_jalan->service) ?></span>
                                                        </p>
                                                        <p class="mb-2">
                                                            <strong>SLA:</strong>
                                                            <span class="badge badge-warning"
                                                                id="displaySLA"><?= htmlspecialchars($surat_jalan->sla) ?></span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-2">
                                                            <strong>Origin:</strong><br>
                                                            <i class="fas fa-map-marker-alt text-success"></i>
                                                            <span
                                                                id="displayOrigin"><?= htmlspecialchars($surat_jalan->origin) ?></span>
                                                        </p>
                                                        <p class="mb-2">
                                                            <strong>Destinasi:</strong>
                                                        </p>
                                                        <ul class="destination-list" id="displayDestinations">
                                                            <?php if ($surat_jalan->dest1): ?>
                                                                <li><i class="fas fa-map-pin text-danger"></i>
                                                                    <?= htmlspecialchars($surat_jalan->dest1) ?></li>
                                                            <?php endif; ?>
                                                            <?php if ($surat_jalan->dest2): ?>
                                                                <li><i class="fas fa-map-pin text-danger"></i>
                                                                    <?= htmlspecialchars($surat_jalan->dest2) ?></li>
                                                            <?php endif; ?>
                                                            <?php if ($surat_jalan->dest3): ?>
                                                                <li><i class="fas fa-map-pin text-danger"></i>
                                                                    <?= htmlspecialchars($surat_jalan->dest3) ?></li>
                                                            <?php endif; ?>
                                                            <?php if ($surat_jalan->dest4): ?>
                                                                <li><i class="fas fa-map-pin text-danger"></i>
                                                                    <?= htmlspecialchars($surat_jalan->dest4) ?></li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <hr>
                                                <p class="mb-0">
                                                    <strong>Biaya Sewa:</strong>
                                                    <span class="text-primary nominal-display" id="displayBiayaSewa">
                                                        Rp <?= number_format($surat_jalan->biaya_sewa, 0, ',', '.') ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 🔥 NEW: Customer Email Section -->
                                <div class="card shadow mb-4" id="customer-email-section">
                                    <div class="card-header py-3"
                                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-envelope"></i> Email Notifikasi Customer
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Email untuk Notifikasi POD</strong>
                                            <p class="mb-0 small">Email akan digunakan untuk mengirim notifikasi POD
                                                (Proof of Delivery) dan invoice ke customer secara otomatis.</p>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Email Perusahaan <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="email" name="customer_email" id="customer_email"
                                                        class="form-control"
                                                        value="<?= htmlspecialchars($surat_jalan->customer_email ?? '') ?>"
                                                        placeholder="finance@company.com" required>
                                                    <small class="form-text text-muted">
                                                        <i class="fas fa-building"></i> Email resmi perusahaan untuk
                                                        invoice & POD
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Email PIC (Optional)
                                                    </label>
                                                    <input type="email" name="customer_email_pic"
                                                        id="customer_email_pic" class="form-control"
                                                        value="<?= htmlspecialchars($surat_jalan->customer_email_pic ?? '') ?>"
                                                        placeholder="pic@company.com">
                                                    <small class="form-text text-muted">
                                                        <i class="fas fa-user"></i> Email PIC untuk komunikasi langsung
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Auto-fill button if email empty -->
                                        <?php if (empty($surat_jalan->customer_email)): ?>
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong>Email Belum Diisi!</strong>
                                                <p class="mb-2 small">Email customer belum diisi pada surat jalan ini.</p>
                                                <button type="button" class="btn btn-sm btn-primary" id="btnSearchEmail">
                                                    <i class="fas fa-search"></i> Cari Email dari Database
                                                </button>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Auto-fill Success Message -->
                                        <div id="email-autofill-info" class="alert alert-success"
                                            style="display: none;">
                                            <i class="fas fa-check-circle"></i>
                                            <strong>Email Ditemukan!</strong>
                                            <p class="mb-0 small">Email otomatis terisi dari database customer.</p>
                                        </div>

                                        <!-- Manual Input Warning -->
                                        <div id="email-manual-warning" class="alert alert-warning"
                                            style="display: none;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Email Tidak Ditemukan!</strong>
                                            <p class="mb-0 small">Silakan isi email secara manual.</p>
                                        </div>

                                        <!-- Loading Indicator -->
                                        <div id="email-loading" class="text-center" style="display: none;">
                                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                            <p class="text-muted mt-2">Mencari email customer...</p>
                                        </div>
                                    </div>
                                </div>



                                <!-- Driver & Unit Selection -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3"
                                        style="background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%); color: white;">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-users"></i> Driver & Unit
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Driver <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="driver_id" id="driver_id" class="form-control select2"
                                                        required>
                                                        <option value="">-- Pilih Driver --</option>
                                                        <?php foreach ($drivers as $driver): ?>
                                                            <option value="<?= $driver->id ?>"
                                                                data-nama="<?= htmlspecialchars($driver->nama_driver) ?>"
                                                                data-nik="<?= htmlspecialchars($driver->nik) ?>"
                                                                data-sim="<?= htmlspecialchars($driver->sim) ?>"
                                                                <?= $driver->id == $surat_jalan->driver_id ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($driver->nama_driver) ?> |
                                                                NIK: <?= htmlspecialchars($driver->nik) ?>
                                                            </option>
                                                        <?php endforeach ?>
                                                    </select>
                                                </div>

                                                <!-- Driver Info Display -->
                                                <div id="driverInfoDisplay">
                                                    <div class="info-box success">
                                                        <p class="mb-1"><strong>Nama:</strong> <span
                                                                id="displayDriverNama"><?= htmlspecialchars($surat_jalan->nama_driver) ?></span>
                                                        </p>
                                                        <p class="mb-1"><strong>NIK:</strong> <span
                                                                id="displayDriverNIK"><?= htmlspecialchars($surat_jalan->driver_nik) ?></span>
                                                        </p>
                                                        <p class="mb-0"><strong>SIM:</strong> <span
                                                                id="displayDriverSIM">-</span></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Unit / Armada <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="unit_id" id="unit_id" class="form-control select2"
                                                        required>
                                                        <option value="">-- Pilih Unit --</option>
                                                        <?php foreach ($units as $unit): ?>
                                                            <option value="<?= $unit->id ?>"
                                                                data-nopol="<?= htmlspecialchars($unit->no_polisi) ?>"
                                                                data-tipe="<?= htmlspecialchars($unit->tipe_unit) ?>"
                                                                data-box="<?= htmlspecialchars($unit->tipe_box) ?>"
                                                                data-tonase="<?= $unit->tonase ?>"
                                                                <?= $unit->id == $surat_jalan->unit_id ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($unit->no_polisi) ?> |
                                                                <?= htmlspecialchars($unit->tipe_unit) ?>
                                                            </option>
                                                        <?php endforeach ?>
                                                    </select>
                                                </div>

                                                <!-- Unit Info Display -->
                                                <div id="unitInfoDisplay">
                                                    <div class="info-box success">
                                                        <p class="mb-1"><strong>No. Polisi:</strong> <span
                                                                id="displayUnitNopol"><?= htmlspecialchars($surat_jalan->no_polisi) ?></span>
                                                        </p>
                                                        <p class="mb-1"><strong>Tipe:</strong> <span
                                                                id="displayUnitTipe"><?= htmlspecialchars($surat_jalan->unit_tipe) ?></span>
                                                        </p>
                                                        <p class="mb-1"><strong>Box:</strong> <span
                                                                id="displayUnitBox"><?= htmlspecialchars($surat_jalan->tipe_box) ?></span>
                                                        </p>
                                                        <p class="mb-0"><strong>Kapasitas:</strong> <span
                                                                id="displayUnitTonase">-</span> Ton</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Muatan Details -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3"
                                        style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-box"></i> Detail Muatan
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                Muatan / Deskripsi Barang <span class="text-danger">*</span>
                                            </label>
                                            <textarea name="muatan" id="muatan" class="form-control" rows="3"
                                                placeholder="Contoh: 100 Karton Produk X, 50 Dos Produk Y"
                                                required><?= htmlspecialchars($surat_jalan->muatan) ?></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Tonase Aktual (Ton)
                                                    </label>
                                                    <input type="number" name="tonase_aktual" id="tonase_aktual"
                                                        class="form-control" step="0.01"
                                                        value="<?= $surat_jalan->tonase_aktual ?>" placeholder="0.00">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Kubikasi Aktual (m³)
                                                    </label>
                                                    <input type="number" name="kubikasi_aktual" id="kubikasi_aktual"
                                                        class="form-control" step="0.01"
                                                        value="<?= $surat_jalan->kubikasi_aktual ?>" placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Biaya Section (Editable) -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 bg-gradient-success text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-dollar-sign"></i> Biaya Operasional
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Biaya Solar (Rp)</label>
                                                <input type="number" name="biaya_solar" id="biaya_solar"
                                                    class="form-control biaya-field"
                                                    value="<?= $surat_jalan->biaya_solar ?>" step="1000"
                                                    placeholder="0">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Biaya Tol (Rp)</label>
                                                <input type="number" name="biaya_tol" id="biaya_tol"
                                                    class="form-control biaya-field"
                                                    value="<?= $surat_jalan->biaya_tol ?>" step="1000" placeholder="0">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Biaya Parkir (Rp)</label>
                                                <input type="number" name="biaya_parkir" id="biaya_parkir"
                                                    class="form-control biaya-field"
                                                    value="<?= $surat_jalan->biaya_parkir ?>" step="1000"
                                                    placeholder="0">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Biaya Makan (Rp)</label>
                                                <input type="number" name="biaya_makan" id="biaya_makan"
                                                    class="form-control biaya-field"
                                                    value="<?= $surat_jalan->biaya_makan ?>" step="1000"
                                                    placeholder="0">
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <label class="font-weight-bold">Biaya Lainnya (Rp)</label>
                                                <input type="number" name="biaya_lainnya" id="biaya_lainnya"
                                                    class="form-control biaya-field"
                                                    value="<?= $surat_jalan->biaya_lainnya ?>" step="1000"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Info -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3"
                                        style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                                        <h6 class="m-0 font-weight-bold text-dark">
                                            <i class="fas fa-sticky-note"></i> Informasi Tambahan
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Catatan</label>
                                            <textarea name="catatan" id="catatan" class="form-control" rows="3"
                                                placeholder="Catatan tambahan (optional)"><?= htmlspecialchars($surat_jalan->catatan) ?></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                Foto Surat Jalan (Optional)
                                            </label>
                                            <?php if ($surat_jalan->foto_surat_jalan): ?>
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-check-circle text-success"></i>
                                                        Foto sudah diupload:
                                                        <?= htmlspecialchars($surat_jalan->foto_surat_jalan) ?>
                                                        <br>Upload file baru untuk mengganti
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            <div class="custom-file">
                                                <input type="file" name="foto_surat_jalan" class="custom-file-input"
                                                    id="fotoSuratJalan" accept="image/*,.pdf">
                                                <label class="custom-file-label" for="fotoSuratJalan">
                                                    Pilih file...
                                                </label>
                                            </div>
                                            <small class="text-muted">
                                                Format: JPG, JPEG, PNG, PDF (Max: 5MB)
                                            </small>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Right Column - Summary -->
                            <div class="col-lg-4">
                                <!-- Calculation Summary -->
                                <div class="card shadow mb-4">
                                    <div class="card-body p-0">
                                        <div class="calculation-box">
                                            <h5 class="font-weight-bold mb-3">
                                                <i class="fas fa-calculator"></i> Ringkasan Biaya
                                            </h5>

                                            <div class="calculation-row">
                                                <span>Biaya Sewa:</span>
                                                <span class="nominal-display" id="summaryBiayaSewa">
                                                    Rp <?= number_format($surat_jalan->biaya_sewa, 0, ',', '.') ?>
                                                </span>
                                            </div>

                                            <div class="calculation-row">
                                                <span>Biaya Solar:</span>
                                                <span class="nominal-display" id="summarySolar">Rp 0</span>
                                            </div>

                                            <div class="calculation-row">
                                                <span>Biaya Tol:</span>
                                                <span class="nominal-display" id="summaryTol">Rp 0</span>
                                            </div>

                                            <div class="calculation-row">
                                                <span>Biaya Parkir:</span>
                                                <span class="nominal-display" id="summaryParkir">Rp 0</span>
                                            </div>

                                            <div class="calculation-row">
                                                <span>Biaya Makan:</span>
                                                <span class="nominal-display" id="summaryMakan">Rp 0</span>
                                            </div>

                                            <div class="calculation-row">
                                                <span>Biaya Lainnya:</span>
                                                <span class="nominal-display" id="summaryLainnya">Rp 0</span>
                                            </div>

                                            <div class="grand-total">
                                                <div class="d-flex justify-content-between">
                                                    <h5 class="mb-0 font-weight-bold">TOTAL BIAYA:</h5>
                                                    <h5 class="mb-0 font-weight-bold nominal-display" id="summaryTotal">
                                                        Rp 0</h5>
                                                </div>
                                            </div>

                                            <hr style="border-color: rgba(255,255,255,0.3); margin-top: 20px;">

                                            <div class="mt-3">
                                                <h6 class="font-weight-bold mb-2">
                                                    <i class="fas fa-info-circle"></i> Informasi
                                                </h6>
                                                <small>
                                                    <ul class="mb-0 pl-3">
                                                        <li>Biaya sewa dari rute</li>
                                                        <li>Biaya operasional bisa ditambah/edit</li>
                                                        <li>Total dihitung otomatis</li>
                                                    </ul>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Change History Info -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 bg-gradient-secondary text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-history"></i> Riwayat
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <small>
                                            <p class="mb-1">
                                                <strong>Dibuat:</strong><br>
                                                <?= htmlspecialchars($surat_jalan->created_by ?? '-') ?> |
                                                <?= date('d/m/Y H:i', strtotime($surat_jalan->created_at)) ?>
                                            </p>
                                            <?php if ($surat_jalan->updated_at && $surat_jalan->updated_at != $surat_jalan->created_at): ?>
                                                <p class="mb-0">
                                                    <strong>Terakhir Diubah:</strong><br>
                                                    <?= date('d/m/Y H:i', strtotime($surat_jalan->updated_at)) ?>
                                                </p>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="card shadow mb-4">
                                    <div class="card-body">
                                        <button type="submit" class="btn btn-warning btn-block btn-lg" id="submitBtn">
                                            <i class="fas fa-save"></i> Update Surat Jalan
                                        </button>
                                        <a href="<?= base_url('surat_jalan/detail/' . $surat_jalan->id) ?>"
                                            class="btn btn-info btn-block">
                                            <i class="fas fa-eye"></i> Lihat Detail
                                        </a>
                                        <a href="<?= base_url('surat_jalan') ?>" class="btn btn-secondary btn-block">
                                            <i class="fas fa-times"></i> Batal
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </form>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            // 🔥 Store initial rute data
            var initialRuteData = {
                customer: '<?= addslashes($surat_jalan->customer) ?>',
                service: '<?= addslashes($surat_jalan->service) ?>',
                sla: '<?= addslashes($surat_jalan->sla) ?>',
                origin: '<?= addslashes($surat_jalan->origin) ?>',
                dest1: '<?= addslashes($surat_jalan->dest1) ?>',
                dest2: '<?= addslashes($surat_jalan->dest2 ?? '') ?>',
                dest3: '<?= addslashes($surat_jalan->dest3 ?? '') ?>',
                dest4: '<?= addslashes($surat_jalan->dest4 ?? '') ?>',
                harga: <?= $surat_jalan->biaya_sewa ?>
            };

            // Format number helper (define first before use)
            function formatNumber(num) {
                return Math.round(num).toLocaleString('id-ID');
            }

            // 🔥 Initialize Select2 with AJAX for rute (Multi-keyword search)
            $('#kode_rute').select2({
                theme: 'bootstrap',
                width: '100%',
                placeholder: '-- Ketik untuk mencari rute (contoh: "Gonusa Cikarang") --',
                minimumInputLength: 2,
                allowClear: true,
                ajax: {
                    url: '<?= base_url('surat_jalan/ajax_search_rute') ?>',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;

                        return {
                            results: data.results,
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                language: {
                    inputTooShort: function () {
                        return 'Ketik minimal 2 karakter untuk mencari...';
                    },
                    searching: function () {
                        return 'Mencari rute...';
                    },
                    noResults: function () {
                        return 'Rute tidak ditemukan';
                    },
                    loadingMore: function () {
                        return 'Memuat lebih banyak...';
                    }
                },
                templateResult: function (item) {
                    if (item.loading) {
                        return item.text;
                    }

                    if (!item.id) {
                        return item.text;
                    }

                    // Get search term for highlighting
                    var searchTerm = '';
                    try {
                        searchTerm = $('#kode_rute').data('select2').$dropdown.find('.select2-search__field').val() || '';
                    } catch (e) {
                        searchTerm = '';
                    }

                    var keywords = searchTerm.split(' ').filter(function (k) {
                        return k.length > 1;
                    });

                    // Highlight function
                    function highlightText(text) {
                        if (!text) return '';

                        keywords.forEach(function (keyword) {
                            if (keyword.length > 1) {
                                var regex = new RegExp('(' + keyword + ')', 'gi');
                                text = text.replace(regex, '<mark>$1</mark>');
                            }
                        });

                        return text;
                    }

                    // Custom display format
                    var $result = $(
                        '<div class="select2-result-item" style="padding: 8px;">' +
                        '<strong>' + highlightText(item.customer) + '</strong><br>' +
                        '<small style="color: #666;">' +
                        '<i class="fas fa-route"></i> ' +
                        highlightText(item.origin) + ' <i class="fas fa-arrow-right"></i> ' + highlightText(item.dest1) +
                        '</small><br>' +
                        '<small>' +
                        '<span class="badge badge-info" style="font-size: 10px;">' + item.service + '</span> ' +
                        '<span class="badge badge-warning" style="font-size: 10px;">' + item.sla + '</span> ' +
                        '<span style="color: #007bff; font-weight: bold;">Rp ' + formatNumber(item.harga) + '</span>' +
                        '</small>' +
                        '</div>'
                    );
                    return $result;
                },
                templateSelection: function (item) {
                    if (!item.id) {
                        return item.text;
                    }

                    // 🔥 FIX: Use data from element or fallback to item properties
                    var customer = item.customer || $(item.element).data('customer') || '';
                    var origin = item.origin || $(item.element).data('origin') || '';
                    var dest1 = item.dest1 || $(item.element).data('dest1') || '';

                    if (customer && origin && dest1) {
                        return customer + ' | ' + origin + ' → ' + dest1;
                    } else {
                        // Last fallback: use text
                        return item.text;
                    }
                },
                escapeMarkup: function (markup) {
                    return markup;
                }
            });

            // 🔥 Rute select event
            $('#kode_rute').on('select2:select', function (e) {
                const data = e.params.data;

                if (data.id) {
                    console.log('✅ Rute changed to:', data);

                    // Get data from element attributes if not in data object
                    var customer = data.customer || $(data.element).data('customer');
                    var service = data.service || $(data.element).data('service');
                    var sla = data.sla || $(data.element).data('sla');
                    var origin = data.origin || $(data.element).data('origin');
                    var dest1 = data.dest1 || $(data.element).data('dest1');
                    var dest2 = data.dest2 || $(data.element).data('dest2') || '';
                    var dest3 = data.dest3 || $(data.element).data('dest3') || '';
                    var dest4 = data.dest4 || $(data.element).data('dest4') || '';
                    var harga = data.harga || $(data.element).data('harga') || 0;

                    // Update display
                    $('#displayCustomer').text(customer);
                    $('#displayService').text(service);
                    $('#displaySLA').text(sla);
                    $('#displayOrigin').text(origin);
                    $('#displayBiayaSewa').text('Rp ' + formatNumber(harga));

                    // Build destinations list
                    let destHTML = '';
                    if (dest1) {
                        destHTML += '<li><i class="fas fa-map-pin text-danger"></i> ' + dest1 + '</li>';
                    }
                    if (dest2) {
                        destHTML += '<li><i class="fas fa-map-pin text-danger"></i> ' + dest2 + '</li>';
                    }
                    if (dest3) {
                        destHTML += '<li><i class="fas fa-map-pin text-danger"></i> ' + dest3 + '</li>';
                    }
                    if (dest4) {
                        destHTML += '<li><i class="fas fa-map-pin text-danger"></i> ' + dest4 + '</li>';
                    }

                    $('#displayDestinations').html(destHTML);

                    // Update summary biaya sewa
                    $('#summaryBiayaSewa').text('Rp ' + formatNumber(harga));

                    // Recalculate total
                    calculateTotal();

                    // ========================================
                    // 🔥 NEW: Auto-search email when rute changes (if email empty)
                    // ========================================
                    const currentEmail = $('#customer_email').val().trim();

                    if (customer && !currentEmail) {
                        console.log('🔍 Rute changed, searching email for:', customer);

                        $('#email-loading').show();
                        $('#email-autofill-info').hide();
                        $('#email-manual-warning').hide();

                        $.ajax({
                            url: '<?= base_url('surat_jalan/ajax_get_customer_email') ?>',
                            type: 'POST',
                            data: {
                                customer_name: customer
                            },
                            dataType: 'json',
                            success: function (response) {
                                $('#email-loading').hide();

                                if (response.success && response.email) {
                                    $('#customer_email').val(response.email).addClass('border-success');

                                    if (response.email_pic) {
                                        $('#customer_email_pic').val(response.email_pic);
                                    }

                                    $('#email-autofill-info').slideDown();
                                    console.log('✅ Email auto-filled after rute change');
                                } else {
                                    $('#email-manual-warning').slideDown();
                                }
                            },
                            error: function () {
                                $('#email-loading').hide();
                                $('#email-manual-warning').slideDown();
                            }
                        });
                    }
                }
            });

            // ========================================
            // 🔥 EMAIL AUTO-FILL FOR EDIT FORM
            // ========================================

            // Manual search email button
            $('#btnSearchEmail').on('click', function () {
                const customerName = '<?= addslashes($surat_jalan->customer) ?>';

                console.log('🔍 Manual search email for:', customerName);

                // Show loading
                $('#email-loading').show();
                $('#email-manual-warning').hide();
                $('#email-autofill-info').hide();

                // AJAX call
                $.ajax({
                    url: '<?= base_url('surat_jalan/ajax_get_customer_email') ?>',
                    type: 'POST',
                    data: {
                        customer_name: customerName
                    },
                    dataType: 'json',
                    success: function (response) {
                        console.log('📧 AJAX Response:', response);

                        // Hide loading
                        $('#email-loading').hide();

                        if (response.success && response.email) {
                            // ✅ Email found
                            console.log('✅ Email found:', response.email);

                            $('#customer_email').val(response.email).addClass('border-success');

                            if (response.email_pic) {
                                $('#customer_email_pic').val(response.email_pic);
                            }

                            // Show success message
                            $('#email-autofill-info').slideDown();

                            // Hide search button
                            $('#btnSearchEmail').closest('.alert').slideUp();

                        } else {
                            // ❌ Email not found
                            console.log('⚠️ Email not found');

                            $('#customer_email').focus();
                            $('#email-manual-warning').slideDown();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('❌ AJAX Error:', error);

                        $('#email-loading').hide();
                        $('#email-manual-warning')
                            .html('<i class="fas fa-exclamation-triangle"></i> <strong>Error!</strong> ' +
                                '<p class="mb-0 small">Gagal mencari email. Silakan isi manual.</p>')
                            .slideDown();
                    }
                });
            });

            // Email validation on blur
            $('#customer_email').on('blur', function () {
                const email = $(this).val().trim();

                if (email) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (emailRegex.test(email)) {
                        $(this).removeClass('border-danger').addClass('border-success');
                        $(this).siblings('.invalid-feedback').remove();
                        console.log('✅ Email format valid:', email);
                    } else {
                        $(this).removeClass('border-success').addClass('border-danger');

                        if (!$(this).siblings('.invalid-feedback').length) {
                            $(this).after('<div class="invalid-feedback d-block">Format email tidak valid!</div>');
                        }
                        console.log('❌ Email format invalid:', email);
                    }
                } else {
                    $(this).removeClass('border-success border-danger');
                    $(this).siblings('.invalid-feedback').remove();
                }
            });

            // Remove invalid feedback on input
            $('#customer_email').on('input', function () {
                $(this).removeClass('border-danger');
                $(this).siblings('.invalid-feedback').remove();
            });

            // Initialize normal Select2 for driver & unit
            $('#driver_id, #unit_id').select2({
                theme: 'bootstrap',
                width: '100%'
            });

            // Custom file input label
            $('.custom-file-input').on('change', function () {
                var fileName = $(this).val().split('\\').pop();
                $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
            });

            // Driver change handler
            $('#driver_id').on('change', function () {
                const selected = $(this).find(':selected');

                if (selected.val()) {
                    const nama = selected.data('nama');
                    const nik = selected.data('nik');
                    const sim = selected.data('sim');

                    $('#displayDriverNama').text(nama);
                    $('#displayDriverNIK').text(nik);
                    $('#displayDriverSIM').text(sim);

                    console.log('✅ Driver changed to:', nama);
                }
            });

            // Unit change handler
            $('#unit_id').on('change', function () {
                const selected = $(this).find(':selected');

                if (selected.val()) {
                    const nopol = selected.data('nopol');
                    const tipe = selected.data('tipe');
                    const box = selected.data('box');
                    const tonase = selected.data('tonase');

                    $('#displayUnitNopol').text(nopol);
                    $('#displayUnitTipe').text(tipe);
                    $('#displayUnitBox').text(box);
                    $('#displayUnitTonase').text(tonase);

                    console.log('✅ Unit changed to:', nopol);
                }
            });

            // 🔥 Real-time biaya calculation
            $('.biaya-field').on('input', function () {
                calculateTotal();
            });

            // Calculate total function
            function calculateTotal() {
                // Get biaya sewa from display (could be changed if rute changed)
                const biayaSewaText = $('#summaryBiayaSewa').text().replace(/[^\d]/g, '');
                const biayaSewa = parseFloat(biayaSewaText) || 0;

                const solar = parseFloat($('#biaya_solar').val()) || 0;
                const tol = parseFloat($('#biaya_tol').val()) || 0;
                const parkir = parseFloat($('#biaya_parkir').val()) || 0;
                const makan = parseFloat($('#biaya_makan').val()) || 0;
                const lainnya = parseFloat($('#biaya_lainnya').val()) || 0;

                const total = biayaSewa + solar + tol + parkir + makan + lainnya;

                // Update summary
                $('#summarySolar').text('Rp ' + formatNumber(solar));
                $('#summaryTol').text('Rp ' + formatNumber(tol));
                $('#summaryParkir').text('Rp ' + formatNumber(parkir));
                $('#summaryMakan').text('Rp ' + formatNumber(makan));
                $('#summaryLainnya').text('Rp ' + formatNumber(lainnya));
                $('#summaryTotal').text('Rp ' + formatNumber(total));
            }

            // Initial calculation
            calculateTotal();

            // 🔥 UPDATED: Form validation (includes email check)
            $('#suratJalanForm').on('submit', function (e) {
                const tanggal = $('#tanggal').val();
                const kode_rute = $('#kode_rute').val();
                const driver_id = $('#driver_id').val();
                const unit_id = $('#unit_id').val();
                const muatan = $('#muatan').val().trim();

                // 🔥 NEW: Email validation
                const customer_email = $('#customer_email').val().trim();

                if (!tanggal || !kode_rute || !driver_id || !unit_id || !muatan) {
                    e.preventDefault();
                    alert('⚠️ Harap lengkapi semua field yang wajib diisi!\n\n' +
                        '✓ Tanggal\n' +
                        '✓ Rute\n' +
                        '✓ Driver\n' +
                        '✓ Unit\n' +
                        '✓ Muatan');
                    return false;
                }

                // 🔥 NEW: Check email
                if (!customer_email) {
                    e.preventDefault();
                    alert('⚠️ Email customer harus diisi!\n\nEmail diperlukan untuk notifikasi POD.');
                    $('#customer_email').focus().addClass('border-danger');
                    $('html, body').animate({
                        scrollTop: $('#customer-email-section').offset().top - 100
                    }, 500);
                    return false;
                }

                // Email format validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(customer_email)) {
                    e.preventDefault();
                    alert('⚠️ Format email tidak valid!\n\nContoh: finance@company.com');
                    $('#customer_email').focus().addClass('border-danger');
                    $('html, body').animate({
                        scrollTop: $('#customer-email-section').offset().top - 100
                    }, 500);
                    return false;
                }

                // Check file size
                const fileInput = $('#fotoSuratJalan')[0];
                if (fileInput.files.length > 0) {
                    const fileSize = fileInput.files[0].size / 1024 / 1024;
                    if (fileSize > 5) {
                        e.preventDefault();
                        alert('⚠️ Ukuran file terlalu besar!\n\nMaksimal 5MB.');
                        return false;
                    }
                }

                // Disable submit button
                $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                console.log('📤 Form submitted - Update with email:', customer_email);
                return true;
            });

            // Auto hide alerts
            setTimeout(function () {
                $('.alert').not('.alert-warning-edit').fadeOut('slow');
            }, 5000);

            // Keyboard shortcuts
            $(document).keydown(function (e) {
                // Ctrl + S = Submit form
                if (e.ctrlKey && e.keyCode === 83) {
                    e.preventDefault();
                    $('#suratJalanForm').submit();
                }

                // ESC = Cancel
                if (e.keyCode === 27) {
                    if (confirm('Batalkan perubahan?')) {
                        window.location.href = '<?= base_url('surat_jalan') ?>';
                    }
                }
            });

            // Warning before leaving
            var formChanged = false;

            $('#suratJalanForm input, #suratJalanForm select, #suratJalanForm textarea').on('change', function () {
                formChanged = true;
            });

            $(window).on('beforeunload', function () {
                if (formChanged) {
                    return 'Perubahan yang diisi akan hilang. Yakin ingin meninggalkan halaman ini?';
                }
            });

            $('#suratJalanForm').on('submit', function () {
                formChanged = false;
            });

            // Tonase validation
            $('#tonase_aktual').on('input', function () {
                const tonaseAktual = parseFloat($(this).val());
                const unitSelected = $('#unit_id').find(':selected');

                if (unitSelected.val() && tonaseAktual) {
                    const unitTonase = parseFloat(unitSelected.data('tonase'));

                    if (tonaseAktual > unitTonase) {
                        $(this).addClass('border-danger');

                        if (!$(this).next('.warning-text').length) {
                            $(this).after('<small class="warning-text text-danger">⚠️ Melebihi kapasitas unit (' + unitTonase + ' ton)</small>');
                        }
                    } else {
                        $(this).removeClass('border-danger');
                        $(this).next('.warning-text').remove();
                    }
                }
            });

            $('#unit_id').on('change', function () {
                $('#tonase_aktual').trigger('input');
            });

            // Character counter for muatan
            $('#muatan').on('input', function () {
                const length = $(this).val().length;
                const counterHtml = '<small class="text-muted char-counter float-right">' + length + ' karakter</small>';

                $(this).siblings('.char-counter').remove();
                $(this).after(counterHtml);
            });

            // Trigger initial character counter
            $('#muatan').trigger('input');

            // Console logging
            console.log('📋 Form Edit Surat Jalan Ready!');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Current Data:');
            console.log('- No. SJ: <?= $surat_jalan->no_surat_jalan ?>');
            console.log('- Status: <?= $surat_jalan->status ?>');
            console.log('- Customer: <?= addslashes($surat_jalan->customer) ?>');
            console.log('- Driver: <?= addslashes($surat_jalan->nama_driver) ?>');
            console.log('- Unit: <?= addslashes($surat_jalan->no_polisi) ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('🔥 EMAIL FEATURES:');
            console.log('- Current email: <?= $surat_jalan->customer_email ?? "NOT SET" ?>');
            console.log('- Auto-search available');
            console.log('- Email validation active');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('🔍 Search Features:');
            console.log('   • Multi-keyword search enabled');
            console.log('   • Example: "Gonusa Cikarang"');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Shortcuts:');
            console.log('- Ctrl + S : Submit form');
            console.log('- ESC : Cancel');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('✅ Real-time calculation active');
            console.log('✅ AJAX search enabled');
            console.log('✅ Form validation ready');
            console.log('✅ Email auto-fill system active');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });

        // Global format functions
        function formatRupiah(angka) {
            return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
        }

        function formatNumber(num) {
            return Math.round(num).toLocaleString('id-ID');
        }
    </script>
</body>

</html>
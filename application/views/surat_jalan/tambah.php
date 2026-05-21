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
            border-left: 4px solid #4e73df;
        }

        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .preview-badge {
            font-size: 1.2rem;
            padding: 10px 20px;
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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

        /* 🔥 Highlight matched keywords in Select2 dropdown */
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
            background-color: #667eea;
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
                            <i class="fas fa-plus-circle text-primary"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('surat_jalan') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Form -->
                    <form action="<?= base_url('surat_jalan/proses_tambah') ?>" method="post" id="suratJalanForm"
                        enctype="multipart/form-data">

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-lg-8">

                                <!-- Preview No. Surat Jalan -->
                                <div class="card shadow mb-4">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted mb-2">No. Surat Jalan (Preview)</h6>
                                        <span class="badge badge-primary preview-badge" id="noSJPreview">
                                            <?= $no_surat_jalan_preview ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">Nomor akan di-generate otomatis saat disimpan</small>
                                    </div>
                                </div>

                                <!-- Basic Info -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3"
                                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
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
                                                        value="<?= date('Y-m-d') ?>" required>
                                                    <small class="text-muted">Tanggal surat jalan dibuat</small>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Status
                                                    </label>
                                                    <select name="status" id="status" class="form-control">
                                                        <option value="draft">Draft</option>
                                                        <option value="scheduled">Scheduled</option>
                                                    </select>
                                                    <small class="text-muted">Status awal surat jalan</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" id="jamBerangkatSection" style="display: none;">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Jam Berangkat (Optional)
                                                    </label>
                                                    <input type="time" name="jam_berangkat" id="jam_berangkat"
                                                        class="form-control">
                                                    <small class="text-muted">Untuk status scheduled</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rute Selection -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3"
                                        style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-route"></i> Pilih Rute
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                Rute <span class="text-danger">*</span>
                                            </label>
                                            <select name="kode_rute" id="kode_rute" class="form-control select2-ajax"
                                                required>
                                                <option value="">-- Ketik untuk mencari rute --</option>
                                            </select>
                                            <small class="text-muted">
                                                <i class="fas fa-search"></i> Ketik nama customer, origin, atau
                                                destinasi untuk mencari
                                            </small>
                                        </div>

                                        <!-- Rute Info Display (SAMA seperti sebelumnya) -->
                                        <div id="ruteInfoDisplay" style="display: none;">
                                            <div class="info-box">
                                                <h6 class="font-weight-bold mb-3">
                                                    <i class="fas fa-info-circle"></i> Informasi Rute
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="mb-2">
                                                            <strong>Customer:</strong> <span
                                                                id="displayCustomer">-</span>
                                                        </p>
                                                        <p class="mb-2">
                                                            <strong>Service:</strong>
                                                            <span class="badge badge-info" id="displayService">-</span>
                                                        </p>
                                                        <p class="mb-2">
                                                            <strong>SLA:</strong>
                                                            <span class="badge badge-warning" id="displaySLA">-</span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-2">
                                                            <strong>Origin:</strong><br>
                                                            <i class="fas fa-map-marker-alt text-success"></i>
                                                            <span id="displayOrigin">-</span>
                                                        </p>
                                                        <p class="mb-2">
                                                            <strong>Destinasi:</strong>
                                                        </p>
                                                        <ul class="destination-list" id="displayDestinations">
                                                            <!-- Destinations will be inserted here -->
                                                        </ul>
                                                    </div>
                                                </div>
                                                <hr>
                                                <p class="mb-0">
                                                    <strong>Biaya Sewa:</strong>
                                                    <span class="text-primary nominal-display" id="displayBiayaSewa">Rp
                                                        0</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 🔥 NEW: Customer Email Section -->
                                <div class="card shadow mb-4" id="customer-email-section" style="display: none;">
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
                                                        class="form-control" placeholder="finance@company.com" required>
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
                                                        placeholder="pic@company.com">
                                                    <small class="form-text text-muted">
                                                        <i class="fas fa-user"></i> Email PIC untuk komunikasi langsung
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Auto-fill Success Message -->
                                        <div id="email-autofill-info" class="alert alert-success"
                                            style="display: none;">
                                            <i class="fas fa-check-circle"></i>
                                            <strong>Email Ditemukan!</strong>
                                            <p class="mb-0 small">
                                                Email otomatis terisi dari database customer.
                                                <span class="text-muted">Anda bisa mengubahnya jika berbeda.</span>
                                            </p>
                                        </div>

                                        <!-- Manual Input Warning -->
                                        <div id="email-manual-warning" class="alert alert-warning"
                                            style="display: none;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Email Tidak Ditemukan!</strong>
                                            <p class="mb-0 small">
                                                Customer ini belum terdaftar di database atau belum memiliki email.
                                                <strong>Silakan isi email secara manual.</strong>
                                            </p>
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
                                                                data-sim="<?= htmlspecialchars($driver->sim) ?>">
                                                                <?= htmlspecialchars($driver->nama_driver) ?> |
                                                                NIK: <?= htmlspecialchars($driver->nik) ?> |
                                                                SIM: <?= htmlspecialchars($driver->sim) ?>
                                                            </option>
                                                        <?php endforeach ?>
                                                    </select>
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle"></i>
                                                        <?= count($drivers) ?> driver tersedia
                                                    </small>
                                                </div>

                                                <!-- Driver Info Display -->
                                                <div id="driverInfoDisplay" style="display: none;">
                                                    <div class="info-box success">
                                                        <p class="mb-1"><strong>Nama:</strong> <span
                                                                id="displayDriverNama">-</span></p>
                                                        <p class="mb-1"><strong>NIK:</strong> <span
                                                                id="displayDriverNIK">-</span></p>
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
                                                                data-tonase="<?= $unit->tonase ?>">
                                                                <?= htmlspecialchars($unit->no_polisi) ?> |
                                                                <?= htmlspecialchars($unit->tipe_unit) ?> |
                                                                <?= htmlspecialchars($unit->tipe_box) ?> |
                                                                <?= $unit->tonase ?> Ton
                                                            </option>
                                                        <?php endforeach ?>
                                                    </select>
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle"></i>
                                                        <?= count($units) ?> unit tersedia
                                                    </small>
                                                </div>

                                                <!-- Unit Info Display -->
                                                <div id="unitInfoDisplay" style="display: none;">
                                                    <div class="info-box success">
                                                        <p class="mb-1"><strong>No. Polisi:</strong> <span
                                                                id="displayUnitNopol">-</span></p>
                                                        <p class="mb-1"><strong>Tipe:</strong> <span
                                                                id="displayUnitTipe">-</span></p>
                                                        <p class="mb-1"><strong>Box:</strong> <span
                                                                id="displayUnitBox">-</span></p>
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
                                                required></textarea>
                                            <small class="text-muted">Deskripsi barang yang akan dikirim</small>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Tonase Aktual (Ton)
                                                    </label>
                                                    <input type="number" name="tonase_aktual" id="tonase_aktual"
                                                        class="form-control" step="0.01" placeholder="0.00">
                                                    <small class="text-muted">Berat barang dalam ton</small>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        Kubikasi Aktual (m³)
                                                    </label>
                                                    <input type="number" name="kubikasi_aktual" id="kubikasi_aktual"
                                                        class="form-control" step="0.01" placeholder="0.00">
                                                    <small class="text-muted">Volume barang dalam m³</small>
                                                </div>
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
                                                placeholder="Catatan tambahan (optional)"></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold">
                                                Foto Surat Jalan (Optional)
                                            </label>
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
                                                <span class="nominal-display" id="summaryBiayaSewa">Rp 0</span>
                                            </div>

                                            <div class="grand-total">
                                                <div class="d-flex justify-content-between">
                                                    <h5 class="mb-0 font-weight-bold">TOTAL:</h5>
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
                                                        <li>Biaya sewa dari rute yang dipilih</li>
                                                        <li>Biaya tambahan bisa ditambah setelah trip dimulai</li>
                                                        <li>Total biaya akan dihitung otomatis</li>
                                                    </ul>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Info Card -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 bg-gradient-info text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-lightbulb"></i> Tips
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0">
                                            <li class="mb-2">
                                                <strong>Draft:</strong> Simpan sebagai draft jika belum dijadwalkan
                                            </li>
                                            <li class="mb-2">
                                                <strong>Scheduled:</strong> Set jadwal keberangkatan untuk persiapan
                                            </li>
                                            <li class="mb-2">
                                                <strong>Driver & Unit:</strong> Hanya menampilkan yang tersedia
                                            </li>
                                            <li class="mb-0">
                                                <strong>SLA:</strong> Target waktu tiba akan dihitung otomatis
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="card shadow mb-4">
                                    <div class="card-body">
                                        <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
                                            <i class="fas fa-save"></i> Simpan Surat Jalan
                                        </button>
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
            // 🔥 Initialize Select2 with AJAX for rute (Multi-keyword search support)
            $('#kode_rute').select2({
                theme: 'bootstrap',
                width: '100%',
                placeholder: '-- Ketik untuk mencari rute (contoh: "Gonusa Cikarang") --',
                minimumInputLength: 2, // User harus ketik min 2 huruf
                allowClear: true,
                ajax: {
                    url: '<?= base_url('surat_jalan/ajax_search_rute') ?>',
                    dataType: 'json',
                    delay: 300, // Delay 300ms sebelum search
                    data: function (params) {
                        return {
                            q: params.term, // Search term (support multi-keyword)
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

                    // Highlight matched keywords
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

                    // Custom display format in dropdown with highlighting
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

                    // Display in selected box (shorter format)
                    return item.customer + ' | ' + item.origin + ' → ' + item.dest1;
                },
                escapeMarkup: function (markup) {
                    return markup; // Allow HTML
                }
            });

            // 🔥 Rute select event (when user picks a rute)
            $('#kode_rute').on('select2:select', function (e) {
                const data = e.params.data;

                if (data.id) {
                    console.log('✅ Rute selected:', data);

                    // Update display
                    $('#displayCustomer').text(data.customer);
                    $('#displayService').text(data.service);
                    $('#displaySLA').text(data.sla);
                    $('#displayOrigin').text(data.origin);
                    $('#displayBiayaSewa').text('Rp ' + formatNumber(data.harga));

                    // Build destinations list
                    let destHTML = '';
                    if (data.dest1) {
                        destHTML += '<li><i class="fas fa-map-pin text-danger"></i> ' + data.dest1 + '</li>';
                    }
                    if (data.dest2) {
                        destHTML += '<li><i class="fas fa-map-pin text-danger"></i> ' + data.dest2 + '</li>';
                    }
                    if (data.dest3) {
                        destHTML += '<li><i class="fas fa-map-pin text-danger"></i> ' + data.dest3 + '</li>';
                    }
                    if (data.dest4) {
                        destHTML += '<li><i class="fas fa-map-pin text-danger"></i> ' + data.dest4 + '</li>';
                    }

                    $('#displayDestinations').html(destHTML);

                    // Update summary
                    $('#summaryBiayaSewa').text('Rp ' + formatNumber(data.harga));
                    $('#summaryTotal').text('Rp ' + formatNumber(data.harga));

                    // Show info box
                    $('#ruteInfoDisplay').slideDown();

                    // ========================================
                    // 🔥 NEW: AUTO-FILL EMAIL FROM CUSTOMER
                    // ========================================

                    if (data.customer) {
                        console.log('🔍 Searching email for customer:', data.customer);

                        // Show email section
                        $('#customer-email-section').slideDown();

                        // Hide previous messages
                        $('#email-autofill-info').hide();
                        $('#email-manual-warning').hide();
                        $('#email-loading').show();

                        // Clear previous email values
                        $('#customer_email').val('').attr('placeholder', 'Mencari email...').removeClass('border-success border-danger');
                        $('#customer_email_pic').val('');

                        // AJAX call to get customer email
                        $.ajax({
                            url: '<?= base_url('surat_jalan/ajax_get_customer_email') ?>',
                            type: 'POST',
                            data: {
                                customer_name: data.customer
                            },
                            dataType: 'json',
                            success: function (response) {
                                console.log('📧 AJAX Response:', response);

                                // Hide loading
                                $('#email-loading').hide();

                                if (response.success && response.email) {
                                    // ✅ Email found - Auto-fill
                                    console.log('✅ Email found:', response.email);

                                    $('#customer_email').val(response.email).addClass('border-success');

                                    if (response.email_pic) {
                                        $('#customer_email_pic').val(response.email_pic);
                                    }

                                    // Show success message
                                    $('#email-autofill-info').slideDown();

                                } else {
                                    // ❌ Email not found - Manual input required
                                    console.log('⚠️ Email not found for:', data.customer);

                                    $('#customer_email')
                                        .val('')
                                        .attr('placeholder', 'Masukkan email customer')
                                        .focus();

                                    $('#customer_email_pic').val('');

                                    // Show warning message
                                    $('#email-manual-warning').slideDown();
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('❌ AJAX Error:', error);
                                console.log('Response Text:', xhr.responseText);

                                // Hide loading
                                $('#email-loading').hide();

                                // Show manual input on error
                                $('#customer_email')
                                    .val('')
                                    .attr('placeholder', 'Masukkan email customer')
                                    .focus();

                                $('#email-manual-warning')
                                    .html('<i class="fas fa-exclamation-triangle"></i> <strong>Error!</strong> ' +
                                        '<p class="mb-0 small">Gagal mencari email. Silakan isi manual.</p>')
                                    .slideDown();
                            }
                        });
                    }
                }
            });

            // 🔥 Rute clear event
            $('#kode_rute').on('select2:clear', function () {
                console.log('🗑️ Rute cleared');

                // Hide rute info
                $('#ruteInfoDisplay').slideUp();
                $('#summaryBiayaSewa').text('Rp 0');
                $('#summaryTotal').text('Rp 0');

                // 🔥 NEW: Hide email section
                $('#customer-email-section').slideUp();
                $('#customer_email').val('').removeClass('border-success border-danger');
                $('#customer_email_pic').val('');
                $('#email-autofill-info').hide();
                $('#email-manual-warning').hide();
                $('#email-loading').hide();
            });

            // 🔥 NEW: Email validation on blur
            $('#customer_email').on('blur', function () {
                const email = $(this).val().trim();

                if (email) {
                    // Simple email regex
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

            // 🔥 NEW: Remove invalid feedback on input
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

            // Status change handler
            $('#status').on('change', function () {
                if ($(this).val() === 'scheduled') {
                    $('#jamBerangkatSection').slideDown();
                } else {
                    $('#jamBerangkatSection').slideUp();
                    $('#jam_berangkat').val('');
                }
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

                    $('#driverInfoDisplay').slideDown();

                    console.log('✅ Driver selected:', nama);
                } else {
                    $('#driverInfoDisplay').slideUp();
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

                    $('#unitInfoDisplay').slideDown();

                    console.log('✅ Unit selected:', nopol);
                } else {
                    $('#unitInfoDisplay').slideUp();
                }
            });

            // 🔥 UPDATED: Form validation (includes email check)
            $('#suratJalanForm').on('submit', function (e) {
                // Check required fields
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
                        '✓ Rute (gunakan search untuk mencari)\n' +
                        '✓ Driver\n' +
                        '✓ Unit\n' +
                        '✓ Muatan');
                    return false;
                }

                // 🔥 NEW: Check email if section is visible
                if ($('#customer-email-section').is(':visible')) {
                    if (!customer_email) {
                        e.preventDefault();
                        alert('⚠️ Email customer harus diisi!\n\nEmail diperlukan untuk notifikasi POD ke customer.');
                        $('#customer_email').focus().addClass('border-danger');
                        return false;
                    }

                    // Email format validation
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(customer_email)) {
                        e.preventDefault();
                        alert('⚠️ Format email tidak valid!\n\nContoh: finance@company.com');
                        $('#customer_email').focus().addClass('border-danger');
                        return false;
                    }
                }

                // Check if scheduled but no jam_berangkat
                const status = $('#status').val();
                const jam_berangkat = $('#jam_berangkat').val();

                if (status === 'scheduled' && !jam_berangkat) {
                    if (!confirm('Status scheduled tapi belum set jam berangkat.\n\nLanjutkan tanpa jam berangkat?')) {
                        e.preventDefault();
                        return false;
                    }
                }

                // Check file size
                const fileInput = $('#fotoSuratJalan')[0];
                if (fileInput.files.length > 0) {
                    const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
                    if (fileSize > 5) {
                        e.preventDefault();
                        alert('⚠️ Ukuran file terlalu besar!\n\nMaksimal 5MB.');
                        return false;
                    }
                }

                // Disable submit button to prevent double submit
                $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                console.log('📤 Form submitted with email:', customer_email);
                return true;
            });

            // Format number helper
            function formatNumber(num) {
                return Math.round(num).toLocaleString('id-ID');
            }

            // Auto hide alerts
            setTimeout(function () {
                $('.alert').fadeOut('slow');
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
                    if (confirm('Batalkan pembuatan surat jalan?')) {
                        window.location.href = '<?= base_url('surat_jalan') ?>';
                    }
                }
            });

            // Warning before leaving page if form is filled
            var formChanged = false;

            $('#suratJalanForm input, #suratJalanForm select, #suratJalanForm textarea').on('change', function () {
                formChanged = true;
            });

            $(window).on('beforeunload', function () {
                if (formChanged) {
                    return 'Data yang diisi akan hilang. Yakin ingin meninggalkan halaman ini?';
                }
            });

            $('#suratJalanForm').on('submit', function () {
                formChanged = false; // Don't show warning on submit
            });

            // Console logging
            console.log('📋 Form Surat Jalan Ready!');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('🔍 Search Features:');
            console.log('   • Single keyword: "Gonusa"');
            console.log('   • Multi keyword: "Gonusa Cikarang"');
            console.log('   • Advanced: "Gonusa Cikarang Hub Medan"');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Available Data:');
            console.log('- Rute: AJAX Search (12,000+ data)');
            console.log('- Driver: <?= count($drivers) ?> available');
            console.log('- Unit: <?= count($units) ?> available');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('🔥 NEW FEATURES:');
            console.log('- Auto-fill email from customer database');
            console.log('- Email validation before submit');
            console.log('- POD notification system ready');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Shortcuts:');
            console.log('- Ctrl + S : Submit form');
            console.log('- ESC : Cancel');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            // Validation hints
            $('#muatan').on('blur', function () {
                const val = $(this).val().trim();
                if (val.length > 0 && val.length < 10) {
                    $(this).addClass('border-warning');
                    if (!$(this).next('.hint-text').length) {
                        $(this).after('<small class="hint-text text-warning">💡 Berikan deskripsi yang lebih detail</small>');
                    }
                } else {
                    $(this).removeClass('border-warning');
                    $(this).next('.hint-text').remove();
                }
            });

            // Tonase validation against unit capacity
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

            // Also trigger validation when unit changes
            $('#unit_id').on('change', function () {
                $('#tonase_aktual').trigger('input');
            });

            // Date validation - cannot be future date
            $('#tanggal').on('change', function () {
                const selectedDate = new Date($(this).val());
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (selectedDate > today) {
                    if (!confirm('Tanggal yang dipilih adalah tanggal masa depan.\n\nLanjutkan?')) {
                        $(this).val('<?= date('Y-m-d') ?>');
                    }
                }
            });

            // If only one driver, select it automatically
            <?php if (count($drivers) == 1): ?>
                $('#driver_id').val('<?= $drivers[0]->id ?>').trigger('change');
                console.log('✅ Auto-selected: Driver (only 1 available)');
            <?php endif; ?>

            // If only one unit, select it automatically
            <?php if (count($units) == 1): ?>
                $('#unit_id').val('<?= $units[0]->id ?>').trigger('change');
                console.log('✅ Auto-selected: Unit (only 1 available)');
            <?php endif; ?>

            // Real-time character counter for muatan
            $('#muatan').on('input', function () {
                const length = $(this).val().length;
                const counterHtml = '<small class="text-muted char-counter float-right">' + length + ' karakter</small>';

                $(this).siblings('.char-counter').remove();
                $(this).after(counterHtml);
            });

            // Show success message placeholder
            console.log('✅ Form validation active');
            console.log('✅ AJAX multi-keyword search enabled');
            console.log('✅ Keyword highlighting enabled');
            console.log('✅ Auto-fill features enabled');
            console.log('✅ Real-time validation ready');
            console.log('✅ Email auto-fill system active');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });

        // Global function for formatting
        function formatRupiah(angka) {
            return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
        }

        // Global format number (used in templateResult)
        function formatNumber(num) {
            return Math.round(num).toLocaleString('id-ID');
        }
    </script>
</body>

</html>
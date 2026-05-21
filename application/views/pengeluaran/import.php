<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .upload-area {
            border: 3px dashed #4e73df;
            border-radius: 10px;
            padding: 50px;
            text-align: center;
            background: #f8f9fc;
            transition: all 0.3s;
            cursor: pointer;
        }

        .upload-area:hover {
            background: #e7e9f4;
            border-color: #224abe;
        }

        .upload-area.dragover {
            background: #d4e3fc;
            border-color: #1e3a8a;
        }

        .file-input {
            display: none;
        }

        .preview-table {
            font-size: 0.9rem;
        }

        .row-error {
            background-color: #f8d7da !important;
        }

        .row-warning {
            background-color: #fff3cd !important;
        }

        .row-success {
            background-color: #d4edda !important;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: #e3e6f0;
            position: relative;
        }

        .step.active {
            background: #4e73df;
            color: white;
            font-weight: bold;
        }

        .step.completed {
            background: #1cc88a;
            color: white;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-file-import text-primary"></i> Import Pengeluaran dari Excel
                        </h1>
                        <a href="<?= base_url('pengeluaran') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Step Indicator -->
                    <div class="step-indicator mb-4">
                        <div class="step active" id="step1">
                            <i class="fas fa-download"></i> 1. Download Template
                        </div>
                        <div class="step" id="step2">
                            <i class="fas fa-edit"></i> 2. Isi Data
                        </div>
                        <div class="step" id="step3">
                            <i class="fas fa-upload"></i> 3. Upload File
                        </div>
                        <div class="step" id="step4">
                            <i class="fas fa-check"></i> 4. Review & Import
                        </div>
                    </div>

                    <!-- Download Template Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-download"></i> Step 1: Download Template Excel
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="text-primary">📋 Template Import Pengeluaran</h5>
                                    <p class="text-muted mb-3">
                                        Template ini sudah dilengkapi dengan:
                                    </p>
                                    <ul class="text-muted">
                                        <li>✅ Format kolom yang benar</li>
                                        <li>✅ Contoh data pengisian</li>
                                        <li>✅ Instruksi lengkap</li>
                                        <li>✅ Referensi akun biaya (COGS & EXPS)</li>
                                        <li>✅ Referensi bank/kas</li>
                                        <li>✅ Referensi vendor</li>
                                    </ul>
                                    <p class="text-danger mb-0">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Penting:</strong> Hapus 3 baris contoh sebelum mengisi data real!
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-file-excel fa-5x text-success mb-3"></i>
                                    <br>
                                    <a href="<?= base_url('pengeluaran/download_template') ?>"
                                        class="btn btn-success btn-lg shadow-sm">
                                        <i class="fas fa-download"></i> Download Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-gradient-danger text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-upload"></i> Step 3: Upload File Excel
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('pengeluaran/proses_import') ?>" method="post"
                                enctype="multipart/form-data" id="formImport">

                                <div class="upload-area" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                                    <h5>Drag & Drop file Excel di sini</h5>
                                    <p class="text-muted">atau</p>
                                    <button type="button" class="btn btn-primary"
                                        onclick="document.getElementById('fileInput').click()">
                                        <i class="fas fa-folder-open"></i> Pilih File
                                    </button>
                                    <input type="file" name="excel_file" id="fileInput" class="file-input"
                                        accept=".xlsx,.xls" required>
                                    <p class="text-muted mt-3 mb-0">
                                        <small>
                                            Format: .xlsx atau .xls | Max: 5MB | Max 500 baris
                                        </small>
                                    </p>
                                </div>

                                <div id="fileInfo" class="mt-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file-excel"></i>
                                        <strong>File dipilih:</strong> <span id="fileName"></span>
                                        <span class="float-right">
                                            <span id="fileSize"></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-danger btn-lg px-5" id="btnUpload" disabled>
                                        <i class="fas fa-upload"></i> Upload & Validasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="card shadow mb-4 border-left-info">
                        <div class="card-body">
                            <h6 class="text-info font-weight-bold">
                                <i class="fas fa-info-circle"></i> Tips Import Excel
                            </h6>
                            <ul class="mb-0 text-muted">
                                <li>Pastikan menggunakan template yang sudah didownload</li>
                                <li>Isi semua kolom yang bertanda bintang (*)</li>
                                <li>Nominal harus angka tanpa titik/koma (contoh: 10000000)</li>
                                <li>Tanggal format: YYYY-MM-DD (contoh: 2025-12-22)</li>
                                <li>Tipe: V untuk Vendor, M untuk Manual</li>
                                <li>Maksimal 500 baris per upload</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        $(document).ready(function () {
            const uploadArea = $('#uploadArea');
            const fileInput = $('#fileInput');
            const fileInfo = $('#fileInfo');
            const btnUpload = $('#btnUpload');

            // Drag & drop handlers
            uploadArea.on('dragover', function (e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            uploadArea.on('dragleave', function (e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });

            uploadArea.on('drop', function (e) {
                e.preventDefault();
                $(this).removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    fileInput[0].files = files;
                    fileInput.trigger('change');
                }
            });

            // File input change
            fileInput.on('change', function () {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const fileSize = (file.size / 1024 / 1024).toFixed(2);

                    $('#fileName').text(file.name);
                    $('#fileSize').text(fileSize + ' MB');
                    fileInfo.slideDown();
                    btnUpload.prop('disabled', false);

                    // Update step
                    $('#step3').addClass('active');
                }
            });

            // Form submit validation
            $('#formImport').on('submit', function (e) {
                const file = fileInput[0].files[0];

                if (!file) {
                    e.preventDefault();
                    alert('Pilih file Excel terlebih dahulu!');
                    return false;
                }

                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert('Ukuran file terlalu besar! Maksimal 5MB.');
                    return false;
                }

                // Check extension
                const ext = file.name.split('.').pop().toLowerCase();
                if (ext !== 'xlsx' && ext !== 'xls') {
                    e.preventDefault();
                    alert('Format file harus .xlsx atau .xls!');
                    return false;
                }

                // Show loading
                btnUpload.html('<i class="fas fa-spinner fa-spin"></i> Uploading...').prop('disabled', true);

                return true;
            });
        });
    </script>
</body>

</html>
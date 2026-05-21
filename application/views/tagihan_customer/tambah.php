<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--bootstrap4 .select2-selection {
            height: calc(1.5em + .75rem + 2px) !important;
            padding: .375rem .75rem;
        }

        .form-section {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #1cc88a;
        }

        .form-section-required {
            border-left: 4px solid #e74a3b;
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

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-plus-circle text-success"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('tagihan_customer') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Info Card -->
                    <div class="card shadow mb-4 border-left-success">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="font-weight-bold text-success mb-2">
                                        <i class="fas fa-info-circle"></i> Mode: SIMPLE (Nominal Only)
                                    </h6>
                                    <p class="text-muted mb-0">
                                        <small>
                                            <i class="fas fa-check-circle text-success"></i> Input hanya nominal
                                            tagihan<br>
                                            <i class="fas fa-check-circle text-success"></i> PPN/PPH dihitung otomatis
                                            saat pemasukan<br>
                                            <i class="fas fa-check-circle text-success"></i> Status: Waiting Payment
                                            (auto)
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Card -->
                    <div class="card shadow">
                        <div class="card-header bg-gradient-success text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-file-invoice"></i> Form Tambah Tagihan Customer
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('tagihan_customer/proses_tambah') ?>" method="post"
                                id="formTagihan">

                                <!-- SECTION 1: Info Customer -->
                                <div class="form-section">
                                    <h6 class="font-weight-bold text-success mb-3">
                                        <i class="fas fa-user"></i> Informasi Customer
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Customer <span class="text-danger">*</span></label>
                                                <select name="customer_id" id="customer_id" class="form-control select2"
                                                    required>
                                                    <option value="">- Pilih Customer -</option>
                                                    <?php foreach ($customers as $c): ?>
                                                        <option value="<?= $c->kode ?>">
                                                            <?= htmlspecialchars($c->nama) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small class="text-muted">Relasi Master Customer</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nama Customer</label>
                                                <input type="text" id="nama_customer_display"
                                                    class="form-control bg-light" readonly
                                                    placeholder="(auto fill dari customer)">
                                                <small class="text-muted">Nama Customer (auto)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 2: Info Tagihan -->
                                <div class="form-section form-section-required">
                                    <h6 class="font-weight-bold text-danger mb-3">
                                        <i class="fas fa-file-alt"></i> Informasi Tagihan
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>No Invoice <span class="text-danger">*</span></label>
                                                <input type="text" name="no_invoice" class="form-control" required
                                                    placeholder="INV-2025-001">
                                                <small class="text-muted">No Invoice Customer</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tanggal <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal" class="form-control"
                                                    value="<?= date('Y-m-d') ?>" required>
                                                <small class="text-muted">Tanggal Invoice</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Bulan Shipment</label>
                                                <select name="bulan_shipment" class="form-control">
                                                    <option value="">- Pilih Bulan -</option>
                                                    <?php
                                                    $bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                                    $current_month = date('n') - 1;
                                                    foreach ($bulan_list as $idx => $bln):
                                                        ?>
                                                        <option value="<?= $bln ?>" <?= $idx == $current_month ? 'selected' : '' ?>>
                                                            <?= $bln ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small class="text-muted">Bulan Shipment</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Deskripsi</label>
                                        <textarea name="deskripsi" class="form-control" rows="2"
                                            placeholder="Deskripsi tagihan (opsional)"></textarea>
                                        <small class="text-muted">Keterangan tambahan</small>
                                    </div>
                                </div>

                                <!-- SECTION 3: Nominal -->
                                <div class="form-section form-section-required">
                                    <h6 class="font-weight-bold text-danger mb-3">
                                        <i class="fas fa-money-bill-wave"></i> Nilai Tagihan
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nominal Tagihan <span class="text-danger">*</span></label>
                                                <input type="text" name="nominal" id="nominal"
                                                    class="form-control text-right font-weight-bold"
                                                    style="font-size: 20px;" required placeholder="0">
                                                <small class="text-muted">Nilai tagihan sebelum pajak</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Status Payment</label>
                                                <input type="text" class="form-control bg-warning font-weight-bold"
                                                    readonly value="Waiting Payment">
                                                <small class="text-muted">Status otomatis (akan update saat
                                                    dibayar)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Catatan:</strong>
                                        <ul class="mb-0">
                                            <li>PPN & PPH akan dihitung otomatis saat membuat pemasukan</li>
                                            <li>Perhitungan menggunakan rate dari master customer</li>
                                            <li>Status akan berubah menjadi "Paid" setelah pemasukan dibuat</li>
                                        </ul>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                        <i class="fas fa-save"></i> Simpan Tagihan
                                    </button>
                                    <a href="<?= base_url('tagihan_customer') ?>" class="btn btn-secondary btn-lg px-5">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: '- Pilih Customer -',
                allowClear: true
            });

            // Auto fill nama customer saat customer dipilih
            $('#customer_id').on('select2:select', function (e) {
                var selected = $(this).find(':selected');
                var nama = selected.text();
                $('#nama_customer_display').val(nama);
            });

            $('#customer_id').on('select2:clear', function (e) {
                $('#nama_customer_display').val('');
            });

            // Format rupiah
            $('#nominal').on('keyup', function () {
                formatRupiah(this);
            });

            function formatRupiah(o) {
                let v = o.value.replace(/\D/g, '');
                v = v.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                o.value = v || '0';
            }

            // ✅ FIXED: Form Validation with double-submit prevention
            var isSubmitting = false;

            $('#formTagihan').on('submit', function (e) {
                // Prevent double submit
                if (isSubmitting) {
                    e.preventDefault();
                    console.log('Form already submitting, blocked duplicate submit');
                    return false;
                }

                var customer_id = $('#customer_id').val();
                var nominal = parseInt($('#nominal').val().replace(/\./g, '') || 0);

                if (!customer_id) {
                    e.preventDefault();
                    alert('⚠️ Customer wajib dipilih!');
                    $('#customer_id').focus();
                    return false;
                }

                if (nominal <= 0) {
                    e.preventDefault();
                    alert('⚠️ Nominal harus lebih besar dari 0!');
                    $('#nominal').focus();
                    return false;
                }

                var confirmMsg = 'Simpan tagihan customer dengan nominal Rp ' + $('#nominal').val() + '?\n\n';
                confirmMsg += 'Status: Waiting Payment\n';
                confirmMsg += 'PPN/PPH akan dihitung saat pemasukan.';

                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                    return false;
                }

                // Mark as submitting
                isSubmitting = true;

                // Disable submit button
                $(this).find('button[type="submit"]').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                console.log('Form submitted with customer_id:', customer_id, 'nominal:', nominal);

                return true;
            });
        });
    </script>
</body>

</html>
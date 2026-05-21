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

        .form-section {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f6c23e;
        }

        .form-section-required {
            border-left: 4px solid #e74a3b;
        }

        .alert-payment {
            background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
                            <i class="fas fa-edit text-warning"></i> <?= $title ?>
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

                    <?php if ($tagihan->status_payment == 'Paid'): ?>
                        <div class="alert-payment">
                            <i class="fas fa-check-circle"></i>
                            <strong>Tagihan Sudah Dibayar!</strong><br>
                            Kode Payment: <strong><?= $tagihan->kode_payment ?></strong><br>
                            <small>Tagihan yang sudah dibayar tidak bisa diubah atau dihapus.</small>
                        </div>
                    <?php endif ?>

                    <!-- Info Card -->
                    <div class="card shadow mb-4 border-left-warning">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-warning mb-2">
                                <i class="fas fa-info-circle"></i> Mode: SIMPLE (Nominal Only)
                            </h6>
                            <p class="text-muted mb-0">
                                <small>
                                    <i class="fas fa-check-circle text-success"></i> Edit hanya nominal tagihan<br>
                                    <i class="fas fa-check-circle text-success"></i> PPN/PPH dihitung saat pemasukan
                                    dibuat/diupdate<br>
                                    <i class="fas fa-check-circle text-success"></i> Status:
                                    <?= $tagihan->status_payment ?>
                                </small>
                            </p>
                        </div>
                    </div>

                    <!-- Form Card -->
                    <div class="card shadow">
                        <div class="card-header bg-gradient-warning text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-file-invoice"></i> Form Ubah Tagihan Customer
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if ($tagihan->status_payment == 'Paid'): ?>
                                <!-- READ ONLY VIEW -->
                                <div class="form-section">
                                    <h6 class="font-weight-bold text-warning mb-3">
                                        <i class="fas fa-user"></i> Informasi Customer
                                    </h6>
                                    <div class="form-group">
                                        <label>Customer</label>
                                        <input type="text" class="form-control"
                                            value="<?= htmlspecialchars($tagihan->nama_customer) ?>" readonly>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h6 class="font-weight-bold text-warning mb-3">
                                        <i class="fas fa-file-alt"></i> Informasi Tagihan
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>No Invoice</label>
                                                <input type="text" class="form-control"
                                                    value="<?= htmlspecialchars($tagihan->no_invoice) ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tanggal</label>
                                                <input type="text" class="form-control"
                                                    value="<?= date('d/m/Y', strtotime($tagihan->tanggal)) ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Bulan Shipment</label>
                                                <input type="text" class="form-control"
                                                    value="<?= $tagihan->bulan_shipment ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Deskripsi</label>
                                        <textarea class="form-control" rows="2"
                                            readonly><?= htmlspecialchars($tagihan->deskripsi) ?></textarea>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h6 class="font-weight-bold text-warning mb-3">
                                        <i class="fas fa-money-bill-wave"></i> Nilai Tagihan
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nominal Tagihan</label>
                                                <input type="text" class="form-control text-right font-weight-bold bg-light"
                                                    style="font-size: 20px;"
                                                    value="Rp <?= number_format($tagihan->nominal, 0, ',', '.') ?>"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Status Payment</label>
                                                <input type="text"
                                                    class="form-control bg-success text-white font-weight-bold"
                                                    value="<?= $tagihan->status_payment ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center">
                                    <a href="<?= base_url('tagihan_customer') ?>" class="btn btn-secondary btn-lg px-5">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>

                            <?php else: ?>
                                <!-- EDITABLE FORM -->
                                <form action="<?= base_url('tagihan_customer/proses_ubah') ?>" method="post"
                                    id="formTagihan">
                                    <input type="hidden" name="id" value="<?= $tagihan->id ?>">

                                    <!-- SECTION 1: Info Customer -->
                                    <div class="form-section">
                                        <h6 class="font-weight-bold text-warning mb-3">
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
                                                            <option value="<?= $c->kode ?>" <?= $c->kode == $tagihan->customer_id ? 'selected' : '' ?>>
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
                                                        value="<?= htmlspecialchars($tagihan->nama_customer) ?>">
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
                                                        placeholder="INV-2025-001"
                                                        value="<?= htmlspecialchars($tagihan->no_invoice) ?>">
                                                    <small class="text-muted">No Invoice Customer</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Tanggal <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal" class="form-control"
                                                        value="<?= $tagihan->tanggal ?>" required>
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
                                                        foreach ($bulan_list as $bln):
                                                            ?>
                                                            <option value="<?= $bln ?>" <?= $tagihan->bulan_shipment == $bln ? 'selected' : '' ?>>
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
                                                placeholder="Deskripsi tagihan (opsional)"><?= htmlspecialchars($tagihan->deskripsi) ?></textarea>
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
                                                        style="font-size: 20px;" required placeholder="0"
                                                        value="<?= number_format($tagihan->nominal, 0, ',', '.') ?>">
                                                    <small class="text-muted">Nilai tagihan sebelum pajak</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Status Payment</label>
                                                    <input type="text" class="form-control bg-warning font-weight-bold"
                                                        readonly value="<?= $tagihan->status_payment ?>">
                                                    <small class="text-muted">Status otomatis (akan update saat
                                                        dibayar)</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Catatan:</strong>
                                            <ul class="mb-0">
                                                <li>PPN & PPH akan dihitung otomatis saat pemasukan dibuat/diupdate</li>
                                                <li>Perhitungan menggunakan rate dari master customer</li>
                                                <li>Status akan berubah menjadi "Paid" setelah pemasukan dibuat</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <hr>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-warning btn-lg px-5 shadow-sm">
                                            <i class="fas fa-save"></i> Update Tagihan
                                        </button>
                                        <a href="<?= base_url('tagihan_customer') ?>" class="btn btn-secondary btn-lg px-5">
                                            <i class="fas fa-times"></i> Batal
                                        </a>
                                    </div>
                                </form>
                            <?php endif ?>
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

            // Form Validation
            $('#formTagihan').on('submit', function (e) {
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

                var confirmMsg = 'Update tagihan customer dengan nominal Rp ' + $('#nominal').val() + '?\n\n';
                confirmMsg += 'PPN/PPH akan dihitung otomatis saat pemasukan dibuat/diupdate.';

                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                    return false;
                }

                return true;
            });
        });
    </script>
</body>

</html>
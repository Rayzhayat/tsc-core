<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .payment-header {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .section-header {
            background: #f8f9fc;
            padding: 10px 15px;
            border-left: 4px solid #1cc88a;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .amount-card {
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fc;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e3e6f0;
        }

        .amount-row:last-child {
            border: none;
            font-size: 1.2rem;
            font-weight: bold;
            padding-top: 15px;
            margin-top: 10px;
            border-top: 2px solid #1cc88a;
        }

        .amount-row.outstanding {
            color: #e74a3b;
        }

        .amount-row.paid {
            color: #1cc88a;
        }

        .payment-method-card {
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method-card:hover {
            border-color: #1cc88a;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .payment-method-card.selected {
            border-color: #1cc88a;
            background-color: #d4edda;
        }

        .preview-image {
            max-width: 300px;
            max-height: 300px;
            border: 2px solid #e3e6f0;
            border-radius: 5px;
            margin-top: 10px;
        }

        .summary-box {
            position: sticky;
            top: 20px;
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
        }

        .summary-row {
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .summary-row:last-child {
            border: none;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .history-table th {
            background: #1cc88a;
            color: white;
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
                            <i class="fas fa-money-bill-wave text-success"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('purchase_order/detail/' . $po->id) ?>" class="btn btn-secondary btn-sm shadow-sm">
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

                    <!-- Header Card -->
                    <div class="payment-header shadow">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-3">
                                    <i class="fas fa-file-invoice"></i> <?= $po->no_po ?>
                                </h3>
                                <p class="mb-2">
                                    <i class="fas fa-building"></i>
                                    <strong>Vendor:</strong> <?= htmlspecialchars($po->vendor_nama) ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-calendar"></i>
                                    <strong>Tanggal PO:</strong> <?= date('d F Y', strtotime($po->tanggal_po)) ?>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-credit-card"></i>
                                    <strong>Payment Terms:</strong> <?= $po->payment_terms ?: '-' ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <h4 class="mb-2">Status:</h4>
                                <?php
                                $badge_color = [
                                    'received' => 'primary',
                                    'partial_received' => 'warning',
                                    'completed' => 'success'
                                ];
                                $color = $badge_color[$po->status] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $color ?>" style="font-size: 1.1rem; padding: 8px 20px;">
                                    <?= strtoupper(str_replace('_', ' ', $po->status)) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <!-- Left Column (Form) -->
                        <div class="col-lg-8">

                            <!-- Amount Summary Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header bg-success text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-calculator"></i> Ringkasan Pembayaran
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="amount-card">
                                        <div class="amount-row">
                                            <span>Total PO:</span>
                                            <strong>Rp <?= number_format($po->total_po, 0, ',', '.') ?></strong>
                                        </div>
                                        <div class="amount-row paid">
                                            <span>Sudah Dibayar:</span>
                                            <strong>Rp <?= number_format($total_paid, 0, ',', '.') ?></strong>
                                        </div>
                                        <div class="amount-row outstanding">
                                            <span>Sisa Pembayaran:</span>
                                            <strong>Rp <?= number_format($remaining, 0, ',', '.') ?></strong>
                                        </div>
                                    </div>

                                    <?php if ($remaining <= 0): ?>
                                        <div class="alert alert-success mb-0">
                                            <i class="fas fa-check-circle"></i>
                                            <strong>PO ini sudah LUNAS!</strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <?php if ($remaining > 0): ?>
                                <form id="paymentForm" method="POST" action="<?= base_url('purchase_order/save_payment') ?>" enctype="multipart/form-data">
                                    <input type="hidden" name="po_id" value="<?= $po->id ?>">

                                    <div class="card shadow mb-4">
                                        <div class="card-body">

                                            <!-- Section: Informasi Pembayaran -->
                                            <div class="section-header">
                                                <i class="fas fa-money-bill-wave"></i> Informasi Pembayaran
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Tanggal Bayar <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal_bayar" class="form-control"
                                                        value="<?= date('Y-m-d') ?>" required>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Jumlah Bayar <span class="text-danger">*</span></label>
                                                    <input type="text" name="jumlah_bayar" id="jumlah_bayar" class="form-control text-right"
                                                        placeholder="0" required>
                                                    <small class="text-muted">
                                                        Max: Rp <?= number_format($remaining, 0, ',', '.') ?>
                                                    </small>
                                                </div>
                                            </div>

                                            <!-- Section: Metode Pembayaran -->
                                            <div class="section-header">
                                                <i class="fas fa-credit-card"></i> Metode Pembayaran
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="payment-method-card" data-method="cash">
                                                        <input type="radio" name="metode_bayar" id="method_cash" value="cash" required>
                                                        <label for="method_cash" class="mb-0 cursor-pointer w-100">
                                                            <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                                                            <h6 class="mt-2 mb-0">Cash</h6>
                                                            <small class="text-muted">Tunai</small>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <div class="payment-method-card" data-method="transfer">
                                                        <input type="radio" name="metode_bayar" id="method_transfer" value="transfer" required>
                                                        <label for="method_transfer" class="mb-0 cursor-pointer w-100">
                                                            <i class="fas fa-university fa-2x text-primary"></i>
                                                            <h6 class="mt-2 mb-0">Transfer Bank</h6>
                                                            <small class="text-muted">Bank Transfer</small>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <div class="payment-method-card" data-method="giro">
                                                        <input type="radio" name="metode_bayar" id="method_giro" value="giro" required>
                                                        <label for="method_giro" class="mb-0 cursor-pointer w-100">
                                                            <i class="fas fa-file-invoice fa-2x text-warning"></i>
                                                            <h6 class="mt-2 mb-0">Giro</h6>
                                                            <small class="text-muted">Bilyet Giro</small>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <div class="payment-method-card" data-method="cek">
                                                        <input type="radio" name="metode_bayar" id="method_cek" value="cek" required>
                                                        <label for="method_cek" class="mb-0 cursor-pointer w-100">
                                                            <i class="fas fa-check fa-2x text-info"></i>
                                                            <h6 class="mt-2 mb-0">Cek</h6>
                                                            <small class="text-muted">Check</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Bank Details (Show if Transfer/Giro/Cek) -->
                                            <div id="bankDetails" style="display: none;">
                                                <div class="section-header">
                                                    <i class="fas fa-university"></i> Detail Bank
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="font-weight-bold">Nama Bank <span class="text-danger">*</span></label>
                                                        <input type="text" name="bank_nama" id="bank_nama" class="form-control"
                                                            placeholder="Contoh: BCA, Mandiri, BNI">
                                                    </div>

                                                    <div class="col-md-6 mb-3">
                                                        <label class="font-weight-bold">No. Rekening</label>
                                                        <input type="text" name="no_rekening" id="no_rekening" class="form-control"
                                                            placeholder="Nomor rekening">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Section: Detail Transaksi -->
                                            <div class="section-header">
                                                <i class="fas fa-clipboard"></i> Detail Transaksi
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">No. Referensi</label>
                                                    <input type="text" name="no_referensi" class="form-control"
                                                        placeholder="No. transaksi / referensi bank">
                                                    <small class="text-muted">Opsional</small>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Bukti Transfer</label>
                                                    <div class="custom-file">
                                                        <input type="file" name="bukti_transfer" id="bukti_transfer"
                                                            class="custom-file-input" accept="image/*,application/pdf">
                                                        <label class="custom-file-label" for="bukti_transfer">Pilih file...</label>
                                                    </div>
                                                    <small class="text-muted">JPG, PNG, PDF - Max 2MB (Opsional)</small>
                                                    <div id="preview_container" style="display: none;">
                                                        <img src="" id="preview_image" class="preview-image" alt="Preview">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="font-weight-bold">Keterangan</label>
                                                    <textarea name="keterangan" class="form-control" rows="3"
                                                        placeholder="Catatan pembayaran (opsional)..."></textarea>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                                        <i class="fas fa-save"></i> Simpan Pembayaran
                                                    </button>
                                                    <a href="<?= base_url('purchase_order/detail/' . $po->id) ?>"
                                                        class="btn btn-secondary btn-lg">
                                                        <i class="fas fa-times"></i> Batal
                                                    </a>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <!-- Payment History -->
                            <?php if (!empty($payment_history)): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-history"></i> Riwayat Pembayaran
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered history-table">
                                                <thead>
                                                    <tr>
                                                        <th width="5%">No</th>
                                                        <th>No. Payment</th>
                                                        <th>Tanggal</th>
                                                        <th class="text-right">Jumlah</th>
                                                        <th>Metode</th>
                                                        <th>No. Referensi</th>
                                                        <th>Bukti</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $no = 1;
                                                    foreach ($payment_history as $payment):
                                                    ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td><strong><?= $payment->no_payment ?></strong></td>
                                                            <td><?= date('d/m/Y', strtotime($payment->tanggal_bayar)) ?></td>
                                                            <td class="text-right">
                                                                <strong>Rp <?= number_format($payment->jumlah_bayar, 0, ',', '.') ?></strong>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $metode_badge = [
                                                                    'cash' => 'success',
                                                                    'transfer' => 'primary',
                                                                    'giro' => 'warning',
                                                                    'cek' => 'info'
                                                                ];
                                                                $badge = $metode_badge[$payment->metode_bayar] ?? 'secondary';
                                                                ?>
                                                                <span class="badge badge-<?= $badge ?>">
                                                                    <?= ucfirst($payment->metode_bayar) ?>
                                                                </span>
                                                            </td>
                                                            <td><?= htmlspecialchars($payment->no_referensi) ?: '-' ?></td>
                                                            <td class="text-center">
                                                                <?php if (!empty($payment->bukti_transfer)): ?>
                                                                    <a href="<?= base_url('uploads/po_payment/' . $payment->bukti_transfer) ?>"
                                                                        target="_blank" class="btn btn-sm btn-info">
                                                                        <i class="fas fa-eye"></i> Lihat
                                                                    </a>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                        <!-- Right Column (Summary) -->
                        <div class="col-lg-4">

                            <!-- Summary Box -->
                            <div class="summary-box shadow">
                                <h5 class="mb-3">
                                    <i class="fas fa-wallet"></i> Ringkasan
                                </h5>

                                <div class="summary-row">
                                    <small>Total PO:</small>
                                    <strong>Rp <?= number_format($po->total_po, 0, ',', '.') ?></strong>
                                </div>

                                <div class="summary-row">
                                    <small>Sudah Dibayar:</small>
                                    <strong>Rp <?= number_format($total_paid, 0, ',', '.') ?></strong>
                                </div>

                                <div class="summary-row">
                                    <small>Sisa Pembayaran:</small>
                                    <strong>Rp <?= number_format($remaining, 0, ',', '.') ?></strong>
                                </div>

                                <?php if ($remaining > 0): ?>
                                    <hr style="border-color: rgba(255,255,255,0.3);">

                                    <div class="summary-row">
                                        <small>Jumlah yang Akan Dibayar:</small>
                                        <strong id="summaryAmount">Rp 0</strong>
                                    </div>

                                    <div class="summary-row">
                                        <small>Sisa Setelah Bayar:</small>
                                        <strong id="summaryRemaining">Rp <?= number_format($remaining, 0, ',', '.') ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Quick Actions -->
                            <div class="card shadow mt-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-bolt"></i> Quick Actions
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($remaining > 0): ?>
                                        <button type="button" class="btn btn-outline-success btn-block mb-2" onclick="setFullPayment()">
                                            <i class="fas fa-check-double"></i> Bayar Lunas
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-block mb-2" onclick="setHalfPayment()">
                                            <i class="fas fa-divide"></i> Bayar 50%
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-block" onclick="setCustomPayment()">
                                            <i class="fas fa-edit"></i> Custom Amount
                                        </button>
                                    <?php else: ?>
                                        <div class="alert alert-success mb-0">
                                            <i class="fas fa-check-circle"></i> <strong>Lunas</strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        $(document).ready(function() {

            const maxAmount = <?= $remaining ?>;

            // ============================================
            // Format Rupiah Input
            // ============================================
            $('#jumlah_bayar').on('keyup', function() {
                formatRupiah(this);
                updateSummary();
            });

            function formatRupiah(input) {
                let value = input.value.replace(/\D/g, '');
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                input.value = value || '0';
            }

            function unformatRupiah(value) {
                return parseInt(value.replace(/\./g, '')) || 0;
            }

            // ============================================
            // Update Summary
            // ============================================
            function updateSummary() {
                const amount = unformatRupiah($('#jumlah_bayar').val());
                const remaining = maxAmount - amount;

                $('#summaryAmount').text('Rp ' + amount.toLocaleString('id-ID'));
                $('#summaryRemaining').text('Rp ' + remaining.toLocaleString('id-ID'));

                // Change color if over max
                if (amount > maxAmount) {
                    $('#summaryRemaining').css('color', '#e74a3b');
                } else {
                    $('#summaryRemaining').css('color', 'white');
                }
            }

            // ============================================
            // Quick Payment Actions
            // ============================================
            window.setFullPayment = function() {
                $('#jumlah_bayar').val(maxAmount.toLocaleString('id-ID'));
                updateSummary();
            };

            window.setHalfPayment = function() {
                const half = Math.round(maxAmount / 2);
                $('#jumlah_bayar').val(half.toLocaleString('id-ID'));
                updateSummary();
            };

            window.setCustomPayment = function() {
                const custom = prompt('Masukkan jumlah pembayaran:', '');
                if (custom) {
                    const amount = parseInt(custom.replace(/\D/g, ''));
                    if (!isNaN(amount) && amount > 0) {
                        $('#jumlah_bayar').val(amount.toLocaleString('id-ID'));
                        updateSummary();
                    }
                }
            };

            // ============================================
            // Payment Method Selection
            // ============================================
            $('input[name="metode_bayar"]').on('change', function() {
                const method = $(this).val();

                // Remove all selected states
                $('.payment-method-card').removeClass('selected');

                // Add selected state to chosen method
                $(this).closest('.payment-method-card').addClass('selected');

                // Show/hide bank details
                if (method === 'transfer' || method === 'giro' || method === 'cek') {
                    $('#bankDetails').slideDown(300);
                    $('#bank_nama').attr('required', true);
                } else {
                    $('#bankDetails').slideUp(300);
                    $('#bank_nama').removeAttr('required');
                    $('#bank_nama').val('');
                    $('#no_rekening').val('');
                }
            });

            // Click on card to select radio
            $('.payment-method-card').on('click', function() {
                $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
            });

            // ============================================
            // File Upload Preview
            // ============================================
            $('#bukti_transfer').on('change', function() {
                const file = this.files[0];

                if (file) {
                    // Validate file size (max 2MB)
                    if (file.size > 2048000) {
                        alert('⚠️ Ukuran file terlalu besar! Maksimal 2MB.');
                        $(this).val('');
                        $('#preview_container').hide();
                        return;
                    }

                    // Update label
                    $(this).next('.custom-file-label').text(file.name);

                    // Show preview for images
                    if (file.type.match('image.*')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#preview_image').attr('src', e.target.result);
                            $('#preview_container').fadeIn();
                        };
                        reader.readAsDataURL(file);
                    } else if (file.type === 'application/pdf') {
                        $('#preview_container').hide();
                    } else {
                        alert('⚠️ File harus berupa gambar (JPG, PNG) atau PDF!');
                        $(this).val('');
                        $('#preview_container').hide();
                    }
                } else {
                    $(this).next('.custom-file-label').text('Pilih file...');
                    $('#preview_container').hide();
                }
            });

            // ============================================
            // Form Validation
            // ============================================
            $('#paymentForm').on('submit', function(e) {
                const amount = unformatRupiah($('#jumlah_bayar').val());
                const method = $('input[name="metode_bayar"]:checked').val();

                // Validate amount
                if (amount <= 0) {
                    e.preventDefault();
                    alert('⚠️ Jumlah pembayaran harus lebih dari 0!');
                    $('#jumlah_bayar').focus();
                    return false;
                }

                if (amount > maxAmount) {
                    e.preventDefault();
                    alert('⚠️ Jumlah pembayaran melebihi sisa pembayaran!\n\nMax: Rp ' + maxAmount.toLocaleString('id-ID'));
                    $('#jumlah_bayar').focus();
                    return false;
                }

                // Validate payment method
                if (!method) {
                    e.preventDefault();
                    alert('⚠️ Pilih metode pembayaran!');
                    return false;
                }

                // Validate bank details if needed
                if ((method === 'transfer' || method === 'giro' || method === 'cek') && !$('#bank_nama').val()) {
                    e.preventDefault();
                    alert('⚠️ Nama bank harus diisi!');
                    $('#bank_nama').focus();
                    return false;
                }

                // Confirm submission
                const confirmation = confirm(
                    'Simpan pembayaran?\n\n' +
                    'Jumlah: Rp ' + amount.toLocaleString('id-ID') + '\n' +
                    'Metode: ' + method.toUpperCase() + '\n' +
                    'Sisa setelah bayar: Rp ' + (maxAmount - amount).toLocaleString('id-ID')
                );

                if (!confirmation) {
                    e.preventDefault();
                    return false;
                }

                // Disable submit button
                $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                console.log('📤 Form Payment submitted');
                return true;
            });

            // ============================================
            // Auto Hide Alerts
            // ============================================
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // ============================================
            // Warning Before Leaving
            // ============================================
            let formChanged = false;

            $('#paymentForm input, #paymentForm select, #paymentForm textarea').on('change', function() {
                formChanged = true;
            });

            $(window).on('beforeunload', function() {
                if (formChanged) {
                    return 'Data yang diinput akan hilang. Yakin ingin meninggalkan halaman ini?';
                }
            });

            $('#paymentForm').on('submit', function() {
                formChanged = false;
            });

            // ============================================
            // Initialize Summary
            // ============================================
            updateSummary();

            // ============================================
            // Console Logging
            // ============================================
            console.log('💰 Form Payment Ready!');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('No. PO: <?= $po->no_po ?>');
            console.log('Status: <?= $po->status ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Total PO: Rp <?= number_format($po->total_po, 0, ',', '.') ?>');
            console.log('Sudah Dibayar: Rp <?= number_format($total_paid, 0, ',', '.') ?>');
            console.log('Sisa: Rp <?= number_format($remaining, 0, ',', '.') ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Payment History: <?= count($payment_history) ?> records');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Features:');
            console.log('✅ Rupiah format input');
            console.log('✅ 4 Payment methods (Cash/Transfer/Giro/Cek)');
            console.log('✅ Quick actions (Full/Half/Custom)');
            console.log('✅ Bank details conditional');
            console.log('✅ File upload with preview');
            console.log('✅ Amount validation');
            console.log('✅ Real-time summary');
            console.log('✅ Payment history table');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });
    </script>
</body>

</html>
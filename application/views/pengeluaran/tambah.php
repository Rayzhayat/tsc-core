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

        .info-tagihan {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
        }

        .form-section {
            background-color: #f8f9fc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #4e73df;
        }

        .form-section-required {
            border-left: 4px solid #e74a3b;
        }

        #pph-info {
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-sm {
            padding: 10px 15px;
            font-size: 0.9rem;
        }
    </style>
</head>

<body class="antialiased">
<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-money-bill-wave text-danger"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('pengeluaran') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Info Card -->
                    <div class="card shadow mb-4 border-left-danger">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="font-weight-bold text-danger mb-2">
                                        <i class="fas fa-info-circle"></i> Mode: ADVANCED (OCAS Support Enabled)
                                    </h6>
                                    <p class="text-muted mb-0">
                                        <small>
                                            <i class="fas fa-check-circle text-success"></i> Pengeluaran dengan potongan
                                            PPH<br>
                                            <i class="fas fa-check-circle text-success"></i> 3-way journal entry (Biaya
                                            + Bank + OCAS)<br>
                                            <i class="fas fa-check-circle text-success"></i> PPH otomatis dicatat ke
                                            akun 51/52 (hutang ke negara)
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Card -->
                    <div class="card shadow">
                        <div class="card-header bg-gradient-danger text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-edit"></i> Form Tambah Pengeluaran
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('pengeluaran/proses_tambah') ?>" method="post"
                                id="formPengeluaran">

                                <!-- Radio Tipe Pengeluaran -->
                                <div class="form-section"
                                    style="background: linear-gradient(135deg, #667eea22 0%, #764ba222 100%);">
                                    <h6 class="font-weight-bold text-primary mb-3">
                                        <i class="fas fa-list-ul"></i> Pilih Tipe Pengeluaran
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="tipeVendor" name="tipe_pengeluaran"
                                                    class="custom-control-input" value="vendor" checked>
                                                <label class="custom-control-label font-weight-bold text-primary"
                                                    for="tipeVendor">
                                                    <i class="fas fa-building"></i> Pengeluaran Vendor
                                                    <br><small class="text-muted">Pembayaran ke vendor/supplier (Reff:
                                                        V-xxxxx)</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="tipeNonVendor" name="tipe_pengeluaran"
                                                    class="custom-control-input" value="non_vendor">
                                                <label class="custom-control-label font-weight-bold text-success"
                                                    for="tipeNonVendor">
                                                    <i class="fas fa-hand-holding-usd"></i> Pengeluaran Manual
                                                    <br><small class="text-muted">Biaya operasional/non-vendor (Reff:
                                                        M-xxxxx)</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 1: Info Wajib -->
                                <div class="form-section form-section-required">
                                    <h6 class="font-weight-bold text-danger mb-3">
                                        <i class="fas fa-exclamation-circle"></i> Informasi Wajib
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Postingan Biaya <span class="text-danger">*</span></label>
                                                <select name="postingan_biaya" class="form-control" required>
                                                    <option value="">- Pilih Postingan Biaya -</option>
                                                    <?php if (!empty($akun_biaya)): ?>
                                                      <?php foreach($akun_biaya as $akun): ?>
                                                        <?php 
                                                        $is_parent = in_array($akun->kode_perkiraan, ['30', '40']);
                                                        ?>
                                                        <option value="<?= $akun->kode_perkiraan ?>"
                                                                <?= $is_parent ? 'disabled style="font-weight:bold; background:#f0f0f0; color:#999;"' : '' ?>>
                                                                <?= $is_parent ? '▼ ' : '    ' ?><?= $akun->kode_perkiraan ?> - <?= htmlspecialchars($akun->nama) ?><?= $is_parent ? ' (Header)' : '' ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <option value="302">302 - Biaya Sewa Vendor Rilase</option>
                                                        <option value="401">401 - Biaya Transport Operasional</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tanggal <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal" class="form-control"
                                                    value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Reff No Preview</label>
                                                <input type="text" id="reff_preview"
                                                    class="form-control bg-gradient-primary text-white font-weight-bold text-center"
                                                    readonly value="V-00001">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bulan Shipment <span class="text-danger">*</span></label>
                                                <select name="bulan_shipment" id="bulan_shipment" class="form-control"
                                                    required>
                                                    <option value="">- Pilih Bulan -</option>
                                                    <option value="Januari">Januari</option>
                                                    <option value="Februari">Februari</option>
                                                    <option value="Maret">Maret</option>
                                                    <option value="April">April</option>
                                                    <option value="Mei">Mei</option>
                                                    <option value="Juni">Juni</option>
                                                    <option value="Juli">Juli</option>
                                                    <option value="Agustus">Agustus</option>
                                                    <option value="September">September</option>
                                                    <option value="Oktober">Oktober</option>
                                                    <option value="November">November</option>
                                                    <option value="Desember" selected>Desember</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bayar dari Akun <span class="text-danger">*</span></label>
                                                <select name="akun_bank_id" class="form-control" required>
                                                    <option value="">- Pilih Bank/Kas -</option>
                                                    <?php foreach ($akun_bank as $akun): ?>
                                                        <option value="<?= $akun->id ?>">
                                                            <?= $akun->nama ?> (Saldo: Rp
                                                            <?= number_format($akun->saldo_awal, 0, ',', '.') ?>)
                                                        </option>
                                                    <?php endforeach ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>
                                            Deskripsi Rincian
                                            <span id="descRequired" style="display:none;" class="text-danger">*</span>
                                        </label>
                                        <textarea name="deskripsi_rincian" id="deskripsi_rincian" class="form-control"
                                            rows="2" placeholder="Contoh: Bayar sewa vendor bulan Desember"></textarea>
                                        <small class="text-muted" id="descHelper">Opsional untuk pengeluaran
                                            vendor</small>
                                    </div>
                                </div>

                                <!-- SECTION 2: Info Vendor -->
                                <!-- SECTION 2: Info Vendor -->
                                <div class="form-section" id="vendorSection">
                                    <h6 class="font-weight-bold text-primary mb-3">
                                        <i class="fas fa-building"></i> Informasi Vendor
                                    </h6>

                                    <div class="form-group">
                                        <label>Vendor</label>
                                        <select name="vendor_id" id="vendor_id" class="form-control select2">
                                            <option value="">- Pilih Vendor -</option>
                                            <?php foreach ($vendors as $v): ?>
                                                <?php
                                                $ppn_value = 0;
                                                if (preg_match('/(\d+(\.\d+)?)/', $v->ppn_vendor ?? '', $matches)) {
                                                    $ppn_value = $matches[1];
                                                }

                                                $pph_value = 0;
                                                if (preg_match('/(\d+(\.\d+)?)/', $v->pph_vendor ?? '', $matches)) {
                                                    $pph_value = $matches[1];
                                                }
                                                ?>
                                                <option value="<?= $v->kode ?>" data-ppn="<?= $ppn_value ?>"
                                                    data-pph="<?= $pph_value ?>">
                                                    <?= htmlspecialchars($v->nama_vendor) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Pilih vendor untuk auto-fill PPN/PPH rate</small>
                                    </div>

                                    <!-- ✅ FIXED: Tagihan Vendor Dropdown -->
                                    <div class="form-group">
                                        <label>Tagihan Vendor</label>
                                        <select name="tagihan_id" id="tagihan_id" class="form-control select2">
                                            <option value="">- Tidak ada tagihan (input manual) -</option>
                                            <?php if (!empty($tagihan_unpaid)): ?>
                                                <?php foreach ($tagihan_unpaid as $t): ?>
                                                    <?php
                                                    // ✅ Extract from ppn_vendor & pph_vendor (TEXT)
                                                    $ppn_value = 0;
                                                    if (!empty($t->ppn_vendor) && preg_match('/(\d+(?:\.\d+)?)/', $t->ppn_vendor, $matches)) {
                                                        $ppn_value = $matches[1];
                                                    }
                                                    
                                                    $pph_value = 0;
                                                    if (!empty($t->pph_vendor) && preg_match('/(\d+(?:\.\d+)?)/', $t->pph_vendor, $matches)) {
                                                        $pph_value = $matches[1];
                                                    }
                                                    ?>
                                                    <option value="<?= $t->id ?>" 
                                                            data-vendor="<?= $t->vendor_id ?>"
                                                            data-invoice="<?= $t->no_invoice ?>" 
                                                            data-nominal="<?= $t->nominal ?>"
                                                            data-bulan="<?= $t->bulan_shipment ?>"
                                                            data-ppn="<?= $ppn_value ?>"
                                                            data-pph="<?= $pph_value ?>"
                                                            class="tagihan-option vendor-<?= $t->vendor_id ?>">
                                                        <?= htmlspecialchars($t->nama_vendor) ?> | 
                                                        <?= htmlspecialchars($t->no_invoice) ?> | 
                                                        Rp <?= number_format($t->nominal, 0, ',', '.') ?> | 
                                                        <?= date('d/m/Y', strtotime($t->invoice_date)) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <option disabled>Tidak ada tagihan vendor yang belum dibayar</option>
                                            <?php endif ?>
                                        </select>
                                        <small class="text-muted">Pilih tagihan untuk auto-fill data</small>
                                    </div>

                                    <div class="alert alert-info info-tagihan" id="info-tagihan">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Tagihan dipilih!</strong> Data invoice & nominal akan terisi otomatis.
                                    </div>

                                    <div class="form-group">
                                        <label>No Invoice Vendor</label>
                                        <input type="text" name="no_invoice_vendor" id="no_invoice_vendor"
                                            class="form-control" placeholder="INV-VENDOR-001">
                                    </div>
                                </div>

                                <!-- SECTION 3: Perhitungan -->
                                <div class="form-section form-section-required">
                                    <h6 class="font-weight-bold text-danger mb-3">
                                        <i class="fas fa-calculator"></i> Perhitungan Biaya (OCAS Support)
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nominal <span class="text-danger">*</span></label>
                                                <input type="text" name="nominal" id="nominal"
                                                    class="form-control text-right font-weight-bold" placeholder="0"
                                                    required>
                                                <small class="text-muted">Nilai tagihan/biaya</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPN <small class="text-success">(+)</small></label>
                                                <input type="text" name="ppn" id="ppn" class="form-control text-right"
                                                    placeholder="0" value="0">
                                                <small class="text-muted">Rate: <span id="ppn_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPH <small class="text-danger">(-)</small></label>
                                                <input type="text" name="pph" id="pph" class="form-control text-right"
                                                    placeholder="0" value="0">
                                                <small class="text-muted">Rate: <span id="pph_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Total Bayar</label>
                                                <input type="text" id="total_bayar"
                                                    class="form-control text-right font-weight-bold bg-light text-danger"
                                                    style="font-size: 18px;" readonly placeholder="0">
                                                <small class="text-muted">Yang diterima vendor</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="fas fa-lightbulb"></i>
                                        <strong>Formula OCAS:</strong>
                                        Total Biaya = Nominal + PPN |
                                        Total Bayar = Total Biaya - PPH
                                        <br>
                                        <small>
                                            • Vendor menerima: <strong>Total Bayar</strong><br>
                                            • PPH ditahan: Dicatat ke akun <strong>51 (PPH 23)</strong> atau <strong>52
                                                (PPH 4(2))</strong><br>
                                            • PPH harus disetor ke negara nanti
                                        </small>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-danger btn-lg px-5 shadow-sm">
                                        <i class="fas fa-save"></i> Simpan Pengeluaran
                                    </button>
                                    <a href="<?= base_url('pengeluaran') ?>" class="btn btn-secondary btn-lg px-5">
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
                allowClear: true,
                placeholder: '- Pilih -'
            });

            // Toggle Vendor Section
            function toggleVendorSection() {
                if ($('#tipeNonVendor').is(':checked')) {
                    $('#vendorSection').slideUp(300);
                    $('#vendor_id').prop('required', false).val('').trigger('change');
                    $('#tagihan_id').val('').trigger('change');
                    $('#reff_preview').val('M-00001').removeClass('bg-gradient-primary').addClass('bg-gradient-success');
                    $('#descRequired').show();
                    $('#deskripsi_rincian').prop('required', true);
                    $('#descHelper').html('<strong class="text-danger">WAJIB</strong> untuk pengeluaran manual');
                    $('#ppn, #pph').val('0');
                    $('#ppn_rate, #pph_rate').text('0');
                    $('#pph-info').remove();
                    hitungTotal();
                } else {
                    $('#vendorSection').slideDown(300);
                    $('#reff_preview').val('V-00001').removeClass('bg-gradient-success').addClass('bg-gradient-primary');
                    $('#descRequired').hide();
                    $('#deskripsi_rincian').prop('required', false);
                    $('#descHelper').text('Opsional untuk pengeluaran vendor');
                }
            }

            $('input[name="tipe_pengeluaran"]').on('change', toggleVendorSection);
            toggleVendorSection();

            // Vendor change - load pajak rate
            $('#vendor_id').on('select2:select', function () {
                var selected = $(this).find(':selected');
                var ppn = parseFloat(selected.data('ppn')) || 0;
                var pph = parseFloat(selected.data('pph')) || 0;

                $('#ppn_rate').text(ppn);
                $('#pph_rate').text(pph);

                hitungPajak();
            });

            $('#vendor_id').on('select2:clear', function () {
                $('#ppn_rate').text('0');
                $('#pph_rate').text('0');
                $('#ppn').val('0');
                $('#pph').val('0');
                $('#pph-info').remove();
                hitungTotal();
            });

            // ✅ FIXED: Tagihan change - auto fill with PPN/PPH
            $('#tagihan_id').on('select2:select', function () {
                var selected = $(this).find(':selected');
                var vendorId = selected.data('vendor');
                var invoice = selected.data('invoice');
                var nominal = selected.data('nominal');
                var bulan = selected.data('bulan');
                
                // ✅ Get PPN/PPH from tagihan
                var ppn_rate = parseFloat(selected.data('ppn')) || 0;
                var pph_rate = parseFloat(selected.data('pph')) || 0;

                // Set vendor
                $('#vendor_id').val(vendorId).trigger('change');

                // Set data
                $('#no_invoice_vendor').val(invoice);
                $('#bulan_shipment').val(bulan);
                $('#nominal').val(formatNumber(nominal));

                // ✅ Set PPN/PPH rate
                $('#ppn_rate').text(ppn_rate);
                $('#pph_rate').text(pph_rate);

                // Show info
                $('#info-tagihan').slideDown(300);

                // ✅ Hitung pajak
                setTimeout(function () {
                    hitungPajak();
                }, 100);
            });

            $('#tagihan_id').on('select2:clear', function () {
                $('#info-tagihan').slideUp(300);
                $('#no_invoice_vendor').val('');
            });

            // Nominal change
            $('#nominal').on('keyup', function () {
                formatInputNumber(this);
                hitungPajak();
            });

            // Manual input PPN/PPH
            $('#ppn, #pph').on('keyup', function () {
                formatInputNumber(this);
                hitungTotal();
            });

            // Fungsi hitung pajak otomatis
            function hitungPajak() {
                var nominal = parseNumber($('#nominal').val());
                var ppn_rate = parseFloat($('#ppn_rate').text()) || 0;
                var pph_rate = parseFloat($('#pph_rate').text()) || 0;

                if (nominal > 0 && (ppn_rate > 0 || pph_rate > 0)) {
                    var ppn = Math.round(nominal * ppn_rate / 100);
                    var pph = Math.round(nominal * pph_rate / 100);

                    $('#ppn').val(formatNumber(ppn));
                    $('#pph').val(formatNumber(pph));
                }

                hitungTotal();
            }

            // Fungsi hitung total bayar (OCAS)
            function hitungTotal() {
                var nominal = parseNumber($('#nominal').val());
                var ppn = parseNumber($('#ppn').val());
                var pph = parseNumber($('#pph').val());

                var total_biaya = nominal + ppn;
                var total = total_biaya - pph;

                $('#total_bayar').val(formatNumber(total));

                if (pph > 0) {
                    var breakdown = '<small class="text-muted d-block mt-1">';
                    breakdown += '<i class="fas fa-calculator"></i> Rincian: ';
                    breakdown += 'Nominal <strong>' + formatNumber(nominal) + '</strong>';
                    if (ppn > 0) breakdown += ' + PPN <strong>' + formatNumber(ppn) + '</strong>';
                    breakdown += ' - PPH <strong>' + formatNumber(pph) + '</strong>';
                    breakdown += ' = <strong class="text-danger">' + formatNumber(total) + '</strong>';
                    breakdown += '</small>';

                    if ($('#calc-breakdown').length === 0) {
                        $('#total_bayar').after('<div id="calc-breakdown">' + breakdown + '</div>');
                    } else {
                        $('#calc-breakdown').html(breakdown);
                    }
                } else {
                    $('#calc-breakdown').remove();
                }

                if (pph > 0) {
                    $('#pph').trigger('change');
                }
            }

            // Show OCAS info when PPH entered
            $('#pph').on('change', function () {
                var pph = parseNumber($(this).val());
                var nominal = parseNumber($('#nominal').val());

                if (pph > 1000 && nominal > 0) {
                    var rate = (pph / nominal * 100).toFixed(2);
                    var pph_type = '';
                    var akun_kode = '';
                    var akun_nama = '';

                    if (rate >= 1.5 && rate <= 2.5) {
                        pph_type = 'PPH 23';
                        akun_kode = '51';
                        akun_nama = 'PPH 23 Memotong 2%';
                    } else if (rate >= 0.3 && rate <= 0.7) {
                        pph_type = 'PPH 4(2)';
                        akun_kode = '52';
                        akun_nama = 'PPH 4(2) Memotong 0.5%';
                    } else {
                        pph_type = 'PPH (custom rate)';
                        akun_kode = '51';
                        akun_nama = 'PPH 23 Memotong (default)';
                    }

                    var info = '<div class="alert alert-info alert-sm mt-2" id="pph-info">';
                    info += '<i class="fas fa-info-circle"></i> ';
                    info += '<strong>' + pph_type + '</strong> (Rate: ' + rate + '%) ';
                    info += '<br><small>';
                    info += '• Akan dicatat ke: <strong>Akun ' + akun_kode + '</strong> (' + akun_nama + ')<br>';
                    info += '• Vendor menerima: <strong>Rp ' + formatNumber(parseNumber($('#total_bayar').val())) + '</strong><br>';
                    info += '• PPH ditahan: <strong>Rp ' + formatNumber(pph) + '</strong> (harus disetor ke negara nanti)<br>';
                    info += '• Journal: DEBIT Biaya | KREDIT Bank + KREDIT OCAS (' + akun_kode + ')';
                    info += '</small></div>';

                    $('#pph-info').remove();
                    $('#pph').closest('.form-group').after(info);
                } else {
                    $('#pph-info').remove();
                }
            });

            // Format input number
            function formatInputNumber(input) {
                var value = $(input).val().replace(/\./g, '');
                if (value !== '') {
                    $(input).val(formatNumber(value));
                }
            }

            // Format angka
            function formatNumber(num) {
                var n = parseInt(num) || 0;
                return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // Parse number
            function parseNumber(str) {
                return parseFloat(str.replace(/\./g, '')) || 0;
            }

            // Form Validation
            $('#formPengeluaran').on('submit', function (e) {
                var tipe = $('input[name="tipe_pengeluaran"]:checked').val();
                var bulan = $('#bulan_shipment').val();
                var desc = $('#deskripsi_rincian').val().trim();
                var nominal = parseNumber($('#nominal').val());

                if (!bulan) {
                    e.preventDefault();
                    alert('⚠️ Bulan Shipment wajib diisi!');
                    $('#bulan_shipment').focus();
                    return false;
                }

                if (nominal <= 0) {
                    e.preventDefault();
                    alert('⚠️ Nominal harus lebih besar dari 0!');
                    $('#nominal').focus();
                    return false;
                }

                if (tipe === 'non_vendor' && !desc) {
                    e.preventDefault();
                    alert('⚠️ Deskripsi Rincian wajib diisi untuk pengeluaran Manual!');
                    $('#deskripsi_rincian').focus();
                    return false;
                }

                var pph = parseNumber($('#pph').val());
                var confirmMsg = 'Simpan pengeluaran dengan total Rp ' + formatNumber(parseNumber($('#total_bayar').val())) + '?';

                if (pph > 0) {
                    confirmMsg += '\n\nPPH Rp ' + formatNumber(pph) + ' akan dicatat ke OCAS (hutang ke negara).';
                }

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
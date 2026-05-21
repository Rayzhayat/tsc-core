<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        .select2-container { width: 100% !important; }
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
                            <i class="fas fa-plus-circle text-danger"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('pengeluaran') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Toggle Tipe Pengeluaran -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-gradient-danger text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-random"></i> Pilih Tipe Pengeluaran
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="tipeVendor" name="tipe_pengeluaran" 
                                               class="custom-control-input" value="vendor" checked>
                                        <label class="custom-control-label" for="tipeVendor">
                                            <i class="fas fa-truck text-primary"></i> 
                                            <strong>Pengeluaran Vendor</strong>
                                            <br><small class="text-muted">Pembayaran kepada vendor/supplier (Reff: V)</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="tipeNonVendor" name="tipe_pengeluaran" 
                                               class="custom-control-input" value="non_vendor">
                                        <label class="custom-control-label" for="tipeNonVendor">
                                            <i class="fas fa-receipt text-success"></i> 
                                            <strong>Pengeluaran Manual</strong>
                                            <br><small class="text-muted">Biaya operasional umum (Reff: M)</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Card -->
                    <div class="card shadow">
                        <div class="card-header bg-gradient-danger text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-file-invoice-dollar"></i> Form Tambah Pengeluaran
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('pengeluaran/proses_tambah') ?>" method="post" id="formPengeluaran">
                                
                                <!-- SECTION 1: Info Wajib -->
                                <div class="form-section form-section-required">
                                    <h6 class="font-weight-bold text-danger mb-3">
                                        <i class="fas fa-exclamation-circle"></i> Informasi Wajib
                                    </h6>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Postingan Biaya <span class="text-danger">*</span></label>
                                                <!-- 🔥 FIXED: Use $akun_biaya from controller -->
                                                <select name="postingan_biaya" class="form-control" required>
                                                    <option value="">- Pilih Postingan Biaya -</option>
                                                    <?php if (!empty($akun_biaya)): ?>
                                                        <?php foreach($akun_biaya as $akun): ?>
                                                            <option value="<?= $akun->kode_perkiraan ?>">
                                                                <?= $akun->kode_perkiraan ?> - <?= htmlspecialchars($akun->nama) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <!-- Fallback jika data kosong -->
                                                        <option value="302">302 - Biaya Vendor A</option>
                                                        <option value="303">303 - Biaya Vendor B</option>
                                                        <option value="401">401 - Biaya Operasional</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tanggal <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Preview Reff</label>
                                                <input type="text" id="reff_preview" class="form-control bg-gradient-primary text-white font-weight-bold text-center" readonly value="V-00001">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bulan Shipment <span class="text-danger">*</span></label>
                                                <select name="bulan_shipment" id="bulan_shipment" class="form-control" required>
                                                    <option value="">- Pilih Bulan -</option>
                                                    <?php 
                                                    $bulan_list = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                                    $current_month = date('n') - 1;
                                                    foreach ($bulan_list as $idx => $bln): 
                                                    ?>
                                                        <option value="<?= $bln ?>" <?= $idx == $current_month ? 'selected' : '' ?>>
                                                            <?= $bln ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bayar dari Akun <span class="text-danger">*</span></label>
                                                <select name="akun_bank_id" class="form-control" required>
                                                    <option value="">- Pilih Bank/Kas -</option>
                                                    <?php foreach($akun_bank as $akun): ?>
                                                        <option value="<?= $akun->id ?>">
                                                            <?= $akun->nama ?> (Saldo: Rp <?= number_format($akun->saldo_awal, 0, ',', '.') ?>)
                                                        </option>
                                                    <?php endforeach ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Deskripsi Rincian <span id="descRequired" class="text-danger">*</span></label>
                                        <textarea name="deskripsi_rincian" id="deskripsi_rincian" class="form-control" rows="2"></textarea>
                                        <small class="text-muted" id="descHelper">Wajib untuk non-vendor, opsional untuk vendor</small>
                                    </div>
                                </div>

                                <!-- SECTION 2: Info Vendor (CONDITIONAL) -->
                                <div id="vendorSection" class="form-section">
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
                                                <option value="<?= $v->kode ?>" 
                                                        data-ppn="<?= $ppn_value ?>" 
                                                        data-pph="<?= $pph_value ?>">
                                                    <?= htmlspecialchars($v->nama_vendor) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Tagihan Vendor</label>
                                        <select name="tagihan_id" id="tagihan_id" class="form-control select2">
                                            <option value="">- Tidak ada tagihan -</option>
                                            <?php if (!empty($tagihan_unpaid)): ?>
                                                <?php foreach ($tagihan_unpaid as $t): ?>
                                                    <option value="<?= $t->id ?>" 
                                                            data-vendor="<?= $t->vendor_id ?>"
                                                            data-invoice="<?= $t->no_invoice ?>"
                                                            data-nominal="<?= $t->nominal ?>"
                                                            data-bulan="<?= $t->bulan_shipment ?>"
                                                            class="tagihan-option vendor-<?= $t->vendor_id ?>">
                                                        <?= htmlspecialchars($t->nama_vendor) ?> | 
                                                        <?= htmlspecialchars($t->no_invoice) ?> | 
                                                        Rp <?= number_format($t->nominal, 0, ',', '.') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif ?>
                                        </select>
                                    </div>
                                    
                                    <div class="info-tagihan" id="info-tagihan">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Auto Fill Aktif!</strong> 
                                        Data akan otomatis terisi dari tagihan yang dipilih.
                                    </div>

                                    <div class="form-group">
                                        <label>No Invoice Vendor</label>
                                        <input type="text" name="no_invoice_vendor" id="no_invoice_vendor" class="form-control" placeholder="Nomor invoice vendor">
                                    </div>
                                </div>

                                <!-- SECTION 3: Perhitungan -->
                                <div class="form-section form-section-required">
                                    <h6 class="font-weight-bold text-danger mb-3">
                                        <i class="fas fa-calculator"></i> Perhitungan Biaya
                                    </h6>
                                    
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nominal <span class="text-danger">*</span></label>
                                                <input type="text" name="nominal" id="nominal" class="form-control text-right font-weight-bold" required placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPN <small class="text-success">(+)</small></label>
                                                <input type="text" name="ppn" id="ppn" class="form-control text-right" value="0" readonly>
                                                <small class="text-muted">Rate: <span id="ppn_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPH <small class="text-danger">(-)</small></label>
                                                <input type="text" name="pph" id="pph" class="form-control text-right" value="0" readonly>
                                                <small class="text-muted">Rate: <span id="pph_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Total Bayar</label>
                                                <input type="text" id="total_bayar" class="form-control text-right font-weight-bold bg-light text-danger" style="font-size: 18px;" readonly value="0">
                                            </div>
                                        </div>
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
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({ 
                theme: 'bootstrap4', 
                allowClear: true,
                placeholder: '- Pilih -'
            });
            
            // Toggle Vendor Section based on tipe pengeluaran
            function toggleVendorSection() {
                if ($('#tipeNonVendor').is(':checked')) {
                    // Pengeluaran Manual (Non-Vendor)
                    $('#vendorSection').slideUp(300);
                    $('#vendor_id').prop('required', false).val('').trigger('change');
                    $('#tagihan_id').val('').trigger('change');
                    $('#reff_preview').val('M-00001').removeClass('bg-gradient-primary').addClass('bg-gradient-success');
                    $('#descRequired').show();
                    $('#deskripsi_rincian').prop('required', true);
                    $('#descHelper').html('<strong class="text-danger">WAJIB</strong> untuk pengeluaran manual');
                    $('#ppn, #pph').val('0');
                    $('#ppn_rate, #pph_rate').text('0');
                    hitungTotal();
                } else {
                    // Pengeluaran Vendor
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
            $('#vendor_id').on('select2:select', function() {
                var selected = $(this).find(':selected');
                var ppn = parseFloat(selected.data('ppn')) || 0;
                var pph = parseFloat(selected.data('pph')) || 0;
                
                $('#ppn_rate').text(ppn);
                $('#pph_rate').text(pph);
                
                hitungPajak();
            });
            
            $('#vendor_id').on('select2:clear', function() {
                $('#ppn_rate').text('0');
                $('#pph_rate').text('0');
                $('#ppn').val('0');
                $('#pph').val('0');
                hitungTotal();
            });
            
            // Tagihan change - auto fill data
            $('#tagihan_id').on('select2:select', function() {
                var selected = $(this).find(':selected');
                var vendorId = selected.data('vendor');
                var invoice = selected.data('invoice');
                var nominal = selected.data('nominal');
                var bulan = selected.data('bulan');
                
                // Set vendor dulu untuk trigger pajak rate
                $('#vendor_id').val(vendorId).trigger('change');
                
                // Set data lainnya
                $('#no_invoice_vendor').val(invoice);
                $('#bulan_shipment').val(bulan);
                $('#nominal').val(formatNumber(nominal));
                
                // Show info
                $('#info-tagihan').slideDown(300);
                
                // Hitung pajak
                setTimeout(function() {
                    hitungPajak();
                }, 100);
            });
            
            $('#tagihan_id').on('select2:clear', function() {
                $('#info-tagihan').slideUp(300);
                $('#no_invoice_vendor').val('');
            });
            
            // Nominal change
            $('#nominal').on('keyup', function() {
                formatInputNumber(this);
                hitungPajak();
            });
            
            // Manual input PPN/PPH jika diperlukan
            $('#ppn, #pph').on('keyup', function() {
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
            
            // Fungsi hitung total bayar
            function hitungTotal() {
                var nominal = parseNumber($('#nominal').val());
                var ppn = parseNumber($('#ppn').val());
                var pph = parseNumber($('#pph').val());
                var total = nominal + ppn - pph;
                
                $('#total_bayar').val(formatNumber(total));
            }
            
            // Format input number dengan titik separator
            function formatInputNumber(input) {
                var value = $(input).val().replace(/\./g, '');
                if (value !== '') {
                    $(input).val(formatNumber(value));
                }
            }
            
            // Format angka ke format Indonesia (1.000.000)
            function formatNumber(num) {
                var n = parseInt(num) || 0;
                return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
            
            // Parse string number ke float
            function parseNumber(str) {
                return parseFloat(str.replace(/\./g, '')) || 0;
            }

            // Form Validation sebelum submit
            $('#formPengeluaran').on('submit', function(e) {
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
                
                // Konfirmasi sebelum submit
                var confirmMsg = 'Simpan pengeluaran dengan total Rp ' + formatNumber(parseNumber($('#total_bayar').val())) + '?';
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
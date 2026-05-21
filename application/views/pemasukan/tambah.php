<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
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
            border-left: 4px solid #1cc88a;
        }

        .form-section-required {
            border-left: 4px solid #e74a3b;
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
                            <i class="fas fa-money-bill-wave text-success"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('pemasukan') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <div class="card shadow">
                        <div class="card-header bg-gradient-success text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-edit"></i> Form Tambah Pemasukan
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('pemasukan/proses_tambah') ?>" method="post" id="formPemasukan">

                                <!-- Radio Tipe Pemasukan -->
                                <div class="form-section"
                                    style="background: linear-gradient(135deg, #1cc88a22 0%, #36b9cc22 100%);">
                                    <h6 class="font-weight-bold text-success mb-3">
                                        <i class="fas fa-list-ul"></i> Pilih Tipe Pemasukan
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="tipeCustomer" name="tipe_pemasukan"
                                                    class="custom-control-input" value="customer" checked>
                                                <label class="custom-control-label font-weight-bold text-success"
                                                    for="tipeCustomer">
                                                    <i class="fas fa-user"></i> Penerimaan Customer
                                                    <br><small class="text-muted">Pembayaran dari customer (Reff: C-xxxxx)</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="tipeNonCustomer" name="tipe_pemasukan"
                                                    class="custom-control-input" value="non_customer">
                                                <label class="custom-control-label font-weight-bold text-info"
                                                    for="tipeNonCustomer">
                                                    <i class="fas fa-hand-holding-usd"></i> Pemasukan Manual
                                                    <br><small class="text-muted">Pendapatan lain-lain (Reff: R-xxxxx)</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SECTION 1: Info Wajib -->
                                <div class="form-section form-section-required">
                                    <h6 class="font-weight-bold text-success mb-3">
                                        <i class="fas fa-exclamation-circle"></i> Informasi Wajib
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Jenis Penerimaan <span class="text-danger">*</span></label>
                                                <select name="jenis_penerimaan" class="form-control" required>
                                                    <option value="">- Pilih Jenis Penerimaan -</option>
                                                    <?php if (!empty($akun_pendapatan)): ?>
                                                        <?php foreach ($akun_pendapatan as $akun): ?>
                                                            <?php 
                                                            $is_parent = in_array($akun->kode_perkiraan, ['20']);
                                                            ?>
                                                            <option value="<?= $akun->kode_perkiraan ?>"
                                                                    <?= $is_parent ? 'disabled style="font-weight:bold; background:#f0f0f0; color:#999;"' : '' ?>>
                                                                <?= $is_parent ? '▼ ' : '    ' ?><?= $akun->kode_perkiraan ?> - <?= htmlspecialchars($akun->nama) ?><?= $is_parent ? ' (Header)' : '' ?>
                                                            </option>
                                                        <?php endforeach; ?>
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
                                                <label>Reff No Preview</label>
                                                <input type="text" id="reff_preview" class="form-control bg-gradient-success text-white font-weight-bold text-center" readonly value="C-00001">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bulan Shipment</label>
                                                <select name="bulan_shipment" class="form-control">
                                                    <option value="">- Pilih Bulan -</option>
                                                    <?php 
                                                    $bulan_list = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                                    $current_month = date('n') - 1;
                                                    foreach ($bulan_list as $idx => $bln): 
                                                    ?>
                                                        <option value="<?= $bln ?>" <?= $idx == $current_month ? 'selected' : '' ?>><?= $bln ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Terima di Akun <span class="text-danger">*</span></label>
                                                <select name="akun_bank_id" class="form-control" required>
                                                    <option value="">- Pilih Bank/Kas -</option>
                                                    <?php foreach ($akun_bank as $akun): ?>
                                                        <option value="<?= $akun->id ?>">
                                                            <?= $akun->nama ?> (Saldo: Rp <?= number_format($akun->saldo_awal, 0, ',', '.') ?>)
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
                                            rows="2" placeholder="Contoh: Penerimaan pembayaran invoice bulan Desember"></textarea>
                                        <small class="text-muted" id="descHelper">Opsional untuk penerimaan customer</small>
                                    </div>
                                </div>

                                <!-- SECTION 2: Info Customer -->
                                <div class="form-section" id="customerSection">
                                    <h6 class="font-weight-bold text-success mb-3">
                                        <i class="fas fa-user"></i> Informasi Customer
                                    </h6>

                                    <div class="form-group">
                                        <label>Customer</label>
                                        <select name="customer_id" id="customer_id" class="form-control select2">
                                            <option value="">- Pilih Customer -</option>
                                            <?php foreach ($customers as $c): ?>
                                                <?php
                                                $ppn_value = 0;
                                                if (preg_match('/(\d+(\.\d+)?)/', $c->ppn ?? '', $matches)) {
                                                    $ppn_value = $matches[1];
                                                }
                                                
                                                $pph_value = 0;
                                                if (preg_match('/(\d+(\.\d+)?)/', $c->pph ?? '', $matches)) {
                                                    $pph_value = $matches[1];
                                                }
                                                ?>
                                                <option value="<?= $c->kode ?>" data-ppn="<?= $ppn_value ?>" data-pph="<?= $pph_value ?>">
                                                    <?= htmlspecialchars($c->nama) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Pilih customer untuk auto-fill PPN/PPH rate</small>
                                    </div>

                                    <!-- ✅ FIXED: Tagihan Customer Dropdown -->
                                    <div class="form-group">
                                        <label>Tagihan Customer</label>
                                        <select name="tagihan_id" id="tagihan_id" class="form-control select2">
                                            <option value="">- Tidak ada tagihan (input manual) -</option>
                                            <?php if (!empty($tagihan_unpaid)): ?>
                                                <?php foreach ($tagihan_unpaid as $t): ?>
                                                    <?php
                                                    // ✅ Extract from ppn & pph (TEXT from customer table)
                                                    $ppn_value = 0;
                                                    if (!empty($t->ppn) && preg_match('/(\d+(?:\.\d+)?)/', $t->ppn, $matches)) {
                                                        $ppn_value = $matches[1];
                                                    }
                                                    
                                                    $pph_value = 0;
                                                    if (!empty($t->pph) && preg_match('/(\d+(?:\.\d+)?)/', $t->pph, $matches)) {
                                                        $pph_value = $matches[1];
                                                    }
                                                    ?>
                                                    <option value="<?= $t->id ?>" 
                                                            data-customer="<?= $t->customer_id ?>"
                                                            data-invoice="<?= $t->no_invoice ?>" 
                                                            data-nominal="<?= $t->nominal ?>"
                                                            data-bulan="<?= $t->bulan_shipment ?>"
                                                            data-ppn="<?= $ppn_value ?>"
                                                            data-pph="<?= $pph_value ?>"
                                                            class="tagihan-option customer-<?= $t->customer_id ?>">
                                                        <?= htmlspecialchars($t->nama_customer) ?> | 
                                                        <?= htmlspecialchars($t->no_invoice) ?> | 
                                                        Rp <?= number_format($t->nominal, 0, ',', '.') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif ?>
                                        </select>
                                        <small class="text-muted">Pilih tagihan untuk auto-fill data</small>
                                    </div>

                                    <div class="alert alert-info info-tagihan" id="info-tagihan">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Tagihan dipilih!</strong> Data invoice & nominal akan terisi otomatis.
                                    </div>

                                    <div class="form-group">
                                        <label>No Invoice Customer</label>
                                        <input type="text" name="no_invoice_cust" id="no_invoice_cust" class="form-control" placeholder="INV-CUST-001">
                                    </div>
                                </div>

                                <!-- SECTION 3: Perhitungan -->
                                <div class="form-section form-section-required">
                                    <h6 class="font-weight-bold text-success mb-3">
                                        <i class="fas fa-calculator"></i> Perhitungan Penerimaan
                                    </h6>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Nominal <span class="text-danger">*</span></label>
                                                <input type="text" name="nominal" id="nominal" class="form-control text-right font-weight-bold" placeholder="0" required>
                                                <small class="text-muted">Nilai tagihan/pendapatan</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPN <small class="text-success">(+)</small></label>
                                                <input type="text" name="ppn" id="ppn" class="form-control text-right" placeholder="0" value="0">
                                                <small class="text-muted">Rate: <span id="ppn_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPH <small class="text-danger">(-)</small></label>
                                                <input type="text" name="pph" id="pph" class="form-control text-right" placeholder="0" value="0">
                                                <small class="text-muted">Rate: <span id="pph_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Total Diterima</label>
                                                <input type="text" id="total_diterima" class="form-control text-right font-weight-bold bg-light text-success" style="font-size: 18px;" readonly placeholder="0">
                                                <small class="text-muted">Yang masuk ke bank</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-success">
                                        <i class="fas fa-lightbulb"></i>
                                        <strong>Formula:</strong> Total Diterima = Nominal + PPN - PPH
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                        <i class="fas fa-save"></i> Simpan Pemasukan
                                    </button>
                                    <a href="<?= base_url('pemasukan') ?>" class="btn btn-secondary btn-lg px-5">
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

            // ✅ Toggle Customer Section
            function toggleCustomerSection() {
                if ($('#tipeNonCustomer').is(':checked')) {
                    // Pemasukan Manual (Non-Customer)
                    $('#customerSection').slideUp(300);
                    $('#customer_id').prop('required', false).val('').trigger('change');
                    $('#tagihan_id').val('').trigger('change');
                    $('#reff_preview').val('R-00001').removeClass('bg-gradient-success').addClass('bg-gradient-info');
                    $('#descRequired').show();
                    $('#deskripsi_rincian').prop('required', true);
                    $('#descHelper').html('<strong class="text-danger">WAJIB</strong> untuk pemasukan manual');
                    $('#ppn, #pph').val('0');
                    $('#ppn_rate, #pph_rate').text('0');
                    hitungTotal();
                } else {
                    // Penerimaan Customer
                    $('#customerSection').slideDown(300);
                    $('#reff_preview').val('C-00001').removeClass('bg-gradient-info').addClass('bg-gradient-success');
                    $('#descRequired').hide();
                    $('#deskripsi_rincian').prop('required', false);
                    $('#descHelper').text('Opsional untuk penerimaan customer');
                }
            }

            $('input[name="tipe_pemasukan"]').on('change', toggleCustomerSection);
            toggleCustomerSection();

            // Customer change - load pajak rate
            $('#customer_id').on('select2:select', function() {
                var selected = $(this).find(':selected');
                var ppn = parseFloat(selected.data('ppn')) || 0;
                var pph = parseFloat(selected.data('pph')) || 0;

                $('#ppn_rate').text(ppn);
                $('#pph_rate').text(pph);

                hitungPajak();
            });

            $('#customer_id').on('select2:clear', function() {
                $('#ppn_rate').text('0');
                $('#pph_rate').text('0');
                $('#ppn').val('0');
                $('#pph').val('0');
                hitungTotal();
            });

            // ✅ FIXED: Tagihan change - auto fill with PPN/PPH from JOIN
            $('#tagihan_id').on('select2:select', function() {
                var selected = $(this).find(':selected');
                var customerId = selected.data('customer');
                var invoice = selected.data('invoice');
                var nominal = selected.data('nominal');
                var bulan = selected.data('bulan');
                
                // ✅ Get PPN/PPH from tagihan data attribute
                var ppn_rate = parseFloat(selected.data('ppn')) || 0;
                var pph_rate = parseFloat(selected.data('pph')) || 0;
                
                // Set customer
                $('#customer_id').val(customerId).trigger('change');
                
                // Set data
                $('#no_invoice_cust').val(invoice);
                $('select[name="bulan_shipment"]').val(bulan);
                $('#nominal').val(formatNumber(nominal));
                
                // ✅ Set PPN/PPH rate
                $('#ppn_rate').text(ppn_rate);
                $('#pph_rate').text(pph_rate);
                
                // Show info
                $('#info-tagihan').slideDown(300);
                
                // ✅ Hitung pajak
                setTimeout(function() {
                    hitungPajak();
                }, 100);
            });

            $('#tagihan_id').on('select2:clear', function() {
                $('#info-tagihan').slideUp(300);
                $('#no_invoice_cust').val('');
            });

            // Nominal change
            $('#nominal').on('keyup', function() {
                formatInputNumber(this);
                hitungPajak();
            });

            // Manual input PPN/PPH
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

            // Fungsi hitung total diterima
            function hitungTotal() {
                var nominal = parseNumber($('#nominal').val());
                var ppn = parseNumber($('#ppn').val());
                var pph = parseNumber($('#pph').val());
                var total = nominal + ppn - pph;

                $('#total_diterima').val(formatNumber(total));
            }

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
            $('#formPemasukan').on('submit', function(e) {
                var tipe = $('input[name="tipe_pemasukan"]:checked').val();
                var desc = $('#deskripsi_rincian').val().trim();
                var nominal = parseNumber($('#nominal').val());

                if (nominal <= 0) {
                    e.preventDefault();
                    alert('⚠️ Nominal harus lebih besar dari 0!');
                    $('#nominal').focus();
                    return false;
                }

                if (tipe === 'non_customer' && !desc) {
                    e.preventDefault();
                    alert('⚠️ Deskripsi Rincian wajib diisi untuk pemasukan Manual!');
                    $('#deskripsi_rincian').focus();
                    return false;
                }

                var confirmMsg = 'Simpan pemasukan dengan total Rp ' + formatNumber(parseNumber($('#total_diterima').val())) + '?';
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
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
                            <i class="fas fa-edit text-warning"></i> <?= $title ?>
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

                    <!-- Info Card -->
                    <div class="card shadow mb-4 border-left-danger">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Reff No:</strong><br>
                                    <span class="badge badge-danger badge-lg"><?= $pengeluaran->reff_no ?></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Tipe:</strong><br>
                                    <?php 
                                    $tipe = substr($pengeluaran->reff_no, 0, 1);
                                    if ($tipe === 'V'): 
                                    ?>
                                        <span class="badge badge-primary">Vendor Payment</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pengeluaran Manual</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Bayar:</strong><br>
                                    <span class="text-danger font-weight-bold">
                                        Rp <?= number_format($pengeluaran->total_bayar, 0, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Dibuat:</strong><br>
                                    <?= date('d/m/Y H:i', strtotime($pengeluaran->created_at)) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Card -->
                    <div class="card shadow">
                        <div class="card-header bg-gradient-warning text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-edit"></i> Form Ubah Pengeluaran
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('pengeluaran/proses_ubah') ?>" method="post" id="formPengeluaran">
                                <input type="hidden" name="id" value="<?= $pengeluaran->id ?>">
                                <input type="hidden" name="old_tagihan_id" value="<?= $pengeluaran->tagihan_id ?>">
                                
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
                                                            <option value="<?= $akun->kode_perkiraan ?>" 
                                                                    <?= $akun->kode_perkiraan == $pengeluaran->postingan_biaya ? 'selected' : '' ?>>
                                                                <?= $akun->kode_perkiraan ?> - <?= htmlspecialchars($akun->nama) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <!-- Fallback -->
                                                        <option value="302" <?= $pengeluaran->postingan_biaya == '302' ? 'selected' : '' ?>>302 - Biaya Vendor A</option>
                                                        <option value="303" <?= $pengeluaran->postingan_biaya == '303' ? 'selected' : '' ?>>303 - Biaya Vendor B</option>
                                                        <option value="401" <?= $pengeluaran->postingan_biaya == '401' ? 'selected' : '' ?>>401 - Biaya Operasional</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tanggal <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal" class="form-control" value="<?= $pengeluaran->tanggal ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Reff No</label>
                                                <input type="text" class="form-control bg-gradient-danger text-white font-weight-bold text-center" readonly value="<?= $pengeluaran->reff_no ?>">
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
                                                    foreach ($bulan_list as $bln): 
                                                    ?>
                                                        <option value="<?= $bln ?>" <?= $bln == $pengeluaran->bulan_shipment ? 'selected' : '' ?>>
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
                                                        <option value="<?= $akun->id ?>" <?= $akun->id == $pengeluaran->akun_bank_id ? 'selected' : '' ?>>
                                                            <?= $akun->nama ?> (Saldo: Rp <?= number_format($akun->saldo_awal, 0, ',', '.') ?>)
                                                        </option>
                                                    <?php endforeach ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Deskripsi Rincian</label>
                                        <textarea name="deskripsi_rincian" id="deskripsi_rincian" class="form-control" rows="2"><?= htmlspecialchars($pengeluaran->deskripsi_rincian ?? '') ?></textarea>
                                    </div>
                                </div>

                                <!-- SECTION 2: Info Vendor -->
                                <div class="form-section">
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
                                                        data-pph="<?= $pph_value ?>"
                                                        <?= $v->kode == $pengeluaran->vendor_id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($v->nama_vendor) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Tagihan Vendor</label>
                                        <select name="tagihan_id" id="tagihan_id" class="form-control select2">
                                            <option value="">- Tidak ada tagihan -</option>
                                            <?php if ($tagihan_terkait): ?>
                                                <option value="<?= $tagihan_terkait->id ?>" 
                                                        data-vendor="<?= $tagihan_terkait->vendor_id ?>"
                                                        data-invoice="<?= $tagihan_terkait->no_invoice ?>"
                                                        data-nominal="<?= $tagihan_terkait->nominal ?>"
                                                        data-bulan="<?= $tagihan_terkait->bulan_shipment ?>"
                                                        selected>
                                                    <?= htmlspecialchars($tagihan_terkait->nama_vendor) ?> | 
                                                    <?= htmlspecialchars($tagihan_terkait->no_invoice) ?> | 
                                                    Rp <?= number_format($tagihan_terkait->nominal, 0, ',', '.') ?> | 
                                                    PAID
                                                </option>
                                            <?php endif; ?>
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
                                        <?php if ($tagihan_terkait): ?>
                                            <small class="text-info">
                                                <i class="fas fa-info-circle"></i> 
                                                Tagihan terkait sudah dibayar. Jika diganti, status tagihan ini akan di-reset.
                                            </small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label>No Invoice Vendor</label>
                                        <input type="text" name="no_invoice_vendor" id="no_invoice_vendor" class="form-control" value="<?= htmlspecialchars($pengeluaran->no_invoice_vendor ?? '') ?>">
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
                                                <input type="text" name="nominal" id="nominal" class="form-control text-right font-weight-bold" required value="<?= number_format($pengeluaran->nominal, 0, ',', '.') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPN <small class="text-success">(+)</small></label>
                                                <input type="text" name="ppn" id="ppn" class="form-control text-right" value="<?= number_format($pengeluaran->ppn, 0, ',', '.') ?>" readonly>
                                                <small class="text-muted">Rate: <span id="ppn_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPH <small class="text-danger">(-)</small></label>
                                                <input type="text" name="pph" id="pph" class="form-control text-right" value="<?= number_format($pengeluaran->pph, 0, ',', '.') ?>" readonly>
                                                <small class="text-muted">Rate: <span id="pph_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Total Bayar</label>
                                                <input type="text" id="total_bayar" class="form-control text-right font-weight-bold bg-light text-danger" style="font-size: 18px;" readonly value="<?= number_format($pengeluaran->total_bayar, 0, ',', '.') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-warning btn-lg px-5 shadow-sm">
                                        <i class="fas fa-save"></i> Update Pengeluaran
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
            $('.select2').select2({ theme: 'bootstrap4', allowClear: true });
            
            // Load initial PPN/PPH rate
            <?php if ($pengeluaran->vendor_id): ?>
                var selectedVendor = $('#vendor_id').find(':selected');
                var ppn = parseFloat(selectedVendor.data('ppn')) || 0;
                var pph = parseFloat(selectedVendor.data('pph')) || 0;
                $('#ppn_rate').text(ppn);
                $('#pph_rate').text(pph);
            <?php endif; ?>
            
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
            
            $('#tagihan_id').on('select2:select', function() {
                var selected = $(this).find(':selected');
                $('#vendor_id').val(selected.data('vendor')).trigger('change');
                $('#no_invoice_vendor').val(selected.data('invoice'));
                $('#bulan_shipment').val(selected.data('bulan'));
                $('#nominal').val(formatNumber(selected.data('nominal')));
                setTimeout(function() { hitungPajak(); }, 100);
            });
            
            $('#nominal').on('keyup', function() {
                formatInputNumber(this);
                hitungPajak();
            });
            
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
            
            function hitungTotal() {
                var nominal = parseNumber($('#nominal').val());
                var ppn = parseNumber($('#ppn').val());
                var pph = parseNumber($('#pph').val());
                var total = nominal + ppn - pph;
                $('#total_bayar').val(formatNumber(total));
            }
            
            function formatInputNumber(input) {
                var value = $(input).val().replace(/\./g, '');
                if (value !== '') $(input).val(formatNumber(value));
            }
            
            function formatNumber(num) {
                var n = parseInt(num) || 0;
                return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
            
            function parseNumber(str) {
                return parseFloat(str.replace(/\./g, '')) || 0;
            }
        });
    </script>
</body>
</html>
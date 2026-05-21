<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        .select2-container { width: 100% !important; }
        .info-tagihan {
            background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
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
            border-left: 4px solid #36b9cc;
        }
        .form-section-required {
            border-left: 4px solid #1cc88a;
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
                            <i class="fas fa-edit text-warning"></i> <?= $title ?>
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

                    <!-- Info Card -->
                    <div class="card shadow mb-4 border-left-warning">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Reff No:</strong><br>
                                    <span class="badge badge-primary badge-lg"><?= $pemasukan->reff_no ?></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Tipe:</strong><br>
                                    <?php 
                                    $tipe = substr($pemasukan->reff_no, 0, 1);
                                    if ($tipe === 'C'): 
                                    ?>
                                        <span class="badge badge-info">Customer Payment</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Pemasukan Lain-Lain</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Diterima:</strong><br>
                                    <span class="text-success font-weight-bold">
                                        Rp <?= number_format($pemasukan->total_diterima, 0, ',', '.') ?>
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Dibuat:</strong><br>
                                    <?= date('d/m/Y H:i', strtotime($pemasukan->created_at)) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Card -->
                    <div class="card shadow">
                        <div class="card-header bg-gradient-warning text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-edit"></i> Form Ubah Pemasukan
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('pemasukan/proses_ubah') ?>" method="post" id="formPemasukan">
                                <input type="hidden" name="id" value="<?= $pemasukan->id ?>">
                                <input type="hidden" name="old_tagihan_id" value="<?= $pemasukan->tagihan_id ?>">
                                
                                <!-- SECTION 1: Info Wajib -->
                                <div class="form-section form-section-required">
                                    <h6 class="font-weight-bold text-success mb-3">
                                        <i class="fas fa-exclamation-circle"></i> Informasi Wajib
                                    </h6>
                                    
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Tanggal <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal" class="form-control" value="<?= $pemasukan->tanggal ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Jenis Penerimaan <span class="text-danger">*</span></label>
                                                <select name="jenis_penerimaan" id="jenis_penerimaan" class="form-control" required>
                                                    <option value="">- Pilih -</option>
                                                    <?php if (!empty($akun_pendapatan)): ?>
                                                        <?php foreach ($akun_pendapatan as $akun): ?>
                                                        <?php 
                                                        // 🔥 Disable parent account (20)
                                                        $is_parent = in_array($akun->kode_perkiraan, ['20']);
                                                        
                                                        // Check if this is the current selected value
                                                        $is_selected = ($akun->kode_perkiraan == $pemasukan->jenis_penerimaan);
                                                           ?>
                                                        <option value="<?= $akun->kode_perkiraan ?>"
                                                            <?= $is_selected ? 'selected' : '' ?>
                                                            <?= $is_parent ? 'disabled style="font-weight:bold; background:#f0f0f0; color:#999;"' : '' ?>>
                                                            <?= $is_parent ? '▼ ' : '    ' ?><?= $akun->kode_perkiraan ?> - <?= htmlspecialchars($akun->nama) ?><?= $is_parent ? ' (Header)' : '' ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <!-- Fallback jika data kosong -->
                                                        <option value="201" <?= $pemasukan->jenis_penerimaan == '201' ? 'selected' : '' ?>>201 - Penjualan Rilase</option>
                                                        <option value="202" <?= $pemasukan->jenis_penerimaan == '202' ? 'selected' : '' ?>>202 - Pendapatan sewa</option>
                                                        <option value="203" <?= $pemasukan->jenis_penerimaan == '203' ? 'selected' : '' ?>>203 - Pendapatan Lain-Lain</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Reff No</label>
                                                <input type="text" class="form-control bg-gradient-primary text-white font-weight-bold text-center" readonly value="<?= $pemasukan->reff_no ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Terima ke Akun <span class="text-danger">*</span></label>
                                                <select name="akun_bank_id" class="form-control" required>
                                                    <option value="">- Pilih Bank/Kas -</option>
                                                    <?php foreach($akun_bank as $akun): ?>
                                                        <option value="<?= $akun->id ?>" <?= $akun->id == $pemasukan->akun_bank_id ? 'selected' : '' ?>>
                                                            <?= $akun->nama ?> (Saldo: Rp <?= number_format($akun->saldo_awal, 0, ',', '.') ?>)
                                                        </option>
                                                    <?php endforeach ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Deskripsi Rincian</label>
                                        <textarea name="deskripsi_rincian" id="deskripsi_rincian" class="form-control" rows="2"><?= htmlspecialchars($pemasukan->deskripsi_rincian ?? '') ?></textarea>
                                    </div>
                                </div>

                                <!-- SECTION 2: Info Customer -->
                                <div class="form-section">
                                    <h6 class="font-weight-bold text-primary mb-3">
                                        <i class="fas fa-users"></i> Informasi Customer
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
                                                <option value="<?= $c->id ?>" 
                                                        data-ppn="<?= $ppn_value ?>" 
                                                        data-pph="<?= $pph_value ?>"
                                                        <?= $c->id == $pemasukan->customer_id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($c->nama) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Tagihan Customer</label>
                                        <select name="tagihan_id" id="tagihan_id" class="form-control select2">
                                            <option value="">- Tidak ada tagihan -</option>
                                            <?php if ($tagihan_terkait): ?>
                                                <option value="<?= $tagihan_terkait->id ?>" 
                                                        data-customer="<?= $tagihan_terkait->customer_id ?>"
                                                        data-invoice="<?= $tagihan_terkait->no_invoice ?>"
                                                        data-nominal="<?= $tagihan_terkait->nominal ?>"
                                                        selected>
                                                    <?= htmlspecialchars($tagihan_terkait->nama_customer) ?> | 
                                                    <?= htmlspecialchars($tagihan_terkait->no_invoice) ?> | 
                                                    Rp <?= number_format($tagihan_terkait->nominal, 0, ',', '.') ?> | 
                                                    PAID
                                                </option>
                                            <?php endif; ?>
                                            <?php if (!empty($tagihan_unpaid)): ?>
                                                <?php foreach ($tagihan_unpaid as $t): ?>
                                                    <option value="<?= $t->id ?>" 
                                                            data-customer="<?= $t->customer_id ?>"
                                                            data-invoice="<?= $t->no_invoice ?>"
                                                            data-nominal="<?= $t->nominal ?>"
                                                            class="tagihan-option customer-<?= $t->customer_id ?>">
                                                        <?= htmlspecialchars($t->nama_customer) ?> | 
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
                                        <label>No Invoice Customer</label>
                                        <input type="text" name="no_invoice_cust" id="no_invoice_cust" class="form-control" value="<?= htmlspecialchars($pemasukan->no_invoice_cust ?? '') ?>">
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
                                                <input type="text" name="nominal" id="nominal" class="form-control text-right font-weight-bold" required value="<?= number_format($pemasukan->nominal, 0, ',', '.') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPN <small class="text-success">(+)</small></label>
                                                <input type="text" name="ppn" id="ppn" class="form-control text-right" value="<?= number_format($pemasukan->ppn, 0, ',', '.') ?>" readonly>
                                                <small class="text-muted">Rate: <span id="ppn_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPH <small class="text-danger">(-)</small></label>
                                                <input type="text" name="pph" id="pph" class="form-control text-right" value="<?= number_format($pemasukan->pph, 0, ',', '.') ?>" readonly>
                                                <small class="text-muted">Rate: <span id="pph_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Total Diterima</label>
                                                <input type="text" id="total_diterima" class="form-control text-right font-weight-bold bg-light text-success" style="font-size: 18px;" readonly value="<?= number_format($pemasukan->total_diterima, 0, ',', '.') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-warning btn-lg px-5 shadow-sm">
                                        <i class="fas fa-save"></i> Update Pemasukan
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
            $('.select2').select2({ theme: 'bootstrap4', allowClear: true });
            
            // Load initial PPN/PPH rate
            <?php if ($pemasukan->customer_id): ?>
                var selectedCustomer = $('#customer_id').find(':selected');
                var ppn = parseFloat(selectedCustomer.data('ppn')) || 0;
                var pph = parseFloat(selectedCustomer.data('pph')) || 0;
                $('#ppn_rate').text(ppn);
                $('#pph_rate').text(pph);
            <?php endif; ?>
            
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
            
            $('#tagihan_id').on('select2:select', function() {
                var selected = $(this).find(':selected');
                $('#customer_id').val(selected.data('customer')).trigger('change');
                $('#no_invoice_cust').val(selected.data('invoice'));
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
                $('#total_diterima').val(formatNumber(total));
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
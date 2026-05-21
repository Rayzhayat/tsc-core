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
                            <i class="fas fa-edit text-warning"></i> <?= $title ?>
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
                    <div class="card shadow mb-4 border-left-warning">
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
                            <hr>
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-info-circle"></i>
                                <strong>Mode: ADVANCED (OCAS Support)</strong> - PPH akan dicatat ke akun 51/52
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
                                                <select name="postingan_biaya" class="form-control" required>
                                                    <option value="">- Pilih Postingan Biaya -</option>
                                                    <?php if (!empty($akun_biaya)): ?>
                                                        <?php foreach($akun_biaya as $akun): ?>
                                                            <?php 
                                                            // 🔥 Disable parent accounts (30, 40)
                                                            $is_parent = in_array($akun->kode_perkiraan, ['30', '40']);
                                                            
                                                            // Check if this is the current selected value
                                                            $is_selected = ($akun->kode_perkiraan == $pengeluaran->postingan_biaya);
                                                            ?>
                                                            <option value="<?= $akun->kode_perkiraan ?>"
                                                                <?= $is_selected ? 'selected' : '' ?>
                                                                <?= $is_parent ? 'disabled style="font-weight:bold; background:#f0f0f0; color:#999;"' : '' ?>>
                                                                <?= $is_parent ? '▼ ' : '    ' ?><?= $akun->kode_perkiraan ?> - <?= htmlspecialchars($akun->nama) ?><?= $is_parent ? ' (Header)' : '' ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <!-- Fallback -->
                                                        <option value="302" <?= $pengeluaran->postingan_biaya == '302' ? 'selected' : '' ?>>302 - Biaya Sewa Vendor Rilase</option>
                                                        <option value="401" <?= $pengeluaran->postingan_biaya == '401' ? 'selected' : '' ?>>401 - Biaya Transport Operasional</option>
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
                                                    $bulan_list = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
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
                                                    <?php foreach ($akun_bank as $akun): ?>
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
                                        <i class="fas fa-calculator"></i> Perhitungan Biaya (OCAS Support)
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
                                                <input type="text" name="ppn" id="ppn" class="form-control text-right" value="<?= number_format($pengeluaran->ppn, 0, ',', '.') ?>">
                                                <small class="text-muted">Rate: <span id="ppn_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>PPH <small class="text-danger">(-)</small></label>
                                                <input type="text" name="pph" id="pph" class="form-control text-right" value="<?= number_format($pengeluaran->pph, 0, ',', '.') ?>">
                                                <small class="text-muted">Rate: <span id="pph_rate">0</span>%</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Total Bayar</label>
                                                <input type="text" id="total_bayar" class="form-control text-right font-weight-bold bg-light text-danger" style="font-size: 18px;" readonly value="<?= number_format($pengeluaran->total_bayar, 0, ',', '.') ?>">
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
                                            • PPH ditahan: Dicatat ke akun <strong>51 (PPH 23)</strong> atau <strong>52 (PPH 4(2))</strong><br>
                                            • Journal akan di-recreate dengan 3-way entry
                                        </small>
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
            $('.select2').select2({
                theme: 'bootstrap4',
                allowClear: true
            });

            // Load initial PPN/PPH rate
            <?php if ($pengeluaran->vendor_id): ?>
                var selectedVendor = $('#vendor_id').find(':selected');
                var ppn = parseFloat(selectedVendor.data('ppn')) || 0;
                var pph = parseFloat(selectedVendor.data('pph')) || 0;
                $('#ppn_rate').text(ppn);
                $('#pph_rate').text(pph);

                // Show initial PPH info if exists
                setTimeout(function() {
                    var current_pph = parseNumber($('#pph').val());
                    if (current_pph > 1000) {
                        $('#pph').trigger('change');
                    }
                }, 500);
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
                $('#pph-info').remove();
                hitungTotal();
            });

            $('#tagihan_id').on('select2:select', function() {
                var selected = $(this).find(':selected');
                $('#vendor_id').val(selected.data('vendor')).trigger('change');
                $('#no_invoice_vendor').val(selected.data('invoice'));
                $('#bulan_shipment').val(selected.data('bulan'));
                $('#nominal').val(formatNumber(selected.data('nominal')));
                setTimeout(function() {
                    hitungPajak();
                }, 100);
            });

            $('#nominal').on('keyup', function() {
                formatInputNumber(this);
                hitungPajak();
            });

            $('#ppn, #pph').on('keyup', function() {
                formatInputNumber(this);
                hitungTotal();
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

            // 🔥 UPGRADED: Fungsi hitung total bayar (OCAS)
            function hitungTotal() {
                var nominal = parseNumber($('#nominal').val());
                var ppn = parseNumber($('#ppn').val());
                var pph = parseNumber($('#pph').val());

                // Total Biaya = Nominal + PPN
                var total_biaya = nominal + ppn;

                // Total Bayar = Total Biaya - PPH
                var total = total_biaya - pph;

                $('#total_bayar').val(formatNumber(total));

                // 🔥 Show calculation breakdown
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

                // Trigger PPH info update
                if (pph > 0) {
                    $('#pph').trigger('change');
                }
            }

            // 🔥 NEW: Show OCAS info
            $('#pph').on('change', function() {
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

            // Form validation
            $('#formPengeluaran').on('submit', function(e) {
                var nominal = parseNumber($('#nominal').val());
                var pph = parseNumber($('#pph').val());

                if (nominal <= 0) {
                    e.preventDefault();
                    alert('⚠️ Nominal harus lebih besar dari 0!');
                    return false;
                }

                var confirmMsg = 'Update pengeluaran dengan total Rp ' + formatNumber(parseNumber($('#total_bayar').val())) + '?';

                if (pph > 0) {
                    confirmMsg += '\n\n⚠️ PERHATIAN:\n';
                    confirmMsg += '• PPH Rp ' + formatNumber(pph) + ' akan dicatat ke OCAS\n';
                    confirmMsg += '• Journal lama akan dihapus dan dibuat ulang\n';
                    confirmMsg += '• Laporan keuangan akan terupdate';
                }

                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                    return false;
                }

                return true;
            });

            // Initial calculation on load
            hitungTotal();
        });
    </script>
</body>

</html>
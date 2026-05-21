<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .form-header {
            background: linear-gradient(135deg, #f6c23e 0%, #f4a742 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .section-header {
            background: #f8f9fc;
            padding: 10px 15px;
            border-left: 4px solid #f6c23e;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .item-row {
            background: #f8f9fc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #e3e6f0;
        }

        .item-row:hover {
            background: #e9ecef;
            border-color: #f6c23e;
        }

        .btn-remove-item {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .calculation-box {
            position: sticky;
            top: 20px;
            background: linear-gradient(135deg, #f6c23e 0%, #f4a742 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
        }

        .calculation-box .calc-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .calculation-box .calc-row:last-child {
            border-bottom: none;
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid rgba(255, 255, 255, 0.5);
        }

        .vendor-info-box {
            background: #fff3cd;
            border: 1px solid #f6c23e;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .vendor-info-box.hidden {
            display: none;
        }

        .alert-warning-custom {
            background: #fff3cd;
            border-left: 4px solid #f6c23e;
            padding: 10px 15px;
            margin-bottom: 15px;
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
                            <i class="fas fa-edit text-warning"></i> <?= $title ?>
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

                    <!-- Warning Alert -->
                    <div class="alert-warning-custom">
                        <i class="fas fa-info-circle"></i>
                        <strong>Perhatian:</strong> Hanya PO dengan status <strong>DRAFT</strong> atau <strong>REJECTED</strong> yang dapat diedit.
                    </div>

                    <form id="poForm" method="POST" action="<?= base_url('purchase_order/update/' . $po->id) ?>">
                        <div class="row">

                            <!-- Left Column (Form) -->
                            <div class="col-lg-8">

                                <!-- Main Card -->
                                <div class="card shadow mb-4">
                                    <div class="card-body p-0">
                                        <div class="form-header">
                                            <h4 class="mb-2">
                                                <i class="fas fa-file-invoice"></i> Edit Purchase Order
                                            </h4>
                                            <p class="mb-0">No. PO: <strong><?= $po->no_po ?></strong></p>
                                            <p class="mb-0">Status:
                                                <span class="badge badge-<?= $po->status == 'draft' ? 'secondary' : 'danger' ?>">
                                                    <?= strtoupper($po->status) ?>
                                                </span>
                                            </p>
                                        </div>

                                        <div class="p-4">

                                            <!-- Section: Informasi Dasar -->
                                            <div class="section-header">
                                                <i class="fas fa-info-circle"></i> Informasi Dasar
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-bold">Tanggal PO <span class="text-danger">*</span></label>
                                                    <input type="date" name="tanggal_po" class="form-control"
                                                        value="<?= $po->tanggal_po ?>" required>
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                                                    <select name="kategori" class="form-control" required>
                                                        <option value="">-- Pilih Kategori --</option>
                                                        <option value="barang" <?= $po->kategori == 'barang' ? 'selected' : '' ?>>Barang</option>
                                                        <option value="jasa" <?= $po->kategori == 'jasa' ? 'selected' : '' ?>>Jasa</option>
                                                        <option value="aset" <?= $po->kategori == 'aset' ? 'selected' : '' ?>>Aset</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-bold">Jenis Pembelian <span class="text-danger">*</span></label>
                                                    <select name="jenis_pembelian" class="form-control" required>
                                                        <option value="">-- Pilih Jenis --</option>
                                                        <option value="stock" <?= $po->jenis_pembelian == 'stock' ? 'selected' : '' ?>>Stock</option>
                                                        <option value="project" <?= $po->jenis_pembelian == 'project' ? 'selected' : '' ?>>Project</option>
                                                        <option value="operational" <?= $po->jenis_pembelian == 'operational' ? 'selected' : '' ?>>Operational</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Section: Vendor -->
                                            <div class="section-header">
                                                <i class="fas fa-building"></i> Vendor
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="font-weight-bold">Pilih Vendor <span class="text-danger">*</span></label>
                                                    <select name="vendor_kode" id="vendor_kode" class="form-control" required>
                                                        <option value="">-- Pilih Vendor --</option>
                                                        <?php foreach ($vendors as $vendor): ?>
                                                            <option value="<?= $vendor->kode ?>"
                                                                <?= $po->vendor_kode == $vendor->kode ? 'selected' : '' ?>
                                                                data-nama="<?= htmlspecialchars($vendor->nama_vendor) ?>"
                                                                data-alamat="<?= htmlspecialchars($vendor->alamat_vendor) ?>"
                                                                data-npwp="<?= htmlspecialchars($vendor->npwp_vendor) ?>"
                                                                data-pic="<?= htmlspecialchars($vendor->pic_vendor) ?>"
                                                                data-telp="<?= htmlspecialchars($vendor->no_telp_vendor) ?>"
                                                                data-ppn="<?= $vendor->ppn ?>"
                                                                data-pph="<?= $vendor->pph ?>">
                                                                <?= htmlspecialchars($vendor->nama_vendor) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Vendor Info Display -->
                                            <div id="vendorInfo" class="vendor-info-box">
                                                <h6 class="font-weight-bold text-warning mb-3">
                                                    <i class="fas fa-check-circle"></i> Informasi Vendor
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>Nama:</strong> <span id="displayNama"><?= $po->vendor_nama ?></span></p>
                                                        <p class="mb-2"><strong>NPWP:</strong> <span id="displayNpwp"><?= $po->vendor_npwp ?: '-' ?></span></p>
                                                        <p class="mb-2"><strong>PIC:</strong> <span id="displayPic"><?= $po->vendor_pic ?: '-' ?></span></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>No. Telp:</strong> <span id="displayTelp"><?= $po->vendor_telp ?: '-' ?></span></p>
                                                        <p class="mb-2"><strong>PPN:</strong> <span id="displayPpn"><?= $po->ppn_persen ?></span>%</p>
                                                        <p class="mb-2"><strong>PPh:</strong> <span id="displayPph"><?= $po->pph_persen ?></span>%</p>
                                                    </div>
                                                </div>
                                                <p class="mb-0"><strong>Alamat:</strong><br><span id="displayAlamat"><?= $po->vendor_alamat ?: '-' ?></span></p>
                                            </div>

                                            <!-- Hidden fields for vendor data -->
                                            <input type="hidden" name="vendor_nama" id="vendor_nama" value="<?= $po->vendor_nama ?>">
                                            <input type="hidden" name="vendor_alamat" id="vendor_alamat" value="<?= $po->vendor_alamat ?>">
                                            <input type="hidden" name="vendor_npwp" id="vendor_npwp" value="<?= $po->vendor_npwp ?>">
                                            <input type="hidden" name="vendor_pic" id="vendor_pic" value="<?= $po->vendor_pic ?>">
                                            <input type="hidden" name="vendor_telp" id="vendor_telp" value="<?= $po->vendor_telp ?>">

                                            <!-- Section: Items -->
                                            <div class="section-header">
                                                <i class="fas fa-boxes"></i> Daftar Item
                                            </div>

                                            <div id="itemsContainer">
                                                <?php
                                                $counter = 0;
                                                foreach ($details as $item):
                                                    $counter++;
                                                ?>
                                                    <div class="item-row position-relative" data-item="<?= $counter ?>">
                                                        <button type="button" class="btn btn-danger btn-sm btn-remove-item" onclick="removeItem(<?= $counter ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>

                                                        <div class="row">
                                                            <div class="col-md-12 mb-2">
                                                                <label class="font-weight-bold">Nama Item <span class="text-danger">*</span></label>
                                                                <input type="text" name="item_nama[]" class="form-control"
                                                                    value="<?= htmlspecialchars($item->item_nama) ?>"
                                                                    placeholder="Nama barang/jasa..." required>
                                                            </div>

                                                            <div class="col-md-4 mb-2">
                                                                <label class="font-weight-bold">Kode Item</label>
                                                                <input type="text" name="item_kode[]" class="form-control"
                                                                    value="<?= htmlspecialchars($item->item_kode) ?>"
                                                                    placeholder="Kode...">
                                                            </div>

                                                            <div class="col-md-4 mb-2">
                                                                <label class="font-weight-bold">Satuan</label>
                                                                <select name="item_satuan[]" class="form-control">
                                                                    <option value="PCS" <?= $item->item_satuan == 'PCS' ? 'selected' : '' ?>>PCS</option>
                                                                    <option value="UNIT" <?= $item->item_satuan == 'UNIT' ? 'selected' : '' ?>>UNIT</option>
                                                                    <option value="SET" <?= $item->item_satuan == 'SET' ? 'selected' : '' ?>>SET</option>
                                                                    <option value="BOX" <?= $item->item_satuan == 'BOX' ? 'selected' : '' ?>>BOX</option>
                                                                    <option value="KG" <?= $item->item_satuan == 'KG' ? 'selected' : '' ?>>KG</option>
                                                                    <option value="TON" <?= $item->item_satuan == 'TON' ? 'selected' : '' ?>>TON</option>
                                                                    <option value="LITER" <?= $item->item_satuan == 'LITER' ? 'selected' : '' ?>>LITER</option>
                                                                    <option value="M" <?= $item->item_satuan == 'M' ? 'selected' : '' ?>>METER</option>
                                                                    <option value="M2" <?= $item->item_satuan == 'M2' ? 'selected' : '' ?>>M²</option>
                                                                    <option value="M3" <?= $item->item_satuan == 'M3' ? 'selected' : '' ?>>M³</option>
                                                                    <option value="PAKET" <?= $item->item_satuan == 'PAKET' ? 'selected' : '' ?>>PAKET</option>
                                                                </select>
                                                            </div>

                                                            <div class="col-md-4 mb-2">
                                                                <label class="font-weight-bold">Qty <span class="text-danger">*</span></label>
                                                                <input type="number" name="qty_order[]" class="form-control item-qty"
                                                                    data-item="<?= $counter ?>"
                                                                    value="<?= $item->qty_order ?>"
                                                                    placeholder="0" min="0" step="0.01" required>
                                                            </div>

                                                            <div class="col-md-4 mb-2">
                                                                <label class="font-weight-bold">Harga Satuan <span class="text-danger">*</span></label>
                                                                <input type="number" name="harga_satuan[]" class="form-control item-harga"
                                                                    data-item="<?= $counter ?>"
                                                                    value="<?= $item->harga_satuan ?>"
                                                                    placeholder="0" min="0" step="1" required>
                                                            </div>

                                                            <div class="col-md-4 mb-2">
                                                                <label class="font-weight-bold">Diskon (%)</label>
                                                                <input type="number" name="diskon_persen[]" class="form-control item-diskon"
                                                                    data-item="<?= $counter ?>"
                                                                    value="<?= $item->diskon_persen ?>"
                                                                    placeholder="0" min="0" max="100" step="0.01">
                                                            </div>

                                                            <div class="col-md-4 mb-2">
                                                                <label class="font-weight-bold">Subtotal</label>
                                                                <input type="text" class="form-control item-subtotal-display"
                                                                    data-item="<?= $counter ?>"
                                                                    readonly value="Rp 0">
                                                                <input type="hidden" name="subtotal[]" class="item-subtotal"
                                                                    data-item="<?= $counter ?>" value="<?= $item->subtotal ?>">
                                                            </div>

                                                            <div class="col-md-12">
                                                                <label class="font-weight-bold">Spesifikasi</label>
                                                                <textarea name="item_spesifikasi[]" class="form-control" rows="2"
                                                                    placeholder="Spesifikasi detail item..."><?= htmlspecialchars($item->item_spesifikasi) ?></textarea>
                                                            </div>

                                                            <div class="col-md-12 mt-2">
                                                                <label class="font-weight-bold">Keterangan</label>
                                                                <input type="text" name="item_keterangan[]" class="form-control"
                                                                    value="<?= htmlspecialchars($item->keterangan) ?>"
                                                                    placeholder="Catatan untuk item ini...">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                                                <i class="fas fa-plus"></i> Tambah Item
                                            </button>
                                            <!-- Section: Biaya Tambahan -->
                                            <div class="section-header mt-4">
                                                <i class="fas fa-calculator"></i> Biaya Tambahan & Pajak
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-bold">Diskon (%)</label>
                                                    <input type="number" name="diskon_persen" id="diskon_persen"
                                                        class="form-control biaya-field"
                                                        value="<?= $po->diskon_persen ?>" min="0" max="100" step="0.01">
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-bold">PPN (%)</label>
                                                    <input type="number" name="ppn_persen" id="ppn_persen"
                                                        class="form-control biaya-field"
                                                        value="<?= $po->ppn_persen ?>" min="0" max="100" step="0.01" readonly>
                                                    <small class="text-muted">Auto dari vendor</small>
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-bold">PPh (%)</label>
                                                    <input type="number" name="pph_persen" id="pph_persen"
                                                        class="form-control biaya-field"
                                                        value="<?= $po->pph_persen ?>" min="0" max="100" step="0.01" readonly>
                                                    <small class="text-muted">Auto dari vendor</small>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Ongkir</label>
                                                    <input type="number" name="ongkir" id="ongkir"
                                                        class="form-control biaya-field"
                                                        value="<?= $po->ongkir ?>" min="0" step="1">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Biaya Lain-lain</label>
                                                    <input type="number" name="biaya_lain" id="biaya_lain"
                                                        class="form-control biaya-field"
                                                        value="<?= $po->biaya_lain ?>" min="0" step="1">
                                                </div>
                                            </div>

                                            <!-- Hidden calculation fields -->
                                            <input type="hidden" name="subtotal_all" id="subtotal_all" value="<?= $po->subtotal ?>">
                                            <input type="hidden" name="diskon_nominal" id="diskon_nominal" value="<?= $po->diskon_nominal ?>">
                                            <input type="hidden" name="ppn_nominal" id="ppn_nominal" value="<?= $po->ppn_nominal ?>">
                                            <input type="hidden" name="pph_nominal" id="pph_nominal" value="<?= $po->pph_nominal ?>">
                                            <input type="hidden" name="total_po" id="total_po" value="<?= $po->total_po ?>">

                                            <!-- Section: Informasi Tambahan -->
                                            <div class="section-header mt-4">
                                                <i class="fas fa-clipboard"></i> Informasi Tambahan
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Expected Delivery</label>
                                                    <input type="date" name="expected_delivery" class="form-control"
                                                        value="<?= $po->expected_delivery ?>">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Payment Terms</label>
                                                    <select name="payment_terms" class="form-control">
                                                        <option value="cash" <?= $po->payment_terms == 'cash' ? 'selected' : '' ?>>Cash</option>
                                                        <option value="tempo 7 hari" <?= $po->payment_terms == 'tempo 7 hari' ? 'selected' : '' ?>>Tempo 7 Hari</option>
                                                        <option value="tempo 14 hari" <?= $po->payment_terms == 'tempo 14 hari' ? 'selected' : '' ?>>Tempo 14 Hari</option>
                                                        <option value="tempo 30 hari" <?= $po->payment_terms == 'tempo 30 hari' ? 'selected' : '' ?>>Tempo 30 Hari</option>
                                                        <option value="tempo 45 hari" <?= $po->payment_terms == 'tempo 45 hari' ? 'selected' : '' ?>>Tempo 45 Hari</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-12 mb-3">
                                                    <label class="font-weight-bold">Alamat Pengiriman</label>
                                                    <textarea name="delivery_address" class="form-control" rows="2"
                                                        placeholder="Alamat tujuan pengiriman..."><?= htmlspecialchars($po->delivery_address) ?></textarea>
                                                </div>

                                                <div class="col-md-12 mb-3">
                                                    <label class="font-weight-bold">Keterangan</label>
                                                    <textarea name="keterangan" class="form-control" rows="3"
                                                        placeholder="Catatan tambahan untuk PO ini..."><?= htmlspecialchars($po->keterangan) ?></textarea>
                                                </div>
                                            </div>

                                            <!-- Rejection Reason (if rejected) -->
                                            <?php if ($po->status == 'rejected' && !empty($po->rejected_reason)): ?>
                                                <div class="alert alert-danger">
                                                    <strong><i class="fas fa-exclamation-triangle"></i> Alasan Penolakan:</strong><br>
                                                    <?= nl2br(htmlspecialchars($po->rejected_reason)) ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Action Buttons -->
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <button type="button" class="btn btn-secondary"
                                                        onclick="window.location.href='<?= base_url('purchase_order/detail/' . $po->id) ?>'">
                                                        <i class="fas fa-times"></i> Batal
                                                    </button>

                                                    <button type="submit" class="btn btn-warning" id="updateBtn">
                                                        <i class="fas fa-save"></i> Update PO
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Right Column (Calculation) -->
                            <div class="col-lg-4">
                                <div class="calculation-box shadow">
                                    <h5 class="mb-3">
                                        <i class="fas fa-calculator"></i> Ringkasan Perhitungan
                                    </h5>

                                    <div class="calc-row">
                                        <span>Subtotal Items:</span>
                                        <strong id="summarySubtotal">Rp 0</strong>
                                    </div>

                                    <div class="calc-row">
                                        <span>Diskon:</span>
                                        <strong id="summaryDiskon">Rp 0</strong>
                                    </div>

                                    <div class="calc-row">
                                        <span>Ongkir:</span>
                                        <strong id="summaryOngkir">Rp 0</strong>
                                    </div>

                                    <div class="calc-row">
                                        <span>Biaya Lain:</span>
                                        <strong id="summaryBiayaLain">Rp 0</strong>
                                    </div>

                                    <div class="calc-row">
                                        <span>PPN:</span>
                                        <strong id="summaryPpn">Rp 0</strong>
                                    </div>

                                    <div class="calc-row">
                                        <span>PPh:</span>
                                        <strong id="summaryPph">Rp 0</strong>
                                    </div>

                                    <div class="calc-row">
                                        <span>TOTAL:</span>
                                        <strong id="summaryTotal">Rp 0</strong>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        $(document).ready(function() {
            let itemCounter = <?= count($details) ?>; // Start from existing items count

            // Initialize Select2 for vendor
            $('#vendor_kode').select2({
                theme: 'bootstrap',
                width: '100%',
                placeholder: '-- Pilih Vendor --'
            });

            // Format number helper
            function formatNumber(num) {
                return Math.round(num).toLocaleString('id-ID');
            }

            function formatRupiah(angka) {
                return 'Rp ' + formatNumber(angka);
            }

            // Vendor change handler
            $('#vendor_kode').on('change', function() {
                const selected = $(this).find(':selected');

                if (selected.val()) {
                    const nama = selected.data('nama');
                    const alamat = selected.data('alamat');
                    const npwp = selected.data('npwp');
                    const pic = selected.data('pic');
                    const telp = selected.data('telp');
                    const ppn = selected.data('ppn') || 0;
                    const pph = selected.data('pph') || 0;

                    // Update hidden fields
                    $('#vendor_nama').val(nama);
                    $('#vendor_alamat').val(alamat);
                    $('#vendor_npwp').val(npwp);
                    $('#vendor_pic').val(pic);
                    $('#vendor_telp').val(telp);

                    // Update display
                    $('#displayNama').text(nama);
                    $('#displayAlamat').text(alamat || '-');
                    $('#displayNpwp').text(npwp || '-');
                    $('#displayPic').text(pic || '-');
                    $('#displayTelp').text(telp || '-');
                    $('#displayPpn').text(ppn);
                    $('#displayPph').text(pph);

                    // Update tax fields
                    $('#ppn_persen').val(ppn);
                    $('#pph_persen').val(pph);

                    // Show vendor info
                    $('#vendorInfo').removeClass('hidden');

                    console.log('✅ Vendor changed:', nama);
                } else {
                    // Hide vendor info
                    $('#vendorInfo').addClass('hidden');

                    // Clear fields
                    $('#ppn_persen').val(0);
                    $('#pph_persen').val(0);
                }

                calculateTotal();
            });

            // Add item button
            $('#addItemBtn').on('click', function() {
                addItemRow();
            });

            // Function to add item row
            function addItemRow() {
                itemCounter++;

                const itemHtml = `
                    <div class="item-row position-relative" data-item="${itemCounter}">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-item" onclick="removeItem(${itemCounter})">
                            <i class="fas fa-trash"></i>
                        </button>
                        
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="font-weight-bold">Nama Item <span class="text-danger">*</span></label>
                                <input type="text" name="item_nama[]" class="form-control" 
                                       placeholder="Nama barang/jasa..." required>
                            </div>
                            
                            <div class="col-md-4 mb-2">
                                <label class="font-weight-bold">Kode Item</label>
                                <input type="text" name="item_kode[]" class="form-control" 
                                       placeholder="Kode...">
                            </div>
                            
                            <div class="col-md-4 mb-2">
                                <label class="font-weight-bold">Satuan</label>
                                <select name="item_satuan[]" class="form-control">
                                    <option value="PCS">PCS</option>
                                    <option value="UNIT">UNIT</option>
                                    <option value="SET">SET</option>
                                    <option value="BOX">BOX</option>
                                    <option value="KG">KG</option>
                                    <option value="TON">TON</option>
                                    <option value="LITER">LITER</option>
                                    <option value="M">METER</option>
                                    <option value="M2">M²</option>
                                    <option value="M3">M³</option>
                                    <option value="PAKET">PAKET</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-2">
                                <label class="font-weight-bold">Qty <span class="text-danger">*</span></label>
                                <input type="number" name="qty_order[]" class="form-control item-qty" 
                                       data-item="${itemCounter}"
                                       placeholder="0" min="0" step="0.01" required>
                            </div>
                            
                            <div class="col-md-4 mb-2">
                                <label class="font-weight-bold">Harga Satuan <span class="text-danger">*</span></label>
                                <input type="number" name="harga_satuan[]" class="form-control item-harga" 
                                       data-item="${itemCounter}"
                                       placeholder="0" min="0" step="1" required>
                            </div>
                            
                            <div class="col-md-4 mb-2">
                                <label class="font-weight-bold">Diskon (%)</label>
                                <input type="number" name="diskon_persen[]" class="form-control item-diskon" 
                                       data-item="${itemCounter}"
                                       placeholder="0" min="0" max="100" step="0.01" value="0">
                            </div>
                            
                            <div class="col-md-4 mb-2">
                                <label class="font-weight-bold">Subtotal</label>
                                <input type="text" class="form-control item-subtotal-display" 
                                       data-item="${itemCounter}"
                                       readonly value="Rp 0">
                                <input type="hidden" name="subtotal[]" class="item-subtotal" 
                                       data-item="${itemCounter}" value="0">
                            </div>
                            
                            <div class="col-md-12">
                                <label class="font-weight-bold">Spesifikasi</label>
                                <textarea name="item_spesifikasi[]" class="form-control" rows="2" 
                                          placeholder="Spesifikasi detail item..."></textarea>
                            </div>
                            
                            <div class="col-md-12 mt-2">
                                <label class="font-weight-bold">Keterangan</label>
                                <input type="text" name="item_keterangan[]" class="form-control" 
                                       placeholder="Catatan untuk item ini...">
                            </div>
                        </div>
                    </div>
                `;

                $('#itemsContainer').append(itemHtml);

                // Attach event handlers to new item
                attachItemEventHandlers(itemCounter);

                console.log('➕ Item added:', itemCounter);
            }

            // Attach event handlers to item fields
            function attachItemEventHandlers(itemId) {
                $(`.item-qty[data-item="${itemId}"], .item-harga[data-item="${itemId}"], .item-diskon[data-item="${itemId}"]`)
                    .on('input', function() {
                        calculateItemSubtotal(itemId);
                    });
            }

            // Calculate item subtotal
            function calculateItemSubtotal(itemId) {
                const qty = parseFloat($(`.item-qty[data-item="${itemId}"]`).val()) || 0;
                const harga = parseFloat($(`.item-harga[data-item="${itemId}"]`).val()) || 0;
                const diskonPersen = parseFloat($(`.item-diskon[data-item="${itemId}"]`).val()) || 0;

                const subtotalBefore = qty * harga;
                const diskonNominal = subtotalBefore * (diskonPersen / 100);
                const subtotal = subtotalBefore - diskonNominal;

                $(`.item-subtotal[data-item="${itemId}"]`).val(subtotal);
                $(`.item-subtotal-display[data-item="${itemId}"]`).val(formatRupiah(subtotal));

                calculateTotal();
            }

            // Remove item function (global scope)
            window.removeItem = function(itemId) {
                if (confirm('Hapus item ini?')) {
                    $(`.item-row[data-item="${itemId}"]`).remove();
                    calculateTotal();
                    console.log('❌ Item removed:', itemId);
                }
            };

            // Calculate total
            function calculateTotal() {
                // Sum all item subtotals
                let subtotal = 0;
                $('.item-subtotal').each(function() {
                    subtotal += parseFloat($(this).val()) || 0;
                });

                // Get additional costs
                const diskonPersen = parseFloat($('#diskon_persen').val()) || 0;
                const ppnPersen = parseFloat($('#ppn_persen').val()) || 0;
                const pphPersen = parseFloat($('#pph_persen').val()) || 0;
                const ongkir = parseFloat($('#ongkir').val()) || 0;
                const biayaLain = parseFloat($('#biaya_lain').val()) || 0;

                // Calculate
                const diskonNominal = subtotal * (diskonPersen / 100);
                const subtotalAfterDiskon = subtotal - diskonNominal;

                const ppnNominal = subtotalAfterDiskon * (ppnPersen / 100);
                const pphNominal = subtotalAfterDiskon * (pphPersen / 100);

                const total = subtotalAfterDiskon + ongkir + biayaLain + ppnNominal - pphNominal;

                // Update hidden fields
                $('#subtotal_all').val(subtotal);
                $('#diskon_nominal').val(diskonNominal);
                $('#ppn_nominal').val(ppnNominal);
                $('#pph_nominal').val(pphNominal);
                $('#total_po').val(total);

                // Update summary display
                $('#summarySubtotal').text(formatRupiah(subtotal));
                $('#summaryDiskon').text(formatRupiah(diskonNominal));
                $('#summaryOngkir').text(formatRupiah(ongkir));
                $('#summaryBiayaLain').text(formatRupiah(biayaLain));
                $('#summaryPpn').text(formatRupiah(ppnNominal));
                $('#summaryPph').text(formatRupiah(pphNominal));
                $('#summaryTotal').text(formatRupiah(total));
            }

            // Biaya field change handler
            $('.biaya-field').on('input', function() {
                calculateTotal();
            });

            // Attach event handlers to existing items
            $('.item-qty, .item-harga, .item-diskon').each(function() {
                const itemId = $(this).data('item');
                attachItemEventHandlers(itemId);
            });

            // Initial calculation
            $('.item-qty, .item-harga, .item-diskon').each(function() {
                const itemId = $(this).data('item');
                calculateItemSubtotal(itemId);
            });

            // Form validation
            $('#poForm').on('submit', function(e) {
                const vendor = $('#vendor_kode').val();
                const itemCount = $('.item-row').length;

                if (!vendor) {
                    e.preventDefault();
                    alert('⚠️ Vendor harus dipilih!');
                    $('#vendor_kode').focus();
                    return false;
                }

                if (itemCount === 0) {
                    e.preventDefault();
                    alert('⚠️ Minimal 1 item harus ditambahkan!');
                    return false;
                }

                // Check if all items have required fields
                let isValid = true;
                $('.item-row').each(function() {
                    const nama = $(this).find('input[name="item_nama[]"]').val();
                    const qty = $(this).find('input[name="qty_order[]"]').val();
                    const harga = $(this).find('input[name="harga_satuan[]"]').val();

                    if (!nama || !qty || !harga) {
                        isValid = false;
                        return false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('⚠️ Semua item harus memiliki Nama, Qty, dan Harga Satuan!');
                    return false;
                }

                // Disable submit button
                $('#updateBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

                console.log('📤 Form submitted');
                return true;
            });

            // Auto hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Warning before leaving
            let formChanged = false;

            $('#poForm input, #poForm select, #poForm textarea').on('change', function() {
                formChanged = true;
            });

            $(window).on('beforeunload', function() {
                if (formChanged) {
                    return 'Data yang diubah akan hilang. Yakin ingin meninggalkan halaman ini?';
                }
            });

            $('#poForm').on('submit', function() {
                formChanged = false;
            });

            // Console logging
            console.log('✏️ Form Edit Purchase Order Ready!');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('No. PO: <?= $po->no_po ?>');
            console.log('Status: <?= $po->status ?>');
            console.log('Total Items: <?= count($details) ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Features:');
            console.log('✅ Load existing items');
            console.log('✅ Add/remove items');
            console.log('✅ Auto tax from vendor');
            console.log('✅ Real-time calculation');
            console.log('✅ Form validation');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });
    </script>
</body>

</html>
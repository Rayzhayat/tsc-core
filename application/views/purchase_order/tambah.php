<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -20px -20px 20px -20px;
        }

        .section-header {
            background: #f8f9fc;
            padding: 10px 15px;
            border-left: 4px solid #4e73df;
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
            border-color: #4e73df;
        }

        .btn-remove-item {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .calculation-box {
            position: sticky;
            top: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: #e8f5e9;
            border: 1px solid #81c784;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .vendor-info-box.hidden {
            display: none;
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
                            <i class="fas fa-plus-circle text-primary"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('purchase_order') ?>" class="btn btn-secondary btn-sm shadow-sm">
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

                    <form id="poForm" method="POST" action="<?= base_url('purchase_order/simpan') ?>">
                        <div class="row">

                            <!-- Left Column (Form) -->
                            <div class="col-lg-8">

                                <!-- Main Card -->
                                <div class="card shadow mb-4">
                                    <div class="card-body p-0">
                                        <div class="form-header">
                                            <h4 class="mb-2">
                                                <i class="fas fa-file-invoice"></i> Purchase Order Baru
                                            </h4>
                                            <p class="mb-0">No. PO: <strong><?= $no_po ?></strong></p>
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
                                                        value="<?= date('Y-m-d') ?>" required>
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                                                    <select name="kategori" class="form-control" required>
                                                        <option value="">-- Pilih Kategori --</option>
                                                        <option value="barang" selected>Barang</option>
                                                        <option value="jasa">Jasa</option>
                                                        <option value="aset">Aset</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-bold">Jenis Pembelian <span class="text-danger">*</span></label>
                                                    <select name="jenis_pembelian" class="form-control" required>
                                                        <option value="">-- Pilih Jenis --</option>
                                                        <option value="stock" selected>Stock</option>
                                                        <option value="project">Project</option>
                                                        <option value="operational">Operational</option>
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
                                            <div id="vendorInfo" class="vendor-info-box hidden">
                                                <h6 class="font-weight-bold text-success mb-3">
                                                    <i class="fas fa-check-circle"></i> Informasi Vendor
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>Nama:</strong> <span id="displayNama">-</span></p>
                                                        <p class="mb-2"><strong>NPWP:</strong> <span id="displayNpwp">-</span></p>
                                                        <p class="mb-2"><strong>PIC:</strong> <span id="displayPic">-</span></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>No. Telp:</strong> <span id="displayTelp">-</span></p>
                                                        <p class="mb-2"><strong>PPN:</strong> <span id="displayPpn">-</span>%</p>
                                                        <p class="mb-2"><strong>PPh:</strong> <span id="displayPph">-</span>%</p>
                                                    </div>
                                                </div>
                                                <p class="mb-0"><strong>Alamat:</strong><br><span id="displayAlamat">-</span></p>
                                            </div>

                                            <!-- Hidden fields for vendor data -->
                                            <input type="hidden" name="vendor_nama" id="vendor_nama">
                                            <input type="hidden" name="vendor_alamat" id="vendor_alamat">
                                            <input type="hidden" name="vendor_npwp" id="vendor_npwp">
                                            <input type="hidden" name="vendor_pic" id="vendor_pic">
                                            <input type="hidden" name="vendor_telp" id="vendor_telp">

                                            <!-- Section: Items -->
                                            <div class="section-header">
                                                <i class="fas fa-boxes"></i> Daftar Item
                                            </div>

                                            <div id="itemsContainer">
                                                <!-- Items will be added here dynamically -->
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
                                                        value="0" min="0" max="100" step="0.01">
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-bold">PPN (%)</label>
                                                    <input type="number" name="ppn_persen" id="ppn_persen"
                                                        class="form-control biaya-field"
                                                        value="0" min="0" max="100" step="0.01" readonly>
                                                    <small class="text-muted">Auto dari vendor</small>
                                                </div>

                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-bold">PPh (%)</label>
                                                    <input type="number" name="pph_persen" id="pph_persen"
                                                        class="form-control biaya-field"
                                                        value="0" min="0" max="100" step="0.01" readonly>
                                                    <small class="text-muted">Auto dari vendor</small>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Ongkir</label>
                                                    <input type="number" name="ongkir" id="ongkir"
                                                        class="form-control biaya-field"
                                                        value="0" min="0" step="1">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Biaya Lain-lain</label>
                                                    <input type="number" name="biaya_lain" id="biaya_lain"
                                                        class="form-control biaya-field"
                                                        value="0" min="0" step="1">
                                                </div>
                                            </div>

                                            <!-- Hidden calculation fields -->
                                            <input type="hidden" name="subtotal_all" id="subtotal_all" value="0">
                                            <input type="hidden" name="diskon_nominal" id="diskon_nominal" value="0">
                                            <input type="hidden" name="ppn_nominal" id="ppn_nominal" value="0">
                                            <input type="hidden" name="pph_nominal" id="pph_nominal" value="0">
                                            <input type="hidden" name="total_po" id="total_po" value="0">

                                            <!-- Section: Informasi Tambahan -->
                                            <div class="section-header mt-4">
                                                <i class="fas fa-clipboard"></i> Informasi Tambahan
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Expected Delivery</label>
                                                    <input type="date" name="expected_delivery" class="form-control"
                                                        value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="font-weight-bold">Payment Terms</label>
                                                    <select name="payment_terms" class="form-control">
                                                        <option value="cash">Cash</option>
                                                        <option value="tempo 7 hari">Tempo 7 Hari</option>
                                                        <option value="tempo 14 hari" selected>Tempo 14 Hari</option>
                                                        <option value="tempo 30 hari">Tempo 30 Hari</option>
                                                        <option value="tempo 45 hari">Tempo 45 Hari</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-12 mb-3">
                                                    <label class="font-weight-bold">Alamat Pengiriman</label>
                                                    <textarea name="delivery_address" class="form-control" rows="2"
                                                        placeholder="Alamat tujuan pengiriman..."></textarea>
                                                </div>

                                                <div class="col-md-12 mb-3">
                                                    <label class="font-weight-bold">Keterangan</label>
                                                    <textarea name="keterangan" class="form-control" rows="3"
                                                        placeholder="Catatan tambahan untuk PO ini..."></textarea>
                                                </div>
                                            </div>

                                            <!-- Action Buttons -->
                                            <div class="row mt-4">
                                                <div class="col-md-12">
                                                    <button type="button" class="btn btn-secondary"
                                                        onclick="window.location.href='<?= base_url('purchase_order') ?>'">
                                                        <i class="fas fa-times"></i> Batal
                                                    </button>

                                                    <button type="submit" name="status" value="draft" class="btn btn-primary" id="saveDraftBtn">
                                                        <i class="fas fa-save"></i> Simpan Draft
                                                    </button>

                                                    <button type="submit" name="status" value="pending" class="btn btn-success" id="submitBtn">
                                                        <i class="fas fa-paper-plane"></i> Submit untuk Approval
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

    <!-- 🔥 Load Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            let itemCounter = 0;

            // Initialize Select2 for vendor
            $('#vendor_kode').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: '-- Pilih Vendor --',
                allowClear: true
            });

            // ============================================
            // FORMAT NUMBER HELPERS
            // ============================================
            function formatNumber(num) {
                return Math.round(num).toLocaleString('id-ID');
            }

            function formatRupiah(angka) {
                return 'Rp ' + formatNumber(angka);
            }

            // ============================================
            // VENDOR CHANGE HANDLER
            // ============================================
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

                    console.log('✅ Vendor selected:', nama);
                } else {
                    // Hide vendor info
                    $('#vendorInfo').addClass('hidden');

                    // Clear fields
                    $('#ppn_persen').val(0);
                    $('#pph_persen').val(0);
                }

                calculateTotal();
            });

            // ============================================
            // ADD ITEM BUTTON - EVENT DELEGATION
            // ============================================
            $(document).on('click', '#addItemBtn', function(e) {
                e.preventDefault();
                addItemRow();
            });

            // ============================================
            // FUNCTION: ADD ITEM ROW
            // ============================================
            function addItemRow() {
                itemCounter++;

                const itemHtml = `
                    <div class="item-row position-relative" data-item="${itemCounter}">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-item" data-item-id="${itemCounter}">
                            <i class="fas fa-trash"></i>
                        </button>
                        
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="font-weight-bold">Nama Item <span class="text-danger">*</span></label>
                                <input type="text" name="item_nama[]" class="form-control item-nama-input" 
                                       data-item="${itemCounter}"
                                       placeholder="Nama barang/jasa..." required>
                                <small class="text-muted">Kode akan di-generate otomatis dari nama</small>
                            </div>
                            
                            <div class="col-md-4 mb-2">
                                <label class="font-weight-bold">Kode Item</label>
                                <div class="input-group">
                                    <input type="text" name="item_kode[]" class="form-control item-kode-field" 
                                           data-item="${itemCounter}"
                                           placeholder="Auto dari nama..."
                                           readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-magic text-primary" title="Auto-generated"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted text-success">✨ Auto-generated</small>
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

            // ============================================
            // AUTO GENERATE KODE FROM NAMA ITEM
            // ============================================
            $(document).on('input', '.item-nama-input', function() {
                const itemId = $(this).data('item');
                const nama = $(this).val().trim();
                const kodeField = $(`.item-kode-field[data-item="${itemId}"]`);

                if (nama) {
                    // Generate kode from nama
                    let kode = nama
                        .toUpperCase()
                        .replace(/[^A-Z0-9\s]/g, '') // Remove special chars, keep spaces
                        .replace(/\s+/g, '') // Remove all spaces
                        .substring(0, 8); // Max 8 chars

                    // Add counter suffix
                    kode += '-' + String(itemId).padStart(3, '0');

                    kodeField.val(kode);

                    console.log('✨ Auto-generated kode:', kode, 'from:', nama);
                } else {
                    kodeField.val('');
                }
            });

            // ============================================
            // ATTACH EVENT HANDLERS TO ITEM FIELDS
            // ============================================
            function attachItemEventHandlers(itemId) {
                $(`.item-qty[data-item="${itemId}"], .item-harga[data-item="${itemId}"], .item-diskon[data-item="${itemId}"]`)
                    .on('input', function() {
                        calculateItemSubtotal(itemId);
                    });
            }

            // ============================================
            // CALCULATE ITEM SUBTOTAL
            // ============================================
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

            // ============================================
            // REMOVE ITEM - EVENT DELEGATION
            // ============================================
            $(document).on('click', '.btn-remove-item', function(e) {
                e.preventDefault();
                const itemId = $(this).data('item-id');

                if (confirm('Hapus item ini?')) {
                    $(`.item-row[data-item="${itemId}"]`).remove();
                    calculateTotal();
                    console.log('❌ Item removed:', itemId);
                }
            });

            // ============================================
            // CALCULATE TOTAL
            // ============================================
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

            // ============================================
            // BIAYA FIELD CHANGE HANDLER
            // ============================================
            $(document).on('input', '.biaya-field', function() {
                calculateTotal();
            });

            // ============================================
            // FORM VALIDATION
            // ============================================
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
                    $('#addItemBtn').focus();
                    return false;
                }

                // Check if all items have required fields
                let isValid = true;
                let errorMsg = '';

                $('.item-row').each(function(index) {
                    const itemNum = index + 1;
                    const nama = $(this).find('input[name="item_nama[]"]').val();
                    const qty = $(this).find('input[name="qty_order[]"]').val();
                    const harga = $(this).find('input[name="harga_satuan[]"]').val();

                    if (!nama) {
                        errorMsg = `⚠️ Item #${itemNum}: Nama item harus diisi!`;
                        isValid = false;
                        return false;
                    }

                    if (!qty || parseFloat(qty) <= 0) {
                        errorMsg = `⚠️ Item #${itemNum}: Qty harus lebih dari 0!`;
                        isValid = false;
                        return false;
                    }

                    if (!harga || parseFloat(harga) <= 0) {
                        errorMsg = `⚠️ Item #${itemNum}: Harga satuan harus lebih dari 0!`;
                        isValid = false;
                        return false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert(errorMsg);
                    return false;
                }

                // Disable submit buttons
                $('#saveDraftBtn, #submitBtn').prop('disabled', true);
                const clickedBtn = $(document.activeElement);
                clickedBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                console.log('📤 Form submitted');
                return true;
            });

            // ============================================
            // AUTO ADD FIRST ITEM
            // ============================================
            addItemRow();

            // ============================================
            // AUTO HIDE ALERTS
            // ============================================
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // ============================================
            // WARNING BEFORE LEAVING
            // ============================================
            let formChanged = false;

            $('#poForm input, #poForm select, #poForm textarea').on('change', function() {
                formChanged = true;
            });

            $(window).on('beforeunload', function() {
                if (formChanged) {
                    return 'Data yang diisi akan hilang. Yakin ingin meninggalkan halaman ini?';
                }
            });

            $('#poForm').on('submit', function() {
                formChanged = false;
            });

            // ============================================
            // CONSOLE LOGGING
            // ============================================
            console.log('📦 Form Tambah Purchase Order Ready!');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('No. PO: <?= $no_po ?>');
            console.log('Total Vendors: <?= count($vendors) ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Features:');
            console.log('✅ Dynamic item rows with event delegation');
            console.log('✅ Auto kode from nama item (real-time)');
            console.log('✅ Auto tax from vendor');
            console.log('✅ Real-time calculation');
            console.log('✅ Enhanced form validation');
            console.log('✅ Add/Remove items working');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Auto Kode Examples:');
            console.log('- "Laptop Dell" → LAPTOPDELL-001');
            console.log('- "Mouse Wireless" → MOUSEWIR-002');
            console.log('- "Printer HP" → PRINTERH-003');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .receive-header {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .section-header {
            background: #f8f9fc;
            padding: 10px 15px;
            border-left: 4px solid #36b9cc;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .item-card {
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .item-card:hover {
            border-color: #36b9cc;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .item-card.selected {
            border-color: #1cc88a;
            background-color: #f0fdf4;
        }

        .item-header {
            background: #36b9cc;
            color: white;
            padding: 10px 15px;
            border-radius: 5px 5px 0 0;
            margin: -20px -20px 15px -20px;
            font-weight: bold;
        }

        .qty-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border: 2px solid #e3e6f0;
            border-radius: 5px;
            margin-top: 10px;
        }

        .kondisi-badge {
            font-size: 1rem;
            padding: 5px 15px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .kondisi-badge:hover {
            transform: scale(1.05);
        }

        input[type="radio"]:checked+.kondisi-badge {
            box-shadow: 0 0 0 3px rgba(54, 185, 204, 0.3);
        }

        .summary-box {
            position: sticky;
            top: 20px;
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
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
            font-size: 1.2rem;
            font-weight: bold;
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
                            <i class="fas fa-box text-info"></i> <?= $title ?>
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
                    <div class="receive-header shadow">
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
                                    <i class="fas fa-money-bill-wave"></i>
                                    <strong>Total PO:</strong> Rp <?= number_format($po->total_po, 0, ',', '.') ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <h4 class="mb-2">Status:</h4>
                                <?php
                                $badge_color = [
                                    'approved' => 'success',
                                    'partial_received' => 'warning',
                                    'received' => 'primary'
                                ];
                                $color = $badge_color[$po->status] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $color ?>" style="font-size: 1.1rem; padding: 8px 20px;">
                                    <?= strtoupper(str_replace('_', ' ', $po->status)) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <form id="receiveForm" method="POST" action="<?= base_url('purchase_order/save_receiving') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="po_id" value="<?= $po->id ?>">

                        <div class="row">

                            <!-- Left Column (Items) -->
                            <div class="col-lg-8">

                                <!-- Main Card -->
                                <div class="card shadow mb-4">
                                    <div class="card-body">

                                        <!-- Section: Informasi Penerimaan -->
                                        <div class="section-header">
                                            <i class="fas fa-info-circle"></i> Informasi Penerimaan
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Tanggal Terima <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_terima" class="form-control"
                                                    value="<?= date('Y-m-d') ?>" required>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Diterima Oleh <span class="text-danger">*</span></label>
                                                <input type="text" name="received_by" class="form-control"
                                                    value="<?= $this->session->userdata('login')['username'] ?? '' ?>"
                                                    placeholder="Nama penerima" required>
                                            </div>
                                        </div>

                                        <!-- Section: Daftar Item -->
                                        <div class="section-header">
                                            <i class="fas fa-boxes"></i> Daftar Item untuk Diterima
                                        </div>

                                        <div id="itemsContainer">
                                            <?php
                                            $item_no = 1;
                                            foreach ($details as $item):
                                                $remaining_qty = $item->qty_order - $item->qty_received;

                                                // Skip if already fully received
                                                if ($remaining_qty <= 0) continue;
                                            ?>
                                                <div class="item-card" data-item-id="<?= $item->id ?>">
                                                    <div class="item-header">
                                                        <div class="custom-control custom-checkbox d-inline">
                                                            <input type="checkbox" class="custom-control-input item-checkbox"
                                                                id="item_<?= $item->id ?>"
                                                                name="items[<?= $item->id ?>][selected]"
                                                                value="1"
                                                                onchange="toggleItem(<?= $item->id ?>)">
                                                            <label class="custom-control-label" for="item_<?= $item->id ?>">
                                                                <strong>Item #<?= $item_no++ ?>: <?= htmlspecialchars($item->item_nama) ?></strong>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="item-body" style="display: none;">

                                                        <!-- Item Info -->
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <p class="mb-1"><strong>Kode:</strong> <?= htmlspecialchars($item->item_kode) ?: '-' ?></p>
                                                                <p class="mb-1"><strong>Satuan:</strong> <?= htmlspecialchars($item->item_satuan) ?></p>
                                                                <?php if (!empty($item->item_spesifikasi)): ?>
                                                                    <p class="mb-1"><strong>Spesifikasi:</strong><br>
                                                                        <small class="text-muted"><?= htmlspecialchars($item->item_spesifikasi) ?></small>
                                                                    </p>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="qty-info">
                                                                    <p class="mb-1"><strong>Qty Order:</strong> <?= number_format($item->qty_order, 2) ?></p>
                                                                    <p class="mb-1"><strong>Sudah Diterima:</strong> <?= number_format($item->qty_received, 2) ?></p>
                                                                    <p class="mb-0"><strong>Sisa:</strong>
                                                                        <span class="text-danger font-weight-bold">
                                                                            <?= number_format($remaining_qty, 2) ?>
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Qty Received -->
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="font-weight-bold">Qty Diterima <span class="text-danger">*</span></label>
                                                                <input type="number"
                                                                    name="items[<?= $item->id ?>][qty_received]"
                                                                    class="form-control qty-received"
                                                                    data-item-id="<?= $item->id ?>"
                                                                    data-max="<?= $remaining_qty ?>"
                                                                    placeholder="0"
                                                                    min="0"
                                                                    max="<?= $remaining_qty ?>"
                                                                    step="0.01"
                                                                    disabled>
                                                                <small class="text-muted">Max: <?= number_format($remaining_qty, 2) ?></small>
                                                            </div>

                                                            <div class="col-md-6 mb-3">
                                                                <label class="font-weight-bold">Qty Ditolak</label>
                                                                <input type="number"
                                                                    name="items[<?= $item->id ?>][qty_rejected]"
                                                                    class="form-control qty-rejected"
                                                                    data-item-id="<?= $item->id ?>"
                                                                    placeholder="0"
                                                                    min="0"
                                                                    step="0.01"
                                                                    disabled>
                                                                <small class="text-muted">Opsional (jika ada barang rusak/cacat)</small>
                                                            </div>
                                                        </div>

                                                        <!-- Kondisi -->
                                                        <div class="mb-3">
                                                            <label class="font-weight-bold d-block">Kondisi <span class="text-danger">*</span></label>
                                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                                <label class="btn btn-outline-success kondisi-label">
                                                                    <input type="radio"
                                                                        name="items[<?= $item->id ?>][kondisi]"
                                                                        value="baik"
                                                                        class="kondisi-radio"
                                                                        disabled>
                                                                    <span class="kondisi-badge badge badge-success">
                                                                        <i class="fas fa-check-circle"></i> Baik
                                                                    </span>
                                                                </label>
                                                                <label class="btn btn-outline-warning kondisi-label">
                                                                    <input type="radio"
                                                                        name="items[<?= $item->id ?>][kondisi]"
                                                                        value="kurang"
                                                                        class="kondisi-radio"
                                                                        disabled>
                                                                    <span class="kondisi-badge badge badge-warning">
                                                                        <i class="fas fa-exclamation-triangle"></i> Kurang
                                                                    </span>
                                                                </label>
                                                                <label class="btn btn-outline-danger kondisi-label">
                                                                    <input type="radio"
                                                                        name="items[<?= $item->id ?>][kondisi]"
                                                                        value="rusak"
                                                                        class="kondisi-radio"
                                                                        disabled>
                                                                    <span class="kondisi-badge badge badge-danger">
                                                                        <i class="fas fa-times-circle"></i> Rusak
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <!-- Foto Bukti -->
                                                        <div class="mb-3">
                                                            <label class="font-weight-bold">Foto Bukti Penerimaan</label>
                                                            <div class="custom-file">
                                                                <input type="file"
                                                                    name="items[<?= $item->id ?>][foto_bukti]"
                                                                    class="custom-file-input foto-input"
                                                                    data-item-id="<?= $item->id ?>"
                                                                    accept="image/*"
                                                                    disabled>
                                                                <label class="custom-file-label">Pilih foto...</label>
                                                            </div>
                                                            <small class="text-muted">Format: JPG, PNG, Max 2MB (Opsional)</small>
                                                            <div class="preview-container" id="preview_<?= $item->id ?>" style="display: none;">
                                                                <img src="" class="preview-image" alt="Preview">
                                                            </div>
                                                        </div>

                                                        <!-- Keterangan -->
                                                        <div class="mb-3">
                                                            <label class="font-weight-bold">Keterangan</label>
                                                            <textarea name="items[<?= $item->id ?>][keterangan]"
                                                                class="form-control keterangan-input"
                                                                rows="2"
                                                                placeholder="Catatan penerimaan (opsional)..."
                                                                disabled></textarea>
                                                        </div>

                                                    </div>
                                                </div>

                                                <input type="hidden" name="items[<?= $item->id ?>][po_detail_id]" value="<?= $item->id ?>">

                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Alert jika tidak ada item -->
                                        <?php if ($item_no == 1): ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i>
                                                Semua item sudah diterima lengkap.
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </div>

                            </div>
                            <!-- Right Column (Summary) -->
                            <div class="col-lg-4">

                                <!-- Summary Box -->
                                <div class="summary-box shadow">
                                    <h5 class="mb-3">
                                        <i class="fas fa-clipboard-list"></i> Ringkasan Penerimaan
                                    </h5>

                                    <div class="summary-row">
                                        <small>Total Item:</small>
                                        <strong id="summaryTotalItems">0</strong>
                                    </div>

                                    <div class="summary-row">
                                        <small>Item Dipilih:</small>
                                        <strong id="summarySelectedItems">0</strong>
                                    </div>

                                    <div class="summary-row">
                                        <small>Total Qty Diterima:</small>
                                        <strong id="summaryTotalQty">0</strong>
                                    </div>

                                    <div class="summary-row">
                                        <small>Total Qty Ditolak:</small>
                                        <strong id="summaryTotalRejected">0</strong>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="submitBtn">
                                        <i class="fas fa-save"></i> Simpan Penerimaan
                                    </button>
                                    <a href="<?= base_url('purchase_order/detail/' . $po->id) ?>"
                                        class="btn btn-secondary btn-lg btn-block">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
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

            let selectedItems = 0;
            let totalItems = $('.item-card').length;

            // Update summary on load
            updateSummary();

            // ============================================
            // Toggle Item Selection
            // ============================================
            window.toggleItem = function(itemId) {
                const checkbox = $('#item_' + itemId);
                const itemCard = $('[data-item-id="' + itemId + '"]');
                const itemBody = itemCard.find('.item-body');

                if (checkbox.is(':checked')) {
                    // Enable item
                    itemCard.addClass('selected');
                    itemBody.slideDown(300);

                    // Enable all inputs
                    itemBody.find('.qty-received').prop('disabled', false).attr('required', true);
                    itemBody.find('.qty-rejected').prop('disabled', false);
                    itemBody.find('.kondisi-radio').prop('disabled', false);
                    itemBody.find('.foto-input').prop('disabled', false);
                    itemBody.find('.keterangan-input').prop('disabled', false);

                    // Set default kondisi to "baik"
                    itemBody.find('.kondisi-radio[value="baik"]').prop('checked', true).parent().addClass('active');

                    selectedItems++;
                } else {
                    // Disable item
                    itemCard.removeClass('selected');
                    itemBody.slideUp(300);

                    // Disable all inputs and clear values
                    itemBody.find('.qty-received').prop('disabled', true).removeAttr('required').val('');
                    itemBody.find('.qty-rejected').prop('disabled', true).val('');
                    itemBody.find('.kondisi-radio').prop('disabled', true).prop('checked', false).parent().removeClass('active');
                    itemBody.find('.foto-input').prop('disabled', true).val('');
                    itemBody.find('.keterangan-input').prop('disabled', true).val('');

                    // Clear preview
                    $('#preview_' + itemId).hide();

                    selectedItems--;
                }

                updateSummary();
            };

            // ============================================
            // Qty Input Validation
            // ============================================
            $('.qty-received').on('input', function() {
                const max = parseFloat($(this).data('max'));
                let val = parseFloat($(this).val()) || 0;

                if (val > max) {
                    $(this).val(max);
                    alert('⚠️ Qty tidak boleh melebihi sisa yang belum diterima!');
                }

                if (val < 0) {
                    $(this).val(0);
                }

                updateSummary();
            });

            $('.qty-rejected').on('input', function() {
                let val = parseFloat($(this).val()) || 0;

                if (val < 0) {
                    $(this).val(0);
                }

                updateSummary();
            });

            // ============================================
            // Photo Preview
            // ============================================
            $('.foto-input').on('change', function() {
                const itemId = $(this).data('item-id');
                const file = this.files[0];
                const preview = $('#preview_' + itemId);

                if (file) {
                    // Validate file size (max 2MB)
                    if (file.size > 2048000) {
                        alert('⚠️ Ukuran file terlalu besar! Maksimal 2MB.');
                        $(this).val('');
                        preview.hide();
                        return;
                    }

                    // Validate file type
                    if (!file.type.match('image.*')) {
                        alert('⚠️ File harus berupa gambar (JPG, PNG)!');
                        $(this).val('');
                        preview.hide();
                        return;
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.find('img').attr('src', e.target.result);
                        preview.fadeIn();
                    };
                    reader.readAsDataURL(file);

                    // Update label
                    $(this).next('.custom-file-label').text(file.name);
                } else {
                    preview.hide();
                    $(this).next('.custom-file-label').text('Pilih foto...');
                }
            });

            // ============================================
            // Update Summary
            // ============================================
            function updateSummary() {
                let totalQty = 0;
                let totalRejected = 0;

                $('.item-checkbox:checked').each(function() {
                    const itemId = $(this).closest('.item-card').data('item-id');
                    const qtyReceived = parseFloat($('[data-item-id="' + itemId + '"].qty-received').val()) || 0;
                    const qtyRejected = parseFloat($('[data-item-id="' + itemId + '"].qty-rejected').val()) || 0;

                    totalQty += qtyReceived;
                    totalRejected += qtyRejected;
                });

                $('#summaryTotalItems').text(totalItems);
                $('#summarySelectedItems').text(selectedItems);
                $('#summaryTotalQty').text(totalQty.toFixed(2));
                $('#summaryTotalRejected').text(totalRejected.toFixed(2));
            }

            // ============================================
            // Form Validation
            // ============================================
            $('#receiveForm').on('submit', function(e) {
                const selectedCount = $('.item-checkbox:checked').length;

                if (selectedCount === 0) {
                    e.preventDefault();
                    alert('⚠️ Pilih minimal 1 item untuk diterima!');
                    return false;
                }

                // Validate each selected item
                let isValid = true;

                $('.item-checkbox:checked').each(function() {
                    const itemId = $(this).closest('.item-card').data('item-id');
                    const itemBody = $(this).closest('.item-card').find('.item-body');
                    const itemName = $(this).closest('.item-card').find('.item-header strong').text();

                    const qtyReceived = parseFloat(itemBody.find('.qty-received').val()) || 0;
                    const kondisi = itemBody.find('.kondisi-radio:checked').val();

                    if (qtyReceived <= 0) {
                        e.preventDefault();
                        alert('⚠️ ' + itemName + ': Qty Diterima harus lebih dari 0!');
                        itemBody.find('.qty-received').focus();
                        isValid = false;
                        return false;
                    }

                    if (!kondisi) {
                        e.preventDefault();
                        alert('⚠️ ' + itemName + ': Pilih kondisi barang!');
                        isValid = false;
                        return false;
                    }
                });

                if (!isValid) {
                    return false;
                }

                // Confirm submission
                if (!confirm('Simpan penerimaan barang untuk ' + selectedCount + ' item?')) {
                    e.preventDefault();
                    return false;
                }

                // Disable submit button
                $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

                console.log('📤 Form Receiving submitted');
                return true;
            });

            // ============================================
            // Kondisi Radio Button Styling
            // ============================================
            $('.kondisi-radio').on('change', function() {
                const name = $(this).attr('name');
                $('input[name="' + name + '"]').parent().removeClass('active');
                $(this).parent().addClass('active');
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

            $('.item-checkbox, .qty-received, .qty-rejected, .kondisi-radio, .foto-input, .keterangan-input').on('change', function() {
                formChanged = true;
            });

            $(window).on('beforeunload', function() {
                if (formChanged && selectedItems > 0) {
                    return 'Data yang diinput akan hilang. Yakin ingin meninggalkan halaman ini?';
                }
            });

            $('#receiveForm').on('submit', function() {
                formChanged = false;
            });

            // ============================================
            // Console Logging
            // ============================================
            console.log('📦 Form Receiving Barang Ready!');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('No. PO: <?= $po->no_po ?>');
            console.log('Status: <?= $po->status ?>');
            console.log('Total Items Available: ' + totalItems);
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Features:');
            console.log('✅ Multi-item selection');
            console.log('✅ Qty validation (max remaining)');
            console.log('✅ Kondisi selection (Baik/Kurang/Rusak)');
            console.log('✅ Photo upload with preview');
            console.log('✅ Real-time summary');
            console.log('✅ Form validation');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .item-row,
        .deduction-row {
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fc;
            border-radius: 5px;
        }

        .calculation-box {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .calculation-box .row {
            margin-bottom: 10px;
        }

        .calculation-box .label {
            font-weight: 600;
        }

        .calculation-box .value {
            font-size: 1.2em;
            font-weight: bold;
        }

        .grand-total {
            font-size: 1.5em !important;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }

        /* ✅ Duplicate warning styles */
        .border-danger {
            border-color: #e74a3b !important;
            box-shadow: 0 0 0 0.2rem rgba(231, 74, 59, 0.25) !important;
        }

        .border-success {
            border-color: #1cc88a !important;
            box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25) !important;
        }

        #duplicateWarning {
            animation: shake 0.5s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-plus-circle text-success"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('invoice_tsc') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <form method="post" action="<?= base_url('invoice_tsc/proses_tambah') ?>" id="invoiceForm">

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-lg-8">

                                <!-- Invoice Header -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 bg-success text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-file-invoice"></i> Informasi Invoice
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">No. Invoice <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="no_invoice" id="no_invoice"
                                                    class="form-control" placeholder="TSC-SPV/00001/XII/2025" required>
                                                <small class="text-muted">Contoh: TSC-SPV/00022/XI/2025</small>
                                                <!-- ✅ Duplicate warning will appear here -->
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Tanggal Invoice <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" name="invoice_date" id="invoice_date"
                                                    class="form-control" value="<?= date('Y-m-d') ?>" required>
                                            </div>

                                            <!-- ✅ NEW: Manual Due Date Input (ganti yang otomatis +14 hari) -->
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Jatuh Tempo <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" name="due_date" id="due_date" class="form-control"
                                                    value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                                                <small class="text-muted">Isi manual tanggal jatuh tempo</small>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Periode Shipment</label>
                                                <select name="periode_shipment" class="form-control">
                                                    <option value="">-- Pilih Bulan (Opsional) --</option>
                                                    <?php foreach ($bulan_options as $bulan): ?>
                                                        <option value="<?= $bulan ?>"><?= $bulan ?></option>
                                                    <?php endforeach ?>
                                                </select>
                                                <small class="text-muted">Pilih bulan periode shipment</small>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Customer <span
                                                        class="text-danger">*</span></label>
                                                <select name="customer_id" id="customer_id" class="form-control"
                                                    required>
                                                    <option value="">-- Pilih Customer --</option>
                                                    <?php foreach ($customers as $cust): ?>
                                                        <option value="<?= $cust->kode ?>">
                                                            <?= htmlspecialchars($cust->nama) ?>
                                                        </option>
                                                    <?php endforeach ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">No. Faktur Pajak</label>
                                                <input type="text" name="no_faktur" class="form-control"
                                                    placeholder="(Optional)">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">No. PO (Purchase Order)</label>
                                                <input type="text" name="no_po" class="form-control"
                                                    placeholder="Contoh: PO-2024-001" maxlength="100">
                                                <small class="form-text text-muted">Nomor Purchase Order dari
                                                    customer</small>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Akun Pendapatan <span
                                                        class="text-danger">*</span></label>
                                                <select name="revenue_account_id" id="revenue_account_id"
                                                    class="form-control" required>
                                                    <option value="">-- Pilih Akun Pendapatan --</option>
                                                    <?php foreach ($revenue_accounts as $acc): ?>
                                                        <option value="<?= $acc->id ?>">
                                                            (<?= $acc->kode_perkiraan ?>)
                                                            <?= htmlspecialchars($acc->nama) ?>
                                                        </option>
                                                    <?php endforeach ?>
                                                </select>
                                                <small class="text-muted">Pilih kategori pendapatan yang sesuai</small>
                                            </div>
                                        </div>

                                        <!-- Customer Info Display -->
                                        <div id="customerInfo" style="display:none;" class="alert alert-info">
                                            <strong>Info Customer:</strong><br>
                                            <span id="customerDisplay"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Items -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 bg-primary text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-list"></i> Rincian Item
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="itemsContainer">
                                            <div class="item-row" data-index="0">
                                                <div class="row">
                                                    <div class="col-md-7">
                                                        <label class="font-weight-bold">Deskripsi</label>
                                                        <textarea name="item_deskripsi[]" class="form-control" rows="2"
                                                            placeholder="FTL LH Periode..." required></textarea>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="font-weight-bold">Jumlah (Rp)</label>
                                                        <input type="text" name="item_jumlah[]"
                                                            class="form-control item-amount" placeholder="0" required>
                                                    </div>
                                                    <div class="col-md-1 text-center">
                                                        <label class="font-weight-bold">&nbsp;</label>
                                                        <button type="button"
                                                            class="btn btn-danger btn-sm btn-block remove-item"
                                                            onclick="removeItem(this)" style="display:none;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                                            <i class="fas fa-plus"></i> Tambah Item
                                        </button>
                                    </div>
                                </div>

                                <!-- Deductions -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 bg-warning text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-minus-circle"></i> Potongan (Optional)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="deductionsContainer">
                                            <!-- Dynamic deductions will be added here -->
                                        </div>

                                        <button type="button" class="btn btn-warning btn-sm" onclick="addDeduction()">
                                            <i class="fas fa-plus"></i> Tambah Potongan
                                        </button>
                                    </div>
                                </div>

                                <!-- Keterangan -->
                                <div class="card shadow mb-4">
                                    <div class="card-body">
                                        <label class="font-weight-bold">Keterangan</label>
                                        <textarea name="keterangan" class="form-control" rows="3"
                                            placeholder="Contoh: Rekapan terlampir"></textarea>
                                    </div>
                                </div>

                            </div>

                            <!-- Right Column - Summary -->
                            <div class="col-lg-4">
                                <div class="card shadow mb-4" style="position: sticky; top: 20px;">
                                    <div class="card-header py-3 bg-success text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-calculator"></i> Ringkasan
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="calculation-box">
                                            <div class="row">
                                                <div class="col-6 label">Subtotal:</div>
                                                <div class="col-6 text-right value" id="display_subtotal">Rp 0</div>
                                            </div>

                                            <div class="row">
                                                <div class="col-6 label">
                                                    PPN (<span id="display_ppn_percent">0</span>%):
                                                </div>
                                                <div class="col-6 text-right value" id="display_ppn">Rp 0</div>
                                            </div>

                                            <div class="row">
                                                <div class="col-6 label">
                                                    PPH (<span id="display_pph_percent">0</span>%):
                                                </div>
                                                <div class="col-6 text-right value text-warning" id="display_pph">Rp 0
                                                </div>
                                            </div>

                                            <hr style="border-color: rgba(255,255,255,0.3);">

                                            <div class="grand-total">
                                                <div class="row">
                                                    <div class="col-6 label">GRAND TOTAL:</div>
                                                    <div class="col-6 text-right value" id="display_grand_total">Rp 0
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <small><strong>Terbilang:</strong></small><br>
                                                <small id="display_terbilang" style="font-style: italic;">-</small>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button type="submit" name="action" value="save"
                                                class="btn btn-success btn-block">
                                                <i class="fas fa-save"></i> Simpan Invoice
                                            </button>
                                            <button type="submit" name="action" value="save_and_export"
                                                class="btn btn-primary btn-block">
                                                <i class="fas fa-file-pdf"></i> Simpan & Export PDF
                                            </button>
                                            <a href="<?= base_url('invoice_tsc') ?>"
                                                class="btn btn-secondary btn-block">
                                                <i class="fas fa-times"></i> Batal
                                            </a>
                                        </div>
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
        let itemIndex = 1;
        let deductionIndex = 0;
        let customerData = null;
        let duplicateCheckTimeout = null;

        // ✅ REAL-TIME DUPLICATE INVOICE CHECK
        $(document).ready(function () {
            $('input[name="no_invoice"]').on('input', function () {
                const noInvoice = $(this).val().trim();

                // Clear previous warning
                $('#duplicateWarning').remove();
                $(this).removeClass('border-danger border-success');

                if (noInvoice.length < 3) {
                    return;
                }

                // Debounce - wait 500ms after user stops typing
                clearTimeout(duplicateCheckTimeout);

                duplicateCheckTimeout = setTimeout(function () {
                    $.ajax({
                        url: '<?= base_url('invoice_tsc/check_duplicate') ?>',
                        type: 'POST',
                        data: { no_invoice: noInvoice },
                        dataType: 'json',
                        success: function (response) {
                            if (response.exists) {
                                // Show warning
                                const warningHtml = `
                                    <div id="duplicateWarning" class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <strong>Peringatan!</strong> No. Invoice <strong>${noInvoice}</strong> sudah ada di database!
                                        <br><small>Invoice dibuat pada: ${response.invoice_date} | Customer: ${response.customer_nama}</small>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                                        </button>
                                    </div>
                                `;

                                $('input[name="no_invoice"]').parent().append(warningHtml);
                                $('input[name="no_invoice"]').addClass('border-danger');

                                // Animate warning
                                $('#duplicateWarning').hide().fadeIn(300);

                            } else {
                                // Show success indicator
                                $('input[name="no_invoice"]').addClass('border-success');
                            }
                        },
                        error: function () {
                            console.error('Failed to check duplicate invoice');
                        }
                    });
                }, 500); // Wait 500ms after user stops typing
            });
        });

        // Load customer data
        $('#customer_id').change(function () {
            const customerId = $(this).val();
            if (!customerId) {
                $('#customerInfo').hide();
                customerData = null;
                calculate();
                return;
            }

            $.post('<?= base_url('invoice_tsc/ajax_get_customer') ?>', {
                customer_id: customerId
            }, function (response) {
                const data = JSON.parse(response);
                if (data.success) {
                    customerData = data.data;

                    const ppnClean = String(customerData.ppn || 0).replace('%', '');
                    const pphClean = String(customerData.pph || 0).replace('%', '');

                    let info = `<strong>${customerData.nama}</strong><br>`;
                    info += `${customerData.alamat || ''}<br>`;
                    info += `PIC: ${customerData.pic || '-'}<br>`;
                    info += `PPN: ${ppnClean}% | PPH: ${pphClean}%`;

                    $('#customerDisplay').html(info);
                    $('#customerInfo').fadeIn();

                    $('#display_ppn_percent').text(ppnClean);
                    $('#display_pph_percent').text(pphClean);

                    customerData.ppn = ppnClean;
                    customerData.pph = pphClean;

                    calculate();
                }
            });
        });

        // Add item row
        function addItem() {
            const html = `
            <div class="item-row" data-index="${itemIndex}">
                <div class="row">
                    <div class="col-md-7">
                        <label class="font-weight-bold">Deskripsi</label>
                        <textarea name="item_deskripsi[]" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">Jumlah (Rp)</label>
                        <input type="text" name="item_jumlah[]" class="form-control item-amount" placeholder="0" required>
                    </div>
                    <div class="col-md-1 text-center">
                        <label class="font-weight-bold">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm btn-block remove-item" onclick="removeItem(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
            $('#itemsContainer').append(html);
            itemIndex++;
            updateRemoveButtons();
        }

        // Add deduction row
        function addDeduction() {
            const html = `
            <div class="deduction-row" data-index="${deductionIndex}">
                <div class="row">
                    <div class="col-md-7">
                        <label class="font-weight-bold">Deskripsi Potongan</label>
                        <input type="text" name="deduction_deskripsi[]" class="form-control" placeholder="Contoh: Discount FTL LH">
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold">Jumlah (Rp)</label>
                        <input type="text" name="deduction_jumlah[]" class="form-control deduction-amount" placeholder="0">
                    </div>
                    <div class="col-md-1 text-center">
                        <label class="font-weight-bold">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm btn-block" onclick="removeDeduction(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
            $('#deductionsContainer').append(html);
            deductionIndex++;
        }

        // Remove item
        function removeItem(btn) {
            $(btn).closest('.item-row').remove();
            updateRemoveButtons();
            calculate();
        }

        // Remove deduction
        function removeDeduction(btn) {
            $(btn).closest('.deduction-row').remove();
            calculate();
        }

        // Update remove buttons visibility
        function updateRemoveButtons() {
            const itemCount = $('.item-row').length;
            if (itemCount > 1) {
                $('.remove-item').show();
            } else {
                $('.remove-item').hide();
            }
        }

        // Format number
        function formatRupiah(angka) {
            return 'Rp ' + angka.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function calculate() {
            let subtotal_items = 0;
            let total_deductions = 0;

            // Sum items ONLY
            $('.item-amount').each(function () {
                const val = parseFloat($(this).val().replace(/[^0-9]/g, '')) || 0;
                subtotal_items += val;
            });

            // Sum deductions SEPARATELY
            $('.deduction-amount').each(function () {
                const val = parseFloat($(this).val().replace(/[^0-9]/g, '')) || 0;
                total_deductions += val;
            });

            // Subtotal setelah potongan
            const subtotal_after_deduction = subtotal_items - total_deductions;

            // PPN & PPH dari SUBTOTAL SETELAH POTONGAN!
            const ppnPercent = customerData ? parseFloat(customerData.ppn || 0) : 0;
            const pphPercent = customerData ? parseFloat(customerData.pph || 0) : 0;

            const ppnAmount = subtotal_after_deduction * (ppnPercent / 100);
            const pphAmount = subtotal_after_deduction * (pphPercent / 100);

            // Grand total
            const grandTotal = subtotal_after_deduction + ppnAmount - pphAmount;

            // Update display
            $('#display_subtotal').text(formatRupiah(subtotal_after_deduction));
            $('#display_ppn').text(formatRupiah(ppnAmount));
            $('#display_pph').text('- ' + formatRupiah(pphAmount));
            $('#display_grand_total').text(formatRupiah(grandTotal));

            if (grandTotal > 0) {
                $('#display_terbilang').text(formatRupiah(grandTotal));
            } else {
                $('#display_terbilang').text('-');
            }
        }

        // Auto-calculate on input
        $(document).on('input', '.item-amount, .deduction-amount', function () {
            calculate();
        });

        // Format number input on blur
        $(document).on('blur', '.item-amount, .deduction-amount', function () {
            const val = parseFloat($(this).val().replace(/[^0-9]/g, '')) || 0;
            $(this).val(val.toFixed(0));
        });

        // Initial setup
        $(document).ready(function () {
            updateRemoveButtons();
        });

        // Due date auto-fill
        document.getElementById('invoice_date').addEventListener('change', function () {
            const invoiceDate = new Date(this.value);
            if (!isNaN(invoiceDate)) {
                invoiceDate.setDate(invoiceDate.getDate() + 14);
                const year = invoiceDate.getFullYear();
                const month = String(invoiceDate.getMonth() + 1).padStart(2, '0');
                const day = String(invoiceDate.getDate()).padStart(2, '0');
                document.getElementById('due_date').value = `${year}-${month}-${day}`;
            }
        });
    </script>
</body>

</html>
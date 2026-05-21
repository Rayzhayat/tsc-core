<!-- ubah_paid.php -->
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
            background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
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

        .locked-field {
            background: #e9ecef !important;
            cursor: not-allowed;
            color: #6c757d;
        }

        .superadmin-banner {
            background: linear-gradient(135deg, #f6c23e 0%, #d4a017 100%);
            color: #333;
            border: none;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .locked-section {
            position: relative;
            opacity: 0.85;
        }

        .locked-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(108, 117, 125, 0.05);
            border-radius: 5px;
            pointer-events: none;
        }

        .badge-paid {
            background: #1cc88a;
            color: #ffffff !important;
            font-size: 13px;
            padding: 5px 10px;
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
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-user-shield text-warning"></i> <?= $title ?>
                        </h1>
                        <a href="<?= base_url('invoice_tsc') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- ⚠️ Superadmin Warning Banner -->
                    <div class="superadmin-banner shadow">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle fa-2x mr-3 mt-1"></i>
                            <div>
                                <strong style="font-size: 1.1em;">Mode Edit Superadmin — Invoice PAID</strong>
                                <hr style="border-color: rgba(0,0,0,0.2); margin: 6px 0;">
                                <p class="mb-1">Anda hanya dapat mengubah <strong>data informasi</strong>: Customer,
                                    Tanggal, No. Faktur, No. PO, Periode, dan Keterangan.</p>
                                <p class="mb-0">
                                    <i class="fas fa-lock mr-1"></i>
                                    <strong>Nominal, PPN, PPH, Grand Total, Status, dan Jurnal Akuntansi TIDAK akan
                                        berubah.</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <form method="post" action="<?= base_url('invoice_tsc/proses_ubah_paid/' . $invoice->id) ?>"
                        id="invoiceForm">

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-lg-8">

                                <!-- Invoice Header -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 bg-gradient-warning text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-file-invoice"></i> Informasi Invoice
                                            <span class="badge badge-paid ml-2">
                                                <i class="fas fa-check-circle"></i> PAID
                                            </span>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">No. Invoice <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="no_invoice" class="form-control"
                                                    value="<?= htmlspecialchars($invoice->no_invoice) ?>" required>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Tanggal Invoice <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" name="invoice_date" id="invoice_date"
                                                    class="form-control" value="<?= $invoice->invoice_date ?>" required>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Jatuh Tempo <span
                                                        class="text-danger">*</span></label>
                                                <input type="date" name="due_date" id="due_date" class="form-control"
                                                    value="<?= $invoice->due_date ?>" required>
                                                <small class="text-muted">
                                                    Sebelumnya: <?= date('d/m/Y', strtotime($invoice->due_date)) ?>
                                                </small>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Periode Shipment</label>
                                                <select name="periode_shipment" class="form-control">
                                                    <option value="">-- Pilih Bulan (Opsional) --</option>
                                                    <?php foreach ($bulan_options as $bulan): ?>
                                                        <option value="<?= $bulan ?>" <?= ($invoice->periode_shipment ?? '') == $bulan ? 'selected' : '' ?>>
                                                            <?= $bulan ?>
                                                        </option>
                                                    <?php endforeach ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">Customer <span
                                                        class="text-danger">*</span></label>
                                                <select name="customer_id" id="customer_id" class="form-control"
                                                    required>
                                                    <option value="">-- Pilih Customer --</option>
                                                    <?php foreach ($customers as $cust): ?>
                                                        <option value="<?= $cust->kode ?>"
                                                            <?= $cust->kode == $invoice->customer_id ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($cust->nama) ?>
                                                        </option>
                                                    <?php endforeach ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">No. Faktur Pajak</label>
                                                <input type="text" name="no_faktur" class="form-control"
                                                    value="<?= htmlspecialchars($invoice->no_faktur ?? '') ?>">
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">No. PO (Purchase Order)</label>
                                                <input type="text" name="no_po" class="form-control"
                                                    value="<?= htmlspecialchars($invoice->no_po ?? '') ?>"
                                                    placeholder="Contoh: PO-2024-001" maxlength="100">
                                                <small class="form-text text-muted">Nomor Purchase Order dari
                                                    customer</small>
                                            </div>

                                            <!-- Akun Pendapatan - READ ONLY (terkunci) -->
                                            <div class="col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    Akun Pendapatan
                                                    <span class="badge badge-secondary ml-1">
                                                        <i class="fas fa-lock"></i> Terkunci
                                                    </span>
                                                </label>
                                                <?php
                                                $selected_acc = null;
                                                foreach ($revenue_accounts as $acc) {
                                                    if ($acc->id == $invoice->revenue_account_id) {
                                                        $selected_acc = $acc;
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <input type="text" class="form-control locked-field"
                                                    value="<?= $selected_acc ? '(' . $selected_acc->kode_perkiraan . ') ' . htmlspecialchars($selected_acc->nama) : '(20) Pendapatan' ?>"
                                                    readonly>
                                                <!-- Hidden input agar nilai tetap terkirim (walaupun tidak diproses di controller) -->
                                                <input type="hidden" name="revenue_account_id"
                                                    value="<?= $invoice->revenue_account_id ?>">
                                                <small class="text-muted">Akun pendapatan tidak dapat diubah untuk
                                                    invoice PAID</small>
                                            </div>
                                        </div>

                                        <!-- Customer Info Display -->
                                        <?php
                                        $ppn_display = str_replace('%', '', $invoice->ppn_percent);
                                        $pph_display = str_replace('%', '', $invoice->pph_percent);
                                        ?>
                                        <div id="customerInfo" class="alert alert-info">
                                            <strong>Info Customer:</strong><br>
                                            <span id="customerDisplay">
                                                <strong><?= htmlspecialchars($invoice->customer_nama) ?></strong><br>
                                                <?= htmlspecialchars($invoice->customer_alamat) ?><br>
                                                PIC: <?= htmlspecialchars($invoice->customer_pic) ?><br>
                                                PPN: <?= $ppn_display ?>% | PPH: <?= $pph_display ?>%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Items - READ ONLY (terkunci, nominal tidak bisa diubah) -->
                                <div class="card shadow mb-4 locked-section">
                                    <div class="card-header py-3 bg-gradient-secondary text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-lock mr-1"></i>
                                            <i class="fas fa-list"></i> Rincian Item
                                            <small class="ml-2" style="font-weight: normal; opacity: 0.85;">(nominal
                                                terkunci — tidak dapat diubah)</small>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered table-sm mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="5%" class="text-center">No</th>
                                                    <th>Deskripsi</th>
                                                    <th width="25%" class="text-right">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $item_no = 1;
                                                $has_items = false;
                                                foreach ($invoice->items as $item):
                                                    if ($item->item_type == 'item'):
                                                        $has_items = true;
                                                        ?>
                                                        <tr>
                                                            <td class="text-center"><?= $item_no++ ?></td>
                                                            <td><?= htmlspecialchars($item->deskripsi) ?></td>
                                                            <td class="text-right">
                                                                <strong>Rp
                                                                    <?= number_format($item->jumlah, 0, ',', '.') ?></strong>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    endif;
                                                endforeach;
                                                if (!$has_items):
                                                    ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Tidak ada item</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>

                                        <!-- Deductions (if any) -->
                                        <?php
                                        $has_deductions = false;
                                        foreach ($invoice->items as $item) {
                                            if ($item->item_type == 'deduction') {
                                                $has_deductions = true;
                                                break;
                                            }
                                        }
                                        ?>
                                        <?php if ($has_deductions): ?>
                                            <div class="mt-3">
                                                <strong class="text-warning"><i class="fas fa-minus-circle"></i>
                                                    Potongan:</strong>
                                                <table class="table table-bordered table-sm mt-2 mb-0">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="5%" class="text-center">No</th>
                                                            <th>Deskripsi Potongan</th>
                                                            <th width="25%" class="text-right">Jumlah</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $ded_no = 1;
                                                        foreach ($invoice->items as $item):
                                                            if ($item->item_type == 'deduction'):
                                                                ?>
                                                                <tr class="table-warning">
                                                                    <td class="text-center"><?= $ded_no++ ?></td>
                                                                    <td><?= htmlspecialchars($item->deskripsi) ?></td>
                                                                    <td class="text-right text-danger">
                                                                        <strong>- Rp
                                                                            <?= number_format(abs($item->jumlah), 0, ',', '.') ?></strong>
                                                                    </td>
                                                                </tr>
                                                                <?php
                                                            endif;
                                                        endforeach;
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>

                                        <div class="alert alert-warning mt-3 mb-0 py-2">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Info:</strong> Rincian item dan nominal tidak dapat diubah karena
                                            invoice sudah berstatus <strong>PAID</strong> dan jurnal akuntansi sudah
                                            terbentuk.
                                        </div>
                                    </div>
                                </div>

                                <!-- Keterangan -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-gray-700">
                                            <i class="fas fa-sticky-note"></i> Keterangan
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea name="keterangan" class="form-control"
                                            rows="3"><?= htmlspecialchars($invoice->keterangan ?? '') ?></textarea>
                                    </div>
                                </div>

                            </div>

                            <!-- Right Column - Summary (LOCKED) -->
                            <div class="col-lg-4">
                                <div class="card shadow mb-4" style="position: sticky; top: 20px;">
                                    <div class="card-header py-3 bg-gradient-success text-white">
                                        <h6 class="m-0 font-weight-bold">
                                            <i class="fas fa-lock mr-1"></i>
                                            <i class="fas fa-calculator"></i> Ringkasan (Terkunci)
                                        </h6>
                                    </div>
                                    <div class="card-body">

                                        <!-- Paid badge info -->
                                        <div class="text-center mb-3">
                                            <span class="badge badge-paid" style="font-size: 14px; padding: 8px 16px;">
                                                <i class="fas fa-check-circle mr-1"></i> PAID
                                            </span>
                                            <?php if (!empty($invoice->paid_date)): ?>
                                                <br><small class="text-muted mt-1 d-block">
                                                    Dibayar: <?= date('d/m/Y', strtotime($invoice->paid_date)) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>

                                        <div class="calculation-box">
                                            <div class="row">
                                                <div class="col-6 label">Subtotal:</div>
                                                <div class="col-6 text-right value">
                                                    Rp <?= number_format($invoice->subtotal, 0, ',', '.') ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-6 label">
                                                    PPN (<?= $ppn_display ?>%):
                                                </div>
                                                <div class="col-6 text-right value">
                                                    Rp <?= number_format($invoice->ppn_amount, 0, ',', '.') ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-6 label">
                                                    PPH (<?= $pph_display ?>%):
                                                </div>
                                                <div class="col-6 text-right value">
                                                    - Rp <?= number_format($invoice->pph_amount, 0, ',', '.') ?>
                                                </div>
                                            </div>

                                            <hr style="border-color: rgba(255,255,255,0.3);">

                                            <div class="grand-total">
                                                <div class="row">
                                                    <div class="col-5 label">GRAND TOTAL:</div>
                                                    <div class="col-7 text-right value">
                                                        Rp <?= number_format($invoice->grand_total, 0, ',', '.') ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <small><strong>Terbilang:</strong></small><br>
                                                <small style="font-style: italic;">
                                                    <?= htmlspecialchars($invoice->terbilang ?? '-') ?>
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Info jurnal -->
                                        <div class="alert alert-secondary mt-3 mb-3 py-2">
                                            <small>
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Jurnal akuntansi tidak akan berubah.</strong><br>
                                                Hanya data informasi yang diupdate.
                                            </small>
                                        </div>

                                        <div class="mt-2">
                                            <button type="submit" class="btn btn-warning btn-block"
                                                onclick="return confirmSave()">
                                                <i class="fas fa-save"></i> Simpan Perubahan
                                            </button>
                                            <a href="<?= base_url('invoice_tsc') ?>"
                                                class="btn btn-secondary btn-block">
                                                <i class="fas fa-times"></i> Batal
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Log info -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-2">
                                        <h6 class="m-0 font-weight-bold text-gray-600" style="font-size: 12px;">
                                            <i class="fas fa-history"></i> Info Invoice
                                        </h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <small class="text-muted">
                                            <strong>No. Invoice:</strong>
                                            <?= htmlspecialchars($invoice->no_invoice) ?><br>
                                            <strong>Dibuat:</strong>
                                            <?= !empty($invoice->created_at) ? date('d/m/Y H:i', strtotime($invoice->created_at)) : '-' ?><br>
                                            <strong>Dibuat oleh:</strong>
                                            <?= htmlspecialchars($invoice->created_by ?? '-') ?><br>
                                            <?php if (!empty($invoice->paid_date)): ?>
                                                <strong>Tanggal Paid:</strong>
                                                <?= date('d/m/Y', strtotime($invoice->paid_date)) ?><br>
                                            <?php endif; ?>
                                        </small>
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
        // Konfirmasi sebelum submit
        function confirmSave() {
            return confirm(
                '⚠️ KONFIRMASI EDIT INVOICE PAID ⚠️\n\n' +
                'Anda akan mengubah data informasi invoice PAID.\n' +
                'Nominal dan jurnal akuntansi TIDAK akan berubah.\n\n' +
                'Yakin ingin melanjutkan?'
            );
        }

        // Update customer info display saat customer diganti
        $('#customer_id').change(function () {
            const customerId = $(this).val();
            if (!customerId) {
                return;
            }

            $.post('<?= base_url('invoice_tsc/ajax_get_customer') ?>', {
                customer_id: customerId
            }, function (response) {
                const data = JSON.parse(response);
                if (data.success) {
                    const ppnClean = String(data.data.ppn || 0).replace('%', '');
                    const pphClean = String(data.data.pph || 0).replace('%', '');

                    let info = `<strong>${data.data.nama}</strong><br>`;
                    info += `${data.data.alamat || ''}<br>`;
                    info += `PIC: ${data.data.pic || '-'}<br>`;
                    info += `PPN: ${ppnClean}% | PPH: ${pphClean}%`;

                    $('#customerDisplay').html(info);
                    $('#customerInfo').fadeIn();
                }
            });
        });

        // Due date auto-fill saat tanggal invoice diubah
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
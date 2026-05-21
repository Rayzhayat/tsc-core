<!-- detail.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .invoice-detail-card {
            border-left: 4px solid #1cc88a;
        }

        .info-row {
            padding: 8px 0;
            border-bottom: 1px solid #e3e6f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #5a5c69;
        }

        .info-value {
            color: #3a3b45;
        }

        .badge-status {
            font-size: 14px;
            padding: 8px 16px;
        }

        .items-table th {
            background: #f8f9fc;
            font-weight: 600;
        }

        .calculation-row {
            background: #f8f9fc;
        }

        .grand-total-row {
            background: #1cc88a;
            color: white;
            font-weight: bold;
        }

        .timeline-item {
            padding: 15px 0;
            border-left: 2px solid #e3e6f0;
            padding-left: 30px;
            position: relative;
        }

        .timeline-item:before {
            content: '';
            width: 12px;
            height: 12px;
            background: #1cc88a;
            border-radius: 50%;
            position: absolute;
            left: -7px;
            top: 20px;
        }

        .btn-action-group .btn {
            margin: 2px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4 no-print">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-file-invoice text-success"></i> Detail Invoice TSC
                        </h1>
                        <div class="btn-action-group">
                            <a href="<?= base_url('invoice_tsc') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <a href="<?= base_url('invoice_tsc/export_pdf/' . $invoice->id) ?>"
                                class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </a>
                            <?php if ($invoice->status != 'paid'): ?>
                                <a href="<?= base_url('invoice_tsc/ubah/' . $invoice->id) ?>"
                                    class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            <?php endif ?>
                            <button onclick="window.print()" class="btn btn-info btn-sm">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>

                    <!-- Flashdata -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show no-print">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <div class="row">
                        <!-- Left Column - Invoice Info -->
                        <div class="col-lg-8">

                            <!-- Invoice Header -->
                            <div class="card shadow mb-4 invoice-detail-card">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-file-invoice"></i> Informasi Invoice
                                    </h6>
                                    <?php
                                    $badge_class = 'badge-secondary';
                                    if ($invoice->status == 'draft')
                                        $badge_class = 'badge-secondary';
                                    if ($invoice->status == 'sent')
                                        $badge_class = 'badge-primary';
                                    if ($invoice->status == 'unsent')
                                        $badge_class = 'badge-warning'; // ✅ TAMBAH INI - Orange/Yellow
                                    if ($invoice->status == 'paid')
                                        $badge_class = 'badge-success';
                                    if ($invoice->status == 'cancelled')
                                        $badge_class = 'badge-danger';
                                    ?>
                                    <span class="badge badge-status <?= $badge_class ?>">
                                        <?= strtoupper($invoice->status) ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">No. Invoice</div>
                                                <div class="info-value h5 text-primary">
                                                    <?= htmlspecialchars($invoice->no_invoice) ?>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Tanggal Invoice</div>
                                                <div class="info-value">
                                                    <?= date('d F Y', strtotime($invoice->invoice_date)) ?>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Jatuh Tempo</div>
                                                <div class="info-value">
                                                    <?= date('d F Y', strtotime($invoice->due_date)) ?>
                                                    <?php
                                                    $days_diff = (strtotime($invoice->due_date) - strtotime(date('Y-m-d'))) / 86400;
                                                    if ($invoice->status != 'paid' && $days_diff < 0):
                                                        ?>
                                                        <span class="badge badge-danger ml-2">
                                                            <i class="fas fa-exclamation-triangle"></i> Overdue
                                                        </span>
                                                    <?php elseif ($invoice->status != 'paid' && $days_diff <= 3): ?>
                                                        <span class="badge badge-warning ml-2">
                                                            <i class="fas fa-clock"></i> <?= ceil($days_diff) ?> hari lagi
                                                        </span>
                                                    <?php endif ?>
                                                </div>
                                            </div>

                                            <!-- ✅ NEW: Simple Periode Shipment Display -->
                                            <?php if (!empty($invoice->periode_shipment)): ?>
                                                <div class="info-row">
                                                    <div class="info-label">Periode Shipment</div>
                                                    <div class="info-value">
                                                        <span class="badge badge-info"
                                                            style="font-size: 14px; padding: 8px 15px;">
                                                            <i class="fas fa-calendar-alt"></i>
                                                            <?= htmlspecialchars($invoice->periode_shipment) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif ?>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">No. Faktur Pajak</div>
                                                <div class="info-value">
                                                    <?= htmlspecialchars($invoice->no_faktur ?: '-') ?>
                                                </div>
                                            </div>
                                            <!-- ✅ TAMBAH: Display No. PO (DI BAWAH NO. FAKTUR) -->
                                            <?php if (!empty($invoice->no_po)): ?>
                                                <div class="info-row">
                                                    <div class="info-label">No. PO (Purchase Order)</div>
                                                    <div class="info-value">
                                                        <span class="badge badge-primary"
                                                            style="font-size: 13px; padding: 6px 12px;">
                                                            <i class="fas fa-file-contract"></i>
                                                            <?= htmlspecialchars($invoice->no_po) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="info-row">
                                                <div class="info-label">Dibuat Oleh</div>
                                                <div class="info-value">
                                                    <?= htmlspecialchars($invoice->created_by) ?>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Tanggal Dibuat</div>
                                                <div class="info-value">
                                                    <?= date('d F Y H:i', strtotime($invoice->created_at)) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-building"></i> Informasi Customer
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-row">
                                        <div class="info-label">Nama Customer</div>
                                        <div class="info-value h6 text-dark">
                                            <?= htmlspecialchars($invoice->customer_nama) ?>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Nama NPWP</div>
                                        <div class="info-value">
                                            <?= htmlspecialchars($invoice->customer_nama_npwp) ?>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Alamat</div>
                                        <div class="info-value">
                                            <?= nl2br(htmlspecialchars($invoice->customer_alamat ?: '-')) ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">NPWP</div>
                                                <div class="info-value">
                                                    <?= htmlspecialchars($invoice->customer_npwp ?: '-') ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">PIC</div>
                                                <div class="info-value">
                                                    <?= htmlspecialchars($invoice->customer_pic) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Items -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-list"></i> Detail Item & Perhitungan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered items-table">
                                            <thead>
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>Deskripsi</th>
                                                    <th width="20%" class="text-right">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $no = 1;
                                                $subtotal_items = 0;
                                                foreach ($invoice->items as $item):
                                                    if ($item->item_type == 'item') {
                                                        $subtotal_items += $item->jumlah;
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td class="text-center"><?= $no++ ?></td>
                                                        <td>
                                                            <?php if ($item->item_type == 'deduction'): ?>
                                                                <i class="fas fa-minus-circle text-danger"></i>
                                                                <strong>POTONGAN:</strong>
                                                            <?php endif ?>
                                                            <?= htmlspecialchars($item->deskripsi) ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php if ($item->item_type == 'deduction'): ?>
                                                                <span class="text-danger">
                                                                    -Rp <?= number_format(abs($item->jumlah), 0, ',', '.') ?>
                                                                </span>
                                                            <?php else: ?>
                                                                Rp <?= number_format($item->jumlah, 0, ',', '.') ?>
                                                            <?php endif ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach ?>

                                                <!-- Subtotal -->
                                                <tr class="calculation-row">
                                                    <td colspan="2" class="text-right"><strong>Subtotal</strong></td>
                                                    <td class="text-right">
                                                        <strong>Rp
                                                            <?= number_format($invoice->subtotal, 0, ',', '.') ?></strong>
                                                    </td>
                                                </tr>

                                                <!-- PPN -->
                                                <?php
                                                // Clean % symbol
                                                $ppn_percent_clean = str_replace('%', '', $invoice->ppn_percent);
                                                $pph_percent_clean = str_replace('%', '', $invoice->pph_percent);
                                                ?>
                                                <?php if ($invoice->ppn_amount > 0): ?>
                                                    <tr class="calculation-row">
                                                        <td colspan="2" class="text-right">
                                                            <strong>PPN (<?= $ppn_percent_clean ?>%)</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                Dihitung dari: Rp
                                                                <?= number_format($subtotal_items, 0, ',', '.') ?>
                                                            </small>
                                                        </td>
                                                        <td class="text-right">
                                                            <strong class="text-success">
                                                                +Rp <?= number_format($invoice->ppn_amount, 0, ',', '.') ?>
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                <?php endif ?>

                                                <!-- PPH -->
                                                <?php if ($invoice->pph_amount > 0): ?>
                                                    <tr class="calculation-row">
                                                        <td colspan="2" class="text-right">
                                                            <strong>PPH 23 (<?= $pph_percent_clean ?>%)</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                Dihitung dari: Rp
                                                                <?= number_format($subtotal_items, 0, ',', '.') ?>
                                                            </small>
                                                        </td>
                                                        <td class="text-right">
                                                            <strong class="text-danger">
                                                                -Rp <?= number_format($invoice->pph_amount, 0, ',', '.') ?>
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                <?php endif ?>

                                                <!-- Grand Total -->
                                                <tr class="grand-total-row">
                                                    <td colspan="2" class="text-right">
                                                        <h5 class="mb-0 text-white">GRAND TOTAL</h5>
                                                    </td>
                                                    <td class="text-right">
                                                        <h5 class="mb-0 text-white">
                                                            Rp <?= number_format($invoice->grand_total, 0, ',', '.') ?>
                                                        </h5>
                                                    </td>
                                                </tr>

                                                <!-- Terbilang -->
                                                <tr>
                                                    <td colspan="3" class="text-center bg-light">
                                                        <em class="text-muted">
                                                            Terbilang: <strong><?= $invoice->terbilang ?></strong>
                                                        </em>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <?php if (!empty($invoice->keterangan)): ?>
                                        <div class="mt-3 p-3 bg-light rounded">
                                            <strong>Keterangan:</strong><br>
                                            <?= nl2br(htmlspecialchars($invoice->keterangan)) ?>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>

                        </div>

                        <!-- Right Column - Summary & Actions -->
                        <div class="col-lg-4">

                            <!-- Quick Summary -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-gradient-success">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-calculator"></i> Ringkasan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-row">
                                        <div class="info-label">Subtotal</div>
                                        <div class="info-value h6">
                                            Rp <?= number_format($invoice->subtotal, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">PPN (<?= $ppn_percent_clean ?>%)</div>
                                        <div class="info-value text-success">
                                            +Rp <?= number_format($invoice->ppn_amount, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">PPH (<?= $pph_percent_clean ?>%)</div>
                                        <div class="info-value text-danger">
                                            -Rp <?= number_format($invoice->pph_amount, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="info-row">
                                        <div class="info-label h5 text-success">Grand Total</div>
                                        <div class="info-value h4 text-success">
                                            Rp <?= number_format($invoice->grand_total, 0, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <?php if ($invoice->status != 'paid'): ?>
                                <div class="card shadow mb-4 no-print">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-success">
                                            <i class="fas fa-cogs"></i> Aksi
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <a href="<?= base_url('invoice_tsc/ubah/' . $invoice->id) ?>"
                                            class="btn btn-warning btn-block">
                                            <i class="fas fa-edit"></i> Edit Invoice
                                        </a>

                                        <!-- ✅ TOMBOL SENT - Muncul kalau status = draft atau unsent -->
                                        <?php if ($invoice->status == 'draft' || $invoice->status == 'unsent'): ?>
                                            <button type="button" class="btn btn-primary btn-block"
                                                onclick="updateStatus(<?= $invoice->id ?>, 'sent')">
                                                <i class="fas fa-paper-plane"></i> Mark as Sent
                                            </button>
                                        <?php endif ?>

                                        <!-- ✅ TOMBOL UNSENT - Muncul kalau status = sent -->
                                        <?php if ($invoice->status == 'sent'): ?>
                                            <button type="button" class="btn btn-warning btn-block"
                                                onclick="updateStatus(<?= $invoice->id ?>, 'unsent')">
                                                <i class="fas fa-undo"></i> Mark as Unsent
                                            </button>
                                        <?php endif ?>

                                        <!-- ✅ TOMBOL PAID - Muncul kalau status = sent -->
                                        <?php if ($invoice->status == 'sent'): ?>
                                            <button type="button" class="btn btn-success btn-block"
                                                onclick="updateStatus(<?= $invoice->id ?>, 'paid')">
                                                <i class="fas fa-check-circle"></i> Mark as Paid
                                            </button>
                                        <?php endif ?>

                                        <button type="button" class="btn btn-danger btn-block"
                                            onclick="confirmDelete(<?= $invoice->id ?>)">
                                            <i class="fas fa-trash"></i> Hapus Invoice
                                        </button>
                                    </div>
                                </div>
                            <?php endif ?>

                            <!-- Piutang Info (if exists) -->
                            <?php if ($piutang ?? null): ?>
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 bg-gradient-info">
                                        <h6 class="m-0 font-weight-bold text-white">
                                            <i class="fas fa-money-bill-wave"></i> Status Piutang
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="info-row">
                                            <div class="info-label">Nominal</div>
                                            <div class="info-value">
                                                Rp <?= number_format($piutang->nominal, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Outstanding</div>
                                            <div
                                                class="info-value h6 <?= $piutang->outstanding > 0 ? 'text-warning' : 'text-success' ?>">
                                                Rp <?= number_format($piutang->outstanding, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Terbayar</div>
                                            <div class="info-value text-success">
                                                Rp <?= number_format($piutang->paid_amount, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Status</div>
                                            <div class="info-value">
                                                <?php
                                                $piutang_badge = 'badge-warning';
                                                if ($piutang->status == 'paid')
                                                    $piutang_badge = 'badge-success';
                                                if ($piutang->status == 'overdue')
                                                    $piutang_badge = 'badge-danger';
                                                ?>
                                                <span class="badge <?= $piutang_badge ?>">
                                                    <?= strtoupper($piutang->status) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if (($piutang->aging_days ?? 0) > 0): ?>
                                            <div class="info-row">
                                                <div class="info-label">Aging</div>
                                                <div class="info-value text-danger">
                                                    <?= $piutang->aging_days ?> hari
                                                </div>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                </div>
                            <?php endif ?>

                            <!-- Timeline -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-history"></i> Timeline
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="timeline-item">
                                        <strong>Invoice Dibuat</strong><br>
                                        <small class="text-muted">
                                            <?= date('d F Y H:i', strtotime($invoice->created_at)) ?><br>
                                            oleh <?= htmlspecialchars($invoice->created_by) ?>
                                        </small>
                                    </div>

                                    <?php if ($invoice->updated_at ?? null): ?>
                                        <div class="timeline-item">
                                            <strong>Terakhir Diupdate</strong><br>
                                            <small class="text-muted">
                                                <?= date('d F Y H:i', strtotime($invoice->updated_at)) ?>
                                            </small>
                                        </div>
                                    <?php endif ?>

                                    <?php if ($invoice->status == 'paid' && ($invoice->paid_date ?? null)): ?>
                                        <div class="timeline-item">
                                            <strong class="text-success">Invoice Lunas</strong><br>
                                            <small class="text-muted">
                                                <?= date('d F Y', strtotime($invoice->paid_date)) ?>
                                            </small>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        function updateStatus(id, status) {
            let message = '';

            if (status == 'sent') {
                message = 'Tandai invoice sebagai SENT (Terkirim)?';
            } else if (status == 'unsent') {
                message = 'Tarik kembali invoice (UNSENT)?';
            } else if (status == 'paid') {
                message = 'Tandai invoice sebagai PAID?\n\nIni akan:\n- Membuat jurnal pembayaran\n- Mengurangi piutang usaha\n- Menambah saldo bank';
            }

            if (confirm(message)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('invoice_tsc/update_status/') ?>' + id;

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'status';
                input.value = status;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function confirmDelete(id) {
            if (confirm('Yakin ingin menghapus invoice ini?\n\nData akan dihapus permanent!')) {
                window.location.href = '<?= base_url('invoice_tsc/hapus/') ?>' + id;
            }
        }
    </script>
</body>

</html>
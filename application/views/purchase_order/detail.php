<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .detail-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .status-badge {
            font-size: 1.1rem;
            padding: 8px 20px;
        }

        .info-card {
            border-left: 4px solid #4e73df;
            background: #f8f9fc;
            padding: 15px;
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #5a5c69;
            margin-bottom: 5px;
        }

        .info-value {
            color: #2e59d9;
            font-size: 1.1rem;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e3e6f0;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #4e73df;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #4e73df;
        }

        .action-buttons .btn {
            margin: 5px;
        }

        .nav-tabs .nav-link {
            font-weight: bold;
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white !important;
        }

        .table-items th {
            background: #4e73df;
            color: white;
        }

        .calculation-summary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
        }

        .calculation-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .calculation-row:last-child {
            border: none;
            font-size: 1.3rem;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid rgba(255, 255, 255, 0.5);
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
                            <i class="fas fa-file-invoice text-primary"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('purchase_order') ?>" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <a href="<?= base_url('purchase_order/print_po/' . $po->id) ?>"
                                class="btn btn-danger btn-sm shadow-sm" target="_blank">
                                <i class="fas fa-file-pdf"></i> Print PDF
                            </a>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Header Card -->
                    <div class="detail-header shadow">
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
                                    <strong>Tanggal:</strong> <?= date('d F Y', strtotime($po->tanggal_po)) ?>
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-user"></i>
                                    <strong>Dibuat oleh:</strong> <?= htmlspecialchars($po->request_by) ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <h4 class="mb-3">Status:</h4>
                                <?php
                                $badge_color = [
                                    'draft' => 'secondary',
                                    'pending' => 'info',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'partial_received' => 'warning',
                                    'received' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'dark'
                                ];
                                $color = $badge_color[$po->status] ?? 'secondary';
                                ?>
                                <span class="badge badge-<?= $color ?> status-badge">
                                    <?= strtoupper(str_replace('_', ' ', $po->status)) ?>
                                </span>

                                <h4 class="mt-3">Total:</h4>
                                <h2 class="mb-0">Rp <?= number_format($po->total_po, 0, ',', '.') ?></h2>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="action-buttons text-center">

                                <!-- Edit Button (Draft/Rejected Only) -->
                                <?php if (in_array($po->status, ['draft', 'rejected'])): ?>
                                    <a href="<?= base_url('purchase_order/ubah/' . $po->id) ?>"
                                        class="btn btn-warning btn-lg">
                                        <i class="fas fa-edit"></i> Edit PO
                                    </a>
                                <?php endif; ?>

                                <!-- Submit Button (Draft Only) -->
                                <?php if ($po->status == 'draft'): ?>
                                    <a href="<?= base_url('purchase_order/submit/' . $po->id) ?>"
                                        class="btn btn-primary btn-lg"
                                        onclick="return confirm('Submit PO untuk approval?')">
                                        <i class="fas fa-paper-plane"></i> Submit untuk Approval
                                    </a>
                                <?php endif; ?>

                                <!-- Approve/Reject Buttons (Pending Only - Superadmin) -->
                                <?php if ($po->status == 'pending' && $user_level == 'superadmin'): ?>
                                    <a href="<?= base_url('purchase_order/approve/' . $po->id) ?>"
                                        class="btn btn-success btn-lg"
                                        onclick="return confirm('Approve PO ini?')">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </a>
                                    <button type="button" class="btn btn-danger btn-lg"
                                        data-toggle="modal" data-target="#rejectModal">
                                        <i class="fas fa-times-circle"></i> Reject
                                    </button>
                                <?php endif; ?>

                                <!-- Receive Button (Approved/Partial Received) -->
                                <?php if (in_array($po->status, ['approved', 'partial_received'])): ?>
                                    <a href="<?= base_url('purchase_order/receive/' . $po->id) ?>"
                                        class="btn btn-info btn-lg">
                                        <i class="fas fa-box"></i> Terima Barang
                                    </a>
                                <?php endif; ?>

                                <!-- Payment Button (Received/Partial Received) -->
                                <?php if (in_array($po->status, ['received', 'partial_received'])): ?>
                                    <a href="<?= base_url('purchase_order/payment/' . $po->id) ?>"
                                        class="btn btn-primary btn-lg">
                                        <i class="fas fa-money-bill-wave"></i> Bayar
                                    </a>
                                <?php endif; ?>

                                <!-- Cancel Button (Draft/Pending/Approved) -->
                                <?php if (in_array($po->status, ['draft', 'pending', 'approved'])): ?>
                                    <button type="button" class="btn btn-dark btn-lg"
                                        data-toggle="modal" data-target="#cancelModal">
                                        <i class="fas fa-ban"></i> Cancel PO
                                    </button>
                                <?php endif; ?>

                                <!-- Delete Button (Draft/Rejected - Superadmin Only) -->
                                <?php if (in_array($po->status, ['draft', 'rejected']) && $user_level == 'superadmin'): ?>
                                    <a href="<?= base_url('purchase_order/hapus/' . $po->id) ?>"
                                        class="btn btn-danger btn-lg"
                                        onclick="return confirm('HAPUS PO ini secara permanen?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <!-- Left Column -->
                        <div class="col-lg-8">

                            <!-- Tabs -->
                            <div class="card shadow mb-4">
                                <div class="card-body">
                                    <ul class="nav nav-tabs" id="poTab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">
                                                <i class="fas fa-info-circle"></i> Informasi PO
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="items-tab" data-toggle="tab" href="#items" role="tab">
                                                <i class="fas fa-boxes"></i> Items (<?= count($details) ?>)
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="receiving-tab" data-toggle="tab" href="#receiving" role="tab">
                                                <i class="fas fa-box"></i> Penerimaan (<?= count($receiving_history) ?>)
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="payment-tab" data-toggle="tab" href="#payment" role="tab">
                                                <i class="fas fa-money-bill-wave"></i> Pembayaran (<?= count($payment_history) ?>)
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="timeline-tab" data-toggle="tab" href="#timeline" role="tab">
                                                <i class="fas fa-history"></i> Timeline
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content mt-3" id="poTabContent">

                                        <!-- Tab: Informasi PO -->
                                        <div class="tab-pane fade show active" id="info" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="info-card">
                                                        <div class="info-label">No. PO</div>
                                                        <div class="info-value"><?= $po->no_po ?></div>
                                                    </div>

                                                    <div class="info-card">
                                                        <div class="info-label">Tanggal PO</div>
                                                        <div class="info-value"><?= date('d F Y', strtotime($po->tanggal_po)) ?></div>
                                                    </div>

                                                    <div class="info-card">
                                                        <div class="info-label">Kategori</div>
                                                        <div class="info-value"><?= ucfirst($po->kategori) ?></div>
                                                    </div>

                                                    <div class="info-card">
                                                        <div class="info-label">Jenis Pembelian</div>
                                                        <div class="info-value"><?= ucfirst(str_replace('_', ' ', $po->jenis_pembelian)) ?></div>
                                                    </div>

                                                    <div class="info-card">
                                                        <div class="info-label">Expected Delivery</div>
                                                        <div class="info-value">
                                                            <?= $po->expected_delivery ? date('d F Y', strtotime($po->expected_delivery)) : '-' ?>
                                                        </div>
                                                    </div>

                                                    <div class="info-card">
                                                        <div class="info-label">Payment Terms</div>
                                                        <div class="info-value"><?= $po->payment_terms ?: '-' ?></div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="info-card">
                                                        <div class="info-label">Vendor</div>
                                                        <div class="info-value"><?= htmlspecialchars($po->vendor_nama) ?></div>
                                                        <small class="text-muted">Kode: <?= $po->vendor_kode ?></small>
                                                    </div>

                                                    <div class="info-card">
                                                        <div class="info-label">Alamat Vendor</div>
                                                        <div class="info-value"><?= nl2br(htmlspecialchars($po->vendor_alamat)) ?: '-' ?></div>
                                                    </div>

                                                    <div class="info-card">
                                                        <div class="info-label">NPWP</div>
                                                        <div class="info-value"><?= $po->vendor_npwp ?: '-' ?></div>
                                                    </div>

                                                    <div class="info-card">
                                                        <div class="info-label">PIC</div>
                                                        <div class="info-value"><?= $po->vendor_pic ?: '-' ?></div>
                                                    </div>

                                                    <div class="info-card">
                                                        <div class="info-label">No. Telp</div>
                                                        <div class="info-value"><?= $po->vendor_telp ?: '-' ?></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if (!empty($po->delivery_address)): ?>
                                                <div class="info-card">
                                                    <div class="info-label">Alamat Pengiriman</div>
                                                    <div class="info-value"><?= nl2br(htmlspecialchars($po->delivery_address)) ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($po->keterangan)): ?>
                                                <div class="info-card">
                                                    <div class="info-label">Keterangan</div>
                                                    <div class="info-value"><?= nl2br(htmlspecialchars($po->keterangan)) ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($po->status == 'rejected' && !empty($po->rejected_reason)): ?>
                                                <div class="alert alert-danger">
                                                    <strong><i class="fas fa-exclamation-triangle"></i> Alasan Penolakan:</strong><br>
                                                    <?= nl2br(htmlspecialchars($po->rejected_reason)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Tab: Items -->
                                        <div class="tab-pane fade" id="items" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-items">
                                                    <thead>
                                                        <tr>
                                                            <th width="5%">No</th>
                                                            <th>Nama Item</th>
                                                            <th width="10%">Kode</th>
                                                            <th width="10%">Satuan</th>
                                                            <th width="10%" class="text-right">Qty Order</th>
                                                            <th width="10%" class="text-right">Qty Received</th>
                                                            <th width="12%" class="text-right">Harga Satuan</th>
                                                            <th width="8%">Diskon</th>
                                                            <th width="15%" class="text-right">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $no = 1;
                                                        foreach ($details as $item):
                                                        ?>
                                                            <tr>
                                                                <td><?= $no++ ?></td>
                                                                <td>
                                                                    <strong><?= htmlspecialchars($item->item_nama) ?></strong>
                                                                    <?php if (!empty($item->item_spesifikasi)): ?>
                                                                        <br><small class="text-muted"><?= htmlspecialchars($item->item_spesifikasi) ?></small>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($item->keterangan)): ?>
                                                                        <br><small class="text-info"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($item->keterangan) ?></small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= htmlspecialchars($item->item_kode) ?: '-' ?></td>
                                                                <td><?= htmlspecialchars($item->item_satuan) ?></td>
                                                                <td class="text-right"><?= number_format($item->qty_order, 2) ?></td>
                                                                <td class="text-right">
                                                                    <?= number_format($item->qty_received, 2) ?>
                                                                    <?php if ($item->qty_received < $item->qty_order): ?>
                                                                        <br><small class="text-warning">
                                                                            (Sisa: <?= number_format($item->qty_order - $item->qty_received, 2) ?>)
                                                                        </small>
                                                                    <?php else: ?>
                                                                        <br><small class="text-success">
                                                                            <i class="fas fa-check"></i> Lengkap
                                                                        </small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-right">Rp <?= number_format($item->harga_satuan, 0, ',', '.') ?></td>
                                                                <td class="text-center">
                                                                    <?php if ($item->diskon_persen > 0): ?>
                                                                        <?= number_format($item->diskon_persen, 2) ?>%
                                                                        <br><small class="text-muted">
                                                                            (Rp <?= number_format($item->diskon_nominal, 0, ',', '.') ?>)
                                                                        </small>
                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-right">
                                                                    <strong>Rp <?= number_format($item->subtotal, 0, ',', '.') ?></strong>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- Tab: Penerimaan -->
                                        <div class="tab-pane fade" id="receiving" role="tabpanel">
                                            <?php if (empty($receiving_history)): ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> Belum ada penerimaan barang untuk PO ini.
                                                </div>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead class="table-items">
                                                            <tr>
                                                                <th width="5%">No</th>
                                                                <th>No. Receiving</th>
                                                                <th>Tanggal Terima</th>
                                                                <th>Item</th>
                                                                <th class="text-right">Qty Diterima</th>
                                                                <th class="text-right">Qty Ditolak</th>
                                                                <th>Kondisi</th>
                                                                <th>Diterima Oleh</th>
                                                                <th>Bukti</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $no = 1;
                                                            foreach ($receiving_history as $receive):
                                                                // Get item detail
                                                                $item_detail = null;
                                                                foreach ($details as $d) {
                                                                    if ($d->id == $receive->po_detail_id) {
                                                                        $item_detail = $d;
                                                                        break;
                                                                    }
                                                                }
                                                            ?>
                                                                <tr>
                                                                    <td><?= $no++ ?></td>
                                                                    <td><strong><?= $receive->no_receiving ?></strong></td>
                                                                    <td><?= date('d/m/Y', strtotime($receive->tanggal_terima)) ?></td>
                                                                    <td>
                                                                        <?= $item_detail ? htmlspecialchars($item_detail->item_nama) : '-' ?>
                                                                        <?php if (!empty($receive->keterangan)): ?>
                                                                            <br><small class="text-muted"><?= htmlspecialchars($receive->keterangan) ?></small>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td class="text-right"><?= number_format($receive->qty_received, 2) ?></td>
                                                                    <td class="text-right">
                                                                        <?php if ($receive->qty_rejected > 0): ?>
                                                                            <span class="text-danger"><?= number_format($receive->qty_rejected, 2) ?></span>
                                                                        <?php else: ?>
                                                                            -
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                        $kondisi_badge = [
                                                                            'baik' => 'success',
                                                                            'rusak' => 'danger',
                                                                            'kurang' => 'warning'
                                                                        ];
                                                                        $badge = $kondisi_badge[$receive->kondisi] ?? 'secondary';
                                                                        ?>
                                                                        <span class="badge badge-<?= $badge ?>">
                                                                            <?= ucfirst($receive->kondisi) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?= htmlspecialchars($receive->received_by) ?></td>
                                                                    <td class="text-center">
                                                                        <?php if (!empty($receive->foto_bukti)): ?>
                                                                            <a href="<?= base_url('uploads/po_receiving/' . $receive->foto_bukti) ?>"
                                                                                target="_blank" class="btn btn-sm btn-info">
                                                                                <i class="fas fa-eye"></i> Lihat
                                                                            </a>
                                                                        <?php else: ?>
                                                                            -
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Tab: Pembayaran -->
                                        <div class="tab-pane fade" id="payment" role="tabpanel">
                                            <?php if (empty($payment_history)): ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> Belum ada pembayaran untuk PO ini.
                                                </div>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead class="table-items">
                                                            <tr>
                                                                <th width="5%">No</th>
                                                                <th>No. Payment</th>
                                                                <th>Tanggal Bayar</th>
                                                                <th class="text-right">Jumlah</th>
                                                                <th>Metode</th>
                                                                <th>Bank / No. Rek</th>
                                                                <th>No. Referensi</th>
                                                                <th>Bukti</th>
                                                                <th>Dibuat Oleh</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $no = 1;
                                                            $total_dibayar = 0;
                                                            foreach ($payment_history as $payment):
                                                                $total_dibayar += $payment->jumlah_bayar;
                                                            ?>
                                                                <tr>
                                                                    <td><?= $no++ ?></td>
                                                                    <td><strong><?= $payment->no_payment ?></strong></td>
                                                                    <td><?= date('d/m/Y', strtotime($payment->tanggal_bayar)) ?></td>
                                                                    <td class="text-right">
                                                                        <strong>Rp <?= number_format($payment->jumlah_bayar, 0, ',', '.') ?></strong>
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                        $metode_badge = [
                                                                            'cash' => 'success',
                                                                            'transfer' => 'primary',
                                                                            'giro' => 'warning',
                                                                            'cek' => 'info'
                                                                        ];
                                                                        $badge = $metode_badge[$payment->metode_bayar] ?? 'secondary';
                                                                        ?>
                                                                        <span class="badge badge-<?= $badge ?>">
                                                                            <?= ucfirst($payment->metode_bayar) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <?php if (!empty($payment->bank_nama)): ?>
                                                                            <?= htmlspecialchars($payment->bank_nama) ?>
                                                                            <?php if (!empty($payment->no_rekening)): ?>
                                                                                <br><small class="text-muted"><?= htmlspecialchars($payment->no_rekening) ?></small>
                                                                            <?php endif; ?>
                                                                        <?php else: ?>
                                                                            -
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td><?= htmlspecialchars($payment->no_referensi) ?: '-' ?></td>
                                                                    <td class="text-center">
                                                                        <?php if (!empty($payment->bukti_transfer)): ?>
                                                                            <a href="<?= base_url('uploads/po_payment/' . $payment->bukti_transfer) ?>"
                                                                                target="_blank" class="btn btn-sm btn-info">
                                                                                <i class="fas fa-eye"></i> Lihat
                                                                            </a>
                                                                        <?php else: ?>
                                                                            -
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td><?= htmlspecialchars($payment->created_by) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <tr class="table-info font-weight-bold">
                                                                <td colspan="3" class="text-right">TOTAL DIBAYAR:</td>
                                                                <td class="text-right">Rp <?= number_format($total_dibayar, 0, ',', '.') ?></td>
                                                                <td colspan="5"></td>
                                                            </tr>
                                                            <tr class="table-warning font-weight-bold">
                                                                <td colspan="3" class="text-right">SISA PEMBAYARAN:</td>
                                                                <td class="text-right">Rp <?= number_format($po->total_po - $total_dibayar, 0, ',', '.') ?></td>
                                                                <td colspan="5"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Tab: Timeline -->
                                        <div class="tab-pane fade" id="timeline" role="tabpanel">
                                            <div class="timeline">

                                                <!-- Created -->
                                                <div class="timeline-item">
                                                    <strong><?= date('d F Y H:i', strtotime($po->created_at)) ?></strong>
                                                    <p class="mb-0">
                                                        <span class="badge badge-secondary">CREATED</span><br>
                                                        PO dibuat oleh <strong><?= htmlspecialchars($po->created_by) ?></strong>
                                                    </p>
                                                </div>

                                                <!-- Submitted -->
                                                <?php if ($po->status != 'draft'): ?>
                                                    <div class="timeline-item">
                                                        <strong><?= date('d F Y H:i', strtotime($po->created_at)) ?></strong>
                                                        <p class="mb-0">
                                                            <span class="badge badge-info">SUBMITTED</span><br>
                                                            PO disubmit untuk approval oleh <strong><?= htmlspecialchars($po->request_by) ?></strong>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Approved -->
                                                <?php if (!empty($po->approved_by)): ?>
                                                    <div class="timeline-item">
                                                        <strong><?= date('d F Y H:i', strtotime($po->approved_at)) ?></strong>
                                                        <p class="mb-0">
                                                            <span class="badge badge-success">APPROVED</span><br>
                                                            PO disetujui oleh <strong><?= htmlspecialchars($po->approved_by) ?></strong>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Rejected -->
                                                <?php if ($po->status == 'rejected'): ?>
                                                    <div class="timeline-item">
                                                        <strong><?= date('d F Y H:i', strtotime($po->updated_at)) ?></strong>
                                                        <p class="mb-0">
                                                            <span class="badge badge-danger">REJECTED</span><br>
                                                            PO ditolak oleh <strong><?= htmlspecialchars($po->approved_by) ?></strong>
                                                            <?php if (!empty($po->rejected_reason)): ?>
                                                                <br><small class="text-muted">Alasan: <?= htmlspecialchars($po->rejected_reason) ?></small>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Receiving History -->
                                                <?php foreach ($receiving_history as $receive): ?>
                                                    <div class="timeline-item">
                                                        <strong><?= date('d F Y H:i', strtotime($receive->created_at)) ?></strong>
                                                        <p class="mb-0">
                                                            <span class="badge badge-primary">RECEIVED</span><br>
                                                            Barang diterima (<?= $receive->no_receiving ?>) oleh <strong><?= htmlspecialchars($receive->received_by) ?></strong>
                                                            <br><small class="text-muted">Qty: <?= number_format($receive->qty_received, 2) ?>, Kondisi: <?= ucfirst($receive->kondisi) ?></small>
                                                        </p>
                                                    </div>
                                                <?php endforeach; ?>

                                                <!-- Payment History -->
                                                <?php foreach ($payment_history as $payment): ?>
                                                    <div class="timeline-item">
                                                        <strong><?= date('d F Y H:i', strtotime($payment->created_at)) ?></strong>
                                                        <p class="mb-0">
                                                            <span class="badge badge-success">PAYMENT</span><br>
                                                            Pembayaran (<?= $payment->no_payment ?>) sebesar <strong>Rp <?= number_format($payment->jumlah_bayar, 0, ',', '.') ?></strong>
                                                            <br><small class="text-muted">Metode: <?= ucfirst($payment->metode_bayar) ?> oleh <?= htmlspecialchars($payment->created_by) ?></small>
                                                        </p>
                                                    </div>
                                                <?php endforeach; ?>

                                                <!-- Completed -->
                                                <?php if ($po->status == 'completed'): ?>
                                                    <div class="timeline-item">
                                                        <strong><?= date('d F Y H:i', strtotime($po->updated_at)) ?></strong>
                                                        <p class="mb-0">
                                                            <span class="badge badge-success">COMPLETED</span><br>
                                                            PO selesai (Barang diterima lengkap & Lunas)
                                                        </p>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Cancelled -->
                                                <?php if ($po->status == 'cancelled'): ?>
                                                    <div class="timeline-item">
                                                        <strong><?= date('d F Y H:i', strtotime($po->updated_at)) ?></strong>
                                                        <p class="mb-0">
                                                            <span class="badge badge-dark">CANCELLED</span><br>
                                                            PO dibatalkan
                                                        </p>
                                                    </div>
                                                <?php endif; ?>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Right Column - Summary -->
                        <div class="col-lg-4">

                            <!-- Calculation Summary -->
                            <div class="calculation-summary shadow mb-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-calculator"></i> Ringkasan Perhitungan
                                </h5>

                                <div class="calculation-row">
                                    <span>Subtotal Items:</span>
                                    <strong>Rp <?= number_format($po->subtotal, 0, ',', '.') ?></strong>
                                </div>

                                <?php if ($po->diskon_nominal > 0): ?>
                                    <div class="calculation-row">
                                        <span>Diskon (<?= $po->diskon_persen ?>%):</span>
                                        <strong>Rp <?= number_format($po->diskon_nominal, 0, ',', '.') ?></strong>
                                    </div>
                                <?php endif; ?>

                                <?php if ($po->ongkir > 0): ?>
                                    <div class="calculation-row">
                                        <span>Ongkir:</span>
                                        <strong>Rp <?= number_format($po->ongkir, 0, ',', '.') ?></strong>
                                    </div>
                                <?php endif; ?>

                                <?php if ($po->biaya_lain > 0): ?>
                                    <div class="calculation-row">
                                        <span>Biaya Lain:</span>
                                        <strong>Rp <?= number_format($po->biaya_lain, 0, ',', '.') ?></strong>
                                    </div>
                                <?php endif; ?>

                                <?php if ($po->ppn_nominal > 0): ?>
                                    <div class="calculation-row">
                                        <span>PPN (<?= $po->ppn_persen ?>%):</span>
                                        <strong>Rp <?= number_format($po->ppn_nominal, 0, ',', '.') ?></strong>
                                    </div>
                                <?php endif; ?>

                                <?php if ($po->pph_nominal > 0): ?>
                                    <div class="calculation-row">
                                        <span>PPh (<?= $po->pph_persen ?>%):</span>
                                        <strong>- Rp <?= number_format($po->pph_nominal, 0, ',', '.') ?></strong>
                                    </div>
                                <?php endif; ?>

                                <div class="calculation-row">
                                    <span>TOTAL PO:</span>
                                    <strong>Rp <?= number_format($po->total_po, 0, ',', '.') ?></strong>
                                </div>
                            </div>

                            <!-- Payment Status -->
                            <?php
                            $total_dibayar = 0;
                            foreach ($payment_history as $payment) {
                                $total_dibayar += $payment->jumlah_bayar;
                            }
                            $sisa_bayar = $po->total_po - $total_dibayar;
                            ?>

                            <div class="card shadow mb-4">
                                <div class="card-header bg-success text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-money-bill-wave"></i> Status Pembayaran
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Total PO:</strong><br>
                                        <h4 class="text-primary">Rp <?= number_format($po->total_po, 0, ',', '.') ?></h4>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Total Dibayar:</strong><br>
                                        <h4 class="text-success">Rp <?= number_format($total_dibayar, 0, ',', '.') ?></h4>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Sisa Pembayaran:</strong><br>
                                        <h4 class="text-<?= $sisa_bayar > 0 ? 'danger' : 'success' ?>">
                                            Rp <?= number_format($sisa_bayar, 0, ',', '.') ?>
                                        </h4>
                                    </div>

                                    <?php if ($sisa_bayar > 0): ?>
                                        <div class="progress mb-2" style="height: 25px;">
                                            <?php
                                            $persentase = ($total_dibayar / $po->total_po) * 100;
                                            ?>
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: <?= $persentase ?>%"
                                                aria-valuenow="<?= $persentase ?>"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <?= number_format($persentase, 1) ?>%
                                            </div>
                                        </div>
                                        <small class="text-muted">Progress Pembayaran</small>
                                    <?php else: ?>
                                        <div class="alert alert-success mb-0">
                                            <i class="fas fa-check-circle"></i> <strong>LUNAS</strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle"></i> Reject Purchase Order
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" action="<?= base_url('purchase_order/reject/' . $po->id) ?>">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="reject_reason" class="form-control" rows="4"
                                placeholder="Jelaskan alasan penolakan PO ini..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times-circle"></i> Reject PO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-ban"></i> Cancel Purchase Order
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" action="<?= base_url('purchase_order/cancel/' . $po->id) ?>">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Alasan Pembatalan <span class="text-danger">*</span></label>
                            <textarea name="cancel_reason" class="form-control" rows="4"
                                placeholder="Jelaskan alasan pembatalan PO ini..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-ban"></i> Cancel PO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>

    <script>
        $(document).ready(function() {
            // Auto hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Console logging
            console.log('📄 Detail Purchase Order Ready!');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('No. PO: <?= $po->no_po ?>');
            console.log('Status: <?= $po->status ?>');
            console.log('Total: Rp <?= number_format($po->total_po, 0, ',', '.') ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Tabs:');
            console.log('- Informasi PO');
            console.log('- Items (<?= count($details) ?>)');
            console.log('- Penerimaan (<?= count($receiving_history) ?>)');
            console.log('- Pembayaran (<?= count($payment_history) ?>)');
            console.log('- Timeline');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });
    </script>
</body>

</html>
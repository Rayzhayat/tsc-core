<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .detail-card {
            border-left: 4px solid #4e73df;
        }

        .info-label {
            font-weight: 600;
            color: #5a5c69;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1rem;
            color: #3a3b45;
            margin-bottom: 20px;
        }

        .badge-tax {
            padding: 8px 15px;
            font-size: 0.9rem;
            border-radius: 20px;
        }

        .document-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .action-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #858796;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
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
                            <i class="fas fa-user-tie text-primary"></i> <?= $title ?>
                        </h1>
                        <div class="action-buttons">
                            <?php
                            $level = $this->session->userdata('login')['user_level'] ?? '';
                            $can_edit = in_array($level, ['superadmin', 'admin_document', 'finance_staff']);
                            ?>

                            <?php if ($can_edit): ?>
                                <a href="<?= base_url('customer/ubah/' . $customer->id) ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            <?php endif; ?>

                            <a href="<?= base_url('customer') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
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

                    <div class="row">
                        <!-- Main Info -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4 detail-card">
                                <div class="card-header py-3 bg-primary text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-info-circle"></i> Informasi Customer
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- Kode Customer -->
                                            <div class="info-label">
                                                <i class="fas fa-barcode"></i> Kode Customer
                                            </div>
                                            <div class="info-value">
                                                <span class="badge badge-primary"
                                                    style="font-size: 1rem; padding: 8px 15px;">
                                                    <?= htmlspecialchars($customer->kode) ?>
                                                </span>
                                            </div>

                                            <!-- Nama Customer -->
                                            <div class="info-label">
                                                <i class="fas fa-building"></i> Nama Customer
                                            </div>
                                            <div class="info-value">
                                                <strong style="font-size: 1.1rem;">
                                                    <?= htmlspecialchars($customer->nama) ?>
                                                </strong>
                                            </div>

                                            <!-- Telepon -->
                                            <div class="info-label">
                                                <i class="fas fa-phone"></i> Telepon
                                            </div>
                                            <div class="info-value">
                                                <?php if ($customer->telepon): ?>
                                                    <a href="tel:<?= $customer->telepon ?>" class="text-primary">
                                                        <i class="fas fa-phone-alt"></i>
                                                        <?= htmlspecialchars($customer->telepon) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <em class="text-muted">Tidak ada data</em>
                                                <?php endif; ?>
                                            </div>

                                            <!-- PIC -->
                                            <div class="info-label">
                                                <i class="fas fa-user"></i> PIC (Person In Charge)
                                            </div>
                                            <div class="info-value">
                                                <?= $customer->pic ? htmlspecialchars($customer->pic) : '<em class="text-muted">Tidak ada data</em>' ?>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <!-- NPWP -->
                                            <div class="info-label">
                                                <i class="fas fa-id-card"></i> NPWP
                                            </div>
                                            <div class="info-value">
                                                <?= $customer->npwp ? '<code style="font-size: 1rem;">' . htmlspecialchars($customer->npwp) . '</code>' : '<em class="text-muted">Tidak ada data</em>' ?>
                                            </div>

                                            <!-- Nama NPWP -->
                                            <div class="info-label">
                                                <i class="fas fa-file-signature"></i> Nama NPWP
                                            </div>
                                            <div class="info-value">
                                                <?= $customer->nama_npwp ? htmlspecialchars($customer->nama_npwp) : '<em class="text-muted">Tidak ada data</em>' ?>
                                            </div>

                                            <!-- Alamat -->
                                            <div class="info-label">
                                                <i class="fas fa-map-marker-alt"></i> Alamat
                                            </div>
                                            <div class="info-value">
                                                <?php if ($customer->alamat): ?>
                                                    <div style="white-space: pre-line;">
                                                        <?= htmlspecialchars($customer->alamat) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <em class="text-muted">Tidak ada data</em>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tax Info Sidebar -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-success text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-percentage"></i> Informasi Pajak
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <!-- PPH -->
                                    <div class="mb-4">
                                        <div class="info-label mb-2">PPH (Pajak Penghasilan)</div>
                                        <?php if ($customer->pph): ?>
                                            <span class="badge badge-warning badge-tax">
                                                <i class="fas fa-percentage"></i> <?= htmlspecialchars($customer->pph) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary badge-tax">
                                                <i class="fas fa-times"></i> Tidak Kena PPH
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- PPN -->
                                    <div>
                                        <div class="info-label mb-2">PPN (Pajak Pertambahan Nilai)</div>
                                        <?php if ($customer->ppn): ?>
                                            <span class="badge badge-success badge-tax">
                                                <i class="fas fa-percentage"></i> <?= htmlspecialchars($customer->ppn) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary badge-tax">
                                                <i class="fas fa-times"></i> Tidak Kena PPN
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Additional Info -->
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Informasi pajak digunakan untuk perhitungan invoice
                                    </small>
                                </div>
                            </div>

                            <!-- Stats Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-info text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-chart-bar"></i> Statistik
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Total Invoice</small>
                                        <h4 class="mb-0"><?= number_format($total_invoices) ?></h4>
                                    </div>

                                    <div class="mb-3">
                                        <small class="text-muted">Total Paid</small>
                                        <h4 class="mb-0 text-success">
                                            Rp <?= number_format($paid_amount ?? 0, 0, ',', '.') ?>
                                        </h4>
                                    </div>

                                    <div>
                                        <small class="text-muted">Outstanding</small>
                                        <h4 class="mb-0 text-danger">
                                            Rp <?= number_format($total_outstanding, 0, ',', '.') ?>
                                        </h4>
                                    </div>

                                    <hr class="my-3">

                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i>
                                        Bergabung sejak:
                                        <strong><?= date('d M Y', strtotime($customer->created_at ?? 'now')) ?></strong>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Related Invoices (Optional - if you want to show) -->
                    <!-- Related Invoices -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-file-invoice"></i> Invoice Terkait
                                        <span
                                            class="badge badge-primary ml-2"><?= number_format($total_invoices) ?></span>
                                    </h6>
                                    <?php if (!empty($invoices)): ?>
                                        <a href="<?= base_url('invoice_tsc?customer_id=' . $customer->kode) ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Lihat Semua Invoice
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($invoices)): ?>
                                        <!-- Empty State -->
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p class="mb-0">Belum ada invoice untuk customer ini</p>
                                            <small class="text-muted">Invoice akan muncul setelah dibuat</small>
                                            <?php
                                            $can_create = in_array($user_level ?? '', ['superadmin', 'finance_staff']);
                                            if ($can_create):
                                                ?>
                                                <div class="mt-3">
                                                    <a href="<?= base_url('invoice_tsc/tambah') ?>"
                                                        class="btn btn-success btn-sm">
                                                        <i class="fas fa-plus"></i> Buat Invoice Baru
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Invoice Table -->
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="3%">No</th>
                                                        <th width="15%">No. Invoice</th>
                                                        <th width="10%">Tanggal</th>
                                                        <th width="12%">Periode Shipment</th>
                                                        <th width="15%" class="text-right">Total</th>
                                                        <th width="10%" class="text-center">Status</th>
                                                        <th width="10%" class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $no = 1;
                                                    foreach ($invoices as $inv):
                                                        ?>
                                                        <tr>
                                                            <td><?= $no++ ?></td>
                                                            <td>
                                                                <strong><?= htmlspecialchars($inv->no_invoice) ?></strong>
                                                                <?php if ($inv->no_faktur): ?>
                                                                    <br><small class="text-muted">
                                                                        Faktur: <?= htmlspecialchars($inv->no_faktur) ?>
                                                                    </small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?= date('d/m/Y', strtotime($inv->invoice_date)) ?>
                                                                <br><small class="text-muted">
                                                                    Due: <?= date('d/m/Y', strtotime($inv->due_date)) ?>
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($inv->periode_shipment)): ?>
                                                                    <span class="badge badge-info" style="font-size: 12px;">
                                                                        <i class="fas fa-calendar-alt"></i>
                                                                        <?= htmlspecialchars($inv->periode_shipment) ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="text-right">
                                                                <strong>Rp
                                                                    <?= number_format($inv->grand_total, 0, ',', '.') ?></strong>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php
                                                                $badge_class = 'secondary';
                                                                $badge_text = strtoupper($inv->status);

                                                                switch ($inv->status) {
                                                                    case 'draft':
                                                                        $badge_class = 'secondary';
                                                                        break;
                                                                    case 'sent':
                                                                        $badge_class = 'primary';
                                                                        break;
                                                                    case 'paid':
                                                                        $badge_class = 'success';
                                                                        break;
                                                                    case 'cancelled':
                                                                        $badge_class = 'danger';
                                                                        break;
                                                                }
                                                                ?>
                                                                <span class="badge badge-<?= $badge_class ?>"
                                                                    style="color: #fff; font-size: 11px;">
                                                                    <?= $badge_text ?>
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="<?= base_url('invoice_tsc/detail/' . $inv->id) ?>"
                                                                    class="btn btn-sm btn-info" title="Detail">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="<?= base_url('invoice_tsc/export_pdf/' . $inv->id) ?>"
                                                                    class="btn btn-sm btn-danger" title="Export PDF">
                                                                    <i class="fas fa-file-pdf"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot class="bg-light">
                                                    <tr>
                                                        <td colspan="4" class="text-right">
                                                            <strong>Total (10 Invoice Terakhir):</strong>
                                                        </td>
                                                        <td class="text-right">
                                                            <strong>
                                                                Rp
                                                                <?= number_format(array_sum(array_column($invoices, 'grand_total')), 0, ',', '.') ?>
                                                            </strong>
                                                        </td>
                                                        <td colspan="2"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <?php if ($total_invoices > 10): ?>
                                            <div class="text-center mt-3">
                                                <a href="<?= base_url('invoice_tsc?customer_id=' . $customer->kode) ?>"
                                                    class="btn btn-primary">
                                                    <i class="fas fa-list"></i> Lihat Semua
                                                    <?= number_format($total_invoices) ?> Invoice
                                                </a>
                                            </div>
                                        <?php endif; ?>
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

    <?php $this->load->view('partials/js') ?>
</body>

</html>
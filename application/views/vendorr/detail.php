<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .detail-card {
            border-left: 4px solid #1cc88a;
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
            background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
        }

        .document-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .document-item:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .document-item a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }

        .document-item i {
            font-size: 2rem;
            margin-bottom: 10px;
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
                            <i class="fas fa-truck text-success"></i> <?= $title ?>
                        </h1>
                        <div class="action-buttons">
                            <?php
                            $level = $this->session->userdata('login')['user_level'] ?? '';
                            $can_edit = in_array($level, ['superadmin', 'admin_document', 'finance_staff']);
                            ?>

                            <?php if ($can_edit): ?>
                                <a href="<?= base_url('vendorr/ubah/' . $vendor->kode) ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            <?php endif; ?>

                            <a href="<?= base_url('vendorr') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <div class="row">
                        <!-- Main Info -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4 detail-card">
                                <div class="card-header py-3 bg-success text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-info-circle"></i> Informasi Vendor
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- Kode Vendor -->
                                            <div class="info-label">
                                                <i class="fas fa-barcode"></i> Kode Vendor
                                            </div>
                                            <div class="info-value">
                                                <span class="badge badge-success"
                                                    style="font-size: 1rem; padding: 8px 15px;">
                                                    <?= htmlspecialchars($vendor->kode) ?>
                                                </span>
                                            </div>

                                            <!-- Nama Vendor -->
                                            <div class="info-label">
                                                <i class="fas fa-building"></i> Nama Vendor
                                            </div>
                                            <div class="info-value">
                                                <strong style="font-size: 1.1rem;">
                                                    <?= htmlspecialchars($vendor->nama_vendor) ?>
                                                </strong>
                                            </div>

                                            <!-- NPWP -->
                                            <div class="info-label">
                                                <i class="fas fa-id-card"></i> NPWP
                                            </div>
                                            <div class="info-value">
                                                <?= $vendor->npwp_vendor ? '<code style="font-size: 1rem;">' . htmlspecialchars($vendor->npwp_vendor) . '</code>' : '<em class="text-muted">Tidak ada data</em>' ?>
                                            </div>

                                            <!-- PIC -->
                                            <div class="info-label">
                                                <i class="fas fa-user"></i> PIC (Person In Charge)
                                            </div>
                                            <div class="info-value">
                                                <?= $vendor->pic_vendor ? htmlspecialchars($vendor->pic_vendor) : '<em class="text-muted">Tidak ada data</em>' ?>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <!-- Telepon -->
                                            <div class="info-label">
                                                <i class="fas fa-phone"></i> Telepon
                                            </div>
                                            <div class="info-value">
                                                <?php if ($vendor->no_telp_vendor): ?>
                                                    <a href="tel:<?= $vendor->no_telp_vendor ?>" class="text-success">
                                                        <i class="fas fa-phone-alt"></i>
                                                        <?= htmlspecialchars($vendor->no_telp_vendor) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <em class="text-muted">Tidak ada data</em>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Alamat -->
                                            <div class="info-label">
                                                <i class="fas fa-map-marker-alt"></i> Alamat
                                            </div>
                                            <div class="info-value">
                                                <?php if ($vendor->alamat_vendor): ?>
                                                    <div style="white-space: pre-line;">
                                                        <?= htmlspecialchars($vendor->alamat_vendor) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <em class="text-muted">Tidak ada data</em>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Documents Section -->
                            <div class="card shadow mb-4">
                                <div class="card-body p-0">
                                    <div class="document-section">
                                        <h5 class="mb-4">
                                            <i class="fas fa-file-pdf"></i> Dokumen Vendor
                                        </h5>

                                        <div class="row">
                                            <!-- NPWP -->
                                            <div class="col-md-4">
                                                <div class="document-item text-center">
                                                    <?php if (!empty($vendor->file_npwp)): ?>
                                                        <i class="fas fa-file-pdf"></i>
                                                        <div class="mt-2">
                                                            <a href="<?= base_url('assets/uploads/vendor/' . $vendor->file_npwp) ?>"
                                                                target="_blank">
                                                                <i class="fas fa-download"></i> NPWP
                                                            </a>
                                                        </div>
                                                        <small style="opacity: 0.8;">Lihat Dokumen</small>
                                                    <?php else: ?>
                                                        <i class="fas fa-times-circle"></i>
                                                        <div class="mt-2">NPWP</div>
                                                        <small style="opacity: 0.6;">Tidak ada file</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- SKB -->
                                            <div class="col-md-4">
                                                <div class="document-item text-center">
                                                    <?php if (!empty($vendor->file_skb)): ?>
                                                        <i class="fas fa-file-pdf"></i>
                                                        <div class="mt-2">
                                                            <a href="<?= base_url('assets/uploads/vendor/' . $vendor->file_skb) ?>"
                                                                target="_blank">
                                                                <i class="fas fa-download"></i> SKB
                                                            </a>
                                                        </div>
                                                        <small style="opacity: 0.8;">Lihat Dokumen</small>
                                                    <?php else: ?>
                                                        <i class="fas fa-times-circle"></i>
                                                        <div class="mt-2">SKB</div>
                                                        <small style="opacity: 0.6;">Tidak ada file</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- SPPKP -->
                                            <div class="col-md-4">
                                                <div class="document-item text-center">
                                                    <?php if (!empty($vendor->file_sppkp)): ?>
                                                        <i class="fas fa-file-pdf"></i>
                                                        <div class="mt-2">
                                                            <a href="<?= base_url('assets/uploads/vendor/' . $vendor->file_sppkp) ?>"
                                                                target="_blank">
                                                                <i class="fas fa-download"></i> SPPKP
                                                            </a>
                                                        </div>
                                                        <small style="opacity: 0.8;">Lihat Dokumen</small>
                                                    <?php else: ?>
                                                        <i class="fas fa-times-circle"></i>
                                                        <div class="mt-2">SPPKP</div>
                                                        <small style="opacity: 0.6;">Tidak ada file</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tax Info Sidebar -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-warning text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-percentage"></i> Informasi Pajak
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <!-- PPN -->
                                    <div class="mb-4">
                                        <div class="info-label mb-2">PPN (Pajak Pertambahan Nilai)</div>
                                        <?php if ($vendor->ppn_vendor && $vendor->ppn_vendor != 'Belum PPN'): ?>
                                            <span class="badge badge-success badge-tax">
                                                <i class="fas fa-check-circle"></i>
                                                <?= htmlspecialchars($vendor->ppn_vendor) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary badge-tax">
                                                <i class="fas fa-times"></i> Belum PPN
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- PPH -->
                                    <div>
                                        <div class="info-label mb-2">PPH (Pajak Penghasilan)</div>
                                        <?php if ($vendor->pph_vendor && $vendor->pph_vendor != 'Belum PPH'): ?>
                                            <span class="badge badge-warning badge-tax">
                                                <i class="fas fa-check-circle"></i>
                                                <?= htmlspecialchars($vendor->pph_vendor) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary badge-tax">
                                                <i class="fas fa-times"></i> Belum PPH
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Additional Info -->
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Informasi pajak digunakan untuk perhitungan tagihan vendor
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
                                    <?php
                                    // Get statistics (you'll need to add these to controller)
                                    $total_tagihan = 0; // TODO: Get from database
                                    $total_outstanding = 0; // TODO: Get from database
                                    ?>

                                    <div class="mb-3">
                                        <small class="text-muted">Total Tagihan</small>
                                        <h4 class="mb-0"><?= $total_tagihan ?></h4>
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
                                        <strong><?= date('d M Y', strtotime($vendor->created_at ?? 'now')) ?></strong>
                                    </small>
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
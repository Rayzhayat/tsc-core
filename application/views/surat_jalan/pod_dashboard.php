<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        :root {
            --pod-pending: #f6c23e;
            --pod-completed: #1cc88a;
            --pod-rejected: #e74a3b;
            --pod-good: #d4edda;
            --pod-damaged: #f8d7da;
            --pod-partial: #fff3cd;
        }

        /* POD Status Badges */
        .pod-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pod-badge.pending {
            background: var(--pod-pending);
            color: #856404;
        }

        .pod-badge.completed {
            background: var(--pod-completed);
            color: white;
        }

        .pod-badge.rejected {
            background: var(--pod-rejected);
            color: white;
        }

        /* Condition Badges */
        .condition-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .condition-badge.baik {
            background: var(--pod-good);
            color: #155724;
        }

        .condition-badge.rusak {
            background: var(--pod-damaged);
            color: #721c24;
        }

        .condition-badge.rusak_sebagian,
        .condition-badge.kurang {
            background: var(--pod-partial);
            color: #856404;
        }

        /* Stats Cards */
        .pod-stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .pod-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .pod-stat-card.completed {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .pod-stat-card.pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .pod-stat-card.good {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .pod-stat-card.damaged {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        /* Timeline Badge */
        .timeline-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
        }

        /* Table Enhancements */
        .table-pod {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .table-pod thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .table-pod thead th {
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 16px 12px;
        }

        .table-pod tbody tr {
            transition: all 0.2s ease;
        }

        .table-pod tbody tr:hover {
            background-color: #f8f9fc;
            transform: scale(1.01);
        }

        /* Photo Preview */
        .photo-preview {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e3e6f0;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .photo-preview:hover {
            transform: scale(1.5);
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* Action Buttons */
        .btn-pod-action {
            padding: 6px 16px;
            font-size: 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-pod-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #858796;
        }

        .empty-state i {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 20px;
        }

        /* Signature Preview */
        .signature-preview {
            max-width: 150px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 4px;
        }

        /* Duration Badge */
        .duration-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
    </style>
</head>

<body id="page-top" class="fixed-nav">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>

                <div class="container-fluid">
                    <!-- PAGE HEADING -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-clipboard-check text-primary"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('surat_jalan') ?>" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Back to Surat Jalan
                            </a>
                        </div>
                    </div>

                    <!-- ALERTS -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- STATISTICS CARDS -->
                    <div class="row mb-4">
                        <?php
                        $stats = $statistics ?? null;
                        $total_trips = $stats->total_trips ?? 0;
                        $completed = $stats->completed_pods ?? 0;
                        $pending = $stats->pending_pods ?? 0;
                        $good_condition = $stats->good_condition ?? 0;
                        $damaged = ($stats->partial_damage ?? 0) + ($stats->damaged ?? 0) + ($stats->shortage ?? 0);
                        ?>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="pod-stat-card">
                                <div class="card-body">
                                    <div class="stat-value"><?= number_format($total_trips) ?></div>
                                    <div class="stat-label">Total Trips</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="pod-stat-card completed">
                                <div class="card-body">
                                    <div class="stat-value"><?= number_format($completed) ?></div>
                                    <div class="stat-label">POD Completed</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="pod-stat-card pending">
                                <div class="card-body">
                                    <div class="stat-value"><?= number_format($pending) ?></div>
                                    <div class="stat-label">POD Pending</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="pod-stat-card good">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="stat-value" style="font-size: 1.8rem;">
                                                <?= number_format($good_condition) ?> / <?= number_format($damaged) ?>
                                            </div>
                                            <div class="stat-label">Good / Damaged</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box-open fa-3x" style="opacity: 0.3;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FILTER SECTION -->
                    <div class="filter-section">
                        <form method="GET" action="<?= base_url('surat_jalan/pod_dashboard') ?>">
                            <div class="row align-items-end">
                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-gray-700">Status</label>
                                    <select name="status" class="form-control form-control-sm">
                                        <option value="pending" <?= ($filters['status'] ?? '') == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="completed" <?= ($filters['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-gray-700">Date From</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= $filters['date_from'] ?? '' ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="small font-weight-bold text-gray-700">Date To</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= $filters['date_to'] ?? '' ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-gray-700">Driver</label>
                                    <select name="driver_id" class="form-control form-control-sm">
                                        <option value="">All Drivers</option>
                                        <?php foreach ($drivers ?? [] as $driver): ?>
                                            <option value="<?= $driver->id ?>" <?= ($filters['driver_id'] ?? '') == $driver->id ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($driver->nama_driver) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-sm mr-2">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="<?= base_url('surat_jalan/pod_dashboard') ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- POD TABLE -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-gradient-primary">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-list"></i> POD List -
                                <?= ucfirst($filters['status'] ?? 'pending') ?>
                                <span class="badge badge-light ml-2"><?= count($pods ?? []) ?></span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-pod mb-0" width="100%">
                                    <thead>
                                        <tr>
                                            <th width="3%">No</th>
                                            <th width="10%">No. SJ</th>
                                            <th width="8%">Tanggal</th>
                                            <th width="10%">Driver</th>
                                            <th width="8%">No Polisi</th>
                                            <th width="15%">Tujuan</th>
                                            <th width="8%">Status</th>
                                            <?php if (($filters['status'] ?? 'pending') == 'completed'): ?>
                                                <th width="10%">Penerima</th>
                                                <th width="8%">Kondisi</th>
                                                <th width="5%">Qty</th>
                                                <th width="5%">Foto</th>
                                            <?php endif; ?>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pods)): ?>
                                            <tr>
                                                <td colspan="<?= ($filters['status'] ?? 'pending') == 'completed' ? '12' : '8' ?>" class="empty-state">
                                                    <i class="fas fa-inbox"></i>
                                                    <h5 class="mt-3">No POD Data</h5>
                                                    <p class="text-muted">There are no <?= $filters['status'] ?? 'pending' ?> PODs at the moment.</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $no = 1;
                                            foreach ($pods as $pod):
                                            ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <strong class="text-primary"><?= $pod->no_sj ?></strong>
                                                    </td>
                                                    <td>
                                                        <small><?= date('d/m/Y', strtotime($pod->tanggal)) ?></small>
                                                    </td>
                                                    <td><?= htmlspecialchars($pod->nama_driver ?? '-') ?></td>
                                                    <td>
                                                        <span class="badge badge-info"><?= $pod->no_polisi ?? '-' ?></span>
                                                    </td>
                                                    <td>
                                                        <small><?= htmlspecialchars($pod->tujuan ?? '-') ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="pod-badge <?= $pod->pod_status ?>">
                                                            <?= strtoupper($pod->pod_status) ?>
                                                        </span>
                                                    </td>

                                                    <?php if (($filters['status'] ?? 'pending') == 'completed'): ?>
                                                        <td>
                                                            <small>
                                                                <strong><?= htmlspecialchars($pod->receiver_name ?? '-') ?></strong>
                                                                <?php if ($pod->receiver_phone): ?>
                                                                    <br><i class="fas fa-phone text-muted"></i> <?= $pod->receiver_phone ?>
                                                                <?php endif; ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <span class="condition-badge <?= $pod->delivery_condition ?? 'baik' ?>">
                                                                <?= ucfirst(str_replace('_', ' ', $pod->delivery_condition ?? 'baik')) ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <strong class="text-success"><?= $pod->qty_delivered ?? 0 ?></strong>
                                                            <?php if (($pod->qty_rejected ?? 0) > 0): ?>
                                                                <br><small class="text-danger">-<?= $pod->qty_rejected ?></small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if ($pod->photo_proof): ?>
                                                                <a href="<?= base_url('uploads/pod/proof/' . $pod->photo_proof) ?>" target="_blank">
                                                                    <img src="<?= base_url('uploads/pod/proof/' . $pod->photo_proof) ?>"
                                                                        class="photo-preview"
                                                                        alt="Proof">
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>

                                                    <td class="text-center">
                                                        <?php if ($pod->pod_status == 'pending'): ?>
                                                            <a href="<?= base_url('surat_jalan/pod_form/' . $pod->id) ?>"
                                                                class="btn btn-primary btn-pod-action">
                                                                <i class="fas fa-clipboard-check"></i> Submit POD
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="<?= base_url('surat_jalan/pod_view/' . $pod->id) ?>"
                                                                class="btn btn-info btn-pod-action">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <a href="<?= base_url('surat_jalan/print_pod/' . $pod->id) ?>"
                                                                class="btn btn-secondary btn-pod-action"
                                                                target="_blank">
                                                                <i class="fas fa-print"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
        $(document).ready(function() {
            // Auto-hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Photo preview lightbox
            $('.photo-preview').on('click', function(e) {
                e.preventDefault();
                // You can add lightbox library here
            });

            console.log('📋 POD Dashboard loaded - Total PODs: <?= count($pods ?? []) ?>');
        });
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        .stats-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        /* Status Badges */
        .badge-draft {
            background: #858796;
        }

        .badge-scheduled {
            background: #4e73df;
        }

        .badge-on_trip {
            background: #f6c23e;
            color: #000;
        }

        .badge-completed {
            background: #1cc88a;
        }

        .badge-cancelled {
            background: #e74a3b;
        }

        /* SLA Status */
        .badge-on_time {
            background: #1cc88a;
        }

        .badge-late {
            background: #f6c23e;
            color: #000;
        }

        .badge-very_late {
            background: #e74a3b;
        }

        .action-buttons .btn {
            margin: 2px;
        }

        .filter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .overdue-row {
            background-color: #fff3cd !important;
        }

        .nominal-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
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
                            <i class="fas fa-truck text-primary"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('surat_jalan/dashboard') ?>" class="btn btn-info btn-sm shadow-sm">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a href="<?= base_url('surat_jalan/tambah') ?>" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-plus"></i> Buat Surat Jalan
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

                    <?php if ($this->session->flashdata('warning')): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('warning') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stats-card border-left-primary shadow h-100 py-2" style="border-left-color: #4e73df;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Surat Jalan
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $summary->total_surat_jalan ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stats-card border-left-warning shadow h-100 py-2" style="border-left-color: #f6c23e;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                On Trip
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $summary->total_on_trip ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stats-card border-left-success shadow h-100 py-2" style="border-left-color: #1cc88a;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Completed
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= $summary->total_completed ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stats-card border-left-info shadow h-100 py-2" style="border-left-color: #36b9cc;">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Total Revenue
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800 nominal-cell">
                                                Rp <?= number_format($summary->total_revenue ?? 0, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header filter-card py-3">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-filter"></i> Filter & Pencarian
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('surat_jalan') ?>">
                                <div class="row">
                                    <div class="col-md-2 mb-3">
                                        <label class="font-weight-bold">Dari Tanggal</label>
                                        <input type="date" name="tanggal_dari" class="form-control"
                                            value="<?= $filters['tanggal_dari'] ?>">
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="font-weight-bold">Sampai Tanggal</label>
                                        <input type="date" name="tanggal_sampai" class="form-control"
                                            value="<?= $filters['tanggal_sampai'] ?>">
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="font-weight-bold">Status</label>
                                        <select name="status" class="form-control">
                                            <option value="">Semua Status</option>
                                            <option value="draft" <?= $filters['status'] == 'draft' ? 'selected' : '' ?>>Draft</option>
                                            <option value="scheduled" <?= $filters['status'] == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                            <option value="on_trip" <?= $filters['status'] == 'on_trip' ? 'selected' : '' ?>>On Trip</option>
                                            <option value="completed" <?= $filters['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="cancelled" <?= $filters['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="font-weight-bold">Driver</label>
                                        <select name="driver_id" class="form-control">
                                            <option value="">Semua Driver</option>
                                            <?php foreach ($drivers as $driver): ?>
                                                <option value="<?= $driver->id ?>" <?= $filters['driver_id'] == $driver->id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($driver->nama_driver) ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="font-weight-bold">Unit</label>
                                        <select name="unit_id" class="form-control">
                                            <option value="">Semua Unit</option>
                                            <?php foreach ($units as $unit): ?>
                                                <option value="<?= $unit->id ?>" <?= $filters['unit_id'] == $unit->id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($unit->no_polisi) ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-3">
                                        <label class="font-weight-bold">Customer</label>
                                        <input type="text" name="customer" class="form-control"
                                            placeholder="Cari customer..." value="<?= $filters['customer'] ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-bold">SLA Status</label>
                                        <select name="sla_status" class="form-control">
                                            <option value="">Semua SLA</option>
                                            <option value="on_time" <?= $filters['sla_status'] == 'on_time' ? 'selected' : '' ?>>On Time</option>
                                            <option value="late" <?= $filters['sla_status'] == 'late' ? 'selected' : '' ?>>Late</option>
                                            <option value="very_late" <?= $filters['sla_status'] == 'very_late' ? 'selected' : '' ?>>Very Late</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="font-weight-bold">Keyword (No. SJ / Customer / Driver / Unit)</label>
                                        <input type="text" name="keyword" class="form-control"
                                            placeholder="Cari..." value="<?= $filters['keyword'] ?>">
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="font-weight-bold">&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-search"></i> Cari
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <a href="<?= base_url('surat_jalan') ?>" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset Filter
                                    </a>
                                    <a href="<?= base_url('surat_jalan/export_excel?' . http_build_query($filters)) ?>"
                                        class="btn btn-success">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list"></i> Daftar Surat Jalan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="suratJalanTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="3%">No</th>
                                            <th width="10%">No. Surat Jalan</th>
                                            <th width="7%">Tanggal</th>
                                            <th width="10%">Customer</th>
                                            <th width="8%">Rute</th>
                                            <th width="10%">Driver</th>
                                            <th width="8%">Unit</th>
                                            <th width="8%" class="text-right">Biaya</th>
                                            <th width="7%" class="text-center">Status</th>
                                            <th width="7%" class="text-center">SLA</th>
                                            <th width="12%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($surat_jalan_list)): ?>
                                            <tr>
                                                <td colspan="11" class="text-center">
                                                    <div class="py-5">
                                                        <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                                                        <h5 class="text-gray-500">Belum ada data surat jalan</h5>
                                                        <p class="text-muted">Klik tombol "Buat Surat Jalan" untuk membuat surat jalan baru</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $no = 1;
                                            foreach ($surat_jalan_list as $sj):
                                                // Check if overdue (on_trip & past target_tiba)
                                                $is_overdue = false;
                                                if ($sj->status == 'on_trip' && $sj->target_tiba) {
                                                    $is_overdue = (strtotime($sj->target_tiba) < time());
                                                }

                                                $row_class = $is_overdue ? 'overdue-row' : '';
                                            ?>
                                                <tr class="<?= $row_class ?>">
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($sj->no_surat_jalan) ?></strong>
                                                        <?php if ($is_overdue): ?>
                                                            <br><span class="badge badge-danger badge-sm">
                                                                <i class="fas fa-exclamation-triangle"></i> OVERDUE
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($sj->tanggal)) ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($sj->customer) ?></strong>
                                                        <br><small class="text-muted">
                                                            <i class="fas fa-tag"></i> <?= htmlspecialchars($sj->service) ?> |
                                                            <?= htmlspecialchars($sj->sla) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <i class="fas fa-map-marker-alt text-success"></i> <?= htmlspecialchars($sj->origin) ?>
                                                            <br>
                                                            <i class="fas fa-arrow-down text-muted"></i>
                                                            <br>
                                                            <i class="fas fa-map-pin text-danger"></i> <?= htmlspecialchars($sj->dest1) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($sj->nama_driver) ?>
                                                        <br><small class="text-muted">NIK: <?= htmlspecialchars($sj->driver_nik) ?></small>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($sj->no_polisi) ?></strong>
                                                        <br><small class="text-muted"><?= htmlspecialchars($sj->unit_tipe) ?></small>
                                                    </td>
                                                    <td class="text-right nominal-cell">
                                                        <small class="text-muted">Sewa:</small><br>
                                                        <strong class="text-primary">Rp <?= number_format($sj->biaya_sewa, 0, ',', '.') ?></strong>
                                                        <?php if ($sj->total_biaya > $sj->biaya_sewa): ?>
                                                            <br><small class="text-danger">
                                                                Total: Rp <?= number_format($sj->total_biaya, 0, ',', '.') ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $status_badge = 'badge-draft';
                                                        $status_icon = 'fa-file';

                                                        switch ($sj->status) {
                                                            case 'scheduled':
                                                                $status_badge = 'badge-scheduled';
                                                                $status_icon = 'fa-calendar-check';
                                                                break;
                                                            case 'on_trip':
                                                                $status_badge = 'badge-on_trip';
                                                                $status_icon = 'fa-truck';
                                                                break;
                                                            case 'completed':
                                                                $status_badge = 'badge-completed';
                                                                $status_icon = 'fa-check-circle';
                                                                break;
                                                            case 'cancelled':
                                                                $status_badge = 'badge-cancelled';
                                                                $status_icon = 'fa-times-circle';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?= $status_badge ?> badge-lg">
                                                            <i class="fas <?= $status_icon ?>"></i> <?= strtoupper($sj->status) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($sj->status == 'completed'): ?>
                                                            <?php
                                                            $sla_badge = 'badge-on_time';
                                                            $sla_icon = 'fa-check';
                                                            $sla_text = 'ON TIME';

                                                            if ($sj->sla_status == 'late') {
                                                                $sla_badge = 'badge-late';
                                                                $sla_icon = 'fa-clock';
                                                                $sla_text = 'LATE';
                                                            } elseif ($sj->sla_status == 'very_late') {
                                                                $sla_badge = 'badge-very_late';
                                                                $sla_icon = 'fa-exclamation-triangle';
                                                                $sla_text = 'VERY LATE';
                                                            }
                                                            ?>
                                                            <span class="badge <?= $sla_badge ?>">
                                                                <i class="fas <?= $sla_icon ?>"></i> <?= $sla_text ?>
                                                            </span>
                                                            <?php if ($sj->keterlambatan > 0): ?>
                                                                <br><small class="text-muted">
                                                                    +<?= round($sj->keterlambatan) ?> menit
                                                                </small>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <small class="text-muted">-</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center action-buttons">
                                                        <!-- Detail -->
                                                        <a href="<?= base_url('surat_jalan/detail/' . $sj->id) ?>"
                                                            class="btn btn-info btn-sm"
                                                            title="Detail"
                                                            data-toggle="tooltip">
                                                            <i class="fas fa-eye"></i>
                                                        </a>

                                                        <!-- Edit (only draft/scheduled) -->
                                                        <?php if (in_array($sj->status, ['draft', 'scheduled'])): ?>
                                                            <a href="<?= base_url('surat_jalan/ubah/' . $sj->id) ?>"
                                                                class="btn btn-warning btn-sm"
                                                                title="Edit"
                                                                data-toggle="tooltip">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <button class="btn btn-secondary btn-sm"
                                                                title="Status <?= $sj->status ?> tidak bisa diedit"
                                                                data-toggle="tooltip"
                                                                disabled>
                                                                <i class="fas fa-lock"></i>
                                                            </button>
                                                        <?php endif; ?>

                                                        <!-- PDF Export -->
                                                        <a href="<?= base_url('surat_jalan/export_pdf/' . $sj->id) ?>"
                                                            class="btn btn-danger btn-sm"
                                                            title="Export PDF"
                                                            data-toggle="tooltip"
                                                            target="_blank">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>

                                                        <!-- Quick Actions based on status -->
                                                        <?php if ($sj->status == 'scheduled'): ?>
                                                            <a href="<?= base_url('surat_jalan/start_trip/' . $sj->id) ?>"
                                                                class="btn btn-success btn-sm"
                                                                title="Start Trip"
                                                                data-toggle="tooltip"
                                                                onclick="return confirm('Mulai trip sekarang?')">
                                                                <i class="fas fa-play"></i>
                                                            </a>
                                                        <?php elseif ($sj->status == 'on_trip'): ?>
                                                            <a href="<?= base_url('surat_jalan/complete_trip/' . $sj->id) ?>"
                                                                class="btn btn-success btn-sm"
                                                                title="Complete Trip"
                                                                data-toggle="tooltip"
                                                                onclick="return confirm('Selesaikan trip?')">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                        <?php endif; ?>

                                                        <!-- Delete (superadmin only, draft/cancelled only) -->
                                                        <?php if ($user_level == 'superadmin' && in_array($sj->status, ['draft', 'cancelled'])): ?>
                                                            <a href="<?= base_url('surat_jalan/hapus/' . $sj->id) ?>"
                                                                class="btn btn-danger btn-sm"
                                                                title="Hapus"
                                                                data-toggle="tooltip"
                                                                onclick="return confirm('Yakin hapus surat jalan ini?\n\nNo: <?= $sj->no_surat_jalan ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Legend -->
                            <div class="mt-3">
                                <small class="text-muted">
                                    <strong>Keterangan Status:</strong><br>
                                    <span class="badge badge-draft">Draft</span> Baru dibuat |
                                    <span class="badge badge-scheduled">Scheduled</span> Dijadwalkan |
                                    <span class="badge badge-on_trip">On Trip</span> Dalam perjalanan |
                                    <span class="badge badge-completed">Completed</span> Selesai |
                                    <span class="badge badge-cancelled">Cancelled</span> Dibatalkan
                                    <br><br>
                                    <strong>Keterangan SLA:</strong><br>
                                    <span class="badge badge-on_time">On Time</span> Tepat waktu |
                                    <span class="badge badge-late">Late</span> Terlambat &lt;1 jam |
                                    <span class="badge badge-very_late">Very Late</span> Terlambat &gt;1 jam
                                    <br><br>
                                    <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i></span>
                                    <em>Baris kuning = Trip sedang berjalan dan sudah melewati target waktu tiba (overdue)</em>
                                </small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#suratJalanTable').DataTable({
                "order": [
                    [0, "desc"]
                ],
                "pageLength": 25,
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                    "infoEmpty": "Tidak ada data tersedia",
                    "infoFiltered": "(difilter dari _MAX_ total data)",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "columnDefs": [{
                        "orderable": false,
                        "targets": [10]
                    } // Disable sorting on Action column
                ]
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Auto hide alerts
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Refresh tooltip on DataTable redraw
            table.on('draw', function() {
                $('[data-toggle="tooltip"]').tooltip();
            });

            // Highlight overdue rows
            var overdueCount = $('.overdue-row').length;
            if (overdueCount > 0) {
                console.log('🚨 PERHATIAN: ' + overdueCount + ' trip overdue!');

                // Optional: Show alert for overdue
                setTimeout(function() {
                    var alertDiv = $('<div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 80px; right: 20px; z-index: 9999; min-width: 300px;">')
                        .html('<i class="fas fa-exclamation-triangle"></i> <strong>Alert!</strong> Terdapat <strong>' + overdueCount + '</strong> trip yang overdue!')
                        .append('<button type="button" class="close" data-dismiss="alert">×</button>');

                    $('body').append(alertDiv);

                    setTimeout(function() {
                        alertDiv.fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }, 10000);
                }, 1000);
            }

            // Stats logging
            console.log('📊 Surat Jalan Statistics:');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Total SJ: <?= $summary->total_surat_jalan ?? 0 ?>');
            console.log('Draft: <?= $summary->total_draft ?? 0 ?>');
            console.log('Scheduled: <?= $summary->total_scheduled ?? 0 ?>');
            console.log('On Trip: <?= $summary->total_on_trip ?? 0 ?>');
            console.log('Completed: <?= $summary->total_completed ?? 0 ?>');
            console.log('Cancelled: <?= $summary->total_cancelled ?? 0 ?>');
            console.log('Total Revenue: Rp <?= number_format($summary->total_revenue ?? 0, 0, ',', '.') ?>');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            <?php if (isset($summary->total_on_time) && isset($summary->total_completed) && $summary->total_completed > 0): ?>
                var slaCompliance = (<?= $summary->total_on_time ?> / <?= $summary->total_completed ?> * 100).toFixed(1);
                console.log('📈 SLA Compliance: ' + slaCompliance + '%');
                console.log('   On Time: <?= $summary->total_on_time ?? 0 ?>');
                console.log('   Late: <?= $summary->total_late ?? 0 ?>');
                console.log('   Very Late: <?= $summary->total_very_late ?? 0 ?>');
            <?php endif; ?>

            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('💡 Shortcuts:');
            console.log('   Ctrl + N : Buat Surat Jalan Baru');
            console.log('   Ctrl + F : Focus Search');
            console.log('   Ctrl + D : Dashboard');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        });

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // Ctrl + N = New Surat Jalan
            if (e.ctrlKey && e.keyCode === 78) {
                e.preventDefault();
                window.location.href = '<?= base_url('surat_jalan/tambah') ?>';
            }

            // Ctrl + F = Focus search
            if (e.ctrlKey && e.keyCode === 70) {
                e.preventDefault();
                $('.dataTables_filter input').focus();
            }

            // Ctrl + D = Dashboard
            if (e.ctrlKey && e.keyCode === 68) {
                e.preventDefault();
                window.location.href = '<?= base_url('surat_jalan/dashboard') ?>';
            }
        });

        // Confirmation for start trip
        $('a[href*="start_trip"]').on('click', function(e) {
            var noSJ = $(this).closest('tr').find('td:eq(1) strong').text().trim();
            var driver = $(this).closest('tr').find('td:eq(5)').text().trim();

            var confirmMsg = '🚚 MULAI TRIP\n\n';
            confirmMsg += 'No. Surat Jalan: ' + noSJ + '\n';
            confirmMsg += 'Driver: ' + driver + '\n\n';
            confirmMsg += 'Mulai trip sekarang?\n\n';
            confirmMsg += '⚠️ Aksi ini akan:\n';
            confirmMsg += '• Set status menjadi ON TRIP\n';
            confirmMsg += '• Mulai tracking waktu\n';
            confirmMsg += '• Aktifkan SLA monitoring\n';

            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
        });

        // Confirmation for complete trip
        $('a[href*="complete_trip"]').on('click', function(e) {
            var noSJ = $(this).closest('tr').find('td:eq(1) strong').text().trim();

            var confirmMsg = '✅ SELESAIKAN TRIP\n\n';
            confirmMsg += 'No. Surat Jalan: ' + noSJ + '\n\n';
            confirmMsg += 'Trip sudah selesai?\n\n';
            confirmMsg += '⚠️ Aksi ini akan:\n';
            confirmMsg += '• Set status menjadi COMPLETED\n';
            confirmMsg += '• Hitung keterlambatan (jika ada)\n';
            confirmMsg += '• Lock untuk edit\n';

            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>

</html>
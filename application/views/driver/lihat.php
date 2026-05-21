<!-- driver/lihat.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .badge-status {
            font-size: 0.75rem;
            padding: 5px 10px;
            font-weight: 600;
        }
        .status-aktif    { background: #1cc88a; color: white; }
        .status-cuti     { background: #f6c23e; color: white; }
        .status-resign   { background: #e74a3b; color: white; }
        .status-nonaktif { background: #858796; color: white; }

        .expiry-warning {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            margin-top: 3px;
        }
        .expiry-ok      { background: #d4edda; color: #155724; }
        .expiry-soon    { background: #fff3cd; color: #856404; }
        .expiry-expired { background: #f8d7da; color: #721c24; }

        .rating-stars { color: #f6c23e; font-size: 0.85rem; }

        .table-hover tbody tr:hover { background-color: #f8f9fc; }

        .driver-photo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e3e6f0;
        }

        .driver-photo-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #e3e6f0;
            background: #f8f9fc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        /* Empty state di luar tabel */
        .empty-state {
            padding: 50px 20px;
            text-align: center;
            color: #b7b9cc;
        }

        .empty-state i {
            font-size: 2.8rem;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>

        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <?php
                    $level    = $this->session->userdata('login')['user_level'] ?? '';
                    $can_edit = in_array($level, ['superadmin', 'admin_operational']);
                    ?>

                    <!-- JUDUL + TOMBOL -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-user-tie text-primary"></i> <?= $title ?>
                        </h1>
                        <?php if ($can_edit): ?>
                            <div>
                                <a href="<?= base_url('driver/export') ?>" class="btn btn-danger btn-sm shadow-sm">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </a>
                                <a href="<?= base_url('driver/tambah') ?>" class="btn btn-primary btn-sm shadow-sm">
                                    <i class="fas fa-plus"></i> Tambah Driver
                                </a>
                                <a href="<?= base_url('driver/terhapus') ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-trash-restore"></i> Driver Terhapus
                                </a>
                            </div>
                        <?php endif ?>
                    </div>

                    <!-- ALERT -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- STATISTICS CARDS -->
                    <div class="row mb-4">
                        <?php
                        $drivers  = $drivers ?? [];
                        $total    = count($drivers);
                        $aktif    = count(array_filter($drivers, fn($d) => ($d->status_driver ?? 'aktif') == 'aktif'));
                        $cuti     = count(array_filter($drivers, fn($d) => ($d->status_driver ?? '') == 'cuti'));
                        $nonaktif = count(array_filter($drivers, fn($d) => in_array(($d->status_driver ?? ''), ['resign', 'nonaktif'])));
                        ?>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col me-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Driver</div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $total ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col me-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Driver Aktif</div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $aktif ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col me-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Cuti</div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $cuti ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col me-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Non-Aktif</div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $nonaktif ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD TABEL -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary">
                            <div class="row align-items-center w-100">
                                <div class="col-md-4">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-list"></i> Daftar Driver
                                    </h6>
                                    <span class="badge bg-light text-dark badge-counter mt-1">
                                        <i class="fas fa-database"></i> <?= count($drivers) ?> Driver Terdaftar
                                    </span>
                                </div>
                                <div class="col-md-8">
                                    <form id="searchForm" class="d-flex">
                                        <input type="text" id="searchInput" class="form-control form-control-sm me-2"
                                            placeholder="Cari nama, NIK, SIM, status..." autocomplete="off">
                                        <button class="btn btn-light btn-sm" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">

                            <?php if (empty($drivers)): ?>
                                <!-- 
                                    Empty state DIPISAH dari tabel agar DataTables
                                    tidak error "Incorrect column count" (tn/18)
                                -->
                                <div class="empty-state">
                                    <i class="fas fa-inbox text-muted"></i>
                                    <em>Tidak ada data driver.</em>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0"
                                        id="dataTable" width="100%" cellspacing="0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="3%">No</th>
                                                <th width="8%">Foto</th>
                                                <th width="15%">Nama Driver</th>
                                                <th width="10%">NIK</th>
                                                <th width="12%">No. SIM / Tipe</th>
                                                <th width="10%">Masa Berlaku</th>
                                                <th width="10%">Kontak</th>
                                                <th width="8%">Status</th>
                                                <th width="10%">Performance</th>
                                                <th width="6%">Foto SIM</th>
                                                <?php if ($can_edit): ?>
                                                    <th width="8%">Aksi</th>
                                                <?php endif ?>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                            <?php
                                            $no = 1;
                                            foreach ($drivers as $driver):
                                                $status       = $driver->status_driver ?? 'aktif';
                                                $status_class = 'status-' . $status;
                                            ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>

                                                    <td class="text-center">
                                                        <?php if (!empty($driver->foto_driver)): ?>
                                                            <a href="<?= base_url('uploads/driver/' . $driver->foto_driver) ?>" target="_blank">
                                                                <img src="<?= base_url('uploads/driver/' . $driver->foto_driver) ?>"
                                                                    class="driver-photo" alt="Driver">
                                                            </a>
                                                        <?php else: ?>
                                                            <div class="driver-photo-placeholder">
                                                                <i class="fas fa-user text-muted"></i>
                                                            </div>
                                                        <?php endif ?>
                                                    </td>

                                                    <td>
                                                        <strong class="text-primary"><?= htmlspecialchars($driver->nama_driver ?? '') ?></strong>
                                                        <?php if (!empty($driver->tanggal_bergabung)): ?>
                                                            <br><small class="text-muted">
                                                                <i class="fas fa-calendar"></i>
                                                                Sejak <?= date('d/m/Y', strtotime($driver->tanggal_bergabung)) ?>
                                                            </small>
                                                        <?php endif ?>
                                                    </td>

                                                    <td><small><?= htmlspecialchars($driver->nik ?? '—') ?></small></td>

                                                    <td>
                                                        <strong><?= htmlspecialchars($driver->sim ?? '—') ?></strong>
                                                        <?php if (!empty($driver->tipe_sim)): ?>
                                                            <br><span class="badge bg-info">SIM <?= $driver->tipe_sim ?></span>
                                                        <?php endif ?>
                                                    </td>

                                                    <td>
                                                        <?php if (!empty($driver->masa_berlaku_sim)):
                                                            $diff = (strtotime($driver->masa_berlaku_sim) - time()) / 86400;
                                                            if ($diff <= 0):
                                                                $exp_class = 'expiry-expired'; $exp_text = '❌ Expired';
                                                            elseif ($diff < 30):
                                                                $exp_class = 'expiry-soon'; $exp_text = '⚠️ ' . ceil($diff) . ' hari';
                                                            else:
                                                                $exp_class = 'expiry-ok'; $exp_text = '✓ ' . date('d/m/Y', strtotime($driver->masa_berlaku_sim));
                                                            endif;
                                                        ?>
                                                            <small class="expiry-warning <?= $exp_class ?>"><?= $exp_text ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif ?>
                                                    </td>

                                                    <td>
                                                        <?php if (!empty($driver->no_hp)): ?>
                                                            <small><i class="fas fa-phone"></i> <?= htmlspecialchars($driver->no_hp) ?></small>
                                                        <?php endif ?>
                                                        <?php if (!empty($driver->email)): ?>
                                                            <br><small><i class="fas fa-envelope"></i> <?= htmlspecialchars($driver->email) ?></small>
                                                        <?php endif ?>
                                                        <?php if (empty($driver->no_hp) && empty($driver->email)): ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif ?>
                                                    </td>

                                                    <td class="text-center">
                                                        <span class="badge badge-status <?= $status_class ?>">
                                                            <?= strtoupper($status) ?>
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <?php
                                                        $rating     = $driver->rating ?? 0;
                                                        $total_trip = $driver->total_trip ?? 0;
                                                        $stars      = round($rating * 2) / 2;
                                                        ?>
                                                        <div class="rating-stars">
                                                            <?php for ($i = 1; $i <= 5; $i++):
                                                                if ($i <= $stars) echo '<i class="fas fa-star"></i>';
                                                                elseif ($i - 0.5 == $stars) echo '<i class="fas fa-star-half-alt"></i>';
                                                                else echo '<i class="far fa-star"></i>';
                                                            endfor ?>
                                                            <br><small class="text-muted"><?= number_format($rating, 1) ?> / 5.0</small>
                                                        </div>
                                                        <small class="text-info d-block mt-1">
                                                            <i class="fas fa-road"></i> <?= number_format($total_trip) ?> trip
                                                        </small>
                                                    </td>

                                                    <td class="text-center">
                                                        <?php if (!empty($driver->foto_sim)): ?>
                                                            <a href="<?= base_url('uploads/sim/' . $driver->foto_sim) ?>" target="_blank">
                                                                <img src="<?= base_url('uploads/sim/' . $driver->foto_sim) ?>"
                                                                    width="40" class="img-thumbnail" alt="SIM">
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif ?>
                                                    </td>

                                                    <?php if ($can_edit): ?>
                                                        <td class="text-center">
                                                            <a href="<?= base_url('driver/ubah/' . ($driver->id ?? '')) ?>"
                                                                class="btn btn-success btn-sm" title="Ubah">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if (in_array($level, ['superadmin', 'admin_operational'])): ?>
                                                                <a onclick="return confirm('Yakin hapus driver <?= htmlspecialchars($driver->nama_driver ?? '') ?>?')"
                                                                    href="<?= base_url('driver/hapus/' . ($driver->id ?? '')) ?>"
                                                                    class="btn btn-danger btn-sm" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-secondary btn-sm" disabled
                                                                    title="Tidak memiliki akses hapus">
                                                                    <i class="fas fa-lock"></i>
                                                                </button>
                                                            <?php endif ?>
                                                        </td>
                                                    <?php endif ?>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif ?>

                        </div>
                    </div>

                </div>
            </div>

            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function () {

            // SEARCH AJAX
            $('#searchForm').on('submit', function (e) {
                e.preventDefault();
                let keyword = $('#searchInput').val().trim();
                $.ajax({
                    url: '<?= base_url('driver/search') ?>',
                    method: 'POST',
                    data: { keyword: keyword },
                    success: function (response) {
                        $('#tableBody').html(response);
                        let count = $('#tableBody tr').length;
                        if ($('#tableBody tr td[colspan]').length > 0) count = 0;
                        $('.badge-counter').html('<i class="fas fa-database"></i> ' + count + ' Driver Terdaftar');
                    },
                    error: function () { alert('Gagal mencari data!'); }
                });
            });

            // REAL-TIME SEARCH
            let searchTimeout;
            $('#searchInput').on('keyup', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () { $('#searchForm').submit(); }, 500);
            });

            // ESC = CLEAR
            $(document).on('keyup', function (e) {
                if (e.key === "Escape") {
                    $('#searchInput').val('');
                    $('#searchForm').submit();
                }
            });

            // DATATABLE — hanya init jika tabel ada di DOM (data tidak kosong)
            if ($('#dataTable').length) {
                $('#dataTable').DataTable({
                    language: { url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" },
                    columnDefs: [
                        { orderable: false, targets: <?= $can_edit ? '[0, 1, 9, 10]' : '[0, 1, 9]' ?> }
                    ],
                    order: [[2, "asc"]],
                    pageLength: 25,
                    destroy: true,
                    responsive: true
                });
            }

            // AUTO HIDE ALERTS
            setTimeout(function () { $('.alert').fadeOut('slow'); }, 5000);

            // TOOLTIPS
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
</body>

</html>
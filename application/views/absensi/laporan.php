<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .filter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }

        .filter-card .form-control,
        .filter-card .form-select {
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            border: none;
        }

        .record-photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .record-photo:hover {
            transform: scale(1.1);
        }

        .stats-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-primary {
            border-color: #4e73df;
        }

        .stats-success {
            border-color: #1cc88a;
        }

        .stats-info {
            border-color: #36b9cc;
        }

        .stats-warning {
            border-color: #f6c23e;
        }

        .modal-photo {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
        }

        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }

        .badge-in {
            background: #1cc88a;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: .75rem;
            font-weight: 600;
        }

        .badge-out {
            background: #e74a3b;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: .75rem;
            font-weight: 600;
        }

        .badge-auto-out {
            background: #f6c23e;
            color: #333;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: .75rem;
            font-weight: 600;
        }

        /* skeleton loader */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 4px;
            display: inline-block;
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        #dataTable tbody tr td {
            vertical-align: middle;
        }

        /* Cursor pointer di header kolom yang bisa di-sort */
        #dataTable thead th:not(.no-sort) {
            cursor: pointer;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- Page Header -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-file-alt"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('absensi') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="button" class="btn btn-success" id="btn-export">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- STATS CARDS -->
                    <?php
                    $total_in = 0;
                    $total_out = 0;
                    $unique_users = 0;
                    foreach ($summary as $s) {
                        $total_in += $s->count_in;
                        $total_out += $s->count_out;
                        if ($s->count_in > 0)
                            $unique_users++;
                    }
                    ?>
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card stats-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total
                                                Record</div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $total_in + $total_out ?></div>
                                            <small class="text-muted">IN: <?= $total_in ?> | OUT:
                                                <?= $total_out ?></small>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card stats-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Karyawan Hadir</div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $unique_users ?></div>
                                            <small class="text-muted">karyawan unik dalam periode</small>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card stats-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total
                                                Hari</div>
                                            <div class="h5 mb-0 font-weight-bold"><?= $total_days ?> hari</div>
                                            <small class="text-muted">
                                                <?= date('d M', strtotime($start_date)) ?> –
                                                <?= date('d M Y', strtotime($end_date)) ?>
                                            </small>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-calendar fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card stats-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Hadir
                                                Hari Ini</div>
                                            <div class="h5 mb-0 font-weight-bold" id="today-count">
                                                <span class="skeleton"
                                                    style="width:40px;height:24px;">&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                            </div>
                                        </div>
                                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CHART & SUMMARY TABLE -->
                    <?php if (!empty($summary)): ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-success">
                                <h6 class="m-0 font-weight-bold text-white">
                                    <i class="fas fa-chart-bar"></i> Statistik Kehadiran Per Karyawan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    Persentase dihitung dari <strong><?= $total_days ?> hari</strong> periode
                                    <strong><?= date('d M Y', strtotime($start_date)) ?> s/d
                                        <?= date('d M Y', strtotime($end_date)) ?></strong>
                                    — dihitung dari absen IN
                                </div>
                                <div class="chart-container">
                                    <canvas id="attendanceChart"></canvas>
                                </div>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Karyawan</th>
                                                <th>NIK</th>
                                                <th>Level</th>
                                                <th class="text-center">Hari Hadir (IN)</th>
                                                <th class="text-center">Hari Pulang (OUT)</th>
                                                <th class="text-center">Persentase Kehadiran</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sorted_summary = (array) $summary;
                                            usort($sorted_summary, fn($a, $b) => $b->count_in - $a->count_in);
                                            $no = 1;
                                            foreach ($sorted_summary as $stat):
                                                $pct = $total_days > 0 ? ($stat->count_in / $total_days) * 100 : 0;
                                                if ($pct >= 90)
                                                    $pc = 'bg-success';
                                                elseif ($pct >= 75)
                                                    $pc = 'bg-info';
                                                elseif ($pct >= 50)
                                                    $pc = 'bg-warning';
                                                else
                                                    $pc = 'bg-danger';
                                                ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><strong><?= $stat->user_nama ?></strong></td>
                                                    <td><?= $stat->user_nik ?></td>
                                                    <td><span
                                                            class="badge badge-info"><?= ucwords(str_replace('_', ' ', $stat->user_level)) ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-success badge-pill"><?= $stat->count_in ?> /
                                                            <?= $total_days ?> hari</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-danger badge-pill"><?= $stat->count_out ?>
                                                            hari</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="progress" style="height:20px;">
                                                            <div class="progress-bar <?= $pc ?>" role="progressbar"
                                                                style="width:<?= min($pct, 100) ?>%">
                                                                <?= number_format($pct, 1) ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong>Keterangan:</strong>
                                        <span class="badge badge-success ml-2">≥ 90% Sangat Baik</span>
                                        <span class="badge badge-info ml-1">75-89% Baik</span>
                                        <span class="badge badge-warning ml-1">50-74% Cukup</span>
                                        <span class="badge badge-danger ml-1">&lt; 50% Kurang</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                    <!-- FILTER -->
                    <div class="card filter-card shadow mb-4">
                        <div class="card-body">
                            <h5 class="mb-3 text-white"><i class="fas fa-filter"></i> Filter Laporan</h5>
                            <form method="GET" action="<?= base_url('absensi/laporan') ?>" id="filterForm">
                                <div class="row g-2">
                                    <div class="col-md-2">
                                        <label class="text-white small">Tanggal Mulai</label>
                                        <input type="date" name="start_date" class="form-control form-control-sm"
                                            value="<?= $start_date ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="text-white small">Tanggal Akhir</label>
                                        <input type="date" name="end_date" class="form-control form-control-sm"
                                            value="<?= $end_date ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="text-white small">Tipe</label>
                                        <select name="tipe" class="form-control form-control-sm" id="filterTipe">
                                            <option value="">-- Semua --</option>
                                            <option value="in" <?= ($selected_tipe ?? '') === 'in' ? 'selected' : '' ?>>IN
                                            </option>
                                            <option value="out" <?= ($selected_tipe ?? '') === 'out' ? 'selected' : '' ?>>
                                                OUT</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="text-white small"><i class="fas fa-layer-group"></i> Group</label>
                                        <select name="group" class="form-control form-control-sm" id="filterGroup"
                                            <?= ($locked_group && count($allowed_groups ?? []) <= 1) ? 'disabled' : '' ?>>
                                            <option value="">-- Semua Group --</option>
                                            <?php
                                            $all_groups = ['Yamazaki Staff', 'Admin TSC', 'Operasional TSC', 'TSF Staff', 'Sinar Boga Staff', 'Rorotan Staff'];
                                            $show_groups = ($allowed_groups === null) ? $all_groups : $allowed_groups;
                                            foreach ($show_groups as $g):
                                                ?>
                                                <option value="<?= $g ?>" <?= ($selected_group ?? '') == $g ? 'selected' : '' ?>><?= $g ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <?php if ($is_admin): ?>
                                        <div class="col-md-2">
                                            <label class="text-white small"><i class="fas fa-user"></i> Karyawan</label>
                                            <select name="user_id" class="form-control form-control-sm" id="filterUser">
                                                <option value="">-- Semua --</option>
                                                <?php foreach ($users as $u): ?>
                                                    <option value="<?= $u->id ?>" <?= ($selected_user_id ?? '') == $u->id ? 'selected' : '' ?>>
                                                        <?= $u->nama ?> (<?= $u->nik ?>)
                                                    </option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                    <?php endif ?>
                                    <div class="col-md-2">
                                        <label class="text-white small d-block">&nbsp;</label>
                                        <button type="submit" class="btn btn-light btn-sm w-100">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- DATA TABLE -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-white">
                                <i class="fas fa-table"></i> Data Absensi
                            </h6>
                            <small class="text-white-50" id="table-info-text"></small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th width="40" class="no-sort">No</th>
                                            <th width="100">Tipe</th>
                                            <th width="90" class="no-sort">Foto</th>
                                            <th>Nama</th>
                                            <th width="100">NIK</th>
                                            <th width="100">Tanggal</th>
                                            <th width="80">Waktu</th>
                                            <th>Alamat</th>
                                            <th width="80" class="no-sort">Lokasi</th>
                                            <th width="120" class="no-sort">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- Photo Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-image"></i> Foto Absensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" alt="Foto" class="modal-photo" id="modal-photo">
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {

            setTimeout(() => $('.alert').fadeOut('slow'), 5000);

            <?php if ($is_admin): ?>
                $('#filterUser').on('change', function () {
                    const hasUser = $(this).val() !== '';
                    $('#filterGroup').prop('disabled', hasUser);
                    if (hasUser) $('#filterGroup').val('');
                });
                if ($('#filterUser').val()) $('#filterGroup').prop('disabled', true);
            <?php endif ?>

            const ajaxParams = {
                start_date: '<?= $start_date ?>',
                end_date: '<?= $end_date ?>',
                group: '<?= addslashes($selected_group ?? '') ?>',
                user_id: '<?= $selected_user_id ?? '' ?>',
                tipe: '<?= addslashes($this->input->get('tipe') ?? '') ?>'
            };

            const table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' },
                ajax: {
                    url: '<?= base_url('absensi/laporan_data') ?>',
                    type: 'GET',
                    data: d => Object.assign(d, ajaxParams),
                    error: function (xhr, err) {
                        console.error('DataTables ajax error:', err);
                    }
                },
                // Default sort: Tanggal DESC (index 5), Waktu DESC (index 6)
                order: [[5, 'desc'], [6, 'desc']],
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                columns: [
                    // 0 No — tidak bisa di-sort
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1
                    },
                    // 1 Tipe — bisa di-sort
                    {
                        data: null,
                        className: 'text-center',
                        render: r => {
                            if (r.tipe === 'out' && r.is_auto_out == 1) {
                                return `<span class="badge-auto-out"><i class="fas fa-robot"></i> AUTO OUT</span>`;
                            }
                            if (r.tipe === 'out') return `<span class="badge-out">OUT</span>`;
                            return `<span class="badge-in">IN</span>`;
                        }
                    },
                    // 2 Foto — tidak bisa di-sort
                    {
                        data: 'photo_url',
                        orderable: false,
                        className: 'text-center',
                        render: u => `<img src="${u}" class="record-photo img-thumbnail" onclick="showPhoto('${u}')">`
                    },
                    // 3 Nama — bisa di-sort
                    {
                        data: null,
                        render: r => `<strong>${r.user_nama}</strong><br><small class="text-muted">${ucwords(r.user_level)}</small>`
                    },
                    // 4 NIK — bisa di-sort
                    { data: 'user_nik' },
                    // 5 Tanggal — bisa di-sort
                    { data: 'tanggal_fmt' },
                    // 6 Waktu — bisa di-sort
                    {
                        data: 'waktu',
                        className: 'text-center',
                        render: w => `<span class="badge badge-info">${w}</span>`
                    },
                    // 7 Alamat — bisa di-sort
                    {
                        data: 'alamat',
                        render: a => `<small>${a ?? '-'}</small>`
                    },
                    // 8 Lokasi — tidak bisa di-sort
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: r => {
                            if (r.is_auto_out == 1) {
                                return `<span class="text-muted small"><i class="fas fa-robot"></i> Auto</span>`;
                            }
                            return `<a href="${r.maps_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-map-marker-alt"></i> Maps
                                    </a>`;
                        }
                    },
                    // 9 Aksi — tidak bisa di-sort
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: (d, t, r) => `
                            <div class="btn-group-vertical">
                                <a href="<?= base_url('absensi/detail/') ?>${r.id}" class="btn btn-sm btn-info mb-1">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <a href="<?= base_url('absensi/edit/') ?>${r.id}" class="btn btn-sm btn-warning mb-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-sm btn-danger btn-delete" data-id="${r.id}" data-nama="${r.user_nama}">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>`
                    }
                ],
                drawCallback: function (settings) {
                    $('.btn-delete').off('click').on('click', function () {
                        const id = $(this).data('id');
                        const nama = $(this).data('nama');
                        Swal.fire({
                            title: 'Hapus Data Absensi?',
                            html: `Data absensi dari <strong>${nama}</strong> akan dihapus permanen!`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then(result => {
                            if (result.isConfirmed)
                                window.location.href = '<?= base_url('absensi/delete/') ?>' + id;
                        });
                    });

                    const api = this.api();
                    const info = api.page.info();
                    $('#table-info-text').text(
                        info.recordsFiltered > 0
                            ? `Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsFiltered} data`
                            : ''
                    );
                }
            });

            // Export Excel
            $('#btn-export').on('click', function () {
                window.location.href = '<?= base_url('absensi/export_excel') ?>?' +
                    new URLSearchParams(window.location.search).toString();
            });

            // Today count
            fetch('<?= base_url('absensi/laporan_data') ?>?start_date=<?= date('Y-m-d') ?>&end_date=<?= date('Y-m-d') ?>&tipe=in&length=1&start=0&draw=1')
                .then(r => r.json())
                .then(d => { $('#today-count').text(d.recordsFiltered ?? 0); })
                .catch(() => $('#today-count').text('–'));

        }); // end ready

        function showPhoto(url) {
            $('#modal-photo').attr('src', url);
            new bootstrap.Modal(document.getElementById('photoModal')).show();
        }

        function ucwords(str) {
            return (str ?? '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        }
    </script>

    <!-- Chart -->
    <?php if (!empty($summary)): ?>
        <script>
            (function () {
                const totalDays = <?= $total_days ?>;
                const summaryData = <?php
                $chart_data = [];
                $sorted = (array) $summary;
                usort($sorted, fn($a, $b) => $b->count_in - $a->count_in);
                foreach ($sorted as $s) {
                    $chart_data[] = ['nama' => $s->user_nama, 'count' => (int) $s->count_in];
                }
                echo json_encode($chart_data);
                ?>;

                const labels = summaryData.map(i => i.nama);
                const counts = summaryData.map(i => i.count);
                const bgColors = counts.map(c => {
                    const p = (c / totalDays) * 100;
                    if (p >= 90) return 'rgba(28,200,138,0.8)';
                    if (p >= 75) return 'rgba(54,185,204,0.8)';
                    if (p >= 50) return 'rgba(246,194,62,0.8)';
                    return 'rgba(231,74,59,0.8)';
                });

                new Chart(document.getElementById('attendanceChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Hari Hadir (IN)',
                            data: counts,
                            backgroundColor: bgColors,
                            borderColor: bgColors.map(c => c.replace('0.8', '1')),
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: 'Grafik Kehadiran — <?= date('d M Y', strtotime($start_date)) ?> s/d <?= date('d M Y', strtotime($end_date)) ?>',
                                font: { size: 16, weight: 'bold' }
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => [
                                        'Hadir: ' + ctx.parsed.y + ' hari',
                                        'Dari: ' + totalDays + ' hari',
                                        'Persentase: ' + ((ctx.parsed.y / totalDays) * 100).toFixed(1) + '%'
                                    ]
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: totalDays,
                                ticks: { stepSize: 1, callback: v => v + ' hari' }
                            },
                            x: { grid: { display: false } }
                        },
                        animation: { duration: 1500, easing: 'easeInOutQuart' }
                    }
                });
            })();
        </script>
    <?php endif ?>

</body>

</html>
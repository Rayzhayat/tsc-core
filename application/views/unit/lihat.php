<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .badge-status {
            font-size: .75rem;
            padding: 4px 9px;
            font-weight: 600;
        }

        .status-aktif {
            background: #1cc88a;
            color: #fff;
        }

        .status-maintenance {
            background: #f6c23e;
            color: #fff;
        }

        .status-rusak {
            background: #e74a3b;
            color: #fff;
        }

        .status-dijual {
            background: #858796;
            color: #fff;
        }

        .status-nonaktif {
            background: #5a5c69;
            color: #fff;
        }

        .expiry-warning {
            font-size: .7rem;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            margin-top: 2px;
        }

        .expiry-ok {
            background: #d4edda;
            color: #155724;
        }

        .expiry-soon {
            background: #fff3cd;
            color: #856404;
        }

        .expiry-expired {
            background: #f8d7da;
            color: #721c24;
        }

        .km-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: .75rem;
            font-weight: 600;
        }

        .stat-card {
            border-left: 4px solid;
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, .1) !important;
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
                    $level = $this->session->userdata('login')['user_level'] ?? '';
                    $can_edit = in_array($level, ['superadmin', 'admin_operational', 'fleet_staff']);
                    $units = $units ?? [];
                    $total = count($units);
                    $aktif = count(array_filter($units, fn($u) => ($u->status_unit ?? 'aktif') == 'aktif'));
                    $maintenance = count(array_filter($units, fn($u) => ($u->status_unit ?? '') == 'maintenance'));
                    $rusak = count(array_filter($units, fn($u) => ($u->status_unit ?? '') == 'rusak'));
                    ?>

                    <!-- PAGE HEADER -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-truck text-primary me-2"></i><?= $title ?>
                            </h2>
                            <small class="text-muted">Kelola armada kendaraan &amp; dokumen unit</small>
                        </div>
                        <?php if ($can_edit): ?>
                            <div class="d-flex gap-2">
                                <a href="<?= base_url('unit/export') ?>" class="btn btn-danger btn-sm">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </a>
                                <a href="<?= base_url('unit/export_excel') ?>" class="btn btn-success btn-sm"
                                    style="background:#1d6f42;border-color:#1d6f42;">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </a>
                                <a href="<?= base_url('unit/tambah') ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Tambah Unit
                                </a>
                            </div>
                        <?php endif ?>
                    </div>

                    <!-- FLASH -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-1"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- STAT CARDS -->
                    <div class="row g-3 mb-3">
                        <?php
                        $stats = [
                            ['label' => 'Total Unit', 'val' => $total, 'color' => '#4e73df', 'icon' => 'fas fa-truck'],
                            ['label' => 'Unit Aktif', 'val' => $aktif, 'color' => '#1cc88a', 'icon' => 'fas fa-check-circle'],
                            ['label' => 'Maintenance', 'val' => $maintenance, 'color' => '#f6c23e', 'icon' => 'fas fa-wrench'],
                            ['label' => 'Unit Rusak', 'val' => $rusak, 'color' => '#e74a3b', 'icon' => 'fas fa-exclamation-triangle'],
                        ];
                        foreach ($stats as $s): ?>
                            <div class="col-xl-3 col-md-6">
                                <div class="card stat-card shadow-sm h-100" style="border-left-color:<?= $s['color'] ?>">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold fs-4 lh-1"><?= $s['val'] ?></div>
                                                <div class="text-muted"
                                                    style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">
                                                    <?= $s['label'] ?></div>
                                            </div>
                                            <i class="<?= $s['icon'] ?> fa-2x opacity-25"
                                                style="color:<?= $s['color'] ?>"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <!-- TABLE CARD -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header py-2 bg-primary text-white">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-list me-1"></i> Daftar Unit Kendaraan
                                    <span class="badge bg-white text-primary ms-2 fw-normal" id="unitCounter">
                                        <?= $total ?> Unit
                                    </span>
                                </h6>
                                <div class="input-group input-group-sm" style="max-width:280px">
                                    <span class="input-group-text bg-white border-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" id="searchInput" class="form-control border-0"
                                        placeholder="Cari no polisi, tipe, status..." autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <?php if (empty($units)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    <em>Tidak ada data unit kendaraan.</em>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0" id="dataTable"
                                        style="font-size:.83rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width:40px">No</th>
                                                <th style="width:100px">No Polisi</th>
                                                <th style="width:90px">Tipe Unit</th>
                                                <th style="width:80px">Tipe Box</th>
                                                <th class="text-center" style="width:55px">Tahun</th>
                                                <th style="width:110px">Dimensi (PxLxT)</th>
                                                <th class="text-center" style="width:80px">Status</th>
                                                <th style="width:110px">STNK / KIR</th>
                                                <th class="text-center" style="width:100px">Odometer</th>
                                                <th style="width:80px">BBM</th>
                                                <th class="text-center" style="width:80px">Foto Dok</th>
                                                <?php if ($can_edit): ?>
                                                    <th class="text-center" style="width:110px">Aksi</th>
                                                <?php endif ?>
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                            <?php $no = 1;
                                            foreach ($units as $unit):
                                                $status = $unit->status_unit ?? 'aktif';
                                                ?>
                                                <tr>
                                                    <td class="text-center text-muted"><?= $no++ ?></td>

                                                    <td>
                                                        <strong class="text-primary">
                                                            <?= htmlspecialchars(strtoupper($unit->no_polisi ?? '')) ?>
                                                        </strong>
                                                        <?php if (!empty($unit->kapasitas_kg)): ?>
                                                            <div class="text-muted" style="font-size:.75rem">
                                                                Cap: <?= number_format($unit->kapasitas_kg) ?> kg
                                                            </div>
                                                        <?php endif ?>
                                                    </td>

                                                    <td><?= htmlspecialchars($unit->tipe_unit ?? '—') ?></td>
                                                    <td><?= htmlspecialchars($unit->tipe_box ?? '—') ?></td>
                                                    <td class="text-center"><?= $unit->tahun_unit ?? '—' ?></td>

                                                    <td>
                                                        <?php
                                                        $p = $unit->panjang ?? 0;
                                                        $l = $unit->lebar ?? 0;
                                                        $t = $unit->tinggi ?? 0;
                                                        if ($p && $l && $t):
                                                            $cbm = $p * $l * $t; ?>
                                                            <div style="font-size:.78rem"><?= "$p × $l × $t" ?> m</div>
                                                            <div class="text-muted" style="font-size:.72rem">CBM:
                                                                <?= number_format($cbm, 2) ?></div>
                                                            <div class="text-info" style="font-size:.72rem">
                                                                <?= $unit->tonase ?? 0 ?> Ton</div>
                                                        <?php else: ?>
                                                            <span class="text-muted">&mdash;</span>
                                                        <?php endif ?>
                                                    </td>

                                                    <td class="text-center">
                                                        <span class="badge badge-status status-<?= $status ?>">
                                                            <?= strtoupper($status) ?>
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <?php
                                                        foreach (['stnk' => $unit->stnk_expired ?? '', 'kir' => $unit->kir_expired ?? ''] as $doc => $exp):
                                                            if (!empty($exp)):
                                                                $diff = (strtotime($exp) - time()) / 86400;
                                                                if ($diff <= 0) {
                                                                    $cls = 'expiry-expired';
                                                                    $txt = '❌ Expired';
                                                                } elseif ($diff < 30) {
                                                                    $cls = 'expiry-soon';
                                                                    $txt = '⚠️ ' . ceil($diff) . ' hari';
                                                                } else {
                                                                    $cls = 'expiry-ok';
                                                                    $txt = '✓ ' . date('d/m/Y', strtotime($exp));
                                                                }
                                                                echo "<div><small class='expiry-warning $cls'>" . strtoupper($doc) . ": $txt</small></div>";
                                                            else:
                                                                echo "<div><small class='text-muted'>" . strtoupper($doc) . ": —</small></div>";
                                                            endif;
                                                        endforeach ?>
                                                    </td>

                                                    <td class="text-center">
                                                        <?php if (!empty($unit->current_km)): ?>
                                                            <span class="km-badge">
                                                                <i
                                                                    class="fas fa-tachometer-alt me-1"></i><?= number_format($unit->current_km) ?>
                                                                km
                                                            </span>
                                                            <?php if (!empty($unit->next_service_km)): ?>
                                                                <div class="text-muted" style="font-size:.72rem">
                                                                    Servis: <?= number_format($unit->next_service_km) ?> km
                                                                </div>
                                                            <?php endif ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">&mdash;</span>
                                                        <?php endif ?>
                                                    </td>

                                                    <td>
                                                        <?php if (!empty($unit->bahan_bakar)): ?>
                                                            <strong
                                                                style="font-size:.8rem"><?= strtoupper($unit->bahan_bakar) ?></strong>
                                                            <?php if (!empty($unit->konsumsi_bbm)): ?>
                                                                <div class="text-muted" style="font-size:.72rem">
                                                                    <?= $unit->konsumsi_bbm ?> km/L</div>
                                                            <?php endif ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">&mdash;</span>
                                                        <?php endif ?>
                                                    </td>

                                                    <td class="text-center">
                                                        <?php
                                                        $docs = [
                                                            ['foto_stnk', 'uploads/stnk/', 'STNK'],
                                                            ['foto_kir', 'uploads/kir/', 'KIR'],
                                                            ['foto_barcode_solar', 'uploads/barcode_solar/', 'Barcode Solar'],
                                                        ];
                                                        $has_doc = false;
                                                        foreach ($docs as [$field, $path, $label]):
                                                            if (!empty($unit->$field)):
                                                                $has_doc = true; ?>
                                                                <a href="<?= base_url($path . $unit->$field) ?>" target="_blank"
                                                                    title="Lihat <?= $label ?>">
                                                                    <img src="<?= base_url($path . $unit->$field) ?>" alt="<?= $label ?>"
                                                                        width="32" class="img-thumbnail mb-1">
                                                                </a>
                                                            <?php endif ?>
                                                        <?php endforeach ?>
                                                        <?php if (!$has_doc): ?>
                                                            <span class="text-muted">&mdash;</span>
                                                        <?php endif ?>
                                                    </td>

                                                    <?php if ($can_edit): ?>
                                                        <td class="text-center">
                                                            <a href="<?= base_url('unit/detail/' . $unit->id) ?>"
                                                                class="btn btn-info btn-sm" title="Detail">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="<?= base_url('unit/ubah/' . ($unit->id ?? '')) ?>"
                                                                class="btn btn-success btn-sm" title="Ubah">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if (in_array($level, ['superadmin', 'admin_operational', 'fleet_staff'])): ?>
                                                                <a href="<?= base_url('unit/hapus/' . ($unit->id ?? '')) ?>"
                                                                    class="btn btn-danger btn-sm"
                                                                    onclick="return confirm('Yakin hapus unit <?= htmlspecialchars($unit->no_polisi ?? '') ?>?')"
                                                                    title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-secondary btn-sm" disabled
                                                                    title="Tidak ada akses hapus">
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
            let searchTimeout;

            $('#searchInput').on('keyup', function () {
                clearTimeout(searchTimeout);
                const keyword = $(this).val().trim();
                searchTimeout = setTimeout(function () {
                    $.ajax({
                        url: '<?= base_url('unit/search') ?>',
                        method: 'POST',
                        data: { keyword },
                        success: function (response) {
                            $('#tableBody').html(response);
                            let count = $('#tableBody tr').length;
                            if ($('#tableBody tr td[colspan]').length > 0) count = 0;
                            $('#unitCounter').text(count + ' Unit');
                        },
                        error: function () { alert('Gagal mencari data!'); }
                    });
                }, 400);
            });

            $(document).on('keyup', function (e) {
                if (e.key === 'Escape') { $('#searchInput').val('').trigger('keyup'); }
            });

            if ($('#dataTable').length) {
                $('#dataTable').DataTable({
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                    columnDefs: [{ orderable: false, targets: <?= $can_edit ? '[0, 10, 11]' : '[0, 10]' ?> }],
                    order: [[1, 'asc']],
                    pageLength: 25,
                    destroy: true,
                    responsive: true
                });
            }

            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
            $('[data-bs-toggle="tooltip"], [title]').tooltip();
        });
    </script>
</body>

</html>
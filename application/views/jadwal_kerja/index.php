<!DOCTYPE html>
<html lang="id">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .page-hero {
            background: linear-gradient(135deg, #1a1f3a 0%, #2d3561 60%, #1e3a5f 100%);
            border-radius: 16px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .page-hero::after {
            content: '';
            position: absolute;
            right: -40px;
            top: -40px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .page-hero h1 {
            font-size: 1.3rem;
            font-weight: 800;
            margin: 0;
        }

        .page-hero p {
            opacity: 0.6;
            font-size: 0.82rem;
            margin: 4px 0 0;
        }

        .sec-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .sec-head h6 {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6c757d;
            margin: 0;
        }

        .sec-head::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e3e6f0;
        }

        .jadwal-card {
            border: 2px solid #e3e6f0;
            border-radius: 12px;
            padding: 16px;
            transition: all 0.2s;
            height: 100%;
        }

        .jadwal-card:hover {
            border-color: #4e73df;
            box-shadow: 0 4px 16px rgba(78, 115, 223, 0.12);
        }

        .jadwal-card .jc-name {
            font-weight: 800;
            font-size: 0.95rem;
            color: #3a3b45;
        }

        .jadwal-card .jc-sub {
            font-size: 0.75rem;
            color: #b7b9cc;
            margin-top: 2px;
        }

        .hari-dots {
            display: flex;
            gap: 4px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .hari-dot {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: 700;
        }

        .hari-dot.aktif {
            background: #4e73df;
            color: #fff;
        }

        .hari-dot.libur {
            background: #f0f2f8;
            color: #b7b9cc;
        }

        .map-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .map-table thead th {
            background: #f8f9fc;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #b7b9cc;
            padding: 10px 14px;
            border-bottom: 2px solid #e3e6f0;
        }

        .map-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid #f0f2f8;
            font-size: 0.88rem;
            vertical-align: middle;
        }

        .map-table tbody tr:hover {
            background: #f8f9fc;
        }

        /* Hari off table */
        .off-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .off-table thead th {
            background: #f8f9fc;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #b7b9cc;
            padding: 10px 14px;
            border-bottom: 2px solid #e3e6f0;
        }

        .off-table tbody td {
            padding: 10px 14px;
            border-bottom: 1px solid #f0f2f8;
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .off-table tbody tr:hover {
            background: #fef9f9;
        }

        .scope-badge-global {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 700;
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .scope-badge-personal {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 700;
            background: #e8f4fd;
            color: #1565c0;
            border: 1px solid #90caf9;
        }

        .gol-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 0.8rem;
            background: #eef0fd;
            color: #4e73df;
            border: 1px solid #d0d8f5;
        }

        .modal-header-dark {
            background: linear-gradient(135deg, #1a1f3a, #2d3561);
            color: #fff;
            border-radius: 12px 12px 0 0;
        }

        .hari-check-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
        }

        .hari-check-item {
            position: relative;
        }

        .hari-check-item input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .hari-check-item label {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 700;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.15s;
        }

        .hari-check-item input:checked+label {
            background: #4e73df;
            border-color: #4e73df;
            color: #fff;
        }

        .hari-check-item label:hover {
            border-color: #4e73df;
            color: #4e73df;
        }

        .gol-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 6px;
            margin-top: 8px;
        }

        .gol-item {
            position: relative;
        }

        .gol-item input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .gol-item label {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 36px;
            border: 2px solid #e3e6f0;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.15s;
        }

        .gol-item input[type="radio"]:checked+label {
            background: #4e73df;
            border-color: #4e73df;
            color: #fff;
        }

        .gol-item label:hover {
            border-color: #4e73df;
            color: #4e73df;
        }

        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #b7b9cc;
        }

        .empty-state i {
            font-size: 2.5rem;
            opacity: 0.3;
            display: block;
            margin-bottom: 12px;
        }

        .filter-bar {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        /* Scope toggle */
        .scope-toggle {
            display: flex;
            gap: 0;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e3e6f0;
        }

        .scope-toggle label {
            flex: 1;
            text-align: center;
            padding: 8px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
            color: #6c757d;
            margin: 0;
            border: none;
        }

        .scope-toggle input[type="radio"] {
            display: none;
        }

        .scope-toggle input[type="radio"]:checked+label {
            background: #4e73df;
            color: #fff;
        }

        .scope-toggle label:hover {
            background: #f0f2f8;
        }

        .scope-toggle input[type="radio"]:checked+label:hover {
            background: #3a5fc7;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <?php
                    $hari_names = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                    $hari_full = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                    $bulan_names = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    $all_golongan = ['1A', '1B', '1C', '2A', '2B', '2C', '2D', '3A', '3B', '3C', '3D', '4A', '4B', '4C', '4D'];

                    // Pisah count global vs personal untuk info
                    $count_global = count(array_filter($hari_off_list, fn($o) => is_null($o->user_id)));
                    $count_personal = count(array_filter($hari_off_list, fn($o) => !is_null($o->user_id)));
                    ?>

                    <!-- Hero -->
                    <div class="page-hero">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h1><i class="fas fa-calendar-alt me-2" style="opacity:0.7;"></i><?= $title ?></h1>
                                <p>Kelola jadwal kerja per golongan dan hari off operasional</p>
                            </div>
                            <a href="<?= base_url('pengguna') ?>" class="btn btn-sm"
                                style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2);">
                                <i class="fas fa-users"></i> Master Karyawan
                            </a>
                        </div>
                    </div>

                    <!-- Flash -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <div class="row g-4">

                        <!-- KOLOM KIRI: Jadwal + Mapping -->
                        <div class="col-lg-7">

                            <!-- Master Jadwal -->
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="sec-head" style="flex:1;margin-bottom:0;">
                                            <h6><i class="fas fa-clock me-1"></i> Master Jadwal Kerja</h6>
                                        </div>
                                        <button class="btn btn-primary btn-sm ms-3" data-bs-toggle="modal"
                                            data-bs-target="#modalTambahJadwal">
                                            <i class="fas fa-plus"></i> Tambah Jadwal
                                        </button>
                                    </div>
                                    <div class="row g-3">
                                        <?php foreach ($jadwal_list as $j):
                                            $hari_aktif = explode(',', $j->hari_kerja);
                                            ?>
                                            <div class="col-md-4">
                                                <div class="jadwal-card">
                                                    <div class="d-flex align-items-start justify-content-between">
                                                        <div>
                                                            <div class="jc-name"><?= $j->nama_jadwal ?></div>
                                                            <div class="jc-sub"><?= $j->keterangan ?? '—' ?></div>
                                                        </div>
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-xs btn-outline-warning"
                                                                style="font-size:0.65rem;padding:3px 7px;"
                                                                onclick="openEditJadwal(<?= $j->id ?>, '<?= addslashes($j->nama_jadwal) ?>', '<?= $j->hari_kerja ?>', '<?= addslashes($j->keterangan ?? '') ?>')">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <a href="<?= base_url('jadwal_kerja/hapus_jadwal/' . $j->id) ?>"
                                                                class="btn btn-xs btn-outline-danger btn-hapus-jadwal"
                                                                style="font-size:0.65rem;padding:3px 7px;"
                                                                data-nama="<?= $j->nama_jadwal ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="hari-dots">
                                                        <?php for ($h = 1; $h <= 7; $h++): ?>
                                                            <div
                                                                class="hari-dot <?= in_array((string) $h, $hari_aktif) ? 'aktif' : 'libur' ?>">
                                                                <?= $hari_names[$h - 1] ?>
                                                            </div>
                                                        <?php endfor ?>
                                                    </div>
                                                    <div class="mt-2" style="font-size:0.72rem;color:#b7b9cc;">
                                                        <?= count($hari_aktif) ?> hari kerja/minggu</div>
                                                </div>
                                            </div>
                                        <?php endforeach ?>
                                        <?php if (empty($jadwal_list)): ?>
                                            <div class="col-12">
                                                <div class="empty-state"><i class="fas fa-calendar-times"></i>Belum ada
                                                    jadwal kerja</div>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Mapping Golongan -->
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="sec-head" style="flex:1;margin-bottom:0;">
                                            <h6><i class="fas fa-link me-1"></i> Mapping Golongan → Jadwal</h6>
                                        </div>
                                        <button class="btn btn-success btn-sm ms-3" data-bs-toggle="modal"
                                            data-bs-target="#modalMapping">
                                            <i class="fas fa-plus"></i> Set Mapping
                                        </button>
                                    </div>
                                    <?php if (empty($mapping_list)): ?>
                                        <div class="empty-state"><i class="fas fa-link"></i>Belum ada mapping.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="map-table">
                                                <thead>
                                                    <tr>
                                                        <th>Golongan</th>
                                                        <th>Jadwal Kerja</th>
                                                        <th>Hari Kerja</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($mapping_list as $m):
                                                        $hari_m = explode(',', $m->hari_kerja);
                                                        ?>
                                                        <tr>
                                                            <td><span class="gol-badge"><?= $m->golongan ?></span></td>
                                                            <td class="fw-bold"><?= $m->nama_jadwal ?></td>
                                                            <td>
                                                                <div class="hari-dots" style="gap:3px;">
                                                                    <?php for ($h = 1; $h <= 7; $h++): ?>
                                                                        <div class="hari-dot <?= in_array((string) $h, $hari_m) ? 'aktif' : 'libur' ?>"
                                                                            style="width:26px;height:26px;font-size:0.58rem;">
                                                                            <?= $hari_names[$h - 1] ?></div>
                                                                    <?php endfor ?>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <a href="<?= base_url('jadwal_kerja/hapus_mapping/' . urlencode($m->golongan)) ?>"
                                                                    class="btn btn-xs btn-outline-danger btn-hapus-mapping"
                                                                    style="font-size:0.65rem;padding:3px 8px;"
                                                                    data-gol="<?= $m->golongan ?>">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif ?>

                                    <?php
                                    $mapped = array_column($mapping_list, 'golongan');
                                    $unmapped = array_filter($golongan_list, fn($g) => !in_array($g->golongan, $mapped));
                                    if (!empty($golongan_list)):
                                        ?>
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="small text-muted mb-2"><i class="fas fa-info-circle"></i> Golongan
                                                belum di-mapping (default: Senin–Sabtu):</div>
                                            <?php if (empty($unmapped)): ?>
                                                <span class="badge bg-success"><i class="fas fa-check"></i> Semua sudah
                                                    di-mapping</span>
                                            <?php else: ?>
                                                <?php foreach ($unmapped as $ug): ?>
                                                    <span class="gol-badge me-1" style="opacity:0.5;"><?= $ug->golongan ?></span>
                                                <?php endforeach ?>
                                            <?php endif ?>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM KANAN: Hari Off -->
                        <div class="col-lg-5">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <div class="sec-head" style="margin-bottom:4px;">
                                                <h6><i class="fas fa-ban me-1 text-danger"></i> Hari Off Operasional
                                                </h6>
                                            </div>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <small class="text-muted">
                                                    <span class="scope-badge-global"><i class="fas fa-users"></i>
                                                        Global</span>
                                                    <?= $count_global ?> hari off
                                                </small>
                                                <small class="text-muted">
                                                    <span class="scope-badge-personal"><i class="fas fa-user"></i> Per
                                                        Karyawan</span>
                                                    <?= $count_personal ?> hari off
                                                </small>
                                            </div>
                                        </div>
                                        <button class="btn btn-danger btn-sm ms-2" data-bs-toggle="modal"
                                            data-bs-target="#modalHariOff">
                                            <i class="fas fa-plus"></i> Tambah
                                        </button>
                                    </div>

                                    <!-- Filter -->
                                    <form method="GET" action="<?= base_url('jadwal_kerja') ?>"
                                        class="filter-bar mb-3 mt-2">
                                        <select name="bulan" class="form-select form-select-sm" style="width:130px;">
                                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                                <option value="<?= $m ?>" <?= $filter_bulan == $m ? 'selected' : '' ?>>
                                                    <?= $bulan_names[$m] ?></option>
                                            <?php endfor ?>
                                        </select>
                                        <select name="tahun" class="form-select form-select-sm" style="width:90px;">
                                            <?php for ($y = date('Y') - 2; $y <= date('Y') + 2; $y++): ?>
                                                <option value="<?= $y ?>" <?= $filter_tahun == $y ? 'selected' : '' ?>>
                                                    <?= $y ?></option>
                                            <?php endfor ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                                    </form>

                                    <?php if (empty($hari_off_list)): ?>
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-check"></i>
                                            Tidak ada hari off di <?= $bulan_names[(int) $filter_bulan] . ' ' . $filter_tahun ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
                                            <table class="off-table">
                                                <thead>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>Berlaku Untuk</th>
                                                        <th>Keterangan</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($hari_off_list as $off): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="fw-bold" style="font-size:0.85rem;">
                                                                    <?= date('d M Y', strtotime($off->tanggal)) ?>
                                                                </div>
                                                                <div class="text-muted" style="font-size:0.7rem;">
                                                                    <?= $hari_full[date('N', strtotime($off->tanggal)) - 1] ?>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <?php if (is_null($off->user_id)): ?>
                                                                    <span class="scope-badge-global">
                                                                        <i class="fas fa-users"></i> Semua Ops
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="scope-badge-personal">
                                                                        <i class="fas fa-user"></i>
                                                                        <?= $off->target_nama ?? '—' ?>
                                                                    </span>
                                                                    <div class="text-muted" style="font-size:0.68rem;">
                                                                        <?= $off->target_nik ?? '' ?></div>
                                                                <?php endif ?>
                                                            </td>
                                                            <td>
                                                                <div style="font-size:0.82rem;"><?= $off->keterangan ?></div>
                                                                <div class="text-muted" style="font-size:0.68rem;">
                                                                    Oleh: <?= $off->created_by_nama ?? '—' ?>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <a href="<?= base_url('jadwal_kerja/hapus_hari_off/' . $off->id) ?>"
                                                                    class="btn btn-xs btn-outline-danger btn-hapus-off"
                                                                    style="font-size:0.65rem;padding:3px 8px;"
                                                                    data-tgl="<?= date('d M Y', strtotime($off->tanggal)) ?>"
                                                                    data-scope="<?= is_null($off->user_id) ? 'global' : $off->target_nama ?>">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-muted small mt-2 text-end">
                                            <?= count($hari_off_list) ?> total hari off ·
                                            <?= $count_global ?> global · <?= $count_personal ?> per karyawan
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>

                    </div><!-- /row -->

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- MODAL: Tambah Jadwal -->
    <div class="modal fade" id="modalTambahJadwal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-header-dark">
                    <h5 class="modal-title text-white"><i class="fas fa-clock me-2"></i>Tambah Jadwal Kerja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= base_url('jadwal_kerja/tambah_jadwal') ?>">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Nama Jadwal <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jadwal" class="form-control"
                                placeholder="Contoh: Senin – Jumat" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">Hari Kerja <span class="text-danger">*</span></label>
                            <div class="hari-check-grid mt-2">
                                <?php foreach ($hari_names as $i => $hn): ?>
                                    <div class="hari-check-item">
                                        <input type="checkbox" name="hari_kerja[]" id="hc_<?= $i + 1 ?>" value="<?= $i + 1 ?>">
                                        <label for="hc_<?= $i + 1 ?>"><?= $hn ?></label>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="fw-bold">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control"
                                placeholder="Contoh: Libur Sabtu & Minggu">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Edit Jadwal -->
    <div class="modal fade" id="modalEditJadwal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-header-dark">
                    <h5 class="modal-title text-white"><i class="fas fa-edit me-2"></i>Ubah Jadwal Kerja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formEditJadwal" action="">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Nama Jadwal <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jadwal" id="edit_nama_jadwal" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">Hari Kerja <span class="text-danger">*</span></label>
                            <div class="hari-check-grid mt-2">
                                <?php foreach ($hari_names as $i => $hn): ?>
                                    <div class="hari-check-item">
                                        <input type="checkbox" name="hari_kerja[]" id="he_<?= $i + 1 ?>" value="<?= $i + 1 ?>">
                                        <label for="he_<?= $i + 1 ?>"><?= $hn ?></label>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="fw-bold">Keterangan</label>
                            <input type="text" name="keterangan" id="edit_keterangan" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Mapping -->
    <div class="modal fade" id="modalMapping" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-header-dark">
                    <h5 class="modal-title text-white"><i class="fas fa-link me-2"></i>Set Mapping Golongan → Jadwal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= base_url('jadwal_kerja/simpan_mapping') ?>">
                    <div class="modal-body">
                        <div class="alert alert-info small py-2 mb-3">
                            <i class="fas fa-info-circle"></i> Jika golongan sudah ada mapping-nya, akan otomatis
                            di-update.
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">Pilih Golongan <span class="text-danger">*</span></label>
                            <div class="gol-grid">
                                <?php foreach ($all_golongan as $g): ?>
                                    <div class="gol-item">
                                        <input type="radio" name="golongan" id="gol_map_<?= $g ?>" value="<?= $g ?>"
                                            required>
                                        <label for="gol_map_<?= $g ?>"><?= $g ?></label>
                                    </div>
                                <?php endforeach ?>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Atau ketik manual:</small>
                                <div class="input-group input-group-sm mt-1">
                                    <input type="text" id="gol_manual_input" class="form-control"
                                        placeholder="Contoh: 5A">
                                    <button type="button" class="btn btn-outline-secondary"
                                        id="btn_pakai_manual">Pakai</button>
                                </div>
                                <small id="gol_manual_preview" class="text-primary fw-bold mt-1 d-block"
                                    style="display:none!important;"></small>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="fw-bold">Jadwal Kerja <span class="text-danger">*</span></label>
                            <select name="jadwal_kerja_id" class="form-control" required>
                                <option value="">-- Pilih Jadwal --</option>
                                <?php foreach ($jadwal_list as $j): ?>
                                    <option value="<?= $j->id ?>">
                                        <?= $j->nama_jadwal ?>    <?= $j->keterangan ? ' (' . $j->keterangan . ')' : '' ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan
                            Mapping</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Hari Off — UPDATED dengan scope global/personal -->
    <div class="modal fade" id="modalHariOff" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-header-dark">
                    <h5 class="modal-title text-white"><i class="fas fa-ban me-2"></i>Tambah Hari Off Operasional</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= base_url('jadwal_kerja/tambah_hari_off') ?>">
                    <div class="modal-body">

                        <!-- Scope toggle -->
                        <div class="form-group mb-3">
                            <label class="fw-bold">Berlaku Untuk <span class="text-danger">*</span></label>
                            <div class="scope-toggle mt-2">
                                <input type="radio" name="scope" id="scope_global" value="global" checked>
                                <label for="scope_global"><i class="fas fa-users me-1"></i> Semua Ops Staff</label>
                                <input type="radio" name="scope" id="scope_personal" value="personal">
                                <label for="scope_personal"><i class="fas fa-user me-1"></i> Per Karyawan</label>
                            </div>
                        </div>

                        <!-- Dropdown karyawan (muncul kalau personal) -->
                        <div class="form-group mb-3" id="wrap_user_id" style="display:none;">
                            <label class="fw-bold">Pilih Karyawan Ops <span class="text-danger">*</span></label>
                            <select name="user_id" id="select_user_id" class="form-control">
                                <option value="">-- Pilih Karyawan --</option>
                                <?php foreach ($ops_staff_list as $ops): ?>
                                    <option value="<?= $ops->id ?>"><?= $ops->nama ?> <span
                                            class="text-muted">(<?= $ops->nik ?>)</span></option>
                                <?php endforeach ?>
                            </select>
                            <small class="text-muted">Hanya menampilkan karyawan dengan role
                                <strong>operational_staff</strong></small>
                        </div>

                        <div class="alert alert-warning small py-2 mb-3" id="alert_scope_global">
                            <i class="fas fa-exclamation-triangle"></i>
                            Hari off ini berlaku untuk <strong>semua operational_staff</strong>.
                        </div>
                        <div class="alert alert-info small py-2 mb-3" id="alert_scope_personal" style="display:none;">
                            <i class="fas fa-user"></i>
                            Hari off ini hanya berlaku untuk <strong>karyawan yang dipilih</strong>.
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-bold">Tanggal Off <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" required>
                            <small id="hari-preview" class="text-muted mt-1 d-block"></small>
                        </div>
                        <div class="form-group mb-0">
                            <label class="fw-bold">Keterangan <span class="text-danger">*</span></label>
                            <input type="text" name="keterangan" class="form-control"
                                placeholder="Contoh: Sistem maintenance, Libur bersama, dll" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-ban"></i> Tambah Hari Off</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {

            // ── Edit jadwal modal ──
            window.openEditJadwal = function (id, nama, hariStr, ket) {
                $('#edit_nama_jadwal').val(nama);
                $('#edit_keterangan').val(ket);
                $('#formEditJadwal').attr('action', '<?= base_url('jadwal_kerja/ubah_jadwal/') ?>' + id);
                $('input[id^="he_"]').prop('checked', false);
                hariStr.split(',').forEach(h => $('#he_' + h.trim()).prop('checked', true));
                $('#modalEditJadwal').modal('show');
            };

            // ── Scope toggle di modal hari off ──
            $('input[name="scope"]').on('change', function () {
                const isPersonal = $(this).val() === 'personal';
                $('#wrap_user_id').toggle(isPersonal);
                $('#select_user_id').prop('required', isPersonal);
                $('#alert_scope_global').toggle(!isPersonal);
                $('#alert_scope_personal').toggle(isPersonal);
            });

            // Reset modal hari off saat ditutup
            $('#modalHariOff').on('hidden.bs.modal', function () {
                $('#scope_global').prop('checked', true).trigger('change');
                $('#select_user_id').val('');
                $('input[name="tanggal"]').val('');
                $('#hari-preview').text('');
            });

            // ── Manual golongan ──
            $('#btn_pakai_manual').on('click', function () {
                const val = $('#gol_manual_input').val().trim().toUpperCase();
                if (!val) return;
                $('input[name="golongan"]').prop('checked', false);
                if ($('#gol_manual_radio').length) {
                    $('#gol_manual_radio').val(val).prop('checked', true);
                } else {
                    $('<input>').attr({ type: 'radio', name: 'golongan', id: 'gol_manual_radio', value: val, checked: true }).appendTo('#modalMapping form');
                }
                $('#gol_manual_preview').text('Dipilih: ' + val).show().css('display', 'block');
            });

            $('#modalMapping').on('hidden.bs.modal', function () {
                $('input[name="golongan"]').prop('checked', false);
                $('#gol_manual_input').val('');
                $('#gol_manual_preview').hide();
                $('#gol_manual_radio').remove();
            });

            // ── Hapus jadwal ──
            $(document).on('click', '.btn-hapus-jadwal', function (e) {
                e.preventDefault();
                const href = $(this).attr('href'), nama = $(this).data('nama');
                Swal.fire({
                    title: 'Hapus Jadwal?',
                    html: `Jadwal <strong>${nama}</strong> akan dihapus.<br><small class="text-muted">Tidak bisa dihapus jika masih dipakai golongan.</small>`,
                    icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', reverseButtons: true
                }).then(r => { if (r.isConfirmed) window.location.href = href; });
            });

            // ── Hapus mapping ──
            $(document).on('click', '.btn-hapus-mapping', function (e) {
                e.preventDefault();
                const href = $(this).attr('href'), gol = $(this).data('gol');
                Swal.fire({
                    title: 'Hapus Mapping?',
                    html: `Mapping golongan <strong>${gol}</strong> akan dihapus.<br>Karyawan golongan ini kembali ke jadwal default.`,
                    icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', reverseButtons: true
                }).then(r => { if (r.isConfirmed) window.location.href = href; });
            });

            // ── Hapus hari off ──
            $(document).on('click', '.btn-hapus-off', function (e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const tgl = $(this).data('tgl');
                const scope = $(this).data('scope');
                Swal.fire({
                    title: 'Hapus Hari Off?',
                    html: `Tanggal <strong>${tgl}</strong> (${scope}) akan dikembalikan sebagai hari kerja.`,
                    icon: 'question', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', reverseButtons: true
                }).then(r => { if (r.isConfirmed) window.location.href = href; });
            });

            // ── Preview hari ──
            const hariNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $('input[name="tanggal"]').on('change', function () {
                if ($(this).val()) {
                    const d = new Date($(this).val());
                    $('#hari-preview').text(hariNames[d.getDay()]);
                }
            });

            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
        });
    </script>
</body>

</html>
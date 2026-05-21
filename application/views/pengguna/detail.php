<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* ── Hero Card ── */
        .hero-card {
            background: linear-gradient(135deg, #1a1f3a 0%, #2d3561 50%, #1a1f3a 100%);
            border-radius: 16px;
            padding: 32px;
            color: #fff;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 240px;
            height: 240px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: 30%;
            width: 300px;
            height: 300px;
            background: rgba(99, 179, 237, 0.06);
            border-radius: 50%;
        }

        .hero-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.25);
            object-fit: cover;
        }

        .hero-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2px;
            letter-spacing: -0.3px;
        }

        .hero-nik {
            font-size: 0.82rem;
            opacity: 0.6;
            letter-spacing: 0.08em;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        /* ── Stat pills ── */
        .stat-pill {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 14px 20px;
            text-align: center;
            flex: 1;
        }

        .stat-pill .val {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
            display: block;
        }

        .stat-pill .lbl {
            font-size: 0.7rem;
            opacity: 0.6;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            margin-top: 4px;
            display: block;
        }

        .stat-pill.green .val {
            color: #68d391;
        }

        .stat-pill.yellow .val {
            color: #f6e05e;
        }

        .stat-pill.red .val {
            color: #fc8181;
        }

        .stat-pill.blue .val {
            color: #63b3ed;
        }

        /* ── Tabs ── */
        .tab-nav {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #e3e6f0;
            margin-bottom: 0;
        }

        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: none;
            font-weight: 600;
            font-size: 0.85rem;
            color: #858796;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .tab-btn:hover {
            color: #4e73df;
        }

        .tab-btn.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
        }

        .tab-badge {
            background: #4e73df;
            color: #fff;
            border-radius: 10px;
            font-size: 0.65rem;
            padding: 1px 6px;
            font-weight: 700;
        }

        .tab-content-panel {
            display: none;
            padding: 24px 0 0;
        }

        .tab-content-panel.active {
            display: block;
        }

        /* ── Info grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .info-item {
            padding: 14px 0;
            border-bottom: 1px solid #f0f2f8;
        }

        .info-item:nth-child(odd) {
            padding-right: 24px;
            border-right: 1px solid #f0f2f8;
        }

        .info-item:nth-child(even) {
            padding-left: 24px;
        }

        .info-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #b7b9cc;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 0.92rem;
            font-weight: 600;
            color: #3a3b45;
        }

        /* ── Performa ring ── */
        .perf-ring-wrap {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 16px;
        }

        .perf-ring-wrap svg {
            transform: rotate(-90deg);
        }

        .perf-ring-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .perf-ring-center .pct {
            font-size: 1.5rem;
            font-weight: 800;
            color: #3a3b45;
            line-height: 1;
        }

        .perf-ring-center .sub {
            font-size: 0.65rem;
            color: #b7b9cc;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        /* ── Cuti timeline ── */
        .cuti-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid #f0f2f8;
        }

        .cuti-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .cuti-dot.pending {
            background: #f6c23e;
        }

        .cuti-dot.disetujui {
            background: #1cc88a;
        }

        .cuti-dot.ditolak {
            background: #e74a3b;
        }

        /* ── Dokumen list ── */
        .dok-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            margin-bottom: 10px;
            transition: box-shadow 0.2s;
        }

        .dok-item:hover {
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        .dok-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .dok-icon.pdf {
            background: #fff0f0;
            color: #e74a3b;
        }

        .dok-icon.img {
            background: #f0f9ff;
            color: #36b9cc;
        }

        .dok-icon.sp {
            background: #fff8f0;
            color: #f6c23e;
        }

        .dok-meta {
            flex: 1;
            min-width: 0;
        }

        .dok-meta .dok-name {
            font-weight: 700;
            font-size: 0.88rem;
            color: #3a3b45;
        }

        .dok-meta .dok-sub {
            font-size: 0.75rem;
            color: #b7b9cc;
        }

        /* ── SP badge ── */
        .sp-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .sp-badge.sp1 {
            background: #fff8e1;
            color: #f59e0b;
            border: 1px solid #fcd34d;
        }

        .sp-badge.sp2 {
            background: #fff0e6;
            color: #ea580c;
            border: 1px solid #fdba74;
        }

        .sp-badge.sp3 {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        /* ── Upload modal ── */
        .modal-header-custom {
            background: linear-gradient(135deg, #1a1f3a, #2d3561);
            color: #fff;
            border-radius: 12px 12px 0 0;
        }

        /* ── Cuti progress bar ── */
        .cuti-bar-wrap {
            background: #f0f2f8;
            border-radius: 8px;
            height: 10px;
            overflow: hidden;
            margin-top: 6px;
        }

        .cuti-bar {
            height: 100%;
            border-radius: 8px;
            transition: width 0.8s ease;
        }

        /* ── Masa kerja chip ── */
        .masa-kerja-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f4ff;
            border: 1px solid #d0d8f5;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 0.78rem;
            color: #4e73df;
            font-weight: 600;
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
                    // ── Shorthand vars ──
                    $p = $pengguna;
                    $perf = $performa;

                    // Masa kerja
                    $masa_kerja_str = '—';
                    if ($p->tanggal_join) {
                        $join = new DateTime($p->tanggal_join);
                        $now = new DateTime();
                        $diff = $join->diff($now);
                        $parts = [];
                        if ($diff->y)
                            $parts[] = $diff->y . ' thn';
                        if ($diff->m)
                            $parts[] = $diff->m . ' bln';
                        if (!$diff->y && !$diff->m)
                            $parts[] = $diff->d . ' hari';
                        $masa_kerja_str = implode(' ', $parts);
                    }

                    // Performa values
                    $pct_hadir = $perf ? (float) $perf->persen_kehadiran : 0;
                    $hadir_bln = $perf ? (int) $perf->hadir_bulan_ini : 0;
                    $total_bln = $perf ? (int) $perf->total_hari_bulan_ini : date('t');
                    $jumlah_sp = $perf ? (int) $perf->jumlah_sp : 0;

                    // Sisa cuti
                    $jatah = (int) ($p->jatah_cuti ?? 12);
                    $sisa = (int) ($p->sisa_cuti ?? $jatah);
                    $terpakai = $jatah - $sisa;
                    $pct_cuti = $jatah > 0 ? round(($sisa / $jatah) * 100) : 0;

                    // Level label
                    $level_labels = [
                        'superadmin' => ['🔴', 'Superadmin', 'danger'],
                        'admin_operational' => ['🟠', 'Admin Operational', 'warning'],
                        'operational_staff' => ['🟡', 'Operational Staff', 'info'],
                        'finance_staff' => ['🟢', 'Finance Staff', 'success'],
                        'fleet_staff' => ['🔵', 'Fleet Staff', 'primary'],
                        'viewer' => ['🟣', 'Viewer', 'secondary'],
                        'admin_document' => ['🟤', 'Admin Document', 'dark'],
                    ];
                    [$lvl_icon, $lvl_label, $lvl_color] = $level_labels[$p->user_level] ?? ['⚪', $p->user_level, 'secondary'];

                    // Performa ring color
                    $ring_color = $pct_hadir >= 90 ? '#1cc88a' : ($pct_hadir >= 75 ? '#36b9cc' : ($pct_hadir >= 50 ? '#f6c23e' : '#e74a3b'));
                    $circumference = 2 * M_PI * 52; // r=52
                    $stroke_dash = $circumference - ($pct_hadir / 100) * $circumference;
                    ?>

                    <!-- ── Page Header ── -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-3">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-1" style="font-size:0.8rem;">
                                    <li class="breadcrumb-item"><a href="<?= base_url('pengguna') ?>">Master
                                            Karyawan</a></li>
                                    <li class="breadcrumb-item active">Detail</li>
                                </ol>
                            </nav>
                            <h1 class="h4 mb-0 text-gray-800 fw-bold"><?= $title ?></h1>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('pengguna/ubah/' . $p->id) ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Ubah
                            </a>
                            <?php if ($can_delete): ?>
                                <a href="<?= base_url('pengguna/hapus/' . $p->id) ?>"
                                    class="btn btn-danger btn-sm btn-hapus" data-nama="<?= $p->nama ?>">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            <?php endif ?>
                            <a href="<?= base_url('pengguna') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
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

                    <!-- ══════════════════════════════════
                     HERO CARD
                ══════════════════════════════════ -->
                    <div class="hero-card">
                        <div class="row align-items-center g-4">
                            <!-- Avatar + Name -->
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= base_url('uploads/profil/' . ($p->foto_profil ?: 'default-1.png')) ?>"
                                        class="hero-avatar" alt="Avatar">
                                    <div>
                                        <div class="hero-name"><?= $p->nama ?></div>
                                        <div class="hero-nik mb-2">NIK: <?= $p->nik ?></div>
                                        <span class="hero-badge">
                                            <?= $lvl_icon ?> <?= $lvl_label ?>
                                        </span>
                                        <?php if ($p->golongan): ?>
                                            <span class="hero-badge ms-1">Gol. <?= $p->golongan ?></span>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Stat pills -->
                            <div class="col-md-8">
                                <div class="d-flex gap-3 flex-wrap">
                                    <div
                                        class="stat-pill <?= $pct_hadir >= 75 ? 'green' : ($pct_hadir >= 50 ? 'yellow' : 'red') ?>">
                                        <span class="val"><?= $pct_hadir ?>%</span>
                                        <span class="lbl">Kehadiran Bulan Ini</span>
                                    </div>
                                    <div class="stat-pill blue">
                                        <span class="val"><?= $hadir_bln ?>/<?= $total_bln ?></span>
                                        <span class="lbl">Hari Hadir</span>
                                    </div>
                                    <div
                                        class="stat-pill <?= $sisa >= 6 ? 'green' : ($sisa >= 3 ? 'yellow' : 'red') ?>">
                                        <span class="val"><?= $sisa ?></span>
                                        <span class="lbl">Sisa Cuti</span>
                                    </div>
                                    <div
                                        class="stat-pill <?= $jumlah_sp == 0 ? 'green' : ($jumlah_sp == 1 ? 'yellow' : 'red') ?>">
                                        <span class="val"><?= $jumlah_sp ?></span>
                                        <span class="lbl">Surat Peringatan</span>
                                    </div>
                                    <?php if ($masa_kerja_str !== '—'): ?>
                                        <div class="stat-pill blue">
                                            <span class="val" style="font-size:1rem;"><?= $masa_kerja_str ?></span>
                                            <span class="lbl">Masa Kerja</span>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══════════════════════════════════
                     TAB NAVIGATION
                ══════════════════════════════════ -->
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="tab-nav px-4">
                                <button class="tab-btn active" data-tab="info">
                                    <i class="fas fa-id-card"></i> Informasi
                                </button>
                                <button class="tab-btn" data-tab="performa">
                                    <i class="fas fa-chart-line"></i> Performa
                                </button>
                                <button class="tab-btn" data-tab="cuti" id="tab-cuti-btn">
                                    <i class="fas fa-umbrella-beach"></i> Cuti
                                    <?php if (count(array_filter($cuti_list, fn($c) => $c->status === 'Pending'))): ?>
                                        <span
                                            class="tab-badge"><?= count(array_filter($cuti_list, fn($c) => $c->status === 'Pending')) ?></span>
                                    <?php endif ?>
                                </button>
                                <button class="tab-btn" data-tab="dokumen" id="tab-dok-btn">
                                    <i class="fas fa-folder-open"></i> Dokumen
                                    <?php if (count($dokumen_list)): ?>
                                        <span class="tab-badge"
                                            style="background:#858796;"><?= count($dokumen_list) ?></span>
                                    <?php endif ?>
                                </button>
                                <button class="tab-btn" data-tab="ktp">
                                    <i class="fas fa-id-badge"></i> KTP
                                </button>
                            </div>

                            <div class="px-4 pb-4">

                                <!-- ══ TAB: INFO ══ -->
                                <div class="tab-content-panel active" id="tab-info">
                                    <div class="row g-4">
                                        <div class="col-md-7">
                                            <h6 class="fw-bold text-muted mb-3"
                                                style="font-size:0.7rem; letter-spacing:0.1em; text-transform:uppercase;">
                                                Data Pribadi & Kepegawaian
                                            </h6>
                                            <div class="info-grid">
                                                <div class="info-item">
                                                    <div class="info-label">Nama Lengkap</div>
                                                    <div class="info-value"><?= $p->nama ?></div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">NIK</div>
                                                    <div class="info-value"><?= $p->nik ?></div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Username</div>
                                                    <div class="info-value"><code>@<?= $p->username ?></code></div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Level Akses</div>
                                                    <div class="info-value">
                                                        <span class="badge bg-<?= $lvl_color ?>"><?= $lvl_icon ?>
                                                            <?= $lvl_label ?></span>
                                                    </div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Tanggal Lahir</div>
                                                    <div class="info-value">
                                                        <?= $p->tanggal_lahir ? date('d M Y', strtotime($p->tanggal_lahir)) : '—' ?>
                                                    </div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Status Kepegawaian</div>
                                                    <div class="info-value">
                                                        <?php if ($p->status_kepegawaian): ?>
                                                            <?php
                                                            $sc = ['Tetap' => 'success', 'Kontrak' => 'warning', 'Magang' => 'info'];
                                                            ?>
                                                            <span
                                                                class="badge bg-<?= $sc[$p->status_kepegawaian] ?? 'secondary' ?>">
                                                                <?= $p->status_kepegawaian ?>
                                                            </span>
                                                        <?php else: ?><span class="text-muted">—</span><?php endif ?>
                                                    </div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Golongan</div>
                                                    <div class="info-value">
                                                        <?= $p->golongan
                                                            ? '<span class="badge bg-primary">' . $p->golongan . '</span>'
                                                            : '<span class="text-muted">—</span>' ?>
                                                    </div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Tanggal Bergabung</div>
                                                    <div class="info-value">
                                                        <?php if ($p->tanggal_join): ?>
                                                            <?= date('d M Y', strtotime($p->tanggal_join)) ?>
                                                            <div class="masa-kerja-chip mt-1">
                                                                <i class="fas fa-clock"></i> <?= $masa_kerja_str ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif ?>
                                                    </div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Jatah Cuti / Tahun</div>
                                                    <div class="info-value"><?= $jatah ?> hari</div>
                                                </div>
                                                <div class="info-item">
                                                    <div class="info-label">Sisa Cuti</div>
                                                    <div class="info-value">
                                                        <span
                                                            class="fw-bold <?= $sisa >= 6 ? 'text-success' : ($sisa >= 3 ? 'text-warning' : 'text-danger') ?>">
                                                            <?= $sisa ?> hari
                                                        </span>
                                                        <span class="text-muted small"> dari <?= $jatah ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mini performa sidebar -->
                                        <div class="col-md-5">
                                            <h6 class="fw-bold text-muted mb-3"
                                                style="font-size:0.7rem; letter-spacing:0.1em; text-transform:uppercase;">
                                                Ringkasan Performa
                                            </h6>
                                            <div class="text-center mb-4">
                                                <div class="perf-ring-wrap">
                                                    <svg width="130" height="130" viewBox="0 0 130 130">
                                                        <circle cx="65" cy="65" r="52" fill="none" stroke="#f0f2f8"
                                                            stroke-width="10" />
                                                        <circle cx="65" cy="65" r="52" fill="none"
                                                            stroke="<?= $ring_color ?>" stroke-width="10"
                                                            stroke-linecap="round"
                                                            stroke-dasharray="<?= $circumference ?>"
                                                            stroke-dashoffset="<?= $stroke_dash ?>"
                                                            class="perf-ring-circle" />
                                                    </svg>
                                                    <div class="perf-ring-center">
                                                        <div class="pct"><?= $pct_hadir ?>%</div>
                                                        <div class="sub">Kehadiran</div>
                                                    </div>
                                                </div>
                                                <div class="text-muted small"><?= $hadir_bln ?> dari <?= $total_bln ?>
                                                    hari bulan ini</div>
                                            </div>

                                            <!-- Cuti bar -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                                    <span>Sisa Cuti</span>
                                                    <span class="text-success"><?= $sisa ?>/<?= $jatah ?> hari</span>
                                                </div>
                                                <div class="cuti-bar-wrap">
                                                    <div class="cuti-bar bg-success" style="width:<?= $pct_cuti ?>%">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- SP warning -->
                                            <?php if ($jumlah_sp > 0): ?>
                                                <div class="alert alert-warning py-2 px-3 small mb-0">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Karyawan ini memiliki <strong><?= $jumlah_sp ?> surat
                                                        peringatan</strong> aktif.
                                                    <?php if ($perf && $perf->jenis_sp_terakhir): ?>
                                                        Terakhir: <strong><?= $perf->jenis_sp_terakhir ?></strong>
                                                    <?php endif ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-success py-2 px-3 small mb-0">
                                                    <i class="fas fa-check-circle"></i> Tidak ada surat peringatan aktif.
                                                </div>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- ══ TAB: PERFORMA ══ -->
                                <div class="tab-content-panel" id="tab-performa">
                                    <div class="row g-4">
                                        <!-- Kehadiran bulan ini -->
                                        <div class="col-md-4">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body text-center">
                                                    <div class="perf-ring-wrap">
                                                        <svg width="130" height="130" viewBox="0 0 130 130">
                                                            <circle cx="65" cy="65" r="52" fill="none" stroke="#e3e6f0"
                                                                stroke-width="12" />
                                                            <circle cx="65" cy="65" r="52" fill="none"
                                                                stroke="<?= $ring_color ?>" stroke-width="12"
                                                                stroke-linecap="round"
                                                                stroke-dasharray="<?= $circumference ?>"
                                                                stroke-dashoffset="<?= $stroke_dash ?>" />
                                                        </svg>
                                                        <div class="perf-ring-center">
                                                            <div class="pct"><?= $pct_hadir ?>%</div>
                                                            <div class="sub">Bulan Ini</div>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-bold">Kehadiran <?= date('F Y') ?></h6>
                                                    <p class="text-muted small mb-0"><?= $hadir_bln ?> dari
                                                        <?= $total_bln ?> hari</p>
                                                    <div class="mt-2">
                                                        <?php
                                                        if ($pct_hadir >= 90)
                                                            echo '<span class="badge bg-success">Sangat Baik</span>';
                                                        elseif ($pct_hadir >= 75)
                                                            echo '<span class="badge bg-info">Baik</span>';
                                                        elseif ($pct_hadir >= 50)
                                                            echo '<span class="badge bg-warning">Cukup</span>';
                                                        else
                                                            echo '<span class="badge bg-danger">Perlu Perhatian</span>';
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Stats lain -->
                                        <div class="col-md-4">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body">
                                                    <h6 class="fw-bold mb-3"><i
                                                            class="fas fa-calendar-alt text-primary"></i> Statistik
                                                        Tahunan <?= date('Y') ?></h6>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Total Hadir</span>
                                                        <span class="fw-bold"><?= $perf ? $perf->hadir_tahun_ini : 0 ?>
                                                            hari</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Cuti Disetujui</span>
                                                        <span
                                                            class="fw-bold"><?= $perf ? $perf->cuti_disetujui_tahun_ini : 0 ?>
                                                            kali</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Cuti Pending</span>
                                                        <span
                                                            class="fw-bold text-warning"><?= $perf ? $perf->cuti_pending : 0 ?>
                                                            kali</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 border-bottom">
                                                        <span class="text-muted small">Surat Peringatan</span>
                                                        <span
                                                            class="fw-bold <?= $jumlah_sp > 0 ? 'text-danger' : 'text-success' ?>"><?= $jumlah_sp ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2">
                                                        <span class="text-muted small">Masa Kerja</span>
                                                        <span
                                                            class="fw-bold"><?= $masa_kerja_str !== '—' ? $masa_kerja_str : '—' ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Skor performa -->
                                        <div class="col-md-4">
                                            <div class="card border-0 bg-light h-100">
                                                <div class="card-body">
                                                    <h6 class="fw-bold mb-3"><i class="fas fa-star text-warning"></i>
                                                        Skor Performa</h6>
                                                    <?php
                                                    // Skor sederhana: bobot kehadiran 60%, sisa cuti 20%, SP penalty 20%
                                                    $skor_hadir = $pct_hadir * 0.6;
                                                    $skor_cuti = $jatah > 0 ? ($sisa / $jatah) * 100 * 0.2 : 20;
                                                    $skor_sp = max(0, 20 - ($jumlah_sp * 10));
                                                    $skor_total = round($skor_hadir + $skor_cuti + $skor_sp);
                                                    $skor_total = min(100, $skor_total);

                                                    if ($skor_total >= 80) {
                                                        $skor_label = 'Excellent';
                                                        $skor_color = '#1cc88a';
                                                    } elseif ($skor_total >= 65) {
                                                        $skor_label = 'Good';
                                                        $skor_color = '#36b9cc';
                                                    } elseif ($skor_total >= 50) {
                                                        $skor_label = 'Average';
                                                        $skor_color = '#f6c23e';
                                                    } else {
                                                        $skor_label = 'Poor';
                                                        $skor_color = '#e74a3b';
                                                    }
                                                    ?>
                                                    <div class="text-center mb-3">
                                                        <div
                                                            style="font-size:2.8rem; font-weight:900; color:<?= $skor_color ?>; line-height:1;">
                                                            <?= $skor_total ?>
                                                        </div>
                                                        <div class="text-muted small">dari 100 poin</div>
                                                        <span class="badge mt-1"
                                                            style="background:<?= $skor_color ?>;"><?= $skor_label ?></span>
                                                    </div>
                                                    <hr>
                                                    <div class="small">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">Kehadiran (60%)</span>
                                                            <span class="fw-bold"><?= round($skor_hadir) ?></span>
                                                        </div>
                                                        <div class="progress mb-2" style="height:5px;">
                                                            <div class="progress-bar bg-primary"
                                                                style="width:<?= ($skor_hadir / 60) * 100 ?>%"></div>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">Sisa Cuti (20%)</span>
                                                            <span class="fw-bold"><?= round($skor_cuti) ?></span>
                                                        </div>
                                                        <div class="progress mb-2" style="height:5px;">
                                                            <div class="progress-bar bg-success"
                                                                style="width:<?= ($skor_cuti / 20) * 100 ?>%"></div>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span class="text-muted">SP Penalty (20%)</span>
                                                            <span class="fw-bold"><?= round($skor_sp) ?></span>
                                                        </div>
                                                        <div class="progress mb-0" style="height:5px;">
                                                            <div class="progress-bar bg-warning"
                                                                style="width:<?= ($skor_sp / 20) * 100 ?>%"></div>
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-light border mt-3 small mb-0 py-2">
                                                        <i class="fas fa-info-circle text-muted"></i>
                                                        Skor dihitung dari kehadiran, sisa cuti, dan jumlah SP aktif.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ══ TAB: CUTI ══ -->
                                <div class="tab-content-panel" id="tab-cuti">
                                    <div class="row g-4">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <h6 class="fw-bold mb-0">Riwayat Pengajuan Cuti</h6>
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#modalCuti">
                                                    <i class="fas fa-plus"></i> Ajukan Cuti
                                                </button>
                                            </div>

                                            <?php if (empty($cuti_list)): ?>
                                                <div class="text-center py-5 text-muted">
                                                    <i class="fas fa-umbrella-beach fa-3x mb-3 d-block"
                                                        style="opacity:0.2;"></i>
                                                    Belum ada riwayat cuti
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($cuti_list as $c): ?>
                                                    <div class="cuti-item">
                                                        <div class="cuti-dot <?= strtolower($c->status) ?>"></div>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <span class="fw-bold small">
                                                                    <?= date('d M Y', strtotime($c->tanggal_mulai)) ?>
                                                                    <?php if ($c->tanggal_mulai !== $c->tanggal_selesai): ?>
                                                                        — <?= date('d M Y', strtotime($c->tanggal_selesai)) ?>
                                                                    <?php endif ?>
                                                                </span>
                                                                <span
                                                                    class="badge <?= $c->status === 'Disetujui' ? 'bg-success' : ($c->status === 'Ditolak' ? 'bg-danger' : 'bg-warning text-dark') ?>"
                                                                    style="font-size:0.65rem;">
                                                                    <?= $c->status ?>
                                                                </span>
                                                                <span class="text-muted small"><?= $c->jumlah_hari ?>
                                                                    hari</span>
                                                            </div>
                                                            <div class="text-muted small"><?= $c->alasan ?></div>
                                                            <?php if ($c->catatan_admin): ?>
                                                                <div class="text-info small mt-1">
                                                                    <i class="fas fa-comment"></i> <?= $c->catatan_admin ?>
                                                                </div>
                                                            <?php endif ?>
                                                        </div>
                                                        <div class="d-flex gap-1 flex-column">
                                                            <?php if ($c->status === 'Pending'): ?>
                                                                <form method="POST"
                                                                    action="<?= base_url('pengguna/approve_cuti/' . $c->id . '/Disetujui') ?>"
                                                                    class="d-inline">
                                                                    <button type="submit" class="btn btn-success btn-xs px-2 py-1"
                                                                        style="font-size:0.7rem;"
                                                                        onclick="return confirm('Setujui cuti ini?')">
                                                                        <i class="fas fa-check"></i> Setujui
                                                                    </button>
                                                                </form>
                                                                <form method="POST"
                                                                    action="<?= base_url('pengguna/approve_cuti/' . $c->id . '/Ditolak') ?>"
                                                                    class="d-inline">
                                                                    <button type="submit" class="btn btn-danger btn-xs px-2 py-1"
                                                                        style="font-size:0.7rem;"
                                                                        onclick="return confirm('Tolak cuti ini?')">
                                                                        <i class="fas fa-times"></i> Tolak
                                                                    </button>
                                                                </form>
                                                                <a href="<?= base_url('pengguna/hapus_cuti/' . $c->id) ?>"
                                                                    class="btn btn-secondary btn-xs px-2 py-1"
                                                                    style="font-size:0.7rem;"
                                                                    onclick="return confirm('Hapus pengajuan?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php endif ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach ?>
                                            <?php endif ?>
                                        </div>

                                        <!-- Cuti summary -->
                                        <div class="col-md-4">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="fw-bold mb-3">Ringkasan Cuti</h6>
                                                    <div class="text-center mb-3">
                                                        <div style="font-size:2.5rem; font-weight:900; color:#1cc88a;">
                                                            <?= $sisa ?></div>
                                                        <div class="text-muted small">hari tersisa dari <?= $jatah ?>
                                                        </div>
                                                        <div class="cuti-bar-wrap mt-2">
                                                            <div class="cuti-bar bg-success"
                                                                style="width:<?= $pct_cuti ?>%"></div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-between py-2 border-bottom small">
                                                        <span class="text-muted">Jatah/tahun</span>
                                                        <span class="fw-bold"><?= $jatah ?> hari</span>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-between py-2 border-bottom small">
                                                        <span class="text-muted">Terpakai</span>
                                                        <span class="fw-bold text-danger"><?= $terpakai ?> hari</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between py-2 small">
                                                        <span class="text-muted">Sisa</span>
                                                        <span class="fw-bold text-success"><?= $sisa ?> hari</span>
                                                    </div>
                                                    <?php
                                                    $pending_count = count(array_filter($cuti_list, fn($c) => $c->status === 'Pending'));
                                                    if ($pending_count > 0):
                                                        ?>
                                                        <div class="alert alert-warning py-2 px-3 small mt-2 mb-0">
                                                            <i class="fas fa-clock"></i> <?= $pending_count ?> pengajuan
                                                            menunggu persetujuan
                                                        </div>
                                                    <?php endif ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ══ TAB: DOKUMEN ══ -->
                                <div class="tab-content-panel" id="tab-dokumen">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="fw-bold mb-0">Dokumen Karyawan</h6>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalDokumen">
                                            <i class="fas fa-upload"></i> Upload Dokumen
                                        </button>
                                    </div>

                                    <?php if (empty($dokumen_list)): ?>
                                        <div class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3 d-block" style="opacity:0.2;"></i>
                                            Belum ada dokumen
                                        </div>
                                    <?php else: ?>
                                        <div class="row g-3">
                                            <?php foreach ($dokumen_list as $dok): ?>
                                                <?php
                                                $is_sp = in_array($dok->jenis_dokumen, ['SP1', 'SP2', 'SP3', 'Surat Peringatan Lainnya']);
                                                $is_pdf = str_ends_with(strtolower($dok->file_path), '.pdf');
                                                $icon_class = $is_sp ? 'sp' : ($is_pdf ? 'pdf' : 'img');
                                                $icon_fa = $is_sp ? 'fa-exclamation-triangle' : ($is_pdf ? 'fa-file-pdf' : 'fa-image');
                                                ?>
                                                <div class="col-md-6">
                                                    <div class="dok-item">
                                                        <div class="dok-icon <?= $icon_class ?>">
                                                            <i class="fas <?= $icon_fa ?>"></i>
                                                        </div>
                                                        <div class="dok-meta">
                                                            <div class="dok-name">
                                                                <?php if ($is_sp): ?>
                                                                    <span
                                                                        class="sp-badge <?= strtolower(str_replace(' ', '', substr($dok->jenis_dokumen, 0, 3))) ?>">
                                                                        <i class="fas fa-exclamation-triangle"></i>
                                                                        <?= $dok->jenis_dokumen ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <?= $dok->jenis_dokumen ?>
                                                                <?php endif ?>
                                                            </div>
                                                            <div class="dok-sub">
                                                                <?= $dok->nomor_dokumen ? '#' . $dok->nomor_dokumen . ' · ' : '' ?>
                                                                <?= date('d M Y', strtotime($dok->tanggal_dokumen)) ?>
                                                                <?php if ($dok->tanggal_expired): ?>
                                                                    · Exp: <span
                                                                        class="<?= strtotime($dok->tanggal_expired) < time() ? 'text-danger fw-bold' : '' ?>">
                                                                        <?= date('d M Y', strtotime($dok->tanggal_expired)) ?>
                                                                    </span>
                                                                <?php endif ?>
                                                            </div>
                                                            <?php if ($dok->keterangan): ?>
                                                                <div class="dok-sub text-muted"><?= $dok->keterangan ?></div>
                                                            <?php endif ?>
                                                        </div>
                                                        <div class="d-flex gap-1">
                                                            <a href="<?= base_url('uploads/dokumen_karyawan/' . $dok->file_path) ?>"
                                                                target="_blank" class="btn btn-sm btn-outline-primary"
                                                                title="Buka">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="<?= base_url('pengguna/hapus_dokumen/' . $dok->id) ?>"
                                                                class="btn btn-sm btn-outline-danger btn-hapus-dok"
                                                                data-jenis="<?= $dok->jenis_dokumen ?>" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach ?>
                                        </div>
                                    <?php endif ?>
                                </div>

                                <!-- ══ TAB: KTP ══ -->
                                <div class="tab-content-panel" id="tab-ktp">
                                    <h6 class="fw-bold mb-3">Foto KTP</h6>
                                    <?php if ($p->foto_ktp): ?>
                                        <div class="text-center">
                                            <a href="<?= base_url('uploads/ktp/' . $p->foto_ktp) ?>" target="_blank">
                                                <img src="<?= base_url('uploads/ktp/' . $p->foto_ktp) ?>"
                                                    class="img-fluid rounded shadow" style="max-height:400px;" alt="KTP">
                                            </a>
                                            <div class="mt-2">
                                                <a href="<?= base_url('uploads/ktp/' . $p->foto_ktp) ?>" target="_blank"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-external-link-alt"></i> Buka Full
                                                </a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5 text-muted">
                                            <i class="fas fa-id-card fa-3x mb-3 d-block" style="opacity:0.2;"></i>
                                            Foto KTP belum diupload
                                        </div>
                                    <?php endif ?>
                                </div>

                            </div><!-- /px-4 pb-4 -->
                        </div><!-- /card-body -->
                    </div><!-- /card -->

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <!-- ══════════════════════════════════
     MODAL: Ajukan Cuti
══════════════════════════════════ -->
    <div class="modal fade" id="modalCuti" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title text-white"><i class="fas fa-umbrella-beach me-2"></i>Ajukan Cuti</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= base_url('pengguna/proses_cuti/' . $p->id) ?>">
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="fas fa-info-circle"></i>
                            Sisa cuti: <strong><?= $sisa ?> hari</strong>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" required
                                min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control" required
                                min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group mb-0">
                            <label class="fw-bold">Alasan <span class="text-danger">*</span></label>
                            <textarea name="alasan" class="form-control" rows="3"
                                placeholder="Jelaskan alasan pengajuan cuti..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════
     MODAL: Upload Dokumen
══════════════════════════════════ -->
    <div class="modal fade" id="modalDokumen" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title text-white"><i class="fas fa-file-upload me-2"></i>Upload Dokumen</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= base_url('pengguna/upload_dokumen/' . $p->id) ?>"
                    enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Jenis Dokumen <span class="text-danger">*</span></label>
                                <select name="jenis_dokumen" class="form-control" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <optgroup label="Administrasi">
                                        <option value="Kontrak Kerja">Kontrak Kerja</option>
                                        <option value="SK Pengangkatan">SK Pengangkatan</option>
                                        <option value="Sertifikat">Sertifikat</option>
                                        <option value="Ijazah">Ijazah</option>
                                    </optgroup>
                                    <optgroup label="Surat Peringatan">
                                        <option value="SP1">SP1 — Peringatan Pertama</option>
                                        <option value="SP2">SP2 — Peringatan Kedua</option>
                                        <option value="SP3">SP3 — Peringatan Ketiga</option>
                                        <option value="Surat Peringatan Lainnya">SP Lainnya</option>
                                    </optgroup>
                                    <optgroup label="Lain-lain">
                                        <option value="Lainnya">Lainnya</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Nomor Dokumen</label>
                                <input type="text" name="nomor_dokumen" class="form-control"
                                    placeholder="Contoh: SK/2024/001">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Tanggal Dokumen <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_dokumen" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Tanggal Berlaku</label>
                                <input type="date" name="tanggal_berlaku" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Tanggal Kadaluarsa</label>
                                <input type="date" name="tanggal_expired" class="form-control">
                                <small class="text-muted">Untuk kontrak / dokumen berkala</small>
                            </div>
                            <div class="col-12">
                                <label class="fw-bold">File Dokumen <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" name="file_dokumen" class="custom-file-input" id="file_dokumen"
                                        accept=".pdf,.jpg,.jpeg,.png" required>
                                    <label class="custom-file-label" for="file_dokumen">Pilih file...</label>
                                </div>
                                <small class="text-muted">Max 5MB · PDF, JPG, PNG</small>
                            </div>
                            <div class="col-12">
                                <label class="fw-bold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2"
                                    placeholder="Keterangan tambahan (opsional)"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {

            // ── Tab switching ──
            $('.tab-btn').on('click', function () {
                const target = $(this).data('tab');
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                $('.tab-content-panel').removeClass('active');
                $('#tab-' + target).addClass('active');

                // Handle hash for direct linking
                history.replaceState(null, null, '#' + target);
            });

            // ── Auto-open tab from hash ──
            const hash = window.location.hash.replace('#', '');
            if (hash && $('.tab-btn[data-tab="' + hash + '"]').length) {
                $('.tab-btn[data-tab="' + hash + '"]').trigger('click');
            }

            // ── Hapus karyawan confirm ──
            $('.btn-hapus').on('click', function (e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const nama = $(this).data('nama');
                Swal.fire({
                    title: 'Hapus Karyawan?',
                    html: `Data <strong>${nama}</strong> akan dihapus permanen!`,
                    icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal', reverseButtons: true
                }).then(r => { if (r.isConfirmed) window.location.href = href; });
            });

            // ── Hapus dokumen confirm ──
            $(document).on('click', '.btn-hapus-dok', function (e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const jenis = $(this).data('jenis');
                Swal.fire({
                    title: 'Hapus Dokumen?',
                    html: `Dokumen <strong>${jenis}</strong> akan dihapus permanen!`,
                    icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal', reverseButtons: true
                }).then(r => { if (r.isConfirmed) window.location.href = href; });
            });

            // ── File upload label update ──
            $('#file_dokumen').on('change', function () {
                const fname = this.files[0] ? this.files[0].name : 'Pilih file...';
                $('.custom-file-label').text(fname);
            });

            // ── Cuti tanggal sync ──
            $('input[name="tanggal_mulai"]').on('change', function () {
                $('input[name="tanggal_selesai"]').attr('min', $(this).val());
            });

            // Auto-hide flash
            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
        });
    </script>
</body>

</html>
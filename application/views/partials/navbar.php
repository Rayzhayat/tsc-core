<?php
$level = $this->session->userdata('login')['user_level'] ?? '';
$nama = $this->session->userdata('login')['nama'] ?? 'User';
$foto_profil = $this->session->userdata('login')['foto_profil'] ?? 'default-1.png';

$can_master = in_array($level, ['superadmin']);
$can_master_limited = in_array($level, ['finance_staff']);
$can_finance = in_array($level, ['superadmin', 'finance_staff']);
$can_operational = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);
$can_fleet = in_array($level, ['superadmin', 'admin_operational', 'fleet_staff']);
$can_tms = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);


$can_absensi = in_array($level, [
    'superadmin',
    'viewer',
    'head_of_departemen',
    'operational_lead',
    'administration_lead',
    'hr_staff',
    'admin_operational',
    'operational_staff',
    'finance_staff',
    'fleet_staff',
    'admin_document',
    'yamazaki',
    'tsf',
    'sinar_boga',
    'rorotan',
]);

$can_master_customer_vendor = in_array($level, ['superadmin', 'finance_staff']);
$can_vendor_operasional = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);
$can_keluhan_driver = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);

// Report Center: siapa yang bisa lihat nav item-nya
$can_report_center = in_array($level, [
    'superadmin',
    'finance_staff',
    'admin_operational',
    'operational_staff',
    'fleet_staff',
    'head_of_departemen',
    'operational_lead',
    'administration_lead',
    'hr_staff',
    'viewer',
]);

$CI_nav = &get_instance();
$can_orgchart = ($level === 'superadmin') || ($CI_nav->db->count_all_results('tb_org_visibility') > 0);
$can_manage_orgchart = in_array($level, ['superadmin', 'head_of_departemen']);

$user_id_nav = $this->session->userdata('login')['id'] ?? 0;

$my_pending_cuti = $CI_nav->db
    ->where('user_id', $user_id_nav)
    ->where('status', 'Pending')
    ->count_all_results('karyawan_cuti');

$all_pending_cuti = 0;
if ($level === 'superadmin') {
    $all_pending_cuti = $CI_nav->db
        ->where('status', 'Pending')
        ->count_all_results('karyawan_cuti');
}

// ── Pending register (superadmin only) ──
$pending_register = 0;
if ($level === 'superadmin') {
    $pending_register = $CI_nav->db
        ->where('status', 'pending')
        ->count_all_results('register_requests');
}

$level_text = [
    'superadmin'          => 'Superadmin',
    'viewer'              => 'Viewer / Manajemen',
    'head_of_departemen'  => 'Head of Departemen',
    'operational_lead'    => 'Operational Lead',
    'administration_lead' => 'Administration Lead',
    'hr_staff'            => 'HR Staff',
    'admin_operational'   => 'Admin Operational',
    'operational_staff'   => 'Operational Staff',
    'finance_staff'       => 'Finance Staff',
    'fleet_staff'         => 'Fleet Staff',
    'admin_document'      => 'Admin Document',
    'yamazaki'            => 'Yamazaki',
    'tsf'                 => 'TSF',
    'sinar_boga'          => 'Sinar Boga',
    'rorotan'             => 'Rorotan',
];
?>

<header class="navbar navbar-expand-lg navbar-dark bg-dark d-print-none sticky-top"
    style="box-shadow: 0 2px 8px rgba(0,0,0,.35); z-index: 1030;">
    <div class="container-fluid px-3">

        <!-- LOGO -->
        <a href="<?= base_url('home') ?>" class="navbar-brand flex-shrink-0 me-2">
            <img src="<?= base_url('assets/img/TSC_page-0001.png') ?>" alt="TSC" class="navbar-brand-image"
                style="max-height:36px; width:auto; min-width:60px;">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-nav flex-row order-md-last flex-shrink-0">

            <!-- NOTIFICATIONS -->
            <div class="nav-item dropdown me-2">
                <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
                    <i class="fas fa-bell fa-fw fs-5 text-white"></i>
                    <span class="badge bg-danger position-absolute top-0 start-75 translate-middle" id="notifCount"
                        style="display:none; font-size:0.6rem;">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end py-0"
                    style="min-width:380px; max-height:500px; overflow-y:auto;">
                    <div class="p-2 bg-primary text-white rounded-top">
                        <i class="fas fa-bell me-2"></i> Notifikasi
                    </div>
                    <div id="notificationList">
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-spinner fa-spin me-2"></i> Loading...
                        </div>
                    </div>
                    <div class="dropdown-divider m-0"></div>
                    <a class="dropdown-item text-center small text-muted py-2" href="#" id="markAllRead">
                        <i class="fas fa-check me-1"></i> Tandai Semua Dibaca
                    </a>
                </div>
            </div>

            <!-- USER PROFILE -->
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex align-items-center gap-2 py-1" data-bs-toggle="dropdown">
                    <img src="<?= base_url('uploads/profil/' . $foto_profil) ?>" id="topbar-profile-img"
                        alt="<?= $nama ?>" class="rounded-circle"
                        style="width:32px;height:32px;object-fit:cover;border:2px solid rgba(255,255,255,0.3);">
                    <span class="d-none d-md-inline text-white fw-semibold small"><?= $nama ?></span>
                    <i class="fas fa-chevron-down small text-white-50"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end py-0" style="min-width:220px;">
                    <div class="text-center p-3 bg-light border-bottom">
                        <img src="<?= base_url('uploads/profil/' . $foto_profil) ?>" class="rounded-circle mb-2"
                            style="width:55px;height:55px;object-fit:cover;border:3px solid #dee2e6;">
                        <div class="fw-bold small"><?= $nama ?></div>
                        <div class="text-muted" style="font-size:0.75rem;"><?= $level_text[$level] ?? $level ?></div>
                    </div>
                    <a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#changeProfileModal">
                        <i class="fas fa-user-circle me-2 text-muted"></i> Ubah Foto Profil
                    </a>
                    <div class="dropdown-divider m-0"></div>
                    <a class="dropdown-item py-2" href="#" id="logoutBtn">
                        <i class="fas fa-sign-out-alt me-2 text-muted"></i> Logout
                    </a>
                    <a class="dropdown-item py-2 text-danger" href="#" id="logoutForgetBtn">
                        <i class="fas fa-user-slash me-2"></i> Logout & Lupakan Perangkat
                    </a>
                </div>
            </div>
        </div>

        <!-- NAV MENU -->
        <div class="collapse navbar-collapse justify-content-center" id="navbar-menu">
            <ul class="navbar-nav pt-lg-0">

                <!-- DASHBOARD -->
                <li class="nav-item">
                    <a class="nav-link <?= $aktif == 'home' ? 'active' : '' ?>" href="<?= base_url('home') ?>">
                        <span class="nav-link-icon"><i class="fas fa-home"></i></span>
                        <span class="nav-link-title">Dashboard</span>
                    </a>
                </li>

                <!-- ABSENSI -->
                <?php if ($can_absensi): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $aktif == 'absensi' ? 'active' : '' ?>" href="<?= base_url('absensi') ?>">
                            <span class="nav-link-icon"><i class="fas fa-camera text-success"></i></span>
                            <span class="nav-link-title">Absensi</span>
                        </a>
                    </li>
                <?php endif ?>

                <!-- BROADCAST -->
                <li class="nav-item">
                    <a class="nav-link <?= $aktif == 'broadcast' ? 'active' : '' ?>" href="<?= base_url('broadcast') ?>">
                        <span class="nav-link-icon"><i class="fas fa-bullhorn text-warning"></i></span>
                        <span class="nav-link-title">
                            Pengumuman
                            <span class="badge bg-danger ms-1" id="broadcastBadge" style="display:none;font-size:.6rem;"></span>
                        </span>
                    </a>
                </li>

                <!-- CUTI -->
                <li class="nav-item">
                    <a class="nav-link <?= $aktif == 'cuti' ? 'active' : '' ?>" href="<?= base_url('cuti') ?>">
                        <span class="nav-link-icon"><i class="fas fa-umbrella-beach text-info"></i></span>
                        <span class="nav-link-title">
                            Cuti
                            <?php if ($my_pending_cuti > 0): ?>
                                <span class="badge bg-warning text-dark ms-1"><?= $my_pending_cuti ?></span>
                            <?php endif ?>
                            <?php if ($level === 'superadmin' && $all_pending_cuti > 0): ?>
                                <span class="badge bg-danger ms-1"><?= $all_pending_cuti ?></span>
                            <?php endif ?>
                        </span>
                    </a>
                </li>

                <!-- ORG CHART -->
                <?php if ($can_orgchart): ?>
                    <li class="nav-item <?= $can_manage_orgchart ? 'dropdown' : '' ?>">
                        <?php if ($can_manage_orgchart): ?>
                            <a class="nav-link dropdown-toggle <?= in_array($aktif, ['orgchart']) ? 'active' : '' ?>"
                                href="#" data-bs-toggle="dropdown">
                                <span class="nav-link-icon"><i class="fas fa-sitemap" style="color:#9b59b6;"></i></span>
                                <span class="nav-link-title">Org Chart</span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item <?= $aktif == 'orgchart' ? 'active' : '' ?>"
                                    href="<?= base_url('org_chart') ?>">
                                    <i class="fas fa-eye me-2 text-primary"></i> Lihat Struktur
                                </a>
                                <a class="dropdown-item <?= $aktif == 'orgchart' ? 'active' : '' ?>"
                                    href="<?= base_url('org_chart/manage') ?>">
                                    <i class="fas fa-edit me-2 text-warning"></i> Kelola Struktur
                                </a>
                            </div>
                        <?php else: ?>
                            <a class="nav-link <?= $aktif == 'orgchart' ? 'active' : '' ?>"
                                href="<?= base_url('org_chart') ?>">
                                <span class="nav-link-icon"><i class="fas fa-sitemap" style="color:#9b59b6;"></i></span>
                                <span class="nav-link-title">Org Chart</span>
                            </a>
                        <?php endif ?>
                    </li>
                <?php endif ?>


                <!-- SECURITY -->
                <?php if ($level == 'superadmin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $aktif == 'security' ? 'active' : '' ?>"
                            href="<?= base_url('security_monitor') ?>">
                            <span class="nav-link-icon"><i class="fas fa-shield-alt"></i></span>
                            <span class="nav-link-title">Security</span>
                        </a>
                    </li>
                <?php endif ?>

                <!-- MAINTENANCE TOGGLE -->
                <?php if ($level === 'superadmin'):
                    $maint_row = $CI_nav->db->get_where('tb_setting', ['key' => 'maintenance_mode'])->row();
                    $is_maintenance = ($maint_row && $maint_row->value == '1');
                ?>
                    <li class="nav-item ms-1">
                        <a class="nav-link d-flex align-items-center gap-2"
                            href="#" id="maintenanceToggleBtn"
                            title="<?= $is_maintenance ? 'Maintenance ON — klik untuk matikan' : 'Aktifkan Maintenance Mode' ?>">
                            <span class="nav-link-icon">
                                <i class="fas fa-tools <?= $is_maintenance ? 'text-warning' : '' ?>"></i>
                            </span>
                            <span class="d-none d-lg-inline" style="font-size:.75rem;">
                                <span style="display:inline-flex;align-items:center;gap:5px;">
                                    <span id="maintSwitchBg" style="width:34px;height:18px;border-radius:9px;background:<?= $is_maintenance ? '#f0a500' : '#555' ?>;position:relative;transition:background .3s;display:inline-block;">
                                        <span id="maintSwitchThumb" style="width:12px;height:12px;border-radius:50%;background:#fff;position:absolute;top:3px;left:<?= $is_maintenance ? '19px' : '3px' ?>;transition:left .3s;display:inline-block;"></span>
                                    </span>
                                    <span id="maintLabel" style="color:<?= $is_maintenance ? '#f0a500' : 'rgba(255,255,255,.6)' ?>">
                                        <?= $is_maintenance ? 'ON' : 'OFF' ?>
                                    </span>
                                </span>
                            </span>
                        </a>
                    </li>
                <?php endif ?>

                <!-- TMS -->
                <?php if ($can_tms): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $aktif == 'tms_dashboard' ? 'active' : '' ?>"
                            href="<?= base_url('tms_dashboard') ?>">
                            <span class="nav-link-icon"><i class="fas fa-tachometer-alt text-warning"></i></span>
                            <span class="nav-link-title">TMS</span>
                        </a>
                    </li>
                <?php endif ?>

                <!-- FLEET -->
                <?php if ($can_fleet): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $aktif == 'fleet' ? 'active' : '' ?>" href="<?= base_url('fleet') ?>">
                            <span class="nav-link-icon"><i class="fas fa-truck text-info"></i></span>
                            <span class="nav-link-title">Fleet</span>
                        </a>
                    </li>
                <?php endif ?>

                <!-- MASTER DATA -->
                <?php if ($can_master || $can_master_limited || $can_master_customer_vendor || $can_vendor_operasional || $can_report_center): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($aktif, ['pengguna', 'performa', 'jadwal_kerja', 'rfid_cards', 'driver', 'unit', 'akunbiaya', 'customer', 'vendorr', 'vendor_operasional', 'register_pending', 'report_center']) ? 'active' : '' ?>"
                            href="#" data-bs-toggle="dropdown">
                            <span class="nav-link-icon"><i class="fas fa-database"></i></span>
                            <span class="nav-link-title">
                                Master Data
                                <?php if ($pending_register > 0): ?>
                                    <span class="badge bg-danger ms-1"><?= $pending_register ?></span>
                                <?php endif ?>
                            </span>
                        </a>
                        <div class="dropdown-menu">

                            <?php if ($can_master_customer_vendor): ?>
                                <a class="dropdown-item <?= $aktif == 'customer' ? 'active' : '' ?>"
                                    href="<?= base_url('customer') ?>">
                                    <i class="fas fa-user-tie me-2 text-primary"></i> Master Customer
                                </a>
                                <a class="dropdown-item <?= $aktif == 'vendorr' ? 'active' : '' ?>"
                                    href="<?= base_url('vendorr') ?>">
                                    <i class="fas fa-truck me-2 text-success"></i> Master Vendor
                                </a>
                            <?php endif ?>

                            <?php if ($can_vendor_operasional): ?>
                                <a class="dropdown-item <?= $aktif == 'vendor_operasional' ? 'active' : '' ?>"
                                    href="<?= base_url('vendor_operasional') ?>">
                                    <i class="fas fa-building me-2 text-primary"></i> Vendor Operasional
                                </a>
                            <?php endif ?>

                            <?php if ($can_master): ?>
                                <a class="dropdown-item <?= $aktif == 'pengguna' ? 'active' : '' ?>"
                                    href="<?= base_url('pengguna') ?>">
                                    <i class="fas fa-users-cog me-2 text-danger"></i> Master Karyawan
                                </a>
                                <a class="dropdown-item <?= $aktif == 'performa' ? 'active' : '' ?>"
                                    href="<?= base_url('pengguna/performa') ?>">
                                    <i class="fas fa-chart-line me-2 text-success"></i> Performa Karyawan
                                </a>

                                <!-- ══ APPROVAL PENDAFTARAN ══ -->
                                <a class="dropdown-item <?= $aktif == 'register_pending' ? 'active' : '' ?>"
                                    href="<?= base_url('register/pending') ?>">
                                    <i class="fas fa-user-clock me-2 text-warning"></i>
                                    Approval Pendaftaran
                                    <?php if ($pending_register > 0): ?>
                                        <span class="badge bg-danger ms-1"><?= $pending_register ?></span>
                                    <?php endif ?>
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item <?= $aktif == 'jadwal_kerja' ? 'active' : '' ?>"
                                    href="<?= base_url('jadwal_kerja') ?>">
                                    <i class="fas fa-calendar-alt me-2 text-warning"></i> Jadwal Kerja
                                </a>
                                <a class="dropdown-item <?= $aktif == 'rfid_cards' ? 'active' : '' ?>"
                                    href="<?= base_url('rfid_cards') ?>">
                                    <i class="fas fa-id-card me-2 text-success"></i> Kartu RFID
                                </a>
                                <a class="dropdown-item <?= $aktif == 'driver' ? 'active' : '' ?>"
                                    href="<?= base_url('driver') ?>">
                                    <i class="fas fa-id-card me-2 text-info"></i> Master Driver
                                </a>
                                <a class="dropdown-item <?= $aktif == 'unit' ? 'active' : '' ?>"
                                    href="<?= base_url('unit') ?>">
                                    <i class="fas fa-car me-2 text-primary"></i> Master Unit
                                </a>
                                <a class="dropdown-item <?= $aktif == 'akunbiaya' ? 'active' : '' ?>"
                                    href="<?= base_url('akunbiaya') ?>">
                                    <i class="fas fa-money-bill-wave me-2 text-warning"></i> Master Akun Biaya
                                </a>
                            <?php endif ?>

                            <!-- ══ REPORT CENTER ══ -->
                            <?php if ($can_report_center): ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item <?= $aktif == 'report_center' ? 'active' : '' ?>"
                                    href="<?= base_url('report_center') ?>">
                                    <i class="fas fa-file-chart-line me-2 text-success"></i> Report Center
                                </a>
                            <?php endif ?>

                        </div>
                    </li>
                <?php endif ?>

                <!-- FINANCE -->
                <?php if ($can_finance): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <span class="nav-link-icon"><i class="fas fa-calculator"></i></span>
                            <span class="nav-link-title">Finance</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item <?= $aktif == 'pemasukan' ? 'active' : '' ?>"
                                href="<?= base_url('pemasukan') ?>">
                                <i class="fas fa-arrow-down me-2 text-success"></i> Pemasukan
                            </a>
                            <a class="dropdown-item <?= $aktif == 'pengeluaran' ? 'active' : '' ?>"
                                href="<?= base_url('pengeluaran') ?>">
                                <i class="fas fa-arrow-up me-2 text-danger"></i> Pengeluaran
                            </a>
                            <a class="dropdown-item <?= $aktif == 'pembayaran_pajak' ? 'active' : '' ?>"
                                href="<?= base_url('pembayaran_pajak') ?>">
                                <i class="fas fa-hand-holding-usd me-2 text-success"></i> Pembayaran Pajak
                            </a>
                            <a class="dropdown-item <?= $aktif == 'laporan_keuangan' ? 'active' : '' ?>"
                                href="<?= base_url('laporan_keuangan') ?>">
                                <i class="fas fa-chart-line me-2"></i> Laporan Keuangan
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item <?= $aktif == 'invoice_tsc' ? 'active' : '' ?>"
                                href="<?= base_url('invoice_tsc') ?>">
                                <i class="fas fa-file-invoice me-2 text-success"></i> Invoice TSC
                            </a>
                        </div>
                    </li>
                <?php endif ?>

                <!-- OPERATIONAL -->
                <?php if ($can_operational): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($aktif, ['ftl_non_spx', 'daily_rent', 'ftl_spx', 'driver_keluhan']) ? 'active' : '' ?>"
                            href="#" data-bs-toggle="dropdown">
                            <span class="nav-link-icon"><i class="fas fa-truck-loading"></i></span>
                            <span class="nav-link-title">Operational</span>
                        </a>
                        <div class="dropdown-menu">
                            <h6 class="dropdown-header">FTL Non SPX</h6>
                            <a class="dropdown-item <?= $aktif == 'ftl_non_spx' ? 'active' : '' ?>"
                                href="<?= base_url('ftl_non_spx') ?>">
                                <i class="fas fa-list me-2 text-primary"></i> Daftar Shipment
                            </a>
                            <a class="dropdown-item" href="<?= base_url('ftl_non_spx/tambah') ?>">
                                <i class="fas fa-plus-circle me-2 text-success"></i> Tambah Shipment
                            </a>
                            <div class="dropdown-divider"></div>
                            <h6 class="dropdown-header">Daily Rent</h6>
                            <a class="dropdown-item <?= $aktif == 'daily_rent' ? 'active' : '' ?>"
                                href="<?= base_url('daily_rent') ?>">
                                <i class="fas fa-list me-2 text-info"></i> Daftar Sewa
                            </a>
                            <a class="dropdown-item" href="<?= base_url('daily_rent/tambah') ?>">
                                <i class="fas fa-plus-circle me-2 text-success"></i> Tambah Sewa
                            </a>
                            <div class="dropdown-divider"></div>
                            <h6 class="dropdown-header">FTL SPX</h6>
                            <a class="dropdown-item <?= $aktif == 'ftl_spx' ? 'active' : '' ?>"
                                href="<?= base_url('ftl_spx') ?>">
                                <i class="fas fa-list me-2 text-warning"></i> Daftar Shipment
                            </a>
                            <a class="dropdown-item" href="<?= base_url('ftl_spx/tambah') ?>">
                                <i class="fas fa-plus-circle me-2 text-success"></i> Tambah Shipment
                            </a>
                            <?php if ($can_keluhan_driver): ?>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">Driver</h6>
                                <a class="dropdown-item <?= $aktif == 'driver_keluhan' ? 'active' : '' ?>"
                                    href="<?= base_url('driver_keluhan/admin') ?>">
                                    <i class="fas fa-comment-dots me-2 text-danger"></i> Keluhan Driver
                                    <?php
                                    $keluhan_baru = $CI_nav->db->where('status', 'baru')->count_all_results('tb_driver_keluhan');
                                    if ($keluhan_baru > 0): ?>
                                        <span class="badge bg-danger ms-1"><?= $keluhan_baru ?></span>
                                    <?php endif ?>
                                </a>
                                <a class="dropdown-item" href="<?= base_url('driver_keluhan') ?>" target="_blank">
                                    <i class="fas fa-external-link-alt me-2 text-muted"></i> Buka Form Driver
                                </a>
                            <?php endif ?>
                        </div>
                    </li>
                <?php endif ?>

                <!-- ANALYTICS -->
                <?php if (in_array($level, ['superadmin', 'finance_staff', 'head_of_departemen', 'operational_lead', 'operational_staff'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($aktif, ['analytics', 'feedback', 'prediction']) ? 'active' : '' ?>"
                            href="#" data-bs-toggle="dropdown">
                            <span class="nav-link-icon"><i class="fas fa-chart-bar text-success"></i></span>
                            <span class="nav-link-title">Analytics</span>
                        </a>
                        <div class="dropdown-menu">

                            <?php if (in_array($level, ['superadmin', 'finance_staff', 'head_of_departemen', 'operational_lead'])): ?>
                                <a class="dropdown-item <?= $aktif == 'analytics' ? 'active' : '' ?>"
                                    href="<?= base_url('analytics') ?>">
                                    <i class="fas fa-tachometer-alt me-2 text-primary"></i> Dashboard Analytics
                                </a>
                                <a class="dropdown-item <?= $aktif == 'analytics_daily' ? 'active' : '' ?>"
                                    href="<?= base_url('analytics/daily') ?>">
                                    <i class="fas fa-calendar-day me-2 text-info"></i> Daily Monitoring
                                </a>
                                <a class="dropdown-item <?= $aktif == 'analytics_weekly' ? 'active' : '' ?>"
                                    href="<?= base_url('analytics/weekly') ?>">
                                    <i class="fas fa-file-alt me-2 text-warning"></i> Laporan Mingguan
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item <?= $aktif == 'prediction' ? 'active' : '' ?>"
                                    href="<?= base_url('prediction') ?>">
                                    <i class="fas fa-robot me-2" style="color:#9b59b6"></i> Prediksi Margin AI
                                </a>
                                <div class="dropdown-divider"></div>
                            <?php endif ?>

                            <a class="dropdown-item <?= $aktif == 'feedback' ? 'active' : '' ?>"
                                href="<?= base_url('feedback') ?>">
                                <i class="fas fa-search me-2 text-warning"></i> Feedback SPX
                            </a>

                            <?php if (in_array($level, ['superadmin', 'finance_staff'])): ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="<?= base_url('analytics/import') ?>">
                                    <i class="fas fa-file-import me-2 text-warning"></i> Import CSV
                                </a>
                            <?php endif ?>

                        </div>
                    </li>
                <?php endif ?>

                <!-- DATABASE BACKUP -->
                <?php if ($level === 'superadmin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $aktif == 'backup' ? 'active' : '' ?>"
                            href="<?= base_url('database_backup') ?>">
                            <span class="nav-link-icon"><i class="fas fa-database"></i></span>
                            <span class="nav-link-title">Backup</span>
                        </a>
                    </li>
                <?php endif ?>

                <!-- SUPPORT TICKET -->
                <li class="nav-item">
                    <a class="nav-link <?= $aktif == 'ticket' ? 'active' : '' ?>" href="<?= base_url('ticket') ?>">
                        <span class="nav-link-icon"><i class="fas fa-ticket-alt text-warning"></i></span>
                        <span class="nav-link-title">
                            Support
                            <?php if ($level === 'superadmin'):
                                $open_ticket = $CI_nav->db->where('status', 'open')->count_all_results('tickets');
                                if ($open_ticket > 0): ?>
                                    <span class="badge bg-danger ms-1"><?= $open_ticket ?></span>
                            <?php endif;
                            endif; ?>
                        </span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</header>

<!-- LOGOUT OVERLAY -->
<div class="logout-overlay" id="logoutOverlay">
    <div class="logout-content">
        <div class="logout-icon"><i class="fas fa-sign-out-alt"></i></div>
        <div class="logout-spinner">
            <div class="spinner-border" role="status"></div>
        </div>
        <div class="logout-text" id="logoutText">Mengakhiri Sesi...</div>
        <div class="logout-subtext" id="logoutSubtext">Terima kasih telah menggunakan sistem</div>
        <div class="logout-dots"><span></span><span></span><span></span></div>
    </div>
</div>

<!-- MODAL UBAH FOTO PROFIL -->
<div class="modal fade" id="changeProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i> Ubah Foto Profil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-change-profile">
                <div class="modal-body">
                    <p class="text-center text-muted mb-3">Pilih foto profil Anda:</p>
                    <div class="profile-selector-modal">
                        <?php foreach (['default-1.png', 'default-2.png', 'default-3.png', 'default-4.png'] as $i => $foto): ?>
                            <label class="profile-option-modal">
                                <input type="radio" name="foto_profil" value="<?= $foto ?>"
                                    <?= $foto_profil == $foto ? 'checked' : '' ?>>
                                <img src="<?= base_url('uploads/profil/' . $foto) ?>" alt="Avatar <?= $i + 1 ?>">
                                <span class="check-icon-modal"><i class="fas fa-check"></i></span>
                            </label>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .navbar-brand {
        flex-shrink: 0 !important;
    }

    .navbar-brand-image {
        max-height: 36px;
        width: auto;
        min-width: 60px;
        display: block;
    }

    .logout-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(30, 58, 95, .96) 0%, rgba(78, 154, 241, .96) 100%);
        backdrop-filter: blur(10px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 99999;
        opacity: 0;
        transition: opacity .4s ease;
    }

    .logout-overlay.show {
        display: flex;
        opacity: 1;
    }

    .logout-content {
        text-align: center;
        color: #fff;
        animation: fadeInUp .6s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .logout-icon {
        font-size: 80px;
        margin-bottom: 20px;
    }

    .logout-spinner {
        margin: 20px auto;
    }

    .logout-spinner .spinner-border {
        width: 60px;
        height: 60px;
        border-width: 4px;
        border-color: rgba(255, 255, 255, .3);
        border-top-color: #fff;
    }

    .logout-text {
        font-size: 24px;
        font-weight: 600;
        margin-top: 20px;
    }

    .logout-subtext {
        font-size: 16px;
        color: rgba(255, 255, 255, .8);
        margin-top: 8px;
    }

    .logout-dots {
        display: inline-flex;
        gap: 10px;
        margin-top: 20px;
    }

    .logout-dots span {
        width: 12px;
        height: 12px;
        background: #fff;
        border-radius: 50%;
        animation: logoutBounce 1.4s infinite ease-in-out both;
    }

    .logout-dots span:nth-child(1) {
        animation-delay: -.32s;
    }

    .logout-dots span:nth-child(2) {
        animation-delay: -.16s;
    }

    @keyframes logoutBounce {

        0%,
        80%,
        100% {
            transform: scale(0);
            opacity: .5;
        }

        40% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .profile-selector-modal {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        padding: 8px;
    }

    .profile-option-modal {
        position: relative;
        cursor: pointer;
    }

    .profile-option-modal input[type=radio] {
        position: absolute;
        opacity: 0;
    }

    .profile-option-modal img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #dee2e6;
        transition: all .3s ease;
    }

    .profile-option-modal input[type=radio]:checked+img {
        border-color: #066fd1;
        box-shadow: 0 0 0 3px rgba(6, 111, 209, .2);
        transform: scale(1.05);
    }

    .profile-option-modal .check-icon-modal {
        position: absolute;
        top: -4px;
        right: 4px;
        background: #066fd1;
        color: #fff;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }

    .profile-option-modal input[type=radio]:checked~.check-icon-modal {
        display: flex;
    }
</style>

<script>
    (function() {
        var _cache = [],
            _dropdownOpen = false;

        function updateBadge(items) {
            var unread = items.filter(function(n) {
                return !n.read;
            }).length;
            var badge = document.getElementById('notifCount');
            if (unread > 0) {
                badge.textContent = unread > 99 ? '99+' : unread;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        }

        function renderList(items) {
            var el = document.getElementById('notificationList');
            if (!items.length) {
                el.innerHTML = '<div class="text-center py-4 text-muted small"><i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>Tidak ada notifikasi</div>';
                return;
            }
            el.innerHTML = items.map(function(n) {
                return '<a href="' + n.url + '" class="dropdown-item py-2 px-3 border-bottom' + (n.read ? ' text-muted' : '') + '" style="white-space:normal">' +
                    '<div class="d-flex align-items-start gap-2">' +
                    '<span class="mt-1"><i class="fas fa-' + n.icon + ' text-' + n.color + '"></i></span>' +
                    '<div class="flex-grow-1" style="min-width:0">' +
                    '<div class="small fw-' + (n.read ? 'normal' : 'bold') + ' text-truncate">' + n.title + '</div>' +
                    '<div class="text-muted" style="font-size:.75rem">' + n.body + '</div>' +
                    '<div class="text-muted" style="font-size:.7rem">' + n.time + '</div>' +
                    '</div>' +
                    (n.read ? '' : '<span class="badge bg-danger rounded-pill ms-1" style="font-size:.6rem">Baru</span>') +
                    '</div></a>';
            }).join('');
        }

        function fetchNotif(renderAfter) {
            fetch('<?= base_url("notifikasi/get") ?>')
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    _cache = data.items || [];
                    updateBadge(_cache);
                    if (renderAfter) renderList(_cache);
                })
                .catch(function() {
                    if (renderAfter) {
                        document.getElementById('notificationList').innerHTML =
                            '<div class="text-center py-3 text-muted small"><i class="fas fa-exclamation-circle me-1"></i>Gagal memuat</div>';
                    }
                });
        }
        var bellNavItem = document.getElementById('notifCount')?.closest('.nav-item');
        if (bellNavItem) {
            bellNavItem.addEventListener('show.bs.dropdown', function() {
                _dropdownOpen = true;
                if (_cache.length) renderList(_cache);
                fetchNotif(true);
            });
            bellNavItem.addEventListener('hide.bs.dropdown', function() {
                _dropdownOpen = false;
            });
        }
        document.getElementById('markAllRead').addEventListener('click', function(e) {
            e.preventDefault();
            fetch('<?= base_url("notifikasi/mark_read") ?>', {
                method: 'POST'
            }).then(function() {
                fetchNotif(_dropdownOpen);
            });
        });
        fetchNotif(false);
        setInterval(function() {
            if (!_dropdownOpen) fetchNotif(false);
        }, 60000);
    })();

    // Badge broadcast
    (function() {
        function fetchBcCount() {
            fetch('<?= base_url("broadcast/count") ?>')
                .then(r => r.json())
                .then(d => {
                    var b = document.getElementById('broadcastBadge');
                    if (!b) return;
                    if (d.count > 0) {
                        b.textContent = d.count > 99 ? '99+' : d.count;
                        b.style.display = '';
                    } else {
                        b.style.display = 'none';
                    }
                }).catch(function() {});
        }
        fetchBcCount();
        setInterval(fetchBcCount, 60000);
    })();

    (function() {
        var btn = document.getElementById('maintenanceToggleBtn');
        if (!btn) return;
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var label = document.getElementById('maintLabel');
            var isOn = label && label.textContent.trim() === 'ON';
            var msg = isOn ?
                'Matikan Maintenance Mode? Semua user bisa akses kembali.' :
                'Aktifkan Maintenance Mode? Semua user (kecuali superadmin) akan di-redirect ke halaman maintenance!';

            if (!confirm(msg)) return;

            fetch('<?= base_url("maintenance/toggle") ?>', {
                    method: 'POST'
                })
                .then(r => r.json())
                .then(d => {
                    if (d.maintenance == '1') {
                        document.getElementById('maintSwitchBg').style.background = '#f0a500';
                        document.getElementById('maintSwitchThumb').style.left = '19px';
                        document.getElementById('maintLabel').textContent = 'ON';
                        document.getElementById('maintLabel').style.color = '#f0a500';
                    } else {
                        document.getElementById('maintSwitchBg').style.background = '#555';
                        document.getElementById('maintSwitchThumb').style.left = '3px';
                        document.getElementById('maintLabel').textContent = 'OFF';
                        document.getElementById('maintLabel').style.color = 'rgba(255,255,255,.6)';
                    }
                })
                .catch(() => alert('Gagal mengubah status maintenance.'));
        });
    })();
</script>
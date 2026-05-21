<?php
$level = $this->session->userdata('login')['user_level'] ?? '';
$username = $this->session->userdata('login')['username'] ?? '';

// ═══════════════════════════════════════════════
// HAK AKSES
// ═══════════════════════════════════════════════
$can_master = in_array($level, ['superadmin']);
$can_master_limited = in_array($level, ['finance_staff', 'admin_document', 'admin_operational']);
$can_transaksi = in_array($level, ['superadmin', 'admin_operational', 'operational_staff', 'finance_staff', 'fleet_staff', 'admin_document']);
$can_finance = in_array($level, ['superadmin', 'finance_staff']);
$can_laporan = in_array($level, ['superadmin', 'viewer', 'finance_staff']);
$can_operational = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);
$can_tms = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);
$can_absensi = in_array($level, ['superadmin', 'finance_staff', 'admin_operational', 'operational_staff']);
$can_master_customer_vendor = in_array($level, ['superadmin', 'finance_staff']);
$can_vendor_operasional = in_array($level, ['superadmin', 'admin_operational', 'operational_staff']);
$can_fleet = in_array($level, ['superadmin', 'admin_operational', 'fleet_staff']);
$can_unit_master = in_array($level, ['superadmin', 'admin_operational', 'fleet_staff']);
$has_master_section = $can_master || $can_master_limited || $can_master_customer_vendor || $can_vendor_operasional;

// ═══════════════════════════════════════════════
// COUNTS (sama seperti sebelumnya)
// ═══════════════════════════════════════════════
$notification_count = 0;
$CI = &get_instance();
if ($CI->db->table_exists('tb_notifications')) {
    try {
        if (!isset($CI->M_notification))
            $CI->load->model('M_notification');
        $notification_count = $CI->M_notification->get_unread_count($username, $level);
    } catch (Exception $e) {
        $notification_count = 0;
    }
}

$pending_approval_count = 0;
if ($can_finance && $CI->db->table_exists('tb_pengeluaran')) {
    try {
        $CI->db->where('status', 'Pending');
        $pending_approval_count = $CI->db->count_all_results('tb_pengeluaran');
    } catch (Exception $e) {
        $pending_approval_count = 0;
    }
}

$today_absensi_count = 0;
if ($can_absensi && $CI->db->table_exists('absensi')) {
    try {
        $user_id = $CI->session->userdata('login')['id'];
        $CI->db->where('user_id', $user_id)->where('tanggal', date('Y-m-d'));
        $today_absensi_count = $CI->db->count_all_results('absensi');
    } catch (Exception $e) {
        $today_absensi_count = 0;
    }
}

$unpaid_tax_count = 0;
if ($can_finance) {
    $CI->load->config('accounting');
    $tax_accounts = $CI->config->item('tax_accounts') ?? [];
    $current_month = date('Y-m');
    $start_date = $current_month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date));
    $periode_label = date('F Y', strtotime($start_date));
    foreach (['pph23' => '51', 'pph42' => '52', 'ppn_keluaran' => '53'] as $key => $default_code) {
        $kode = $tax_accounts[$key] ?? $default_code;
        $akun = $CI->db->where('kode_perkiraan', $kode)->get('tb_akunbiaya')->row();
        if ($akun) {
            $kredit = $CI->db->select('COALESCE(SUM(kredit),0) as total')->from('tb_transaksi_keuangan')
                ->where('akun_id', $akun->id)->where('tanggal >=', $start_date)->where('tanggal <=', $end_date)
                ->where('kredit >', 0)->get()->row()->total;
            $debit = $CI->db->select('COALESCE(SUM(nominal),0) as total')->from('tb_pembayaran_pajak')
                ->where('akun_ocas_id', $akun->id)->like('masa_pajak', $periode_label, 'both')->get()->row()->total;
            if (round($kredit - $debit, 0) > 0)
                $unpaid_tax_count++;
        }
    }
}

$ftl_incomplete_count = 0;
if ($can_operational && $CI->db->table_exists('ftl_non_spx')) {
    try {
        $CI->db->where('deleted_at IS NULL', null, false)->where('status_shipment', 'Scheduled')
            ->where('(vendor_id IS NULL OR nopol IS NULL OR driver IS NULL)', null, false);
        $ftl_incomplete_count = $CI->db->count_all_results('ftl_non_spx');
    } catch (Exception $e) {
        $ftl_incomplete_count = 0;
    }
}

$fleet_alert_count = 0;
if ($can_fleet) {
    try {
        if ($CI->db->table_exists('unit_documents')) {
            $CI->db->where('tanggal_expired <=', date('Y-m-d', strtotime('+30 days')))->where('status !=', 'diproses');
            $fleet_alert_count += $CI->db->count_all_results('unit_documents');
        }
        if ($CI->db->table_exists('units')) {
            $units_all = $CI->db->select('current_km, next_service_km')
                ->where('next_service_km IS NOT NULL', null, false)->where('current_km IS NOT NULL', null, false)
                ->where('next_service_km > 0')->where('current_km > 0')->get('units')->result();
            foreach ($units_all as $u) {
                if ((($u->next_service_km ?? 0) - ($u->current_km ?? 0)) <= 5000)
                    $fleet_alert_count++;
            }
        }
    } catch (Exception $e) {
        $fleet_alert_count = 0;
    }
}

// Badge & label map
$badge_map = [
    'superadmin' => 'danger',
    'admin_operational' => 'warning',
    'operational_staff' => 'info',
    'finance_staff' => 'success',
    'admin_document' => 'secondary',
    'fleet_staff' => 'primary',
    'viewer' => 'light',
];
$level_labels = [
    'superadmin' => 'Superadmin',
    'admin_operational' => 'Admin Operational',
    'operational_staff' => 'Operational Staff',
    'finance_staff' => 'Finance Staff',
    'admin_document' => 'Admin Document',
    'fleet_staff' => 'Fleet Staff',
    'viewer' => 'Viewer',
];

$nama_user = $this->session->userdata('login')['nama'] ?? 'User';
$foto_profil = $this->session->userdata('login')['foto_profil'] ?? 'default-1.png';

// Active group detection
$master_aktif = in_array($aktif, ['pengguna', 'customer', 'vendorr', 'driver', 'akunbiaya', 'unit', 'rute', 'input_saldo', 'vendor_operasional']);
$finance_aktif = in_array($aktif, ['pemasukan', 'pengeluaran', 'tagihan_vendor', 'tagihan_customer', 'pembayaran_pajak', 'laporan_keuangan', 'invoice_tsc']);
$operational_aktif = in_array($aktif, ['ftl_non_spx', 'daily_rent', 'ftl_spx']);
?>

<div class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
    <div class="sidebar-brand">
        <a href="<?= base_url('home') ?>" class="d-flex justify-content-center">
            <img src="<?= base_url('assets/img/TSC_page-0001.png') ?>" alt="TSC" class="img-fluid"
                style="max-width:110px;">
        </a>
    </div>

    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar>

        <!-- DASHBOARD -->
        <li class="nav-item">
            <a class="nav-link <?= $aktif == 'home' ? 'active' : '' ?>" href="<?= base_url('home') ?>">
                <i class="fas fa-home nav-icon me-2"></i> Dashboard
            </a>
        </li>

        <!-- ABSENSI -->
        <?php if ($can_absensi): ?>
            <li class="nav-item">
                <a class="nav-link <?= $aktif == 'absensi' ? 'active' : '' ?>" href="<?= base_url('absensi') ?>">
                    <i class="fas fa-camera nav-icon me-2 text-success"></i> Absensi
                    <?php if ($today_absensi_count > 0): ?>
                        <span class="badge bg-success ms-auto"><?= $today_absensi_count ?></span>
                    <?php endif ?>
                </a>
            </li>
        <?php endif ?>

        <!-- SECURITY MONITOR -->
        <?php if ($level == 'superadmin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $aktif == 'security' ? 'active' : '' ?>" href="<?= base_url('security_monitor') ?>">
                    <i class="fas fa-shield-alt nav-icon me-2"></i> Security Monitor
                </a>
            </li>
        <?php endif ?>

        <!-- TMS DASHBOARD -->
        <?php if ($can_tms): ?>
            <li class="nav-item">
                <a class="nav-link <?= $aktif == 'tms_dashboard' ? 'active' : '' ?>"
                    href="<?= base_url('tms_dashboard') ?>">
                    <i class="fas fa-tachometer-alt nav-icon me-2 text-warning"></i> TMS Dashboard
                </a>
            </li>
        <?php endif ?>

        <!-- FLEET DASHBOARD -->
        <?php if ($can_fleet): ?>
            <li class="nav-item">
                <a class="nav-link <?= $aktif == 'fleet' ? 'active' : '' ?>" href="<?= base_url('fleet') ?>">
                    <i class="fas fa-truck nav-icon me-2 text-info"></i> Fleet Dashboard
                    <?php if ($fleet_alert_count > 0): ?>
                        <span class="badge bg-danger ms-auto"><?= $fleet_alert_count ?></span>
                    <?php endif ?>
                </a>
            </li>
        <?php endif ?>

        <!-- DATABASE BACKUP -->
        <?php if ($level === 'superadmin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $aktif == 'backup' ? 'active' : '' ?>" href="<?= base_url('database_backup') ?>">
                    <i class="fas fa-database nav-icon me-2"></i> Database Backup
                </a>
            </li>
        <?php endif ?>

        <!-- ══════════ MASTER DATA ══════════ -->
        <?php if ($has_master_section): ?>
            <li class="nav-title">Master Data</li>
            <li class="nav-group <?= $master_aktif ? 'show' : '' ?>">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="fas fa-database nav-icon me-2"></i> Master Data
                </a>
                <ul class="nav-group-items">

                    <?php if ($can_master_customer_vendor): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'customer' ? 'active' : '' ?>" href="<?= base_url('customer') ?>">
                                <i class="fas fa-user-tie me-2 text-primary"></i> Master Customer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'vendorr' ? 'active' : '' ?>" href="<?= base_url('vendorr') ?>">
                                <i class="fas fa-truck me-2 text-success"></i> Master Vendor
                            </a>
                        </li>
                    <?php endif ?>

                    <?php if ($can_vendor_operasional): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'vendor_operasional' ? 'active' : '' ?>"
                                href="<?= base_url('vendor_operasional') ?>">
                                <i class="fas fa-building me-2 text-primary"></i> Vendor Operasional
                            </a>
                        </li>
                    <?php endif ?>

                    <?php if ($level == 'admin_operational'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'pengguna' ? 'active' : '' ?>" href="<?= base_url('pengguna') ?>">
                                <i class="fas fa-users me-2 text-warning"></i> Master Karyawan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'unit' ? 'active' : '' ?>" href="<?= base_url('unit') ?>">
                                <i class="fas fa-car me-2 text-primary"></i> Master Unit
                            </a>
                        </li>
                    <?php endif ?>

                    <?php if ($level == 'fleet_staff'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'unit' ? 'active' : '' ?>" href="<?= base_url('unit') ?>">
                                <i class="fas fa-car me-2 text-primary"></i> Master Unit
                            </a>
                        </li>
                    <?php endif ?>

                    <?php if ($can_master): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'pengguna' ? 'active' : '' ?>" href="<?= base_url('pengguna') ?>">
                                <i class="fas fa-users-cog me-2 text-danger"></i> Master Karyawan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'driver' ? 'active' : '' ?>" href="<?= base_url('driver') ?>">
                                <i class="fas fa-id-card me-2 text-info"></i> Master Driver
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'akunbiaya' ? 'active' : '' ?>"
                                href="<?= base_url('akunbiaya') ?>">
                                <i class="fas fa-money-bill-wave me-2 text-warning"></i> Master Akun Biaya
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'input_saldo' ? 'active' : '' ?>"
                                href="<?= base_url('akunbiaya/input_saldo') ?>">
                                <i class="fas fa-coins me-2 text-success"></i> Input Saldo Awal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'unit' ? 'active' : '' ?>" href="<?= base_url('unit') ?>">
                                <i class="fas fa-car me-2 text-primary"></i> Master Unit
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $aktif == 'rute' ? 'active' : '' ?>" href="<?= base_url('rute') ?>">
                                <i class="fas fa-road me-2 text-secondary"></i> Master Rute
                            </a>
                        </li>
                    <?php endif ?>

                </ul>
            </li>
        <?php endif ?>

        <!-- ══════════ FINANCE ══════════ -->
        <?php if ($can_finance): ?>
            <li class="nav-title">Finance</li>

            <li class="nav-group <?= $finance_aktif ? 'show' : '' ?>">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="fas fa-calculator nav-icon me-2"></i> Finance
                    <?php if ($pending_approval_count > 0): ?>
                        <span class="badge bg-warning ms-auto"><?= $pending_approval_count ?></span>
                    <?php endif ?>
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link <?= $aktif == 'pemasukan' ? 'active' : '' ?>"
                            href="<?= base_url('pemasukan') ?>">
                            <i class="fas fa-arrow-down me-2 text-success"></i> Pemasukan Lain-Lain
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $aktif == 'pengeluaran' ? 'active' : '' ?>"
                            href="<?= base_url('pengeluaran') ?>">
                            <i class="fas fa-arrow-up me-2 text-danger"></i> Pengeluaran Lain-Lain
                            <?php if ($pending_approval_count > 0): ?>
                                <span class="badge bg-warning ms-auto"><?= $pending_approval_count ?></span>
                            <?php endif ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $aktif == 'pembayaran_pajak' ? 'active' : '' ?>"
                            href="<?= base_url('pembayaran_pajak') ?>">
                            <i class="fas fa-hand-holding-usd me-2 text-success"></i> Pembayaran Pajak
                            <?php if ($unpaid_tax_count > 0): ?>
                                <span class="badge bg-danger ms-auto"><?= $unpaid_tax_count ?></span>
                            <?php endif ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $aktif == 'laporan_keuangan' ? 'active' : '' ?>"
                            href="<?= base_url('laporan_keuangan') ?>">
                            <i class="fas fa-chart-line me-2"></i> Laporan Keuangan
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Invoice TSC -->
            <li class="nav-group <?= $aktif == 'invoice_tsc' ? 'show' : '' ?>">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="fas fa-file-invoice nav-icon me-2 text-success"></i> Invoice TSC
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'invoice_tsc' && $this->uri->segment(2) == '') ? 'active' : '' ?>"
                            href="<?= base_url('invoice_tsc') ?>">
                            <i class="fas fa-list me-2"></i> Daftar Invoice
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'invoice_tsc' && $this->uri->segment(2) == 'tambah') ? 'active' : '' ?>"
                            href="<?= base_url('invoice_tsc/tambah') ?>">
                            <i class="fas fa-plus-circle me-2"></i> Buat Invoice
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif ?>

        <!-- ══════════ OPERATIONAL ══════════ -->
        <?php if ($can_operational): ?>
            <li class="nav-title">Operational</li>

            <!-- FTL Non SPX -->
            <li class="nav-group <?= $aktif == 'ftl_non_spx' ? 'show' : '' ?>">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="fas fa-truck nav-icon me-2 text-primary"></i> FTL Non SPX
                    <?php if ($ftl_incomplete_count > 0): ?>
                        <span class="badge bg-warning ms-auto"><?= $ftl_incomplete_count ?></span>
                    <?php endif ?>
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'ftl_non_spx' && $this->uri->segment(2) == '') ? 'active' : '' ?>"
                            href="<?= base_url('ftl_non_spx') ?>">
                            <i class="fas fa-list me-2 text-primary"></i> Daftar Shipment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'ftl_non_spx' && $this->uri->segment(2) == 'tambah') ? 'active' : '' ?>"
                            href="<?= base_url('ftl_non_spx/tambah') ?>">
                            <i class="fas fa-plus-circle me-2 text-success"></i> Tambah Shipment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'ftl_non_spx' && $this->uri->segment(2) == 'import') ? 'active' : '' ?>"
                            href="<?= base_url('ftl_non_spx/import') ?>">
                            <i class="fas fa-file-excel me-2 text-success"></i> Import Excel
                        </a>
                    </li>
                    <?php if ($level === 'superadmin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($aktif == 'ftl_non_spx' && $this->uri->segment(2) == 'terhapus') ? 'active' : '' ?>"
                                href="<?= base_url('ftl_non_spx/terhapus') ?>">
                                <i class="fas fa-trash-restore me-2 text-warning"></i> Data Terhapus
                            </a>
                        </li>
                    <?php endif ?>
                </ul>
            </li>

            <!-- Daily Rent -->
            <li class="nav-group <?= $aktif == 'daily_rent' ? 'show' : '' ?>">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="fas fa-calendar-day nav-icon me-2 text-info"></i> Daily Rent
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'daily_rent' && $this->uri->segment(2) == '') ? 'active' : '' ?>"
                            href="<?= base_url('daily_rent') ?>">
                            <i class="fas fa-list me-2 text-info"></i> Daftar Sewa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'daily_rent' && $this->uri->segment(2) == 'tambah') ? 'active' : '' ?>"
                            href="<?= base_url('daily_rent/tambah') ?>">
                            <i class="fas fa-plus-circle me-2 text-success"></i> Tambah Sewa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'daily_rent' && $this->uri->segment(2) == 'import') ? 'active' : '' ?>"
                            href="<?= base_url('daily_rent/import') ?>">
                            <i class="fas fa-file-excel me-2 text-success"></i> Import Excel
                        </a>
                    </li>
                </ul>
            </li>

            <!-- FTL SPX -->
            <li class="nav-group <?= $aktif == 'ftl_spx' ? 'show' : '' ?>">
                <a class="nav-link nav-group-toggle" href="#">
                    <i class="fas fa-truck-loading nav-icon me-2 text-warning"></i> FTL SPX
                </a>
                <ul class="nav-group-items">
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'ftl_spx' && $this->uri->segment(2) == '') ? 'active' : '' ?>"
                            href="<?= base_url('ftl_spx') ?>">
                            <i class="fas fa-list me-2 text-warning"></i> Daftar Shipment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'ftl_spx' && $this->uri->segment(2) == 'tambah') ? 'active' : '' ?>"
                            href="<?= base_url('ftl_spx/tambah') ?>">
                            <i class="fas fa-plus-circle me-2 text-success"></i> Tambah Shipment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($aktif == 'ftl_spx' && $this->uri->segment(2) == 'import') ? 'active' : '' ?>"
                            href="<?= base_url('ftl_spx/import') ?>">
                            <i class="fas fa-file-excel me-2 text-success"></i> Import Excel
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif ?>

    </ul>

    <!-- ── USER INFO BOTTOM ── -->
    <div class="sidebar-user-info">
        <div class="d-flex align-items-center gap-2 mb-2">
            <img src="<?= base_url('uploads/profil/' . $foto_profil) ?>"
                style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.3);">
            <div>
                <div class="user-name"><?= $nama_user ?></div>
                <span class="badge bg-<?= $badge_map[$level] ?? 'secondary' ?>" style="font-size:0.65rem;">
                    <?= $level_labels[$level] ?? ucwords(str_replace('_', ' ', $level)) ?>
                </span>
            </div>
        </div>

        <?php if ($can_absensi && $today_absensi_count == 0): ?>
            <a href="<?= base_url('absensi') ?>" class="btn btn-sm btn-success w-100 mt-1">
                <i class="fas fa-camera"></i> Belum Absen Hari Ini
            </a>
        <?php elseif ($can_absensi && $today_absensi_count > 0): ?>
            <div class="text-center mt-1">
                <span class="badge bg-success w-100 py-1">
                    <i class="fas fa-check-circle"></i> Sudah Absen (<?= $today_absensi_count ?>x)
                </span>
            </div>
        <?php endif ?>

        <?php if ($can_finance && $unpaid_tax_count > 0): ?>
            <a href="<?= base_url('pembayaran_pajak') ?>" class="btn btn-sm btn-danger w-100 mt-1">
                <i class="fas fa-exclamation-triangle"></i> Pajak Bulan Ini! (<?= $unpaid_tax_count ?>)
            </a>
        <?php endif ?>

        <?php if ($can_operational && $ftl_incomplete_count > 0): ?>
            <a href="<?= base_url('ftl_non_spx') ?>" class="btn btn-sm btn-warning w-100 mt-1">
                <i class="fas fa-truck"></i> <?= $ftl_incomplete_count ?> FTL belum lengkap
            </a>
        <?php endif ?>

        <?php if ($can_fleet && $fleet_alert_count > 0): ?>
            <a href="<?= base_url('fleet') ?>" class="btn btn-sm btn-danger w-100 mt-1">
                <i class="fas fa-exclamation-circle"></i> <?= $fleet_alert_count ?> Alert Armada
            </a>
        <?php endif ?>
    </div>

    <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
</div>
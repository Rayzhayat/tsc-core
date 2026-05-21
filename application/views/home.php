<?php defined('BASEPATH') OR exit('No direct script access allowed');

// ════════════════════════════════════════════════════════════
// ORG CHART WIDGET RENDERER
// Didefinisikan di atas DOCTYPE agar tidak ada masalah scope
// ════════════════════════════════════════════════════════════
function _render_orgw(array $tree, int $depth): void
{
    if ($depth > 2)
        return;
    foreach ($tree as $node):
        $dc = 'od' . min($depth, 3);
        $has_children = !empty($node->children) && $depth < 2;
        $foto = $node->pengguna_foto ?: 'default-1.png';
        ?>
        <div class="orgw-node <?= $dc ?>">
            <?php if (!empty($node->departemen) && $depth === 1): ?>
                <div class="ow-dept"><?= htmlspecialchars($node->departemen) ?></div>
            <?php endif ?>
            <div class="orgw-card">
                <?php if ($node->pengguna_id): ?>
                    <img src="<?= base_url('uploads/profil/' . htmlspecialchars($foto)) ?>"
                        alt="<?= htmlspecialchars($node->pengguna_nama ?? '') ?>" class="ow-av">
                <?php endif ?>
                <div class="ow-j"><?= htmlspecialchars($node->jabatan) ?></div>
                <div class="ow-n <?= $node->pengguna_id ? '' : 'empty' ?>">
                    <?= $node->pengguna_id
                        ? htmlspecialchars($node->pengguna_nama)
                        : '— Belum diisi —' ?>
                </div>
            </div>
            <?php if ($has_children): ?>
                <div class="ow-vline"></div>
                <?php if (count($node->children) === 1): ?>
                    <?php _render_orgw($node->children, $depth + 1); ?>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;align-items:center">
                        <div class="ow-siblings">
                            <?php foreach ($node->children as $child): ?>
                                <div style="display:flex;flex-direction:column;align-items:center">
                                    <div class="ow-btop"></div>
                                    <?php _render_orgw([$child], $depth + 1); ?>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php endif ?>
            <?php endif ?>
        </div>
        <?php
    endforeach;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* ── Welcome alert animation ── */
        #welcome-alert {
            animation: slideDown .5s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .fade-out {
            animation: fadeOut .5s ease-out forwards;
        }

        /* ── Stats cards ── */
        .stats-card {
            border-left: 4px solid;
            transition: all .3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        .stats-icon {
            font-size: 2rem;
            opacity: .3;
        }

        /* ── Recent item list ── */
        .recent-item {
            padding: 10px 15px;
            border-bottom: 1px solid #e3e6f0;
            transition: background .2s;
        }

        .recent-item:hover {
            background: #f8f9fc;
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        /* ── Finance header banner ── */
        .finance-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        /* ── Section divider ── */
        .section-divider {
            border: none;
            border-top: 2px dashed #e3e6f0;
            margin: 30px 0 20px;
        }

        /* ── Pulse alert ── */
        .alert-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .8
            }
        }

        /* ── Invoice alert table ── */
        .invoice-alert-table {
            font-size: 13px;
            margin-top: 10px;
        }

        .invoice-alert-table th {
            font-weight: 600;
            white-space: nowrap;
        }

        .invoice-alert-table td {
            vertical-align: middle;
        }

        /* ════════════════════════════════════════
           ORG CHART WIDGET
        ════════════════════════════════════════ */
        .orgw-wrap {
            overflow-x: auto;
            padding: 16px 8px 24px;
        }

        .orgw-tree {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }

        .orgw-node {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Card box */
        .orgw-card {
            background: #fff;
            border: 1.5px solid #4e73df;
            border-radius: 9px;
            padding: 8px 12px;
            min-width: 110px;
            max-width: 155px;
            text-align: center;
            transition: box-shadow .2s, transform .2s;
            position: relative;
        }

        .orgw-card:hover {
            box-shadow: 0 4px 16px rgba(78, 115, 223, .18);
            transform: translateY(-2px);
        }

        .orgw-card .ow-j {
            font-size: .72rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .orgw-card .ow-n {
            font-size: .68rem;
            color: #6c757d;
            border-top: 1px solid #e3e6f0;
            padding-top: 4px;
            margin-top: 2px;
        }

        .orgw-card .ow-n.empty {
            color: #bbb;
            font-style: italic;
        }

        .orgw-card .ow-av {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
            margin: 0 auto 5px;
            display: block;
        }

        /* Depth color coding */
        .od0>.orgw-card {
            border-color: #1a237e;
            background: #e8eaf6;
        }

        .od0>.orgw-card .ow-j {
            color: #1a237e;
        }

        .od1>.orgw-card {
            border-color: #1976d2;
            background: #e3f2fd;
        }

        .od1>.orgw-card .ow-j {
            color: #1565c0;
        }

        .od2>.orgw-card {
            border-color: #2e7d32;
            background: #e8f5e9;
        }

        .od2>.orgw-card .ow-j {
            color: #2e7d32;
        }

        .od3>.orgw-card {
            border-color: #9e9e9e;
            background: #fafafa;
        }

        /* Connectors */
        .ow-vline {
            width: 2px;
            height: 20px;
            background: #b0bec5;
            margin: 0 auto;
        }

        .ow-siblings {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            position: relative;
        }

        .ow-siblings::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: #b0bec5;
        }

        .ow-btop {
            width: 2px;
            height: 16px;
            background: #b0bec5;
        }

        /* Dept label tag */
        .ow-dept {
            font-size: .6rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #4e73df;
            margin-bottom: 3px;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">

        <?php $this->load->view('partials/navbar') ?>
        <?php $this->load->view('broadcast/banner', ['broadcasts_banner' => $broadcasts_banner]) ?>

        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <!-- ── Welcome Alert ── -->
                    <div id="welcome-alert" class="alert alert-success alert-dismissible fade show shadow-sm mb-3"
                        role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle fa-2x me-3"></i>
                            <div>
                                <h5 class="mb-1">Login Berhasil!</h5>
                                <p class="mb-0">Selamat datang, <strong><?= htmlspecialchars($nama) ?></strong> di
                                    <strong>TSC Core System</strong></p>
                                <small class="text-muted">Level: <span
                                        class="badge bg-primary"><?= strtoupper($level) ?></span></small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                    <?php
                    // ── Helper pajak — tersedia semua role ──
                    if (!function_exists('get_tax_balance_for_period')) {
                        function get_tax_balance_for_period($CI, $akun_id, $year, $month)
                        {
                            $start_date = "$year-$month-01";
                            $end_date = date('Y-m-t', strtotime($start_date));
                            $periode_label = date('F Y', strtotime($start_date));
                            $kredit = $CI->db->select('COALESCE(SUM(kredit),0) as total')
                                ->from('tb_transaksi_keuangan')
                                ->where('akun_id', $akun_id)
                                ->where('tanggal >=', $start_date)
                                ->where('tanggal <=', $end_date)
                                ->where('kredit >', 0)->get()->row()->total;
                            $debit = $CI->db->select('COALESCE(SUM(nominal),0) as total')
                                ->from('tb_pembayaran_pajak')
                                ->where('akun_ocas_id', $akun_id)
                                ->like('masa_pajak', $periode_label, 'both')->get()->row()->total;
                            return round($kredit - $debit, 0);
                        }
                    }
                    ?>

                    <?php if ($is_superadmin): ?>
                        <!-- ╔══════════════════════════════════════════════════════╗
                     ║  SUPERADMIN DASHBOARD                               ║
                     ╚══════════════════════════════════════════════════════╝ -->

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h1 class="page-title mb-0"><i class="fas fa-tachometer-alt text-primary"></i> Dashboard
                                Overview</h1>
                            <span class="text-muted"><i class="far fa-clock"></i> <span id="dashboard-time"></span></span>
                        </div>

                        <!-- INVOICE ALERTS -->
                        <?php if (!empty($overdue_invoices)): ?>
                            <div class="alert alert-danger alert-dismissible fade show shadow mb-3" style="overflow:hidden">
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <h6 class="fw-bold mb-1 pe-4"><i class="fas fa-exclamation-circle me-1"></i> ⚠️ INVOICE OVERDUE!
                                </h6>
                                <p class="small mb-2"><strong><?= count($overdue_invoices) ?></strong> invoice lewat jatuh
                                    tempo, belum dibayar.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white mb-2 align-middle"
                                        style="font-size:.8rem">
                                        <thead class="table-danger">
                                            <tr>
                                                <th>Invoice</th>
                                                <th class="d-none d-sm-table-cell">Customer</th>
                                                <th class="text-center">Jatuh Tempo</th>
                                                <th class="text-center">Telat</th>
                                                <th class="text-end">Nominal</th>
                                                <th class="text-center" style="width:36px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($overdue_invoices as $inv): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($inv->no_invoice) ?></strong>
                                                        <div class="d-sm-none text-muted" style="font-size:.72rem">
                                                            <?= htmlspecialchars($inv->customer_nama) ?></div>
                                                    </td>
                                                    <td class="d-none d-sm-table-cell"><?= htmlspecialchars($inv->customer_nama) ?>
                                                    </td>
                                                    <td class="text-center" style="white-space:nowrap">
                                                        <?= date('d/m/Y', strtotime($inv->due_date)) ?></td>
                                                    <td class="text-center"><span class="badge bg-danger"><?= $inv->days_overdue ?>
                                                            hari</span></td>
                                                    <td class="text-end" style="white-space:nowrap"><strong>Rp
                                                            <?= number_format($inv->grand_total, 0, ',', '.') ?></strong></td>
                                                    <td class="text-center"><a
                                                            href="<?= base_url('invoice_tsc/detail/' . $inv->id) ?>"
                                                            class="btn btn-primary btn-sm py-0 px-2"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="<?= base_url('invoice_tsc?status=sent') ?>" class="btn btn-sm btn-light"><i
                                        class="fas fa-list me-1"></i> Semua Invoice Outstanding</a>
                            </div>
                        <?php endif ?>

                        <?php if (!empty($upcoming_due_invoices)): ?>
                            <div class="alert alert-warning alert-dismissible fade show shadow mb-3" style="overflow:hidden">
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <h6 class="fw-bold mb-1 pe-4"><i class="fas fa-bell me-1"></i> 🔔 Invoice Mendekati Jatuh Tempo
                                </h6>
                                <p class="small mb-2"><strong><?= count($upcoming_due_invoices) ?></strong> invoice jatuh tempo
                                    dalam 1&ndash;2 hari.</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white mb-2 align-middle"
                                        style="font-size:.8rem">
                                        <thead class="table-warning">
                                            <tr>
                                                <th>Invoice</th>
                                                <th class="d-none d-sm-table-cell">Customer</th>
                                                <th class="text-center">Jatuh Tempo</th>
                                                <th class="text-center">Sisa</th>
                                                <th class="text-end">Nominal</th>
                                                <th class="text-center" style="width:36px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($upcoming_due_invoices as $inv): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($inv->no_invoice) ?></strong>
                                                        <div class="d-sm-none text-muted" style="font-size:.72rem">
                                                            <?= htmlspecialchars($inv->customer_nama) ?></div>
                                                    </td>
                                                    <td class="d-none d-sm-table-cell"><?= htmlspecialchars($inv->customer_nama) ?>
                                                    </td>
                                                    <td class="text-center" style="white-space:nowrap">
                                                        <?= date('d/m/Y', strtotime($inv->due_date)) ?></td>
                                                    <td class="text-center">
                                                        <?php if ($inv->days_left == 0): ?><span class="badge bg-danger">Hari
                                                                Ini</span>
                                                        <?php elseif ($inv->days_left == 1): ?><span
                                                                class="badge bg-warning text-dark">Besok</span>
                                                        <?php else: ?><span class="badge bg-info"><?= $inv->days_left ?> hari</span>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-end" style="white-space:nowrap"><strong>Rp
                                                            <?= number_format($inv->grand_total, 0, ',', '.') ?></strong></td>
                                                    <td class="text-center"><a
                                                            href="<?= base_url('invoice_tsc/detail/' . $inv->id) ?>"
                                                            class="btn btn-primary btn-sm py-0 px-2"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="<?= base_url('invoice_tsc') ?>" class="btn btn-sm btn-light"><i
                                        class="fas fa-file-invoice me-1"></i> Semua Invoice</a>
                            </div>
                        <?php endif ?>

                        <!-- MASTER DATA STATS -->
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-muted"><i class="fas fa-database text-primary me-2"></i> Master
                                Data</h5>
                            <hr class="flex-grow-1 ms-3" style="border-color:#e3e6f0">
                        </div>
                        <div class="row g-3 mb-3">
                            <?php
                            $master_cards = [
                                ['label' => 'Total Vendor', 'val' => $total_vendor ?? 0, 'color' => 'primary', 'icon' => 'truck', 'url' => 'vendorr'],
                                ['label' => 'Total Customer', 'val' => $total_customer ?? 0, 'color' => 'success', 'icon' => 'users', 'url' => 'customer'],
                                ['label' => 'Total Pengguna', 'val' => $total_pengguna ?? 0, 'color' => 'warning', 'icon' => 'user-shield', 'url' => 'pengguna'],
                                ['label' => 'Total Akun Biaya', 'val' => $total_akun_biaya ?? 0, 'color' => 'info', 'icon' => 'book', 'url' => 'akunbiaya'],
                            ];
                            $border_colors = ['primary' => '#4e73df', 'success' => '#1cc88a', 'warning' => '#f6c23e', 'info' => '#36b9cc'];
                            foreach ($master_cards as $card):
                                ?>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card stats-card shadow h-100 py-2"
                                        style="border-left-color:<?= $border_colors[$card['color']] ?>">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <div class="text-xs fw-bold text-<?= $card['color'] ?> text-uppercase mb-1">
                                                        <?= $card['label'] ?></div>
                                                    <div class="h5 mb-0 fw-bold"><?= number_format($card['val']) ?></div>
                                                </div>
                                                <i
                                                    class="fas fa-<?= $card['icon'] ?> stats-icon text-<?= $card['color'] ?>"></i>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-white border-0 pt-0">
                                            <a href="<?= base_url($card['url']) ?>"
                                                class="btn btn-sm btn-<?= $card['color'] ?> w-100"><i class="fas fa-eye"></i>
                                                Lihat Data</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>

                        <!-- FINANCE STATS -->
                        <hr class="section-divider">
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-muted"><i class="fas fa-calculator text-success me-2"></i>
                                Ringkasan Finance</h5>
                            <hr class="flex-grow-1 ms-3" style="border-color:#e3e6f0">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-xl-3 col-md-6">
                                <div class="card stats-card shadow h-100 py-2" style="border-left-color:#f6c23e">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Invoice Bulan
                                                    Ini</div>
                                                <div class="h5 mb-0 fw-bold"><?= number_format($total_invoice_month ?? 0) ?>
                                                </div>
                                                <small class="text-muted">Invoice TSC</small>
                                            </div>
                                            <i class="fas fa-file-invoice stats-icon text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0"><a href="<?= base_url('invoice_tsc') ?>"
                                            class="btn btn-sm btn-warning w-100"><i class="fas fa-eye"></i> Lihat
                                            Invoice</a></div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card stats-card shadow h-100 py-2" style="border-left-color:#e74a3b">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Outstanding
                                                </div>
                                                <div class="h6 mb-0 fw-bold">Rp
                                                    <?= number_format($total_outstanding ?? 0, 0, ',', '.') ?></div>
                                                <small class="text-muted">Invoice belum dibayar</small>
                                            </div>
                                            <i class="fas fa-exclamation-triangle stats-icon text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0"><a href="<?= base_url('invoice_tsc') ?>"
                                            class="btn btn-sm btn-danger w-100"><i class="fas fa-eye"></i> Lihat Detail</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card stats-card shadow h-100 py-2" style="border-left:4px solid #858796">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-xs fw-bold text-secondary text-uppercase mb-1">Invoice
                                                    Unpaid</div>
                                                <div class="h5 mb-0 fw-bold">
                                                    <?= number_format($total_outstanding_invoices ?? 0) ?></div>
                                                <small class="text-muted">Status sent / draft</small>
                                            </div>
                                            <i class="fas fa-hourglass-half stats-icon text-secondary"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0"><a
                                            href="<?= base_url('invoice_tsc?status=sent') ?>"
                                            class="btn btn-sm btn-secondary w-100"><i class="fas fa-eye"></i> Lihat
                                            Detail</a></div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card stats-card shadow h-100 py-2" style="border-left:4px solid #4e73df">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Tagihan Vendor
                                                    Pending</div>
                                                <div class="h5 mb-0 fw-bold"><?= number_format($total_unpaid_bills ?? 0) ?>
                                                </div>
                                                <small class="text-muted">Waiting payment</small>
                                            </div>
                                            <i class="fas fa-file-invoice-dollar stats-icon text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0"><a
                                            href="<?= base_url('tagihan_vendor') ?>" class="btn btn-sm btn-primary w-100"><i
                                                class="fas fa-eye"></i> Lihat Tagihan</a></div>
                                </div>
                            </div>
                        </div>

                        <!-- TAX ALERTS -->
                        <?php
                        $CI = &get_instance();
                        $CI->load->config('accounting');
                        $tax_accounts = $CI->config->item('tax_accounts') ?? [];
                        $pph23_akun = $CI->db->where('kode_perkiraan', $tax_accounts['pph23'] ?? '51')->get('tb_akunbiaya')->row();
                        $pph42_akun = $CI->db->where('kode_perkiraan', $tax_accounts['pph42'] ?? '52')->get('tb_akunbiaya')->row();
                        $ppn_akun = $CI->db->where('kode_perkiraan', $tax_accounts['ppn_keluaran'] ?? '53')->get('tb_akunbiaya')->row();

                        $tax_alerts_current = [];
                        $tax_alerts_past = [];
                        for ($i = 0; $i < 6; $i++) {
                            $cd = strtotime("-$i months");
                            $cy = date('Y', $cd);
                            $cm = date('m', $cd);
                            $dl = date('Y-m-15', strtotime("$cy-$cm-01 +1 month"));
                            foreach ([
                                ['akun' => $pph23_akun, 'type' => 'PPH 23', 'color' => 'danger'],
                                ['akun' => $pph42_akun, 'type' => 'PPH 4(2)', 'color' => 'warning'],
                                ['akun' => $ppn_akun, 'type' => 'PPN Keluaran', 'color' => 'success'],
                            ] as $t) {
                                if (!$t['akun'])
                                    continue;
                                $bal = get_tax_balance_for_period($CI, $t['akun']->id, $cy, $cm);
                                if ($bal <= 0)
                                    continue;
                                $row = ['type' => $t['type'], 'periode' => date('F Y', $cd), 'balance' => $bal, 'deadline' => date('d M Y', strtotime($dl)), 'color' => $t['color'], 'year_month' => "$cy-$cm", 'overdue_months' => $i];
                                if ($i == 0)
                                    $tax_alerts_current[] = $row;
                                else
                                    $tax_alerts_past[] = $row;
                            }
                        }
                        $total_current_unpaid = array_sum(array_column($tax_alerts_current, 'balance'));
                        $total_past_unpaid = array_sum(array_column($tax_alerts_past, 'balance'));
                        $total_all_unpaid = $total_current_unpaid + $total_past_unpaid;
                        ?>

                        <?php if (!empty($tax_alerts_current)): ?>
                            <div class="alert alert-danger alert-dismissible fade show shadow alert-pulse mb-3" role="alert"
                                style="overflow:hidden">
                                <div class="d-flex align-items-start">
                                    <div class="me-3"><i class="fas fa-exclamation-triangle fa-3x"></i></div>
                                    <div class="flex-grow-1" style="min-width:0;overflow:hidden">
                                        <h5 class="alert-heading mb-2"><strong>⚠️ PAJAK BULAN INI BELUM DIBAYAR!</strong></h5>
                                        <p class="mb-2">Ada <strong><?= count($tax_alerts_current) ?></strong> jenis pajak untuk
                                            periode <strong><?= date('F Y') ?></strong> yang belum dibayar:</p>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                <thead class="bg-danger text-white">
                                                    <tr>
                                                        <th>Jenis Pajak</th>
                                                        <th>Periode</th>
                                                        <th class="text-end">Nominal</th>
                                                        <th>Batas</th>
                                                        <th>Status</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($tax_alerts_current as $tax): ?>
                                                        <tr>
                                                            <td><span class="badge bg-<?= $tax['color'] ?>"><i
                                                                        class="fas fa-file-invoice-dollar"></i>
                                                                    <?= $tax['type'] ?></span></td>
                                                            <td><strong><?= $tax['periode'] ?></strong></td>
                                                            <td class="text-end"><strong class="text-danger">Rp
                                                                    <?= number_format($tax['balance'], 0, ',', '.') ?></strong></td>
                                                            <td><i class="fas fa-calendar-alt text-danger"></i>
                                                                <?= $tax['deadline'] ?></td>
                                                            <td><span class="badge bg-danger"><i class="fas fa-clock"></i> Jatuh
                                                                    Tempo Bulan Ini</span></td>
                                                            <td class="text-center"><a
                                                                    href="<?= base_url('pembayaran_pajak?periode=' . $tax['year_month']) ?>"
                                                                    class="btn btn-sm btn-danger"><i
                                                                        class="fas fa-money-bill-wave"></i> Bayar</a></td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="2" class="text-end">TOTAL BULAN INI:</th>
                                                        <th class="text-end text-danger">Rp
                                                            <?= number_format($total_current_unpaid, 0, ',', '.') ?></th>
                                                        <th colspan="3"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="mt-3"><a href="<?= base_url('pembayaran_pajak?periode=' . date('Y-m')) ?>"
                                                class="btn btn-sm btn-danger"><i class="fas fa-hand-holding-usd"></i> Bayar
                                                Pajak Bulan Ini</a></div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>

                        <?php if (!empty($tax_alerts_past)): ?>
                            <div class="alert alert-warning alert-dismissible fade show shadow mb-3" role="alert"
                                style="overflow:hidden">
                                <div class="d-flex align-items-start">
                                    <div class="me-3"><i class="fas fa-history fa-3x"></i></div>
                                    <div class="flex-grow-1" style="min-width:0;overflow:hidden">
                                        <h5 class="alert-heading mb-2"><strong>📋 Pajak Bulan Sebelumnya Belum Dibayar</strong>
                                        </h5>
                                        <p class="mb-2">Ada <strong><?= count($tax_alerts_past) ?></strong> pajak dari
                                            bulan-bulan sebelumnya yang belum diselesaikan:</p>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                <thead class="bg-warning">
                                                    <tr>
                                                        <th>Jenis Pajak</th>
                                                        <th>Periode</th>
                                                        <th class="text-end">Nominal</th>
                                                        <th>Deadline</th>
                                                        <th>Status</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($tax_alerts_past as $tax): ?>
                                                        <tr>
                                                            <td><span class="badge bg-<?= $tax['color'] ?>"><i
                                                                        class="fas fa-file-invoice-dollar"></i>
                                                                    <?= $tax['type'] ?></span></td>
                                                            <td><strong><?= $tax['periode'] ?></strong></td>
                                                            <td class="text-end"><strong class="text-warning">Rp
                                                                    <?= number_format($tax['balance'], 0, ',', '.') ?></strong></td>
                                                            <td><i class="fas fa-calendar-times text-danger"></i>
                                                                <?= $tax['deadline'] ?></td>
                                                            <td><span class="badge bg-danger"><i
                                                                        class="fas fa-exclamation-triangle"></i> Overdue
                                                                    (<?= $tax['overdue_months'] ?> bln)</span></td>
                                                            <td class="text-center"><a
                                                                    href="<?= base_url('pembayaran_pajak?periode=' . $tax['year_month']) ?>"
                                                                    class="btn btn-sm btn-warning"><i
                                                                        class="fas fa-money-bill-wave"></i> Bayar</a></td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="2" class="text-end">TOTAL BULAN LALU:</th>
                                                        <th class="text-end text-warning">Rp
                                                            <?= number_format($total_past_unpaid, 0, ',', '.') ?></th>
                                                        <th colspan="3"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="mt-3"><a href="<?= base_url('laporan_keuangan') ?>"
                                                class="btn btn-sm btn-info"><i class="fas fa-chart-line"></i> Laporan
                                                Keuangan</a></div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>

                        <?php if (!empty($tax_alerts_current) && !empty($tax_alerts_past)): ?>
                            <div class="card shadow mb-3" style="border-left:4px solid #e74a3b">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                        <div>
                                            <h5 class="text-danger mb-2"><strong>TOTAL PAJAK BELUM DIBAYAR</strong></h5>
                                            <p class="mb-0">Total akumulasi
                                                (<?= count($tax_alerts_current) + count($tax_alerts_past) ?> item):</p>
                                            <h3 class="text-danger mt-2 mb-0">Rp
                                                <?= number_format($total_all_unpaid, 0, ',', '.') ?></h3>
                                            <small class="text-muted">Bulan Ini: Rp
                                                <?= number_format($total_current_unpaid, 0, ',', '.') ?> | Bulan Lalu: Rp
                                                <?= number_format($total_past_unpaid, 0, ',', '.') ?></small>
                                        </div>
                                        <a href="<?= base_url('pembayaran_pajak') ?>" class="btn btn-danger btn-lg"><i
                                                class="fas fa-hand-holding-usd"></i><br><strong>Bayar Pajak</strong></a>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>

                        <!-- RECENT DATA -->
                        <hr class="section-divider">
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-muted"><i class="fas fa-history text-warning me-2"></i> Data
                                Terbaru</h5>
                            <hr class="flex-grow-1 ms-3" style="border-color:#e3e6f0">
                        </div>
                        <div class="row g-3 mb-3">
                            <!-- 5 Vendor Terbaru -->
                            <div class="col-lg-4">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3 bg-primary text-white">
                                        <h6 class="m-0 fw-bold"><i class="fas fa-truck"></i> 5 Vendor Terbaru</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (!empty($recent_vendors)):
                                            foreach ($recent_vendors as $v): ?>
                                                <div class="recent-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?= htmlspecialchars($v->nama_vendor) ?></strong><br>
                                                            <small class="text-muted"><i class="fas fa-hashtag"></i>
                                                                <?= htmlspecialchars($v->kode) ?> | <i class="fas fa-user"></i>
                                                                <?= htmlspecialchars($v->pic_vendor) ?></small>
                                                        </div>
                                                        <a href="<?= base_url('vendorr/detail/' . $v->kode) ?>"
                                                            class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                                    </div>
                                                </div>
                                            <?php endforeach; else: ?>
                                            <div class="text-center py-4 text-muted"><i
                                                    class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                <p>Belum ada data vendor</p>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                    <div class="card-footer text-center"><a href="<?= base_url('vendorr') ?>"
                                            class="text-primary">Lihat Semua Vendor <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <!-- 5 Customer Terbaru -->
                            <div class="col-lg-4">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3 bg-success text-white">
                                        <h6 class="m-0 fw-bold"><i class="fas fa-users"></i> 5 Customer Terbaru</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (!empty($recent_customers)):
                                            foreach ($recent_customers as $c): ?>
                                                <div class="recent-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?= htmlspecialchars($c->nama) ?></strong><br>
                                                            <small class="text-muted"><i class="fas fa-hashtag"></i>
                                                                <?= htmlspecialchars($c->kode) ?>            <?= !empty($c->nama_npwp) ? ' | NPWP: ' . htmlspecialchars($c->nama_npwp) : '' ?></small>
                                                        </div>
                                                        <a href="<?= base_url('customer/detail/' . $c->id) ?>"
                                                            class="btn btn-sm btn-outline-success"><i class="fas fa-eye"></i></a>
                                                    </div>
                                                </div>
                                            <?php endforeach; else: ?>
                                            <div class="text-center py-4 text-muted"><i
                                                    class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                <p>Belum ada data customer</p>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                    <div class="card-footer text-center"><a href="<?= base_url('customer') ?>"
                                            class="text-success">Lihat Semua Customer <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <!-- 5 Invoice Terbaru -->
                            <div class="col-lg-4">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3 bg-warning text-white">
                                        <h6 class="m-0 fw-bold"><i class="fas fa-file-invoice"></i> 5 Invoice Terbaru</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (!empty($recent_invoices)):
                                            foreach ($recent_invoices as $inv): ?>
                                                <div class="recent-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?= htmlspecialchars($inv->no_invoice) ?></strong><br>
                                                            <small class="text-muted"><?= htmlspecialchars($inv->customer_nama) ?> |
                                                                Rp <?= number_format($inv->grand_total, 0, ',', '.') ?></small>
                                                        </div>
                                                        <span
                                                            class="badge bg-<?= $inv->status == 'paid' ? 'success' : 'warning' ?>"><?= strtoupper($inv->status) ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; else: ?>
                                            <div class="text-center py-4 text-muted"><i
                                                    class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                <p>Belum ada invoice</p>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                    <div class="card-footer text-center"><a href="<?= base_url('invoice_tsc') ?>"
                                            class="text-warning">Lihat Semua Invoice <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QUICK ACCESS -->
                        <hr class="section-divider">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-link"></i> Quick Access</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-md-3 col-6"><a href="<?= base_url('invoice_tsc/tambah') ?>"
                                            class="btn btn-success w-100"><i class="fas fa-plus-circle"></i> Buat
                                            Invoice</a></div>
                                    <div class="col-md-3 col-6"><a href="<?= base_url('pemasukan') ?>"
                                            class="btn btn-info w-100"><i class="fas fa-arrow-down"></i> Pemasukan</a></div>
                                    <div class="col-md-3 col-6"><a href="<?= base_url('pengeluaran') ?>"
                                            class="btn btn-danger w-100"><i class="fas fa-arrow-up"></i> Pengeluaran</a>
                                    </div>
                                    <div class="col-md-3 col-6"><a href="<?= base_url('laporan_keuangan') ?>"
                                            class="btn btn-primary w-100"><i class="fas fa-chart-line"></i> Laporan
                                            Keuangan</a></div>
                                    <div class="col-md-3 col-6"><a href="<?= base_url('pembayaran_pajak') ?>"
                                            class="btn btn-warning w-100"><i class="fas fa-hand-holding-usd"></i> Pembayaran
                                            Pajak</a></div>
                                    <div class="col-md-3 col-6"><a href="<?= base_url('unit') ?>"
                                            class="btn btn-secondary w-100"><i class="fas fa-truck"></i> Unit Kendaraan</a>
                                    </div>
                                    <div class="col-md-3 col-6"><a href="<?= base_url('pengguna') ?>"
                                            class="btn btn-dark w-100"><i class="fas fa-user-shield"></i> Kelola
                                            Pengguna</a></div>
                                    <div class="col-md-3 col-6"><a href="<?= base_url('akunbiaya') ?>"
                                            class="btn btn-outline-primary w-100"><i class="fas fa-book"></i> Akun Biaya</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($level == 'finance_staff'): ?>
                        <!-- ╔══════════════════════════════════════════════════════╗
                     ║  FINANCE STAFF DASHBOARD                            ║
                     ╚══════════════════════════════════════════════════════╝ -->

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h1 class="page-title mb-0"><i class="fas fa-calculator text-success"></i> Finance Dashboard
                            </h1>
                            <span class="text-muted"><i class="far fa-clock"></i> <span id="dashboard-time"></span></span>
                        </div>

                        <div class="finance-summary shadow mb-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h4 class="mb-1"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($nama) ?></h4>
                                    <p class="mb-0">Finance Staff &mdash; TSC Core System</p>
                                </div>
                                <div class="text-end">
                                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Hari Ini</h5>
                                    <p class="mb-0" id="finance-date"></p>
                                </div>
                            </div>
                        </div>

                        <?php
                        $CI2 = &get_instance();
                        $CI2->load->config('accounting');
                        $tax_accounts2 = $CI2->config->item('tax_accounts') ?? [];
                        $pph23_2 = $CI2->db->where('kode_perkiraan', $tax_accounts2['pph23'] ?? '51')->get('tb_akunbiaya')->row();
                        $pph42_2 = $CI2->db->where('kode_perkiraan', $tax_accounts2['pph42'] ?? '52')->get('tb_akunbiaya')->row();
                        $ppn_2 = $CI2->db->where('kode_perkiraan', $tax_accounts2['ppn_keluaran'] ?? '53')->get('tb_akunbiaya')->row();

                        $tax_c2 = [];
                        $tax_p2 = [];
                        for ($i = 0; $i < 6; $i++) {
                            $cd = strtotime("-$i months");
                            $cy = date('Y', $cd);
                            $cm = date('m', $cd);
                            $dl = date('Y-m-15', strtotime("$cy-$cm-01 +1 month"));
                            foreach ([
                                ['akun' => $pph23_2, 'type' => 'PPH 23', 'color' => 'danger'],
                                ['akun' => $pph42_2, 'type' => 'PPH 4(2)', 'color' => 'warning'],
                                ['akun' => $ppn_2, 'type' => 'PPN Keluaran', 'color' => 'success'],
                            ] as $t) {
                                if (!$t['akun'])
                                    continue;
                                $bal = get_tax_balance_for_period($CI2, $t['akun']->id, $cy, $cm);
                                if ($bal <= 0)
                                    continue;
                                $row = ['type' => $t['type'], 'periode' => date('F Y', $cd), 'balance' => $bal, 'deadline' => date('d M Y', strtotime($dl)), 'color' => $t['color'], 'year_month' => "$cy-$cm", 'overdue_months' => $i];
                                if ($i == 0)
                                    $tax_c2[] = $row;
                                else
                                    $tax_p2[] = $row;
                            }
                        }
                        $tcp2 = array_sum(array_column($tax_c2, 'balance'));
                        $tpp2 = array_sum(array_column($tax_p2, 'balance'));
                        $tall2 = $tcp2 + $tpp2;
                        ?>

                        <?php if (!empty($tax_c2)): ?>
                            <div class="alert alert-danger alert-dismissible fade show shadow alert-pulse mb-3"
                                style="overflow:hidden">
                                <div class="d-flex align-items-start">
                                    <div class="me-3"><i class="fas fa-exclamation-triangle fa-3x"></i></div>
                                    <div class="flex-grow-1" style="min-width:0;overflow:hidden">
                                        <h5 class="alert-heading mb-2"><strong>⚠️ PAJAK BULAN INI BELUM DIBAYAR!</strong></h5>
                                        <p class="mb-2">Ada <strong><?= count($tax_c2) ?></strong> jenis pajak untuk periode
                                            <strong><?= date('F Y') ?></strong>:</p>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                <thead class="bg-danger text-white">
                                                    <tr>
                                                        <th>Jenis Pajak</th>
                                                        <th>Periode</th>
                                                        <th class="text-end">Nominal</th>
                                                        <th>Batas</th>
                                                        <th>Status</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($tax_c2 as $tax): ?>
                                                        <tr>
                                                            <td><span
                                                                    class="badge bg-<?= $tax['color'] ?>"><?= $tax['type'] ?></span>
                                                            </td>
                                                            <td><strong><?= $tax['periode'] ?></strong></td>
                                                            <td class="text-end"><strong class="text-danger">Rp
                                                                    <?= number_format($tax['balance'], 0, ',', '.') ?></strong></td>
                                                            <td><?= $tax['deadline'] ?></td>
                                                            <td><span class="badge bg-danger">Jatuh Tempo Bulan Ini</span></td>
                                                            <td><a href="<?= base_url('pembayaran_pajak?periode=' . $tax['year_month']) ?>"
                                                                    class="btn btn-sm btn-danger"><i
                                                                        class="fas fa-money-bill-wave"></i> Bayar</a></td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="2" class="text-end">TOTAL:</th>
                                                        <th class="text-end text-danger">Rp
                                                            <?= number_format($tcp2, 0, ',', '.') ?></th>
                                                        <th colspan="3"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>

                        <?php if (!empty($tax_p2)): ?>
                            <div class="alert alert-warning alert-dismissible fade show shadow mb-3" style="overflow:hidden">
                                <div class="d-flex align-items-start">
                                    <div class="me-3"><i class="fas fa-history fa-3x"></i></div>
                                    <div class="flex-grow-1" style="min-width:0;overflow:hidden">
                                        <h5 class="alert-heading mb-2"><strong>📋 Pajak Bulan Sebelumnya Belum Dibayar</strong>
                                        </h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                <thead class="bg-warning">
                                                    <tr>
                                                        <th>Jenis</th>
                                                        <th>Periode</th>
                                                        <th class="text-end">Nominal</th>
                                                        <th>Deadline</th>
                                                        <th>Status</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($tax_p2 as $tax): ?>
                                                        <tr>
                                                            <td><span
                                                                    class="badge bg-<?= $tax['color'] ?>"><?= $tax['type'] ?></span>
                                                            </td>
                                                            <td><strong><?= $tax['periode'] ?></strong></td>
                                                            <td class="text-end"><strong class="text-warning">Rp
                                                                    <?= number_format($tax['balance'], 0, ',', '.') ?></strong></td>
                                                            <td><?= $tax['deadline'] ?></td>
                                                            <td><span class="badge bg-danger">Overdue (<?= $tax['overdue_months'] ?>
                                                                    bln)</span></td>
                                                            <td><a href="<?= base_url('pembayaran_pajak?periode=' . $tax['year_month']) ?>"
                                                                    class="btn btn-sm btn-warning"><i
                                                                        class="fas fa-money-bill-wave"></i> Bayar</a></td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <th colspan="2" class="text-end">TOTAL:</th>
                                                        <th class="text-end text-warning">Rp
                                                            <?= number_format($tpp2, 0, ',', '.') ?></th>
                                                        <th colspan="3"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>

                        <?php if (!empty($tax_c2) && !empty($tax_p2)): ?>
                            <div class="card shadow mb-3" style="border-left:4px solid #e74a3b">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                        <div>
                                            <h5 class="text-danger mb-2"><strong>TOTAL PAJAK BELUM DIBAYAR</strong></h5>
                                            <h3 class="text-danger mt-2 mb-0">Rp <?= number_format($tall2, 0, ',', '.') ?></h3>
                                            <small class="text-muted">Bulan Ini: Rp <?= number_format($tcp2, 0, ',', '.') ?> |
                                                Bulan Lalu: Rp <?= number_format($tpp2, 0, ',', '.') ?></small>
                                        </div>
                                        <a href="<?= base_url('pembayaran_pajak') ?>" class="btn btn-danger btn-lg"><i
                                                class="fas fa-hand-holding-usd"></i><br><strong>Bayar Pajak</strong></a>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if (!empty($overdue_invoices)): ?>
                            <div class="alert alert-danger alert-dismissible fade show shadow alert-pulse mb-3"
                                style="overflow:hidden">
                                <div class="d-flex align-items-start">
                                    <div class="me-3"><i class="fas fa-exclamation-circle fa-3x"></i></div>
                                    <div class="flex-grow-1" style="min-width:0;overflow:hidden">
                                        <h5 class="alert-heading mb-2"><strong>⚠️ INVOICE OVERDUE!</strong></h5>
                                        <p class="mb-2"><strong><?= count($overdue_invoices) ?></strong> invoice sudah lewat
                                            jatuh tempo.</p>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0 invoice-alert-table">
                                                <thead class="bg-danger text-white">
                                                    <tr>
                                                        <th>No. Invoice</th>
                                                        <th class="d-none d-sm-table-cell">Customer</th>
                                                        <th>Jatuh Tempo</th>
                                                        <th>Terlambat</th>
                                                        <th class="text-end">Nominal</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($overdue_invoices as $inv): ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($inv->no_invoice) ?></strong></td>
                                                            <td class="d-none d-sm-table-cell">
                                                                <?= htmlspecialchars($inv->customer_nama) ?></td>
                                                            <td><?= date('d/m/Y', strtotime($inv->due_date)) ?></td>
                                                            <td><span class="badge bg-danger"><?= $inv->days_overdue ?> hari</span>
                                                            </td>
                                                            <td class="text-end"><strong>Rp
                                                                    <?= number_format($inv->grand_total, 0, ',', '.') ?></strong></td>
                                                            <td><a href="<?= base_url('invoice_tsc/detail/' . $inv->id) ?>"
                                                                    class="btn btn-sm btn-outline-primary"><i
                                                                        class="fas fa-eye"></i></a></td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>

                        <?php if (!empty($upcoming_due_invoices)): ?>
                            <div class="alert alert-warning alert-dismissible fade show shadow mb-3" style="overflow:hidden">
                                <div class="d-flex align-items-start">
                                    <div class="me-3"><i class="fas fa-clock fa-3x"></i></div>
                                    <div class="flex-grow-1" style="min-width:0;overflow:hidden">
                                        <h5 class="alert-heading mb-2"><strong>🔔 Invoice Mendekati Jatuh Tempo</strong></h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered bg-white mb-0 invoice-alert-table">
                                                <thead class="bg-warning">
                                                    <tr>
                                                        <th>No. Invoice</th>
                                                        <th class="d-none d-sm-table-cell">Customer</th>
                                                        <th>Jatuh Tempo</th>
                                                        <th>Sisa</th>
                                                        <th class="text-end">Nominal</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($upcoming_due_invoices as $inv): ?>
                                                        <tr>
                                                            <td><strong><?= htmlspecialchars($inv->no_invoice) ?></strong></td>
                                                            <td class="d-none d-sm-table-cell">
                                                                <?= htmlspecialchars($inv->customer_nama) ?></td>
                                                            <td><?= date('d/m/Y', strtotime($inv->due_date)) ?></td>
                                                            <td>
                                                                <?php if ($inv->days_left == 0): ?><span class="badge bg-danger">Hari
                                                                        Ini!</span>
                                                                <?php elseif ($inv->days_left == 1): ?><span
                                                                        class="badge bg-warning text-dark">Besok (H-1)</span>
                                                                <?php else: ?><span class="badge bg-info"><?= $inv->days_left ?>
                                                                        hari lagi</span>
                                                                <?php endif ?>
                                                            </td>
                                                            <td class="text-end"><strong>Rp
                                                                    <?= number_format($inv->grand_total, 0, ',', '.') ?></strong></td>
                                                            <td><a href="<?= base_url('invoice_tsc/detail/' . $inv->id) ?>"
                                                                    class="btn btn-sm btn-outline-primary"><i
                                                                        class="fas fa-eye"></i></a></td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>

                        <!-- Finance Statistics -->
                        <div class="row g-3 mb-3">
                            <div class="col-xl-3 col-md-6">
                                <div class="card stats-card shadow h-100 py-2" style="border-left-color:#1cc88a">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Total Customer
                                                </div>
                                                <div class="h5 mb-0 fw-bold"><?= number_format($total_customer ?? 0) ?></div>
                                                <small class="text-muted">Customer aktif</small>
                                            </div>
                                            <i class="fas fa-users stats-icon text-success"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0"><a href="<?= base_url('customer') ?>"
                                            class="btn btn-sm btn-success w-100"><i class="fas fa-eye"></i> Lihat Data</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card stats-card shadow h-100 py-2" style="border-left-color:#4e73df">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Vendor
                                                </div>
                                                <div class="h5 mb-0 fw-bold"><?= number_format($total_vendor ?? 0) ?></div>
                                                <small class="text-muted">Vendor aktif</small>
                                            </div>
                                            <i class="fas fa-truck stats-icon text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0"><a href="<?= base_url('vendorr') ?>"
                                            class="btn btn-sm btn-primary w-100"><i class="fas fa-eye"></i> Lihat Data</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card stats-card shadow h-100 py-2" style="border-left-color:#f6c23e">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Invoice Bulan
                                                    Ini</div>
                                                <div class="h5 mb-0 fw-bold"><?= number_format($total_invoice_month ?? 0) ?>
                                                </div><small class="text-muted">Invoice TSC</small>
                                            </div>
                                            <i class="fas fa-file-invoice stats-icon text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0"><a href="<?= base_url('invoice_tsc') ?>"
                                            class="btn btn-sm btn-warning w-100"><i class="fas fa-eye"></i> Lihat
                                            Invoice</a></div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card stats-card shadow h-100 py-2" style="border-left-color:#e74a3b">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="text-xs fw-bold text-danger text-uppercase mb-1">Outstanding
                                                </div>
                                                <div class="h6 mb-0 fw-bold">Rp
                                                    <?= number_format($total_outstanding ?? 0, 0, ',', '.') ?></div><small
                                                    class="text-muted">Invoice belum dibayar</small>
                                            </div>
                                            <i class="fas fa-exclamation-triangle stats-icon text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0"><a href="<?= base_url('invoice_tsc') ?>"
                                            class="btn btn-sm btn-danger w-100"><i class="fas fa-eye"></i> Lihat Detail</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions Finance -->
                        <div class="card shadow mb-3">
                            <div class="card-header py-3 bg-success text-white">
                                <h6 class="m-0 fw-bold"><i class="fas fa-bolt"></i> Quick Actions — Finance</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-md-4 col-6"><a href="<?= base_url('invoice_tsc/tambah') ?>"
                                            class="btn btn-success btn-lg w-100"><i
                                                class="fas fa-plus-circle"></i><br><strong>Buat Invoice</strong></a></div>
                                    <div class="col-md-4 col-6"><a href="<?= base_url('pemasukan') ?>"
                                            class="btn btn-info btn-lg w-100"><i
                                                class="fas fa-arrow-down"></i><br><strong>Pemasukan</strong></a></div>
                                    <div class="col-md-4 col-6"><a href="<?= base_url('pengeluaran') ?>"
                                            class="btn btn-danger btn-lg w-100"><i
                                                class="fas fa-arrow-up"></i><br><strong>Pengeluaran</strong></a></div>
                                    <div class="col-md-4 col-6"><a href="<?= base_url('laporan_keuangan') ?>"
                                            class="btn btn-primary btn-lg w-100"><i
                                                class="fas fa-chart-line"></i><br><strong>Laporan Keuangan</strong></a>
                                    </div>
                                    <div class="col-md-4 col-6"><a href="<?= base_url('customer') ?>"
                                            class="btn btn-warning btn-lg w-100"><i
                                                class="fas fa-users"></i><br><strong>Kelola Customer</strong></a></div>
                                    <div class="col-md-4 col-6"><a href="<?= base_url('vendorr') ?>"
                                            class="btn btn-secondary btn-lg w-100"><i
                                                class="fas fa-truck"></i><br><strong>Kelola Vendor</strong></a></div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3 bg-warning text-white">
                                        <h6 class="m-0 fw-bold"><i class="fas fa-file-invoice"></i> 5 Invoice Terbaru</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (!empty($recent_invoices)):
                                            foreach ($recent_invoices as $inv): ?>
                                                <div class="recent-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?= htmlspecialchars($inv->no_invoice) ?></strong><br>
                                                            <small class="text-muted"><?= htmlspecialchars($inv->customer_nama) ?> |
                                                                Rp <?= number_format($inv->grand_total, 0, ',', '.') ?></small>
                                                        </div>
                                                        <span
                                                            class="badge bg-<?= $inv->status == 'paid' ? 'success' : 'warning' ?>"><?= strtoupper($inv->status) ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; else: ?>
                                            <div class="text-center py-4 text-muted"><i
                                                    class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                <p>Belum ada invoice</p>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                    <div class="card-footer text-center"><a href="<?= base_url('invoice_tsc') ?>"
                                            class="text-warning">Lihat Semua Invoice <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card shadow h-100">
                                    <div class="card-header py-3 bg-danger text-white">
                                        <h6 class="m-0 fw-bold"><i class="fas fa-tasks"></i> Task Pending</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i>
                                            <strong><?= $total_outstanding_invoices ?? 0 ?></strong> invoice belum dibayar
                                        </div>
                                        <div class="alert alert-info"><i class="fas fa-info-circle"></i>
                                            <strong><?= $total_unpaid_bills ?? 0 ?></strong> tagihan vendor pending</div>
                                        <div class="alert alert-success mb-0"><i class="fas fa-check-circle"></i> Semua
                                            transaksi hari ini sudah tercatat</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- ╔══════════════════════════════════════════════════════╗
                     ║  OTHER ROLES — Welcome Card                         ║
                     ╚══════════════════════════════════════════════════════╝ -->
                        <div class="row justify-content-center mt-5">
                            <div class="col-lg-6">
                                <div class="card shadow text-center">
                                    <div class="card-body py-5">
                                        <i class="fas fa-home fa-4x text-primary mb-4"></i>
                                        <h2 class="mb-3">Selamat Datang!</h2>
                                        <h4 class="text-primary mb-3"><?= htmlspecialchars($nama) ?></h4>
                                        <p class="lead">di <strong>TSC Core System</strong></p>
                                        <hr>
                                        <p class="text-muted">Level Akses: <span
                                                class="badge bg-primary"><?= strtoupper($level) ?></span></p>
                                        <p class="text-muted"><i class="far fa-clock"></i> <span id="simple-time"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                    <!-- ════════════════════════════════════════════════════
                   ORG CHART WIDGET — muncul di semua role yang diizinkan
                ════════════════════════════════════════════════════ -->
                    <?php if (!empty($show_orgchart) && !empty($orgchart_tree)): ?>
                        <hr class="section-divider mt-4">
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="fw-bold mb-0 text-muted">
                                <i class="fas fa-sitemap text-primary me-2"></i> Struktur Organisasi
                            </h5>
                            <hr class="flex-grow-1 ms-3" style="border-color:#e3e6f0">
                            <a href="<?= base_url('org_chart') ?>" class="btn btn-outline-primary btn-sm ms-3">
                                <i class="fas fa-expand-alt me-1"></i> Lihat Penuh
                            </a>
                            <?php if (in_array($level, ['superadmin', 'head_of_departemen'])): ?>
                                <a href="<?= base_url('org_chart/manage') ?>" class="btn btn-outline-secondary btn-sm ms-2">
                                    <i class="fas fa-edit me-1"></i> Kelola
                                </a>
                            <?php endif ?>
                        </div>
                        <div class="card shadow-sm mb-4">
                            <div class="card-body p-0">
                                <div class="orgw-wrap">
                                    <div id="orgDashTree" style="display:flex;flex-direction:column;align-items:center">
                                        <?php _render_orgw($orgchart_tree, 0); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>

                </div><!-- /container-xl -->
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        // ── Auto dismiss welcome alert ──
        setTimeout(function () {
            const el = document.getElementById('welcome-alert');
            if (el) { el.classList.add('fade-out'); setTimeout(() => el.style.display = 'none', 500); }
        }, 5000);

        // ── Live clock ──
        function updateDashboardTime() {
            const s = new Date().toLocaleDateString('id-ID', {
                day: '2-digit', month: 'long', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
            });
            ['dashboard-time', 'finance-date', 'simple-time'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = s;
            });
        }
        setInterval(updateDashboardTime, 1000);
        updateDashboardTime();
    </script>
</body>

</html>
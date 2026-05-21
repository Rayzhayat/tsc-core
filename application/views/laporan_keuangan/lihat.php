<!-- lihat.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .tooltip-icon {
            cursor: help;
            color: #36b9cc;
            margin-left: 4px;
        }

        .tooltip-icon:hover {
            color: #2e59d9;
        }

        .cogs-highlight {
            background-color: #fff3cd !important;
            border-left: 4px solid #ffc107;
        }

        .info-card {
            border-left: 4px solid;
            transition: transform .2s, box-shadow .2s;
        }

        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, .1) !important;
        }

        .info-card.success {
            border-color: #1cc88a;
        }

        .info-card.danger {
            border-color: #e74a3b;
        }

        .info-card.primary {
            border-color: #4e73df;
        }

        .badge-in {
            background: #1cc88a;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: .78rem;
        }

        .badge-out {
            background: #e74a3b;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: .78rem;
        }

        .tipe-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: .82rem;
            letter-spacing: .4px;
        }

        .tipe-header td {
            padding: 8px 12px !important;
        }

        .tipe-header-ocas {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: .82rem;
        }

        .tipe-header-ocas td {
            padding: 8px 12px !important;
        }

        .table thead th {
            background-color: #4e73df;
            color: #fff;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: .78rem;
            letter-spacing: .4px;
        }

        .saldo-positif {
            color: #1cc88a;
            font-weight: 700;
        }

        .saldo-negatif {
            color: #e74a3b;
            font-weight: 700;
        }

        .nominal-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        .filter-badge {
            background: #4e73df;
            color: #fff;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .8rem;
            margin-left: 8px;
        }

        .ocas-alert {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #f5576c;
            margin: 0;
            padding: 12px 16px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <!-- PAGE HEADER -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-file-invoice-dollar text-primary me-2"></i><?= $title ?>
                                <span class="filter-badge">
                                    <?= date('d M Y', strtotime($start_date)) ?> –
                                    <?= date('d M Y', strtotime($end_date)) ?>
                                </span>
                            </h2>
                            <small class="text-muted">Ringkasan arus kas &amp; saldo akun</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2 no-print">
                            <a href="<?= base_url('laporan_keuangan/laba_rugi?tanggal_awal=' . $start_date . '&tanggal_akhir=' . $end_date) ?>"
                                class="btn btn-success btn-sm">
                                <i class="fas fa-chart-line me-1"></i> Laba Rugi
                            </a>
                            <a href="<?= base_url('laporan_keuangan/neraca?tanggal=' . $end_date) ?>"
                                class="btn btn-info btn-sm">
                                <i class="fas fa-balance-scale me-1"></i> Neraca
                            </a>
                            <a href="<?= base_url('laporan_keuangan/arus_kas?tanggal_awal=' . $start_date . '&tanggal_akhir=' . $end_date) ?>"
                                class="btn btn-warning btn-sm">
                                <i class="fas fa-money-bill-wave me-1"></i> Arus Kas
                            </a>
                            <button class="btn btn-secondary btn-sm" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>

                    <!-- FLASH -->
                    <?php foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $k => $cls): ?>
                        <?php if ($this->session->flashdata($k)): ?>
                            <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                                <?= $this->session->flashdata($k) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>
                    <?php endforeach ?>

                    <!-- FILTER CARD -->
                    <div class="card shadow-sm mb-3 no-print">
                        <div class="card-header py-2 bg-primary text-white">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-filter me-1"></i> Filter Periode
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <form method="get" action="<?= base_url('laporan_keuangan') ?>" id="filterForm">
                                <!-- Quick buttons -->
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span class="text-muted small me-1 align-self-center">Quick:</span>
                                    <?php
                                    $qbtns = ['Hari Ini' => 'setToday()', 'Minggu Ini' => 'setThisWeek()', 'Bulan Ini' => 'setThisMonth()', 'Bulan Lalu' => 'setLastMonth()', 'Tahun Ini' => 'setThisYear()'];
                                    foreach ($qbtns as $lbl => $fn): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="<?= $fn ?>"><?= $lbl ?></button>
                                    <?php endforeach ?>
                                </div>
                                <!-- Date inputs -->
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold mb-1 small">
                                            <i class="fas fa-calendar-alt me-1"></i> Tanggal Mulai
                                        </label>
                                        <input type="date" name="start_date" id="start_date"
                                            class="form-control form-control-sm" value="<?= $start_date ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold mb-1 small">
                                            <i class="fas fa-calendar-check me-1"></i> Tanggal Akhir
                                        </label>
                                        <input type="date" name="end_date" id="end_date"
                                            class="form-control form-control-sm" value="<?= $end_date ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-search me-1"></i> Tampilkan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- INFO CARDS -->
                    <div class="row g-3 mb-3">
                        <div class="col-xl-4 col-md-6">
                            <div class="card info-card success shadow-sm h-100">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-success text-uppercase fw-semibold mb-1"
                                                style="font-size:.72rem;letter-spacing:.5px">Total Pemasukan (Cash In)
                                            </div>
                                            <div class="fs-5 fw-bold nominal-cell">Rp
                                                <?= number_format($total_in, 0, ',', '.') ?></div>
                                        </div>
                                        <i class="fas fa-arrow-down fa-2x text-success opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="card info-card danger shadow-sm h-100">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-danger text-uppercase fw-semibold mb-1"
                                                style="font-size:.72rem;letter-spacing:.5px">Total Pengeluaran (Cash
                                                Out)</div>
                                            <div class="fs-5 fw-bold nominal-cell">Rp
                                                <?= number_format($total_out, 0, ',', '.') ?></div>
                                        </div>
                                        <i class="fas fa-arrow-up fa-2x text-danger opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6">
                            <div class="card info-card primary shadow-sm h-100">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-primary text-uppercase fw-semibold mb-1"
                                                style="font-size:.72rem;letter-spacing:.5px">Net Cashflow</div>
                                            <div
                                                class="fs-5 fw-bold nominal-cell <?= $net_cashflow >= 0 ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($net_cashflow, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php if ($net_cashflow >= 0): ?>
                                                    <i class="fas fa-check-circle text-success me-1"></i>Surplus
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-circle text-danger me-1"></i>Defisit
                                                <?php endif ?>
                                            </small>
                                        </div>
                                        <i class="fas fa-balance-scale fa-2x text-primary opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABLE CARD -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header py-2 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="fas fa-table me-1"></i>
                                Laporan Keuangan
                                <strong><?= date('d/m/Y', strtotime($start_date)) ?></strong> s/d
                                <strong><?= date('d/m/Y', strtotime($end_date)) ?></strong>
                            </h6>
                            <small class="text-muted no-print">
                                <i class="fas fa-info-circle me-1"></i>
                                Klik <i class="fas fa-eye"></i> untuk detail transaksi
                            </small>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0" id="dataTable"
                                    style="font-size:.83rem;">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:45px">No</th>
                                            <th style="width:70px">Kode</th>
                                            <th>Nama Akun</th>
                                            <th class="text-end" style="width:130px">Saldo Awal</th>
                                            <th class="text-end" style="width:130px">Pemasukan</th>
                                            <th class="text-end" style="width:130px">Pengeluaran</th>
                                            <th class="text-end" style="width:130px">Saldo Akhir</th>
                                            <th class="text-end" style="width:120px">Mutasi</th>
                                            <th class="text-center no-print" style="width:60px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($grouped_summary)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-5 text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                    Tidak ada transaksi untuk periode ini.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $no = 1;
                                            $grand_total_saldo_awal = $grand_total_masuk = $grand_total_keluar = $grand_total_saldo_akhir = $grand_total_mutasi = 0;

                                            foreach ($grouped_summary as $tipe => $items):
                                                if ($tipe === 'OCAS')
                                                    continue;

                                                $subtotal_saldo_awal = $subtotal_masuk = $subtotal_keluar = $subtotal_saldo_akhir = $subtotal_mutasi = 0;
                                                $cogs_total_pph = $cogs_total_cash_out = 0;

                                                if ($tipe === 'COGS') {
                                                    foreach ($items as $item) {
                                                        $transactions = $this->db->select('no_transaksi')->from('tb_transaksi_keuangan')
                                                            ->where('akun_id', $item->id)->where('tanggal >=', $start_date)
                                                            ->where('tanggal <=', $end_date)->where('debit >', 0)->get()->result();
                                                        foreach ($transactions as $trx) {
                                                            $tax_accounts = $this->config->item('tax_accounts') ?? [];
                                                            $pph_kodes = array_values($tax_accounts);
                                                            if (!empty($pph_kodes)) {
                                                                $r = $this->db->select('SUM(t.kredit) as total_pph')
                                                                    ->from('tb_transaksi_keuangan t')
                                                                    ->join('tb_akunbiaya a', 't.akun_id = a.id')
                                                                    ->where('t.no_transaksi', $trx->no_transaksi)
                                                                    ->where_in('a.kode_perkiraan', $pph_kodes)
                                                                    ->where('t.kredit >', 0)->get()->row();
                                                                if ($r && $r->total_pph > 0)
                                                                    $cogs_total_pph += $r->total_pph;
                                                            }
                                                        }
                                                    }
                                                }

                                                $tipe_names = [
                                                    'BANK' => 'KAS & BANK',
                                                    'AREC' => 'PIUTANG',
                                                    'ASET' => 'ASET',
                                                    'LIAB' => 'LIABILITAS (UTANG)',
                                                    'EKUI' => 'EKUITAS',
                                                    'REVE' => 'PENDAPATAN',
                                                    'COGS' => 'HARGA POKOK PENJUALAN',
                                                    'EXPS' => 'BIAYA OPERASIONAL',
                                                    'OCLV' => 'OTHER CURRENT LIABILITIES (PPN/PPH)'
                                                ];
                                                ?>
                                                <!-- TIPE HEADER -->
                                                <tr class="tipe-header">
                                                    <td colspan="9">
                                                        <i class="fas fa-folder-open me-1"></i>
                                                        <?= $tipe_names[$tipe] ?? htmlspecialchars($tipe) ?>
                                                    </td>
                                                </tr>

                                                <?php foreach ($items as $item):
                                                    $mutasi = $item->total_debit - $item->total_kredit;
                                                    $subtotal_saldo_awal += $item->saldo_awal;
                                                    $subtotal_masuk += $item->total_masuk;
                                                    $subtotal_keluar += $item->total_keluar;
                                                    $subtotal_saldo_akhir += $item->saldo_akhir;
                                                    $subtotal_mutasi += $mutasi;

                                                    $akun_pph = 0;
                                                    $akun_cash_out = $item->saldo_akhir;
                                                    $is_cogs_account = in_array($item->kode_perkiraan, ['302', '303']);

                                                    if ($tipe === 'COGS' && $is_cogs_account) {
                                                        $transactions = $this->db->select('no_transaksi')->from('tb_transaksi_keuangan')
                                                            ->where('akun_id', $item->id)->where('tanggal >=', $start_date)
                                                            ->where('tanggal <=', $end_date)->where('debit >', 0)->get()->result();
                                                        foreach ($transactions as $trx) {
                                                            $tax_accounts = $this->config->item('tax_accounts') ?? [];
                                                            $pph_kodes = array_values($tax_accounts);
                                                            if (!empty($pph_kodes)) {
                                                                $r = $this->db->select('SUM(t.kredit) as total_pph')
                                                                    ->from('tb_transaksi_keuangan t')
                                                                    ->join('tb_akunbiaya a', 't.akun_id = a.id')
                                                                    ->where('t.no_transaksi', $trx->no_transaksi)
                                                                    ->where_in('a.kode_perkiraan', $pph_kodes)
                                                                    ->where('t.kredit >', 0)->get()->row();
                                                                if ($r && $r->total_pph > 0)
                                                                    $akun_pph += $r->total_pph;
                                                            }
                                                        }
                                                        $akun_cash_out = $item->saldo_akhir - $akun_pph;
                                                    }
                                                    ?>
                                                    <tr class="<?= $is_cogs_account ? 'cogs-highlight' : '' ?>">
                                                        <td class="text-center text-muted"><?= $no++ ?></td>
                                                        <td><strong><?= htmlspecialchars($item->kode_perkiraan) ?></strong></td>
                                                        <td>
                                                            <?= htmlspecialchars($item->nama_akun) ?>
                                                            <?php if ($is_cogs_account && $akun_pph > 0): ?>
                                                                <i class="fas fa-info-circle tooltip-icon" data-bs-toggle="tooltip"
                                                                    data-html="true" data-bs-placement="right"
                                                                    title="<div style='text-align:left'><strong>Breakdown:</strong><br>• Total: Rp <?= number_format($item->saldo_akhir, 0, ',', '.') ?><br>• Cash Out: Rp <?= number_format($akun_cash_out, 0, ',', '.') ?><br>• PPH: Rp <?= number_format($akun_pph, 0, ',', '.') ?></div>">
                                                                </i>
                                                            <?php endif ?>
                                                        </td>
                                                        <td class="text-end nominal-cell">Rp
                                                            <?= number_format($item->saldo_awal, 0, ',', '.') ?></td>
                                                        <td class="text-end">
                                                            <?= $item->total_masuk > 0
                                                                ? '<span class="badge-in nominal-cell">Rp ' . number_format($item->total_masuk, 0, ',', '.') . '</span>'
                                                                : '<span class="text-muted">&mdash;</span>' ?>
                                                        </td>
                                                        <td class="text-end">
                                                            <?= $item->total_keluar > 0
                                                                ? '<span class="badge-out nominal-cell">Rp ' . number_format($item->total_keluar, 0, ',', '.') . '</span>'
                                                                : '<span class="text-muted">&mdash;</span>' ?>
                                                        </td>
                                                        <td class="text-end nominal-cell">
                                                            <strong
                                                                class="<?= $item->saldo_akhir >= 0 ? 'saldo-positif' : 'saldo-negatif' ?>">
                                                                Rp <?= number_format($item->saldo_akhir, 0, ',', '.') ?>
                                                            </strong>
                                                        </td>
                                                        <td class="text-end nominal-cell">
                                                            <span class="<?= $mutasi >= 0 ? 'text-success' : 'text-danger' ?>">
                                                                <?= $mutasi >= 0 ? '+' : '' ?>Rp
                                                                <?= number_format(abs($mutasi), 0, ',', '.') ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center no-print">
                                                            <a href="<?= base_url('laporan_keuangan/detail_akun/' . $item->id . '?start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                                                class="btn btn-info btn-sm" data-bs-toggle="tooltip"
                                                                title="Detail Transaksi">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach ?>

                                                <!-- SUBTOTAL -->
                                                <tr class="fw-bold" style="background-color:#f8f9fc">
                                                    <td colspan="3" class="text-end">
                                                        <i class="fas fa-calculator me-1"></i>Subtotal
                                                        <?= htmlspecialchars($tipe) ?>
                                                        <?php if ($tipe === 'COGS'):
                                                            $cogs_total_cash_out = $subtotal_saldo_akhir - $cogs_total_pph;
                                                            if ($cogs_total_pph > 0): ?>
                                                                <a href="<?= base_url('reconciliation?start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                                                    class="btn btn-warning btn-sm ms-2 no-print" title="Rekonsiliasi">
                                                                    <i class="fas fa-balance-scale"></i>
                                                                </a>
                                                            <?php endif ?>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-end nominal-cell">Rp
                                                        <?= number_format($subtotal_saldo_awal, 0, ',', '.') ?></td>
                                                    <td class="text-end nominal-cell">
                                                        <?= $subtotal_masuk > 0 ? 'Rp ' . number_format($subtotal_masuk, 0, ',', '.') : '&mdash;' ?>
                                                    </td>
                                                    <td class="text-end nominal-cell">
                                                        <?= $subtotal_keluar > 0 ? 'Rp ' . number_format($subtotal_keluar, 0, ',', '.') : '&mdash;' ?>
                                                    </td>
                                                    <td class="text-end nominal-cell">
                                                        <strong
                                                            class="<?= $subtotal_saldo_akhir >= 0 ? 'saldo-positif' : 'saldo-negatif' ?>">
                                                            Rp <?= number_format($subtotal_saldo_akhir, 0, ',', '.') ?>
                                                        </strong>
                                                        <?php if ($tipe === 'COGS' && isset($cogs_total_pph) && $cogs_total_pph > 0): ?>
                                                            <i class="fas fa-question-circle tooltip-icon no-print"
                                                                data-bs-toggle="tooltip" data-html="true" data-bs-placement="left"
                                                                title="<div style='text-align:left'><strong>Total COGS: Rp <?= number_format($subtotal_saldo_akhir, 0, ',', '.') ?></strong><br><hr style='margin:4px 0'>• Cash Out: Rp <?= number_format($cogs_total_cash_out, 0, ',', '.') ?><br>• PPH: Rp <?= number_format($cogs_total_pph, 0, ',', '.') ?></div>">
                                                            </i>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-end nominal-cell">
                                                        <span
                                                            class="<?= $subtotal_mutasi >= 0 ? 'text-success' : 'text-danger' ?>">
                                                            <?= $subtotal_mutasi >= 0 ? '+' : '' ?>Rp
                                                            <?= number_format(abs($subtotal_mutasi), 0, ',', '.') ?>
                                                        </span>
                                                    </td>
                                                    <td class="no-print"></td>
                                                </tr>

                                                <?php
                                                $grand_total_saldo_awal += $subtotal_saldo_awal;
                                                $grand_total_masuk += $subtotal_masuk;
                                                $grand_total_keluar += $subtotal_keluar;
                                                $grand_total_saldo_akhir += $subtotal_saldo_akhir;
                                                $grand_total_mutasi += $subtotal_mutasi;
                                                ?>
                                            <?php endforeach ?>

                                            <?php
                                            // OCAS SECTION
                                            $ocas_items = $grouped_summary['OCAS'] ?? [];
                                            if (!empty($ocas_items)):
                                                $subtotal_ocas_awal = $subtotal_ocas_masuk = $subtotal_ocas_keluar = $subtotal_ocas_akhir = $subtotal_ocas_mutasi = 0;
                                                ?>
                                                <tr class="tipe-header-ocas">
                                                    <td colspan="9">
                                                        <i class="fas fa-file-invoice-dollar me-1"></i>
                                                        OCAS — PPH MEMOTONG (Hutang ke Negara)
                                                    </td>
                                                </tr>

                                                <?php foreach ($ocas_items as $item):
                                                    $mutasi = $item->total_debit - $item->total_kredit;
                                                    $subtotal_ocas_awal += $item->saldo_awal;
                                                    $subtotal_ocas_masuk += $item->total_masuk;
                                                    $subtotal_ocas_keluar += $item->total_keluar;
                                                    $subtotal_ocas_akhir += $item->saldo_akhir;
                                                    $subtotal_ocas_mutasi += $mutasi;
                                                    ?>
                                                    <tr>
                                                        <td class="text-center text-muted"><?= $no++ ?></td>
                                                        <td><strong><?= htmlspecialchars($item->kode_perkiraan) ?></strong></td>
                                                        <td>
                                                            <?= htmlspecialchars($item->nama_akun) ?>
                                                            <?php if ($item->kode_perkiraan == '51'): ?>
                                                                <span class="badge bg-warning ms-1">PPH 23</span>
                                                            <?php elseif ($item->kode_perkiraan == '52'): ?>
                                                                <span class="badge bg-info ms-1">PPH 4(2)</span>
                                                            <?php endif ?>
                                                        </td>
                                                        <td class="text-end nominal-cell">Rp
                                                            <?= number_format($item->saldo_awal, 0, ',', '.') ?></td>
                                                        <td class="text-end">
                                                            <?= $item->total_masuk > 0
                                                                ? '<span class="badge-in nominal-cell">Rp ' . number_format($item->total_masuk, 0, ',', '.') . '</span>'
                                                                : '<span class="text-muted">&mdash;</span>' ?>
                                                        </td>
                                                        <td class="text-end">
                                                            <?= $item->total_keluar > 0
                                                                ? '<span class="badge-out nominal-cell">Rp ' . number_format($item->total_keluar, 0, ',', '.') . '</span>'
                                                                : '<span class="text-muted">&mdash;</span>' ?>
                                                        </td>
                                                        <td class="text-end nominal-cell">
                                                            <strong class="text-warning">Rp
                                                                <?= number_format($item->saldo_akhir, 0, ',', '.') ?></strong>
                                                        </td>
                                                        <td class="text-end nominal-cell">
                                                            <span class="<?= $mutasi >= 0 ? 'text-success' : 'text-danger' ?>">
                                                                <?= $mutasi >= 0 ? '+' : '' ?>Rp
                                                                <?= number_format(abs($mutasi), 0, ',', '.') ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center no-print">
                                                            <a href="<?= base_url('laporan_keuangan/detail_akun/' . $item->id . '?start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                                                class="btn btn-info btn-sm" data-bs-toggle="tooltip"
                                                                title="Detail Transaksi">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach ?>

                                                <!-- SUBTOTAL OCAS -->
                                                <tr class="fw-bold" style="background-color:#fff3cd">
                                                    <td colspan="3" class="text-end">
                                                        <i class="fas fa-calculator me-1"></i>Subtotal OCAS
                                                    </td>
                                                    <td class="text-end nominal-cell">Rp
                                                        <?= number_format($subtotal_ocas_awal, 0, ',', '.') ?></td>
                                                    <td class="text-end nominal-cell">
                                                        <?= $subtotal_ocas_masuk > 0 ? 'Rp ' . number_format($subtotal_ocas_masuk, 0, ',', '.') : '&mdash;' ?>
                                                    </td>
                                                    <td class="text-end nominal-cell">
                                                        <?= $subtotal_ocas_keluar > 0 ? 'Rp ' . number_format($subtotal_ocas_keluar, 0, ',', '.') : '&mdash;' ?>
                                                    </td>
                                                    <td class="text-end nominal-cell">
                                                        <strong class="text-warning">Rp
                                                            <?= number_format($subtotal_ocas_akhir, 0, ',', '.') ?></strong>
                                                    </td>
                                                    <td class="text-end nominal-cell">
                                                        <span
                                                            class="<?= $subtotal_ocas_mutasi >= 0 ? 'text-success' : 'text-danger' ?>">
                                                            <?= $subtotal_ocas_mutasi >= 0 ? '+' : '' ?>Rp
                                                            <?= number_format(abs($subtotal_ocas_mutasi), 0, ',', '.') ?>
                                                        </span>
                                                    </td>
                                                    <td class="no-print"></td>
                                                </tr>

                                                <?php if ($subtotal_ocas_akhir > 0): ?>
                                                    <tr class="no-print">
                                                        <td colspan="9" style="padding:0;border:none;">
                                                            <div class="ocas-alert d-flex align-items-start gap-3">
                                                                <i class="fas fa-exclamation-triangle fa-2x text-danger mt-1"></i>
                                                                <div>
                                                                    <strong class="text-danger">
                                                                        <i class="fas fa-bell me-1"></i>PERHATIAN — PPH BELUM
                                                                        DISETOR:
                                                                    </strong><br>
                                                                    Terdapat PPH yang ditahan dan belum disetor ke negara sebesar
                                                                    <strong class="text-danger" style="font-size:1.05rem;">
                                                                        Rp <?= number_format($subtotal_ocas_akhir, 0, ',', '.') ?>
                                                                    </strong><br>
                                                                    <small class="text-muted">
                                                                        PPH ini harus disetor ke negara sesuai jadwal.
                                                                        Jurnal: DEBIT OCAS (51/52) | KREDIT Bank
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif ?>

                                                <?php
                                                $grand_total_saldo_awal += $subtotal_ocas_awal;
                                                $grand_total_masuk += $subtotal_ocas_masuk;
                                                $grand_total_keluar += $subtotal_ocas_keluar;
                                                $grand_total_saldo_akhir += $subtotal_ocas_akhir;
                                                $grand_total_mutasi += $subtotal_ocas_mutasi;
                                                ?>
                                            <?php endif ?>

                                            <!-- GRAND TOTAL -->
                                            <tr class="fw-bold"
                                                style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;font-size:1rem;">
                                                <td colspan="3" class="text-end">
                                                    <i class="fas fa-chart-line me-1"></i>GRAND TOTAL
                                                </td>
                                                <td class="text-end nominal-cell">Rp
                                                    <?= number_format($grand_total_saldo_awal, 0, ',', '.') ?></td>
                                                <td class="text-end nominal-cell">Rp
                                                    <?= number_format($grand_total_masuk, 0, ',', '.') ?></td>
                                                <td class="text-end nominal-cell">Rp
                                                    <?= number_format($grand_total_keluar, 0, ',', '.') ?></td>
                                                <td class="text-end nominal-cell">Rp
                                                    <?= number_format($grand_total_saldo_akhir, 0, ',', '.') ?></td>
                                                <td class="text-end nominal-cell">
                                                    <?= $grand_total_mutasi >= 0 ? '+' : '' ?>Rp
                                                    <?= number_format(abs($grand_total_mutasi), 0, ',', '.') ?>
                                                </td>
                                                <td class="no-print"></td>
                                            </tr>
                                        <?php endif ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- LEGENDS -->
                        <div class="card-footer py-3">
                            <small class="text-muted">
                                <strong>Keterangan:</strong><br>
                                <span class="badge-in me-2 mb-1" style="display:inline-block">Pemasukan</span>
                                <span class="badge-out me-2 mb-1" style="display:inline-block">Pengeluaran</span>
                                <span class="badge bg-warning me-2 mb-1">PPH 23</span>
                                <span class="badge bg-info me-2 mb-1">PPH 4(2)</span>
                                <br><br>
                                <i class="fas fa-info-circle text-primary me-1"></i>
                                <em>Kolom "Pemasukan" dan "Pengeluaran" hanya menampilkan transaksi cashflow (Kas &amp;
                                    Bank).
                                    Untuk akun lain (Piutang, Biaya, Pendapatan, OCAS), lihat kolom "Mutasi" dan "Saldo
                                    Akhir".</em><br>

                                <i class="fas fa-file-invoice-dollar text-warning me-1"></i>
                                <em><strong>OCAS PPH 23 (dipotong 2%)</strong> adalah PPH yang dipotong dari pembayaran
                                    Customer dan perlu di-collect Bukti Potongnya.</em><br>

                                <i class="fas fa-file-invoice-dollar text-secondary me-1"></i>
                                <em><strong>OCAS PPN Keluaran</strong> adalah PPN yang ditagihkan ke Customer dan perlu
                                    dibayarkan ke Negara.</em><br>

                                <i class="fas fa-receipt text-info me-1"></i>
                                <em><strong>PIUTANG USAHA</strong> dicatat sebagai: <strong>DPP + PPN &minus;
                                        PPH</strong></em><br>

                                <i class="fas fa-truck text-danger me-1"></i>
                                <em><strong>BIAYA VENDOR</strong> dicatat sebesar: <strong>DPP</strong> (tanpa PPN &amp;
                                    PPH)</em><br>

                                <i class="fas fa-chart-line text-success me-1"></i>
                                <em><strong>PENDAPATAN</strong> adalah DPP Penjualan <strong>Exclude PPN &amp;
                                        PPH</strong></em>
                            </small>
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
            setTimeout(() => $('.alert').fadeOut('slow'), 5000);
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        function formatDate(d) {
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        }
        function setToday() { const t = formatDate(new Date()); $('#start_date,#end_date').val(t); }
        function setThisWeek() { const t = new Date(), d = t.getDay(), f = new Date(t); f.setDate(t.getDate() + (d === 0 ? -6 : 1 - d)); const l = new Date(f); l.setDate(f.getDate() + 6); $('#start_date').val(formatDate(f)); $('#end_date').val(formatDate(l)); }
        function setThisMonth() { const t = new Date(); $('#start_date').val(formatDate(new Date(t.getFullYear(), t.getMonth(), 1))); $('#end_date').val(formatDate(new Date(t.getFullYear(), t.getMonth() + 1, 0))); }
        function setLastMonth() { const t = new Date(); $('#start_date').val(formatDate(new Date(t.getFullYear(), t.getMonth() - 1, 1))); $('#end_date').val(formatDate(new Date(t.getFullYear(), t.getMonth(), 0))); }
        function setThisYear() { const t = new Date(); $('#start_date').val(formatDate(new Date(t.getFullYear(), 0, 1))); $('#end_date').val(formatDate(new Date(t.getFullYear(), 11, 31))); }

        function exportToExcel() {
            const table = document.getElementById('dataTable').cloneNode(true);
            Array.from(table.rows).forEach(r => r.deleteCell(-1));
            const blob = new Blob(['\ufeff', table.outerHTML], { type: 'application/vnd.ms-excel' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'Laporan_Keuangan_<?= date('Ymd', strtotime($start_date)) ?>_<?= date('Ymd', strtotime($end_date)) ?>.xls';
            a.click();
        }
    </script>
</body>

</html>
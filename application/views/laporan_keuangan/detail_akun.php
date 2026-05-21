<!-- detail_akun.php - ENHANCED WITH EXPLANATION -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .info-card {
            border-left: 4px solid;
        }

        .info-card.success {
            border-color: #1cc88a;
        }

        .info-card.danger {
            border-color: #e74a3b;
        }

        .info-card.info {
            border-color: #36b9cc;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fc;
        }

        .akun-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        /* 🔥 NEW: Explanation Box Styling */
        .explanation-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .breakdown-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .breakdown-item:last-child {
            background: rgba(255, 255, 255, 0.3);
            font-weight: bold;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-chart-line text-primary"></i> <?= $title ?>
                        </h1>
                        <div>
                            <a href="<?= base_url('reconciliation?start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                class="btn btn-warning btn-sm shadow-sm mr-2">
                                <i class="fas fa-balance-scale"></i> Lihat Rekonsiliasi
                            </a>
                            <a href="<?= base_url('laporan_keuangan?start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Kembali ke Laporan
                            </a>
                        </div>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- 🔥 NEW: Explanation Box for COGS Accounts -->
                    <?php
                    // Hitung PPH amount dari OCAS jika ada
                    $pph_amount = 0;
                    $is_cogs = in_array($akun->kode_perkiraan, ['302', '303']); // Biaya Sewa, etc
                    
                    if ($is_cogs && !empty($transaksi)) {
                        // Cari PPH dari transaksi yang sama periode
                        $tax_accounts = $this->config->item('tax_accounts');
                        $pph_kodes = array_values($tax_accounts);

                        foreach ($transaksi as $item) {
                            if ($item->debit > 0) { // Transaksi biaya (DEBIT)
                                // Cek apakah ada PPH yang dipotong di transaksi yang sama
                                $same_transaction = $this->db
                                    ->select('SUM(kredit) as total_pph')
                                    ->from('tb_transaksi_keuangan t')
                                    ->join('tb_akunbiaya a', 't.akun_id = a.id')
                                    ->where('t.no_transaksi', $item->no_transaksi)
                                    ->where_in('a.kode_perkiraan', $pph_kodes)
                                    ->where('t.kredit >', 0)
                                    ->get()
                                    ->row();

                                if ($same_transaction && $same_transaction->total_pph > 0) {
                                    $pph_amount += $same_transaction->total_pph;
                                }
                            }
                        }
                    }

                    $cash_out = $saldo_akhir - $pph_amount;
                    ?>

                    <?php if ($is_cogs && $pph_amount > 0): ?>
                        <div class="explanation-box">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5><i class="fas fa-graduation-cap"></i> Penjelasan: Biaya vs Cash Out</h5>
                                    <p class="mb-0">
                                        <small>
                                            Akun ini mencatat <strong>full economic cost</strong> perusahaan,
                                            sedangkan cash yang keluar dari bank lebih kecil karena PPH dipotong.
                                        </small>
                                    </p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button class="btn btn-light btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#breakdownModal">
                                        <i class="fas fa-chart-pie"></i> Lihat Breakdown
                                    </button>
                                </div>
                            </div>

                            <hr style="border-color: rgba(255,255,255,0.3);">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="breakdown-item">
                                        <small>Total Biaya (COGS)</small>
                                        <h5 class="mb-0">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></h5>
                                        <small><i class="fas fa-info-circle"></i> Nominal + PPN</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="breakdown-item">
                                        <small>Cash Out Bank</small>
                                        <h5 class="mb-0">Rp <?= number_format($cash_out, 0, ',', '.') ?></h5>
                                        <small><i class="fas fa-university"></i> Yang dibayar ke vendor</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="breakdown-item">
                                        <small>PPH Dipotong (OCAS)</small>
                                        <h5 class="mb-0">Rp <?= number_format($pph_amount, 0, ',', '.') ?></h5>
                                        <small><i class="fas fa-hand-holding-usd"></i> Belum bayar ke negara</small>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-light mt-3 mb-0">
                                <small>
                                    <i class="fas fa-lightbulb"></i> <strong>Catatan:</strong>
                                    Saat PPH dibayar ke negara (via menu
                                    <a href="<?= base_url('pembayaran_pajak') ?>" class="text-primary"><u>Pembayaran
                                            Pajak</u></a>),
                                    total cash out akan sama dengan Total Biaya = <strong>Rp
                                        <?= number_format($saldo_akhir, 0, ',', '.') ?></strong> ✓
                                </small>
                            </div>
                        </div>
                    <?php endif ?>

                    <!-- Modal Breakdown Detail -->
                    <div class="modal fade" id="breakdownModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-chart-pie"></i> Breakdown Detail:
                                        <?= htmlspecialchars($akun->nama) ?>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <h6 class="text-primary">
                                        <i class="fas fa-question-circle"></i> Mengapa Biaya berbeda dengan Cash Out
                                        Bank?
                                    </h6>
                                    <p>
                                        Karena sistem menggunakan prinsip <strong>accrual accounting</strong>,
                                        dimana biaya dicatat berdasarkan <strong>economic obligation</strong> (kewajiban
                                        ekonomis),
                                        bukan hanya cash yang keluar.
                                    </p>

                                    <hr>

                                    <h6 class="text-success">
                                        <i class="fas fa-calculator"></i> Perhitungan Detail
                                    </h6>
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <td><strong>Total Biaya (COGS)</strong></td>
                                            <td class="text-right">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?>
                                            </td>
                                            <td><small class="text-muted">Full economic cost</small></td>
                                        </tr>
                                        <tr>
                                            <td>Dikurangi: PPH dipotong</td>
                                            <td class="text-right text-danger">(Rp
                                                <?= number_format($pph_amount, 0, ',', '.') ?>)
                                            </td>
                                            <td><small class="text-muted">Belum bayar ke negara</small></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td><strong>Cash Out ke Vendor</strong></td>
                                            <td class="text-right"><strong>Rp
                                                    <?= number_format($cash_out, 0, ',', '.') ?></strong></td>
                                            <td><small class="text-success">✓ Match dengan Bank!</small></td>
                                        </tr>
                                    </table>

                                    <hr>

                                    <h6 class="text-warning">
                                        <i class="fas fa-calendar-check"></i> Nanti Saat Bayar PPH
                                    </h6>
                                    <table class="table table-sm table-bordered">
                                        <tr>
                                            <td>Cash out ke vendor (sudah bayar)</td>
                                            <td class="text-right">Rp <?= number_format($cash_out, 0, ',', '.') ?></td>
                                        </tr>
                                        <tr>
                                            <td>Cash out bayar PPH (nanti)</td>
                                            <td class="text-right">Rp <?= number_format($pph_amount, 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td><strong>Total Cash Out</strong></td>
                                            <td class="text-right"><strong>Rp
                                                    <?= number_format($saldo_akhir, 0, ',', '.') ?></strong></td>
                                        </tr>
                                    </table>

                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-check-circle"></i>
                                        <strong>Kesimpulan:</strong>
                                        Total cash out akan sama dengan Total Biaya setelah PPH dibayar ke negara!
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <a href="<?= base_url('pembayaran_pajak') ?>" class="btn btn-primary">
                                        <i class="fas fa-hand-holding-usd"></i> Bayar PPH Sekarang
                                    </a>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Akun Info Card -->
                    <div class="akun-info shadow">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><i class="fas fa-wallet"></i> <?= htmlspecialchars($akun->nama) ?></h4>
                                <p class="mb-0">
                                    <strong>Kode Perkiraan:</strong> <?= htmlspecialchars($akun->kode_perkiraan) ?><br>
                                    <strong>Tipe Akun:</strong>
                                    <?php
                                    $tipe_labels = [
                                        'ASET' => 'Asset',
                                        'BANK' => 'Bank/Kas',
                                        'LIAB' => 'Liability (Hutang)',
                                        'EKUI' => 'Equity (Modal)',
                                        'REVE' => 'Revenue (Pendapatan)',
                                        'COGS' => 'Cost of Goods Sold',
                                        'EXPS' => 'Expense (Beban)',
                                        'OCAS' => 'OCAS (Hutang Pajak)'
                                    ];
                                    echo $tipe_labels[$akun->tipe_akun] ?? $akun->tipe_akun;
                                    ?>
                                    <?php if ($akun->is_kas_bank): ?>
                                        <span class="badge badge-light ml-2">
                                            <i class="fas fa-university"></i> Kas/Bank
                                        </span>
                                    <?php endif ?>
                                </p>
                            </div>
                            <div class="col-md-6 text-right">
                                <p class="mb-1"><strong>Periode:</strong></p>
                                <h5><?= date('d/m/Y', strtotime($start_date)) ?> s/d
                                    <?= date('d/m/Y', strtotime($end_date)) ?>
                                </h5>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Periode -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-calendar-alt"></i> Ubah Periode
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('laporan_keuangan/detail_akun/' . $akun->id) ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><i class="fas fa-calendar"></i> Tanggal Mulai</label>
                                            <input type="date" name="start_date" class="form-control"
                                                value="<?= $start_date ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><i class="fas fa-calendar"></i> Tanggal Akhir</label>
                                            <input type="date" name="end_date" class="form-control"
                                                value="<?= $end_date ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-search"></i> Tampilkan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Info Cards -->
                    <?php
                    $is_liability_type = in_array($akun->tipe_akun, ['LIAB', 'EKUI', 'REVE', 'OCAS']);
                    $label_debit = $is_liability_type ? 'Total Debit (Kurang)' : 'Total Debit (Tambah)';
                    $label_kredit = $is_liability_type ? 'Total Kredit (Tambah)' : 'Total Kredit (Kurang)';
                    ?>
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card info-card info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Saldo Awal
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($saldo_awal_periode, 0, ',', '.') ?>
                                                <!-- ✅ FIXED - pakai $saldo_awal_periode yang dynamic -->
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card info-card success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                <?= $label_debit ?>
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_in, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-plus fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card info-card danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                <?= $label_kredit ?>
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_out, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-minus fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card shadow h-100 py-2"
                                style="border-left: 4px solid <?= $saldo_akhir >= 0 ? '#1cc88a' : '#e74a3b' ?>">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1"
                                                style="color: <?= $saldo_akhir >= 0 ? '#1cc88a' : '#e74a3b' ?>">
                                                Saldo Akhir
                                            </div>
                                            <div
                                                class="h6 mb-0 font-weight-bold <?= $saldo_akhir >= 0 ? 'text-success' : 'text-danger' ?>">
                                                Rp <?= number_format($saldo_akhir, 0, ',', '.') ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-white">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list"></i> Mutasi Transaksi - <?= htmlspecialchars($akun->nama) ?>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th width="10%">Tanggal</th>
                                            <th width="12%">No Transaksi</th>
                                            <th width="8%" class="text-center">Tipe</th>
                                            <th width="28%">Keterangan</th>
                                            <th width="12%" class="text-right">Debit</th>
                                            <th width="12%" class="text-right">Kredit</th>
                                            <th width="13%" class="text-right">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($transaksi)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
                                                    <em><i class="fas fa-inbox"></i> Tidak ada transaksi untuk periode
                                                        ini.</em>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <tr class="table-info">
                                                <td colspan="5" class="text-right"><strong>Saldo Awal Periode</strong></td>
                                                <td class="text-right">-</td>
                                                <td class="text-right">-</td>
                                                <td class="text-right"><strong>Rp
                                                        <?= number_format($saldo_awal_periode, 0, ',', '.') ?></strong></td>
                                            </tr>

                                            <?php $no = 1;
                                            foreach ($transaksi as $item): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= date('d/m/Y', strtotime($item->tanggal)) ?></td>
                                                    <td><strong
                                                            class="text-primary"><?= htmlspecialchars($item->no_transaksi) ?></strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($item->tipe == 'IN'): ?>
                                                            <span class="badge badge-success badge-sm">
                                                                <i class="fas fa-arrow-down"></i> IN
                                                            </span>
                                                        <?php elseif ($item->tipe == 'OUT'): ?>
                                                            <span class="badge badge-danger badge-sm">
                                                                <i class="fas fa-arrow-up"></i> OUT
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary badge-sm">
                                                                <i class="fas fa-exchange-alt"></i> JE
                                                            </span>
                                                        <?php endif ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($item->keterangan) ?>
                                                        <?php if ($item->referensi_tipe): ?>
                                                            <br><small class="text-muted">
                                                                <i class="fas fa-link"></i>
                                                                <?= $item->referensi_tipe ?> #<?= $item->referensi_id ?>
                                                            </small>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <?php if ($item->debit > 0): ?>
                                                            <strong class="text-success">
                                                                Rp <?= number_format($item->debit, 0, ',', '.') ?>
                                                            </strong>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <?php if ($item->kredit > 0): ?>
                                                            <strong class="text-danger">
                                                                Rp <?= number_format($item->kredit, 0, ',', '.') ?>
                                                            </strong>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong
                                                            class="<?= $item->saldo_running >= 0 ? 'text-success' : 'text-danger' ?>">
                                                            Rp <?= number_format($item->saldo_running, 0, ',', '.') ?>
                                                        </strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>

                                            <tr class="table-secondary">
                                                <td colspan="5" class="text-right"><strong>Saldo Akhir Periode</strong></td>
                                                <td class="text-right"><strong class="text-success">Rp
                                                        <?= number_format($total_in, 0, ',', '.') ?></strong></td>
                                                <td class="text-right"><strong class="text-danger">Rp
                                                        <?= number_format($total_out, 0, ',', '.') ?></strong></td>
                                                <td class="text-right">
                                                    <strong
                                                        class="<?= $saldo_akhir >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        Rp <?= number_format($saldo_akhir, 0, ',', '.') ?>
                                                    </strong>
                                                </td>
                                            </tr>
                                        <?php endif ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Legend -->
                            <div class="mt-3">
                                <small class="text-muted">
                                    <strong>Keterangan:</strong><br>
                                    • <span class="badge badge-success badge-sm">IN</span> = Uang masuk ke akun
                                    Bank/Kas<br>
                                    • <span class="badge badge-danger badge-sm">OUT</span> = Uang keluar dari akun
                                    Bank/Kas<br>
                                    • <span class="badge badge-secondary badge-sm">JE</span> = Journal Entry (mutasi
                                    non-cashflow)<br>
                                    <br>
                                    <strong>Interpretasi Saldo per Tipe Akun:</strong><br>
                                    <?php if (in_array($akun->tipe_akun, ['LIAB', 'EKUI', 'REVE', 'OCAS'])): ?>
                                        • Akun <strong><?= $akun->tipe_akun ?></strong> → <span class="text-success">Kredit
                                            (+)</span> = Saldo bertambah, <span class="text-danger">Debit (-)</span> = Saldo
                                        berkurang<br>
                                        • Contoh: Hutang/Modal/Pendapatan bertambah saat KREDIT, berkurang saat DEBIT
                                    <?php else: ?>
                                        • Akun <strong><?= $akun->tipe_akun ?></strong> → <span class="text-success">Debit
                                            (+)</span> = Saldo bertambah, <span class="text-danger">Kredit (-)</span> =
                                        Saldo berkurang<br>
                                        • Contoh: Asset/Bank/Expense bertambah saat DEBIT, berkurang saat KREDIT
                                    <?php endif ?>
                                    <br>
                                    <i class="fas fa-receipt text-info"></i>
                                    <em><strong>PIUTANG</strong> dicatat sebagai: <strong>DPP + PPN - PPH</strong>
                                        (cash yang akan diterima dari customer setelah dipotong PPH).</em>
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

    <script>
        $(document).ready(function () {
            // Auto hide alerts
            setTimeout(function () {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Enable Bootstrap tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
</body>

</html>
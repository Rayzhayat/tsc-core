<!-- reconciliation/index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .reconciliation-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .reconciliation-card {
            border-left: 5px solid;
            transition: transform 0.3s;
        }
        .reconciliation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .reconciliation-card.success {
            border-color: #1cc88a;
        }
        .reconciliation-card.warning {
            border-color: #f6c23e;
        }
        .reconciliation-card.danger {
            border-color: #e74a3b;
        }
        .flow-arrow {
            font-size: 2rem;
            color: #6c757d;
        }
        .match-badge {
            font-size: 1.2rem;
            padding: 10px 20px;
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
                            <i class="fas fa-balance-scale text-primary"></i> Laporan Rekonsiliasi
                        </h1>
                        <div>
                            <a href="<?= base_url('reconciliation/export_excel?start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                               class="btn btn-success btn-sm shadow-sm mr-2">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                            <a href="<?= base_url('laporan_keuangan?start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                               class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <!-- Hero Section -->
                    <div class="reconciliation-hero">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3><i class="fas fa-question-circle"></i> Apa itu Rekonsiliasi?</h3>
                                <p class="mb-0">
                                    Laporan ini menjelaskan <strong>mengapa Total Biaya (COGS) berbeda dengan Cash Out Bank</strong>.
                                    Selisihnya adalah <strong>PPH yang dipotong</strong> tapi belum dibayar ke negara.
                                </p>
                                <small class="d-block mt-2">
                                    <i class="fas fa-lightbulb"></i> 
                                    Saat PPH dibayar nanti (via menu Pembayaran Pajak), total cash out akan sama dengan Total Biaya.
                                </small>
                            </div>
                            <div class="col-md-4 text-right">
                                <h2 class="mb-0">
                                    <i class="fas fa-calendar-alt"></i>
                                </h2>
                                <p class="mb-0">Periode:</p>
                                <h5><?= date('d/m/Y', strtotime($start_date)) ?><br>s/d<br><?= date('d/m/Y', strtotime($end_date)) ?></h5>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Periode -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-calendar-alt"></i> Filter Periode
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('reconciliation') ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tanggal Mulai</label>
                                            <input type="date" name="start_date" class="form-control"
                                                value="<?= $start_date ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tanggal Akhir</label>
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

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card reconciliation-card success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Total Biaya (COGS)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_biaya, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">Nominal + PPN</small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card reconciliation-card warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Cash Out Bank
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_cash_out, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">Yang dibayar ke vendor</small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-university fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card reconciliation-card danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                PPH Dipotong (OCAS)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_pph, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">Belum bayar ke negara</small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card shadow h-100 py-2" style="border-left: 5px solid <?= abs($total_biaya - ($total_cash_out + $total_pph)) < 1 ? '#1cc88a' : '#e74a3b' ?>">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1"
                                                style="color: <?= abs($total_biaya - ($total_cash_out + $total_pph)) < 1 ? '#1cc88a' : '#e74a3b' ?>">
                                                Status
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold">
                                                <?php if (abs($total_biaya - ($total_cash_out + $total_pph)) < 1): ?>
                                                    <span class="text-success">
                                                        <i class="fas fa-check-circle"></i> MATCH!
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> ERROR
                                                    </span>
                                                <?php endif ?>
                                            </div>
                                            <small class="text-muted">Biaya = Cash Out + PPH</small>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Flow Diagram -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-gradient-info text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-project-diagram"></i> Alur Rekonsiliasi
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center text-center">
                                <div class="col-md-3">
                                    <div class="card bg-success text-white shadow">
                                        <div class="card-body">
                                            <h6>Total Biaya (COGS)</h6>
                                            <h4>Rp <?= number_format($total_biaya, 0, ',', '.') ?></h4>
                                            <small>Nominal + PPN</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="flow-arrow">
                                        <i class="fas fa-equals"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white shadow">
                                        <div class="card-body">
                                            <h6>Cash Out Bank</h6>
                                            <h4>Rp <?= number_format($total_cash_out, 0, ',', '.') ?></h4>
                                            <small>Dibayar ke vendor</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="flow-arrow">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white shadow">
                                        <div class="card-body">
                                            <h6>PPH (OCAS)</h6>
                                            <h4>Rp <?= number_format($total_pph, 0, ',', '.') ?></h4>
                                            <small>Belum bayar</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="flow-arrow">
                                        <?php if (abs($total_biaya - ($total_cash_out + $total_pph)) < 1): ?>
                                            <i class="fas fa-check text-success"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times text-danger"></i>
                                        <?php endif ?>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="alert alert-info mb-0">
                                <h6><i class="fas fa-info-circle"></i> Penjelasan:</h6>
                                <p class="mb-0">
                                    <strong>Total Biaya (COGS)</strong> mencatat full economic cost perusahaan.<br>
                                    <strong>Cash Out Bank</strong> adalah uang yang benar-benar keluar dari rekening.<br>
                                    <strong>PPH (OCAS)</strong> adalah pajak yang dipotong tapi belum dibayar ke negara.<br><br>
                                    <i class="fas fa-arrow-right text-primary"></i> 
                                    Saat PPH dibayar nanti, total cash out akan sama dengan Total Biaya!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Breakdown per Akun -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-list-alt"></i> Detail Rekonsiliasi per Akun
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($reconciliation_data)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Tidak ada data untuk periode ini.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="25%">Akun</th>
                                                <th width="18%" class="text-right">Total Biaya (COGS)</th>
                                                <th width="18%" class="text-right">Cash Out Bank</th>
                                                <th width="18%" class="text-right">PPH Dipotong</th>
                                                <th width="8%" class="text-center">Status</th>
                                                <th width="8%" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($reconciliation_data as $item): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td>
                                                        <strong><?= $item['akun']->kode_perkiraan ?></strong>
                                                        - <?= $item['akun']->nama ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-success">
                                                            Rp <?= number_format($item['total_biaya'], 0, ',', '.') ?>
                                                        </strong>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-warning">
                                                            Rp <?= number_format($item['cash_out'], 0, ',', '.') ?>
                                                        </strong>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-danger">
                                                            Rp <?= number_format($item['pph_amount'], 0, ',', '.') ?>
                                                        </strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (abs($item['total_biaya'] - ($item['cash_out'] + $item['pph_amount'])) < 1): ?>
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check"></i> Match
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">
                                                                <i class="fas fa-times"></i> Error
                                                            </span>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#detailModal<?= $item['akun']->id ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- Modal Detail -->
                                                <div class="modal fade" id="detailModal<?= $item['akun']->id ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">
                                                                    Detail: <?= $item['akun']->kode_perkiraan ?> - <?= $item['akun']->nama ?>
                                                                </h5>
                                                              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <h6 class="text-primary">Perhitungan:</h6>
                                                                <table class="table table-sm table-bordered">
                                                                    <tr>
                                                                        <td><strong>Total Biaya (COGS)</strong></td>
                                                                        <td class="text-right">Rp <?= number_format($item['total_biaya'], 0, ',', '.') ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Dikurangi: PPH dipotong</td>
                                                                        <td class="text-right text-danger">(Rp <?= number_format($item['pph_amount'], 0, ',', '.') ?>)</td>
                                                                    </tr>
                                                                    <tr class="table-success">
                                                                        <td><strong>Cash Out ke Vendor</strong></td>
                                                                        <td class="text-right"><strong>Rp <?= number_format($item['cash_out'], 0, ',', '.') ?></strong></td>
                                                                    </tr>
                                                                </table>
                                                                
                                                                <hr>
                                                                
                                                                <h6 class="text-warning">Nanti Saat Bayar PPH:</h6>
                                                                <table class="table table-sm table-bordered">
                                                                    <tr>
                                                                        <td>Cash out ke vendor (sudah)</td>
                                                                        <td class="text-right">Rp <?= number_format($item['cash_out'], 0, ',', '.') ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Cash out bayar PPH (nanti)</td>
                                                                        <td class="text-right">Rp <?= number_format($item['pph_amount'], 0, ',', '.') ?></td>
                                                                    </tr>
                                                                    <tr class="table-primary">
                                                                        <td><strong>Total Cash Out</strong></td>
                                                                        <td class="text-right"><strong>Rp <?= number_format($item['total_biaya'], 0, ',', '.') ?></strong></td>
                                                                    </tr>
                                                                </table>
                                                                
                                                                <div class="alert alert-info mb-0">
                                                                    <i class="fas fa-check-circle"></i>
                                                                    Total cash out akan sama dengan Total Biaya!
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="<?= base_url('laporan_keuangan/detail_akun/' . $item['akun']->id . '?start_date=' . $start_date . '&end_date=' . $end_date) ?>" 
                                                                   class="btn btn-primary">
                                                                    <i class="fas fa-eye"></i> Lihat Detail Akun
                                                                </a>
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach ?>
                                            
                                            <!-- Total Row -->
                                            <tr class="table-primary">
                                                <td colspan="2" class="text-right"><strong>GRAND TOTAL:</strong></td>
                                                <td class="text-right">
                                                    <strong class="text-success">
                                                        Rp <?= number_format($total_biaya, 0, ',', '.') ?>
                                                    </strong>
                                                </td>
                                                <td class="text-right">
                                                    <strong class="text-warning">
                                                        Rp <?= number_format($total_cash_out, 0, ',', '.') ?>
                                                    </strong>
                                                </td>
                                                <td class="text-right">
                                                    <strong class="text-danger">
                                                        Rp <?= number_format($total_pph, 0, ',', '.') ?>
                                                    </strong>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (abs($total_biaya - ($total_cash_out + $total_pph)) < 1): ?>
                                                        <span class="badge badge-success match-badge">
                                                            <i class="fas fa-check-circle"></i> MATCH!
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger match-badge">
                                                            <i class="fas fa-exclamation-triangle"></i> ERROR
                                                        </span>
                                                    <?php endif ?>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>

                    <!-- OCAS Verification -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-gradient-warning text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-clipboard-check"></i> Verifikasi OCAS (Hutang Pajak)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card border-left-danger shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                PPH 23 (OCAS)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($pph23_balance, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">Hutang belum bayar</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-left-warning shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                PPH 4(2) (OCAS)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($pph42_balance, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">Hutang belum bayar</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total OCAS
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                Rp <?= number_format($total_ocas_balance, 0, ',', '.') ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php if (abs($total_ocas_balance - $total_pph) < 1): ?>
                                                    <span class="text-success">
                                                        <i class="fas fa-check-circle"></i> Match dengan PPH Dipotong!
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> Tidak match!
                                                    </span>
                                                <?php endif ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-info-circle"></i>
                                <strong>Catatan:</strong> 
                                Saldo OCAS harus sama dengan total PPH yang dipotong. 
                                Jika berbeda, ada transaksi yang tidak tercatat dengan benar.
                                <a href="<?= base_url('pembayaran_pajak') ?>" class="alert-link">
                                    Klik di sini untuk bayar PPH
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
</body>
</html>
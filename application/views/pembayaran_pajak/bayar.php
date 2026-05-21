<!-- bayar.php - FIXED: All tax deadlines are 15th of next month -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .form-card {
            border-left: 5px solid #1cc88a;
        }

        .jenis-pajak-option {
            border: 2px solid #e3e6f0;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }

        .jenis-pajak-option:hover {
            border-color: #1cc88a;
            background: #f8f9fc;
        }

        .jenis-pajak-option.selected {
            border-color: #1cc88a;
            background: #d4edda;
        }

        .jenis-pajak-option input[type="radio"] {
            width: 20px;
            height: 20px;
            margin-right: 15px;
        }

        .balance-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .required-field::after {
            content: " *";
            color: #e74a3b;
            font-weight: bold;
        }

        .periode-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
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
                            <i class="fas fa-money-bill-wave text-success"></i> Bayar Pajak ke Negara
                        </h1>
                        <a href="<?= base_url('pembayaran_pajak?periode=' . $current_periode) ?>"
                            class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- 🔥 PERIODE INFO BANNER -->
                    <div class="alert alert-info shadow-lg mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-2">
                                    <i class="fas fa-calendar-alt"></i> Periode Pembayaran
                                </h5>
                                <p class="mb-0">
                                    Anda akan membayar pajak untuk periode:
                                    <strong class="periode-badge"><?= $periode_label ?></strong>
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Saldo yang ditampilkan adalah untuk periode ini saja
                                </small>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?= base_url('pembayaran_pajak') ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-exchange-alt"></i> Ganti Periode
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Left Column: Balance Info -->
                        <div class="col-md-4 mb-4">
                            <div class="card shadow">
                                <div class="card-header bg-gradient-primary text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-info-circle"></i> Saldo Pajak - <?= $periode_label ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- PPH 23 Balance -->
                                    <div class="mb-3 p-3 border-left-danger shadow-sm"
                                        style="border-left: 4px solid #e74a3b;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                    PPH 23
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="balance_pph23">
                                                    Rp <?= number_format(round($pph23_balance, 0), 0, ',', '.') ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?= $pph23_akun ? $pph23_akun->kode_perkiraan : '' ?>
                                                </small>
                                            </div>
                                            <div>
                                                <?php if (round($pph23_balance, 0) > 0): ?>
                                                    <span class="badge badge-danger">Belum Bayar</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Lunas</span>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PPH 4(2) Balance -->
                                    <div class="mb-3 p-3 border-left-warning shadow-sm"
                                        style="border-left: 4px solid #f6c23e;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                    PPH 4(2)
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="balance_pph42">
                                                    Rp <?= number_format(round($pph42_balance, 0), 0, ',', '.') ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?= $pph42_akun ? $pph42_akun->kode_perkiraan : '' ?>
                                                </small>
                                            </div>
                                            <div>
                                                <?php if (round($pph42_balance, 0) > 0): ?>
                                                    <span class="badge badge-warning">Belum Bayar</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Lunas</span>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 🔥 PPN KELUARAN Balance -->
                                    <div class="mb-3 p-3 border-left-success shadow-sm"
                                        style="border-left: 4px solid #1cc88a;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    PPN Keluaran
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="balance_ppn">
                                                    Rp <?= number_format(round($ppn_balance, 0), 0, ',', '.') ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?= $ppn_akun ? $ppn_akun->kode_perkiraan : '' ?>
                                                </small>
                                            </div>
                                            <div>
                                                <?php if (round($ppn_balance, 0) > 0): ?>
                                                    <span class="badge badge-success">Belum Bayar</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Lunas</span>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Info Box -->
                                    <div class="alert alert-info mb-0">
                                        <small>
                                            <i class="fas fa-lightbulb"></i> <strong>Tips:</strong><br>
                                            • Pilih jenis pajak sesuai saldo yang ingin dibayar<br>
                                            • Nominal tidak boleh melebihi saldo<br>
                                            • Masa pajak otomatis terisi sesuai periode
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Info -->
                            <div class="card shadow mt-3">
                                <div class="card-header bg-gradient-info text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-question-circle"></i> Informasi Pajak
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="small mb-2">
                                        <strong>PPH 23:</strong> Pajak dipotong dari vendor. Rate ~2%.
                                    </p>
                                    <p class="small mb-2">
                                        <strong>PPH 4(2):</strong> Pajak final atas sewa. Rate ~0.5%.
                                    </p>
                                    <p class="small mb-2">
                                        <strong>PPN Keluaran:</strong> Pajak dipungut dari customer. Rate 11%.
                                    </p>
                                    <hr>
                                    <p class="small mb-2 text-danger">
                                        <i class="fas fa-calendar-alt"></i> Batas pembayaran:<br>
                                        <strong>Semua Jenis Pajak:
                                            <?= date('15 F Y', strtotime($start_date . ' +1 month')) ?></strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Form -->
                        <div class="col-md-8">
                            <div class="card shadow form-card">
                                <div class="card-header bg-gradient-success text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-file-invoice-dollar"></i> Form Pembayaran -
                                        <?= $periode_label ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('pembayaran_pajak/proses_bayar') ?>" method="post"
                                        id="formBayar">

                                        <!-- Hidden periode field -->
                                        <input type="hidden" name="periode" value="<?= $current_periode ?>">

                                        <!-- Pilih Jenis Pajak -->
                                        <div class="form-group">
                                            <label class="font-weight-bold required-field">Pilih Jenis Pajak</label>

                                            <!-- PPH 23 Option -->
                                            <div class="jenis-pajak-option" onclick="selectJenis('pph23')"
                                                id="option_pph23">
                                                <label class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                                    <input type="radio" name="jenis_pajak" value="pph23"
                                                        id="jenis_pph23" <?= ($this->input->get('jenis') === 'pph23' || !$this->input->get('jenis')) ? 'checked' : '' ?>>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h6 class="mb-0 font-weight-bold text-danger">
                                                                    <i class="fas fa-receipt"></i> PPH Pasal 23
                                                                </h6>
                                                                <small class="text-muted">Pajak dari vendor
                                                                    (dipotong)</small>
                                                            </div>
                                                            <div class="text-right">
                                                                <span class="badge badge-danger">Saldo</span>
                                                                <h6 class="mb-0 text-danger font-weight-bold">
                                                                    Rp
                                                                    <?= number_format(round($pph23_balance, 0), 0, ',', '.') ?>
                                                                </h6>
                                                                <small class="text-muted"><?= $periode_label ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>

                                            <!-- PPH 4(2) Option -->
                                            <div class="jenis-pajak-option" onclick="selectJenis('pph42')"
                                                id="option_pph42">
                                                <label class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                                    <input type="radio" name="jenis_pajak" value="pph42"
                                                        id="jenis_pph42" <?= $this->input->get('jenis') === 'pph42' ? 'checked' : '' ?>>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h6 class="mb-0 font-weight-bold text-warning">
                                                                    <i class="fas fa-building"></i> PPH Pasal 4 Ayat 2
                                                                </h6>
                                                                <small class="text-muted">Pajak final atas sewa</small>
                                                            </div>
                                                            <div class="text-right">
                                                                <span class="badge badge-warning">Saldo</span>
                                                                <h6 class="mb-0 text-warning font-weight-bold">
                                                                    Rp
                                                                    <?= number_format(round($pph42_balance, 0), 0, ',', '.') ?>
                                                                </h6>
                                                                <small class="text-muted"><?= $periode_label ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>

                                            <!-- 🔥 PPN KELUARAN Option -->
                                            <div class="jenis-pajak-option" onclick="selectJenis('ppn_keluaran')"
                                                id="option_ppn_keluaran">
                                                <label class="mb-0 d-flex align-items-center" style="cursor: pointer;">
                                                    <input type="radio" name="jenis_pajak" value="ppn_keluaran"
                                                        id="jenis_ppn_keluaran"
                                                        <?= $this->input->get('jenis') === 'ppn_keluaran' ? 'checked' : '' ?>>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h6 class="mb-0 font-weight-bold text-success">
                                                                    <i class="fas fa-file-invoice-dollar"></i> PPN
                                                                    Keluaran
                                                                </h6>
                                                                <small class="text-muted">Pajak dari customer
                                                                    (dipungut)</small>
                                                            </div>
                                                            <div class="text-right">
                                                                <span class="badge badge-success">Saldo</span>
                                                                <h6 class="mb-0 text-success font-weight-bold">
                                                                    Rp
                                                                    <?= number_format(round($ppn_balance, 0), 0, ',', '.') ?>
                                                                </h6>
                                                                <small class="text-muted"><?= $periode_label ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Tanggal Bayar -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold required-field">Tanggal
                                                        Pembayaran</label>
                                                    <input type="date" name="tanggal_bayar" class="form-control"
                                                        value="<?= date('Y-m-d') ?>" required>
                                                    <small class="text-muted">Tanggal saat bayar ke negara</small>
                                                </div>
                                            </div>

                                            <!-- Masa Pajak (AUTO-FILLED) -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold required-field">Masa Pajak</label>
                                                    <input type="text" name="masa_pajak" class="form-control"
                                                        value="<?= $periode_label ?>" readonly>
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle"></i> Otomatis terisi sesuai
                                                        periode
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Nominal -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold required-field">Nominal
                                                        Pembayaran</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">Rp</span>
                                                        </div>
                                                        <input type="text" name="nominal" id="nominal"
                                                            class="form-control" placeholder="0" required>
                                                    </div>
                                                    <small class="text-muted">
                                                        Maks: <span id="max_amount">Rp
                                                            <?= number_format(round($pph23_balance, 0), 0, ',', '.') ?></span>
                                                        <button type="button" class="btn btn-link btn-sm p-0 ml-2"
                                                            onclick="useMaxAmount()">
                                                            <i class="fas fa-arrow-up"></i> Bayar Semua
                                                        </button>
                                                    </small>
                                                </div>
                                            </div>

                                            <!-- Bank -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="font-weight-bold required-field">Bank
                                                        Pembayaran</label>
                                                    <select name="akun_bank_id" class="form-control" required>
                                                        <option value="">-- Pilih Bank --</option>
                                                        <?php foreach ($akun_bank as $bank): ?>
                                                            <option value="<?= $bank->id ?>">
                                                                <?= htmlspecialchars($bank->kode_perkiraan . ' - ' . $bank->nama) ?>
                                                            </option>
                                                        <?php endforeach ?>
                                                    </select>
                                                    <small class="text-muted">Bank yang digunakan untuk bayar</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- No Bukti Potong -->
                                        <div class="form-group">
                                            <label class="font-weight-bold">Nomor Bukti Potong (Opsional)</label>
                                            <input type="text" name="no_bukti_potong" class="form-control"
                                                placeholder="Contoh: BP-2025-12-001 atau SPT-PPN-001">
                                            <small class="text-muted">Nomor bukti potong dari sistem e-Bupot/e-Faktur
                                                (jika ada)</small>
                                        </div>

                                        <!-- Keterangan -->
                                        <div class="form-group">
                                            <label class="font-weight-bold">Keterangan (Opsional)</label>
                                            <textarea name="keterangan" class="form-control" rows="3"
                                                placeholder="Catatan tambahan..."></textarea>
                                        </div>

                                        <hr>

                                        <!-- Summary -->
                                        <div class="alert alert-success">
                                            <h6 class="font-weight-bold mb-2">
                                                <i class="fas fa-calculator"></i> Ringkasan Pembayaran:
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <strong>Periode:</strong>
                                                        <span class="text-primary"><?= $periode_label ?></span>
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Jenis Pajak:</strong>
                                                        <span id="summary_jenis">PPH 23</span>
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Saldo Sebelum:</strong>
                                                        <span id="summary_before" class="text-danger">Rp
                                                            <?= number_format(round($pph23_balance, 0), 0, ',', '.') ?></span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <strong>Nominal Bayar:</strong>
                                                        <span id="summary_amount" class="text-primary">Rp 0</span>
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Saldo Setelah:</strong>
                                                        <span id="summary_after" class="text-success">Rp
                                                            <?= number_format(round($pph23_balance, 0), 0, ',', '.') ?></span>
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Batas Bayar:</strong>
                                                        <span class="text-danger" id="summary_deadline">
                                                            <?= date('15 M Y', strtotime($start_date . ' +1 month')) ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="form-group text-center">
                                            <button type="submit" class="btn btn-success btn-lg px-5">
                                                <i class="fas fa-money-bill-wave"></i> Bayar Pajak
                                            </button>
                                            <a href="<?= base_url('pembayaran_pajak?periode=' . $current_periode) ?>"
                                                class="btn btn-secondary btn-lg px-5">
                                                <i class="fas fa-times"></i> Batal
                                            </a>
                                        </div>
                                    </form>
                                </div>
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
        // 🔥 FIX: Balance data - rounded to nearest integer
        const balances = {
            pph23: <?= round($pph23_balance, 0) ?>,
            pph42: <?= round($pph42_balance, 0) ?>,
            ppn_keluaran: <?= round($ppn_balance, 0) ?>
        };

        // 🔥 FIX: All deadlines are 15th of next month
        const deadlines = {
            pph23: '<?= date('15 M Y', strtotime($start_date . ' +1 month')) ?>',
            pph42: '<?= date('15 M Y', strtotime($start_date . ' +1 month')) ?>',
            ppn_keluaran: '<?= date('15 M Y', strtotime($start_date . ' +1 month')) ?>'
        };

        let currentJenis = 'pph23';

        $(document).ready(function () {
            // Auto-select based on URL param
            <?php if ($this->input->get('jenis')): ?>
                selectJenis('<?= $this->input->get('jenis') ?>');
            <?php else: ?>
                selectJenis('pph23');
            <?php endif ?>

            // 🔥 FIX: Format number input - NO DECIMALS
            $('#nominal').on('input', function () {
                // Remove ALL non-digit characters (including comma, period, etc)
                let val = $(this).val().replace(/\D/g, '');
                $(this).val(formatNumber(val));
                updateSummary();
            });

            // Form validation
            $('#formBayar').on('submit', function (e) {
                const nominal = parseInt($('#nominal').val().replace(/\D/g, '')) || 0;
                const maxAmount = balances[currentJenis];

                if (nominal > maxAmount) {
                    e.preventDefault();
                    alert('❌ Nominal pembayaran (Rp ' + formatNumber(nominal) + ') melebihi saldo (Rp ' + formatNumber(maxAmount) + ')!');
                    return false;
                }

                if (nominal <= 0) {
                    e.preventDefault();
                    alert('❌ Nominal harus lebih dari 0!');
                    return false;
                }

                // Get display name
                let jenisDisplay = '';
                switch (currentJenis) {
                    case 'pph23': jenisDisplay = 'PPH 23'; break;
                    case 'pph42': jenisDisplay = 'PPH 4(2)'; break;
                    case 'ppn_keluaran': jenisDisplay = 'PPN Keluaran'; break;
                }

                return confirm(
                    '✅ Konfirmasi Pembayaran Pajak\n\n' +
                    'Periode: <?= $periode_label ?>\n' +
                    'Jenis: ' + jenisDisplay + '\n' +
                    'Nominal: Rp ' + formatNumber(nominal) + '\n\n' +
                    'Lanjutkan pembayaran?'
                );
            });
        });

        function selectJenis(jenis) {
            currentJenis = jenis;

            // Remove all selected class
            $('.jenis-pajak-option').removeClass('selected');

            // Add selected class
            $('#option_' + jenis).addClass('selected');

            // Check radio
            $('#jenis_' + jenis).prop('checked', true);

            // Update max amount
            const maxAmount = balances[jenis];
            $('#max_amount').text('Rp ' + formatNumber(maxAmount));

            // Update summary text
            let jenisText = '';
            switch (jenis) {
                case 'pph23': jenisText = 'PPH 23'; break;
                case 'pph42': jenisText = 'PPH 4(2)'; break;
                case 'ppn_keluaran': jenisText = 'PPN Keluaran'; break;
            }

            $('#summary_jenis').text(jenisText);
            $('#summary_before').text('Rp ' + formatNumber(maxAmount));
            $('#summary_deadline').text(deadlines[jenis]);

            updateSummary();
        }

        function useMaxAmount() {
            const maxAmount = balances[currentJenis];
            $('#nominal').val(formatNumber(maxAmount));
            updateSummary();
        }

        function updateSummary() {
            const nominal = parseInt($('#nominal').val().replace(/\D/g, '')) || 0;
            const before = balances[currentJenis];
            const after = Math.max(0, before - nominal);

            $('#summary_amount').text('Rp ' + formatNumber(nominal));
            $('#summary_after').text('Rp ' + formatNumber(after));

            // Validation indicator
            if (nominal > before) {
                $('#summary_amount').removeClass('text-primary').addClass('text-danger');
            } else {
                $('#summary_amount').removeClass('text-danger').addClass('text-primary');
            }
        }

        // 🔥 FIX: Format number function - NO DECIMALS
        function formatNumber(num) {
            // Parse as integer (remove any decimals)
            const intNum = Math.round(parseFloat(num)) || 0;
            // Format with thousand separator (dot)
            return intNum.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>
</body>

</html>
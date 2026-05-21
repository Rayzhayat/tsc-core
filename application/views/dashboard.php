<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head.php') ?>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- load sidebar -->
        <?php $this->load->view('partials/sidebar.php') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content" data-url="<?= base_url('dashboard') ?>">
                <!-- load Topbar -->
                <?php $this->load->view('partials/topbar.php') ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
                        <small class="text-muted">Selamat datang, <strong><?= $this->session->userdata('login')['nama'] ?></strong></small>
                    </div>

                    <!-- ALERT -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- CARD STATISTIK -->
                    <div class="row">

                        <!-- Barang -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Jumlah Barang</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jumlah_barang ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Karyawan (GANTI DARI PETUGAS) -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Jumlah Karyawan</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jumlah_karyawan ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengeluaran -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Jumlah Pengeluaran</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jumlah_pengeluaran ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Penerimaan -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Jumlah Penerimaan</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jumlah_penerimaan ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PROFIL TOKO & USER -->
                    <div class="row">
                        <!-- PROFIL TOKO -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow">
                                <div class="card-header bg-primary text-white">
                                    <strong>Profil Toko</strong>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Nama Toko</label>
                                        <input type="text" value="<?= $toko->nama_toko ?? '-' ?>" readonly class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Pemilik</label>
                                        <input type="text" value="<?= $toko->nama_pemilik ?? '-' ?>" readonly class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>No. Telepon</label>
                                        <input type="text" value="<?= $toko->no_telepon ?? '-' ?>" readonly class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Alamat</label>
                                        <textarea readonly class="form-control" rows="2"><?= $toko->alamat ?? '-' ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- USER SEDANG LOGIN -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow">
                                <div class="card-header bg-success text-white">
                                    <strong>User Sedang Login</strong>
                                </div>
                                <div class="card-body">
                                    <?php 
                                    $login = $this->session->userdata('login');
                                    $jam_masuk = $this->session->userdata('jam_masuk') ?? date('H:i:s');
                                    ?>
                                    <div class="form-group">
                                        <label>Nama</label>
                                        <input type="text" value="<?= $login['nama'] ?? '-' ?>" readonly class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Username / NIK</label>
                                        <input type="text" value="<?= $login['username'] ?? $login['nik'] ?>" readonly class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Level Akses</label>
                                        <input type="text" value="<?= ucfirst($login['user_level']) ?>" readonly class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Jam Masuk</label>
                                        <input type="text" value="<?= $jam_masuk ?>" readonly class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GRAFIK (HANYA SUPERADMIN & ADMIN) -->
                    <?php if (in_array($this->session->userdata('login')['user_level'], ['superadmin', 'admin'])): ?>
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Penerimaan Bulanan (<?= date('Y') ?>)</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartPenerimaan"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Pengeluaran Bulanan (<?= date('Y') ?>)</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="chartPengeluaran"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- load footer -->
            <?php $this->load->view('partials/footer.php') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js.php') ?>

    <!-- CHART JS -->
    <script src="<?= base_url('sb-admin/vendor/chart.js/Chart.min.js') ?>"></script>
    <script>
        // DATA DARI PHP KE JS
        const penerimaan = <?= json_encode($penerimaan_bulan ?? []) ?>;
        const pengeluaran = <?= json_encode($pengeluaran_bulan ?? []) ?>;

        const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const dataPenerimaan = Array(12).fill(0);
        const dataPengeluaran = Array(12).fill(0);

        penerimaan.forEach(item => {
            dataPenerimaan[item.bulan - 1] = item.total;
        });
        pengeluaran.forEach(item => {
            dataPengeluaran[item.bulan - 1] = item.total;
        });

        // GRAFIK PENERIMAAN
        new Chart(document.getElementById('chartPenerimaan'), {
            type: 'bar',
            data: {
                labels: bulan,
                datasets: [{
                    label: 'Penerimaan (Rp)',
                    data: dataPenerimaan,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });

        // GRAFIK PENGELUARAN
        new Chart(document.getElementById('chartPengeluaran'), {
            type: 'bar',
            data: {
                labels: bulan,
                datasets: [{
                    label: 'Pengeluaran (Rp)',
                    data: dataPengeluaran,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
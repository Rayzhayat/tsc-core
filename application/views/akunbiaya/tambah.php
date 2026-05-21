<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">
                    <!-- JUDUL + KEMBALI -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>
                        <a href="<?= base_url('akunbiaya') ?>" class="btn btn-secondary btn-sm">
                            Kembali
                        </a>
                    </div>

                    <!-- ALERT ERROR -->
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <!-- CARD FORM -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">Form Tambah Akun Biaya</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('akunbiaya/proses_tambah') ?>" method="POST">
                                <div class="row">
                                    <!-- KIRI -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tipe Akun <span class="text-danger">*</span></label>
                                            <input type="text" name="tipe_akun" id="tipe_akun" class="form-control"
                                                placeholder="Contoh: EXPS, COGS, REVE"
                                                value="<?= set_value('tipe_akun') ?>" list="tipe_akun_list" required>
                                            <datalist id="tipe_akun_list">
                                                <option value="BANK">
                                                <option value="REVE">
                                                <option value="COGS">
                                                <option value="EXPS">
                                            </datalist>
                                            <small class="text-muted">Ketik manual atau pilih dari saran</small>
                                        </div>

                                        <div class="form-group">
                                            <label>Kode Perkiraan <span class="text-danger">*</span></label>
                                            <input type="text" name="kode_perkiraan" id="kode_perkiraan"
                                                class="form-control" placeholder="Contoh: 600001"
                                                value="<?= set_value('kode_perkiraan') ?>" required>
                                            <small class="text-muted">Hanya angka, harus unik</small>
                                        </div>
                                    </div>

                                    <!-- KANAN -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nama Akun <span class="text-danger">*</span></label>
                                            <input type="text" name="nama" class="form-control"
                                                placeholder="Contoh: Beban Listrik" value="<?= set_value('nama') ?>"
                                                required>
                                        </div>

                                        <div class="form-group">
                                            <label>Akun Induk</label>
                                            <input type="text" name="akun_induk" id="akun_induk" class="form-control"
                                                placeholder="Contoh: 10, 20, 30, 40"
                                                value="<?= set_value('akun_induk') ?>" list="akun_induk_list">
                                            <datalist id="akun_induk_list">
                                                <option value="10">
                                                <option value="20">
                                                <option value="30">
                                                <option value="40">
                                            </datalist>
                                            <small class="text-muted">Opsional - Kosongkan jika tidak ada induk</small>
                                        </div>
                                    </div>

                                    <!-- ROW 2: ADDITIONAL FIELDS -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Saldo Awal</label>
                                            <input type="text" name="saldo_awal" id="saldo_awal" class="form-control"
                                                placeholder="0" value="<?= set_value('saldo_awal', '0') ?>">
                                            <small class="text-muted">Opsional - Default: 0</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Kas/Bank</label>
                                            <select name="is_kas_bank" class="form-control">
                                                <option value="0" <?= set_select('is_kas_bank', '0', TRUE) ?>>Tidak
                                                </option>
                                                <option value="1" <?= set_select('is_kas_bank', '1') ?>>Ya</option>
                                            </select>
                                            <small class="text-muted">Tandai jika akun ini adalah Kas atau Bank</small>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fa fa-save"></i> Simpan Akun
                                    </button>
                                </div>
                            </form>
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
            // Hanya angka di kode perkiraan
            $('#kode_perkiraan').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Hanya angka di akun induk (karena berdasarkan data database)
            $('#akun_induk').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Format saldo awal dengan titik ribuan
            $('#saldo_awal').on('input', function () {
                let val = this.value.replace(/\./g, ''); // Hapus titik
                val = val.replace(/[^0-9]/g, ''); // Hanya angka
                if (val) {
                    this.value = parseInt(val).toLocaleString('id-ID');
                } else {
                    this.value = '0';
                }
            });

            // ESC = Kembali
            $(document).on('keyup', function (e) {
                if (e.key === "Escape") {
                    window.location.href = '<?= base_url('akunbiaya') ?>';
                }
            });
        });
    </script>
</body>

</html>
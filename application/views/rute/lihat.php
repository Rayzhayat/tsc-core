<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
</head>

<body id="page-top" class="fixed-nav">
    <div id="wrapper">
        <?php $this->load->view('partials/sidebar') ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php $this->load->view('partials/topbar') ?>

                <div class="container-fluid">
                    <!-- JUDUL + TOMBOL -->
                    <!-- DI ATAS CARD TABEL, TAMBAH TOMBOL IMPORT -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?= $title ?></h1>

                        <?php
                        $level = $this->session->userdata('login')['user_level'] ?? '';
                        $can_edit = in_array($level, ['superadmin', 'admin_document']);
                        ?>
                        <?php if ($can_edit): ?>
                            <div>
                                <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#modalImport">
                                    Import Excel
                                </button>
                                <a href="<?= base_url('rute/tambah') ?>" class="btn btn-primary btn-sm">
                                    Tambah Rute
                                </a>
                            </div>
                        <?php endif ?>
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

                    <!-- CARD + SEARCH + FILTER + ENTRI -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Daftar Rute</h6>
                                </div>
                                <div class="col-md-3">
                                    <form id="searchForm" class="d-flex">
                                        <input type="text" id="searchInput" class="form-control form-control-sm"
                                            placeholder="Cari customer, origin..." autocomplete="off">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary btn-sm" type="submit">
                                                Search
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <!-- FILTER TIPE UNIT SEARCH -->
                                <div class="col-md-3">
                                    <select id="filterTipeUnit" class="form-control form-control-sm">
                                        <option value="">Semua Tipe Unit</option>
                                        <option value="WB">WB</option>
                                        <option value="CDDL">CDDL</option>
                                        <option value="CDD">CDD</option>
                                        <option value="CDE">CDE</option>
                                        <option value="L300">L300</option>
                                        <option value="FUSO">FUSO</option>
                                        <option value="Tronton">Tronton</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filterService" class="form-control form-control-sm">
                                        <option value="">Semua Service</option>
                                        <option value="FTL">FTL</option>
                                        <option value="Daily">Daily</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <label class="mr-2 mb-0 text-muted small">Show</label>
                                        <select id="limitEntries" class="form-control form-control-sm" style="width: 70px;">
                                            <option value="10">10</option>
                                            <option value="25" selected>25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                        <label class="ml-2 mb-0 text-muted small">entries</label>
                                    </div>
                                </div>
                            </div>
                            <!-- 🔥 FIXED: Renamed class to avoid conflict with sidebar notification badge -->
                            <div class="mt-2">
                                <span class="badge badge-secondary badge-info-count">
                                    <i class="fas fa-database"></i>
                                    <?= number_format(count($all_rute ?? []), 0, ',', '.') ?> Rute
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th>Kode Rute</th>
                                            <th>Customer</th>
                                            <th>Service</th>
                                            <th>Tipe Unit</th>
                                            <th>SLA</th>
                                            <th>Origin</th>
                                            <th>Dest 1</th>
                                            <th>Dest 2</th>
                                            <th>Dest 3</th>
                                            <th>Dest 4</th>
                                            <th>Harga</th>
                                            <?php if ($can_edit): ?>
                                                <th width="10%">Aksi</th>
                                            <?php endif ?>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody">
                                        <?php
                                        $all_rute = $all_rute ?? [];
                                        $no = 1;
                                        foreach ($all_rute as $r):
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><strong><?= htmlspecialchars($r->kode_rute) ?></strong></td>
                                                <td><?= htmlspecialchars($r->customer) ?></td>
                                                <td><?= htmlspecialchars($r->service) ?></td>
                                                <td><?= htmlspecialchars($r->tipe_unit) ?></td>
                                                <td><?= htmlspecialchars($r->sla) ?></td>
                                                <td><?= htmlspecialchars($r->origin) ?></td>
                                                <td><?= htmlspecialchars($r->dest1) ?></td>
                                                <td><?= $r->dest2 ?: '-' ?></td>
                                                <td><?= $r->dest3 ?: '-' ?></td>
                                                <td><?= $r->dest4 ?: '-' ?></td>
                                                <td class="text-right"><?= number_format($r->harga, 0, ',', '.') ?></td>
                                                <?php if ($can_edit): ?>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('rute/ubah/' . $r->id) ?>"
                                                            class="btn btn-success btn-sm" title="Ubah">
                                                            Edit
                                                        </a>
                                                        <a onclick="return confirm('Yakin hapus rute <?= htmlspecialchars($r->kode_rute) ?>?')"
                                                            href="<?= base_url('rute/hapus/' . $r->id) ?>"
                                                            class="btn btn-danger btn-sm" title="Hapus">
                                                            Delete
                                                        </a>
                                                    </td>
                                                <?php endif ?>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($all_rute)): ?>
                                            <tr>
                                                <td colspan="<?= $can_edit ? '13' : '12' ?>" class="text-center text-muted">
                                                    <em>Tidak ada data rute.</em>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="paginationInfo" class="mt-3 text-muted small"></div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div id="paginationInfo" class="text-muted small"></div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrev" disabled>
                                        <i class="fa fa-chevron-left"></i> Previous
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnNext" disabled>
                                        Next <i class="fa fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>
    <!-- MODAL IMPORT (DI BAWAH CARD TABEL) -->
    <div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Master Rute dari Excel</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <?php if ($this->session->flashdata('import_success')): ?>
                        <div class="alert alert-success">
                            <?= $this->session->flashdata('import_success') ?>
                        </div>
                    <?php endif ?>
                    <?php if ($this->session->flashdata('import_error')): ?>
                        <div class="alert alert-danger">
                            <?= $this->session->flashdata('import_error') ?>
                        </div>
                    <?php endif ?>

                    <form action="<?= base_url('rute/proses_import') ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Pilih File Excel (.xlsx)</label>
                            <input type="file" name="file_excel" class="form-control-file" accept=".xlsx" required>
                            <small class="text-muted">
                                Kolom: Customer | Service | Tipe Unit | SLA | Origin | Dest 1 | Dest 2 (ops) | Dest 3 (ops) | Dest 4 (ops) | Harga
                            </small>
                        </div>
                        <button type="submit" class="btn btn-success">
                            Import Sekarang
                        </button>
                    </form>

                    <hr>
                    <p><a href="<?= base_url('assets/template/master_rute_template.xlsx') ?>" class="btn btn-info btn-sm">
                            Download Template Excel
                        </a></p>
                </div>
            </div>
        </div>
    </div>
    <?php $this->load->view('partials/js') ?>
    <script>
        $(document).ready(function() {
            let searchTimeout;
            let currentPage = 1;
            let totalPages = 1;

            function loadData() {
                let keyword = $('#searchInput').val().trim();
                let service = $('#filterService').val();
                let tipe_unit = $('#filterTipeUnit').val();
                let limit = parseInt($('#limitEntries').val());
                let offset = (currentPage - 1) * limit;

                $.ajax({
                    url: '<?= base_url('rute/filter') ?>',
                    method: 'POST',
                    data: {
                        keyword: keyword,
                        service: service,
                        tipe_unit: tipe_unit,
                        limit: limit,
                        offset: offset
                    },
                    success: function(response) {
                        let data = JSON.parse(response);
                        $('#tableBody').html(data.html);

                        // 🔥 FIXED: Update badge dengan class yang baru
                        $('.badge-info-count').html(
                            '<i class="fas fa-database"></i> ' +
                            data.total.toLocaleString('id-ID') + ' Rute'
                        );

                        // Hitung total halaman
                        totalPages = Math.ceil(data.total / limit);

                        // Update info
                        $('#paginationInfo').html(
                            data.total > 0 ?
                            `Menampilkan ${data.start} sampai ${data.end} dari ${data.total.toLocaleString('id-ID')} entri` :
                            'Menampilkan 0 entri'
                        );

                        // Update tombol pagination
                        $('#btnPrev').prop('disabled', currentPage <= 1);
                        $('#btnNext').prop('disabled', currentPage >= totalPages);
                    }
                });
            }

            // Trigger
            $('#searchForm, #filterService, #filterTipeUnit, #limitEntries').on('submit change', function(e) {
                if (e.type === 'submit') e.preventDefault();
                currentPage = 1;
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(loadData, 500);
            });

            $('#searchInput').on('keyup', function() {
                currentPage = 1;
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(loadData, 500);
            });

            // NAVIGASI PAGE
            $('#btnPrev').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    loadData();
                }
            });

            $('#btnNext').on('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadData();
                }
            });

            // ESC = RESET
            $(document).on('keyup', function(e) {
                if (e.key === "Escape") {
                    $('#searchInput').val('');
                    $('#filterService').val('');
                    $('#filterTipeUnit').val('');
                    $('#limitEntries').val('25');
                    currentPage = 1;
                    loadData();
                }
            });

            // INIT
            loadData();
        });
    </script>
</body>

</html>
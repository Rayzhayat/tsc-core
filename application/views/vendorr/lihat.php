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

                    <?php
                    $can_edit   = $can_edit   ?? false;
                    $can_delete = $can_delete ?? false;
                    ?>

                    <!-- HEADER -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="mb-0"><?= $title ?></h2>
                        <?php if ($can_edit): ?>
                            <div class="d-flex gap-2">
                                <a href="<?= base_url('vendorr/export') ?>" class="btn btn-danger btn-sm">
                                    <i class="fa fa-file-pdf me-1"></i>Export PDF
                                </a>
                                <a href="<?= base_url('vendorr/tambah') ?>" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus me-1"></i>Tambah Vendor
                                </a>
                            </div>
                        <?php endif ?>
                    </div>

                    <!-- FLASH MESSAGES -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- CARD -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="mb-0 fw-semibold text-primary">Daftar Vendor</h6>
                                    <span class="badge bg-primary badge-vendor-count">
                                        <i class="fas fa-truck me-1"></i><?= number_format(count($all_vendorr ?? []), 0, ',', '.') ?> Vendor
                                    </span>
                                </div>
                                <?php if (!$can_delete && $can_edit): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Finance Staff: tidak bisa delete
                                    </small>
                                <?php endif ?>
                            </div>
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <form id="searchForm" class="input-group input-group-sm">
                                        <input type="text" id="searchInput" class="form-control"
                                            placeholder="Cari nama, NPWP..." autocomplete="off">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-2">
                                    <select id="filterPPN" class="form-select form-select-sm">
                                        <option value="">Semua PPN</option>
                                        <option value="Belum PPN">Belum PPN</option>
                                        <option value="11%">11%</option>
                                        <option value="1.1%">1.1%</option>
                                        <option value="0.1%">0.1%</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select id="filterPPH" class="form-select form-select-sm">
                                        <option value="">Semua PPH</option>
                                        <option value="Belum PPH">Belum PPH</option>
                                        <option value="2%">2%</option>
                                        <option value="2.5%">2.5%</option>
                                        <option value="0.5%">0.5%</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="mb-0 text-muted small text-nowrap">Show</label>
                                        <select id="limitEntries" class="form-select form-select-sm" style="width:70px">
                                            <option value="10">10</option>
                                            <option value="25" selected>25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm mb-0" id="dataTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="3%">No</th>
                                            <th width="13%">Nama Vendor</th>
                                            <th width="17%">Alamat</th>
                                            <th width="11%">NPWP</th>
                                            <th width="9%">PIC</th>
                                            <th width="9%">No Telp</th>
                                            <th width="5%">PPN</th>
                                            <th width="5%">PPH</th>
                                            <th width="7%">Dokumen</th>
                                            <th width="<?= $can_edit ? '13%' : '8%' ?>">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <small id="paginationInfo" class="text-muted"></small>
                            <div class="d-flex align-items-center gap-2">
                                <button id="btnPrevious" class="btn btn-sm btn-outline-primary" disabled>
                                    <i class="fa fa-chevron-left"></i> Previous
                                </button>
                                <small id="pageIndicator" class="text-muted">Page 1</small>
                                <button id="btnNext" class="btn btn-sm btn-outline-primary" disabled>
                                    Next <i class="fa fa-chevron-right"></i>
                                </button>
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
            let searchTimeout;
            let currentPage  = 1;
            let totalPages   = 1;
            let totalRecords = 0;

            function loadData() {
                const keyword = $('#searchInput').val().trim();
                const ppn     = $('#filterPPN').val();
                const pph     = $('#filterPPH').val();
                const limit   = parseInt($('#limitEntries').val());
                const offset  = (currentPage - 1) * limit;

                $.post('<?= base_url('vendorr/filter') ?>', { keyword, ppn, pph, limit, offset }, function (response) {
                    const data = JSON.parse(response);
                    $('#tableBody').html(data.html);

                    totalRecords = data.total;
                    totalPages   = Math.ceil(totalRecords / limit) || 1;

                    $('.badge-vendor-count').html('<i class="fas fa-truck me-1"></i>' + totalRecords.toLocaleString('id-ID') + ' Vendor');

                    $('#paginationInfo').text(
                        totalRecords > 0
                            ? `Menampilkan ${data.start}–${data.end} dari ${totalRecords.toLocaleString('id-ID')} entri`
                            : 'Menampilkan 0 entri'
                    );
                    $('#pageIndicator').text(`Page ${currentPage} of ${totalPages}`);
                    $('#btnPrevious').prop('disabled', currentPage <= 1);
                    $('#btnNext').prop('disabled', currentPage >= totalPages || totalRecords === 0);
                }).fail(function () {
                    alert('Gagal memuat data!');
                });
            }

            function resetAndLoad() {
                currentPage = 1;
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(loadData, 400);
            }

            $('#btnPrevious').on('click', function () { if (currentPage > 1) { currentPage--; loadData(); } });
            $('#btnNext').on('click',     function () { if (currentPage < totalPages) { currentPage++; loadData(); } });

            $('#searchForm').on('submit', function (e) { e.preventDefault(); resetAndLoad(); });
            $('#searchInput').on('keyup', resetAndLoad);
            $('#filterPPN, #filterPPH, #limitEntries').on('change', resetAndLoad);

            $(document).on('keyup', function (e) {
                if (e.key === 'Escape') {
                    $('#searchInput').val('');
                    $('#filterPPN, #filterPPH').val('');
                    $('#limitEntries').val('25');
                    resetAndLoad();
                }
            });

            $(document).on('keydown', function (e) {
                if (e.altKey && e.key === 'ArrowLeft'  && currentPage > 1)          { currentPage--; loadData(); }
                if (e.altKey && e.key === 'ArrowRight' && currentPage < totalPages) { currentPage++; loadData(); }
            });

            loadData();
        });
    </script>
</body>

</html>
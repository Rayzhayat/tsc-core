<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .badge-purple {
            background-color: #6f42c1;
            color: #fff;
        }

        .table-terhapus thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
        }

        .table-terhapus tbody td {
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .no-shipment {
            font-family: monospace;
            font-weight: 700;
            font-size: 1rem;
            color: #e74a3b;
        }

        #bulkBar {
            transition: all .3s ease;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <!-- HEADER -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-trash-restore text-warning"></i> Data Terhapus — FTL Non SPX
                        </h1>
                        <a href="<?= base_url('ftl_non_spx') ?>" class="btn btn-secondary btn-sm shadow-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- ALERTS -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">×</button>
                        </div>
                    <?php endif; ?>

                    <!-- INFO BOX -->
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Superadmin Only.</strong> Data di halaman ini sudah di-soft delete.
                        Kamu bisa <strong>Pulihkan</strong> atau <strong>Hapus Permanen</strong> — bisa satu-satu atau
                        pilih banyak sekaligus.
                        Hapus permanen <strong>tidak bisa dibatalkan</strong>.
                    </div>

                    <!-- ==================== FILTER ==================== -->
                    <div class="card shadow mb-3">
                        <div class="card-header py-2 bg-light">
                            <h6 class="m-0 font-weight-bold text-secondary">
                                <i class="fas fa-filter"></i> Filter Data Terhapus
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="small font-weight-bold mb-1">Cari</label>
                                    <input type="text" id="fKeyword" class="form-control form-control-sm"
                                        placeholder="No Shipment, Customer, Vendor...">
                                </div>
                                <div class="col-md-2">
                                    <label class="small font-weight-bold mb-1">Status</label>
                                    <select id="fStatus" class="form-control form-control-sm">
                                        <option value="">Semua Status</option>
                                        <option>Sourcing Vendor</option>
                                        <option>Scheduled</option>
                                        <option>Tiba di Lokasi Muat</option>
                                        <option>Loading</option>
                                        <option>On Trip</option>
                                        <option>Tiba di Lokasi Bongkar</option>
                                        <option>Completed</option>
                                        <option>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="small font-weight-bold mb-1">Dihapus Dari</label>
                                    <input type="date" id="fDateFrom" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <label class="small font-weight-bold mb-1">Dihapus Sampai</label>
                                    <input type="date" id="fDateTo" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button id="btnFilter" class="btn btn-primary btn-sm mr-2">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <button id="btnReset" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== BULK ACTION BAR ==================== -->
                    <div id="bulkBar" class="card shadow mb-3 border-left-primary d-none">
                        <div class="card-body py-2 d-flex align-items-center">
                            <span class="font-weight-bold text-primary mr-3">
                                <i class="fas fa-check-square"></i>
                                <span id="selectedCount">0</span> data dipilih
                            </span>
                            <button id="btnBulkRestore" class="btn btn-success btn-sm mr-2">
                                <i class="fas fa-trash-restore"></i> Pulihkan Semua Terpilih
                            </button>
                            <button id="btnBulkDelete" class="btn btn-danger btn-sm mr-3">
                                <i class="fas fa-skull-crossbones"></i> Hapus Permanen Terpilih
                            </button>
                            <button id="btnClearSelect" class="btn btn-outline-secondary btn-sm ml-auto">
                                <i class="fas fa-times"></i> Batal Pilih
                            </button>
                        </div>
                    </div>

                    <!-- ==================== TABLE ==================== -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-gradient-warning">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 font-weight-bold text-white">
                                        <i class="fas fa-list"></i> Daftar Shipment Terhapus
                                    </h6>
                                </div>
                                <div class="col-auto">
                                    <span class="badge badge-light" id="totalBadge">
                                        <i class="fas fa-database"></i> <span
                                            id="totalCount"><?= count($shipments) ?></span> Data
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-terhapus mb-0" id="tblTerhapus">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="3%" class="text-center">
                                                <input type="checkbox" id="checkAll" title="Pilih Semua">
                                            </th>
                                            <th width="3%">No</th>
                                            <th width="8%">No Shipment</th>
                                            <th width="10%">Customer</th>
                                            <th width="6%">Origin</th>
                                            <th width="10%">Destination</th>
                                            <th width="5%">Truck</th>
                                            <th width="8%">Vendor</th>
                                            <th width="9%">Nopol / Driver</th>
                                            <th width="7%">Status</th>
                                            <th width="8%">Dihapus Pada</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tblBody">
                                        <?php
                                        $status_colors = [
                                            'Scheduled' => 'secondary',
                                            'Sourcing Vendor' => 'dark',
                                            'Loading' => 'info',
                                            'On Trip' => 'primary',
                                            'Tiba di Lokasi Muat' => 'warning',
                                            'Tiba di Lokasi Bongkar' => 'purple',
                                            'Completed' => 'success',
                                            'Cancelled' => 'danger',
                                        ];
                                        ?>
                                        <?php if (empty($shipments)): ?>
                                            <tr id="emptyRow">
                                                <td colspan="12" class="text-center text-muted py-5">
                                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                    <em>Tidak ada data yang terhapus.</em>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1;
                                            foreach ($shipments as $s):
                                                $status = $s->status_shipment ?? '-';
                                                $color = $status_colors[$status] ?? 'secondary';
                                                ?>
                                                <tr class="table-danger" data-id="<?= $s->id ?>"
                                                    data-no="<?= htmlspecialchars($s->no_shipment ?? '') ?>" data-keyword="<?= strtolower(htmlspecialchars(
                                                            ($s->no_shipment ?? '') . ' ' .
                                                            ($s->nama_customer ?? '') . ' ' .
                                                            ($s->nama_vendor ?? '') . ' ' .
                                                            ($s->origin ?? '') . ' ' .
                                                            ($s->dest1 ?? '') . ' ' .
                                                            ($s->driver ?? '') . ' ' .
                                                            ($s->nopol ?? '')
                                                        )) ?>" data-status="<?= htmlspecialchars($status) ?>"
                                                    data-deleted="<?= !empty($s->deleted_at) ? date('Y-m-d', strtotime($s->deleted_at)) : '' ?>">

                                                    <td class="text-center">
                                                        <input type="checkbox" class="row-check" value="<?= $s->id ?>">
                                                    </td>
                                                    <td class="text-center row-no"><?= $no++ ?></td>
                                                    <td class="text-center">
                                                        <span
                                                            class="no-shipment"><?= htmlspecialchars($s->no_shipment ?? '') ?></span>
                                                    </td>
                                                    <td><?= htmlspecialchars($s->nama_customer ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($s->origin ?? '-') ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($s->dest1 ?? '-') ?>
                                                        <?php if (!empty($s->dest2)): ?>
                                                            <br><small class="text-muted">→
                                                                <?= htmlspecialchars($s->dest2) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (!empty($s->truck_type)): ?>
                                                            <span
                                                                class="badge badge-dark"><?= htmlspecialchars($s->truck_type) ?></span>
                                                        <?php else: ?>-<?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($s->nama_vendor ?? '-') ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($s->nopol ?? '-') ?></strong>
                                                        <?php if (!empty($s->driver)): ?>
                                                            <br><small
                                                                class="text-muted"><?= htmlspecialchars($s->driver) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge badge-<?= $color ?>"><?= htmlspecialchars($status) ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <small class="text-danger">
                                                            <i class="fas fa-clock"></i>
                                                            <?= !empty($s->deleted_at) ? date('d/m/Y H:i', strtotime($s->deleted_at)) : '-' ?>
                                                        </small>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('ftl_non_spx/restore/' . $s->id) ?>"
                                                            class="btn btn-success btn-sm mb-1"
                                                            onclick="return confirm('Pulihkan shipment <?= htmlspecialchars($s->no_shipment ?? '') ?>?')"
                                                            title="Pulihkan">
                                                            <i class="fas fa-trash-restore"></i> Pulihkan
                                                        </a>
                                                        <a href="<?= base_url('ftl_non_spx/hapus_permanen/' . $s->id) ?>"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('⚠️ HAPUS PERMANEN shipment <?= htmlspecialchars($s->no_shipment ?? '') ?>?\n\nData TIDAK BISA dipulihkan kembali!')"
                                                            title="Hapus Permanen">
                                                            <i class="fas fa-skull-crossbones"></i> Hapus Permanen
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
            const BASE = '<?= base_url() ?>';

            // =============================================
            // FILTER (client-side — data sudah di DOM)
            // =============================================
            function applyFilter() {
                const kw = $('#fKeyword').val().toLowerCase().trim();
                const status = $('#fStatus').val().toLowerCase();
                const dateFrom = $('#fDateFrom').val();
                const dateTo = $('#fDateTo').val();

                let visible = 0;
                let no = 1;

                $('#tblBody tr[data-id]').each(function () {
                    const rowKw = $(this).data('keyword') || '';
                    const rowStatus = ($(this).data('status') || '').toLowerCase();
                    const rowDeleted = $(this).data('deleted') || '';

                    let show = true;
                    if (kw && !rowKw.includes(kw)) show = false;
                    if (status && rowStatus !== status) show = false;
                    if (dateFrom && rowDeleted < dateFrom) show = false;
                    if (dateTo && rowDeleted > dateTo) show = false;

                    $(this).toggle(show);
                    if (show) $(this).find('.row-no').text(no++);
                });

                visible = no - 1;
                $('#totalCount').text(visible);

                // Show/hide empty state
                if (visible === 0) {
                    if ($('#noResultRow').length === 0) {
                        $('#tblBody').append('<tr id="noResultRow"><td colspan="12" class="text-center text-muted py-4"><i class="fas fa-search fa-2x mb-2 d-block"></i><em>Tidak ada data yang cocok dengan filter.</em></td></tr>');
                    }
                } else {
                    $('#noResultRow').remove();
                }

                // Reset checkboxes setelah filter
                clearSelection();
            }

            $('#btnFilter').on('click', applyFilter);

            $('#fKeyword').on('keypress', function (e) {
                if (e.which === 13) applyFilter();
            });

            $('#btnReset').on('click', function () {
                $('#fKeyword').val('');
                $('#fStatus').val('');
                $('#fDateFrom').val('');
                $('#fDateTo').val('');
                $('#tblBody tr[data-id]').show();
                $('#noResultRow').remove();
                $('#totalCount').text(<?= count($shipments) ?>);
                let no = 1;
                $('#tblBody tr[data-id]').each(function () { $(this).find('.row-no').text(no++); });
                clearSelection();
            });

            // =============================================
            // CHECKBOX — SELECT ALL
            // =============================================
            $('#checkAll').on('change', function () {
                const checked = $(this).is(':checked');
                // Hanya centang yang visible
                $('#tblBody tr[data-id]:visible .row-check').prop('checked', checked);
                updateBulkBar();
            });

            $(document).on('change', '.row-check', function () {
                const total = $('#tblBody tr[data-id]:visible .row-check').length;
                const checked = $('#tblBody tr[data-id]:visible .row-check:checked').length;
                $('#checkAll').prop('indeterminate', checked > 0 && checked < total);
                $('#checkAll').prop('checked', checked === total && total > 0);
                updateBulkBar();
            });

            function getSelectedIds() {
                const ids = [];
                $('.row-check:checked').each(function () { ids.push($(this).val()); });
                return ids;
            }

            function updateBulkBar() {
                const ids = getSelectedIds();
                if (ids.length > 0) {
                    $('#selectedCount').text(ids.length);
                    $('#bulkBar').removeClass('d-none');
                } else {
                    $('#bulkBar').addClass('d-none');
                }
            }

            function clearSelection() {
                $('.row-check, #checkAll').prop('checked', false);
                $('#checkAll').prop('indeterminate', false);
                $('#bulkBar').addClass('d-none');
            }

            $('#btnClearSelect').on('click', clearSelection);

            // =============================================
            // BULK RESTORE
            // =============================================
            $('#btnBulkRestore').on('click', function () {
                const ids = getSelectedIds();
                if (ids.length === 0) return;
                if (!confirm('Pulihkan ' + ids.length + ' shipment terpilih?')) return;

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

                $.ajax({
                    url: BASE + 'ftl_non_spx/bulk_restore',
                    method: 'POST',
                    data: { ids: ids },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            // Hapus baris yang sudah dipulihkan dari DOM
                            ids.forEach(function (id) {
                                $('tr[data-id="' + id + '"]').fadeOut(400, function () { $(this).remove(); });
                            });
                            setTimeout(function () {
                                const remaining = $('#tblBody tr[data-id]:visible').length;
                                $('#totalCount').text(remaining);
                                clearSelection();
                                showAlert('success', '<i class="fas fa-check-circle"></i> ' + res.message);
                            }, 500);
                        } else {
                            alert(res.message || 'Gagal!');
                            $('#btnBulkRestore').prop('disabled', false).html('<i class="fas fa-trash-restore"></i> Pulihkan Semua Terpilih');
                        }
                    },
                    error: function () {
                        alert('Gagal koneksi ke server!');
                        $('#btnBulkRestore').prop('disabled', false).html('<i class="fas fa-trash-restore"></i> Pulihkan Semua Terpilih');
                    }
                });
            });

            // =============================================
            // BULK HAPUS PERMANEN
            // =============================================
            $('#btnBulkDelete').on('click', function () {
                const ids = getSelectedIds();
                if (ids.length === 0) return;
                if (!confirm('⚠️ HAPUS PERMANEN ' + ids.length + ' shipment terpilih?\n\nData TIDAK BISA dipulihkan kembali!')) return;

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

                $.ajax({
                    url: BASE + 'ftl_non_spx/bulk_hapus_permanen',
                    method: 'POST',
                    data: { ids: ids },
                    dataType: 'json',
                    success: function (res) {
                        if (res.success) {
                            ids.forEach(function (id) {
                                $('tr[data-id="' + id + '"]').fadeOut(400, function () { $(this).remove(); });
                            });
                            setTimeout(function () {
                                const remaining = $('#tblBody tr[data-id]:visible').length;
                                $('#totalCount').text(remaining);
                                clearSelection();
                                showAlert('danger', '<i class="fas fa-skull-crossbones"></i> ' + res.message);
                            }, 500);
                        } else {
                            alert(res.message || 'Gagal!');
                            $('#btnBulkDelete').prop('disabled', false).html('<i class="fas fa-skull-crossbones"></i> Hapus Permanen Terpilih');
                        }
                    },
                    error: function () {
                        alert('Gagal koneksi ke server!');
                        $('#btnBulkDelete').prop('disabled', false).html('<i class="fas fa-skull-crossbones"></i> Hapus Permanen Terpilih');
                    }
                });
            });

            // =============================================
            // HELPER: show alert dinamis
            // =============================================
            function showAlert(type, msg) {
                const el = $('<div class="alert alert-' + type + ' alert-dismissible fade show">' + msg +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert">×</button></div>');
                $('.container-fluid').prepend(el);
                setTimeout(function () { el.fadeOut('slow', function () { el.remove(); }); }, 5000);
            }

            setTimeout(function () { $('.alert-dismissible').fadeOut('slow'); }, 5000);
        });
    </script>
</body>

</html>
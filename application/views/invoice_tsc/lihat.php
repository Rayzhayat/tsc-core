<!-- lihat.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .stats-card {
            border-left: 4px solid;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15) !important;
        }
        .badge-draft     { background:#858796; color:#fff; }
        .badge-sent      { background:#4e73df; color:#fff; }
        .badge-unsent    { background:#f6c23e; color:#333; }
        .badge-paid      { background:#1cc88a; color:#fff; }
        .badge-cancelled { background:#e74a3b; color:#fff; }
        .badge-overdue   { background:#f6c23e; color:#333; }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <!-- FLASH -->
                    <?php foreach (['success'=>'success','error'=>'danger','warning'=>'warning'] as $key => $cls): ?>
                        <?php if ($this->session->flashdata($key)): ?>
                            <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                                <?= $this->session->flashdata($key) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif ?>
                    <?php endforeach ?>

                    <!-- PAGE HEADER -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-file-invoice text-success me-2"></i><?= $title ?>
                            </h2>
                            <small class="text-muted">Kelola invoice penagihan ke customer</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('invoice_tsc/tambah') ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-1"></i> Buat Invoice
                            </a>
                            <a href="<?= base_url('invoice_tsc/export_excel?' . http_build_query($filters)) ?>"
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </a>
                        </div>
                    </div>

                    <!-- SUMMARY CARDS -->
                    <div class="row g-3 mb-3">
                        <?php
                        $stat_cards = [
                            ['label'=>'Total Invoice',    'val'=>number_format($summary->total_invoice ?? 0),                                  'color'=>'#4e73df','icon'=>'fas fa-file-invoice'],
                            ['label'=>'Total Amount',     'val'=>'Rp '.number_format($summary->total_amount ?? 0, 0, ',', '.'),                'color'=>'#1cc88a','icon'=>'fas fa-money-bill-wave'],
                            ['label'=>'Outstanding',      'val'=>'Rp '.number_format($summary->outstanding_amount ?? 0, 0, ',', '.'),          'color'=>'#f6c23e','icon'=>'fas fa-clock'],
                            ['label'=>'Paid Invoice',     'val'=>number_format($summary->paid ?? 0),                                           'color'=>'#36b9cc','icon'=>'fas fa-check-circle'],
                        ];
                        foreach ($stat_cards as $sc): ?>
                            <div class="col-xl-3 col-md-6">
                                <div class="card stats-card shadow-sm h-100" style="border-left-color:<?= $sc['color'] ?>">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="font-size:1.6rem;color:<?= $sc['color'] ?>">
                                                <i class="<?= $sc['icon'] ?>"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold fs-5 lh-1"><?= $sc['val'] ?></div>
                                                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px"><?= $sc['label'] ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <!-- FILTER CARD -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header py-2" style="border-left:4px solid #1cc88a;">
                            <h6 class="mb-0 fw-bold text-success">
                                <i class="fas fa-filter me-1"></i> Filter &amp; Pencarian
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <form method="get" action="<?= base_url('invoice_tsc') ?>">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold mb-1 small">Customer</label>
                                        <select name="customer_id" class="form-select form-select-sm">
                                            <option value="">Semua Customer</option>
                                            <?php foreach ($customers as $cust): ?>
                                                <option value="<?= $cust->kode ?>"
                                                    <?= ($filters['customer_id'] ?? '') == $cust->kode ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cust->nama) ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold mb-1 small">Tgl Invoice — Dari</label>
                                        <input type="date" name="date_from" class="form-control form-control-sm"
                                            value="<?= $filters['date_from'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold mb-1 small">Tgl Invoice — Sampai</label>
                                        <input type="date" name="date_to" class="form-control form-control-sm"
                                            value="<?= $filters['date_to'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold mb-1 small">Periode Shipment</label>
                                        <select name="periode_shipment" class="form-select form-select-sm">
                                            <option value="">Semua Periode</option>
                                            <?php foreach ($bulan_options as $bulan): ?>
                                                <option value="<?= $bulan ?>"
                                                    <?= ($filters['periode_shipment'] ?? '') == $bulan ? 'selected' : '' ?>>
                                                    <?= $bulan ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label fw-semibold mb-1 small">Status</label>
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="">Semua</option>
                                            <option value="draft"     <?= ($filters['status'] ?? '') == 'draft'     ? 'selected' : '' ?>>Draft</option>
                                            <option value="sent"      <?= ($filters['status'] ?? '') == 'sent'      ? 'selected' : '' ?>>Sent</option>
                                            <option value="unsent"    <?= ($filters['status'] ?? '') == 'unsent'    ? 'selected' : '' ?>>Unsent</option>
                                            <option value="paid"      <?= ($filters['status'] ?? '') == 'paid'      ? 'selected' : '' ?>>Paid</option>
                                            <option value="cancelled" <?= ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold mb-1 small">Keyword</label>
                                        <input type="text" name="keyword" class="form-control form-control-sm"
                                            placeholder="No. Invoice / No. Faktur"
                                            value="<?= $filters['keyword'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-search me-1"></i> Cari
                                    </button>
                                    <a href="<?= base_url('invoice_tsc') ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-redo me-1"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- TABLE CARD -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header py-2 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold text-success">
                                <i class="fas fa-list me-1"></i> Daftar Invoice TSC
                            </h6>
                            <!-- Show entries + info -->
                            <div class="d-flex align-items-center gap-2">
                                <?php if (array_filter($filters)): ?>
                                    <span class="badge bg-info">Filtered</span>
                                <?php endif ?>
                                <small class="text-muted text-nowrap">
                                    <?php
                                    $showing_from = $pagination['offset'] + 1;
                                    $showing_to   = min($pagination['offset'] + $pagination['per_page'], $pagination['total_records']);
                                    ?>
                                    <?= number_format($showing_from) ?>–<?= number_format($showing_to) ?>
                                    dari <?= number_format($pagination['total_records']) ?> entri
                                </small>
                                <form method="get" id="perPageForm" class="d-flex align-items-center gap-1">
                                    <?php foreach ($filters as $key => $value): ?>
                                        <?php if (!empty($value)): ?>
                                            <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
                                        <?php endif ?>
                                    <?php endforeach ?>
                                    <span class="text-muted small">Show</span>
                                    <select name="per_page" class="form-select form-select-sm" style="width:65px"
                                        onchange="this.form.submit()">
                                        <?php foreach ([10,25,50,100] as $n): ?>
                                            <option value="<?= $n ?>" <?= ($pagination['per_page'] ?? 10) == $n ? 'selected' : '' ?>><?= $n ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0"
                                       style="font-size:.84rem;">
                                    <thead class="table-success">
                                        <tr>
                                            <th class="text-center" style="width:40px">No</th>
                                            <th style="width:130px">No. Invoice</th>
                                            <th style="width:85px">Tgl Invoice</th>
                                            <th style="width:110px">Periode Shipment</th>
                                            <th>Customer</th>
                                            <th style="width:100px">No. Faktur</th>
                                            <th style="width:90px">No. PO</th>
                                            <th class="text-end" style="width:120px">Grand Total</th>
                                            <th class="text-center" style="width:80px">Status</th>
                                            <th class="text-center" style="width:160px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($invoices)): ?>
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-5">
                                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                    Belum ada data invoice
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; foreach ($invoices as $inv): ?>
                                                <tr>
                                                    <td class="text-center text-muted"><?= $no++ ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($inv->no_invoice) ?></strong>
                                                        <div class="text-muted" style="font-size:.75rem">
                                                            Jatuh Tempo: <?= date('d/m/Y', strtotime($inv->due_date)) ?>
                                                        </div>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($inv->invoice_date)) ?></td>
                                                    <td>
                                                        <?php if (!empty($inv->periode_shipment)): ?>
                                                            <span class="badge bg-info text-white" style="font-size:.78rem">
                                                                <i class="fas fa-calendar-alt me-1"></i><?= htmlspecialchars($inv->periode_shipment) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($inv->customer_nama) ?></td>
                                                    <td class="text-muted small"><?= htmlspecialchars($inv->no_faktur ?? '—') ?></td>
                                                    <td>
                                                        <?php if (!empty($inv->no_po)): ?>
                                                            <span class="badge bg-primary" style="font-size:.75rem">
                                                                <?= htmlspecialchars($inv->no_po) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">—</span>
                                                        <?php endif ?>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong>Rp <?= number_format($inv->grand_total, 0, ',', '.') ?></strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $bc = 'badge-draft';
                                                        if ($inv->status=='sent')      $bc='badge-sent';
                                                        if ($inv->status=='unsent')    $bc='badge-unsent';
                                                        if ($inv->status=='paid')      $bc='badge-paid';
                                                        if ($inv->status=='cancelled') $bc='badge-cancelled';
                                                        ?>
                                                        <span class="badge <?= $bc ?>" style="font-size:.75rem">
                                                            <?= strtoupper($inv->status) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <!-- Detail -->
                                                        <a href="<?= base_url('invoice_tsc/detail/'.$inv->id) ?>"
                                                           class="btn btn-info btn-sm" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <!-- PDF -->
                                                        <a href="<?= base_url('invoice_tsc/export_pdf/'.$inv->id) ?>"
                                                           class="btn btn-danger btn-sm" title="Export PDF">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>

                                                        <?php if ($inv->status != 'paid' && $inv->status != 'cancelled'): ?>
                                                            <a href="<?= base_url('invoice_tsc/ubah/'.$inv->id) ?>"
                                                               class="btn btn-warning btn-sm" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if ($inv->status=='draft' || $inv->status=='unsent'): ?>
                                                                <button class="btn btn-primary btn-sm"
                                                                    onclick="updateStatus(<?= $inv->id ?>,'sent')"
                                                                    title="Mark as Sent">
                                                                    <i class="fas fa-paper-plane"></i>
                                                                </button>
                                                            <?php endif ?>
                                                            <?php if ($inv->status=='sent'): ?>
                                                                <button class="btn btn-warning btn-sm"
                                                                    onclick="updateStatus(<?= $inv->id ?>,'unsent')"
                                                                    title="Mark as Unsent">
                                                                    <i class="fas fa-undo"></i>
                                                                </button>
                                                                <button class="btn btn-success btn-sm"
                                                                    onclick="updateStatus(<?= $inv->id ?>,'paid')"
                                                                    title="Mark as Paid">
                                                                    <i class="fas fa-check-circle"></i>
                                                                </button>
                                                            <?php endif ?>
                                                            <button class="btn btn-secondary btn-sm"
                                                                onclick="confirmCancel(<?= $inv->id ?>)"
                                                                title="Cancel Invoice">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                            <button class="btn btn-danger btn-sm"
                                                                onclick="confirmDelete(<?= $inv->id ?>)"
                                                                title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>

                                                        <?php elseif ($inv->status=='cancelled'): ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-ban me-1"></i>Cancelled
                                                            </span>
                                                        <?php else: ?>
                                                            <?php if (($user_level ?? '')==='superadmin'): ?>
                                                                <a href="<?= base_url('invoice_tsc/ubah_paid/'.$inv->id) ?>"
                                                                   class="btn btn-warning btn-sm" title="Edit (Superadmin)">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <button class="btn btn-danger btn-sm"
                                                                    onclick="confirmDeletePaid(<?= $inv->id ?>)"
                                                                    title="Hapus (Superadmin)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">
                                                                    <i class="fas fa-lock me-1"></i>Paid
                                                                </span>
                                                            <?php endif ?>
                                                        <?php endif ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- PAGINATION -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <div class="card-footer py-2">
                                <nav>
                                    <ul class="pagination pagination-sm justify-content-center mb-0">
                                        <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link" href="<?= $pagination['current_page'] > 1
                                                ? base_url('invoice_tsc?'.http_build_query(array_merge($filters,['per_page'=>$pagination['per_page'],'page'=>$pagination['current_page']-1])))
                                                : '#' ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        <?php
                                        $sp = max(1, $pagination['current_page']-3);
                                        $ep = min($pagination['total_pages'], $pagination['current_page']+3);
                                        if ($sp > 1): ?>
                                            <li class="page-item"><a class="page-link" href="<?= base_url('invoice_tsc?'.http_build_query(array_merge($filters,['per_page'=>$pagination['per_page'],'page'=>1]))) ?>">1</a></li>
                                            <?php if ($sp > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif ?>
                                        <?php endif ?>
                                        <?php for ($i=$sp; $i<=$ep; $i++): ?>
                                            <li class="page-item <?= $i==$pagination['current_page']?'active':'' ?>">
                                                <a class="page-link" href="<?= base_url('invoice_tsc?'.http_build_query(array_merge($filters,['per_page'=>$pagination['per_page'],'page'=>$i]))) ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor ?>
                                        <?php if ($ep < $pagination['total_pages']): ?>
                                            <?php if ($ep < $pagination['total_pages']-1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif ?>
                                            <li class="page-item"><a class="page-link" href="<?= base_url('invoice_tsc?'.http_build_query(array_merge($filters,['per_page'=>$pagination['per_page'],'page'=>$pagination['total_pages']]))) ?>"><?= $pagination['total_pages'] ?></a></li>
                                        <?php endif ?>
                                        <li class="page-item <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                                            <a class="page-link" href="<?= $pagination['current_page'] < $pagination['total_pages']
                                                ? base_url('invoice_tsc?'.http_build_query(array_merge($filters,['per_page'=>$pagination['per_page'],'page'=>$pagination['current_page']+1])))
                                                : '#' ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif ?>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function updateStatus(id, status) {
            const msgs = {
                paid:   'Tandai invoice sebagai PAID?\n\nIni akan membuat jurnal pembayaran dan mengurangi piutang usaha.',
                sent:   'Tandai invoice sebagai SENT?\n\nInvoice akan ditandai sudah terkirim ke customer.',
                unsent: 'Tarik kembali invoice (UNSENT)?\n\nStatus invoice akan dikembalikan dan perlu dikirim ulang.'
            };
            if (confirm(msgs[status] || 'Lanjutkan?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('invoice_tsc/update_status/') ?>' + id;
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'status'; input.value = status;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function confirmCancel(id) {
            if (confirm('⚠️ CANCEL INVOICE\n\nInvoice akan dibatalkan dan tidak bisa diubah lagi.\n\nYakin?')) {
                updateStatus(id, 'cancelled');
            }
        }

        function confirmDelete(id) {
            if (confirm('Yakin ingin menghapus invoice ini?\n\nData akan dihapus permanent!')) {
                window.location.href = '<?= base_url('invoice_tsc/hapus/') ?>' + id;
            }
        }

        function confirmDeletePaid(id) {
            if (confirm('⚠️ PERINGATAN SUPERADMIN\n\nAnda akan menghapus invoice yang sudah PAID!\nIni akan menghapus jurnal akuntansi dan tidak bisa dikembalikan.\n\nYakin?')) {
                if (confirm('Konfirmasi sekali lagi — Hapus invoice PAID ID: ' + id + '?')) {
                    window.location.href = '<?= base_url('invoice_tsc/hapus/') ?>' + id;
                }
            }
        }

        setTimeout(() => document.querySelectorAll('.alert').forEach(el => $(el).fadeOut('slow')), 5000);
    </script>

    <?php if ($this->session->flashdata('download_pdf')): ?>
    <script>
        $(document).ready(function () {
            const pdfUrl   = '<?= $this->session->flashdata('download_pdf') ?>';
            const invoiceNo = '<?= $this->session->flashdata('invoice_no') ?>';
            Swal.fire({
                title: 'Invoice Berhasil Dibuat!',
                html: 'Invoice <strong>' + invoiceNo + '</strong> tersimpan.<br>PDF sedang didownload...',
                icon: 'success', timer: 3000, showConfirmButton: false, timerProgressBar: true
            });
            setTimeout(() => {
                const link = document.createElement('a');
                link.href = pdfUrl;
                link.download = 'Invoice_' + invoiceNo.replace(/\//g, '-') + '.pdf';
                document.body.appendChild(link); link.click(); document.body.removeChild(link);
            }, 500);
        });
    </script>
    <?php endif ?>
</body>
</html>
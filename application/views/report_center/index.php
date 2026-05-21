<!DOCTYPE html>
<html lang="id">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* ── Report Card ── */
        .report-card {
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            transition: all .25s ease;
            cursor: pointer;
        }

        .report-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .1);
            border-color: #4e73df;
        }

        .report-card .card-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* ── Filter panel ── */
        #filterPanel {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            display: none;
        }

        /* ── Preview area ── */
        #previewArea {
            display: none;
        }

        /* ── Table ── */
        #previewTable th {
            font-size: .73rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #4e73df;
        }

        #previewTable td {
            font-size: .82rem;
            vertical-align: middle;
        }

        /* ── Stat pill ── */
        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 600;
        }

        /* ── Selected card ── */
        .report-card.selected {
            border-color: #4e73df;
            background: #eef2ff;
        }

        .report-card.selected .card-title {
            color: #4e73df;
        }

        /* ── Loading overlay ── */
        #loadingOverlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .75);
            backdrop-filter: blur(3px);
            z-index: 10;
            border-radius: 10px;
            display: none;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <!-- Header -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-file-chart-line me-2 text-primary"></i><?= $title ?>
                            </h2>
                            <small class="text-muted">Pilih laporan, atur filter, lalu preview &amp; export</small>
                        </div>
                    </div>

                    <!-- Flash messages -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-1"></i> <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <!-- ═══ Report Menu Cards ═══ -->
                    <div class="row g-3 mb-4" id="reportMenu">

                        <?php if ($can_absensi): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card report-card h-100 p-3" data-report="absensi" onclick="selectReport(this)">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="card-icon bg-primary-lt">
                                            <i class="fas fa-camera text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="card-title fw-bold mb-1" style="font-size:.9rem;">Rekap Absensi
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem;">Kehadiran bulanan per karyawan
                                                / group</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if ($can_performa): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card report-card h-100 p-3" data-report="performa" onclick="selectReport(this)">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="card-icon bg-success-lt">
                                            <i class="fas fa-chart-line text-success"></i>
                                        </div>
                                        <div>
                                            <div class="card-title fw-bold mb-1" style="font-size:.9rem;">Performa Karyawan
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem;">Skor kehadiran, cuti, &amp;
                                                produktivitas</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if ($can_cuti): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card report-card h-100 p-3" data-report="cuti" onclick="selectReport(this)">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="card-icon bg-info-lt">
                                            <i class="fas fa-umbrella-beach text-info"></i>
                                        </div>
                                        <div>
                                            <div class="card-title fw-bold mb-1" style="font-size:.9rem;">Laporan Cuti</div>
                                            <div class="text-muted" style="font-size:.75rem;">Riwayat &amp; sisa jatah cuti
                                                karyawan</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if ($can_operasional): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card report-card h-100 p-3" data-report="operasional"
                                    onclick="selectReport(this)">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="card-icon bg-warning-lt">
                                            <i class="fas fa-truck-loading text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="card-title fw-bold mb-1" style="font-size:.9rem;">Summary
                                                Operasional</div>
                                            <div class="text-muted" style="font-size:.75rem;">FTL Non-SPX, Daily Rent, FTL
                                                SPX</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if ($can_finance): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card report-card h-100 p-3" data-report="keuangan" onclick="selectReport(this)">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="card-icon bg-danger-lt">
                                            <i class="fas fa-calculator text-danger"></i>
                                        </div>
                                        <div>
                                            <div class="card-title fw-bold mb-1" style="font-size:.9rem;">Laporan Keuangan
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem;">Pemasukan, pengeluaran &amp;
                                                neraca</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card report-card h-100 p-3" data-report="invoice" onclick="selectReport(this)">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="card-icon bg-teal-lt">
                                            <i class="fas fa-file-invoice text-teal"></i>
                                        </div>
                                        <div>
                                            <div class="card-title fw-bold mb-1" style="font-size:.9rem;">Rekap Invoice
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem;">Daftar invoice TSC per periode
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>

                        <?php if ($can_fleet): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card report-card h-100 p-3" data-report="fleet" onclick="selectReport(this)">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="card-icon" style="background:#e0f2fe;">
                                            <i class="fas fa-truck" style="color:#0284c7;"></i>
                                        </div>
                                        <div>
                                            <div class="card-title fw-bold mb-1" style="font-size:.9rem;">Laporan Fleet
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem;">Utilisasi &amp; kondisi unit
                                                kendaraan</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>

                    </div><!-- /reportMenu -->

                    <!-- ═══ Filter Panel ═══ -->
                    <div id="filterPanel" class="p-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0 fw-bold" id="filterPanelTitle">
                                <i class="fas fa-sliders-h me-2 text-primary"></i> Filter Laporan
                            </h6>
                            <span class="badge bg-primary" id="activeBadge">—</span>
                        </div>
                        <div class="row g-3" id="filterFields"></div>
                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <button class="btn btn-primary btn-sm" onclick="loadPreview()">
                                <i class="fas fa-eye me-1"></i> Preview Data
                            </button>
                            <button class="btn btn-success btn-sm" onclick="exportExcel()">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="exportPdf()">
                                <i class="fas fa-file-pdf me-1"></i> Export PDF
                            </button>
                            <button class="btn btn-secondary btn-sm ms-auto" onclick="resetFilter()">
                                <i class="fas fa-times me-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- ═══ Preview Area ═══ -->
                    <div id="previewArea">
                        <div class="card shadow-sm position-relative">
                            <div id="loadingOverlay">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" style="width:2.5rem;height:2.5rem;"></div>
                                    <div class="mt-2 fw-semibold text-muted">Memuat data...</div>
                                </div>
                            </div>
                            <div class="card-header py-2 d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 fw-bold text-primary" id="previewTitle">
                                    <i class="fas fa-table me-1"></i> Preview
                                </h6>
                                <div class="d-flex align-items-center gap-2 flex-wrap" id="previewStats"></div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0" id="previewTable">
                                        <thead class="table-light" id="previewThead"></thead>
                                        <tbody id="previewTbody">
                                            <tr>
                                                <td colspan="10" class="text-center py-5 text-muted">
                                                    <i class="fas fa-hand-pointer fa-2x mb-2 d-block"></i>
                                                    Pilih laporan &amp; klik <strong>Preview Data</strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer py-2 d-flex justify-content-between align-items-center">
                                <small class="text-muted" id="previewInfo">—</small>
                                <small class="text-muted" id="previewTimestamp">—</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ══ State ══
        let activeReport = null;

        const reportMeta = {
            absensi: { label: 'Rekap Absensi', icon: 'fa-camera', color: 'text-primary', filters: ['periode_bulan', 'periode_tahun', 'group_karyawan', 'user_id'] },
            performa: { label: 'Performa Karyawan', icon: 'fa-chart-line', color: 'text-success', filters: ['periode_tahun', 'group_karyawan'] },
            cuti: { label: 'Laporan Cuti', icon: 'fa-umbrella-beach', color: 'text-info', filters: ['periode_bulan', 'periode_tahun', 'status_cuti', 'group_karyawan'] },
            operasional: { label: 'Summary Operasional', icon: 'fa-truck-loading', color: 'text-warning', filters: ['periode_bulan', 'periode_tahun', 'tipe_shipment', 'customer_id'] },
            keuangan: { label: 'Laporan Keuangan', icon: 'fa-calculator', color: 'text-danger', filters: ['periode_bulan', 'periode_tahun', 'tipe_keuangan'] },
            invoice: { label: 'Rekap Invoice', icon: 'fa-file-invoice', color: 'text-teal', filters: ['periode_bulan', 'periode_tahun', 'customer_id', 'status_invoice'] },
            fleet: { label: 'Laporan Fleet', icon: 'fa-truck', color: 'text-primary', filters: ['periode_bulan', 'periode_tahun'] },
        };

        // ── Filter field builders ──
        const filterBuilders = {
            periode_bulan: () => {
                const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const now = new Date().getMonth(); // 0-based
                const opts = bulan.map((n, i) =>
                    `<option value="${i + 1}" ${i === now ? 'selected' : ''}>${n}</option>`
                ).join('');
                return `<div class="col-6 col-md-3">
            <label class="form-label form-label-sm fw-semibold">Bulan</label>
            <select id="f_bulan" class="form-select form-select-sm">${opts}</select>
        </div>`;
            },

            periode_tahun: () => {
                const y = new Date().getFullYear();
                const opts = [y - 1, y, y + 1].map(yr =>
                    `<option value="${yr}" ${yr === y ? 'selected' : ''}>${yr}</option>`
                ).join('');
                return `<div class="col-6 col-md-2">
            <label class="form-label form-label-sm fw-semibold">Tahun</label>
            <select id="f_tahun" class="form-select form-select-sm">${opts}</select>
        </div>`;
            },

            group_karyawan: () => `
        <div class="col-6 col-md-3">
            <label class="form-label form-label-sm fw-semibold">Group</label>
            <select id="f_group" class="form-select form-select-sm">
                <option value="">Semua Group</option>
                <option value="Yamazaki Staff">Yamazaki Staff</option>
                <option value="Admin TSC">Admin TSC</option>
                <option value="Operasional TSC">Operasional TSC</option>
                <option value="TSF Staff">TSF Staff</option>
                <option value="Sinar Boga Staff">Sinar Boga Staff</option>
                <option value="Rorotan Staff">Rorotan Staff</option>
            </select>
        </div>`,

            user_id: () => `
        <div class="col-6 col-md-3">
            <label class="form-label form-label-sm fw-semibold">Karyawan</label>
            <select id="f_user_id" class="form-select form-select-sm">
                <option value="">Semua Karyawan</option>
                <?php foreach ($daftar_karyawan as $k): ?>
                    <option value="<?= $k->id ?>"><?= htmlspecialchars($k->nama) ?> (<?= $k->nik ?>)</option>
                <?php endforeach ?>
            </select>
        </div>`,

            status_cuti: () => `
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm fw-semibold">Status</label>
            <select id="f_status_cuti" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="Pending">Pending</option>
                <option value="Disetujui">Disetujui</option>
                <option value="Ditolak">Ditolak</option>
            </select>
        </div>`,

            tipe_shipment: () => `
        <div class="col-6 col-md-3">
            <label class="form-label form-label-sm fw-semibold">Tipe</label>
            <select id="f_tipe_shipment" class="form-select form-select-sm">
                <option value="">Semua Tipe</option>
                <option value="ftl_non_spx">FTL Non-SPX</option>
                <option value="daily_rent">Daily Rent</option>
                <option value="ftl_spx">FTL SPX</option>
            </select>
        </div>`,

            tipe_keuangan: () => `
        <div class="col-6 col-md-3">
            <label class="form-label form-label-sm fw-semibold">Tipe</label>
            <select id="f_tipe_keuangan" class="form-select form-select-sm">
                <option value="">Pemasukan + Pengeluaran</option>
                <option value="pemasukan">Pemasukan</option>
                <option value="pengeluaran">Pengeluaran</option>
            </select>
        </div>`,

            customer_id: () => `
        <div class="col-6 col-md-3">
            <label class="form-label form-label-sm fw-semibold">Customer</label>
            <select id="f_customer_id" class="form-select form-select-sm">
                <option value="">Semua Customer</option>
                <?php foreach ($daftar_customer as $c): ?>
                    <option value="<?= $c->id ?>"><?= htmlspecialchars($c->nama) ?></option>
                <?php endforeach ?>
            </select>
        </div>`,

            status_invoice: () => `
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm fw-semibold">Status</label>
            <select id="f_status_invoice" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="unpaid">Unpaid</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
            </select>
        </div>`,
        };

        // ── Select report ──
        function selectReport(el) {
            document.querySelectorAll('.report-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            activeReport = el.dataset.report;

            const meta = reportMeta[activeReport];
            document.getElementById('activeBadge').textContent = meta.label;
            document.getElementById('filterPanelTitle').innerHTML =
                `<i class="fas ${meta.icon} me-2 ${meta.color}"></i> Filter — ${meta.label}`;

            const fields = meta.filters.map(f => filterBuilders[f] ? filterBuilders[f]() : '').join('');
            document.getElementById('filterFields').innerHTML = fields;

            document.getElementById('filterPanel').style.display = 'block';
            document.getElementById('previewArea').style.display = 'block';

            document.getElementById('filterPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // ── Collect filter values ──
        function getFilters() {
            return {
                report: activeReport,
                bulan: document.getElementById('f_bulan')?.value || '',
                tahun: document.getElementById('f_tahun')?.value || '',
                group: document.getElementById('f_group')?.value || '',
                user_id: document.getElementById('f_user_id')?.value || '',
                status_cuti: document.getElementById('f_status_cuti')?.value || '',
                tipe_shipment: document.getElementById('f_tipe_shipment')?.value || '',
                tipe_keuangan: document.getElementById('f_tipe_keuangan')?.value || '',
                customer_id: document.getElementById('f_customer_id')?.value || '',
                status_invoice: document.getElementById('f_status_invoice')?.value || '',
            };
        }

        // ── Load preview ──
        function loadPreview() {
            if (!activeReport) return;

            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = 'flex';

            $.ajax({
                url: '<?= base_url('report_center/preview') ?>',
                method: 'POST',
                data: getFilters(),
                dataType: 'json',           // FIX: biarkan jQuery yang parse JSON — tidak perlu JSON.parse manual
                success(d) {
                    overlay.style.display = 'none';
                    if (!d.success) {
                        Swal.fire({ icon: 'error', title: 'Error', text: d.message || 'Gagal memuat data' });
                        return;
                    }
                    renderPreview(d);
                },
                error(xhr) {
                    overlay.style.display = 'none';
                    // Tampilkan response asli di console buat debug
                    console.error('Server response:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Error',
                        text: 'Gagal menghubungi server. Cek console untuk detail.'
                    });
                }
            });
        }

        // ── Render preview ──
        function renderPreview(d) {
            document.getElementById('previewTitle').innerHTML =
                `<i class="fas fa-table me-1"></i> Preview — ${d.label}`;
            document.getElementById('previewInfo').textContent = `${d.total} baris data`;
            document.getElementById('previewTimestamp').textContent = 'Diambil: ' + new Date().toLocaleString('id-ID');

            // Stats
            let statsHtml = '';
            if (d.stats) {
                d.stats.forEach(s => {
                    statsHtml += `<span class="stat-pill bg-${s.color}-lt text-${s.color}">
                <i class="fas ${s.icon}"></i> ${s.label}: <strong>${s.value}</strong>
            </span>`;
                });
            }
            document.getElementById('previewStats').innerHTML = statsHtml;

            // Head
            document.getElementById('previewThead').innerHTML =
                '<tr>' + d.columns.map(c => `<th>${escHtml(c)}</th>`).join('') + '</tr>';

            // Body
            if (!d.rows || !d.rows.length) {
                document.getElementById('previewTbody').innerHTML =
                    `<tr><td colspan="${d.columns.length}" class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i><em>Tidak ada data</em>
             </td></tr>`;
                return;
            }

            const rows = d.rows.map(r => {
                const cells = d.keys.map(k => {
                    const val = r[k] ?? '—';
                    if (k === 'status') return `<td>${statusBadge(val)}</td>`;
                    if (k === 'persen' || k === 'persen_kehadiran') return `<td>${percentBar(val)}</td>`;
                    return `<td>${escHtml(String(val))}</td>`;
                }).join('');
                return `<tr>${cells}</tr>`;
            }).join('');

            document.getElementById('previewTbody').innerHTML = rows;
        }

        function statusBadge(val) {
            const map = {
                'Pending': 'warning text-dark',
                'Disetujui': 'success',
                'Ditolak': 'danger',
                'paid': 'success',
                'unpaid': 'warning text-dark',
                'overdue': 'danger',
            };
            const cls = map[val] || 'secondary';
            return `<span class="badge bg-${cls}">${escHtml(String(val))}</span>`;
        }

        function percentBar(val) {
            const pct = parseFloat(val) || 0;
            const cls = pct >= 90 ? 'success' : pct >= 75 ? 'warning' : 'danger';
            return `<div class="d-flex align-items-center gap-2">
        <div class="progress flex-grow-1" style="height:6px;min-width:60px;">
            <div class="progress-bar bg-${cls}" style="width:${pct}%"></div>
        </div>
        <span class="small">${pct}%</span>
    </div>`;
        }

        // ── Export ──
        function exportExcel() {
            if (!activeReport) { Swal.fire({ icon: 'warning', text: 'Pilih laporan dulu!' }); return; }
            const params = new URLSearchParams({ ...getFilters(), format: 'excel' });
            window.location.href = '<?= base_url('report_center/export') ?>?' + params.toString();
        }

        function exportPdf() {
            if (!activeReport) { Swal.fire({ icon: 'warning', text: 'Pilih laporan dulu!' }); return; }
            const params = new URLSearchParams({ ...getFilters(), format: 'pdf' });
            window.open('<?= base_url('report_center/export') ?>?' + params.toString(), '_blank');
        }

        // ── Reset ──
        function resetFilter() {
            document.querySelectorAll('.report-card').forEach(c => c.classList.remove('selected'));
            activeReport = null;
            document.getElementById('filterPanel').style.display = 'none';
            document.getElementById('previewArea').style.display = 'none';
            document.getElementById('filterFields').innerHTML = '';
        }

        function escHtml(t) {
            const d = document.createElement('div');
            d.textContent = t;
            return d.innerHTML;
        }

        setTimeout(() => document.querySelectorAll('.alert').forEach(a => $(a).fadeOut('slow')), 5000);
    </script>
</body>

</html>
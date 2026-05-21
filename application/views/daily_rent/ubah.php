<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        .no-rent-display {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: var(--tblr-bg-surface-secondary, #f8fafc);
            border: 1px solid var(--tblr-border-color, #e6ebf1);
            border-radius: 6px;
            font-family: monospace;
            font-weight: 700;
            font-size: 1rem;
            color: var(--tblr-primary, #206bc4);
        }
        .duration-hint {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 6px;
            padding: 4px 10px;
            background: rgba(32,107,196,.07);
            color: var(--tblr-primary, #206bc4);
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .duration-hint.error {
            background: rgba(214,57,57,.07);
            color: var(--tblr-danger, #d63939);
        }
    </style>
</head>
<body class="antialiased">
<div class="wrapper">
    <?php $this->load->view('partials/navbar') ?>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">

                <!-- PAGE HEADER -->
                <div class="page-header d-print-none mb-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="page-pretitle">Daily Rent</div>
                            <h2 class="page-title"><?= $title ?></h2>
                        </div>
                        <div class="col-auto ms-auto d-print-none">
                            <a href="<?= base_url('daily_rent/detail/' . $rent->id) ?>"
                                class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Detail
                            </a>
                        </div>
                    </div>
                </div>

                <!-- FLASH ERROR -->
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= $this->session->flashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif ?>

                <!-- INFO NOTICE -->
                <div class="alert alert-warning mb-3">
                    <div class="d-flex gap-2">
                        <i class="fas fa-exclamation-triangle mt-1 flex-shrink-0"></i>
                        <div class="small">
                            <strong>Catatan:</strong>
                            Perubahan periode sewa di sini <strong>tidak otomatis</strong> mengupdate periode unit.
                            Update periode per unit dilakukan di halaman detail.
                        </div>
                    </div>
                </div>

                <form action="<?= base_url('daily_rent/proses_ubah/' . $rent->id) ?>" method="post" id="formUbah">
                <div class="row">

                    <!-- ══ KIRI: INFO ORDER ══ -->
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-alt me-2 text-blue"></i>
                                    Informasi Order
                                </h3>
                                <div class="card-options">
                                    <span class="badge bg-success-lt">
                                        <i class="fas fa-edit me-1"></i>Mode Edit
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">

                                <!-- No Rent -->
                                <div class="mb-3">
                                    <label class="form-label">No Rent</label>
                                    <div class="no-rent-display">
                                        <i class="fas fa-hashtag text-muted" style="font-size:.85rem;"></i>
                                        <?= htmlspecialchars($rent->no_rent) ?>
                                        <span class="badge bg-secondary-lt ms-auto">Tidak dapat diubah</span>
                                    </div>
                                    <input type="hidden" name="no_rent" value="<?= htmlspecialchars($rent->no_rent) ?>">
                                </div>

                                <!-- Customer -->
                                <div class="mb-3">
                                    <label class="form-label required">Customer</label>
                                    <select name="customer_id" class="form-select" required>
                                        <option value="">-- Pilih Customer --</option>
                                        <?php foreach ($customers ?? [] as $c): ?>
                                            <option value="<?= $c->id ?>"
                                                <?= $c->id == $rent->customer_id ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c->nama) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <!-- PIC -->
                                <div class="row g-2 mb-3">
                                    <div class="col-7">
                                        <label class="form-label">PIC Customer</label>
                                        <input type="text" name="pic_customer" class="form-control"
                                            value="<?= htmlspecialchars($rent->pic_customer ?? '') ?>"
                                            placeholder="Nama PIC di sisi customer">
                                    </div>
                                    <div class="col-5">
                                        <label class="form-label">No HP PIC</label>
                                        <input type="text" name="pic_customer_phone" class="form-control"
                                            value="<?= htmlspecialchars($rent->pic_customer_phone ?? '') ?>"
                                            placeholder="08xxxxxxxxxx">
                                    </div>
                                </div>

                                <!-- Lokasi -->
                                <div class="mb-3">
                                    <label class="form-label">Lokasi Operasional</label>
                                    <input type="text" name="location" class="form-control"
                                        value="<?= htmlspecialchars($rent->location ?? '') ?>"
                                        placeholder="Contoh: Area Cikarang, Pabrik Legok, ...">
                                </div>

                                <!-- Notes -->
                                <div class="mb-0">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3"
                                        placeholder="Catatan / instruksi khusus..."><?= htmlspecialchars($rent->notes ?? '') ?></textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- ══ KANAN: PERIODE ══ -->
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calendar-alt me-2 text-yellow"></i>
                                    Periode Sewa
                                </h3>
                            </div>
                            <div class="card-body">

                                <!-- Vendor Default -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        Vendor Default
                                        <span class="badge bg-secondary-lt ms-1">Opsional</span>
                                    </label>
                                    <select name="vendor_id" class="form-select">
                                        <option value="">-- Tanpa Vendor Default --</option>
                                        <?php foreach ($vendors ?? [] as $v): ?>
                                            <option value="<?= $v->id ?>"
                                                <?= $v->id == $rent->vendor_id ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($v->nama_vendor) ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select>
                                </div>

                                <div class="hr-text hr-text-left mb-3">
                                    <span>Durasi Sewa</span>
                                </div>

                                <!-- Tanggal Mulai -->
                                <div class="mb-3">
                                    <label class="form-label required">Tanggal Mulai</label>
                                    <div class="row g-2">
                                        <div class="col-8">
                                            <input type="date" name="rent_start_date" id="rentStartDate"
                                                class="form-control"
                                                value="<?= $rent->rent_start_date ?? '' ?>"
                                                required>
                                        </div>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                                <input type="time" name="rent_start_time" class="form-control"
                                                    value="<?= $rent->rent_start_time ? substr($rent->rent_start_time, 0, 5) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tanggal Selesai -->
                                <div class="mb-1">
                                    <label class="form-label required">Tanggal Selesai</label>
                                    <div class="row g-2">
                                        <div class="col-8">
                                            <input type="date" name="rent_end_date" id="rentEndDate"
                                                class="form-control"
                                                value="<?= $rent->rent_end_date ?? '' ?>"
                                                required>
                                        </div>
                                        <div class="col-4">
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                                <input type="time" name="rent_end_time" class="form-control"
                                                    value="<?= $rent->rent_end_time ? substr($rent->rent_end_time, 0, 5) : '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Duration preview -->
                                <div id="durationPreview" style="display:none;">
                                    <div class="duration-hint" id="durationBadge">
                                        <i class="fas fa-ruler-horizontal"></i>
                                        <span id="durationText"></span>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- STATUS CARD (info only) -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle me-2 text-cyan"></i>
                                    Info Order Saat Ini
                                </h3>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-muted small mb-1">Status</div>
                                        <?php
                                            $sc = ['Sourcing Vendor'=>'secondary','Scheduled'=>'secondary',
                                                   'Active'=>'primary','Partially Returned'=>'warning',
                                                   'Completed'=>'success','Cancelled'=>'danger'];
                                            $col = $sc[$rent->status_rent ?? ''] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $col ?>">
                                            <?= htmlspecialchars($rent->status_rent ?? '-') ?>
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small mb-1">No Rent</div>
                                        <code style="font-size:.9rem;"><?= htmlspecialchars($rent->no_rent) ?></code>
                                    </div>
                                    <?php if (!empty($rent->created_at)): ?>
                                    <div class="col-12">
                                        <div class="text-muted small mb-1">Dibuat</div>
                                        <div class="small"><?= date('d M Y H:i', strtotime($rent->created_at)) ?></div>
                                    </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- SUBMIT BAR -->
                <div class="card mb-4">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Simpan perubahan header order. Untuk mengubah unit, buka halaman detail.
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= base_url('daily_rent/detail/' . $rent->id) ?>"
                                class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>

                </form>
            </div>
        </div>
        <?php $this->load->view('partials/footer') ?>
    </div>
</div>

<?php $this->load->view('partials/js') ?>
<script>
$(document).ready(function () {

    function updateDuration() {
        const s = $('#rentStartDate').val();
        const e = $('#rentEndDate').val();
        if (!s || !e) { $('#durationPreview').hide(); return; }
        const diff = Math.round((new Date(e) - new Date(s)) / 86400000);
        $('#durationPreview').show();
        const $b = $('#durationBadge');
        if (diff < 0) {
            $b.addClass('error');
            $('#durationText').text('Tanggal selesai sebelum tanggal mulai!');
        } else {
            $b.removeClass('error');
            const fmt = d => new Date(d).toLocaleDateString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric'
            });
            $('#durationText').html('<strong>' + diff + ' hari</strong> &nbsp;·&nbsp; ' + fmt(s) + ' – ' + fmt(e));
        }
    }

    // Trigger langsung saat load kalau sudah ada value
    updateDuration();
    $('#rentStartDate, #rentEndDate').on('change', updateDuration);

    $('#formUbah').on('submit', function (e) {
        const s  = $('[name=rent_start_date]').val();
        const en = $('[name=rent_end_date]').val();
        if (s && en && en < s) {
            e.preventDefault();
            alert('Tanggal selesai tidak boleh sebelum tanggal mulai!');
            $('[name=rent_end_date]').focus();
            return false;
        }
    });
});
</script>
</body>
</html>
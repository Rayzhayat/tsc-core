<?php
// View ini di-load via AJAX fetch, jadi ga perlu full HTML
$badge = ['baru' => 'danger', 'diproses' => 'warning', 'selesai' => 'success'];
$label = ['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];
?>
<div class="row g-3">

    <!-- Info Driver -->
    <div class="col-md-6">
        <div class="card border h-100">
            <div class="card-header py-2 bg-light">
                <h6 class="mb-0 fw-bold"><i class="fas fa-id-card text-primary me-1"></i> Data Driver</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th width="40%">Nama</th>
                        <td><?= htmlspecialchars($keluhan->nama_driver) ?></td>
                    </tr>
                    <tr>
                        <th>No Polisi</th>
                        <td>

                        </td>
                    </tr>
                    <tr>
                        <th>Vendor</th>
                        <td><?= htmlspecialchars($keluhan->vendor ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>No LT</th>
                        <td><?= htmlspecialchars($keluhan->no_lt ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Origin</th>
                        <td><?= htmlspecialchars($keluhan->origin ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Destinasi</th>
                        <td><?= htmlspecialchars($keluhan->destinasi ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Waktu Lapor</th>
                        <td><?= date('d M Y, H:i', strtotime($keluhan->created_at)) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Status & Aksi -->
    <div class="col-md-6">
        <div class="card border h-100">
            <div class="card-header py-2 bg-light">
                <h6 class="mb-0 fw-bold"><i class="fas fa-cog text-primary me-1"></i> Update Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    Status saat ini: <span
                        class="badge bg-<?= $badge[$keluhan->status] ?> ms-1"><?= $label[$keluhan->status] ?></span>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label fw-semibold small">Ubah Status</label>
                    <select id="selectStatus" class="form-select form-select-sm">
                        <?php foreach (['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= $keluhan->status === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Catatan Admin</label>
                    <textarea id="catatanAdmin" class="form-control form-control-sm" rows="3"
                        placeholder="Tindakan yang sudah diambil..."><?= htmlspecialchars($keluhan->catatan_admin ?? '') ?></textarea>
                </div>
                <button class="btn btn-primary btn-sm w-100" onclick="simpanStatus(<?= $keluhan->id ?>)">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    <!-- Keluhan -->
    <div class="col-12">
        <div class="card border">
            <div class="card-header py-2 bg-light">
                <h6 class="mb-0 fw-bold"><i class="fas fa-comment-dots text-primary me-1"></i> Isi Keluhan</h6>
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space:pre-line"><?= htmlspecialchars($keluhan->keluhan) ?></p>
            </div>
        </div>
    </div>

    <!-- Foto -->
    <?php if ($keluhan->foto): ?>
        <div class="col-12">
            <div class="card border">
                <div class="card-header py-2 bg-light">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-image text-primary me-1"></i> Foto Bukti</h6>
                </div>
                <div class="card-body text-center">
                    <?php if (pathinfo($keluhan->foto, PATHINFO_EXTENSION) === 'pdf'): ?>
                        <a href="<?= base_url($keluhan->foto) ?>" target="_blank" class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i> Buka PDF
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url($keluhan->foto) ?>" target="_blank">
                            <img src="<?= base_url($keluhan->foto) ?>" class="img-fluid rounded" style="max-height:300px">
                        </a>
                    <?php endif ?>
                </div>
            </div>
        </div>
    <?php endif ?>

</div>

<script>
    function simpanStatus(id) {
        const status = document.getElementById('selectStatus').value;
        const catatan = document.getElementById('catatanAdmin').value;

        fetch('<?= base_url('driver_keluhan/update_status/') ?>' + id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `status=${encodeURIComponent(status)}&catatan_admin=${encodeURIComponent(catatan)}&<?= $this->security->get_csrf_token_name() ?>=<?= $this->security->get_csrf_hash() ?>`
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Tersimpan!', text: 'Status berhasil diupdate.', timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan.' });
                }
            });
    }
</script>
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="page-wrapper">
  <div class="container-xl py-4">

    <div class="page-header mb-4">
      <div class="row align-items-center">
        <div class="col-auto">
          <a href="<?= base_url('ticket') ?>" class="btn btn-ghost-secondary btn-sm">
            ← Kembali
          </a>
        </div>
        <div class="col">
          <h2 class="page-title mb-0">🎫 Buat Ticket Support Baru</h2>
        </div>
      </div>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-8">

        <?php if (validation_errors()): ?>
        <div class="alert alert-danger alert-dismissible mb-3">
          <h4 class="alert-title">Ada yang kurang nih!</h4>
          <div><?= validation_errors('<p class="mb-0">', '</p>') ?></div>
          <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Informasi Ticket</h3>
            <div class="card-subtitle text-muted">
              Jelaskan masalah yang kamu alami dengan detail agar tim IT bisa segera menangani.
            </div>
          </div>
          <div class="card-body">
            <?= form_open_multipart('ticket/buat', ['id' => 'formTicket']) ?>

              <!-- Judul -->
              <div class="mb-3">
                <label class="form-label required">Judul Masalah</label>
                <input type="text" name="judul" class="form-control <?= form_error('judul') ? 'is-invalid' : '' ?>"
                  value="<?= set_value('judul') ?>"
                  placeholder="Contoh: Tidak bisa login ke sistem TSC"
                  maxlength="255" required>
                <?php if (form_error('judul')): ?>
                <div class="invalid-feedback"><?= form_error('judul') ?></div>
                <?php endif; ?>
              </div>

              <!-- Deskripsi -->
              <div class="mb-3">
                <label class="form-label required">Deskripsi Lengkap</label>
                <textarea name="deskripsi" rows="5"
                  class="form-control <?= form_error('deskripsi') ? 'is-invalid' : '' ?>"
                  placeholder="Jelaskan:&#10;- Apa yang terjadi?&#10;- Kapan mulai terjadi?&#10;- Langkah apa yang sudah dicoba?&#10;- Pesan error (jika ada)"
                  required><?= set_value('deskripsi') ?></textarea>
                <?php if (form_error('deskripsi')): ?>
                <div class="invalid-feedback"><?= form_error('deskripsi') ?></div>
                <?php endif; ?>
              </div>

              <div class="row">
                <!-- Kategori -->
                <div class="col-md-6 mb-3">
                  <label class="form-label required">Kategori</label>
                  <select name="kategori" class="form-select <?= form_error('kategori') ? 'is-invalid' : '' ?>" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php
                    $kategori_opts = [
                        'bug'       => '🐛 Bug / Error Sistem',
                        'akses'     => '🔑 Hak Akses / Login',
                        'hardware'  => '🖥️ Hardware / Perangkat',
                        'software'  => '💿 Software / Aplikasi',
                        'jaringan'  => '🌐 Jaringan / Internet',
                        'lainnya'   => '📋 Lainnya',
                    ];
                    foreach ($kategori_opts as $val => $label):
                    ?>
                    <option value="<?= $val ?>" <?= set_select('kategori', $val) ?>>
                      <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                  </select>
                  <?php if (form_error('kategori')): ?>
                  <div class="invalid-feedback"><?= form_error('kategori') ?></div>
                  <?php endif; ?>
                </div>

                <!-- Priority -->
                <div class="col-md-6 mb-3">
                  <label class="form-label required">
                    Priority
                    <span class="ms-1 text-muted form-hint" style="font-size:0.75rem">
                      — seberapa mendesak?
                    </span>
                  </label>
                  <select name="priority" class="form-select <?= form_error('priority') ? 'is-invalid' : '' ?>" required>
                    <option value="">-- Pilih Priority --</option>
                    <option value="low"    <?= set_select('priority', 'low') ?>>🟢 Low — Tidak mendesak</option>
                    <option value="medium" <?= set_select('priority', 'medium', true) ?>>🟡 Medium — Perlu segera</option>
                    <option value="high"   <?= set_select('priority', 'high') ?>>🟠 High — Mengganggu kerja</option>
                    <option value="urgent" <?= set_select('priority', 'urgent') ?>>🔴 Urgent — Sistem down / darurat</option>
                  </select>
                  <?php if (form_error('priority')): ?>
                  <div class="invalid-feedback"><?= form_error('priority') ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Attachment (opsional) -->
              <div class="mb-4">
                <label class="form-label">
                  Lampiran
                  <span class="text-muted ms-1" style="font-size:0.78rem">(opsional — screenshot, log, dll)</span>
                </label>
                <input type="file" name="attachment" class="form-control"
                  accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xlsx,.txt">
                <div class="form-hint">Maks 5MB. Format: JPG, PNG, PDF, DOC, XLSX, TXT</div>
              </div>

              <!-- Priority guide -->
              <div class="alert alert-info mb-4">
                <div class="d-flex gap-3 flex-wrap">
                  <div><span class="badge bg-success me-1">Low</span>Pertanyaan umum, tidak urgent</div>
                  <div><span class="badge bg-warning me-1">Medium</span>Perlu diselesaikan hari ini</div>
                  <div><span class="badge bg-danger me-1">High</span>Menghambat pekerjaan</div>
                  <div><span class="badge bg-purple me-1">Urgent</span>Sistem tidak bisa digunakan sama sekali</div>
                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="btnSubmit">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="10" y1="14" x2="21" y2="3"/><path d="M21 3l-6.5 18a0.55 0.55 0 0 1 -1 0l-3.5 -7l-7 -3.5a0.55 0.55 0 0 1 0 -1l18 -6.5"/></svg>
                  Kirim Ticket
                </button>
                <a href="<?= base_url('ticket') ?>" class="btn btn-ghost-secondary">Batal</a>
              </div>

            <?= form_close() ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('formTicket').addEventListener('submit', function() {
    var btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
});
</script>
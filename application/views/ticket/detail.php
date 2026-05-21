<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
function priority_badge($p) {
    $map = ['low'=>['bg-success','Low'],'medium'=>['bg-warning','Medium'],'high'=>['bg-danger','High'],'urgent'=>['bg-purple','Urgent']];
    $m = $map[$p] ?? ['bg-secondary',ucfirst($p)];
    return "<span class='badge {$m[0]}'>{$m[1]}</span>";
}
function status_badge($s) {
    $map = ['open'=>['bg-azure','Open'],'in_progress'=>['bg-yellow','In Progress'],'resolved'=>['bg-green','Resolved'],'closed'=>['bg-secondary','Closed']];
    $m = $map[$s] ?? ['bg-secondary',ucfirst($s)];
    return "<span class='badge {$m[0]}'>{$m[1]}</span>";
}
?>

<div class="page-wrapper">
  <div class="container-xl py-4">

    <!-- Header -->
    <div class="page-header mb-4">
      <div class="row align-items-center">
        <div class="col-auto">
          <a href="<?= base_url('ticket') ?>" class="btn btn-ghost-secondary btn-sm">← Kembali</a>
        </div>
        <div class="col">
          <h2 class="page-title mb-0">
            🎫 <?= htmlspecialchars($ticket->judul, ENT_QUOTES) ?>
          </h2>
          <div class="text-muted mt-1">
            <code><?= $ticket->kode ?></code>
            · Dibuat <?= date('d M Y H:i', strtotime($ticket->created_at)) ?>
          </div>
        </div>
        <div class="col-auto">
          <?= priority_badge($ticket->priority) ?>
          <?= status_badge($ticket->status) ?>
        </div>
      </div>
    </div>

    <div class="row g-4">

      <!-- Kiri: detail ticket -->
      <div class="col-lg-8">

        <!-- Info ticket -->
        <div class="card mb-4">
          <div class="card-header"><h3 class="card-title">Detail Laporan</h3></div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Kategori</div>
              <div class="col-sm-8"><?= ucfirst($ticket->kategori) ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Priority</div>
              <div class="col-sm-8"><?= priority_badge($ticket->priority) ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Status</div>
              <div class="col-sm-8"><?= status_badge($ticket->status) ?></div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Diajukan oleh</div>
              <div class="col-sm-8"><?= htmlspecialchars($ticket->submitter_nama ?? '-', ENT_QUOTES) ?></div>
            </div>
            <?php if ($ticket->handler_nama): ?>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Ditangani</div>
              <div class="col-sm-8"><?= htmlspecialchars($ticket->handler_nama, ENT_QUOTES) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($ticket->resolved_at): ?>
            <div class="row mb-3">
              <div class="col-sm-4 text-muted">Selesai pada</div>
              <div class="col-sm-8"><?= date('d M Y H:i', strtotime($ticket->resolved_at)) ?></div>
            </div>
            <?php endif; ?>

            <hr>

            <div class="mb-0">
              <div class="text-muted mb-2">Deskripsi Masalah</div>
              <div class="bg-light rounded p-3" style="white-space:pre-line;font-size:0.9rem"><?= htmlspecialchars($ticket->deskripsi, ENT_QUOTES) ?></div>
            </div>

            <?php if ($ticket->catatan_admin): ?>
            <div class="mt-3">
              <div class="text-muted mb-2">Catatan Admin</div>
              <div class="bg-light rounded p-3 border-start border-primary border-3" style="white-space:pre-line;font-size:0.9rem"><?= htmlspecialchars($ticket->catatan_admin, ENT_QUOTES) ?></div>
            </div>
            <?php endif; ?>

            <?php if ($ticket->attachment): ?>
            <div class="mt-3">
              <div class="text-muted mb-2">Lampiran</div>
              <?php
              $ext = strtolower(pathinfo($ticket->attachment, PATHINFO_EXTENSION));
              $img_exts = ['jpg','jpeg','png','gif'];
              $file_url = base_url('uploads/tickets/' . $ticket->attachment);
              ?>
              <?php if (in_array($ext, $img_exts)): ?>
              <a href="<?= $file_url ?>" target="_blank">
                <img src="<?= $file_url ?>" class="rounded border" style="max-width:100%;max-height:300px;object-fit:contain">
              </a>
              <?php else: ?>
              <a href="<?= $file_url ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/></svg>
                <?= $ticket->attachment ?>
              </a>
              <?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- History / Timeline -->
        <div class="card">
          <div class="card-header"><h3 class="card-title">Riwayat Ticket</h3></div>
          <div class="card-body">
            <?php if (empty($logs)): ?>
            <div class="text-muted text-center py-3">Belum ada riwayat.</div>
            <?php else: ?>
            <ul class="timeline">
              <?php foreach ($logs as $log): ?>
              <li class="timeline-event">
                <div class="timeline-event-icon bg-primary-lt">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm text-primary" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
                </div>
                <div class="card timeline-event-card">
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                      <strong class="text-sm"><?= htmlspecialchars($log->action, ENT_QUOTES) ?></strong>
                      <span class="text-muted" style="font-size:0.78rem;white-space:nowrap;margin-left:12px">
                        <?= date('d M Y H:i', strtotime($log->created_at)) ?>
                      </span>
                    </div>
                    <?php if ($log->old_status && $log->new_status && $log->old_status !== $log->new_status): ?>
                    <div class="mt-1 text-muted" style="font-size:0.8rem">
                      <span class="badge bg-secondary"><?= $log->old_status ?></span>
                      → <span class="badge bg-primary"><?= $log->new_status ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($log->catatan): ?>
                    <div class="mt-1 text-muted" style="font-size:0.82rem;white-space:pre-line"><?= htmlspecialchars($log->catatan, ENT_QUOTES) ?></div>
                    <?php endif; ?>
                    <div class="mt-1 text-muted" style="font-size:0.75rem">
                      oleh <strong><?= htmlspecialchars($log->by_nama ?? '-', ENT_QUOTES) ?></strong>
                    </div>
                  </div>
                </div>
              </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
          </div>
        </div>

      </div><!-- /col-lg-8 -->

      <!-- Kanan: Update status (ADMIN only) -->
      <?php if ($is_admin && $ticket->status !== 'closed'): ?>
      <div class="col-lg-4">
        <div class="card sticky-top" style="top:80px">
          <div class="card-header"><h3 class="card-title">⚙️ Update Status</h3></div>
          <div class="card-body">
            <div id="updateMsg" class="mb-3" style="display:none"></div>

            <div class="mb-3">
              <label class="form-label">Status Baru</label>
              <select id="selectStatus" class="form-select">
                <option value="">-- Pilih Status --</option>
                <option value="open"        <?= $ticket->status === 'open'        ? 'selected' : '' ?>>Open</option>
                <option value="in_progress" <?= $ticket->status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="resolved"    <?= $ticket->status === 'resolved'    ? 'selected' : '' ?>>Resolved</option>
                <option value="closed">Closed (Tutup Ticket)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Catatan Admin <span class="text-muted">(opsional)</span></label>
              <textarea id="catatanAdmin" rows="4" class="form-control"
                placeholder="Jelaskan langkah penanganan, solusi, atau informasi tambahan..."><?= htmlspecialchars($ticket->catatan_admin ?? '', ENT_QUOTES) ?></textarea>
            </div>

            <button id="btnUpdate" class="btn btn-primary w-100">
              Simpan Update
            </button>
          </div>
        </div>
      </div>
      <?php elseif ($is_admin && $ticket->status === 'closed'): ?>
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body text-center text-muted py-4">
            <div style="font-size:2rem">🔒</div>
            <div class="mt-2">Ticket ini sudah ditutup.</div>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /row -->
  </div>
</div>

<?php if ($is_admin && $ticket->status !== 'closed'): ?>
<script>
document.getElementById('btnUpdate').addEventListener('click', function() {
    var status  = document.getElementById('selectStatus').value;
    var catatan = document.getElementById('catatanAdmin').value;
    var msgEl   = document.getElementById('updateMsg');
    var btn     = this;

    if (!status) {
        msgEl.innerHTML = '<div class="alert alert-warning py-2 mb-0">Pilih status terlebih dahulu.</div>';
        msgEl.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    msgEl.style.display = 'none';

    fetch('<?= base_url("ticket/update_status/" . $ticket->id) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'status=' + encodeURIComponent(status) + '&catatan_admin=' + encodeURIComponent(catatan)
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            msgEl.innerHTML = '<div class="alert alert-success py-2 mb-0">✅ ' + res.message + ' — Halaman akan refresh...</div>';
            msgEl.style.display = 'block';
            setTimeout(() => location.reload(), 1500);
        } else {
            msgEl.innerHTML = '<div class="alert alert-danger py-2 mb-0">❌ ' + res.message + '</div>';
            msgEl.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = 'Simpan Update';
        }
    })
    .catch(() => {
        msgEl.innerHTML = '<div class="alert alert-danger py-2 mb-0">❌ Gagal menghubungi server.</div>';
        msgEl.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = 'Simpan Update';
    });
});
</script>
<?php endif; ?>
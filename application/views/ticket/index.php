<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
// ── Helper functions ──────────────────────────────────────────────
function ticket_priority_badge($p) {
    $map = [
        'low'    => ['bg-success', 'Low'],
        'medium' => ['bg-warning', 'Medium'],
        'high'   => ['bg-danger',  'High'],
        'urgent' => ['bg-purple',  'Urgent'],
    ];
    $m = $map[$p] ?? ['bg-secondary', ucfirst($p)];
    return "<span class='badge {$m[0]}'>{$m[1]}</span>";
}

function ticket_status_badge($s) {
    $map = [
        'open'        => ['bg-azure',   'Open'],
        'in_progress' => ['bg-yellow',  'In Progress'],
        'resolved'    => ['bg-green',   'Resolved'],
        'closed'      => ['bg-secondary','Closed'],
    ];
    $m = $map[$s] ?? ['bg-secondary', ucfirst($s)];
    return "<span class='badge {$m[0]}'>{$m[1]}</span>";
}
?>

<div class="page-wrapper">
  <div class="container-xl py-4">

    <!-- Header -->
    <div class="page-header d-print-none mb-3">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">🎫 Support Ticket</h2>
          <div class="text-muted mt-1">
            <?= $is_admin ? 'Kelola semua tiket internal TSC' : 'Pantau tiket support yang kamu ajukan' ?>
          </div>
        </div>
        <div class="col-auto ms-auto">
          <a href="<?= base_url('ticket/buat') ?>" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Ticket Baru
          </a>
        </div>
      </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible" role="alert">
      <div><?= $this->session->flashdata('success') ?></div>
      <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
      <div><?= $this->session->flashdata('error') ?></div>
      <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
      <?php
      $cards = [
          'open'        => ['label' => 'Open',        'icon' => 'ticket',       'color' => 'azure'],
          'in_progress' => ['label' => 'In Progress',  'icon' => 'loader',       'color' => 'yellow'],
          'resolved'    => ['label' => 'Resolved',     'icon' => 'check-circle', 'color' => 'green'],
          'closed'      => ['label' => 'Closed',       'icon' => 'lock',         'color' => 'secondary'],
      ];
      foreach ($cards as $key => $card): ?>
      <div class="col-6 col-md-3">
        <div class="card card-sm">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-auto">
                <span class="bg-<?= $card['color'] ?> text-white avatar">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                    <?php if ($card['icon'] === 'ticket'): ?><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="15" y1="5" x2="15" y2="7"/><line x1="15" y1="11" x2="15" y2="13"/><line x1="15" y1="17" x2="15" y2="19"/><path d="M5 5h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-3a2 2 0 0 0 0 -4v-3a2 2 0 0 1 2 -2"/>
                    <?php elseif ($card['icon'] === 'loader'): ?><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="6" x2="12" y2="3"/><line x1="16.25" y1="7.75" x2="18.4" y2="5.6"/><line x1="18" y1="12" x2="21" y2="12"/><line x1="16.25" y1="16.25" x2="18.4" y2="18.4"/><line x1="12" y1="18" x2="12" y2="21"/><line x1="7.75" y1="16.25" x2="5.6" y2="18.4"/><line x1="6" y1="12" x2="3" y2="12"/><line x1="7.75" y1="7.75" x2="5.6" y2="5.6"/>
                    <?php elseif ($card['icon'] === 'check-circle'): ?><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2l4 -4"/>
                    <?php elseif ($card['icon'] === 'lock'): ?><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="5" y="11" width="14" height="10" rx="2"/><circle cx="12" cy="16" r="1"/><path d="M8 11v-4a4 4 0 0 1 8 0v4"/>
                    <?php endif; ?>
                  </svg>
                </span>
              </div>
              <div class="col">
                <div class="font-weight-medium"><?= $summary[$key] ?? 0 ?></div>
                <div class="text-muted"><?= $card['label'] ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
      <div class="card-body py-2">
        <form method="get" action="<?= base_url('ticket') ?>" class="row g-2 align-items-center">
          <div class="col-auto">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">Semua Status</option>
              <option value="open"        <?= ($filter['status'] === 'open')        ? 'selected' : '' ?>>Open</option>
              <option value="in_progress" <?= ($filter['status'] === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
              <option value="resolved"    <?= ($filter['status'] === 'resolved')    ? 'selected' : '' ?>>Resolved</option>
              <option value="closed"      <?= ($filter['status'] === 'closed')      ? 'selected' : '' ?>>Closed</option>
            </select>
          </div>
          <div class="col-auto">
            <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">Semua Priority</option>
              <option value="urgent" <?= ($filter['priority'] === 'urgent') ? 'selected' : '' ?>>🔴 Urgent</option>
              <option value="high"   <?= ($filter['priority'] === 'high')   ? 'selected' : '' ?>>🟠 High</option>
              <option value="medium" <?= ($filter['priority'] === 'medium') ? 'selected' : '' ?>>🟡 Medium</option>
              <option value="low"    <?= ($filter['priority'] === 'low')    ? 'selected' : '' ?>>🟢 Low</option>
            </select>
          </div>
          <?php if ($filter['status'] || $filter['priority']): ?>
          <div class="col-auto">
            <a href="<?= base_url('ticket') ?>" class="btn btn-sm btn-ghost-secondary">Reset</a>
          </div>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <!-- Tabel Ticket -->
    <div class="card">
      <div class="card-header"><h3 class="card-title">Daftar Ticket</h3></div>
      <div class="table-responsive">
        <table class="table table-vcenter table-hover card-table" id="tblTicket">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Judul</th>
              <th>Kategori</th>
              <th>Priority</th>
              <th>Status</th>
              <?php if ($is_admin): ?><th>Diajukan</th><?php endif; ?>
              <th>Tgl Buat</th>
              <th class="w-1">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($tickets)): ?>
            <tr>
              <td colspan="<?= $is_admin ? 8 : 7 ?>" class="text-center text-muted py-5">
                <div class="empty">
                  <div class="empty-img">🎫</div>
                  <p class="empty-title">Belum ada ticket</p>
                  <p class="empty-subtitle">Klik "Buat Ticket Baru" untuk melaporkan masalah.</p>
                </div>
              </td>
            </tr>
            <?php else: ?>
            <?php foreach ($tickets as $t): ?>
            <tr>
              <td><code class="text-primary"><?= $t->kode ?></code></td>
              <td><?= htmlspecialchars($t->judul, ENT_QUOTES) ?></td>
              <td><span class="text-muted"><?= ucfirst($t->kategori) ?></span></td>
              <td><?= ticket_priority_badge($t->priority) ?></td>
              <td><?= ticket_status_badge($t->status) ?></td>
              <?php if ($is_admin): ?>
              <td><?= htmlspecialchars($t->submitter_nama ?? '-', ENT_QUOTES) ?></td>
              <?php endif; ?>
              <td class="text-muted"><?= date('d M Y H:i', strtotime($t->created_at)) ?></td>
              <td>
                <a href="<?= base_url('ticket/detail/' . $t->id) ?>" class="btn btn-sm btn-outline-primary">
                  Detail
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
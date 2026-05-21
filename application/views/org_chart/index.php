<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* ── Tree layout ── */
        .org-wrap {
            overflow-x: auto;
            padding: 20px 0 40px;
        }

        .org-tree {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .org-level {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: nowrap;
            margin-bottom: 0;
        }

        /* ── Node card ── */
        .org-node {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .org-card {
            background: #fff;
            border: 1.5px solid #4e73df;
            border-radius: 10px;
            padding: 10px 14px;
            min-width: 100px;
            max-width: 170px;
            text-align: center;
            cursor: default;
            transition: box-shadow .2s, transform .2s;
            position: relative;
        }

        .org-card:hover {
            box-shadow: 0 4px 18px rgba(78, 115, 223, .18);
            transform: translateY(-2px);
        }

        .org-card .jabatan {
            font-size: .78rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1.3;
            margin-bottom: 5px;
        }

        .org-card .nama {
            font-size: .71rem;
            color: #6c757d;
            border-top: 1px solid #e3e6f0;
            padding-top: 5px;
            margin-top: 2px;
        }

        .org-card .nama.empty {
            color: #bbb;
            font-style: italic;
        }

        .org-card .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
            margin: 0 auto 6px;
            display: block;
        }

        /* Top level (komisaris & direktur) */
        .node-top>.org-card {
            border-color: #1a237e;
            background: #e8eaf6;
        }

        .node-top>.org-card .jabatan {
            color: #1a237e;
        }

        /* Head */
        .node-head>.org-card {
            border-color: #1976d2;
            background: #e3f2fd;
        }

        .node-head>.org-card .jabatan {
            color: #1565c0;
        }

        /* SPV */
        .node-spv>.org-card {
            border-color: #388e3c;
            background: #e8f5e9;
        }

        .node-spv>.org-card .jabatan {
            color: #2e7d32;
        }

        /* Staff */
        .node-staff>.org-card {
            border-color: #9e9e9e;
            background: #fafafa;
        }

        /* ── Connector lines ── */
        .org-connector-v {
            width: 2px;
            height: 24px;
            background: #b0bec5;
            margin: 0 auto;
        }

        .org-connector-h-wrap {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            position: relative;
        }

        .org-connector-h-wrap::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            right: 50%;
            height: 2px;
            background: #b0bec5;
        }

        .org-branch {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .org-branch .branch-line-top {
            width: 2px;
            height: 20px;
            background: #b0bec5;
        }

        .org-siblings {
            display: flex;
            gap: 20px;
            align-items: flex-start;
            position: relative;
        }

        /* horizontal bar di atas siblings */
        .org-siblings::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: #b0bec5;
        }

        /* ── Manage button ── */
        .btn-manage-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999;
        }

        /* ── Dept label ── */
        .dept-label {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #4e73df;
            margin-bottom: 4px;
        }

        /* ── Edit chip (superadmin / head) ── */
        .edit-chip {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ffc107;
            color: #212529;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .2);
            text-decoration: none;
        }

        .edit-chip:hover {
            background: #e0a800;
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl mt-3">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="mb-0"><i class="fas fa-sitemap text-primary"></i> Struktur Organisasi</h2>
                            <small class="text-muted">PT Tata Sanjaya Cakrawala</small>
                        </div>
                        <?php if ($can_manage): ?>
                            <a href="<?= base_url('org_chart/manage') ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i> Kelola Struktur
                            </a>
                        <?php endif ?>
                    </div>

                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-1"></i> <?= $this->session->flashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif ?>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="org-wrap">
                                <?php if (empty($tree)): ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-sitemap fa-3x mb-3 d-block opacity-25"></i>
                                        <p>Belum ada data struktur organisasi.</p>
                                        <?php if ($can_manage): ?>
                                            <a href="<?= base_url('org_chart/manage') ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus me-1"></i> Mulai Isi Struktur
                                            </a>
                                        <?php endif ?>
                                    </div>
                                <?php else: ?>
                                    <div class="org-tree" id="orgTree">
                                        <?php _render_tree($tree, 0, $can_manage); ?>
                                    </div>
                                <?php endif ?>
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
        // Auto fade alert
        setTimeout(() => document.querySelectorAll('.alert').forEach(el => {
            el.classList.remove('show'); setTimeout(() => el.remove(), 500);
        }), 5000);
    </script>
</body>

</html>

<?php
// ── Recursive tree renderer ── (dipanggil dari view ini sendiri)
function _render_tree($tree, $depth = 0, $can_manage = false)
{
    $CI = &get_instance();
    foreach ($tree as $i => $node):
        $depth_class = $depth === 0 ? 'node-top' :
            ($depth === 1 ? 'node-head' :
                ($depth === 2 ? 'node-spv' : 'node-staff'));
        $has_children = !empty($node->children);
        $foto = $node->pengguna_foto ?: 'default-1.png';
        ?>
        <div class="org-node <?= $depth_class ?>">
            <?php if ($node->departemen && $depth === 2): ?>
                <div class="dept-label"><?= htmlspecialchars($node->departemen) ?></div>
            <?php endif ?>

            <div class="org-card" style="position:relative">
                <?php if ($can_manage): ?>
                    <a href="<?= base_url('org_chart/manage') ?>#node-<?= $node->id ?>" class="edit-chip" title="Edit posisi ini">
                        <i class="fas fa-pen"></i>
                    </a>
                <?php endif ?>

                <?php if ($node->pengguna_id): ?>
                    <img src="<?= base_url('uploads/profil/' . htmlspecialchars($foto)) ?>"
                        alt="<?= htmlspecialchars($node->pengguna_nama) ?>" class="avatar">
                <?php endif ?>

                <div class="jabatan"><?= htmlspecialchars($node->jabatan) ?></div>
                <div class="nama <?= $node->pengguna_id ? '' : 'empty' ?>">
                    <?= $node->pengguna_id ? htmlspecialchars($node->pengguna_nama) : '— Belum diisi —' ?>
                </div>
            </div>

            <?php if ($has_children): ?>
                <div class="org-connector-v"></div>
                <?php if (count($node->children) === 1): ?>
                    <?php _render_tree($node->children, $depth + 1, $can_manage); ?>
                <?php else: ?>
                    <div class="org-branch">
                        <div class="org-siblings">
                            <?php foreach ($node->children as $child): ?>
                                <div style="display:flex;flex-direction:column;align-items:center">
                                    <div class="branch-line-top"></div>
                                    <?php _render_tree([$child], $depth + 1, $can_manage); ?>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                <?php endif ?>
            <?php endif ?>
        </div>
        <?php
    endforeach;
}
?>
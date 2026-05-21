<!DOCTYPE html>
<html lang="id">

<head>
    <?php $this->load->view('partials/head') ?>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #0f1117;
            --surface: #191c26;
            --surface2: #21263a;
            --border: rgba(255, 255, 255, 0.07);
            --accent: #4f8ef7;
            --accent2: #7c3aed;
            --green: #22c55e;
            --orange: #f97316;
            --red: #ef4444;
            --text: #e2e8f0;
            --muted: rgba(226, 232, 240, 0.45);
            --radius: 14px;
        }

        /* ── RESET & BASE ─────────────────────────────────── */
        body {
            background: var(--bg) !important;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
        }

        .page-body {
            background: var(--bg) !important;
        }

        .wrapper {
            background: var(--bg) !important;
        }

        /* ── HEADER ───────────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 32px;
        }

        .page-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.5px;
            margin: 0;
        }

        .page-title span {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .live-dot {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--green);
            margin-left: 12px;
        }

        .live-dot::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.25);
            animation: livePulse 1.8s ease-in-out infinite;
        }

        @keyframes livePulse {

            0%,
            100% {
                box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.25);
            }

            50% {
                box-shadow: 0 0 0 7px rgba(34, 197, 94, 0.08);
            }
        }

        /* ── STAT CARDS ───────────────────────────────────── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.2s, border-color 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.14);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .stat-icon.blue {
            background: rgba(79, 142, 247, 0.15);
            color: var(--accent);
        }

        .stat-icon.green {
            background: rgba(34, 197, 94, 0.15);
            color: var(--green);
        }

        .stat-icon.orange {
            background: rgba(249, 115, 22, 0.15);
            color: var(--orange);
        }

        .stat-icon.purple {
            background: rgba(124, 58, 237, 0.15);
            color: var(--accent2);
        }

        .stat-val {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1;
        }

        .stat-lbl {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 3px;
        }

        /* ── SECTION TITLE ────────────────────────────────── */
        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── SESSION CARDS ────────────────────────────────── */
        .sessions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 16px;
        }

        .session-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            position: relative;
            transition: border-color 0.2s, transform 0.2s;
            animation: cardIn 0.4s ease both;
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .session-card:hover {
            border-color: rgba(255, 255, 255, 0.14);
            transform: translateY(-2px);
        }

        .session-card.is-current {
            border-color: rgba(79, 142, 247, 0.4);
            background: linear-gradient(135deg, rgba(79, 142, 247, 0.06), var(--surface));
        }

        /* current badge */
        .badge-current {
            position: absolute;
            top: 14px;
            right: 14px;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            background: rgba(79, 142, 247, 0.15);
            color: var(--accent);
            border: 1px solid rgba(79, 142, 247, 0.3);
            padding: 3px 10px;
            border-radius: 20px;
        }

        /* avatar + user info */
        .card-user {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
            flex-shrink: 0;
        }

        .avatar-placeholder {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .user-name {
            font-weight: 500;
            font-size: 0.92rem;
            color: var(--text);
            line-height: 1.2;
        }

        .user-level {
            font-size: 0.72rem;
            color: var(--muted);
            margin-top: 2px;
        }

        /* meta rows */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .meta-item {
            background: var(--surface2);
            border-radius: 8px;
            padding: 10px 12px;
        }

        .meta-item.full {
            grid-column: 1 / -1;
        }

        .meta-label {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .meta-value {
            font-size: 0.82rem;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-value i {
            color: var(--muted);
            font-size: 0.75rem;
        }

        /* device chip */
        .device-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .device-desktop {
            background: rgba(79, 142, 247, 0.12);
            color: var(--accent);
        }

        .device-mobile {
            background: rgba(34, 197, 94, 0.12);
            color: var(--green);
        }

        .device-tablet {
            background: rgba(249, 115, 22, 0.12);
            color: var(--orange);
        }

        .device-unknown {
            background: rgba(255, 255, 255, 0.07);
            color: var(--muted);
        }

        /* activity bar */
        .activity-bar {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 14px;
        }

        .activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-active {
            background: var(--green);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }

        .dot-idle {
            background: var(--orange);
        }

        .dot-expired {
            background: var(--red);
        }

        .activity-text {
            font-size: 0.75rem;
            color: var(--muted);
        }

        /* actions */
        .card-actions {
            display: flex;
            gap: 8px;
        }

        .btn-force-logout {
            flex: 1;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: var(--red);
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-force-logout:hover {
            background: rgba(239, 68, 68, 0.18);
            border-color: rgba(239, 68, 68, 0.45);
            color: var(--red);
            text-decoration: none;
        }

        .btn-info-sm {
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--muted);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.78rem;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-info-sm:hover {
            border-color: rgba(255, 255, 255, 0.14);
            color: var(--text);
            text-decoration: none;
        }

        /* empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--muted);
            margin-bottom: 16px;
        }

        .empty-state h5 {
            font-family: 'Syne', sans-serif;
            color: var(--text);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--muted);
            font-size: 0.85rem;
        }

        /* flash */
        .flash-bar {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .flash-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #4ade80;
        }

        .flash-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
        }

        /* toolbar */
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .toolbar-right {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-sm-dark {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 0.78rem;
            font-weight: 500;
            cursor: pointer;
            transition: border-color 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-sm-dark:hover {
            border-color: rgba(255, 255, 255, 0.18);
            color: var(--text);
            text-decoration: none;
        }

        .btn-sm-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: var(--red);
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 0.78rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-sm-danger:hover {
            background: rgba(239, 68, 68, 0.18);
            color: var(--red);
            text-decoration: none;
        }

        /* last refresh */
        #lastRefresh {
            font-size: 0.72rem;
            color: var(--muted);
        }

        /* level badge */
        .level-pill {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .level-superadmin {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }

        .level-admin {
            background: rgba(79, 142, 247, 0.15);
            color: var(--accent);
        }

        .level-finance {
            background: rgba(34, 197, 94, 0.15);
            color: var(--green);
        }

        .level-operational {
            background: rgba(249, 115, 22, 0.15);
            color: var(--orange);
        }

        .level-viewer {
            background: rgba(255, 255, 255, 0.07);
            color: var(--muted);
        }

        .level-fleet {
            background: rgba(124, 58, 237, 0.15);
            color: var(--accent2);
        }

        .level-default {
            background: rgba(255, 255, 255, 0.07);
            color: var(--muted);
        }
    </style>
</head>

<body class="antialiased">
    <div class="wrapper">
        <?php $this->load->view('partials/navbar') ?>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">

                    <?php
                    $current_session_id = $current_session_id ?? '';

                    // Helper: level pill class
                    function level_class($lvl)
                    {
                        if (strpos($lvl, 'superadmin') !== false)
                            return 'level-superadmin';
                        if (strpos($lvl, 'admin') !== false)
                            return 'level-admin';
                        if (strpos($lvl, 'finance') !== false)
                            return 'level-finance';
                        if (strpos($lvl, 'operational') !== false)
                            return 'level-operational';
                        if (strpos($lvl, 'viewer') !== false)
                            return 'level-viewer';
                        if (strpos($lvl, 'fleet') !== false)
                            return 'level-fleet';
                        return 'level-default';
                    }

                    // Helper: activity status
                    function activity_status($last_activity)
                    {
                        $diff = time() - strtotime($last_activity);
                        if ($diff < 600)
                            return ['dot-active', 'Aktif sekarang'];
                        if ($diff < 3600)
                            return ['dot-idle', 'Idle ' . floor($diff / 60) . ' menit'];
                        return ['dot-expired', 'Tidak aktif ' . floor($diff / 3600) . ' jam'];
                    }
                    ?>

                    <!-- HEADER -->
                    <div class="page-header">
                        <div>
                            <h1 class="page-title">
                                <span>Active Sessions</span>
                            </h1>
                            <p style="color:var(--muted);font-size:0.82rem;margin:4px 0 0">
                                Siapa yang sedang login — realtime, per device.
                                <span class="live-dot ms-2">Live</span>
                            </p>
                        </div>
                        <div class="toolbar-right">
                            <span id="lastRefresh"></span>
                            <a href="<?= base_url('security_monitor/cleanup') ?>" class="btn-sm-dark"
                                onclick="return confirm('Bersihkan sessions yang expired?')">
                                <i class="fas fa-broom"></i> Cleanup
                            </a>
                        </div>
                    </div>

                    <!-- FLASH -->
                    <?php if ($f = $this->session->flashdata('success')): ?>
                        <div class="flash-bar flash-success"><i class="fas fa-check-circle"></i><?= $f ?></div>
                    <?php elseif ($f = $this->session->flashdata('error')): ?>
                        <div class="flash-bar flash-error"><i class="fas fa-exclamation-circle"></i><?= $f ?></div>
                    <?php endif ?>

                    <!-- STATS -->
                    <?php
                    $stat_devices = [];
                    foreach ($stats['by_device'] as $d)
                        $stat_devices[$d->device_type] = $d->total;
                    ?>
                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                            <div>
                                <div class="stat-val"><?= $stats['total'] ?></div>
                                <div class="stat-lbl">Total Active Sessions</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green"><i class="fas fa-desktop"></i></div>
                            <div>
                                <div class="stat-val"><?= $stat_devices['desktop'] ?? 0 ?></div>
                                <div class="stat-lbl">Desktop</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon orange"><i class="fas fa-mobile-alt"></i></div>
                            <div>
                                <div class="stat-val"><?= $stat_devices['mobile'] ?? 0 ?></div>
                                <div class="stat-lbl">Mobile</div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon purple"><i class="fas fa-tablet-alt"></i></div>
                            <div>
                                <div class="stat-val"><?= $stat_devices['tablet'] ?? 0 ?></div>
                                <div class="stat-lbl">Tablet</div>
                            </div>
                        </div>
                    </div>

                    <!-- SESSION CARDS -->
                    <div class="section-title">Sessions yang Sedang Aktif</div>

                    <?php if (empty($sessions)): ?>
                        <div class="empty-state">
                            <i class="fas fa-ghost d-block"></i>
                            <h5>Tidak ada sesi aktif</h5>
                            <p>Belum ada user yang login dalam 2 jam terakhir.</p>
                        </div>
                    <?php else: ?>
                        <div class="sessions-grid" id="sessionsGrid">
                            <?php foreach ($sessions as $i => $s):
                                $isCurrent = ($s->session_id === $current_session_id);
                                [$dot_class, $dot_label] = activity_status($s->last_activity);
                                $device_chip_class = 'device-' . ($s->device_type ?: 'unknown');
                                $initials = strtoupper(substr($s->nama ?? 'U', 0, 1));
                                $lvl_class = level_class($s->user_level);
                                $lvl_label = str_replace('_', ' ', ucwords($s->user_level));
                                ?>
                                <div class="session-card <?= $isCurrent ? 'is-current' : '' ?>"
                                    style="animation-delay: <?= $i * 0.05 ?>s">

                                    <?php if ($isCurrent): ?>
                                        <div class="badge-current">Session Anda</div>
                                    <?php endif ?>

                                    <!-- User Info -->
                                    <div class="card-user">
                                        <?php
                                        $default_avatars = ['default-1.png', 'default-2.png', 'default-3.png', 'default-4.png'];
                                        if (!empty($s->foto_profil) && !in_array($s->foto_profil, $default_avatars)):
                                            ?>
                                            <img src="<?= base_url('assets/img/profil/' . $s->foto_profil) ?>" class="avatar"
                                                alt="">
                                        <?php else: ?>
                                            <div class="avatar-placeholder"><?= $initials ?></div>
                                        <?php endif ?>
                                        <div>
                                            <div class="user-name"><?= htmlspecialchars($s->nama ?? 'Unknown User') ?></div>
                                            <div class="user-level">
                                                <span class="level-pill <?= $lvl_class ?>"><?= $lvl_label ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Activity -->
                                    <div class="activity-bar">
                                        <div class="activity-dot <?= $dot_class ?>"></div>
                                        <span class="activity-text"><?= $dot_label ?></span>
                                        <span class="activity-text" style="margin-left:auto">
                                            Login: <?= date('d/m H:i', strtotime($s->login_at)) ?>
                                        </span>
                                    </div>

                                    <!-- Meta -->
                                    <div class="meta-grid">
                                        <div class="meta-item">
                                            <div class="meta-label">Device</div>
                                            <div class="meta-value">
                                                <span class="device-chip <?= $device_chip_class ?>">
                                                    <i class="fas <?= device_icon($s->device_type) ?>"></i>
                                                    <?= ucfirst($s->device_type ?: 'Unknown') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="meta-item">
                                            <div class="meta-label">Browser</div>
                                            <div class="meta-value">
                                                <i class="fab <?= browser_icon($s->browser) ?>"></i>
                                                <?= htmlspecialchars($s->browser ?: '-') ?>
                                            </div>
                                        </div>
                                        <div class="meta-item">
                                            <div class="meta-label">OS</div>
                                            <div class="meta-value">
                                                <i class="fas fa-microchip"></i>
                                                <?= htmlspecialchars($s->os ?: '-') ?>
                                            </div>
                                        </div>
                                        <div class="meta-item">
                                            <div class="meta-label">IP Address</div>
                                            <div class="meta-value">
                                                <i class="fas fa-network-wired"></i>
                                                <?= htmlspecialchars($s->ip_address) ?>
                                            </div>
                                        </div>
                                        <div class="meta-item full">
                                            <div class="meta-label">Lokasi</div>
                                            <div class="meta-value">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php
                                                $loc = array_filter([$s->city, $s->country]);
                                                echo $loc ? htmlspecialchars(implode(', ', $loc)) : 'Tidak diketahui';
                                                ?>
                                                <?php if ($s->isp): ?>
                                                    <span style="color:var(--muted);font-size:0.72rem;margin-left:4px">
                                                        · <?= htmlspecialchars($s->isp) ?>
                                                    </span>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="card-actions">
                                        <?php if (!$isCurrent): ?>
                                            <a href="<?= base_url('security_monitor/force_logout/' . $s->session_id) ?>"
                                                class="btn-force-logout"
                                                onclick="return confirm('Force logout <?= htmlspecialchars(addslashes($s->nama ?? 'user ini')) ?>?\n\nMereka akan langsung keluar dari sistem.')">
                                                <i class="fas fa-sign-out-alt"></i> Force Logout
                                            </a>
                                            <a href="<?= base_url('security_monitor/force_logout_all/' . $s->user_id) ?>"
                                                class="btn-info-sm" title="Logout semua device milik user ini"
                                                onclick="return confirm('Logout SEMUA device milik user ini?')">
                                                <i class="fas fa-layer-group"></i> All
                                            </a>
                                        <?php else: ?>
                                            <span style="font-size:0.75rem;color:var(--muted);padding:8px 0">
                                                <i class="fas fa-shield-alt me-1"></i>Ini session Anda sendiri
                                            </span>
                                        <?php endif ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>

                    <div style="height:40px"></div>

                </div>
            </div>
            <?php $this->load->view('partials/footer') ?>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        // ── AUTO REFRESH SETIAP 30 DETIK ─────────────────────────────────────────────
        let countdown = 30;

        function updateRefreshLabel() {
            const el = document.getElementById('lastRefresh');
            if (el) el.textContent = `Refresh dalam ${countdown}s`;
        }

        updateRefreshLabel();

        const timer = setInterval(() => {
            countdown--;
            updateRefreshLabel();
            if (countdown <= 0) {
                location.reload();
            }
        }, 1000);

        // Manual refresh
        document.addEventListener('keydown', e => {
            if (e.key === 'r' && !e.ctrlKey && !e.metaKey) location.reload();
        });
    </script>
</body>

</html>
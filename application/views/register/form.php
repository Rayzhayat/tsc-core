<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $this->load->view('partials/head') ?>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        :root {
            --navy: #0d1b2a;
            --navy-mid: #1a2e45;
            --blue: #1e6fbf;
            --blue-light: #3d9be9;
            --accent: #f0a500;
            --green: #1cc88a;
            --white: #ffffff;
            --white-dim: rgba(255, 255, 255, 0.07);
            --white-border: rgba(255, 255, 255, 0.12);
            --text-muted: rgba(255, 255, 255, 0.45)
        }

        html,
        body {
            min-height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--navy);
            overflow-x: hidden;
        }

        #bg-video {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            filter: brightness(0.22) saturate(0.6)
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 1;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            opacity: 0.4
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 1;
            pointer-events: none;
            animation: orbFloat 8s ease-in-out infinite
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(30, 111, 191, 0.3) 0%, transparent 70%);
            top: -100px;
            left: -100px
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(28, 200, 138, 0.18) 0%, transparent 70%);
            bottom: -50px;
            right: -50px;
            animation-delay: -4s
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(240, 165, 0, 0.15) 0%, transparent 70%);
            top: 40%;
            right: 10%;
            animation-delay: -2s
        }

        @keyframes orbFloat {

            0%,
            100% {
                transform: translate(0, 0) scale(1)
            }

            33% {
                transform: translate(20px, -30px) scale(1.05)
            }

            66% {
                transform: translate(-15px, 15px) scale(0.97)
            }
        }

        .page {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: 1fr 480px;
            min-height: 100vh;
        }

        /* ── Left panel ── */
        .left-panel {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 56px;
            opacity: 0;
            animation: fadeSlideRight 0.8s ease 0.3s forwards
        }

        .brand-mark {
            display: flex;
            align-items: center;
            gap: 14px
        }

        .brand-mark img {
            height: 42px;
            width: auto;
            filter: brightness(0) invert(1);
            opacity: 0.9
        }

        .brand-divider {
            width: 1px;
            height: 32px;
            background: var(--white-border)
        }

        .brand-label {
            font-family: 'Syne', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--text-muted)
        }

        .left-hero {
            padding-bottom: 80px
        }

        .left-hero .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--green);
            margin-bottom: 24px
        }

        .left-hero .eyebrow::before {
            content: '';
            display: block;
            width: 24px;
            height: 1px;
            background: var(--green)
        }

        .left-hero h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2.6rem, 3.5vw, 3.6rem);
            font-weight: 800;
            line-height: 1.05;
            color: var(--white);
            letter-spacing: -1px;
            margin-bottom: 20px
        }

        .left-hero h1 em {
            font-style: normal;
            color: transparent;
            -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.45)
        }

        .left-hero p {
            font-size: 1rem;
            color: var(--text-muted);
            line-height: 1.7;
            max-width: 380px;
            font-weight: 300
        }

        .steps-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px
        }

        .steps-list li {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            color: var(--text-muted);
            font-size: 0.88rem
        }

        .steps-list li .step-num {
            min-width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(28, 200, 138, 0.15);
            border: 1px solid rgba(28, 200, 138, 0.35);
            color: var(--green);
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0
        }

        /* ── Right panel ── */
        .right-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            border-left: 1px solid var(--white-border);
            background: rgba(13, 27, 42, 0.65);
            backdrop-filter: blur(24px);
            overflow-y: auto;
            opacity: 0;
            animation: fadeSlideLeft 0.8s ease 0.5s forwards
        }

        .register-card {
            width: 100%;
            padding: 8px 0 24px
        }

        .card-header-area {
            margin-bottom: 28px
        }

        .card-header-area .tag {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--green);
            border: 1px solid rgba(28, 200, 138, 0.35);
            background: rgba(28, 200, 138, 0.08);
            padding: 4px 12px;
            border-radius: 2px;
            margin-bottom: 14px
        }

        .card-header-area h2 {
            font-family: 'Syne', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1.2;
            letter-spacing: -0.5px
        }

        .card-header-area p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 8px;
            font-weight: 300
        }

        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 22px 0 14px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--white-border)
        }

        .field-group {
            margin-bottom: 14px
        }

        .field-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 7px
        }

        .field-wrap {
            position: relative
        }

        .field-wrap .field-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.82rem;
            pointer-events: none
        }

        .field-wrap input,
        .field-wrap select {
            width: 100%;
            height: 46px;
            background: var(--white-dim);
            border: 1px solid var(--white-border);
            border-radius: 8px;
            color: var(--white);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            padding: 0 14px 0 40px;
            outline: none;
            transition: border-color 0.25s, background 0.25s, box-shadow 0.25s;
            -webkit-appearance: none
        }

        .field-wrap select option,
        .field-wrap select optgroup {
            background: var(--navy-mid);
            color: var(--white)
        }

        .field-wrap input::placeholder {
            color: var(--text-muted)
        }

        .field-wrap input:focus,
        .field-wrap select:focus {
            border-color: var(--green);
            background: rgba(28, 200, 138, 0.05);
            box-shadow: 0 0 0 3px rgba(28, 200, 138, 0.12)
        }

        .select-arrow::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            font-size: 0.78rem
        }

        .toggle-pass {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 0.82rem;
            padding: 4px;
            transition: color 0.2s;
            z-index: 2
        }

        .toggle-pass:hover {
            color: var(--white)
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px
        }

        .field-hint {
            font-size: 0.7rem;
            margin-top: 4px
        }

        .req {
            color: #f87;
            margin-left: 2px
        }

        /* ── Status pills ── */
        .status-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px
        }

        .status-pill {
            position: relative
        }

        .status-pill input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0
        }

        .status-pill label {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            border: 1.5px solid var(--white-border);
            border-radius: 30px;
            font-size: 0.79rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            user-select: none
        }

        .status-pill input:checked+label {
            border-color: var(--blue-light);
            background: rgba(61, 155, 233, 0.12);
            color: var(--white)
        }

        .status-pill label:hover {
            border-color: var(--blue-light)
        }

        /* ── Group grid ── */
        .grp-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 8px
        }

        .grp-opt {
            position: relative
        }

        .grp-opt input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0
        }

        .grp-opt label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 11px;
            border: 1.5px solid var(--white-border);
            border-radius: 9px;
            font-size: 0.79rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
            height: 100%
        }

        .grp-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0
        }

        .grp-opt input:checked+label {
            border-color: var(--blue-light);
            background: rgba(61, 155, 233, 0.1);
            color: var(--white)
        }

        .grp-opt label:hover {
            border-color: var(--blue-light)
        }

        /* ── KTP upload opsional ── */
        .ktp-upload-area {
            border: 1.5px dashed var(--white-border);
            border-radius: 10px;
            padding: 18px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
            background: var(--white-dim);
            margin-top: 8px;
        }

        .ktp-upload-area:hover {
            border-color: var(--blue-light);
            background: rgba(61, 155, 233, 0.05)
        }

        .ktp-upload-area.has-file {
            border-color: var(--green);
            background: rgba(28, 200, 138, 0.05)
        }

        .ktp-upload-area input {
            display: none
        }

        .ktp-preview {
            max-width: 130px;
            max-height: 85px;
            border-radius: 8px;
            display: none;
            margin: 10px auto 0
        }

        .optional-tag {
            display: inline-block;
            font-size: 0.6rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            border: 1px solid var(--white-border);
            border-radius: 20px;
            padding: 2px 8px;
            margin-left: 8px;
            vertical-align: middle;
        }

        /* ── Password strength ── */
        .pwd-strength {
            height: 3px;
            border-radius: 2px;
            margin-top: 6px;
            transition: all 0.3s;
            background: var(--white-border)
        }

        /* ── Flash ── */
        .flash-alert {
            padding: 11px 15px;
            border-radius: 8px;
            font-size: 0.82rem;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px
        }

        .flash-alert.error {
            background: rgba(220, 53, 69, 0.12);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #f87
        }

        /* ── Submit ── */
        .btn-submit {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, var(--green) 0%, #17a673 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-family: 'Syne', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 8px 24px rgba(28, 200, 138, 0.3);
            margin-top: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(28, 200, 138, 0.4)
        }

        .btn-submit:active {
            transform: translateY(0)
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.3s
        }

        .btn-submit:hover::before {
            opacity: 1
        }

        .btn-submit.loading {
            pointer-events: none
        }

        .btn-submit .btn-text {
            transition: opacity 0.2s
        }

        .btn-submit.loading .btn-text {
            opacity: 0
        }

        .btn-spinner {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 6px
        }

        .btn-submit.loading .btn-spinner {
            display: flex
        }

        .btn-spinner span {
            width: 7px;
            height: 7px;
            background: white;
            border-radius: 50%;
            animation: dotBounce 1.2s infinite ease-in-out both
        }

        .btn-spinner span:nth-child(1) {
            animation-delay: -0.32s
        }

        .btn-spinner span:nth-child(2) {
            animation-delay: -0.16s
        }

        @keyframes dotBounce {

            0%,
            80%,
            100% {
                transform: scale(0);
                opacity: 0.5
            }

            40% {
                transform: scale(1);
                opacity: 1
            }
        }

        .login-link {
            margin-top: 20px;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted)
        }

        .login-link a {
            color: var(--blue-light);
            text-decoration: none;
            font-weight: 500
        }

        .login-link a:hover {
            color: var(--white)
        }

        .footer-note {
            margin-top: 16px;
            text-align: center;
            font-size: 0.68rem;
            color: var(--text-muted)
        }

        @keyframes fadeSlideRight {
            from {
                opacity: 0;
                transform: translateX(-30px)
            }

            to {
                opacity: 1;
                transform: translateX(0)
            }
        }

        @keyframes fadeSlideLeft {
            from {
                opacity: 0;
                transform: translateX(30px)
            }

            to {
                opacity: 1;
                transform: translateX(0)
            }
        }

        @media(max-width:900px) {
            .page {
                grid-template-columns: 1fr
            }

            .left-panel {
                display: none
            }

            .right-panel {
                border-left: none;
                padding: 24px 20px
            }
        }
    </style>
</head>

<body>
    <video autoplay muted loop playsinline id="bg-video">
        <source src="<?= base_url('assets/video/login-bg2.mp4') ?>" type="video/mp4">
    </video>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="page">

        <!-- LEFT -->
        <div class="left-panel">
            <div class="brand-mark">
                <img src="<?= base_url('assets/img/TSC_page-0001.png') ?>" alt="TSC">
                <div class="brand-divider"></div>
                <span class="brand-label">Core System</span>
            </div>
            <div class="left-hero">
                <div class="eyebrow">Pendaftaran Karyawan Baru</div>
                <h1>Bergabung<br>dengan <em>Tim</em><br>TSC</h1>
                <p>Daftarkan dirimu sebagai karyawan PT Tata Sanjaya Cakrawala. Akunmu akan aktif setelah disetujui oleh
                    Superadmin.</p>
            </div>
            <div>
                <p
                    style="font-size:0.72rem;color:var(--text-muted);letter-spacing:1.5px;text-transform:uppercase;font-weight:600;margin-bottom:14px">
                    Cara Kerja</p>
                <ul class="steps-list">
                    <li><span class="step-num">1</span>Isi formulir dengan data diri yang benar</li>
                    <li><span class="step-num">2</span>Buat password untuk akun kamu</li>
                    <li><span class="step-num">3</span>Tunggu persetujuan Superadmin (1×24 jam)</li>
                    <li><span class="step-num">4</span>Login menggunakan nama lengkap(tanpa spasi) misalkan ABDUL SOMAD (abdulsomad) &amp; password</li>
                </ul>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="right-panel">
            <div class="register-card">

                <div class="card-header-area">
                    <div class="tag">Formulir Pendaftaran</div>
                    <h2>Buat Akun<br>Karyawan Baru</h2>
                    <p>Data akan diverifikasi sebelum akun diaktifkan.</p>
                </div>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="flash-alert error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $this->session->flashdata('error') ?>
                    </div>
                <?php endif ?>

                <form action="<?= base_url('register/proses') ?>" method="POST" enctype="multipart/form-data"
                    id="registerForm">

                    <!-- ── IDENTITAS ── -->
                    <div class="section-title">Identitas Diri</div>

                    <div class="field-group">
                        <label class="field-label">NIK <span class="req">*</span></label>
                        <div class="field-wrap">
                            <i class="fas fa-id-card field-icon"></i>
                            <input type="text" name="nik" id="nikField" placeholder="16 digit NIK KTP" maxlength="16"
                                inputmode="numeric" required value="<?= set_value('nik') ?>">
                        </div>
                        <div id="nik-hint" class="field-hint"></div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Nama Lengkap <span class="req">*</span></label>
                        <div class="field-wrap">
                            <i class="fas fa-user field-icon"></i>
                            <input type="text" name="nama" placeholder="Sesuai KTP" required
                                value="<?= set_value('nama') ?>">
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Tanggal Lahir <span class="req">*</span></label>
                            <div class="field-wrap">
                                <i class="fas fa-calendar field-icon"></i>
                                <input type="date" name="tanggal_lahir" required
                                    max="<?= date('Y-m-d', strtotime('-17 years')) ?>"
                                    value="<?= set_value('tanggal_lahir') ?>">
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Tgl Bergabung</label>
                            <div class="field-wrap">
                                <i class="fas fa-briefcase field-icon"></i>
                                <input type="date" name="tanggal_join">
                            </div>
                        </div>
                    </div>

                    <!-- ── POSISI & DIVISI ── -->
                    <div class="section-title">Posisi &amp; Divisi</div>

                    <div class="field-group">
                        <label class="field-label">Posisi / Role <span class="req">*</span></label>
                        <div class="field-wrap select-arrow">
                            <i class="fas fa-layer-group field-icon"></i>
                            <select name="user_level" required>
                                <option value="">-- Pilih posisi --</option>
                                <optgroup label="--- Staff ---">
                                    <option value="operational_staff">Operational Staff</option>
                                    <option value="finance_staff">Finance Staff</option>
                                    <option value="fleet_staff">Fleet Staff</option>
                                    <option value="admin_document">Admin Document</option>
                                    <option value="admin_operational">Admin Operational</option>
                                    <option value="hr_staff">HR Staff</option>
                                </optgroup>
                                <optgroup label="--- Operasional ---">
                                    <option value="yamazaki">Yamazaki</option>
                                    <option value="tsf">TSF</option>
                                    <option value="sinar_boga">Sinar Boga</option>
                                    <option value="rorotan">Rorotan</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Status Kepegawaian</label>
                        <div class="status-pills">
                            <?php foreach ([['Tetap', 'fa-user-check'], ['Kontrak', 'fa-file-contract'], ['Magang', 'fa-user-graduate']] as [$val, $icon]): ?>
                                <div class="status-pill">
                                    <input type="radio" name="status_kepegawaian" id="sk_<?= $val ?>" value="<?= $val ?>">
                                    <label for="sk_<?= $val ?>"><i class="fas <?= $icon ?>"></i> <?= $val ?></label>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Group / Divisi</label>
                        <?php $groups = [['Yamazaki Staff', '#c2185b'], ['Admin TSC', '#1565c0'], ['Operasional TSC', '#2e7d32'], ['TSF Staff', '#f57f17'], ['Sinar Boga Staff', '#6a1b9a'], ['Rorotan Staff', '#bf360c']]; ?>
                        <div class="grp-grid">
                            <?php foreach ($groups as [$grp, $clr]): ?>
                                <div class="grp-opt">
                                    <input type="radio" name="group_karyawan" id="grp_<?= str_replace(' ', '_', $grp) ?>"
                                        value="<?= $grp ?>">
                                    <label for="grp_<?= str_replace(' ', '_', $grp) ?>">
                                        <span class="grp-dot" style="background:<?= $clr ?>"></span><?= $grp ?>
                                    </label>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <!-- ── FOTO KTP OPSIONAL ── -->
                    <div class="section-title">
                        Foto KTP <span class="optional-tag">Opsional</span>
                    </div>

                    <div class="ktp-upload-area" id="ktpArea" onclick="document.getElementById('foto_ktp').click()">
                        <input type="file" name="foto_ktp" id="foto_ktp" accept="image/*">
                        <i class="fas fa-cloud-upload-alt"
                            style="font-size:1.6rem;color:var(--text-muted);margin-bottom:6px;display:block"></i>
                        <p id="ktpText" style="font-size:0.79rem;color:var(--text-muted);margin:0">
                            Klik untuk upload <em style="opacity:0.6">(boleh dilewati)</em><br>
                            <small>JPG / PNG · Maks 2MB</small>
                        </p>
                        <img id="ktpPreview" class="ktp-preview" alt="Preview KTP">
                    </div>

                    <!-- ── PASSWORD ── -->
                    <div class="section-title">Keamanan Akun</div>

                    <div class="field-group">
                        <label class="field-label">Password <span class="req">*</span></label>
                        <div class="field-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" name="password" id="pwdField" placeholder="Minimal 6 karakter"
                                required>
                            <button type="button" class="toggle-pass" onclick="togglePwd('pwdField','toggleIcon1')">
                                <i class="fas fa-eye-slash" id="toggleIcon1"></i>
                            </button>
                        </div>
                        <div class="pwd-strength" id="pwdStrengthBar"></div>
                        <div class="field-hint" id="pwdHint">Minimal 6 karakter</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Konfirmasi Password <span class="req">*</span></label>
                        <div class="field-wrap">
                            <i class="fas fa-lock field-icon"></i>
                            <input type="password" name="password_konfirm" id="pwdKonfirm" placeholder="Ulangi password"
                                required>
                            <button type="button" class="toggle-pass" onclick="togglePwd('pwdKonfirm','toggleIcon2')">
                                <i class="fas fa-eye-slash" id="toggleIcon2"></i>
                            </button>
                        </div>
                        <div class="field-hint" id="pwdMatchHint"></div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="btn-text"><i class="fas fa-paper-plane" style="margin-right:8px"></i>Kirim
                            Pendaftaran</span>
                        <div class="btn-spinner"><span></span><span></span><span></span></div>
                    </button>

                </form>

                <div class="login-link">
                    Sudah punya akun? <a href="<?= base_url('login') ?>">Masuk di sini</a>
                </div>
                <div class="footer-note">
                    &copy; <?= date('Y') ?> PT Tata Sanjaya Cakrawala &nbsp;&middot;&nbsp; All Rights Reserved
                </div>

            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        function togglePwd(fieldId, iconId) {
            const f = document.getElementById(fieldId);
            const i = document.getElementById(iconId);
            if (f.type === 'password') { f.type = 'text'; i.classList.replace('fa-eye-slash', 'fa-eye'); }
            else { f.type = 'password'; i.classList.replace('fa-eye', 'fa-eye-slash'); }
        }

        // NIK angka only
        document.getElementById('nikField').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            const hint = document.getElementById('nik-hint');
            const len = this.value.length;
            if (!len) { hint.textContent = ''; return; }
            if (len < 16) { hint.textContent = len + '/16 digit'; hint.style.color = '#f8b400'; }
            else { hint.textContent = '✓ NIK valid'; hint.style.color = '#1cc88a'; }
        });

        // Password strength
        document.getElementById('pwdField').addEventListener('input', function () {
            const v = this.value;
            const bar = document.getElementById('pwdStrengthBar');
            const hint = document.getElementById('pwdHint');
            let score = 0;
            if (v.length >= 6) score++;
            if (v.length >= 10) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const colors = ['', '#dc3545', '#f8b400', '#f8b400', '#1cc88a', '#1cc88a'];
            const labels = ['', 'Sangat Lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
            const idx = Math.min(score, 5);
            bar.style.background = v.length ? colors[idx] : 'rgba(255,255,255,0.12)';
            bar.style.width = v.length ? (score / 5 * 100) + '%' : '100%';
            hint.textContent = v.length ? labels[idx] : 'Minimal 6 karakter';
            hint.style.color = v.length ? colors[idx] : '';
            checkMatch();
        });

        // Password match
        document.getElementById('pwdKonfirm').addEventListener('input', checkMatch);
        function checkMatch() {
            const a = document.getElementById('pwdField').value;
            const b = document.getElementById('pwdKonfirm').value;
            const hint = document.getElementById('pwdMatchHint');
            if (!b) { hint.textContent = ''; return; }
            if (a === b) { hint.textContent = '✓ Password cocok'; hint.style.color = '#1cc88a'; }
            else { hint.textContent = '✗ Password tidak cocok'; hint.style.color = '#dc3545'; }
        }

        // KTP upload opsional
        document.getElementById('foto_ktp').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                alert('File terlalu besar! Maksimal 2MB.');
                this.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.getElementById('ktpPreview');
                img.src = e.target.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(file);
            document.getElementById('ktpText').innerHTML =
                '<strong style="color:#1cc88a"><i class="fas fa-check-circle me-1"></i>' + file.name + '</strong>';
            document.getElementById('ktpArea').classList.add('has-file');
        });

        // Submit validation
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const nik = document.getElementById('nikField').value;
            const pwd = document.getElementById('pwdField').value;
            const pwd2 = document.getElementById('pwdKonfirm').value;
            if (nik.length !== 16) { alert('NIK harus 16 digit!'); return; }
            if (pwd.length < 6) { alert('Password minimal 6 karakter!'); return; }
            if (pwd !== pwd2) { alert('Konfirmasi password tidak cocok!'); return; }
            document.getElementById('submitBtn').classList.add('loading');
            this.submit();
        });
    </script>
</body>

</html>
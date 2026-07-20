<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
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
            --white: #ffffff;
            --white-dim: rgba(255, 255, 255, 0.07);
            --white-border: rgba(255, 255, 255, 0.12);
            --text-muted: rgba(255, 255, 255, 0.45)
        }

        html,
        body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--navy);
            overflow: hidden
        }

        #bg-video {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            filter: brightness(0.25) saturate(0.6)
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
            background: radial-gradient(circle, rgba(30, 111, 191, 0.35) 0%, transparent 70%);
            top: -100px;
            left: -100px;
            animation-delay: 0s
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(240, 165, 0, 0.2) 0%, transparent 70%);
            bottom: -50px;
            right: -50px;
            animation-delay: -4s
        }

        .orb-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(61, 155, 233, 0.2) 0%, transparent 70%);
            top: 50%;
            right: 20%;
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
            height: 100vh
        }

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
            color: var(--accent);
            margin-bottom: 24px
        }

        .left-hero .eyebrow::before {
            content: '';
            display: block;
            width: 24px;
            height: 1px;
            background: var(--accent)
        }

        .left-hero h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2.8rem, 4vw, 4rem);
            font-weight: 800;
            line-height: 1.05;
            color: var(--white);
            letter-spacing: -1px;
            margin-bottom: 20px
        }

        .left-hero h1 em {
            font-style: normal;
            color: transparent;
            -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.5)
        }

        .left-hero p {
            font-size: 1rem;
            color: var(--text-muted);
            line-height: 1.7;
            max-width: 380px;
            font-weight: 300
        }

        .stats-row {
            display: flex;
            gap: 40px
        }

        .stat-item .val {
            font-family: 'Syne', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1
        }

        .stat-item .lbl {
            font-size: 0.72rem;
            color: var(--text-muted);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 4px
        }

        .stat-divider {
            width: 1px;
            background: var(--white-border);
            align-self: stretch
        }

        .right-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 40px;
            border-left: 1px solid var(--white-border);
            background: rgba(13, 27, 42, 0.6);
            backdrop-filter: blur(24px);
            opacity: 0;
            animation: fadeSlideLeft 0.8s ease 0.5s forwards
        }

        .login-card {
            width: 100%
        }

        .card-header-area {
            margin-bottom: 36px
        }

        .card-header-area .tag {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent);
            border: 1px solid rgba(240, 165, 0, 0.3);
            background: rgba(240, 165, 0, 0.08);
            padding: 4px 12px;
            border-radius: 2px;
            margin-bottom: 16px
        }

        .card-header-area h2 {
            font-family: 'Syne', sans-serif;
            font-size: 1.9rem;
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

        .field-group {
            margin-bottom: 16px
        }

        .field-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 8px
        }

        .field-wrap {
            position: relative
        }

        .field-wrap .field-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.85rem;
            pointer-events: none;
            transition: color 0.2s
        }

        .field-wrap input,
        .field-wrap select {
            width: 100%;
            height: 50px;
            background: var(--white-dim);
            border: 1px solid var(--white-border);
            border-radius: 8px;
            color: var(--white);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            padding: 0 44px 0 44px;
            outline: none;
            transition: border-color 0.25s, background 0.25s, box-shadow 0.25s;
            -webkit-appearance: none
        }

        .field-wrap select option {
            background: var(--navy-mid);
            color: var(--white)
        }

        .field-wrap select optgroup {
            background: var(--navy-mid);
            color: var(--accent);
            font-size: 0.75rem
        }

        .field-wrap input::placeholder {
            color: var(--text-muted)
        }

        .field-wrap input:focus,
        .field-wrap select:focus {
            border-color: var(--blue-light);
            background: rgba(61, 155, 233, 0.06);
            box-shadow: 0 0 0 3px rgba(61, 155, 233, 0.12)
        }

        .select-wrap::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            font-size: 0.8rem
        }

        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 0.85rem;
            padding: 4px;
            transition: color 0.2s;
            z-index: 2
        }

        .toggle-pass:hover {
            color: var(--white)
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 18px 0 24px
        }

        .custom-check {
            width: 18px;
            height: 18px;
            border: 1.5px solid var(--white-border);
            border-radius: 4px;
            background: var(--white-dim);
            appearance: none;
            cursor: pointer;
            position: relative;
            flex-shrink: 0;
            transition: border-color 0.2s, background 0.2s
        }

        .custom-check:checked {
            background: var(--blue);
            border-color: var(--blue)
        }

        .custom-check:checked::after {
            content: '';
            position: absolute;
            left: 4px;
            top: 1px;
            width: 6px;
            height: 10px;
            border: 2px solid white;
            border-top: none;
            border-left: none;
            transform: rotate(45deg)
        }

        .remember-row label {
            font-size: 0.82rem;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none
        }

        .btn-submit {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-light) 100%);
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
            box-shadow: 0 8px 24px rgba(30, 111, 191, 0.35)
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.3s
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(30, 111, 191, 0.45)
        }

        .btn-submit:hover::before {
            opacity: 1
        }

        .btn-submit:active {
            transform: translateY(0)
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

        .btn-spinner-dots {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 6px
        }

        .btn-submit.loading .btn-spinner-dots {
            display: flex
        }

        .btn-spinner-dots span {
            width: 7px;
            height: 7px;
            background: white;
            border-radius: 50%;
            animation: dotBounce 1.2s infinite ease-in-out both
        }

        .btn-spinner-dots span:nth-child(1) {
            animation-delay: -0.32s
        }

        .btn-spinner-dots span:nth-child(2) {
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

        .flash-alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.82rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px
        }

        .flash-alert.error {
            background: rgba(220, 53, 69, 0.12);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #f87
        }

        .flash-alert.success {
            background: rgba(25, 135, 84, 0.12);
            border: 1px solid rgba(25, 135, 84, 0.3);
            color: #6f9
        }

        /* ── Register link ── */
        .register-link {
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid var(--white-border);
            text-align: center;
            font-size: 0.82rem;
            color: var(--text-muted)
        }

        .register-link a {
            color: var(--blue-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s
        }

        .register-link a:hover {
            color: var(--white)
        }

        .card-footer-area {
            margin-top: 16px;
            text-align: center;
            font-size: 0.72rem;
            color: var(--text-muted);
            letter-spacing: 0.5px
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            background: var(--navy);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 20px;
            opacity: 0;
            transition: opacity 0.4s
        }

        .loading-overlay.show {
            display: flex;
            opacity: 1
        }

        .loading-logo {
            width: 60px;
            filter: brightness(0) invert(1);
            opacity: 0.8;
            animation: loadPulse 1.5s ease-in-out infinite
        }

        @keyframes loadPulse {

            0%,
            100% {
                opacity: 0.5;
                transform: scale(0.97)
            }

            50% {
                opacity: 1;
                transform: scale(1.03)
            }
        }

        .loading-bar-wrap {
            width: 200px;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden
        }

        .loading-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--blue), var(--blue-light), var(--accent));
            border-radius: 2px;
            animation: loadBar 2s ease-in-out forwards
        }

        @keyframes loadBar {
            0% {
                width: 0%
            }

            40% {
                width: 60%
            }

            70% {
                width: 80%
            }

            100% {
                width: 100%
            }
        }

        .loading-msg {
            font-family: 'Syne', sans-serif;
            font-size: 0.75rem;
            letter-spacing: 2px;
            text-transform: uppercase;
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

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0)
            }

            15%,
            45%,
            75% {
                transform: translateX(-8px)
            }

            30%,
            60%,
            90% {
                transform: translateX(8px)
            }
        }

        .shake {
            animation: shake 0.5s ease
        }

        @media(max-width:900px) {
            .page {
                grid-template-columns: 1fr
            }

            .left-panel {
                display: none
            }

            .right-panel {
                border-left: none
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

    <div class="loading-overlay" id="loadingOverlay">
        <img src="<?= base_url('assets/img/TSC_page-0001.png') ?>" class="loading-logo" alt="TSC">
        <div class="loading-bar-wrap">
            <div class="loading-bar" id="loadingBar"></div>
        </div>
        <div class="loading-msg" id="loadingMsg">Memverifikasi...</div>
    </div>

    <div class="page">

        <!-- LEFT -->
        <div class="left-panel">
            <div class="brand-mark">
                <img src="<?= base_url('assets/img/TSC_page-0001.png') ?>" alt="TSC">
                <div class="brand-divider"></div>
                <span class="brand-label">Core System</span>
            </div>
            <div class="left-hero">
                <div class="eyebrow">Sistem Manajemen Terpadu</div>
                <h1>Kelola <em>Bisnis</em><br>Lebih Efisien</h1>
                <p>Platform terintegrasi untuk operasional, keuangan, armada, dan inventaris PT Tata Sanjaya Cakrawala.
                </p>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <div class="val">15+</div>
                    <div class="lbl">Level Akses</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="val">24/7</div>
                    <div class="lbl">Monitoring</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="val">Real-time</div>
                    <div class="lbl">Notifikasi</div>
                </div>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="right-panel">
            <div class="login-card">

                <div class="card-header-area">
                    <div class="tag">Secure Login</div>
                    <h2>Masuk ke<br>Dashboard</h2>
                    <p>Gunakan kredensial yang telah diberikan admin.</p>
                </div>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="flash-alert error" id="flashAlert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $this->session->flashdata('error') ?>
                    </div>
                <?php endif ?>

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="flash-alert success">
                        <i class="fas fa-check-circle"></i>
                        <?= $this->session->flashdata('success') ?>
                    </div>
                <?php endif ?>

                <form action="<?= base_url('login/proses') ?>" method="POST" id="loginForm">

                    <div class="field-group">
                        <label class="field-label">NIK / Username</label>
                        <div class="field-wrap">
                            <input type="text" name="identifier" id="identifierField"
                                placeholder="Masukkan NIK atau username" required autocomplete="username">
                            <i class="fas fa-user field-icon"></i>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Password</label>
                        <div class="field-wrap">
                            <input type="password" name="password" id="passwordField" placeholder="Masukkan password"
                                required autocomplete="current-password">
                            <i class="fas fa-lock field-icon"></i>
                            <button type="button" class="toggle-pass" onclick="togglePassword()">
                                <i class="fas fa-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Level Akses</label>
                        <div class="field-wrap select-wrap">
                            <select name="role" id="roleField" required>
                                <option value="">-- Pilih level --</option>
                                <optgroup label="--- Management ---">
                                    <option value="superadmin">Superadmin</option>
                                    <option value="viewer">Viewer / Manajemen</option>
                                    <option value="head_of_departemen">Head of Departemen</option>
                                    <option value="operational_lead">Operational Lead</option>
                                    <option value="administration_lead">Administration Lead</option>
                                    <option value="hr_staff">HR Staff</option>
                                </optgroup>
                                <optgroup label="--- Staff ---">
                                    <option value="admin_operational">Admin Operational</option>
                                    <option value="operational_staff">Operational Staff</option>
                                    <option value="finance_staff">Finance Staff</option>
                                    <option value="fleet_staff">Fleet Staff</option>
                                    <option value="admin_document">Admin Document</option>
                                </optgroup>
                                <optgroup label="--- Operasional ---">
                                    <option value="yamazaki">Yamazaki</option>
                                    <option value="tsf">TSF</option>
                                    <option value="sinar_boga">Sinar Boga</option>
                                    <option value="rorotan">Rorotan</option>
                                </optgroup>
                            </select>
                            <i class="fas fa-layer-group field-icon"></i>
                        </div>
                    </div>

                    <div class="remember-row">
                        <input type="checkbox" name="remember_me" id="rememberMe" class="custom-check" value="1">
                        <label for="rememberMe">Ingat saya selama 30 hari</label>
                    </div>

                    <button type="submit" class="btn-submit" id="loginBtn">
                        <span class="btn-text">Masuk Sekarang</span>
                        <div class="btn-spinner-dots"><span></span><span></span><span></span></div>
                    </button>

                </form>

                <!-- ── Link Daftar ── -->
                <div class="register-link">
                    Karyawan baru?
                    <a href="<?= base_url('register') ?>">
                        <i class="fas fa-user-plus" style="margin-right:4px"></i>Daftar di sini
                    </a>
                </div>

                <div class="card-footer-area">
                    &copy; <?= date('Y') ?> PT Tata Sanjaya Cakrawala &nbsp;&middot;&nbsp; All Rights Reserved
                </div>

            </div>
        </div>

    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        function togglePassword() {
            const f = document.getElementById('passwordField');
            const i = document.getElementById('toggleIcon');
            if (f.type === 'password') { f.type = 'text'; i.classList.replace('fa-eye-slash', 'fa-eye'); }
            else { f.type = 'password'; i.classList.replace('fa-eye', 'fa-eye-slash'); }
        }

        window.addEventListener('DOMContentLoaded', function () {
            const saved = getCookie('tsc_remember');
            if (saved) {
                try {
                    const d = JSON.parse(decodeURIComponent(saved));
                    document.getElementById('identifierField').value = d.identifier || '';
                    document.getElementById('roleField').value = d.role || '';
                    document.getElementById('rememberMe').checked = true;
                } catch (e) { }
            }
            <?php if ($this->session->flashdata('error')): ?>
                const card = document.querySelector('.login-card');
                if (card) { card.classList.add('shake'); setTimeout(() => card.classList.remove('shake'), 500); }
            <?php endif ?>
        });

        function getCookie(name) {
            const v = `; ${document.cookie}`;
            const p = v.split(`; ${name}=`);
            if (p.length === 2) return p.pop().split(';').shift();
            return null;
        }

        const msgs = ['Memverifikasi Kredensial...', 'Menyiapkan Dashboard...', 'Hampir Selesai...'];
        let msgIdx = 0;

        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('loginBtn');
            const overlay = document.getElementById('loadingOverlay');
            const msgEl = document.getElementById('loadingMsg');
            btn.classList.add('loading');
            setTimeout(() => {
                overlay.classList.add('show');
                const iv = setInterval(() => { msgIdx = (msgIdx + 1) % msgs.length; msgEl.textContent = msgs[msgIdx]; }, 900);
                setTimeout(() => { clearInterval(iv); this.submit(); }, 2800);
            }, 400);
        }.bind(document.getElementById('loginForm')));

        history.pushState(null, null, location.href);
        window.onpopstate = () => history.go(1);
        // Enter di field manapun langsung trigger submit
        ['identifierField', 'passwordField', 'roleField'].forEach(function (id) {
            document.getElementById(id).addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('loginForm').requestSubmit
                        ? document.getElementById('loginForm').requestSubmit()
                        : document.getElementById('loginForm').dispatchEvent(new Event('submit', { cancelable: true }));
                }
            });
        });
    </script>
</body>

</html>
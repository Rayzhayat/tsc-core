<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->load->view('partials/head') ?>
    <style>
        /* VIDEO BACKGROUND */
        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: -1;
            object-fit: cover;
        }

        /* OVERLAY AGAR FORM TETAP TERBACA */
        .login-overlay {
            position: relative;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            border-radius: 18px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .login-box {
            max-width: 420px;
            margin: 5vh auto;
        }

        .logo-img {
            width: 280px;
            height: auto;
            margin-bottom: 15px;
            border-radius: 10px;
        }

        .input-group-text {
            cursor: pointer;
        }

        /* ================================= */
        /* LOADING OVERLAY */
        /* ================================= */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .loading-overlay.show {
            display: flex;
            opacity: 1;
        }

        .loading-content {
            text-align: center;
            color: white;
        }

        /* Spinner Animation */
        .spinner {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            position: relative;
        }

        .spinner-ring {
            width: 100%;
            height: 100%;
            border: 4px solid rgba(255, 255, 255, 0.2);
            border-top-color: #4e73df;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Dots Animation */
        .loading-dots {
            display: inline-flex;
            gap: 8px;
            margin-top: 10px;
        }

        .loading-dots span {
            width: 10px;
            height: 10px;
            background: #4e73df;
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .loading-dots span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .loading-dots span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .loading-text {
            font-size: 18px;
            font-weight: 500;
            margin-top: 15px;
        }

        .loading-subtext {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 5px;
        }

        /* Button Loading State */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading .btn-text {
            opacity: 0;
        }

        .btn-loading .btn-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            gap: 5px;
        }

        .btn-spinner span {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            animation: btnBounce 1.4s infinite ease-in-out both;
        }

        .btn-spinner span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .btn-spinner span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes btnBounce {
            0%, 80%, 100% {
                transform: scale(0);
            }
            40% {
                transform: scale(1);
            }
        }

        /* Form Shake Animation on Error */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
            20%, 40%, 60%, 80% { transform: translateX(10px); }
        }

        .shake {
            animation: shake 0.6s;
        }

        /* Success Animation */
        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: block;
            stroke-width: 3;
            stroke: #4bb543;
            stroke-miterlimit: 10;
            box-shadow: inset 0px 0px 0px #4bb543;
            animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
        }

        .success-checkmark circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 3;
            stroke-miterlimit: 10;
            stroke: #4bb543;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .success-checkmark path {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }

        @keyframes scale {
            0%, 100% {
                transform: none;
            }
            50% {
                transform: scale3d(1.1, 1.1, 1);
            }
        }

        @keyframes fill {
            100% {
                box-shadow: inset 0px 0px 0px 30px #4bb543;
            }
        }
    </style>
</head>

<body>
    <!-- VIDEO BACKGROUND MP4 -->
    <video autoplay muted loop playsinline id="bg-video">
        <source src="<?= base_url('assets/video/login-bg2.mp4') ?>" type="video/mp4">
        Browser lu gak support video 😢
    </video>

    <!-- LOADING OVERLAY -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="spinner">
                <div class="spinner-ring"></div>
            </div>
            <div class="loading-text" id="loadingText">Memproses Login...</div>
            <div class="loading-subtext" id="loadingSubtext">Mohon tunggu sebentar</div>
            <div class="loading-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="login-box">
            <div class="card login-overlay shadow-lg border-0" id="loginCard">
                <div class="card-body p-5 text-center">
                    <!-- LOGO -->
                    <img src="<?= base_url('assets/img/TSC_page-0001.png') ?>" alt="Logo PT Tata Sanjaya Cakrawala" class="logo-img">

                    <h4 class="mb-1">PT TATA SANJAYA CAKRAWALA</h4>
                    <p class="text-muted mb-4">TSC CORE SYSTEM</p>

                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">×</button>
                        </div>
                    <?php endif ?>

                    <form action="<?= base_url('login/proses') ?>" method="POST" id="loginForm">
                        <div class="form-group">
                            <input type="text" name="identifier" class="form-control form-control-lg" placeholder="NIK / Username" required>
                        </div>

                        <div class="form-group">
                            <div class="input-group input-group-lg">
                                <input type="password" name="password" id="passwordField" class="form-control" placeholder="Password" required>
                                <div class="input-group-append">
                                    <span class="input-group-text" onclick="togglePassword()">
                                        <i class="fa fa-eye-slash" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <select name="role" class="form-control form-control-lg" required>
                                <option value="">-- Pilih Level Akses --</option>
                                <option value="superadmin">Superadmin</option>
                                <option value="admin_operational">Admin Operational</option>
                                <option value="operational_staff">Operational Staff</option>
                                <option value="finance_staff">Finance Staff</option>
                                <option value="fleet_staff">Fleet Staff</option>
                                <option value="viewer">Viewer / Manajemen</option>
                                <option value="admin_document">Admin Document</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg mt-4" id="loginBtn">
                            <span class="btn-text">MASUK SISTEM</span>
                            <span class="btn-spinner" style="display: none;">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>
                    </form>

                    <hr>
                    <small class="text-muted">© 2025 PT Tata Sanjaya Cakrawala • All Rights Reserved</small>
                </div>
            </div>
        </div>
    </div>

    <?php $this->load->view('partials/js') ?>
    <script>
        // Toggle Password
        function togglePassword() {
            const field = document.getElementById('passwordField');
            const icon = document.getElementById('toggleIcon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        }

        // Login Form Handler
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const loginBtn = document.getElementById('loginBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const loadingText = document.getElementById('loadingText');
            const loadingSubtext = document.getElementById('loadingSubtext');
            
            // Add button loading state
            loginBtn.classList.add('btn-loading');
            loginBtn.querySelector('.btn-spinner').style.display = 'flex';
            
            // Show loading overlay after 500ms
            setTimeout(() => {
                loadingOverlay.classList.add('show');
            }, 500);

            // Simulate login process (replace with actual AJAX if needed)
            setTimeout(() => {
                loadingText.textContent = 'Memverifikasi Kredensial...';
                loadingSubtext.textContent = 'Memeriksa data pengguna';
            }, 1000);

            setTimeout(() => {
                loadingText.textContent = 'Menyiapkan Dashboard...';
                loadingSubtext.textContent = 'Hampir selesai';
            }, 2000);

            // Submit form after animation
            setTimeout(() => {
                form.submit();
            }, 2500);
        });

        // Check if there's an error and shake the form
        <?php if ($this->session->flashdata('error')): ?>
        window.addEventListener('DOMContentLoaded', function() {
            const loginCard = document.getElementById('loginCard');
            loginCard.classList.add('shake');
            setTimeout(() => {
                loginCard.classList.remove('shake');
            }, 600);
        });
        <?php endif ?>

        // Prevent back button after successful login
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</body>

</html>
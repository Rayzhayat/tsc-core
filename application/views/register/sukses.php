<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $this->load->view('partials/head') ?>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--navy:#0d1b2a;--blue:#1e6fbf;--blue-light:#3d9be9;--green:#1cc88a;--white:#ffffff;--white-dim:rgba(255,255,255,0.07);--white-border:rgba(255,255,255,0.12);--text-muted:rgba(255,255,255,0.45)}
        html,body{height:100%;font-family:'DM Sans',sans-serif;background:var(--navy);display:flex;align-items:center;justify-content:center}
        .orb{position:fixed;border-radius:50%;filter:blur(80px);z-index:0;pointer-events:none;animation:orbFloat 8s ease-in-out infinite}
        .orb-1{width:500px;height:500px;background:radial-gradient(circle,rgba(28,200,138,0.25) 0%,transparent 70%);top:-100px;left:-100px}
        .orb-2{width:400px;height:400px;background:radial-gradient(circle,rgba(30,111,191,0.2) 0%,transparent 70%);bottom:-50px;right:-50px;animation-delay:-4s}
        @keyframes orbFloat{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(20px,-20px) scale(1.04)}}

        .card{
            position:relative;z-index:1;
            background:rgba(26,46,69,0.7);
            backdrop-filter:blur(24px);
            border:1px solid var(--white-border);
            border-radius:20px;
            padding:52px 48px;
            max-width:480px;width:90%;
            text-align:center;
            animation:fadeUp 0.7s ease forwards;
        }
        @keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}

        .check-circle{
            width:80px;height:80px;border-radius:50%;
            background:rgba(28,200,138,0.15);
            border:2px solid rgba(28,200,138,0.4);
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 28px;
            font-size:2rem;color:var(--green);
            animation:popIn 0.5s cubic-bezier(0.175,0.885,0.32,1.275) 0.4s both;
        }
        @keyframes popIn{from{transform:scale(0)}to{transform:scale(1)}}

        .tag{display:inline-block;font-size:0.68rem;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--green);border:1px solid rgba(28,200,138,0.35);background:rgba(28,200,138,0.08);padding:4px 12px;border-radius:2px;margin-bottom:16px}
        h1{font-family:'Syne',sans-serif;font-size:1.9rem;font-weight:800;color:var(--white);margin-bottom:10px;letter-spacing:-0.5px}
        .subtitle{font-size:0.9rem;color:var(--text-muted);line-height:1.7;font-weight:300;margin-bottom:28px}
        .nama-highlight{color:var(--green);font-weight:600}

        .info-box{
            background:var(--white-dim);border:1px solid var(--white-border);
            border-radius:12px;padding:18px 20px;margin-bottom:28px;text-align:left;
        }
        .info-box .info-row{display:flex;align-items:flex-start;gap:12px;font-size:0.82rem;color:var(--text-muted);margin-bottom:10px}
        .info-box .info-row:last-child{margin-bottom:0}
        .info-box .info-row i{color:var(--blue-light);width:16px;text-align:center;margin-top:2px;flex-shrink:0}

        .btn-login{
            display:inline-flex;align-items:center;gap:10px;
            width:100%;height:48px;justify-content:center;
            background:linear-gradient(135deg,var(--blue) 0%,var(--blue-light) 100%);
            border:none;border-radius:8px;color:white;
            font-family:'Syne',sans-serif;font-size:0.82rem;font-weight:700;
            letter-spacing:2px;text-transform:uppercase;
            text-decoration:none;cursor:pointer;
            transition:transform 0.2s,box-shadow 0.2s;
            box-shadow:0 8px 24px rgba(30,111,191,0.3);
        }
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(30,111,191,0.4);color:white}

        .footer-note{margin-top:20px;font-size:0.72rem;color:var(--text-muted)}
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="card">
        <div class="check-circle"><i class="fas fa-check"></i></div>
        <div class="tag">Pendaftaran Terkirim</div>
        <h1>Berhasil!</h1>
        <p class="subtitle">
            Halo <span class="nama-highlight"><?= htmlspecialchars($nama) ?></span>, pengajuan pendaftaran kamu telah berhasil dikirim.<br>
            Tunggu persetujuan dari Superadmin sebelum bisa login.
        </p>

        <div class="info-box">
            <div class="info-row">
                <i class="fas fa-clock"></i>
                <span>Proses persetujuan biasanya membutuhkan waktu <strong style="color:var(--white)">1×24 jam</strong> di hari kerja.</span>
            </div>
            <div class="info-row">
                <i class="fas fa-envelope"></i>
                <span>Superadmin akan mereview data dan foto KTP yang kamu upload.</span>
            </div>
            <div class="info-row">
                <i class="fas fa-info-circle"></i>
                <span>Setelah disetujui, kamu bisa login menggunakan <strong style="color:var(--white)">Nama Lengkap</strong> dan <strong style="color:var(--white)">password</strong> yang tadi dibuat.</span>
            </div>
        </div>

        <a href="<?= base_url('login') ?>" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Kembali ke Halaman Login
        </a>
        <div class="footer-note">&copy; <?= date('Y') ?> PT Tata Sanjaya Cakrawala</div>
    </div>

    <?php $this->load->view('partials/js') ?>
</body>
</html>
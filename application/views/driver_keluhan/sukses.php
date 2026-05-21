<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Terkirim &mdash; TSC</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
            width: 100%;
            max-width: 440px;
            text-align: center;
            padding: 48px 32px;
        }

        .checkmark {
            width: 80px;
            height: 80px;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.2rem;
        }

        h3 {
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 8px;
        }

        p {
            color: #6c757d;
            font-size: .92rem;
            margin-bottom: 24px;
        }

        .btn-back {
            background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px 32px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            font-size: .95rem;
            transition: opacity .2s;
        }

        .btn-back:hover {
            opacity: .9;
            color: #fff;
        }

        .tsc-footer {
            margin-top: 28px;
            font-size: .75rem;
            color: #adb5bd;
        }
    </style>
</head>

<body>
    <div class="success-card">
        <div class="checkmark">✅</div>
        <h3>Laporan Terkirim!</h3>
        <p>Terima kasih. Keluhan kamu sudah diterima dan akan segera ditindaklanjuti oleh tim operasional TSC.</p>
        <a href="<?= base_url('driver_keluhan') ?>" class="btn-back">
            <i class="fas fa-plus me-2"></i> Buat Laporan Baru
        </a>
        <div class="tsc-footer">&copy; <?= date('Y') ?> PT Tata Sanjaya Cakrawala</div>
    </div>
</body>

</html>
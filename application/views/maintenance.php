<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance — TSC</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;800&family=DM+Sans:wght@300;400&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: #0d1b2a;
            color: #fff;
            overflow: hidden;
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(240, 165, 0, .2) 0%, transparent 70%);
            top: -100px;
            left: -100px;
            animation: orbFloat 8s ease-in-out infinite;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(30, 111, 191, .2) 0%, transparent 70%);
            bottom: -80px;
            right: -80px;
            animation: orbFloat 8s ease-in-out infinite reverse;
        }

        @keyframes orbFloat {

            0%,
            100% {
                transform: translate(0, 0)
            }

            50% {
                transform: translate(20px, -20px)
            }
        }

        .wrap {
            position: relative;
            z-index: 2;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0;
            text-align: center;
            padding: 2rem;
        }

        .icon-ring {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid rgba(240, 165, 0, .4);
            background: rgba(240, 165, 0, .08);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(240, 165, 0, .3)
            }

            50% {
                box-shadow: 0 0 0 20px rgba(240, 165, 0, 0)
            }
        }

        .gear {
            font-size: 48px;
            animation: spin 8s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg)
            }

            to {
                transform: rotate(360deg)
            }
        }

        .tag {
            display: inline-block;
            font-size: .7rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #f0a500;
            border: 1px solid rgba(240, 165, 0, .35);
            padding: 4px 14px;
            border-radius: 2px;
            margin-bottom: 20px;
        }

        h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }

        h1 span {
            color: #f0a500;
        }

        p.sub {
            font-size: 1rem;
            color: rgba(255, 255, 255, .5);
            line-height: 1.7;
            max-width: 400px;
            font-weight: 300;
            margin-bottom: 40px;
        }

        .dots {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .dots span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #f0a500;
            animation: blink 1.4s ease-in-out infinite;
        }

        .dots span:nth-child(2) {
            animation-delay: .3s;
        }

        .dots span:nth-child(3) {
            animation-delay: .6s;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: .15
            }

            50% {
                opacity: 1
            }
        }

        .footer {
            margin-top: 48px;
            font-size: .75rem;
            color: rgba(255, 255, 255, .2);
        }
    </style>
</head>

<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="wrap">
        <div class="icon-ring">
            <span class="gear">⚙️</span>
        </div>

        <div class="tag">System Notice</div>
        <h1>Under <span>Maintenance</span></h1>
        <p class="sub">Sistem sedang dalam pemeliharaan terjadwal. Kami sedang bekerja untuk meningkatkan layanan. Mohon coba beberapa saat lagi.</p>
        <div class="dots"><span></span><span></span><span></span></div>

        <div class="footer">&copy; <?= date('Y') ?> PT Tata Sanjaya Cakrawala</div>
    </div>
</body>

</html>
<script>
    setInterval(function() {
        fetch(window.location.href, {
                method: 'HEAD'
            })
            .then(r => {
                // Kalau server redirect (maintenance OFF), reload halaman
                if (r.redirected) window.location.reload();
            })
            .catch(() => {});
    }, 10000); // cek tiap 10 detik
</script>
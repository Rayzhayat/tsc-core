<!DOCTYPE html>
<html lang="id" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TSC - Your Best Logistic Partner</title>
    <link rel="icon" type="image/png" href="assets/img/TSC_page-0001.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ══════════════════════════════════════════
           THEME VARIABLES
        ══════════════════════════════════════════ */
        :root {
            --electric: #2563EB;
            --electric-bright: #3B82F6;
            --electric-glow: #60A5FA;
            --gold: #F59E0B;
            --gold-light: #FCD34D;
            --radius: 16px;
            --radius-lg: 24px;
            --transition-theme: background 0.4s ease, color 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
        }

        /* ── DARK THEME (default) ── */
        [data-theme="dark"] {
            --bg: #0A1628;
            --bg-mid: #0F1F3D;
            --bg-light: #162845;
            --white: #F8FAFC;
            --white-dim: rgba(248, 250, 252, 0.85);
            --white-muted: rgba(248, 250, 252, 0.5);
            --white-ghost: rgba(248, 250, 252, 0.08);
            --border: rgba(248, 250, 252, 0.1);
            --shadow-electric: 0 0 40px rgba(37, 99, 235, 0.3);
            --shadow-deep: 0 25px 50px rgba(0, 0, 0, 0.5);
            --footer-bg: #060E1A;
            --card-bg: #0F1F3D;
            --card-hover: #162845;
            --about-placeholder-bg: linear-gradient(135deg, #162845, #0F1F3D);
        }

        /* ── LIGHT THEME ── */
        [data-theme="light"] {
            --bg: #F0F4FF;
            --bg-mid: #E8EEFF;
            --bg-light: #DDE6FF;
            --white: #0A1628;
            --white-dim: rgba(10, 22, 40, 0.85);
            --white-muted: rgba(10, 22, 40, 0.55);
            --white-ghost: rgba(10, 22, 40, 0.06);
            --border: rgba(10, 22, 40, 0.12);
            --shadow-electric: 0 0 40px rgba(37, 99, 235, 0.2);
            --shadow-deep: 0 25px 50px rgba(10, 22, 40, 0.15);
            --footer-bg: #D8E2FF;
            --card-bg: #FFFFFF;
            --card-hover: #EEF3FF;
            --about-placeholder-bg: linear-gradient(135deg, #DDE6FF, #E8EEFF);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
            transition: var(--transition-theme);
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 9999;
            opacity: 0.4;
        }

        /* ══════════════════════════════════════════
           CONTROLS BAR (Theme + Language)
        ══════════════════════════════════════════ */
        .controls-bar {
            position: fixed;
            top: 0;
            right: 0;
            z-index: 1100;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
        }

        /* Language Toggle */
        .lang-toggle {
            display: flex;
            align-items: center;
            background: var(--white-ghost);
            border: 1px solid var(--border);
            border-radius: 100px;
            padding: 3px;
            gap: 2px;
            transition: var(--transition-theme);
            backdrop-filter: blur(12px);
        }

        .lang-btn {
            font-family: 'Syne', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 4px 10px;
            border-radius: 100px;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
            background: transparent;
            color: var(--white-muted);
        }

        .lang-btn.active {
            background: var(--electric);
            color: white;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4);
        }

        /* Theme Toggle */
        .theme-toggle {
            width: 44px;
            height: 24px;
            background: var(--white-ghost);
            border: 1px solid var(--border);
            border-radius: 100px;
            cursor: pointer;
            position: relative;
            transition: var(--transition-theme);
            backdrop-filter: blur(12px);
            flex-shrink: 0;
        }

        .theme-toggle-knob {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--electric);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.55rem;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4);
        }

        [data-theme="light"] .theme-toggle-knob {
            transform: translateX(20px);
            background: var(--gold);
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.4);
        }

        .theme-icon {
            pointer-events: none;
            line-height: 1;
        }

        /* ══════════════════════════════════════════
           NAVBAR
        ══════════════════════════════════════════ */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 1.2rem 0;
            transition: all 0.4s ease;
        }

        .navbar.scrolled {
            background: rgba(var(--bg-rgb, 10, 22, 40), 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0.8rem 0;
        }

        [data-theme="light"] .navbar.scrolled {
            background: rgba(240, 244, 255, 0.95);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            z-index: 1001;
            flex-shrink: 0;
        }

        .logo-placeholder {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--electric), var(--electric-glow));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 0.95rem;
            color: white;
        }

        .navbar-logo img {
            height: 40px;
            width: auto;
            filter: brightness(0) invert(1);
            transition: opacity 0.3s;
        }

        [data-theme="light"] .navbar-logo img {
            filter: brightness(0);
        }

        .navbar-logo:hover img {
            opacity: 0.8;
        }

        .logo-text {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--white);
            letter-spacing: 0.5px;
        }

        .navbar-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .navbar-menu a {
            text-decoration: none;
            color: var(--white-muted);
            font-size: 0.88rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: color 0.3s;
            white-space: nowrap;
        }

        .navbar-menu a:hover {
            color: var(--white);
        }

        .nav-cta {
            background: var(--electric) !important;
            color: white !important;
            padding: 0.5rem 1.2rem !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            transition: all 0.3s !important;
        }

        .nav-cta:hover {
            background: var(--electric-bright) !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4) !important;
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            z-index: 1002;
            gap: 5px;
            padding: 4px;
            background: none;
            border: none;
        }

        .menu-toggle span {
            width: 24px;
            height: 2px;
            background: var(--white);
            border-radius: 2px;
            transition: all 0.3s ease;
            display: block;
        }

        .menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 7px);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -7px);
        }

        .nav-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .nav-overlay.active {
            display: block;
            opacity: 1;
        }

        /* ══════════════════════════════════════════
           HERO
        ══════════════════════════════════════════ */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 120px 1.5rem 80px;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(37, 99, 235, 0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.07) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 50%, black 0%, transparent 100%);
        }

        .hero-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
        }

        .hero-orb-1 {
            width: clamp(200px, 50vw, 500px);
            height: clamp(200px, 50vw, 500px);
            background: radial-gradient(circle, rgba(37, 99, 235, 0.25) 0%, transparent 70%);
            top: -100px;
            left: -100px;
            animation: orbFloat 8s ease-in-out infinite;
        }

        .hero-orb-2 {
            width: clamp(150px, 40vw, 400px);
            height: clamp(150px, 40vw, 400px);
            background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, transparent 70%);
            bottom: -50px;
            right: -50px;
            animation: orbFloat 10s ease-in-out infinite reverse;
        }

        .hero-orb-3 {
            width: clamp(100px, 30vw, 300px);
            height: clamp(100px, 30vw, 300px);
            background: radial-gradient(circle, rgba(96, 165, 250, 0.15) 0%, transparent 70%);
            top: 50%;
            right: 10%;
            animation: orbFloat 7s ease-in-out infinite 2s;
        }

        @keyframes orbFloat {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(30px, -20px) scale(1.05);
            }

            66% {
                transform: translate(-20px, 15px) scale(0.95);
            }
        }

        .hero-content {
            text-align: center;
            position: relative;
            z-index: 1;
            max-width: 800px;
            width: 100%;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--white-ghost);
            border: 1px solid var(--border);
            border-radius: 100px;
            padding: 0.4rem 1rem;
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--electric-glow);
            margin-bottom: 1.5rem;
            letter-spacing: 0.5px;
            animation: fadeInDown 0.6s ease both;
        }

        .hero-badge-dot {
            width: 6px;
            height: 6px;
            background: var(--gold);
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
            flex-shrink: 0;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.5);
                opacity: 0.7;
            }
        }

        .hero h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2.2rem, 6vw, 5rem);
            font-weight: 800;
            line-height: 1.05;
            margin-bottom: 0.5rem;
            letter-spacing: -2px;
            animation: fadeInUp 0.7s ease 0.1s both;
        }

        .hero-title-highlight {
            background: linear-gradient(135deg, var(--electric-bright) 0%, var(--electric-glow) 50%, var(--gold-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-tagline {
            font-family: 'Syne', sans-serif;
            font-size: clamp(0.75rem, 2vw, 1.1rem);
            font-weight: 500;
            color: var(--gold);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 1.2rem;
            animation: fadeInUp 0.7s ease 0.2s both;
        }

        .hero-subtitle {
            font-size: clamp(0.9rem, 1.8vw, 1.1rem);
            color: var(--white-muted);
            max-width: 560px;
            margin: 0 auto 2.2rem;
            font-weight: 300;
            line-height: 1.8;
            animation: fadeInUp 0.7s ease 0.3s both;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.7s ease 0.4s both;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 1.8rem;
            background: var(--electric);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-primary:hover {
            background: var(--electric-bright);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.45);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 1.8rem;
            background: var(--white-ghost);
            color: var(--white);
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            border: 1px solid var(--border);
            transition: all 0.3s;
            white-space: nowrap;
        }

        .btn-secondary:hover {
            background: rgba(248, 250, 252, 0.12);
            border-color: rgba(248, 250, 252, 0.25);
            transform: translateY(-3px);
        }

        [data-theme="light"] .btn-secondary:hover {
            background: rgba(10, 22, 40, 0.08);
            border-color: rgba(10, 22, 40, 0.2);
        }

        /* ══════════════════════════════════════════
           STATS
        ══════════════════════════════════════════ */
        .stats-banner {
            padding: 2.5rem 1.5rem;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            background: var(--bg-mid);
            transition: var(--transition-theme);
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }

        .stat-box {
            text-align: center;
            padding: 1.2rem;
            position: relative;
        }

        .stat-box+.stat-box::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 1px;
            background: var(--border);
        }

        .stat-number {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            font-weight: 800;
            color: var(--white);
            display: block;
            line-height: 1;
            margin-bottom: 0.4rem;
            letter-spacing: -1px;
        }

        .stat-number span {
            background: linear-gradient(135deg, var(--electric-bright), var(--gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: var(--white-muted);
            font-size: 0.82rem;
            font-weight: 400;
            letter-spacing: 0.3px;
        }

        /* ══════════════════════════════════════════
           SECTION COMMON
        ══════════════════════════════════════════ */
        section {
            width: 100%;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3.5rem;
        }

        .section-eyebrow {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--electric-glow);
            margin-bottom: 1rem;
            display: block;
        }

        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 800;
            color: var(--white);
            letter-spacing: -1px;
            line-height: 1.1;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 0.95rem;
            color: var(--white-muted);
            max-width: 520px;
            margin: 0 auto;
            font-weight: 300;
            line-height: 1.8;
        }

        /* ══════════════════════════════════════════
           FEATURES
        ══════════════════════════════════════════ */
        .features {
            padding: 80px 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5px;
            background: var(--border);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }

        .feature-card {
            background: var(--card-bg);
            padding: 2.2rem 1.8rem;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), transparent);
            opacity: 0;
            transition: opacity 0.4s;
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover {
            background: var(--card-hover);
            transform: scale(1.01);
            z-index: 1;
            box-shadow: var(--shadow-electric);
        }

        .feature-icon-wrap {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(37, 99, 235, 0.15);
            border: 1px solid rgba(37, 99, 235, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 1.3rem;
            transition: all 0.3s;
        }

        .feature-card:hover .feature-icon-wrap {
            background: rgba(37, 99, 235, 0.25);
            border-color: var(--electric);
            transform: scale(1.1);
        }

        .feature-card h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.7rem;
            letter-spacing: -0.3px;
        }

        .feature-card p {
            color: var(--white-muted);
            font-size: 0.88rem;
            line-height: 1.75;
            font-weight: 300;
        }

        /* ══════════════════════════════════════════
           WHY CHOOSE
        ══════════════════════════════════════════ */
        .why-choose {
            padding: 80px 1.5rem;
            background: var(--bg-mid);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            transition: var(--transition-theme);
        }

        .why-inner {
            max-width: 1200px;
            margin: 0 auto;
        }

        .why-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.2rem;
            margin-top: 2.5rem;
        }

        .why-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.8rem 1.8rem 1.8rem 2rem;
            display: flex;
            gap: 1.2rem;
            align-items: flex-start;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .why-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, var(--electric), var(--electric-glow));
            border-radius: 0 2px 2px 0;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .why-card:hover::before {
            opacity: 1;
        }

        .why-card:hover {
            border-color: rgba(37, 99, 235, 0.3);
            background: var(--card-hover);
            transform: translateX(6px);
        }

        .why-icon {
            font-size: 1.7rem;
            flex-shrink: 0;
        }

        .why-card h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.4rem;
            letter-spacing: -0.2px;
        }

        .why-card p {
            color: var(--white-muted);
            font-size: 0.86rem;
            line-height: 1.7;
            font-weight: 300;
        }

        /* ══════════════════════════════════════════
           PROCESS
        ══════════════════════════════════════════ */
        .process {
            padding: 80px 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .process-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            position: relative;
            margin-top: 2.5rem;
        }

        .process-steps::before {
            content: '';
            position: absolute;
            top: 32px;
            left: 12.5%;
            right: 12.5%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--electric), var(--electric-glow), var(--electric), transparent);
            z-index: 0;
        }

        .step-card {
            text-align: center;
            padding: 0 1.2rem;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--bg-mid);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--electric-glow);
            position: relative;
            transition: all 0.3s;
        }

        .step-card:hover .step-number {
            background: var(--electric);
            border-color: var(--electric);
            color: white;
            box-shadow: 0 0 30px rgba(37, 99, 235, 0.4);
        }

        .step-icon {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .step-card h3 {
            font-family: 'Syne', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.6rem;
            letter-spacing: -0.2px;
        }

        .step-card p {
            color: var(--white-muted);
            font-size: 0.83rem;
            line-height: 1.7;
            font-weight: 300;
        }

        /* ══════════════════════════════════════════
           FLEET
        ══════════════════════════════════════════ */
        .fleet-section {
            padding: 80px 1.5rem;
            background: var(--bg-mid);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            transition: var(--transition-theme);
        }

        .fleet-inner {
            max-width: 1200px;
            margin: 0 auto;
        }

        .fleet-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2.5rem;
        }

        .fleet-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.2rem 1.8rem;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
        }

        .fleet-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--electric), var(--electric-glow));
            transform: scaleX(0);
            transition: transform 0.4s ease;
            transform-origin: left;
        }

        .fleet-card:hover::after {
            transform: scaleX(1);
        }

        .fleet-card:hover {
            border-color: rgba(37, 99, 235, 0.3);
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
        }

        .fleet-icon {
            font-size: 2.5rem;
            margin-bottom: 1.2rem;
        }

        .fleet-card h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.8rem;
        }

        .fleet-card p {
            color: var(--white-muted);
            font-size: 0.86rem;
            line-height: 1.75;
            font-weight: 300;
            margin-bottom: 1.5rem;
        }

        .fleet-capacity {
            padding-top: 1.2rem;
            border-top: 1px solid var(--border);
            font-size: 0.83rem;
            color: var(--electric-glow);
            font-weight: 500;
        }

        .coverage-area {
            margin-top: 4rem;
        }

        .coverage-area h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            letter-spacing: -0.5px;
        }

        .coverage-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .coverage-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.8rem;
            text-align: center;
            transition: all 0.3s;
        }

        .coverage-card:hover {
            border-color: rgba(37, 99, 235, 0.3);
            transform: translateY(-4px);
        }

        .coverage-icon {
            font-size: 2rem;
            margin-bottom: 0.8rem;
        }

        .coverage-card strong {
            display: block;
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
        }

        .coverage-card p {
            color: var(--white-muted);
            font-size: 0.85rem;
        }

        /* ══════════════════════════════════════════
           ABOUT
        ══════════════════════════════════════════ */
        .about {
            padding: 80px 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .about-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .about-img-wrap {
            border-radius: var(--radius-lg);
            overflow: hidden;
            position: relative;
            border: 1px solid var(--border);
        }

        .about-img-wrap img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            display: block;
            filter: brightness(0.85) saturate(0.8);
        }

        .about-img-placeholder {
            width: 100%;
            height: 380px;
            background: var(--about-placeholder-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
        }

        .about-img-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.2), transparent);
        }

        .about-badge {
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 130px;
            height: 130px;
            background: var(--electric);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 4px solid var(--bg);
            animation: rotateSlow 15s linear infinite;
        }

        .about-badge-text {
            font-family: 'Syne', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
            line-height: 1;
        }

        .about-badge-label {
            font-size: 0.6rem;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
        }

        @keyframes rotateSlow {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .about-content h2 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.8rem, 3.5vw, 2.5rem);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }

        .about-content p {
            color: var(--white-muted);
            font-size: 0.92rem;
            line-height: 1.9;
            margin-bottom: 1.2rem;
            font-weight: 300;
        }

        .about-features-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
            margin-top: 2rem;
        }

        .about-feature-item {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
        }

        .about-feature-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(37, 99, 235, 0.15);
            border: 1px solid rgba(37, 99, 235, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--electric-glow);
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .about-feature-item h4 {
            font-family: 'Syne', sans-serif;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.15rem;
        }

        .about-feature-item p {
            font-size: 0.78rem;
            color: var(--white-muted);
            margin: 0;
        }

        /* ══════════════════════════════════════════
           CTA
        ══════════════════════════════════════════ */
        .cta {
            padding: 80px 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .cta-inner {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .cta-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--bg-mid) 0%, var(--bg-light) 100%);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            transition: var(--transition-theme);
        }

        .cta-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.2), transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .cta h2 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.8rem, 4vw, 3rem);
            font-weight: 800;
            letter-spacing: -1.5px;
            margin-bottom: 1rem;
            line-height: 1.1;
        }

        .cta p {
            color: var(--white-muted);
            font-size: 0.95rem;
            font-weight: 300;
            margin-bottom: 2.5rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ══════════════════════════════════════════
           FOOTER
        ══════════════════════════════════════════ */
        .footer {
            background: var(--footer-bg);
            border-top: 1px solid var(--border);
            padding: 4rem 1.5rem 2rem;
            transition: var(--transition-theme);
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.5fr repeat(3, 1fr);
            gap: 2.5rem;
            margin-bottom: 3rem;
        }

        .footer-logo-img {
            height: 40px;
            filter: brightness(0) invert(1);
            margin-bottom: 1.2rem;
        }

        [data-theme="light"] .footer-logo-img {
            filter: brightness(0);
            opacity: 0.7;
        }

        .footer-logo-text {
            font-family: 'Syne', sans-serif;
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 0.8rem;
            letter-spacing: -0.3px;
        }

        .footer-section p,
        .footer-section a {
            color: var(--white-muted);
            font-size: 0.85rem;
            line-height: 1.8;
            text-decoration: none;
            display: block;
            margin-bottom: 0.4rem;
            transition: color 0.3s;
            font-weight: 300;
        }

        .footer-section a:hover {
            color: var(--white);
        }

        .footer-section h3 {
            font-family: 'Syne', sans-serif;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--white-muted);
            margin-bottom: 1.3rem;
        }

        .social-links {
            display: flex;
            gap: 0.8rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .social-link {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: var(--white-ghost);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            flex-shrink: 0;
        }

        .social-link:hover {
            background: var(--electric);
            border-color: var(--electric);
            color: white;
            transform: translateY(-3px);
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-bottom-left {
            font-size: 0.8rem;
            color: var(--white-muted);
        }

        .portal-link {
            color: var(--footer-bg) !important;
            font-size: 0.76rem !important;
            text-decoration: none;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .portal-link:hover {
            color: var(--white-muted) !important;
        }

        /* Scroll Top */
        .scroll-top {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 48px;
            height: 48px;
            background: var(--electric);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
            transition: all 0.3s;
            z-index: 998;
        }

        .scroll-top.show {
            display: flex;
        }

        .scroll-top:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.5);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ══════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════ */
        @media (min-width: 769px) and (max-width: 1024px) {
            .navbar-menu {
                gap: 1.5rem;
            }

            .navbar-menu a {
                font-size: 0.82rem;
            }

            .stats-container {
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .why-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .process-steps {
                grid-template-columns: repeat(2, 1fr);
                gap: 2.5rem 1rem;
            }

            .process-steps::before {
                display: none;
            }

            .fleet-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .fleet-card:last-child {
                grid-column: 1 / -1;
                max-width: 400px;
                margin: 0 auto;
                width: 100%;
            }

            .about-container {
                grid-template-columns: 1fr;
                gap: 3rem;
            }

            .about-visual {
                max-width: 600px;
                margin: 0 auto;
                width: 100%;
            }

            .about-badge {
                display: none;
            }

            .about-img-wrap img {
                height: 320px;
            }

            .footer-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }

            .coverage-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .controls-bar {
                top: 0;
                padding: 8px 12px;
            }

            .menu-toggle {
                display: flex;
            }

            .navbar-menu {
                position: fixed;
                top: 0;
                right: -100%;
                width: min(280px, 80vw);
                height: 100vh;
                background: var(--bg-mid);
                border-left: 1px solid var(--border);
                flex-direction: column;
                justify-content: center;
                align-items: flex-start;
                gap: 0;
                padding: 6rem 2rem 3rem;
                transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1000;
                overflow-y: auto;
            }

            .navbar-menu li {
                width: 100%;
                border-bottom: 1px solid var(--border);
            }

            .navbar-menu li:first-child {
                border-top: 1px solid var(--border);
            }

            .navbar-menu.active {
                right: 0;
            }

            .navbar-menu a {
                font-size: 1rem;
                padding: 1rem 0;
                display: block;
                width: 100%;
                color: var(--white-muted);
            }

            .navbar-menu li:last-child {
                border-bottom: none;
            }

            .nav-cta {
                background: none !important;
                padding: 1rem 0 !important;
                border-radius: 0 !important;
                font-weight: 600 !important;
                color: var(--electric-glow) !important;
                box-shadow: none !important;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 0;
            }

            .stat-box {
                padding: 1.5rem 1rem;
                border-bottom: 1px solid var(--border);
            }

            .stat-box:nth-child(odd) {
                border-right: 1px solid var(--border);
            }

            .stat-box+.stat-box::before {
                display: none;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .why-grid {
                grid-template-columns: 1fr;
            }

            .process-steps {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem 1rem;
            }

            .process-steps::before {
                display: none;
            }

            .step-card {
                padding: 0 0.5rem;
            }

            .fleet-grid {
                grid-template-columns: 1fr;
            }

            .coverage-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .about-container {
                grid-template-columns: 1fr;
                gap: 2.5rem;
            }

            .about-badge {
                display: none;
            }

            .about-img-wrap img,
            .about-img-placeholder {
                height: 280px;
            }

            .about-features-list {
                grid-template-columns: 1fr 1fr;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                max-width: 320px;
                justify-content: center;
            }

            .footer-container {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }

            .hero-buttons {
                gap: 0.75rem;
            }
        }

        @media (max-width: 480px) {

            .features,
            .why-choose,
            .process,
            .fleet-section,
            .about,
            .cta {
                padding: 60px 1rem;
            }

            .footer {
                padding: 3rem 1rem 1.5rem;
            }

            .stats-banner {
                padding: 0;
            }

            .hero {
                padding: 100px 1rem 60px;
            }

            .hero h1 {
                letter-spacing: -1px;
            }

            .hero-badge {
                font-size: 0.72rem;
                padding: 0.35rem 0.8rem;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid {
                border-radius: var(--radius);
            }

            .feature-card {
                padding: 1.8rem 1.4rem;
            }

            .process-steps {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .why-card {
                padding: 1.4rem;
            }

            .fleet-card {
                padding: 1.8rem 1.4rem;
            }

            .coverage-grid {
                grid-template-columns: 1fr;
                max-width: 320px;
            }

            .about-features-list {
                grid-template-columns: 1fr;
            }

            .footer-container {
                grid-template-columns: 1fr;
                gap: 1.8rem;
            }

            .footer-bottom {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .scroll-top {
                bottom: 16px;
                right: 16px;
                width: 42px;
                height: 42px;
            }

            .section-header {
                margin-bottom: 2.5rem;
            }
        }

        @media (max-width: 360px) {
            .hero h1 {
                font-size: 2rem;
                letter-spacing: -0.5px;
            }

            .navbar-menu {
                width: 100%;
                right: -100%;
                border-left: none;
                border-bottom: 1px solid var(--border);
            }

            .stat-number {
                font-size: 1.6rem;
            }

            .btn-primary,
            .btn-secondary {
                padding: 0.75rem 1.2rem;
                font-size: 0.85rem;
            }
        }

        @media (max-height: 500px) and (max-width: 900px) and (orientation: landscape) {
            .hero {
                min-height: auto;
                padding: 90px 1.5rem 60px;
            }

            .navbar-menu {
                padding-top: 4rem;
                justify-content: flex-start;
            }
        }

        @media (hover: none) {

            .feature-card:hover,
            .why-card:hover,
            .fleet-card:hover,
            .coverage-card:hover {
                transform: none;
            }

            .btn-primary:hover,
            .btn-secondary:hover,
            .nav-cta:hover {
                transform: none;
            }
        }

        @media print {

            .navbar,
            .scroll-top,
            .hero-orb,
            .cta-glow {
                display: none;
            }

            body {
                background: white;
                color: black;
            }
        }
    </style>
</head>

<body>

    <!-- Overlay for mobile nav -->
    <div class="nav-overlay" id="navOverlay"></div>

    <!-- ══ CONTROLS BAR (Theme + Language) ══ -->
    <div class="controls-bar">
        <!-- Language Toggle -->
        <div class="lang-toggle" role="group" aria-label="Language selector">
            <button class="lang-btn active" id="btnID" onclick="setLang('id')" aria-pressed="true">ID</button>
            <button class="lang-btn" id="btnEN" onclick="setLang('en')" aria-pressed="false">EN</button>
        </div>
        <!-- Theme Toggle -->
        <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()" aria-label="Toggle dark/light mode"
            title="Toggle theme">
            <div class="theme-toggle-knob">
                <span class="theme-icon" id="themeIcon">🌙</span>
            </div>
        </button>
    </div>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="navbar-container">
            <a href="#" class="navbar-logo">
                <img src="assets/img/TSC_page-0001.png" alt="TSC Logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                <div class="logo-placeholder" style="display:none">TSC</div>
                <span class="logo-text">TSC</span>
            </a>
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="#home" data-i18n="nav.home">Home</a></li>
                <li><a href="#features" data-i18n="nav.services">Layanan</a></li>
                <li><a href="#about" data-i18n="nav.about">Tentang</a></li>
                <li><a href="#process" data-i18n="nav.howItWorks">Cara Kerja</a></li>
                <li><a href="#fleet" data-i18n="nav.fleet">Armada</a></li>
                <li><a href="#contact" class="nav-cta" data-i18n="nav.contact">Hubungi Kami</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero" id="home">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
        <div class="hero-content">
            <div class="hero-badge">
                <div class="hero-badge-dot"></div>
                <span data-i18n="hero.badge">Solusi Logistik Terpercaya Indonesia</span>
            </div>
            <p class="hero-tagline">Your Best Logistic Partner</p>
            <h1>PT Tata Sanjaya<br><span class="hero-title-highlight">Cakrawala</span></h1>
            <p class="hero-subtitle" data-i18n="hero.subtitle">Solusi logistik modern yang menggerakkan bisnis Anda —
                dari Jabodetabek hingga seluruh pelosok nusantara.</p>
            <div class="hero-buttons">
                <a href="#contact" class="btn-primary">
                    <i class="fas fa-phone-alt"></i>
                    <span data-i18n="hero.ctaPrimary">Hubungi Kami</span>
                </a>
                <a href="#about" class="btn-secondary">
                    <span data-i18n="hero.ctaSecondary">Pelajari Lebih Lanjut</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="stats-banner">
        <div class="stats-container">
            <div class="stat-box">
                <span class="stat-number"><span>100+</span></span>
                <span class="stat-label" data-i18n="stats.clients">Klien Aktif</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><span>10K+</span></span>
                <span class="stat-label" data-i18n="stats.deliveries">Pengiriman / Bulan</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><span>99.8%</span></span>
                <span class="stat-label" data-i18n="stats.ontime">Ketepatan Waktu</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><span>24/7</span></span>
                <span class="stat-label" data-i18n="stats.support">Customer Support</span>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <div class="section-header reveal">
            <span class="section-eyebrow" data-i18n="features.eyebrow">Apa yang Kami Tawarkan</span>
            <h2 class="section-title" data-i18n="features.title">Layanan Unggulan TSC</h2>
            <p class="section-subtitle" data-i18n="features.subtitle">Solusi logistik lengkap yang disesuaikan dengan
                skala dan kebutuhan bisnis Anda.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card reveal">
                <div class="feature-icon-wrap">📅</div>
                <h3 data-i18n="features.daily.title">Daily Rent</h3>
                <p data-i18n="features.daily.desc">Sewa kendaraan logistik harian dengan fleksibilitas tinggi. Tersedia
                    dari pick-up hingga truk kontainer.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon-wrap">🚛</div>
                <h3 data-i18n="features.regular.title">Reguler</h3>
                <p data-i18n="features.regular.desc">Pengiriman terjadwal dengan rute tetap untuk efisiensi operasional.
                    Hemat biaya hingga 30% dengan kontrak jangka panjang.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon-wrap">📞</div>
                <h3 data-i18n="features.oncall.title">On Call</h3>
                <p data-i18n="features.oncall.desc">Layanan siap pakai 24/7 untuk kebutuhan mendesak. Response time
                    kurang dari 2 jam.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon-wrap">👁️</div>
                <h3 data-i18n="features.visibility.title">Real-time Visibility</h3>
                <p data-i18n="features.visibility.desc">Tracking dan monitoring real-time. Pantau posisi dan status
                    barang melalui aplikasi mobile kapan saja.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon-wrap">🛡️</div>
                <h3 data-i18n="features.reliability.title">Reliability</h3>
                <p data-i18n="features.reliability.desc">Jaminan pengiriman tepat waktu 99.8%. Asuransi penuh untuk
                    setiap pengiriman Anda.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon-wrap">💻</div>
                <h3 data-i18n="features.tech.title">Smart Technology</h3>
                <p data-i18n="features.tech.desc">Sistem digital terintegrasi dengan AI untuk optimasi rute, prediksi
                    waktu, dan pelaporan otomatis.</p>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose">
        <div class="why-inner">
            <div class="section-header reveal">
                <span class="section-eyebrow" data-i18n="why.eyebrow">Keunggulan Kami</span>
                <h2 class="section-title" data-i18n="why.title">Mengapa Memilih TSC?</h2>
                <p class="section-subtitle" data-i18n="why.subtitle">Bukan sekadar jasa pengiriman — kami adalah partner
                    pertumbuhan bisnis Anda.</p>
            </div>
            <div class="why-grid">
                <div class="why-card reveal">
                    <div class="why-icon">🏆</div>
                    <div>
                        <h3 data-i18n="why.exp.title">Pengalaman Terpercaya</h3>
                        <p data-i18n="why.exp.desc">Lebih dari 5 tahun melayani berbagai industri dengan tingkat
                            kepuasan pelanggan 98%.</p>
                    </div>
                </div>
                <div class="why-card reveal">
                    <div class="why-icon">🚀</div>
                    <div>
                        <h3 data-i18n="why.fleet.title">Armada Modern</h3>
                        <p data-i18n="why.fleet.desc">Fleet terawat dengan usia rata-rata kendaraan di bawah 3 tahun dan
                            maintenance rutin terjadwal.</p>
                    </div>
                </div>
                <div class="why-card reveal">
                    <div class="why-icon">💼</div>
                    <div>
                        <h3 data-i18n="why.driver.title">Driver Profesional</h3>
                        <p data-i18n="why.driver.desc">Tim driver terlatih, bersertifikat, dengan track record
                            keselamatan yang excellent.</p>
                    </div>
                </div>
                <div class="why-card reveal">
                    <div class="why-icon">💰</div>
                    <div>
                        <h3 data-i18n="why.price.title">Harga Kompetitif</h3>
                        <p data-i18n="why.price.desc">Tarif transparan tanpa biaya tersembunyi. Berbagai paket hemat
                            untuk kebutuhan reguler Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Process -->
    <section class="process" id="process">
        <div class="section-header reveal">
            <span class="section-eyebrow" data-i18n="process.eyebrow">Proses Sederhana</span>
            <h2 class="section-title" data-i18n="process.title">Cara Kerja TSC</h2>
            <p class="section-subtitle" data-i18n="process.subtitle">Empat langkah mudah untuk memulai perjalanan
                logistik terbaik Anda.</p>
        </div>
        <div class="process-steps">
            <div class="step-card reveal">
                <div class="step-number">01</div>
                <div class="step-icon">📱</div>
                <h3 data-i18n="process.step1.title">Hubungi Kami</h3>
                <p data-i18n="process.step1.desc">Via WhatsApp, telepon, atau website untuk konsultasi kebutuhan
                    logistik Anda</p>
            </div>
            <div class="step-card reveal">
                <div class="step-number">02</div>
                <div class="step-icon">📋</div>
                <h3 data-i18n="process.step2.title">Penawaran Custom</h3>
                <p data-i18n="process.step2.desc">Dapatkan penawaran harga terbaik sesuai volume dan frekuensi
                    pengiriman</p>
            </div>
            <div class="step-card reveal">
                <div class="step-number">03</div>
                <div class="step-icon">✅</div>
                <h3 data-i18n="process.step3.title">Konfirmasi Order</h3>
                <p data-i18n="process.step3.desc">Setujui penawaran dan jadwalkan pick-up sesuai waktu yang Anda
                    inginkan</p>
            </div>
            <div class="step-card reveal">
                <div class="step-number">04</div>
                <div class="step-icon">🚚</div>
                <h3 data-i18n="process.step4.title">Track & Delivery</h3>
                <p data-i18n="process.step4.desc">Monitor pengiriman real-time dan terima barang tepat waktu dengan aman
                </p>
            </div>
        </div>
    </section>

    <!-- Fleet -->
    <section class="fleet-section" id="fleet">
        <div class="fleet-inner">
            <div class="section-header reveal">
                <span class="section-eyebrow" data-i18n="fleet.eyebrow">Armada & Jangkauan</span>
                <h2 class="section-title" data-i18n="fleet.title">Pilihan Armada Lengkap</h2>
                <p class="section-subtitle" data-i18n="fleet.subtitle">Dari pengiriman kecil hingga industri besar —
                    semua ada di TSC.</p>
            </div>
            <div class="fleet-grid">
                <div class="fleet-card reveal">
                    <div class="fleet-icon">🚚</div>
                    <h3 data-i18n="fleet.van.title">Blind Van & Box</h3>
                    <p data-i18n="fleet.van.desc">Ideal untuk pengiriman retail dan e-commerce. Cocok untuk distribusi
                        ke toko dan pelanggan.</p>
                    <div class="fleet-capacity" data-i18n="fleet.van.cap">📦 Kapasitas: 500kg – 1 ton</div>
                </div>
                <div class="fleet-card reveal">
                    <div class="fleet-icon">🚛</div>
                    <h3 data-i18n="fleet.cdd.title">CDD & Truk Engkel</h3>
                    <p data-i18n="fleet.cdd.desc">Untuk kebutuhan pengiriman skala menengah. Sempurna untuk distribusi
                        regional dan antar kota.</p>
                    <div class="fleet-capacity" data-i18n="fleet.cdd.cap">📦 Kapasitas: 2 – 4 ton</div>
                </div>
                <div class="fleet-card reveal">
                    <div class="fleet-icon">🚚</div>
                    <h3 data-i18n="fleet.fuso.title">Fuso & Wing Box</h3>
                    <p data-i18n="fleet.fuso.desc">Armada besar untuk pengiriman skala industri. Dilengkapi GPS tracking
                        dan asuransi penuh.</p>
                    <div class="fleet-capacity" data-i18n="fleet.fuso.cap">📦 Kapasitas: 6 – 20 ton</div>
                </div>
            </div>
            <div class="coverage-area reveal">
                <h3 data-i18n="fleet.coverage.title">Area Jangkauan</h3>
                <div class="coverage-grid">
                    <div class="coverage-card">
                        <div class="coverage-icon">📍</div>
                        <strong>Jabodetabek</strong>
                        <p data-i18n="fleet.coverage.jabodetabek">Jakarta, Bogor, Depok, Tangerang, Bekasi</p>
                    </div>
                    <div class="coverage-card">
                        <div class="coverage-icon">🇮🇩</div>
                        <strong data-i18n="fleet.coverage.national">Seluruh Indonesia</strong>
                        <p data-i18n="fleet.coverage.nationalDesc">Layanan pengiriman ke seluruh nusantara</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About -->
    <section class="about" id="about">
        <div class="about-container">
            <div class="about-visual reveal">
                <div class="about-img-wrap">
                    <img src="assets/img/wp-1.png" alt="TSC Logistics"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <div class="about-img-placeholder" style="display:none">🚛</div>
                    <div class="about-img-overlay"></div>
                </div>
                <div class="about-badge">
                    <span class="about-badge-text">10+</span>
                    <span class="about-badge-label" data-i18n="about.badge">Tahun<br>Pengalaman</span>
                </div>
            </div>
            <div class="about-content reveal">
                <span class="section-eyebrow" data-i18n="about.eyebrow">Tentang Kami</span>
                <h2>
                    <span data-i18n="about.titleLine1">Membangun Kepercayaan</span><br>
                    <span data-i18n="about.titleLine2">Satu Pengiriman</span><br>
                    <span class="hero-title-highlight" data-i18n="about.titleLine3">Sekaligus</span>
                </h2>
                <p data-i18n="about.p1">PT Tata Sanjaya Cakrawala adalah perusahaan logistik terpercaya yang berkomitmen
                    memberikan solusi transportasi dan distribusi terbaik untuk bisnis Anda.</p>
                <p data-i18n="about.p2">Dengan pengalaman lebih dari 10 tahun di industri logistik, kami memahami
                    pentingnya ketepatan waktu, keamanan, dan efisiensi dalam setiap pengiriman. Didukung armada modern
                    dan teknologi terkini.</p>
                <div class="about-features-list">
                    <div class="about-feature-item">
                        <div class="about-feature-icon"><i class="fas fa-certificate"></i></div>
                        <div>
                            <h4 data-i18n="about.feat1.title">Tersertifikasi</h4>
                            <p data-i18n="about.feat1.desc">ISO 9001:2015 certified</p>
                        </div>
                    </div>
                    <div class="about-feature-item">
                        <div class="about-feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <h4 data-i18n="about.feat2.title">Asuransi Penuh</h4>
                            <p data-i18n="about.feat2.desc">Semua pengiriman diasuransikan</p>
                        </div>
                    </div>
                    <div class="about-feature-item">
                        <div class="about-feature-icon"><i class="fas fa-users"></i></div>
                        <div>
                            <h4 data-i18n="about.feat3.title">Tim Profesional</h4>
                            <p data-i18n="about.feat3.desc">Dikelola staff terlatih</p>
                        </div>
                    </div>
                    <div class="about-feature-item">
                        <div class="about-feature-icon"><i class="fas fa-chart-line"></i></div>
                        <div>
                            <h4 data-i18n="about.feat4.title">Pertumbuhan Stabil</h4>
                            <p data-i18n="about.feat4.desc">Growth 40% year over year</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <div class="cta-bg"></div>
        <div class="cta-glow"></div>
        <div class="cta-inner">
            <span class="section-eyebrow" style="margin-bottom:1.5rem" data-i18n="cta.eyebrow">Mulai Sekarang</span>
            <h2 data-i18n="cta.title">Siap Tingkatkan Efisiensi<br>Logistik Anda?</h2>
            <p data-i18n="cta.subtitle">Bergabunglah dengan 500+ perusahaan yang telah mempercayai TSC sebagai partner
                logistik mereka.</p>
            <div class="cta-buttons">
                <a href="https://wa.me/6281310879577" target="_blank" class="btn-primary">
                    <i class="fab fa-whatsapp"></i>
                    <span data-i18n="cta.whatsapp">Chat via WhatsApp</span>
                </a>
                <a href="mailto:tatasanjayacakrawala@gmail.com" class="btn-secondary">
                    <i class="fas fa-envelope"></i>
                    <span data-i18n="cta.email">Kirim Email</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="footer-container">
            <div class="footer-section">
                <img src="assets/img/TSC_page-0001.png" alt="TSC Logo" class="footer-logo-img"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                <div class="footer-logo-text" style="display:none">TSC</div>
                <p data-i18n="footer.tagline" style="margin-bottom:1rem">Your Best Logistic Partner — solusi logistik
                    terpercaya dengan teknologi modern.</p>
                <div class="social-links">
                    <a href="https://instagram.com/tatasanjayacakrawala" target="_blank" class="social-link"
                        aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://id.linkedin.com/in/tata-sanjaya-cakrawala-b61542323" target="_blank"
                        class="social-link" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <a href="https://wa.me/6281310879577" class="social-link" aria-label="WhatsApp"><i
                            class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3 data-i18n="footer.services">Layanan</h3>
                <a href="#features" data-i18n="features.daily.title">Daily Rent</a>
                <a href="#features" data-i18n="footer.regularDelivery">Reguler Delivery</a>
                <a href="#features" data-i18n="footer.onCallService">On Call Service</a>
                <a href="#features" data-i18n="footer.realtime">Real-time Tracking</a>
                <a href="#features" data-i18n="features.tech.title">Smart Technology</a>
            </div>
            <div class="footer-section">
                <h3 data-i18n="footer.company">Perusahaan</h3>
                <a href="#about" data-i18n="nav.about">Tentang Kami</a>
                <a href="#fleet" data-i18n="footer.fleetLink">Armada & Jangkauan</a>
                <a href="#" data-i18n="footer.career">Karir</a>
                <a href="#" data-i18n="footer.blog">Blog & News</a>
                <a href="#" data-i18n="footer.faq">FAQ</a>
            </div>
            <div class="footer-section">
                <h3 data-i18n="footer.contact">Kontak</h3>
                <a href="https://instagram.com/tatasanjayacakrawala" target="_blank"><i class="fab fa-instagram"
                        style="width:18px"></i> @tatasanjayacakrawala</a>
                <a href="tel:+6281310879577"><i class="fas fa-phone" style="width:18px"></i> +62 813 1087 9577</a>
                <a href="mailto:tatasanjayacakrawala@gmail.com"><i class="fas fa-envelope" style="width:18px"></i>
                    tatasanjayacakrawala@gmail.com</a>
                <p><i class="fas fa-map-marker-alt" style="width:18px"></i> Bekasi, Jawa Barat</p>
                <p><i class="fas fa-clock" style="width:18px"></i> <span data-i18n="footer.hours">24 Jam Nonstop</span>
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            <span class="footer-bottom-left">&copy; 2025 PT Tata Sanjaya Cakrawala. All Rights Reserved.</span>
            <a href="app.php/login" class="portal-link">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" />
                </svg>
                System Portal
            </a>
        </div>
    </footer>

    <!-- Scroll Top -->
    <button class="scroll-top" id="scrollTop" aria-label="Scroll to top"><i class="fas fa-arrow-up"></i></button>

    <script>
        /* ══════════════════════════════════════════
           TRANSLATIONS
        ══════════════════════════════════════════ */
        const translations = {
            id: {
                'nav.home': 'Home',
                'nav.services': 'Layanan',
                'nav.about': 'Tentang',
                'nav.howItWorks': 'Cara Kerja',
                'nav.fleet': 'Armada',
                'nav.contact': 'Hubungi Kami',
                'hero.badge': 'Solusi Logistik Terpercaya Indonesia',
                'hero.subtitle': 'Solusi logistik modern yang menggerakkan bisnis Anda — dari Jabodetabek hingga seluruh pelosok nusantara.',
                'hero.ctaPrimary': 'Hubungi Kami',
                'hero.ctaSecondary': 'Pelajari Lebih Lanjut',
                'stats.clients': 'Klien Aktif',
                'stats.deliveries': 'Pengiriman / Bulan',
                'stats.ontime': 'Ketepatan Waktu',
                'stats.support': 'Customer Support',
                'features.eyebrow': 'Apa yang Kami Tawarkan',
                'features.title': 'Layanan Unggulan TSC',
                'features.subtitle': 'Solusi logistik lengkap yang disesuaikan dengan skala dan kebutuhan bisnis Anda.',
                'features.daily.title': 'Daily Rent',
                'features.daily.desc': 'Sewa kendaraan logistik harian dengan fleksibilitas tinggi. Tersedia dari pick-up hingga truk kontainer.',
                'features.regular.title': 'Reguler',
                'features.regular.desc': 'Pengiriman terjadwal dengan rute tetap untuk efisiensi operasional. Hemat biaya hingga 30% dengan kontrak jangka panjang.',
                'features.oncall.title': 'On Call',
                'features.oncall.desc': 'Layanan siap pakai 24/7 untuk kebutuhan mendesak. Response time kurang dari 2 jam.',
                'features.visibility.title': 'Real-time Visibility',
                'features.visibility.desc': 'Tracking dan monitoring real-time. Pantau posisi dan status barang melalui aplikasi mobile kapan saja.',
                'features.reliability.title': 'Reliability',
                'features.reliability.desc': 'Jaminan pengiriman tepat waktu 99.8%. Asuransi penuh untuk setiap pengiriman Anda.',
                'features.tech.title': 'Smart Technology',
                'features.tech.desc': 'Sistem digital terintegrasi dengan AI untuk optimasi rute, prediksi waktu, dan pelaporan otomatis.',
                'why.eyebrow': 'Keunggulan Kami',
                'why.title': 'Mengapa Memilih TSC?',
                'why.subtitle': 'Bukan sekadar jasa pengiriman — kami adalah partner pertumbuhan bisnis Anda.',
                'why.exp.title': 'Pengalaman Terpercaya',
                'why.exp.desc': 'Lebih dari 5 tahun melayani berbagai industri dengan tingkat kepuasan pelanggan 98%.',
                'why.fleet.title': 'Armada Modern',
                'why.fleet.desc': 'Fleet terawat dengan usia rata-rata kendaraan di bawah 3 tahun dan maintenance rutin terjadwal.',
                'why.driver.title': 'Driver Profesional',
                'why.driver.desc': 'Tim driver terlatih, bersertifikat, dengan track record keselamatan yang excellent.',
                'why.price.title': 'Harga Kompetitif',
                'why.price.desc': 'Tarif transparan tanpa biaya tersembunyi. Berbagai paket hemat untuk kebutuhan reguler Anda.',
                'process.eyebrow': 'Proses Sederhana',
                'process.title': 'Cara Kerja TSC',
                'process.subtitle': 'Empat langkah mudah untuk memulai perjalanan logistik terbaik Anda.',
                'process.step1.title': 'Hubungi Kami',
                'process.step1.desc': 'Via WhatsApp, telepon, atau website untuk konsultasi kebutuhan logistik Anda',
                'process.step2.title': 'Penawaran Custom',
                'process.step2.desc': 'Dapatkan penawaran harga terbaik sesuai volume dan frekuensi pengiriman',
                'process.step3.title': 'Konfirmasi Order',
                'process.step3.desc': 'Setujui penawaran dan jadwalkan pick-up sesuai waktu yang Anda inginkan',
                'process.step4.title': 'Track & Delivery',
                'process.step4.desc': 'Monitor pengiriman real-time dan terima barang tepat waktu dengan aman',
                'fleet.eyebrow': 'Armada & Jangkauan',
                'fleet.title': 'Pilihan Armada Lengkap',
                'fleet.subtitle': 'Dari pengiriman kecil hingga industri besar — semua ada di TSC.',
                'fleet.van.title': 'Blind Van & Box',
                'fleet.van.desc': 'Ideal untuk pengiriman retail dan e-commerce. Cocok untuk distribusi ke toko dan pelanggan.',
                'fleet.van.cap': '📦 Kapasitas: 500kg – 1 ton',
                'fleet.cdd.title': 'CDD & Truk Engkel',
                'fleet.cdd.desc': 'Untuk kebutuhan pengiriman skala menengah. Sempurna untuk distribusi regional dan antar kota.',
                'fleet.cdd.cap': '📦 Kapasitas: 2 – 4 ton',
                'fleet.fuso.title': 'Fuso & Wing Box',
                'fleet.fuso.desc': 'Armada besar untuk pengiriman skala industri. Dilengkapi GPS tracking dan asuransi penuh.',
                'fleet.fuso.cap': '📦 Kapasitas: 6 – 20 ton',
                'fleet.coverage.title': 'Area Jangkauan',
                'fleet.coverage.jabodetabek': 'Jakarta, Bogor, Depok, Tangerang, Bekasi',
                'fleet.coverage.national': 'Seluruh Indonesia',
                'fleet.coverage.nationalDesc': 'Layanan pengiriman ke seluruh nusantara',
                'about.eyebrow': 'Tentang Kami',
                'about.badge': 'Tahun\nPengalaman',
                'about.titleLine1': 'Membangun Kepercayaan',
                'about.titleLine2': 'Satu Pengiriman',
                'about.titleLine3': 'Sekaligus',
                'about.p1': 'PT Tata Sanjaya Cakrawala adalah perusahaan logistik terpercaya yang berkomitmen memberikan solusi transportasi dan distribusi terbaik untuk bisnis Anda.',
                'about.p2': 'Dengan pengalaman lebih dari 10 tahun di industri logistik, kami memahami pentingnya ketepatan waktu, keamanan, dan efisiensi dalam setiap pengiriman. Didukung armada modern dan teknologi terkini.',
                'about.feat1.title': 'Tersertifikasi',
                'about.feat1.desc': 'ISO 9001:2015 certified',
                'about.feat2.title': 'Asuransi Penuh',
                'about.feat2.desc': 'Semua pengiriman diasuransikan',
                'about.feat3.title': 'Tim Profesional',
                'about.feat3.desc': 'Dikelola staff terlatih',
                'about.feat4.title': 'Pertumbuhan Stabil',
                'about.feat4.desc': 'Growth 40% year over year',
                'cta.eyebrow': 'Mulai Sekarang',
                'cta.title': 'Siap Tingkatkan Efisiensi<br>Logistik Anda?',
                'cta.subtitle': 'Bergabunglah dengan 500+ perusahaan yang telah mempercayai TSC sebagai partner logistik mereka.',
                'cta.whatsapp': 'Chat via WhatsApp',
                'cta.email': 'Kirim Email',
                'footer.tagline': 'Your Best Logistic Partner — solusi logistik terpercaya dengan teknologi modern.',
                'footer.services': 'Layanan',
                'footer.regularDelivery': 'Reguler Delivery',
                'footer.onCallService': 'On Call Service',
                'footer.realtime': 'Real-time Tracking',
                'footer.company': 'Perusahaan',
                'footer.fleetLink': 'Armada & Jangkauan',
                'footer.career': 'Karir',
                'footer.blog': 'Blog & News',
                'footer.faq': 'FAQ',
                'footer.contact': 'Kontak',
                'footer.hours': '24 Jam Nonstop',
            },
            en: {
                'nav.home': 'Home',
                'nav.services': 'Services',
                'nav.about': 'About',
                'nav.howItWorks': 'How It Works',
                'nav.fleet': 'Fleet',
                'nav.contact': 'Contact Us',
                'hero.badge': 'Indonesia\'s Trusted Logistics Solution',
                'hero.subtitle': 'Modern logistics solutions that drive your business — from Greater Jakarta to every corner of Indonesia.',
                'hero.ctaPrimary': 'Contact Us',
                'hero.ctaSecondary': 'Learn More',
                'stats.clients': 'Active Clients',
                'stats.deliveries': 'Deliveries / Month',
                'stats.ontime': 'On-time Rate',
                'stats.support': 'Customer Support',
                'features.eyebrow': 'What We Offer',
                'features.title': 'TSC Premium Services',
                'features.subtitle': 'Complete logistics solutions tailored to your business scale and needs.',
                'features.daily.title': 'Daily Rent',
                'features.daily.desc': 'Flexible daily vehicle rental for logistics. Available from pickup trucks to container trucks.',
                'features.regular.title': 'Regular',
                'features.regular.desc': 'Scheduled delivery with fixed routes for operational efficiency. Save up to 30% with long-term contracts.',
                'features.oncall.title': 'On Call',
                'features.oncall.desc': '24/7 ready-to-use service for urgent needs. Response time under 2 hours.',
                'features.visibility.title': 'Real-time Visibility',
                'features.visibility.desc': 'Real-time tracking and monitoring. Track goods position and status via mobile app anytime.',
                'features.reliability.title': 'Reliability',
                'features.reliability.desc': '99.8% on-time delivery guarantee. Full insurance for every shipment.',
                'features.tech.title': 'Smart Technology',
                'features.tech.desc': 'AI-integrated digital system for route optimization, time prediction, and automated reporting.',
                'why.eyebrow': 'Our Advantages',
                'why.title': 'Why Choose TSC?',
                'why.subtitle': 'Not just a delivery service — we are your business growth partner.',
                'why.exp.title': 'Trusted Experience',
                'why.exp.desc': 'Over 5 years serving various industries with a 98% customer satisfaction rate.',
                'why.fleet.title': 'Modern Fleet',
                'why.fleet.desc': 'Well-maintained fleet with an average vehicle age under 3 years and scheduled routine maintenance.',
                'why.driver.title': 'Professional Drivers',
                'why.driver.desc': 'Trained, certified drivers with an excellent safety track record.',
                'why.price.title': 'Competitive Pricing',
                'why.price.desc': 'Transparent rates with no hidden fees. Various savings packages for your regular needs.',
                'process.eyebrow': 'Simple Process',
                'process.title': 'How TSC Works',
                'process.subtitle': 'Four easy steps to start your best logistics journey.',
                'process.step1.title': 'Contact Us',
                'process.step1.desc': 'Via WhatsApp, phone, or website for logistics needs consultation',
                'process.step2.title': 'Custom Quote',
                'process.step2.desc': 'Get the best pricing based on your delivery volume and frequency',
                'process.step3.title': 'Confirm Order',
                'process.step3.desc': 'Approve the quote and schedule a pickup at your preferred time',
                'process.step4.title': 'Track & Delivery',
                'process.step4.desc': 'Monitor shipment in real-time and receive goods safely on time',
                'fleet.eyebrow': 'Fleet & Coverage',
                'fleet.title': 'Complete Fleet Options',
                'fleet.subtitle': 'From small deliveries to large industries — all available at TSC.',
                'fleet.van.title': 'Blind Van & Box',
                'fleet.van.desc': 'Ideal for retail and e-commerce delivery. Perfect for distribution to stores and customers.',
                'fleet.van.cap': '📦 Capacity: 500kg – 1 ton',
                'fleet.cdd.title': 'CDD & Single Axle Truck',
                'fleet.cdd.desc': 'For medium-scale delivery needs. Perfect for regional and inter-city distribution.',
                'fleet.cdd.cap': '📦 Capacity: 2 – 4 tons',
                'fleet.fuso.title': 'Fuso & Wing Box',
                'fleet.fuso.desc': 'Large fleet for industrial-scale delivery. Equipped with GPS tracking and full insurance.',
                'fleet.fuso.cap': '📦 Capacity: 6 – 20 tons',
                'fleet.coverage.title': 'Coverage Area',
                'fleet.coverage.jabodetabek': 'Jakarta, Bogor, Depok, Tangerang, Bekasi',
                'fleet.coverage.national': 'All of Indonesia',
                'fleet.coverage.nationalDesc': 'Delivery service across the entire archipelago',
                'about.eyebrow': 'About Us',
                'about.badge': 'Years of\nExperience',
                'about.titleLine1': 'Building Trust',
                'about.titleLine2': 'One Delivery',
                'about.titleLine3': 'At a Time',
                'about.p1': 'PT Tata Sanjaya Cakrawala is a trusted logistics company committed to providing the best transportation and distribution solutions for your business.',
                'about.p2': 'With over 10 years of experience in the logistics industry, we understand the importance of punctuality, safety, and efficiency in every shipment. Supported by a modern fleet and the latest technology.',
                'about.feat1.title': 'Certified',
                'about.feat1.desc': 'ISO 9001:2015 certified',
                'about.feat2.title': 'Full Insurance',
                'about.feat2.desc': 'All shipments are insured',
                'about.feat3.title': 'Professional Team',
                'about.feat3.desc': 'Managed by trained staff',
                'about.feat4.title': 'Stable Growth',
                'about.feat4.desc': 'Growth 40% year over year',
                'cta.eyebrow': 'Start Now',
                'cta.title': 'Ready to Boost Your<br>Logistics Efficiency?',
                'cta.subtitle': 'Join 500+ companies that have trusted TSC as their logistics partner.',
                'cta.whatsapp': 'Chat via WhatsApp',
                'cta.email': 'Send Email',
                'footer.tagline': 'Your Best Logistic Partner — trusted logistics with modern technology.',
                'footer.services': 'Services',
                'footer.regularDelivery': 'Regular Delivery',
                'footer.onCallService': 'On Call Service',
                'footer.realtime': 'Real-time Tracking',
                'footer.company': 'Company',
                'footer.fleetLink': 'Fleet & Coverage',
                'footer.career': 'Careers',
                'footer.blog': 'Blog & News',
                'footer.faq': 'FAQ',
                'footer.contact': 'Contact',
                'footer.hours': '24 Hours Nonstop',
            }
        };

        let currentLang = 'id';
        let currentTheme = 'dark';

        /* ── Language System ── */
        function setLang(lang) {
            currentLang = lang;
            const t = translations[lang];

            document.querySelectorAll('[data-i18n]').forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (t[key] !== undefined) {
                    if (el.innerHTML.includes('<br>') || t[key].includes('<br>')) {
                        el.innerHTML = t[key];
                    } else {
                        el.textContent = t[key];
                    }
                }
            });

            // Update active button
            document.getElementById('btnID').classList.toggle('active', lang === 'id');
            document.getElementById('btnEN').classList.toggle('active', lang === 'en');
            document.getElementById('btnID').setAttribute('aria-pressed', lang === 'id');
            document.getElementById('btnEN').setAttribute('aria-pressed', lang === 'en');

            // Update html lang
            document.documentElement.lang = lang;

            // Save preference
            try { localStorage.setItem('tsc-lang', lang); } catch (e) { }
        }

        /* ── Theme System ── */
        function toggleTheme() {
            currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(currentTheme);
        }

        function applyTheme(theme) {
            currentTheme = theme;
            document.documentElement.setAttribute('data-theme', theme);
            document.getElementById('themeIcon').textContent = theme === 'dark' ? '🌙' : '☀️';
            document.getElementById('themeToggle').setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
            try { localStorage.setItem('tsc-theme', theme); } catch (e) { }
        }

        /* ── Init on load ── */
        (function init() {
            try {
                const savedTheme = localStorage.getItem('tsc-theme');
                if (savedTheme) applyTheme(savedTheme);
                const savedLang = localStorage.getItem('tsc-lang');
                if (savedLang) setLang(savedLang);
            } catch (e) { }
        })();

        /* ══════════════════════════════════════════
           MOBILE MENU
        ══════════════════════════════════════════ */
        const menuToggle = document.getElementById('menuToggle');
        const navbarMenu = document.getElementById('navbarMenu');
        const navOverlay = document.getElementById('navOverlay');

        function openMenu() {
            menuToggle.classList.add('active');
            navbarMenu.classList.add('active');
            navOverlay.classList.add('active');
            menuToggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
            menuToggle.classList.remove('active');
            navbarMenu.classList.remove('active');
            navOverlay.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        menuToggle.addEventListener('click', () => navbarMenu.classList.contains('active') ? closeMenu() : openMenu());
        document.querySelectorAll('.navbar-menu a').forEach(link => link.addEventListener('click', closeMenu));
        navOverlay.addEventListener('click', closeMenu);
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && navbarMenu.classList.contains('active')) closeMenu(); });

        /* ── Navbar Scroll ── */
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
            document.getElementById('scrollTop').classList.toggle('show', window.scrollY > 300);
        });

        /* ── Scroll Top ── */
        document.getElementById('scrollTop').addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

        /* ── Smooth Scroll ── */
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const href = a.getAttribute('href');
                if (href === '#') return;
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const top = target.getBoundingClientRect().top + window.scrollY - 80;
                    window.scrollTo({ top, behavior: 'smooth' });
                }
            });
        });

        /* ── Reveal on Scroll ── */
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    setTimeout(() => entry.target.classList.add('visible'), i * 80);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/app-inventori/sw.js', {
                    scope: '/app-inventori/'
                })
                    .then(reg => console.log('SW registered:', reg.scope))
                    .catch(err => console.log('SW error:', err));
            });
        }
    </script>
</body>

</html>
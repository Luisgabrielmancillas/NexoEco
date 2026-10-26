<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NexoEco - El marketplace de los microemprendedores</title>
    @vite('resources/css/design-system.css')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg: var(--color-background);
            --ink: var(--color-text-primary);
            --cream: var(--color-background);
            --accent: var(--color-primary);
            --accent2: var(--color-primary);
            --muted: var(--color-text-secondary);
            --card: var(--color-surface);
            --border: var(--color-border);
            --seller: var(--color-primary);
            --buyer: var(--color-primary);
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Comic Sans', sans-serif;
            background: var(--bg);
            color: var(--ink);
            overflow-x: hidden;
        }

        h1, h2, h3, h4 {
            font-family: 'Comic Sans', sans-serif;
        }

        /* ── NAV ── */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            background: var(--color-surface);
            border-bottom: 1.5px solid var(--border);
            padding: 0 2rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: box-shadow 0.2s;
        }
        nav.scrolled { box-shadow: 0 4px 24px rgb(var(--color-text-primary-rgb) / 0.06); }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Comic Sans', sans-serif;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--ink);
            text-decoration: none;
        }
        .nav-logo-dot {
            width: 10px; height: 10px;
            background: var(--accent);
            border-radius: 50%;
            display: inline-block;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }
        .nav-links a {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--ink); }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 100px; font-size: 0.875rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s; border: none; font-family: 'DM Sans', sans-serif; }
        .btn-ghost { background: transparent; color: var(--ink); border: 1.5px solid var(--border); }
        .btn-ghost:hover { border-color: var(--ink); }
        .btn-ink { background: var(--color-primary); color: var(--color-on-primary); }
        .btn-ink:hover { background: var(--color-primary-hover); transform: translateY(-1px); box-shadow: 0 6px 16px rgb(var(--color-text-primary-rgb) / 0.15); }
        .btn-accent { background: var(--accent); color: var(--color-on-primary); }
        .btn-accent:hover { background: var(--color-primary-hover); transform: translateY(-1px); box-shadow: 0 6px 16px rgb(var(--color-text-primary-rgb) / 0.3); }
        .btn-blue { background: var(--accent2); color: var(--color-on-primary); }
        .btn-blue:hover { background: var(--color-primary-hover); transform: translateY(-1px); box-shadow: 0 6px 16px rgb(var(--color-text-primary-rgb) / 0.3); }
        .btn-lg { padding: 0.875rem 2rem; font-size: 1rem; }

        /* ── HERO ── */
        .hero {
            padding-top: 64px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .hero-top {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        /* Left side — buyers */
        .hero-buyer {
            background: var(--cream);
            padding: 5rem 3rem 4rem 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            border-right: 1.5px solid var(--border);
        }
        .hero-buyer::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 20% 80%, rgb(var(--color-primary-rgb) / 0.06) 0%, transparent 60%);
            pointer-events: none;
        }

        /* Right side — sellers */
        .hero-seller {
            background: var(--color-primary-soft);
            padding: 5rem 4rem 4rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .hero-seller::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 80% 20%, rgb(var(--color-primary-rgb) / 0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            padding: 0.35rem 0.875rem;
            border-radius: 100px;
        }
        .tag-buyer { background: rgb(var(--color-primary-rgb) / 0.1); color: var(--accent2); }
        .tag-seller { background: var(--color-primary-subtle); color: var(--color-primary-text); }

        .hero-title {
            font-size: clamp(2rem, 3.5vw, 3rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.25rem;
        }
        .hero-buyer .hero-title { color: var(--ink); }
        .hero-seller .hero-title { color: var(--color-text-primary); }

        .hero-sub {
            font-size: 1.0625rem;
            line-height: 1.65;
            margin-bottom: 2.5rem;
            max-width: 420px;
        }
        .hero-buyer .hero-sub { color: var(--muted); }
        .hero-seller .hero-sub { color: var(--color-text-secondary); }

        .hero-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }

        /* Product preview cards */
        .preview-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-top: 2.5rem;
        }

        .preview-card {
            background: var(--color-surface);
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .preview-card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px rgb(var(--color-text-primary-rgb) / 0.08); }

        .preview-img {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }
        .preview-img-1 { background: var(--color-primary-soft); }
        .preview-img-2 { background: var(--color-primary-soft); }
        .preview-img-3 { background: var(--color-success-soft); }
        .preview-img-4 { background: var(--color-primary-soft); }
        .preview-img-5 { background: var(--color-primary-soft); }
        .preview-img-6 { background: var(--color-danger-soft); }

        .preview-info { padding: 0.625rem 0.75rem; }
        .preview-name { font-size: 0.75rem; font-weight: 600; color: var(--ink); }
        .preview-shop { font-size: 0.625rem; color: var(--muted); margin-top: 2px; }
        .preview-price { font-size: 0.75rem; font-weight: 700; color: var(--accent2); margin-top: 4px; }

        /* Seller dashboard preview */
        .dash-preview {
            margin-top: 2.5rem;
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 1.25rem;
        }
        .dash-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgb(var(--color-surface-rgb) / 0.08);
        }
        .dash-title { font-size: 0.8rem; font-weight: 600; color: var(--color-text-secondary); font-family: 'Syne', sans-serif; }
        .dash-live { display: flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; color: var(--color-success-text); }
        .live-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--color-success); animation: pulse 2s infinite; }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .dash-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .dash-stat {
            background: var(--color-background-secondary);
            border-radius: 10px;
            padding: 0.75rem;
        }
        .dash-stat-val { font-size: 1.125rem; font-weight: 700; color: var(--color-primary-text); font-family: 'Syne', sans-serif; }
        .dash-stat-lbl { font-size: 0.65rem; color: var(--color-text-secondary); margin-top: 2px; }

        .dash-orders { display: flex; flex-direction: column; gap: 0.5rem; }
        .dash-order {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--color-background-secondary);
            border-radius: 8px;
            padding: 0.625rem 0.875rem;
        }
        .order-info { font-size: 0.725rem; color: var(--color-text-secondary); }
        .order-name { font-size: 0.75rem; font-weight: 600; color: var(--color-text-primary); }
        .order-badge { font-size: 0.65rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 100px; }
        .badge-new { background: var(--color-primary-soft); color: var(--color-primary-text); }
        .badge-done { background: var(--color-success-soft); color: var(--color-success-text); }

        /* ── DIVIDER BANNER ── */
        .banner {
            background: var(--color-primary-soft);
            padding: 1.25rem 2rem;
            overflow: hidden;
            position: relative;
        }
        .banner-track {
            display: flex;
            gap: 3rem;
            white-space: nowrap;
            animation: marquee 20s linear infinite;
        }
        .banner-item {
            font-family: 'Comic Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--color-primary-text);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
        }
        .banner-sep { opacity: 0.4; }

        @keyframes marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        /* ── HOW IT WORKS ── */
        #como-funciona {
            padding: 6rem 2rem;
            background: var(--cream);
        }
        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.75rem;
        }
        .section-title {
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 1rem;
            line-height: 1.15;
        }
        .section-sub {
            font-size: 1.0625rem;
            color: var(--muted);
            max-width: 520px;
            line-height: 1.65;
        }

        .how-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            max-width: 1100px;
            margin: 4rem auto 0;
        }

        .how-col-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-family: 'Comic Sans', sans-serif;
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 2rem;
        }
        .how-col-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .icon-buyer { background: rgb(var(--color-primary-rgb) / 0.1); color: var(--accent2); }
        .icon-seller { background: rgb(var(--color-primary-rgb) / 0.1); color: var(--accent); }

        .steps { display: flex; flex-direction: column; gap: 1.5rem; }
        .step {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }
        .step-num {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            flex-shrink: 0;
            font-family: 'Comic Sans', sans-serif;
        }
        .num-buyer { background: rgb(var(--color-primary-rgb) / 0.12); color: var(--accent2); }
        .num-seller { background: rgb(var(--color-primary-rgb) / 0.12); color: var(--accent); }

        .step-text h4 { font-size: 0.9375rem; font-weight: 600; margin-bottom: 0.25rem; }
        .step-text p { font-size: 0.875rem; color: var(--muted); line-height: 1.6; }

        /* ── FEATURES ── */
        #caracteristicas {
            padding: 6rem 2rem;
            background: var(--color-surface);
        }
        #caracteristicas .section-label { color: var(--color-primary-text); }
        #caracteristicas .section-title { color: var(--color-text-primary); }
        #caracteristicas .section-sub { color: var(--color-text-secondary); }

        .feat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5px;
            margin-top: 4rem;
            background: var(--color-border);
            border: 1.5px solid rgb(var(--color-surface-rgb) / 0.08);
            border-radius: 20px;
            overflow: hidden;
        }
        .feat-card {
            background: var(--color-surface);
            padding: 2rem;
            transition: background 0.2s;
        }
        .feat-card:hover { background: var(--color-primary-soft); }

        .feat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1.25rem;
        }
        .feat-icon-primary { background: rgb(var(--color-primary-rgb) / 0.15); color: var(--color-primary-text); }
        .feat-icon-blue { background: rgb(var(--color-primary-rgb) / 0.15); color: var(--color-primary-text); }
        .feat-icon-green { background: var(--color-primary-soft); color: var(--color-primary-text); }

        .feat-card h3 { font-size: 1rem; font-weight: 700; color: var(--color-text-primary); margin-bottom: 0.5rem; }
        .feat-card p { font-size: 0.875rem; color: var(--color-text-secondary); line-height: 1.65; }
        .feat-tag {
            display: inline-block;
            margin-bottom: 0.75rem;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.2rem 0.5rem;
            border-radius: 100px;
        }
        .ft-seller { background: rgb(var(--color-primary-rgb) / 0.15); color: var(--color-primary-text); }
        .ft-buyer { background: rgb(var(--color-primary-rgb) / 0.15); color: var(--color-primary-text); }
        .ft-all { background: var(--color-background-secondary); color: var(--color-text-secondary); }

        /* ── STATS ── */
        .stats {
            padding: 5rem 2rem;
            background: var(--color-primary-soft);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            max-width: 900px;
            margin: 0 auto;
        }
        .stat-item { text-align: center; }
        .stat-val {
            font-family: 'Comic Sans', sans-serif;
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--color-primary-text);
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        .stat-lbl { font-size: 0.875rem; color: var(--color-text-secondary); font-weight: 500; }

        /* ── TESTIMONIALS ── */
        #testimonios {
            padding: 6rem 2rem;
            background: var(--bg);
        }
        .testi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            max-width: 1100px;
            margin: 3rem auto 0;
        }
        .testi-card {
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: 18px;
            padding: 1.75rem;
            transition: all 0.2s;
        }
        .testi-card:hover {
            border-color: var(--accent);
            box-shadow: 0 12px 32px rgb(var(--color-text-primary-rgb) / 0.08);
            transform: translateY(-2px);
        }
        .testi-role {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.2rem 0.6rem;
            border-radius: 100px;
            display: inline-block;
            margin-bottom: 1rem;
        }
        .role-seller { background: rgb(var(--color-primary-rgb) / 0.1); color: var(--accent); }
        .role-buyer { background: rgb(var(--color-primary-rgb) / 0.1); color: var(--accent2); }

        .testi-text {
            font-size: 0.9375rem;
            line-height: 1.7;
            color: var(--color-text-primary);
            margin-bottom: 1.25rem;
            font-style: italic;
        }
        .testi-author {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .testi-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .testi-name { font-size: 0.875rem; font-weight: 600; }
        .testi-business { font-size: 0.75rem; color: var(--muted); }
        .stars { color: var(--color-warning-text); font-size: 0.75rem; letter-spacing: 1px; margin-top: 0.25rem; }

        /* ── PRICING ── */
        #precios {
            padding: 6rem 2rem;
            background: var(--cream);
        }

        .pricing-note {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgb(var(--color-primary-rgb) / 0.08);
            color: var(--accent2);
            font-size: 0.8125rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 100px;
            margin-bottom: 1.5rem;
            border: 1px solid rgb(var(--color-primary-rgb) / 0.15);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            max-width: 1000px;
            margin: 3rem auto 0;
        }

        .price-card {
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: 24px;
            padding: 2rem;
            position: relative;
            transition: all 0.2s;
        }
        .price-card:hover { box-shadow: 0 16px 40px rgb(var(--color-text-primary-rgb) / 0.07); transform: translateY(-3px); }
        .price-card.popular {
            border-color: var(--accent);
            box-shadow: 0 20px 48px rgb(var(--color-text-primary-rgb) / 0.12);
        }

        .popular-badge {
            position: absolute;
            top: -1px;
            right: 1.5rem;
            background: var(--accent);
            color: var(--color-on-primary);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.3rem 0.875rem;
            border-radius: 0 0 10px 10px;
        }

        .price-name { font-family: 'Comic Sans', sans-serif; font-size: 1.125rem; font-weight: 700; margin-bottom: 0.25rem; }
        .price-desc { font-size: 0.8125rem; color: var(--muted); margin-bottom: 1.5rem; }
        .price-val { font-family: 'Comic Sans', sans-serif; font-size: 2.5rem; font-weight: 800; margin-bottom: 1.5rem; }
        .price-val span { font-size: 1rem; font-weight: 400; color: var(--muted); }
        .price-features { list-style: none; display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 2rem; }
        .price-features li {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 0.875rem;
        }
        .price-features li i { color: var(--accent); font-size: 0.75rem; width: 14px; }
        .price-features li.off { color: var(--muted); }
        .price-features li.off i { color: var(--color-text-secondary); }

        /* ── CTA ── */
        .cta-section {
            padding: 7rem 2rem;
            background: var(--ink);
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .cta-section::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgb(var(--color-primary-rgb) / 0.07);
            top: -200px; left: -100px;
            pointer-events: none;
        }
        .cta-section::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgb(var(--color-primary-rgb) / 0.07);
            bottom: -150px; right: -100px;
            pointer-events: none;
        }

        .cta-section .section-title { color: var(--color-on-primary); font-size: clamp(2rem, 4vw, 3rem); max-width: 700px; margin: 0 auto 1.25rem; }
        .cta-section .section-sub { color: var(--color-dark-muted); margin: 0 auto 3rem; }

        .cta-duo {
            display: flex;
            align-items: stretch;
            gap: 1.5rem;
            justify-content: center;
            max-width: 560px;
            margin: 0 auto;
        }
        .cta-card {
            flex: 1;
            background: rgb(var(--color-surface-rgb) / 0.05);
            border: 1.5px solid rgb(var(--color-surface-rgb) / 0.1);
            border-radius: 20px;
            padding: 1.75rem 1.5rem;
            text-align: center;
            transition: all 0.2s;
        }
        .cta-card:hover { background: rgb(var(--color-surface-rgb) / 0.08); }
        .cta-card-icon { font-size: 1.75rem; margin-bottom: 0.875rem; }
        .cta-card-title { font-family: 'Comic Sans', sans-serif; font-size: 1rem; font-weight: 700; color: var(--color-on-primary); margin-bottom: 0.375rem; }
        .cta-card-sub { font-size: 0.8125rem; color: var(--color-dark-muted); margin-bottom: 1.25rem; }

        /* Container */
        .container { max-width: 1100px; margin: 0 auto; }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate { animation: fadeUp 0.6s ease both; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* Responsive */
        @media (max-width: 900px) {
            .hero-top { grid-template-columns: 1fr; }
            .hero-buyer { border-right: none; border-bottom: 1.5px solid var(--border); padding: 4rem 2rem 3rem; }
            .hero-seller { padding: 3rem 2rem 4rem;  background: var(--color-primary-soft); }
            .how-grid { grid-template-columns: 1fr; gap: 2.5rem; }
            .feat-grid { grid-template-columns: 1fr 1fr;  background: var(--color-border); }
            .testi-grid { grid-template-columns: 1fr; max-width: 480px; }
            .pricing-grid { grid-template-columns: 1fr; max-width: 400px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .preview-grid { grid-template-columns: repeat(2, 1fr); }
            .dash-stats { grid-template-columns: repeat(2, 1fr); }
            .cta-duo { flex-direction: column; }
        }
        @media (max-width: 600px) {
            nav { padding: 0 1rem;  background: var(--color-surface); }
            .nav-links { display: none; }
            .feat-grid { grid-template-columns: 1fr;  background: var(--color-border); }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
</head>
<body class="nexo-landing">

<!-- NAV -->
<nav id="navbar">
    <a href="#" class="nav-logo">
        <span class="nav-logo-dot"></span>
        NexoEco
    </a>
    <ul class="nav-links">
        <li><a href="#como-funciona">Cómo funciona</a></li>
        <li><a href="#caracteristicas">Características</a></li>
        <li><a href="#precios">Precios</a></li>
        <li><a href="#testimonios">Testimonios</a></li>
    </ul>
    <div class="nav-actions">
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-ink">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="btn btn-accent">Registrarse <i class="fas fa-arrow-right" style="font-size:0.75rem"></i></a>
            @endauth
        @endif
    </div>
</nav>

<!-- HERO — SPLIT -->
<section class="hero">
    <div class="hero-top">

        <!-- COMPRADOR -->
        <div class="hero-buyer">
            <span class="hero-tag tag-buyer"><i class="fas fa-shopping-bag"></i> Para compradores</span>
            <h1 class="hero-title">Descubre tiendas únicas de emprendedores reales</h1>
            <p class="hero-sub">Explora miles de productos auténticos de microemprendedores. Apoya el comercio local y encuentra lo que buscas en un solo lugar.</p>
            <div class="hero-actions">
                <a href="{{ route('register') }}" class="btn btn-blue btn-lg">
                    <i class="fas fa-search"></i> Explorar tiendas
                </a>
                <a href="#como-funciona" class="btn btn-ghost btn-lg">Cómo comprar</a>
            </div>

            <!-- Product preview -->
            <div class="preview-grid">
                <div class="preview-card">
                    <div class="preview-img preview-img-1">🧴</div>
                    <div class="preview-info">
                        <div class="preview-name">Crema natural</div>
                        <div class="preview-shop">🏪 Naturalia</div>
                        <div class="preview-price">$180</div>
                    </div>
                </div>
                <div class="preview-card">
                    <div class="preview-img preview-img-2">👜</div>
                    <div class="preview-info">
                        <div class="preview-name">Bolsa tejida</div>
                        <div class="preview-shop">🏪 ArtMex</div>
                        <div class="preview-price">$350</div>
                    </div>
                </div>
                <div class="preview-card">
                    <div class="preview-img preview-img-3">🌿</div>
                    <div class="preview-info">
                        <div class="preview-name">Té orgánico</div>
                        <div class="preview-shop">🏪 TéVerde</div>
                        <div class="preview-price">$120</div>
                    </div>
                </div>
                <div class="preview-card">
                    <div class="preview-img preview-img-4">🕯️</div>
                    <div class="preview-info">
                        <div class="preview-name">Vela artesanal</div>
                        <div class="preview-shop">🏪 LuzPropia</div>
                        <div class="preview-price">$95</div>
                    </div>
                </div>
                <div class="preview-card">
                    <div class="preview-img preview-img-5">🎨</div>
                    <div class="preview-info">
                        <div class="preview-name">Print digital</div>
                        <div class="preview-shop">🏪 EstudioK</div>
                        <div class="preview-price">$200</div>
                    </div>
                </div>
                <div class="preview-card">
                    <div class="preview-img preview-img-6">🧁</div>
                    <div class="preview-info">
                        <div class="preview-name">Caja repostería</div>
                        <div class="preview-shop">🏪 DulceFab</div>
                        <div class="preview-price">$280</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VENDEDOR -->
        <div class="hero-seller">
            <span class="hero-tag tag-seller"><i class="fas fa-store"></i> Para vendedores</span>
            <h1 class="hero-title">Lanza tu tienda virtual y vende desde hoy</h1>
            <p class="hero-sub">Crea tu microtienda, carga tus productos y empieza a vender a miles de compradores registrados. Sin conocimientos técnicos.</p>
            <div class="hero-actions">
                <a href="{{ route('vendedor.register') }}" class="btn btn-accent btn-lg">
                    <i class="fas fa-store"></i> Crear mi tienda
                </a>
                <a href="#precios" class="btn btn-lg" style="border:1.5px solid var(--color-primary);color:var(--color-primary-text);background:transparent">Ver planes</a>
            </div>

            <!-- Dashboard preview -->
            <div class="dash-preview">
                <div class="dash-header">
                    <span class="dash-title">Mi Tienda — Dashboard</span>
                    <span class="dash-live"><span class="live-dot"></span>En vivo</span>
                </div>
                <div class="dash-stats">
                    <div class="dash-stat">
                        <div class="dash-stat-val">$4,280</div>
                        <div class="dash-stat-lbl">Ventas este mes</div>
                    </div>
                    <div class="dash-stat">
                        <div class="dash-stat-val">38</div>
                        <div class="dash-stat-lbl">Pedidos</div>
                    </div>
                    <div class="dash-stat">
                        <div class="dash-stat-val">124</div>
                        <div class="dash-stat-lbl">Visitas hoy</div>
                    </div>
                </div>
                <div class="dash-orders">
                    <div class="dash-order">
                        <div>
                            <div class="order-name">Pedido #1042 — María L.</div>
                            <div class="order-info">Crema natural x2 · hace 3 min</div>
                        </div>
                        <span class="order-badge badge-new">Nuevo</span>
                    </div>
                    <div class="dash-order">
                        <div>
                            <div class="order-name">Pedido #1041 — Carlos R.</div>
                            <div class="order-info">Bolsa tejida x1 · hace 18 min</div>
                        </div>
                        <span class="order-badge badge-done">Enviado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MARQUEE BANNER -->
<div class="banner">
    <div class="banner-track">
        <span class="banner-item">🛍️ Compra gratis <span class="banner-sep">·</span></span>
        <span class="banner-item">🏪 Miles de tiendas <span class="banner-sep">·</span></span>
        <span class="banner-item">🚀 Crea tu tienda hoy <span class="banner-sep">·</span></span>
        <span class="banner-item">💳 Pagos seguros <span class="banner-sep">·</span></span>
        <span class="banner-item">📦 Envíos automatizados <span class="banner-sep">·</span></span>
        <span class="banner-item">🌎 Manzanillo <span class="banner-sep">·</span></span>
        <span class="banner-item">✅ Sin comisiones ocultas <span class="banner-sep">·</span></span>
        <!-- duplicate for infinite loop -->
        <span class="banner-item">🛍️ Compra gratis <span class="banner-sep">·</span></span>
        <span class="banner-item">🏪 Miles de tiendas <span class="banner-sep">·</span></span>
        <span class="banner-item">🚀 Crea tu tienda hoy <span class="banner-sep">·</span></span>
        <span class="banner-item">💳 Pagos seguros <span class="banner-sep">·</span></span>
        <span class="banner-item">📦 Envíos automatizados <span class="banner-sep">·</span></span>
        <span class="banner-item">🌎 Manzanillo <span class="banner-sep">·</span></span>
        <span class="banner-item">✅ Sin comisiones ocultas <span class="banner-sep">·</span></span>
    </div>
</div>

<!-- HOW IT WORKS -->
<section id="como-funciona">
    <div class="container">
        <div class="section-label">Cómo funciona</div>
        <h2 class="section-title">Una plataforma, dos experiencias</h2>
        <p class="section-sub">Ya seas emprendedor o comprador, NexoEco tiene el flujo ideal para ti. Simple, rápido y sin complicaciones.</p>

        <div class="how-grid">
            <!-- Compradores -->
            <div>
                <div class="how-col-title">
                    <div class="how-col-icon icon-buyer"><i class="fas fa-shopping-cart"></i></div>
                    Para compradores — siempre gratis
                </div>
                <div class="steps">
                    <div class="step">
                        <div class="step-num num-buyer">1</div>
                        <div class="step-text">
                            <h4>Regístrate gratis</h4>
                            <p>Crea tu cuenta de comprador en segundos, sin tarjeta de crédito ni costo alguno.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-num num-buyer">2</div>
                        <div class="step-text">
                            <h4>Explora tiendas y productos</h4>
                            <p>Navega por cientos de microtiendas de emprendedores. Filtra por categoría, precio o ubicación.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-num num-buyer">3</div>
                        <div class="step-text">
                            <h4>Compra con seguridad</h4>
                            <p>Paga de forma segura y sigue el estado de tu pedido en tiempo real hasta que llegue a tus manos.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendedores -->
            <div>
                <div class="how-col-title">
                    <div class="how-col-icon icon-seller"><i class="fas fa-store-alt"></i></div>
                    Para vendedores — elige tu plan
                </div>
                <div class="steps">
                    <div class="step">
                        <div class="step-num num-seller">1</div>
                        <div class="step-text">
                            <h4>Crea tu tienda virtual</h4>
                            <p>Elige el nombre de tu tienda, personaliza tu perfil y empieza a cargar productos en minutos.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-num num-seller">2</div>
                        <div class="step-text">
                            <h4>Gestiona inventario y pedidos</h4>
                            <p>Recibe notificaciones de nuevos pedidos, controla tu stock y coordina envíos desde un panel sencillo.</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-num num-seller">3</div>
                        <div class="step-text">
                            <h4>Cobra y haz crecer tu negocio</h4>
                            <p>Recibe pagos directamente. Accede a reportes de ventas y crece apoyado por miles de compradores en la plataforma.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section id="caracteristicas">
    <div class="container">
        <div class="section-label">Características</div>
        <h2 class="section-title">Todo lo que necesitas en un solo lugar</h2>
        <p class="section-sub">Funcionalidades diseñadas tanto para quien compra como para quien vende.</p>

        <div class="feat-grid">
            <div class="feat-card">
                <span class="feat-tag ft-buyer">Para compradores</span>
                <div class="feat-icon feat-icon-blue"><i class="fas fa-search"></i></div>
                <h3>Búsqueda inteligente</h3>
                <p>Encuentra exactamente lo que buscas con filtros por categoría, precio, ubicación y valoraciones.</p>
            </div>
            <div class="feat-card">
                <span class="feat-tag ft-seller">Para vendedores</span>
                <div class="feat-icon feat-icon-primary"><i class="fas fa-cube"></i></div>
                <h3>Gestión de productos</h3>
                <p>Agrega fotos, variantes, stock y precios. Organiza tu catálogo de forma profesional sin complicaciones.</p>
            </div>
            <div class="feat-card">
                <span class="feat-tag ft-all">Para todos</span>
                <div class="feat-icon feat-icon-green"><i class="fas fa-shield-alt"></i></div>
                <h3>Pagos seguros</h3>
                <p>Transacciones protegidas con cifrado de extremo a extremo. Compradores y vendedores siempre protegidos.</p>
            </div>
            <div class="feat-card">
                <span class="feat-tag ft-seller">Para vendedores</span>
                <div class="feat-icon feat-icon-primary"><i class="fas fa-chart-line"></i></div>
                <h3>Reportes en tiempo real</h3>
                <p>Visualiza tus ingresos, tendencias de venta y productos más populares desde tu dashboard.</p>
            </div>
            <div class="feat-card">
                <span class="feat-tag ft-buyer">Para compradores</span>
                <div class="feat-icon feat-icon-blue"><i class="fas fa-truck"></i></div>
                <h3>Seguimiento de pedidos</h3>
                <p>Sigue tu compra en tiempo real desde que el vendedor la procesa hasta que llega a tu puerta.</p>
            </div>
            <div class="feat-card">
                <span class="feat-tag ft-seller">Para vendedores</span>
                <div class="feat-icon feat-icon-primary"><i class="fas fa-mobile-alt"></i></div>
                <h3>Gestión desde el celular</h3>
                <p>Administra tu tienda, responde pedidos y revisa estadísticas desde cualquier dispositivo.</p>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<div class="stats">
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-val">+10K</div>
            <div class="stat-lbl">Tiendas activas</div>
        </div>
        <div class="stat-item">
            <div class="stat-val">+80K</div>
            <div class="stat-lbl">Compradores registrados</div>
        </div>
        <div class="stat-item">
            <div class="stat-val">15</div>
            <div class="stat-lbl">Países en Latam</div>
        </div>
        <div class="stat-item">
            <div class="stat-val">98%</div>
            <div class="stat-lbl">Satisfacción</div>
        </div>
    </div>
</div>

<!-- TESTIMONIALS -->
<section id="testimonios">
    <div class="container">
        <div class="section-label">Testimonios</div>
        <h2 class="section-title">Lo que dicen compradores y vendedores</h2>
        <p class="section-sub">Personas reales que ya están aprovechando NexoEco, desde ambos lados del mostrador.</p>

        <div class="testi-grid">
            <div class="testi-card">
                <span class="testi-role role-seller">Vendedora</span>
                <p class="testi-text">"Nunca imaginé que abrir mi tienda fuera tan fácil. En una tarde ya tenía mis productos cargados y a los dos días llegó mi primera venta."</p>
                <div class="testi-author">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" class="testi-avatar" alt="María José">
                    <div>
                        <div class="testi-name">María José</div>
                        <div class="testi-business">Artesanías Jalisco</div>
                        <div class="stars">★★★★★</div>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <span class="testi-role role-buyer">Comprador</span>
                <p class="testi-text">"Me encanta poder descubrir tiendas de emprendedores que nunca encontraría en otro lado. Los productos son únicos y el proceso de compra es muy sencillo."</p>
                <div class="testi-author">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" class="testi-avatar" alt="Carlos López">
                    <div>
                        <div class="testi-name">Carlos López</div>
                        <div class="testi-business">Comprador frecuente</div>
                        <div class="stars">★★★★★</div>
                    </div>
                </div>
            </div>
            <div class="testi-card">
                <span class="testi-role role-seller">Vendedora</span>
                <p class="testi-text">"El dashboard de ventas es clarísimo. Puedo ver mis ingresos, los pedidos pendientes y las visitas a mi tienda todo en tiempo real."</p>
                <div class="testi-author">
                    <img src="https://randomuser.me/api/portraits/women/68.jpg" class="testi-avatar" alt="Ana García">
                    <div>
                        <div class="testi-name">Ana García</div>
                        <div class="testi-business">Cosméticos naturales</div>
                        <div class="stars">★★★★★</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PRICING -->
<section id="precios">
    <div class="container">
        <div class="section-label">Precios</div>
        <h2 class="section-title">Planes solo para vendedores</h2>

        <div class="pricing-note">
            <i class="fas fa-info-circle"></i>
            Registrarse y comprar en NexoEco es siempre <strong>100% gratuito</strong> para compradores
        </div>

        <p class="section-sub" style="margin-top:0.5rem">Elige el plan que mejor se adapte al tamaño de tu emprendimiento. Sin contratos, cancela cuando quieras.</p>

        <div class="pricing-grid">
            <!-- Básico -->
            <div class="price-card">
                <div class="price-name">Básico</div>
                <div class="price-desc">Para empezar a vender</div>
                <div class="price-val">$0 <span>/mes</span></div>
                <ul class="price-features">
                    <li><i class="fas fa-check"></i> Hasta 10 productos</li>
                    <li><i class="fas fa-check"></i> Pagos integrados</li>
                    <li><i class="fas fa-check"></i> Panel de pedidos</li>
                    <li><i class="fas fa-check"></i> Soporte por email</li>
                    <li class="off"><i class="fas fa-times"></i> Reportes avanzados</li>
                    <li class="off"><i class="fas fa-times"></i> Tienda personalizada</li>
                </ul>
                <a href="{{ route('vendedor.register') }}" class="btn btn-ghost" style="width:100%;justify-content:center">Comenzar gratis</a>
            </div>

            <!-- Profesional -->
            <div class="price-card popular">
                <div class="popular-badge">Más popular</div>
                <div class="price-name">Profesional</div>
                <div class="price-desc">Para negocios en crecimiento</div>
                <div class="price-val">$29 <span>/mes</span></div>
                <ul class="price-features">
                    <li><i class="fas fa-check"></i> Productos ilimitados</li>
                    <li><i class="fas fa-check"></i> Reportes avanzados</li>
                    <li><i class="fas fa-check"></i> Tienda personalizable</li>
                    <li><i class="fas fa-check"></i> Soporte 24/7</li>
                    <li><i class="fas fa-check"></i> Acceso a API</li>
                    <li><i class="fas fa-check"></i> Gestión de envíos</li>
                </ul>
                <a href="{{ route('vendedor.register') }}" class="btn btn-accent" style="width:100%;justify-content:center">Prueba 14 días gratis</a>
            </div>

            <!-- Empresarial -->
            <div class="price-card">
                <div class="price-name">Empresarial</div>
                <div class="price-desc">Para grandes operaciones</div>
                <div class="price-val">$99 <span>/mes</span></div>
                <ul class="price-features">
                    <li><i class="fas fa-check"></i> Todo el plan Pro</li>
                    <li><i class="fas fa-check"></i> Múltiples usuarios</li>
                    <li><i class="fas fa-check"></i> Personalización total</li>
                    <li><i class="fas fa-check"></i> Gerente dedicado</li>
                    <li><i class="fas fa-check"></i> SLA garantizado</li>
                    <li><i class="fas fa-check"></i> Integraciones custom</li>
                </ul>
                <a href="#" class="btn btn-ghost" style="width:100%;justify-content:center">Contactar ventas</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="section-label" style="color:var(--color-on-primary)">Únete hoy</div>
    <h2 class="section-title">¿Para qué lado del mostrador estás tú?</h2>
    <p class="section-sub">Sea cual sea tu rol, NexoEco tiene un lugar para ti. Únete a la comunidad de microemprendedores y compradores de Latinoamérica.</p>

    <div class="cta-duo">
        <div class="cta-card">
            <div class="cta-card-icon">🛍️</div>
            <div class="cta-card-title">Soy comprador</div>
            <div class="cta-card-sub">Registro gratis, sin costos</div>
            <a href="{{ route('register') }}" class="btn btn-blue" style="width:100%;justify-content:center">Explorar tiendas</a>
        </div>
        <div class="cta-card">
            <div class="cta-card-icon">🏪</div>
            <div class="cta-card-title">Quiero vender</div>
            <div class="cta-card-sub">14 días gratis, sin tarjeta</div>
            <a href="{{ route('vendedor.register') }}" class="btn btn-accent" style="width:100%;justify-content:center">Crear mi tienda</a>
        </div>
    </div>
</section>

<script>
    // Navbar scroll shadow
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 10);
    });

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Simple scroll reveal
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.feat-card, .testi-card, .price-card, .step').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
</script>

</body>
</html>

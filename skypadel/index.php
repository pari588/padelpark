<?php
/**
 * Sky Padel India - Premium Client Portal
 * Design: Midnight Court Club - Cinematic Luxury Sports Aesthetic
 * Features: Lenis Smooth Scroll, Parallax, GSAP Animations, 3D Effects
 */
require_once __DIR__ . '/core/config.php';

$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_lead'])) {
    $db = getDB();
    $prefix = DB_PREFIX;

    $clientName = trim($_POST['clientName'] ?? '');
    $clientEmail = trim($_POST['clientEmail'] ?? '');
    $clientPhone = trim($_POST['clientPhone'] ?? '');
    $clientCompany = trim($_POST['clientCompany'] ?? '');
    $siteCity = trim($_POST['siteCity'] ?? '');
    $siteState = trim($_POST['siteState'] ?? '');
    $courtRequirement = trim($_POST['courtRequirement'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($clientName) || empty($clientPhone)) {
        $error = 'Name and phone number are required.';
    } else {
        $year = date('Y');
        $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM {$prefix}sky_padel_lead WHERE YEAR(created) = ?");
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $nextNum = intval($result['cnt']) + 1;
        $leadNo = 'SPL-' . $year . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $leadDate = date('Y-m-d');
        $leadSource = 'Website';
        $leadStatus = 'New';

        $stmt = $db->prepare("INSERT INTO {$prefix}sky_padel_lead
                              (leadNo, leadDate, clientName, clientEmail, clientPhone, clientCompany,
                               siteCity, siteState, courtRequirement, leadSource, leadStatus, notes)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssss",
            $leadNo, $leadDate, $clientName, $clientEmail, $clientPhone, $clientCompany,
            $siteCity, $siteState, $courtRequirement, $leadSource, $leadStatus, $notes);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = 'Failed to submit inquiry. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Premium Padel Court Construction</title>
    <meta name="description" content="Sky Padel India designs, manufactures and installs premium padel courts. 1200+ courts worldwide, 5-year warranty.">

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Premium Typography: Clash Display + Cabinet Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&f[]=cabinet-grotesk@400,500,700&display=swap" rel="stylesheet">

    <!-- Lenis Smooth Scroll -->
    <script src="https://unpkg.com/lenis@1.1.14/dist/lenis.min.js"></script>

    <!-- GSAP for animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <style>
        /* =====================================================
           MIDNIGHT COURT CLUB - DESIGN SYSTEM
           Premium Padel Court Experience
        ===================================================== */

        :root {
            /* Noir Foundation */
            --noir-deep: #030508;
            --noir-base: #070b12;
            --noir-elevated: #0d1420;
            --noir-surface: #131c2e;
            --noir-glass: rgba(13, 20, 32, 0.85);

            /* Court Blue - Electric */
            --court-blue: #0088cc;
            --court-blue-bright: #00a8e8;
            --court-blue-glow: rgba(0, 136, 204, 0.5);
            --court-blue-subtle: rgba(0, 136, 204, 0.15);

            /* Turf Green - Victory */
            --turf-green: #10B981;
            --turf-green-bright: #34D399;
            --turf-green-glow: rgba(16, 185, 129, 0.5);

            /* Ball Yellow - Energy */
            --ball-yellow: #FBBF24;
            --ball-yellow-bright: #FDE68A;
            --ball-yellow-glow: rgba(251, 191, 36, 0.6);
            --ball-gradient: linear-gradient(135deg, #FDE68A 0%, #FBBF24 50%, #F59E0B 100%);

            /* Text Hierarchy */
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --text-accent: var(--ball-yellow);

            /* Typography */
            --font-display: 'Clash Display', 'Bebas Neue', sans-serif;
            --font-body: 'Cabinet Grotesk', 'Space Grotesk', sans-serif;

            /* Glass Effects */
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-shine: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);

            /* Timing */
            --ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1);
            --ease-out-back: cubic-bezier(0.34, 1.56, 0.64, 1);
            --ease-smooth: cubic-bezier(0.25, 0.1, 0.25, 1);
        }

        /* =====================================================
           BASE RESET & SMOOTH SCROLL
        ===================================================== */

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html.lenis, html.lenis body {
            height: auto;
        }

        .lenis.lenis-smooth {
            scroll-behavior: auto !important;
        }

        .lenis.lenis-smooth [data-lenis-prevent] {
            overscroll-behavior: contain;
        }

        .lenis.lenis-stopped {
            overflow: hidden;
        }

        .lenis.lenis-smooth iframe {
            pointer-events: none;
        }

        html {
            font-size: 16px;
        }

        body {
            font-family: var(--font-body);
            background: var(--noir-deep);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        ::selection {
            background: var(--court-blue);
            color: white;
        }

        /* =====================================================
           CUSTOM CURSOR
        ===================================================== */

        .cursor {
            width: 20px;
            height: 20px;
            border: 2px solid var(--ball-yellow);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.15s var(--ease-out-expo),
                        opacity 0.15s var(--ease-out-expo),
                        background 0.15s var(--ease-out-expo);
            mix-blend-mode: difference;
        }

        .cursor.hover {
            transform: scale(2);
            background: var(--ball-yellow);
            border-color: var(--ball-yellow);
        }

        .cursor-dot {
            width: 6px;
            height: 6px;
            background: var(--ball-yellow);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
        }

        /* =====================================================
           LOADING SCREEN
        ===================================================== */

        .loader {
            position: fixed;
            inset: 0;
            background: var(--noir-deep);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 40px;
        }

        .loader-court {
            width: 120px;
            height: 80px;
            position: relative;
            border: 3px solid var(--court-blue);
            border-radius: 4px;
        }

        .loader-court::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--court-blue);
            transform: translateX(-50%);
        }

        .loader-court::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--court-blue);
            transform: translateY(-50%);
        }

        .loader-ball {
            width: 20px;
            height: 20px;
            background: var(--ball-gradient);
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 30px var(--ball-yellow-glow);
            animation: ballBounce 1.5s ease-in-out infinite;
        }

        @keyframes ballBounce {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            25% { transform: translate(200%, -200%) scale(0.8); }
            50% { transform: translate(-50%, -50%) scale(1); }
            75% { transform: translate(-200%, 100%) scale(0.8); }
        }

        .loader-text {
            font-family: var(--font-display);
            font-size: 0.9rem;
            letter-spacing: 0.3em;
            color: var(--text-secondary);
            text-transform: uppercase;
        }

        .loader-progress {
            width: 200px;
            height: 2px;
            background: var(--noir-surface);
            border-radius: 2px;
            overflow: hidden;
        }

        .loader-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--court-blue), var(--turf-green), var(--ball-yellow));
            width: 0%;
            animation: loadProgress 2s var(--ease-out-expo) forwards;
        }

        @keyframes loadProgress {
            to { width: 100%; }
        }

        /* =====================================================
           NAVIGATION
        ===================================================== */

        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 24px 48px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.5s var(--ease-out-expo);
        }

        .nav.scrolled {
            padding: 16px 48px;
            background: var(--noir-glass);
            backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--glass-border);
        }

        .nav-logo {
            height: 44px;
            transition: transform 0.4s var(--ease-out-expo);
        }

        .nav-logo:hover {
            transform: scale(1.05);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link {
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-secondary);
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 100px;
            transition: all 0.3s var(--ease-out-expo);
            position: relative;
        }

        .nav-link:hover {
            color: var(--text-primary);
        }

        .nav-link.primary {
            background: var(--ball-gradient);
            color: var(--noir-deep);
            font-weight: 600;
        }

        .nav-link.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px var(--ball-yellow-glow);
        }

        /* =====================================================
           HERO SECTION - CINEMATIC PARALLAX
        ===================================================== */

        .hero {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        /* Parallax Background Layers */
        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .parallax-layer {
            position: absolute;
            inset: 0;
            will-change: transform;
        }

        /* Deep space gradient */
        .parallax-gradient {
            background:
                radial-gradient(ellipse 100% 100% at 20% 80%, rgba(0, 136, 204, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse 80% 80% at 80% 20%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse 60% 60% at 50% 100%, rgba(251, 191, 36, 0.08) 0%, transparent 40%),
                var(--noir-deep);
        }

        /* Court lines pattern - abstract */
        .parallax-court-lines {
            opacity: 0.04;
            background-image:
                linear-gradient(90deg, var(--text-primary) 1px, transparent 1px),
                linear-gradient(0deg, var(--text-primary) 1px, transparent 1px);
            background-size: 100px 100px;
            transform: perspective(1000px) rotateX(60deg) translateY(100px);
            transform-origin: center bottom;
        }

        /* Animated grid */
        .parallax-grid {
            background-image:
                linear-gradient(rgba(0, 136, 204, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 136, 204, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: gridPulse 10s ease-in-out infinite;
        }

        @keyframes gridPulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* Stadium light beams */
        .light-beam {
            position: absolute;
            width: 300px;
            height: 150vh;
            background: linear-gradient(180deg,
                rgba(255, 255, 255, 0.03) 0%,
                transparent 100%);
            transform-origin: top center;
            pointer-events: none;
        }

        .light-beam-1 {
            left: 10%;
            top: -50%;
            transform: rotate(-15deg);
            animation: beamSway 8s ease-in-out infinite;
        }

        .light-beam-2 {
            right: 15%;
            top: -50%;
            transform: rotate(20deg);
            animation: beamSway 10s ease-in-out infinite reverse;
        }

        @keyframes beamSway {
            0%, 100% { transform: rotate(-15deg); }
            50% { transform: rotate(-10deg); }
        }

        /* Floating Particles */
        .particles {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--ball-yellow);
            border-radius: 50%;
            opacity: 0;
            animation: particleFloat 15s linear infinite;
        }

        @keyframes particleFloat {
            0% {
                opacity: 0;
                transform: translateY(100vh) scale(0);
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                opacity: 0;
                transform: translateY(-100vh) scale(1);
            }
        }

        /* Hero Content */
        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 60px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .hero-text {
            padding-top: 80px;
        }

        /* Tagline Badge */
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            background: var(--noir-glass);
            border: 1px solid var(--glass-border);
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 32px;
            backdrop-filter: blur(10px);
            opacity: 0;
            transform: translateY(20px);
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            background: var(--ball-yellow);
            border-radius: 50%;
            animation: dotPulse 2s ease-in-out infinite;
            box-shadow: 0 0 15px var(--ball-yellow-glow);
        }

        @keyframes dotPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        /* Main Headline */
        .hero-title {
            font-family: var(--font-display);
            font-size: clamp(3.5rem, 8vw, 7rem);
            font-weight: 600;
            line-height: 0.95;
            letter-spacing: -0.02em;
            margin-bottom: 28px;
        }

        .title-line {
            display: block;
            overflow: hidden;
        }

        .title-line-inner {
            display: block;
            transform: translateY(100%);
        }

        .title-accent {
            background: linear-gradient(135deg,
                var(--ball-yellow-bright) 0%,
                var(--ball-yellow) 40%,
                var(--turf-green-bright) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .title-outline {
            -webkit-text-stroke: 1px var(--text-secondary);
            -webkit-text-fill-color: transparent;
        }

        /* Subtitle */
        .hero-subtitle {
            font-size: 1.2rem;
            font-weight: 400;
            color: var(--text-secondary);
            max-width: 480px;
            line-height: 1.7;
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(20px);
        }

        /* Stats Row */
        .hero-stats {
            display: flex;
            gap: 48px;
            opacity: 0;
            transform: translateY(20px);
        }

        .stat-item {
            position: relative;
        }

        .stat-item::after {
            content: '';
            position: absolute;
            right: -24px;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 50px;
            background: linear-gradient(180deg, transparent, var(--glass-border), transparent);
        }

        .stat-item:last-child::after {
            display: none;
        }

        .stat-value {
            font-family: var(--font-display);
            font-size: 3.5rem;
            font-weight: 600;
            line-height: 1;
            background: linear-gradient(135deg, var(--text-primary), var(--court-blue-bright));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-top: 8px;
        }

        /* =====================================================
           3D COURT VISUALIZATION
        ===================================================== */

        .hero-visual {
            position: relative;
            perspective: 1000px;
        }

        .court-3d-wrapper {
            position: relative;
            transform-style: preserve-3d;
            transform: rotateX(10deg) rotateY(-5deg);
            transition: transform 0.5s var(--ease-out-expo);
        }

        .court-3d-wrapper:hover {
            transform: rotateX(5deg) rotateY(0deg);
        }

        /* Glow behind court */
        .court-glow {
            position: absolute;
            width: 120%;
            height: 120%;
            left: -10%;
            top: -10%;
            background: radial-gradient(ellipse at center, var(--court-blue-glow) 0%, transparent 60%);
            filter: blur(60px);
            opacity: 0.6;
            animation: glowPulse 4s ease-in-out infinite;
        }

        @keyframes glowPulse {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }

        /* Court Container */
        .court-container {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 3;
            background: linear-gradient(145deg, var(--noir-surface), var(--noir-elevated));
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            overflow: hidden;
            box-shadow:
                0 50px 100px -20px rgba(0, 0, 0, 0.5),
                0 0 0 1px var(--glass-border) inset,
                0 0 80px var(--court-blue-subtle);
        }

        .court-container::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--glass-shine);
            pointer-events: none;
            z-index: 10;
        }

        /* Sketchfab iframe */
        .court-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Loading state */
        .court-loading {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            background: var(--noir-elevated);
            z-index: 5;
            transition: opacity 0.5s, visibility 0.5s;
        }

        .court-loading.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .court-loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid var(--noir-surface);
            border-top-color: var(--court-blue-bright);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .court-caption {
            text-align: center;
            margin-top: 20px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .court-caption span {
            color: var(--court-blue-bright);
        }

        /* =====================================================
           FLOATING PADEL BALLS
        ===================================================== */

        .floating-ball {
            position: absolute;
            border-radius: 50%;
            z-index: 20;
            pointer-events: none;
        }

        .ball-sphere {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            position: relative;
            background:
                radial-gradient(ellipse 70% 70% at 30% 25%, rgba(255,255,255,0.9) 0%, transparent 50%),
                radial-gradient(ellipse 90% 90% at 70% 75%, rgba(0,0,0,0.4) 0%, transparent 50%),
                radial-gradient(circle at 40% 35%, #FDE68A 0%, #FBBF24 40%, #F59E0B 100%);
            box-shadow:
                0 20px 60px var(--ball-yellow-glow),
                inset 0 -10px 30px rgba(0,0,0,0.2);
        }

        /* Ball seam line */
        .ball-sphere::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: radial-gradient(
                closest-side,
                transparent 58%,
                rgba(255,255,255,0.1) 59%,
                rgba(255,255,255,0.8) 60%,
                rgba(255,255,255,0.8) 64%,
                transparent 65%
            );
            background-size: 130% 130%;
            background-position: -20% 50%;
            transform: rotate(-20deg);
        }

        /* Felt texture */
        .ball-sphere::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            opacity: 0.4;
            mix-blend-mode: overlay;
            background: url("data:image/svg+xml,%3Csvg viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100' height='100' filter='url(%23noise)'/%3E%3C/svg%3E");
        }

        .ball-1 {
            width: 100px;
            height: 100px;
            top: 8%;
            left: 5%;
            animation: floatBall1 8s ease-in-out infinite;
        }

        .ball-2 {
            width: 60px;
            height: 60px;
            top: 70%;
            left: 25%;
            animation: floatBall2 10s ease-in-out infinite 1s;
        }

        .ball-3 {
            width: 45px;
            height: 45px;
            top: 15%;
            right: 8%;
            animation: floatBall3 9s ease-in-out infinite 2s;
        }

        .ball-4 {
            width: 70px;
            height: 70px;
            bottom: 10%;
            right: 5%;
            animation: floatBall1 11s ease-in-out infinite 1.5s;
        }

        @keyframes floatBall1 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(15px, -25px) rotate(90deg); }
            50% { transform: translate(0, -10px) rotate(180deg); }
            75% { transform: translate(-15px, -20px) rotate(270deg); }
        }

        @keyframes floatBall2 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(20px, -30px) rotate(180deg); }
        }

        @keyframes floatBall3 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-15px, 25px) scale(1.05); }
        }

        /* =====================================================
           FEATURES SECTION
        ===================================================== */

        .features {
            padding: 160px 60px;
            position: relative;
            background: var(--noir-base);
        }

        .features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--court-blue-subtle), transparent);
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 80px;
        }

        .section-eyebrow {
            font-family: var(--font-body);
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: var(--court-blue-bright);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .section-eyebrow::before,
        .section-eyebrow::after {
            content: '';
            width: 40px;
            height: 1px;
            background: var(--court-blue);
        }

        .section-title {
            font-family: var(--font-display);
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 600;
            margin-bottom: 20px;
        }

        .section-title span {
            background: linear-gradient(135deg, var(--court-blue-bright), var(--turf-green-bright));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-desc {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.8;
        }

        /* Feature Cards Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: var(--noir-elevated);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            position: relative;
            overflow: hidden;
            transition: all 0.5s var(--ease-out-expo);
            opacity: 0;
            transform: translateY(40px);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--court-blue), var(--turf-green));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: radial-gradient(circle at center, var(--court-blue-subtle) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.5s;
            pointer-events: none;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: var(--court-blue);
            box-shadow: 0 30px 80px -20px rgba(0, 136, 204, 0.3);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover::after {
            opacity: 1;
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--court-blue-subtle), var(--turf-green-glow));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            position: relative;
        }

        .feature-icon svg {
            width: 28px;
            height: 28px;
            stroke: var(--court-blue-bright);
        }

        /* Ball accent on icon */
        .feature-icon::after {
            content: '';
            position: absolute;
            top: -6px;
            right: -6px;
            width: 18px;
            height: 18px;
            background: var(--ball-gradient);
            border-radius: 50%;
            box-shadow: 0 4px 15px var(--ball-yellow-glow);
        }

        .feature-title {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .feature-desc {
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        /* =====================================================
           QUOTE FORM SECTION
        ===================================================== */

        .quote-section {
            padding: 160px 60px;
            position: relative;
            background: linear-gradient(180deg, var(--noir-base) 0%, var(--noir-deep) 100%);
            overflow: hidden;
        }

        /* Court lines decoration */
        .quote-court-lines {
            position: absolute;
            inset: 0;
            opacity: 0.02;
            background:
                linear-gradient(90deg, var(--text-primary) 2px, transparent 2px),
                linear-gradient(var(--text-primary) 2px, transparent 2px);
            background-size: 100px 100px;
            background-position: center center;
        }

        .quote-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 80px;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        .quote-intro {
            opacity: 0;
            transform: translateX(-40px);
        }

        .quote-tagline {
            font-family: var(--font-display);
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: var(--ball-yellow);
            margin-bottom: 20px;
        }

        .quote-title {
            font-family: var(--font-display);
            font-size: clamp(3rem, 6vw, 5rem);
            font-weight: 600;
            line-height: 0.95;
            margin-bottom: 24px;
        }

        .quote-title span {
            background: linear-gradient(135deg, var(--court-blue-bright), var(--turf-green-bright));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .quote-desc {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 40px;
        }

        /* Feature list */
        .quote-features {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .quote-feature {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 0.95rem;
            color: var(--text-secondary);
        }

        .quote-feature-icon {
            width: 44px;
            height: 44px;
            background: var(--noir-glass);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .quote-feature-icon svg {
            width: 20px;
            height: 20px;
            stroke: var(--turf-green-bright);
        }

        /* Glass Form Card */
        .quote-form-card {
            background: var(--noir-glass);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            padding: 48px;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateX(40px);
        }

        .quote-form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--court-blue), var(--ball-yellow), var(--turf-green));
        }

        /* Floating ball decoration */
        .quote-form-card::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            bottom: -40px;
            right: -40px;
            background: radial-gradient(circle, var(--ball-yellow-glow) 0%, transparent 70%);
            opacity: 0.3;
            filter: blur(40px);
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-title {
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 16px 20px;
            font-family: var(--font-body);
            font-size: 0.95rem;
            color: var(--text-primary);
            background: var(--noir-elevated);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            transition: all 0.3s var(--ease-out-expo);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--court-blue);
            box-shadow: 0 0 0 4px var(--court-blue-subtle);
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn-submit {
            width: 100%;
            padding: 18px 32px;
            font-family: var(--font-body);
            font-size: 1rem;
            font-weight: 600;
            color: var(--noir-deep);
            background: var(--ball-gradient);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s var(--ease-out-expo);
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s var(--ease-out-expo);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 50px var(--ball-yellow-glow);
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        /* Alert styles */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--turf-green-bright);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {
            padding: 60px;
            background: var(--noir-deep);
            border-top: 1px solid var(--glass-border);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-logo {
            height: 36px;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .footer-logo:hover {
            opacity: 1;
        }

        .footer-links {
            display: flex;
            gap: 32px;
        }

        .footer-link {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-link:hover {
            color: var(--ball-yellow);
        }

        .footer-copy {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* =====================================================
           RESPONSIVE DESIGN
        ===================================================== */

        @media (max-width: 1200px) {
            .hero-content {
                grid-template-columns: 1fr;
                gap: 60px;
                text-align: center;
            }

            .hero-text {
                order: 1;
            }

            .hero-visual {
                order: 2;
                max-width: 600px;
                margin: 0 auto;
            }

            .hero-badge {
                margin: 0 auto 32px;
            }

            .hero-subtitle {
                margin: 0 auto 40px;
            }

            .hero-stats {
                justify-content: center;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .quote-container {
                grid-template-columns: 1fr;
                gap: 60px;
            }

            .quote-intro {
                text-align: center;
            }

            .quote-features {
                align-items: center;
            }
        }

        @media (max-width: 768px) {
            .nav {
                padding: 16px 24px;
            }

            .nav-link:not(.primary) {
                display: none;
            }

            .hero {
                padding-top: 100px;
            }

            .hero-content {
                padding: 0 24px;
            }

            .hero-title {
                font-size: 2.8rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 24px;
            }

            .stat-item::after {
                display: none;
            }

            .features,
            .quote-section {
                padding: 100px 24px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .quote-form-card {
                padding: 32px;
            }

            .footer-content {
                flex-direction: column;
                gap: 24px;
                text-align: center;
            }

            .ball-1, .ball-4 {
                display: none;
            }

            .cursor, .cursor-dot {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2.2rem;
            }

            .stat-value {
                font-size: 2.5rem;
            }

            .ball-2, .ball-3 {
                display: none;
            }
        }

        /* =====================================================
           SCROLL REVEAL ANIMATIONS
        ===================================================== */

        [data-reveal] {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s var(--ease-out-expo), transform 0.8s var(--ease-out-expo);
        }

        [data-reveal].revealed {
            opacity: 1;
            transform: translateY(0);
        }

        [data-reveal="left"] {
            transform: translateX(-40px);
        }

        [data-reveal="left"].revealed {
            transform: translateX(0);
        }

        [data-reveal="right"] {
            transform: translateX(40px);
        }

        [data-reveal="right"].revealed {
            transform: translateX(0);
        }

        [data-reveal="scale"] {
            transform: scale(0.9);
        }

        [data-reveal="scale"].revealed {
            transform: scale(1);
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loader" id="loader">
        <div class="loader-court">
            <div class="loader-ball"></div>
        </div>
        <span class="loader-text">Loading Experience</span>
        <div class="loader-progress">
            <div class="loader-progress-bar"></div>
        </div>
    </div>

    <!-- Custom Cursor -->
    <div class="cursor" id="cursor"></div>
    <div class="cursor-dot" id="cursorDot"></div>

    <!-- Navigation -->
    <nav class="nav" id="nav">
        <a href="<?php echo SITE_URL; ?>">
            <img src="https://cdn.prod.website-files.com/66c705c26941f009cfd3255f/66c70de3185822b627ec80ac_SKYPADEL_INDIA_LOGO.png" alt="Sky Padel India" class="nav-logo">
        </a>
        <div class="nav-links">
            <a href="#features" class="nav-link">Why Us</a>
            <a href="#quote" class="nav-link">Get Quote</a>
            <a href="<?php echo SITE_URL; ?>/login.php" class="nav-link primary">Client Portal</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <!-- Parallax Background -->
        <div class="hero-bg">
            <div class="parallax-layer parallax-gradient" data-speed="0.1"></div>
            <div class="parallax-layer parallax-court-lines" data-speed="0.2"></div>
            <div class="parallax-layer parallax-grid" data-speed="0.15"></div>

            <!-- Stadium Light Beams -->
            <div class="light-beam light-beam-1"></div>
            <div class="light-beam light-beam-2"></div>

            <!-- Floating Particles -->
            <div class="particles" id="particles"></div>
        </div>

        <!-- Floating Padel Balls -->
        <div class="floating-ball ball-1" data-speed="0.3">
            <div class="ball-sphere"></div>
        </div>
        <div class="floating-ball ball-2" data-speed="0.4">
            <div class="ball-sphere"></div>
        </div>
        <div class="floating-ball ball-3" data-speed="0.25">
            <div class="ball-sphere"></div>
        </div>
        <div class="floating-ball ball-4" data-speed="0.35">
            <div class="ball-sphere"></div>
        </div>

        <!-- Hero Content -->
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge" id="heroBadge">
                    <span class="badge-dot"></span>
                    India's Leading Padel Court Builder
                </div>

                <h1 class="hero-title">
                    <span class="title-line">
                        <span class="title-line-inner" id="titleLine1">The Ball Is</span>
                    </span>
                    <span class="title-line">
                        <span class="title-line-inner title-accent" id="titleLine2">In Your</span>
                    </span>
                    <span class="title-line">
                        <span class="title-line-inner title-outline" id="titleLine3">Court</span>
                    </span>
                </h1>

                <p class="hero-subtitle" id="heroSubtitle">
                    From architectural vision to turnkey installation, we craft world-class
                    padel facilities with FIP certification and an industry-leading 5-year warranty.
                </p>

                <div class="hero-stats" id="heroStats">
                    <div class="stat-item">
                        <div class="stat-value">1200+</div>
                        <div class="stat-label">Courts Worldwide</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">50+</div>
                        <div class="stat-label">Countries</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">5yr</div>
                        <div class="stat-label">Warranty</div>
                    </div>
                </div>
            </div>

            <!-- 3D Court Visualization -->
            <div class="hero-visual">
                <div class="court-3d-wrapper" id="courtWrapper">
                    <div class="court-glow"></div>
                    <div class="court-container">
                        <div class="court-loading" id="courtLoading">
                            <div class="court-loading-spinner"></div>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">Loading 3D Model</span>
                        </div>
                        <iframe
                            id="sketchfab-viewer"
                            title="Padel Court 3D Model"
                            allow="autoplay; fullscreen; xr-spatial-tracking"
                            allowfullscreen
                            mozallowfullscreen="true"
                            webkitallowfullscreen="true">
                        </iframe>
                    </div>
                </div>
                <p class="court-caption">Interactive 3D Court — <span>Drag to explore</span></p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="section-header" data-reveal>
            <div class="section-eyebrow">Premium Quality</div>
            <h2 class="section-title">Why Choose <span>Sky Padel?</span></h2>
            <p class="section-desc">
                International manufacturing standards, local expertise. We bring world-class
                padel infrastructure to India with uncompromising quality.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card" data-reveal>
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h3 class="feature-title">FIP Certified</h3>
                <p class="feature-desc">
                    All courts meet International Padel Federation standards with imported
                    tempered glass and professional-grade artificial turf.
                </p>
            </div>

            <div class="feature-card" data-reveal>
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="3" y1="9" x2="21" y2="9"/>
                        <line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                </div>
                <h3 class="feature-title">Turnkey Solutions</h3>
                <p class="feature-desc">
                    Complete end-to-end management from site assessment and design to
                    installation, lighting, and ongoing maintenance.
                </p>
            </div>

            <div class="feature-card" data-reveal>
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <h3 class="feature-title">Live Tracking</h3>
                <p class="feature-desc">
                    Dedicated client portal with real-time milestone tracking, document
                    management, and seamless payment processing.
                </p>
            </div>

            <div class="feature-card" data-reveal>
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <h3 class="feature-title">5-Year Warranty</h3>
                <p class="feature-desc">
                    Industry-leading anti-rust warranty backed by our commitment to
                    long-term quality and customer satisfaction.
                </p>
            </div>

            <div class="feature-card" data-reveal>
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </div>
                <h3 class="feature-title">Global Manufacturing</h3>
                <p class="feature-desc">
                    Manufacturing facilities in Spain, America, and India ensuring
                    consistent quality aligned with European standards.
                </p>
            </div>

            <div class="feature-card" data-reveal>
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3 class="feature-title">Expert Network</h3>
                <p class="feature-desc">
                    Post-sales services through our partner network spanning 50+ countries
                    ensuring support wherever you are.
                </p>
            </div>
        </div>
    </section>

    <!-- Quote Form Section -->
    <section class="quote-section" id="quote">
        <div class="quote-court-lines"></div>

        <div class="quote-container">
            <div class="quote-intro" data-reveal="left">
                <div class="quote-tagline">Start Your Project</div>
                <h2 class="quote-title">Ready to <span>Build?</span></h2>
                <p class="quote-desc">
                    Get a detailed, no-obligation quote for your padel court project.
                    Our team will reach out within 24 hours with a customized proposal.
                </p>

                <div class="quote-features">
                    <div class="quote-feature">
                        <div class="quote-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        </div>
                        <span>Free site assessment & consultation</span>
                    </div>
                    <div class="quote-feature">
                        <div class="quote-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        </div>
                        <span>Detailed project timeline & milestones</span>
                    </div>
                    <div class="quote-feature">
                        <div class="quote-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        </div>
                        <span>Flexible payment options available</span>
                    </div>
                </div>
            </div>

            <div class="quote-form-card" data-reveal="right">
                <div class="form-header">
                    <h3 class="form-title">Get Your Free Quote</h3>
                    <p class="form-subtitle">We'll respond within 24 hours</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        Thank you! Your inquiry has been submitted. We'll contact you soon.
                    </div>
                <?php elseif ($error): ?>
                    <div class="alert alert-error">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <?php echo e($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!$success): ?>
                <form method="POST" action="#quote">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Your Name *</label>
                            <input type="text" name="clientName" class="form-input" placeholder="Full Name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" name="clientPhone" class="form-input" placeholder="+91 XXXXX XXXXX" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="clientEmail" class="form-input" placeholder="email@example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Company / Club</label>
                            <input type="text" name="clientCompany" class="form-input" placeholder="Organization Name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="siteCity" class="form-input" placeholder="City">
                        </div>
                        <div class="form-group">
                            <label class="form-label">State</label>
                            <input type="text" name="siteState" class="form-input" placeholder="State">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Court Requirement</label>
                        <select name="courtRequirement" class="form-select">
                            <option value="">Select Configuration</option>
                            <option value="Single Court">Single Court</option>
                            <option value="2 Courts">2 Courts</option>
                            <option value="3 Courts">3 Courts</option>
                            <option value="4+ Courts">4+ Courts</option>
                            <option value="Not Sure">Not Sure - Need Consultation</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Project Details</label>
                        <textarea name="notes" class="form-textarea" placeholder="Tell us about your vision..."></textarea>
                    </div>

                    <button type="submit" name="submit_lead" class="btn-submit">
                        Submit Inquiry
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <a href="<?php echo SITE_URL; ?>">
                <img src="https://cdn.prod.website-files.com/66c705c26941f009cfd3255f/66c70de3185822b627ec80ac_SKYPADEL_INDIA_LOGO.png" alt="Sky Padel India" class="footer-logo">
            </a>
            <div class="footer-links">
                <a href="#features" class="footer-link">Why Sky Padel</a>
                <a href="#quote" class="footer-link">Get Quote</a>
                <a href="<?php echo SITE_URL; ?>/login.php" class="footer-link">Client Portal</a>
            </div>
            <p class="footer-copy">&copy; <?php echo date('Y'); ?> Sky Padel India. All rights reserved.</p>
        </div>
    </footer>

    <!-- Sketchfab Viewer API -->
    <script src="https://static.sketchfab.com/api/sketchfab-viewer-1.12.1.js"></script>

    <script>
        // =====================================================
        // LENIS SMOOTH SCROLL
        // =====================================================

        const lenis = new Lenis({
            duration: 1.2,
            easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            direction: 'vertical',
            gestureDirection: 'vertical',
            smooth: true,
            mouseMultiplier: 1,
            smoothTouch: false,
            touchMultiplier: 2,
            infinite: false,
        });

        function raf(time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        }

        requestAnimationFrame(raf);

        // GSAP ScrollTrigger integration
        gsap.registerPlugin(ScrollTrigger);

        lenis.on('scroll', ScrollTrigger.update);

        gsap.ticker.add((time) => {
            lenis.raf(time * 1000);
        });

        gsap.ticker.lagSmoothing(0);

        // =====================================================
        // LOADING SCREEN
        // =====================================================

        window.addEventListener('load', () => {
            const loader = document.getElementById('loader');

            setTimeout(() => {
                loader.style.opacity = '0';
                loader.style.visibility = 'hidden';
                loader.style.transition = 'opacity 0.5s, visibility 0.5s';

                // Start hero animations
                animateHero();
            }, 2200);
        });

        // =====================================================
        // CUSTOM CURSOR
        // =====================================================

        const cursor = document.getElementById('cursor');
        const cursorDot = document.getElementById('cursorDot');
        let mouseX = 0, mouseY = 0;
        let cursorX = 0, cursorY = 0;

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });

        function animateCursor() {
            cursorX += (mouseX - cursorX) * 0.1;
            cursorY += (mouseY - cursorY) * 0.1;

            cursor.style.left = cursorX - 10 + 'px';
            cursor.style.top = cursorY - 10 + 'px';

            cursorDot.style.left = mouseX - 3 + 'px';
            cursorDot.style.top = mouseY - 3 + 'px';

            requestAnimationFrame(animateCursor);
        }

        animateCursor();

        // Cursor hover effect
        document.querySelectorAll('a, button, .feature-card').forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
        });

        // =====================================================
        // HERO ANIMATIONS
        // =====================================================

        function animateHero() {
            // Badge animation
            gsap.to('#heroBadge', {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'expo.out'
            });

            // Title lines stagger
            gsap.to('#titleLine1', {
                y: 0,
                duration: 1,
                ease: 'expo.out',
                delay: 0.2
            });

            gsap.to('#titleLine2', {
                y: 0,
                duration: 1,
                ease: 'expo.out',
                delay: 0.35
            });

            gsap.to('#titleLine3', {
                y: 0,
                duration: 1,
                ease: 'expo.out',
                delay: 0.5
            });

            // Subtitle
            gsap.to('#heroSubtitle', {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'expo.out',
                delay: 0.6
            });

            // Stats
            gsap.to('#heroStats', {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'expo.out',
                delay: 0.8
            });

            // Court 3D wrapper
            gsap.from('#courtWrapper', {
                rotateX: 20,
                rotateY: -15,
                scale: 0.9,
                opacity: 0,
                duration: 1.2,
                ease: 'expo.out',
                delay: 0.4
            });
        }

        // =====================================================
        // PARALLAX EFFECTS
        // =====================================================

        const parallaxLayers = document.querySelectorAll('[data-speed]');
        const floatingBalls = document.querySelectorAll('.floating-ball');

        lenis.on('scroll', ({ scroll }) => {
            // Background parallax
            parallaxLayers.forEach(layer => {
                const speed = parseFloat(layer.dataset.speed);
                layer.style.transform = `translateY(${scroll * speed}px)`;
            });

            // Floating balls parallax
            floatingBalls.forEach(ball => {
                const speed = parseFloat(ball.dataset.speed) || 0.3;
                const yOffset = scroll * speed;
                ball.style.transform = `translateY(${yOffset}px)`;
            });
        });

        // Mouse parallax for balls
        let ballMouseX = 0, ballMouseY = 0;
        let ballTargetX = 0, ballTargetY = 0;

        document.addEventListener('mousemove', (e) => {
            ballMouseX = (e.clientX / window.innerWidth - 0.5) * 2;
            ballMouseY = (e.clientY / window.innerHeight - 0.5) * 2;
        });

        function animateBallsParallax() {
            ballTargetX += (ballMouseX - ballTargetX) * 0.05;
            ballTargetY += (ballMouseY - ballTargetY) * 0.05;

            floatingBalls.forEach((ball, i) => {
                const intensity = (i + 1) * 15;
                const x = ballTargetX * intensity;
                const y = ballTargetY * intensity;
                ball.style.transform += ` translate(${x}px, ${y}px)`;
            });

            requestAnimationFrame(animateBallsParallax);
        }

        // Only run parallax on desktop
        if (window.innerWidth > 768) {
            animateBallsParallax();
        }

        // =====================================================
        // FLOATING PARTICLES
        // =====================================================

        const particlesContainer = document.getElementById('particles');

        function createParticles() {
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        createParticles();

        // =====================================================
        // NAVBAR SCROLL EFFECT
        // =====================================================

        const nav = document.getElementById('nav');

        lenis.on('scroll', ({ scroll }) => {
            if (scroll > 100) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        // =====================================================
        // SCROLL REVEAL ANIMATIONS
        // =====================================================

        const revealElements = document.querySelectorAll('[data-reveal]');

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(el => {
            revealObserver.observe(el);
        });

        // Feature cards stagger animation
        const featureCards = document.querySelectorAll('.feature-card');

        ScrollTrigger.batch(featureCards, {
            onEnter: (elements) => {
                gsap.to(elements, {
                    opacity: 1,
                    y: 0,
                    stagger: 0.15,
                    duration: 0.8,
                    ease: 'expo.out'
                });
            },
            start: 'top 85%'
        });

        // Quote section animations
        ScrollTrigger.create({
            trigger: '.quote-section',
            start: 'top 70%',
            onEnter: () => {
                gsap.to('.quote-intro', {
                    opacity: 1,
                    x: 0,
                    duration: 1,
                    ease: 'expo.out'
                });

                gsap.to('.quote-form-card', {
                    opacity: 1,
                    x: 0,
                    duration: 1,
                    ease: 'expo.out',
                    delay: 0.2
                });
            }
        });

        // =====================================================
        // 3D COURT INTERACTION
        // =====================================================

        const courtWrapper = document.getElementById('courtWrapper');

        if (window.innerWidth > 768) {
            document.addEventListener('mousemove', (e) => {
                const rect = courtWrapper.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;

                const rotateY = (e.clientX - centerX) / rect.width * 10;
                const rotateX = (e.clientY - centerY) / rect.height * -10;

                courtWrapper.style.transform = `rotateX(${10 + rotateX}deg) rotateY(${-5 + rotateY}deg)`;
            });
        }

        // =====================================================
        // SKETCHFAB VIEWER
        // =====================================================

        (function() {
            var iframe = document.getElementById('sketchfab-viewer');
            var loading = document.getElementById('courtLoading');
            var uid = 'ef977f0912af4297bd842ab7bb82dde1';

            var client = new Sketchfab(iframe);

            client.init(uid, {
                success: function(api) {
                    api.start();

                    api.addEventListener('viewerready', function() {
                        loading.classList.add('hidden');

                        api.setBackground({color: [0.027, 0.043, 0.071]}, function() {
                            console.log('Background matched to site theme');
                        });
                    });
                },
                error: function() {
                    console.error('Sketchfab viewer failed to load');
                    loading.innerHTML = '<p style="color: var(--text-muted);">3D Model unavailable</p>';
                },
                autospin: 0.2,
                autostart: 1,
                camera: 0,
                preload: 1,
                ui_animations: 0,
                ui_infos: 0,
                ui_stop: 0,
                ui_inspector: 0,
                ui_watermark_link: 0,
                ui_watermark: 0,
                ui_ar: 0,
                ui_help: 0,
                ui_settings: 0,
                ui_vr: 0,
                ui_fullscreen: 0,
                ui_annotations: 0,
                ui_hint: 0,
                ui_controls: 1,
                scrollwheel: 1,
                transparent: 0
            });
        })();

        // =====================================================
        // SMOOTH SCROLL LINKS
        // =====================================================

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    lenis.scrollTo(target, {
                        offset: -100,
                        duration: 1.2
                    });
                }
            });
        });
    </script>
</body>
</html>

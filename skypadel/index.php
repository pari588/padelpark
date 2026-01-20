<?php
/**
 * Sky Padel India - Premium Padel Court Construction
 * Design: Luxury Athletic Noir - Dark, Cinematic, Precision Engineering
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
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /*
         * ============================================
         * SKY PADEL INDIA - LUXURY ATHLETIC NOIR
         * Dark, Cinematic, Precision Engineering
         * ============================================
         */

        :root {
            /* Court Blue - The signature color */
            --court: #0088cc;
            --court-light: #00a8e8;
            --court-dark: #006699;
            --court-glow: rgba(0, 136, 204, 0.4);

            /* Tennis/Padel Ball - From user's CSS */
            --felt-hi: #f3ff83;
            --felt: #cfe72a;
            --felt-lo: #7aa105;
            --seam: #f4f3ea;

            /* Noir Palette */
            --void: #050810;
            --surface: #0a0f1a;
            --surface-elevated: #111827;
            --glass: rgba(255, 255, 255, 0.08);
            --glass-edge: rgba(255, 255, 255, 0.15);

            /* Text Hierarchy */
            --text: #f0f4f8;
            --text-secondary: #8892a4;
            --text-tertiary: #4a5568;

            /* Typography */
            --font-display: 'Bebas Neue', Impact, sans-serif;
            --font-body: 'Outfit', system-ui, sans-serif;

            /* Timing */
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-body);
            background: var(--void);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        ::selection {
            background: var(--court);
            color: white;
        }

        /* ============================================
           NAVIGATION
        ============================================ */
        .nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.4s var(--ease-out);
        }

        .nav.scrolled {
            background: rgba(5, 8, 16, 0.9);
            backdrop-filter: blur(20px);
            padding: 14px 40px;
            border-bottom: 1px solid var(--glass);
        }

        .nav-logo {
            height: 42px;
            transition: transform 0.3s var(--ease-out);
        }

        .nav-logo:hover {
            transform: scale(1.05);
        }

        .nav-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .nav-link {
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            transition: all 0.3s var(--ease-out);
        }

        .nav-link-ghost {
            color: var(--text-secondary);
        }

        .nav-link-ghost:hover {
            color: var(--text);
            background: var(--glass);
        }

        .nav-link-primary {
            background: var(--court);
            color: white;
            font-weight: 600;
        }

        .nav-link-primary:hover {
            background: var(--court-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--court-glow);
        }

        /* ============================================
           HERO SECTION
        ============================================ */
        .hero {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
            overflow: hidden;
        }

        /* Atmospheric Background */
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 50% at 70% 50%, rgba(0, 136, 204, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse 60% 40% at 30% 60%, rgba(207, 231, 42, 0.04) 0%, transparent 40%);
            pointer-events: none;
        }

        /* Subtle Grid */
        .hero-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 80px 80px;
            mask-image: radial-gradient(ellipse 70% 60% at 50% 50%, black, transparent);
        }

        /* ============================================
           CSS TENNIS BALLS
           User's exact design: Single Arc + Fuzz
        ============================================ */
        .ball {
            --size: 80px;
            width: var(--size);
            aspect-ratio: 1;
            border-radius: 50%;
            position: absolute;
            z-index: 5;
            overflow: hidden;

            background:
                /* Highlight */
                radial-gradient(60% 60% at 30% 25%,
                    rgba(255,255,255,.9) 0%,
                    rgba(255,255,255,.25) 30%,
                    rgba(255,255,255,0) 55%),
                /* Shadow */
                radial-gradient(95% 95% at 70% 75%,
                    rgba(0,0,0,.45) 0%,
                    rgba(0,0,0,0) 55%),
                /* Felt */
                radial-gradient(circle at 45% 40%,
                    var(--felt-hi) 0%,
                    var(--felt) 45%,
                    var(--felt-lo) 100%);

            box-shadow:
                inset 0 -20px 30px rgba(0,0,0,.25),
                0 20px 40px rgba(0,0,0,.4),
                0 0 60px rgba(207, 231, 42, 0.2);
        }

        /* Single Arc Seam */
        .ball::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 50%;
            pointer-events: none;

            background: radial-gradient(
                closest-side,
                rgba(0,0,0,0) 0%,
                rgba(0,0,0,0) 58%,
                /* Groove shadow */
                rgba(0,0,0,.14) 58.6%,
                rgba(0,0,0,0) 59.3%,
                /* White seam */
                var(--seam) 59.3%,
                var(--seam) 63.3%,
                rgba(244,243,234,0) 64.0%,
                rgba(0,0,0,0) 100%
            );

            background-size: 130% 130%;
            background-position: -26% 50%;
            background-repeat: no-repeat;
            transform: rotate(-18deg);
        }

        /* Fuzz / Felt Texture */
        .ball::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 50%;
            pointer-events: none;
            opacity: .45;
            mix-blend-mode: overlay;

            background:
                radial-gradient(circle at 18% 32%, rgba(255,255,255,.14) 0 1px, transparent 2px),
                radial-gradient(circle at 32% 54%, rgba(0,0,0,.12) 0 1px, transparent 2px),
                radial-gradient(circle at 46% 38%, rgba(255,255,255,.12) 0 1px, transparent 2px),
                radial-gradient(circle at 58% 62%, rgba(0,0,0,.12) 0 1px, transparent 2px),
                radial-gradient(circle at 70% 42%, rgba(255,255,255,.10) 0 1px, transparent 2px),
                radial-gradient(circle at 82% 68%, rgba(0,0,0,.12) 0 1px, transparent 2px);
            background-size: 18px 18px;
        }

        .ball-1 {
            --size: 100px;
            top: 10%;
            left: 5%;
            animation: float1 6s ease-in-out infinite;
        }

        .ball-2 {
            --size: 55px;
            top: 65%;
            left: 28%;
            animation: float2 5s ease-in-out infinite 0.5s;
        }

        .ball-3 {
            --size: 40px;
            top: 18%;
            right: 10%;
            animation: float3 5.5s ease-in-out infinite 1s;
        }

        .ball-4 {
            --size: 70px;
            bottom: 15%;
            right: 5%;
            animation: float1 7s ease-in-out infinite 1.5s;
        }

        @keyframes float1 {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        @keyframes float2 {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(8px, -15px); }
        }

        @keyframes float3 {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-25px) scale(1.03); }
        }

        /* ============================================
           HERO CONTENT - Left Side
        ============================================ */
        .hero-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 120px 60px 80px 80px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            background: var(--glass);
            border: 1px solid var(--glass-edge);
            border-radius: 100px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            width: fit-content;
            margin-bottom: 28px;
            opacity: 0;
            animation: fadeUp 0.8s var(--ease-out) 0.2s forwards;
        }

        .hero-badge-dot {
            width: 6px;
            height: 6px;
            background: var(--felt);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.2); }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero-title {
            font-family: var(--font-display);
            font-size: clamp(3.5rem, 8vw, 7rem);
            line-height: 0.92;
            letter-spacing: -0.01em;
            text-transform: uppercase;
            margin-bottom: 28px;
        }

        .hero-title-line {
            display: block;
            opacity: 0;
            animation: titleSlide 0.9s var(--ease-out) forwards;
        }

        .hero-title-line:nth-child(1) { animation-delay: 0.3s; }
        .hero-title-line:nth-child(2) { animation-delay: 0.4s; }
        .hero-title-line:nth-child(3) { animation-delay: 0.5s; }

        @keyframes titleSlide {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero-title-accent {
            background: linear-gradient(135deg, var(--felt-hi) 0%, var(--felt) 60%, var(--court-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.15rem;
            font-weight: 400;
            color: var(--text-secondary);
            max-width: 420px;
            line-height: 1.7;
            margin-bottom: 40px;
            opacity: 0;
            animation: fadeUp 0.8s var(--ease-out) 0.6s forwards;
        }

        .hero-stats {
            display: flex;
            gap: 40px;
            opacity: 0;
            animation: fadeUp 0.8s var(--ease-out) 0.7s forwards;
        }

        .stat {
            position: relative;
        }

        .stat::after {
            content: '';
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 40px;
            background: linear-gradient(180deg, transparent, var(--glass-edge), transparent);
        }

        .stat:last-child::after { display: none; }

        .stat-value {
            font-family: var(--font-display);
            font-size: 3rem;
            line-height: 1;
            color: var(--text);
        }

        .stat-label {
            font-size: 0.7rem;
            color: var(--text-tertiary);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-top: 6px;
        }

        /* ============================================
           3D PADEL COURT - Sketchfab Viewer API
           Seamlessly integrated with site design
        ============================================ */
        .hero-visual {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100%;
            padding: 20px;
        }

        .court-viewer-wrapper {
            position: relative;
            width: 100%;
            max-width: 700px;
        }

        /* Ambient glow behind the viewer */
        .court-glow {
            position: absolute;
            width: 80%;
            height: 60%;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background: radial-gradient(ellipse, var(--court-glow) 0%, transparent 70%);
            filter: blur(60px);
            pointer-events: none;
            z-index: 0;
        }

        .court-container {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 3;
            border-radius: 20px;
            overflow: hidden;
            z-index: 1;
            background: linear-gradient(145deg, rgba(10,15,26,0.8), rgba(5,8,16,0.95));
            border: 1px solid var(--glass-edge);
        }

        .court-container iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /* Loading state */
        .court-container::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 30% 40%, var(--court-glow) 0%, transparent 50%),
                var(--surface);
            z-index: -1;
        }

        .court-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface);
            z-index: 2;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .court-loading.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .court-loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid var(--glass-edge);
            border-top-color: var(--court-light);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Caption below viewer */
        .court-caption {
            text-align: center;
            margin-top: 16px;
            font-size: 0.85rem;
            color: var(--text-tertiary);
        }

        .court-caption span {
            color: var(--court-light);
        }

        /* ============================================
           FORM SECTION
        ============================================ */
        .form-section {
            padding: 100px 40px;
            background: linear-gradient(180deg, var(--void) 0%, var(--surface) 100%);
        }

        .form-container {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 60px;
            align-items: center;
        }

        .form-intro h2 {
            font-family: var(--font-display);
            font-size: clamp(2.5rem, 5vw, 4rem);
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .form-intro h2 span {
            color: var(--court-light);
        }

        .form-intro p {
            color: var(--text-secondary);
            line-height: 1.8;
            max-width: 380px;
        }

        .form-features {
            margin-top: 36px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-feature {
            display: flex;
            align-items: center;
            gap: 14px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .form-feature-icon {
            width: 36px;
            height: 36px;
            background: var(--glass);
            border: 1px solid var(--glass-edge);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .form-feature-icon svg {
            width: 18px;
            height: 18px;
            stroke: var(--court-light);
        }

        .form-card {
            background: var(--surface-elevated);
            border: 1px solid var(--glass-edge);
            border-radius: 16px;
            padding: 36px;
            position: relative;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--court), var(--felt), var(--court-light));
            border-radius: 16px 16px 0 0;
        }

        .form-title {
            font-family: var(--font-display);
            font-size: 1.5rem;
            margin-bottom: 6px;
        }

        .form-subtitle {
            color: var(--text-tertiary);
            font-size: 0.9rem;
            margin-bottom: 28px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-tertiary);
            margin-bottom: 8px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 14px 16px;
            font-family: var(--font-body);
            font-size: 0.9rem;
            color: var(--text);
            background: var(--surface);
            border: 1px solid var(--glass-edge);
            border-radius: 8px;
            transition: all 0.3s var(--ease-out);
        }

        .form-input::placeholder {
            color: var(--text-tertiary);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--court);
            box-shadow: 0 0 0 3px var(--court-glow);
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }

        .form-textarea {
            min-height: 90px;
            resize: vertical;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            font-family: var(--font-body);
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--void);
            background: linear-gradient(135deg, var(--felt-hi) 0%, var(--felt) 100%);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s var(--ease-out);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(207, 231, 42, 0.3);
        }

        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        /* ============================================
           FEATURES SECTION
        ============================================ */
        .features {
            padding: 100px 40px;
            background: var(--surface);
        }

        .features-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .features-header h2 {
            font-family: var(--font-display);
            font-size: clamp(2rem, 4vw, 3.5rem);
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .features-header h2 span {
            background: linear-gradient(135deg, var(--court), var(--court-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .features-header p {
            color: var(--text-secondary);
            max-width: 460px;
            margin: 0 auto;
        }

        .features-grid {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .feature-card {
            background: var(--surface-elevated);
            border: 1px solid var(--glass-edge);
            border-radius: 12px;
            padding: 32px;
            transition: all 0.4s var(--ease-out);
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -15px rgba(0,136,204,0.2);
            border-color: var(--court);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            background: var(--glass);
            border: 1px solid var(--glass-edge);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .feature-icon svg {
            width: 24px;
            height: 24px;
            stroke: var(--court-light);
        }

        .feature-title {
            font-family: var(--font-display);
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .feature-desc {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.7;
        }

        /* ============================================
           FOOTER
        ============================================ */
        .footer {
            padding: 50px 40px;
            background: var(--void);
            border-top: 1px solid var(--glass);
        }

        .footer-content {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-logo {
            height: 32px;
            opacity: 0.7;
            transition: opacity 0.3s;
        }

        .footer-logo:hover { opacity: 1; }

        .footer-links {
            display: flex;
            gap: 28px;
        }

        .footer-link {
            color: var(--text-tertiary);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s;
        }

        .footer-link:hover { color: var(--felt); }

        .footer-copy {
            color: var(--text-tertiary);
            font-size: 0.8rem;
        }

        /* ============================================
           RESPONSIVE
        ============================================ */
        @media (max-width: 1024px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-content {
                padding: 130px 40px 60px;
                text-align: center;
            }

            .hero-badge { margin: 0 auto 28px; }
            .hero-subtitle { margin: 0 auto 40px; }
            .hero-stats { justify-content: center; }

            .hero-visual {
                min-height: 340px;
            }

            .court-wrapper {
                transform: scale(0.9);
            }

            .form-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .form-intro { text-align: center; }
            .form-intro p { margin: 0 auto; }
            .form-features { align-items: center; }

            .features-grid {
                grid-template-columns: 1fr;
                max-width: 420px;
            }
        }

        @media (max-width: 768px) {
            .nav { padding: 16px 20px; }
            .nav-link-ghost { display: none; }

            .hero-content { padding: 110px 24px 50px; }
            .hero-title { font-size: 2.8rem; }

            .hero-stats {
                flex-direction: column;
                gap: 20px;
            }

            .stat::after { display: none; }

            .form-section, .features { padding: 70px 20px; }
            .form-card { padding: 28px; }
            .form-row { grid-template-columns: 1fr; }

            .footer-content {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .ball-1, .ball-4 { display: none; }

            .court-wrapper {
                transform: scale(0.75);
            }

            .hero-visual {
                min-height: 300px;
            }
        }

        @media (max-width: 480px) {
            .hero-title { font-size: 2.4rem; }

            .court-wrapper {
                transform: scale(0.6);
            }

            .hero-visual {
                min-height: 260px;
            }

            .ball-2, .ball-3 { display: none; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav" id="nav">
        <a href="<?php echo SITE_URL; ?>">
            <img src="https://cdn.prod.website-files.com/66c705c26941f009cfd3255f/66c70de3185822b627ec80ac_SKYPADEL_INDIA_LOGO.png" alt="Sky Padel India" class="nav-logo">
        </a>
        <div class="nav-actions">
            <a href="#quote" class="nav-link nav-link-ghost">Get Quote</a>
            <a href="<?php echo SITE_URL; ?>/login.php" class="nav-link nav-link-primary">Client Portal</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-grid"></div>

        <!-- Tennis Balls - User's CSS design -->
        <div class="ball ball-1" aria-hidden="true"></div>
        <div class="ball ball-2" aria-hidden="true"></div>
        <div class="ball ball-3" aria-hidden="true"></div>
        <div class="ball ball-4" aria-hidden="true"></div>

        <div class="hero-content">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                India's Leading Padel Court Builder
            </div>

            <h1 class="hero-title">
                <span class="hero-title-line">Build Your</span>
                <span class="hero-title-line hero-title-accent">Premium</span>
                <span class="hero-title-line">Padel Court</span>
            </h1>

            <p class="hero-subtitle">
                From architectural design to turnkey installation, we deliver world-class
                padel facilities with FIP certification and 5-year warranty.
            </p>

            <div class="hero-stats">
                <div class="stat">
                    <div class="stat-value">1200+</div>
                    <div class="stat-label">Courts Worldwide</div>
                </div>
                <div class="stat">
                    <div class="stat-value">50+</div>
                    <div class="stat-label">Countries</div>
                </div>
                <div class="stat">
                    <div class="stat-value">5yr</div>
                    <div class="stat-label">Warranty</div>
                </div>
            </div>
        </div>

        <!-- 3D Padel Court - Sketchfab Viewer API Integration -->
        <div class="hero-visual">
            <div class="court-viewer-wrapper">
                <div class="court-glow"></div>
                <div class="court-container">
                    <div class="court-loading" id="courtLoading">
                        <div class="court-loading-spinner"></div>
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
                <p class="court-caption">Interactive 3D Model &mdash; <span>Drag to rotate</span>, scroll to zoom</p>
            </div>
        </div>
    </section>

    <!-- Form Section -->
    <section class="form-section" id="quote">
        <div class="form-container">
            <div class="form-intro">
                <h2>Ready to <span>Build?</span></h2>
                <p>
                    Get a detailed quote for your padel court project. Our team will reach out
                    within 24 hours with a customized proposal.
                </p>
                <div class="form-features">
                    <div class="form-feature">
                        <div class="form-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        </div>
                        <span>FIP Certified Courts</span>
                    </div>
                    <div class="form-feature">
                        <div class="form-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <line x1="3" y1="9" x2="21" y2="9"/>
                                <line x1="9" y1="21" x2="9" y2="9"/>
                            </svg>
                        </div>
                        <span>Complete Turnkey Solutions</span>
                    </div>
                    <div class="form-feature">
                        <div class="form-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <span>5-Year Comprehensive Warranty</span>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <h3 class="form-title">Get Your Free Quote</h3>
                <p class="form-subtitle">Fill the form and we'll get back to you within 24 hours</p>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        Thank you! Your inquiry has been submitted. We'll contact you soon.
                    </div>
                <?php elseif ($error): ?>
                    <div class="alert alert-error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                    <div class="form-group full">
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
                    <div class="form-group full">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-textarea" placeholder="Tell us about your project..."></textarea>
                    </div>
                    <button type="submit" name="submit_lead" class="btn-submit">Submit Inquiry</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="features-header">
            <h2>Why <span>Sky Padel?</span></h2>
            <p>International standards, local expertise. We bring world-class padel infrastructure to India.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h3 class="feature-title">Premium Quality</h3>
                <p class="feature-desc">FIP certified courts with imported tempered glass and professional-grade artificial turf.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="3" y1="9" x2="21" y2="9"/>
                        <line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                </div>
                <h3 class="feature-title">Turnkey Solutions</h3>
                <p class="feature-desc">End-to-end management from site assessment to installation, lighting, and after-sales.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                </div>
                <h3 class="feature-title">Real-Time Tracking</h3>
                <p class="feature-desc">Dedicated client portal with milestone tracking, documents, and payment management.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <img src="https://cdn.prod.website-files.com/66c705c26941f009cfd3255f/66c70de3185822b627ec80ac_SKYPADEL_INDIA_LOGO.png" alt="Sky Padel India" class="footer-logo">
            <div class="footer-links">
                <a href="#quote" class="footer-link">Get Quote</a>
                <a href="<?php echo SITE_URL; ?>/login.php" class="footer-link">Client Portal</a>
            </div>
            <p class="footer-copy">&copy; <?php echo date('Y'); ?> Sky Padel India. All rights reserved.</p>
        </div>
    </footer>

    <!-- Sketchfab Viewer API -->
    <script src="https://static.sketchfab.com/api/sketchfab-viewer-1.12.1.js"></script>

    <script>
        // Initialize Sketchfab 3D Viewer
        (function() {
            var iframe = document.getElementById('sketchfab-viewer');
            var loading = document.getElementById('courtLoading');
            var uid = 'ef977f0912af4297bd842ab7bb82dde1';

            var client = new Sketchfab(iframe);

            client.init(uid, {
                success: function(api) {
                    api.start();

                    api.addEventListener('viewerready', function() {
                        // Hide loading spinner
                        loading.classList.add('hidden');

                        // Set dark background to match site theme
                        api.setBackground({color: [0.02, 0.03, 0.06]}, function() {
                            console.log('Background set to match site theme');
                        });

                        // Get initial camera position for reference
                        api.getCameraLookAt(function(err, camera) {
                            if (!err) {
                                console.log('Camera ready:', camera);
                            }
                        });
                    });
                },
                error: function() {
                    console.error('Sketchfab viewer failed to load');
                    loading.innerHTML = '<p style="color: var(--text-secondary);">Failed to load 3D model</p>';
                },
                autospin: 0.3,
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
                transparent: 0,
                orbit_constraint_pitch_down: 0,
                orbit_constraint_pitch_up: 1.2
            });
        })();

        // Navbar scroll effect
        const nav = document.getElementById('nav');
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 50);
        });

        // Reveal on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.feature-card, .form-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(24px)';
            el.style.transition = 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
            observer.observe(el);
        });

        // Parallax on balls
        let mx = 0, my = 0, cx = 0, cy = 0;
        document.addEventListener('mousemove', e => {
            mx = e.clientX / window.innerWidth - 0.5;
            my = e.clientY / window.innerHeight - 0.5;
        });

        function animateBalls() {
            cx += (mx - cx) * 0.06;
            cy += (my - cy) * 0.06;
            document.querySelectorAll('.ball').forEach((b, i) => {
                const s = (i + 1) * 12;
                b.style.transform = `translate(${cx * s}px, ${cy * s}px)`;
            });
            requestAnimationFrame(animateBalls);
        }
        animateBalls();
    </script>
</body>
</html>

<?php
/**
 * Sky Padel Client Portal - Login Page
 * Electric Court Design - Fresh & Dynamic
 */
require_once __DIR__ . '/core/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$error = '';
$success = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'timeout':
            $error = 'Your session has expired. Please login again.';
            break;
        case 'logout':
            $success = 'You have been logged out successfully.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Portal Login - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Court Blue */
            --color-primary: #0077B6;
            --color-primary-dark: #005A8C;
            --color-primary-light: #0096C7;
            --color-primary-subtle: #E0F4FF;
            /* Turf Green */
            --color-secondary: #10B981;
            --color-secondary-dark: #059669;
            /* Energy Yellow (padel ball) */
            --color-accent: #FBBF24;
            --color-accent-light: #FDE68A;
            --color-accent-glow: rgba(251, 191, 36, 0.4);
            /* Neutrals */
            --color-bg: #F8FAFC;
            --color-bg-dark: #0F172A;
            --color-surface: #FFFFFF;
            --color-border: #E2E8F0;
            --color-text: #0F172A;
            --color-text-secondary: #475569;
            --color-text-muted: #94A3B8;
            --color-success: #22C55E;
            --color-error: #EF4444;
            --font-display: 'Bebas Neue', sans-serif;
            --font-body: 'Outfit', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body);
            background: var(--color-bg);
            color: var(--color-text);
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Left Panel - Branding with Padel Court */
        .login-branding {
            flex: 1;
            background: linear-gradient(160deg, var(--color-bg-dark) 0%, #1E293B 40%, var(--color-primary-dark) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        /* Animated Padel Court Background */
        .court-container {
            position: absolute;
            width: 500px;
            height: 300px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) perspective(800px) rotateX(55deg) rotateZ(-5deg);
            opacity: 0.25;
            animation: courtFloat 8s ease-in-out infinite;
        }

        @keyframes courtFloat {
            0%, 100% { transform: translate(-50%, -50%) perspective(800px) rotateX(55deg) rotateZ(-5deg); }
            50% { transform: translate(-50%, -52%) perspective(800px) rotateX(53deg) rotateZ(-3deg); }
        }

        /* Padel Court SVG */
        .padel-court {
            width: 100%;
            height: 100%;
        }

        /* Floating Padel Balls */
        .float-ball {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, var(--color-accent-light), var(--color-accent));
            box-shadow: 0 10px 40px var(--color-accent-glow), inset -4px -4px 12px rgba(0,0,0,0.15);
            animation: floatBall 6s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        .float-ball-1 {
            width: 70px;
            height: 70px;
            top: 12%;
            left: 8%;
            animation-delay: 0s;
        }

        .float-ball-2 {
            width: 45px;
            height: 45px;
            top: 65%;
            right: 12%;
            animation-delay: 1.5s;
        }

        .float-ball-3 {
            width: 28px;
            height: 28px;
            bottom: 18%;
            left: 18%;
            animation-delay: 3s;
        }

        .float-ball-4 {
            width: 35px;
            height: 35px;
            top: 35%;
            right: 25%;
            animation-delay: 4.5s;
        }

        @keyframes floatBall {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                box-shadow: 0 10px 40px var(--color-accent-glow), inset -4px -4px 12px rgba(0,0,0,0.15);
            }
            50% {
                transform: translateY(-25px) rotate(180deg);
                box-shadow: 0 25px 50px var(--color-accent-glow), inset -4px -4px 12px rgba(0,0,0,0.15);
            }
        }

        /* Glass Wall Effect Lines */
        .glass-lines {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .glass-line {
            position: absolute;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
            animation: glassShimmer 4s ease-in-out infinite;
        }

        .glass-line-1 {
            width: 2px;
            height: 120%;
            left: 20%;
            top: -10%;
            animation-delay: 0s;
        }

        .glass-line-2 {
            width: 2px;
            height: 120%;
            left: 50%;
            top: -10%;
            animation-delay: 1s;
        }

        .glass-line-3 {
            width: 2px;
            height: 120%;
            left: 80%;
            top: -10%;
            animation-delay: 2s;
        }

        @keyframes glassShimmer {
            0%, 100% { opacity: 0.3; transform: translateY(0); }
            50% { opacity: 0.6; transform: translateY(5px); }
        }

        .branding-content {
            position: relative;
            z-index: 10;
            text-align: center;
            color: white;
        }

        .branding-logo {
            margin-bottom: 32px;
            animation: fadeSlideDown 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .branding-logo img {
            height: 70px;
            width: auto;
        }

        @keyframes fadeSlideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .branding-title {
            font-family: var(--font-display);
            font-size: 3.5rem;
            letter-spacing: 0.1em;
            line-height: 1;
            margin-bottom: 12px;
            text-transform: uppercase;
            background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeSlideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s forwards;
            opacity: 0;
        }

        .branding-subtitle {
            font-size: 1rem;
            opacity: 0.8;
            font-weight: 400;
            letter-spacing: 0.1em;
            animation: fadeSlideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.4s forwards;
            opacity: 0;
        }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .branding-features {
            margin-top: 50px;
            display: flex;
            gap: 32px;
            animation: fadeSlideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.6s forwards;
            opacity: 0;
        }

        .branding-feature {
            text-align: center;
            transition: transform 0.3s ease;
        }

        .branding-feature:hover {
            transform: translateY(-5px);
        }

        .branding-feature-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, rgba(0, 119, 182, 0.3), rgba(16, 185, 129, 0.2));
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .branding-feature:hover .branding-feature-icon {
            background: linear-gradient(135deg, rgba(0, 119, 182, 0.5), rgba(16, 185, 129, 0.4));
            border-color: rgba(255, 255, 255, 0.3);
        }

        .branding-feature-text {
            font-size: 0.85rem;
            opacity: 0.85;
            font-weight: 500;
        }

        /* Right Panel - Login Form */
        .login-form-panel {
            width: 520px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            background: var(--color-surface);
            position: relative;
            animation: slideInRight 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* Accent stripe */
        .login-form-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, var(--color-primary) 0%, var(--color-secondary) 100%);
        }

        .login-header {
            margin-bottom: 36px;
        }

        .login-welcome {
            font-family: var(--font-display);
            font-size: 2.5rem;
            letter-spacing: 0.04em;
            color: var(--color-text);
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .login-description {
            color: var(--color-text-secondary);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--color-text-secondary);
            margin-bottom: 10px;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--color-text);
            background: var(--color-bg);
            border: 2px solid var(--color-border);
            border-radius: 12px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--color-primary);
            background: var(--color-surface);
            box-shadow: 0 0 0 4px var(--color-primary-subtle);
        }

        .form-input::placeholder {
            color: var(--color-text-muted);
        }

        /* OTP Step */
        .otp-step {
            display: none;
        }

        .otp-step.active {
            display: block;
            animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .email-display {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, var(--color-primary-subtle), rgba(16, 185, 129, 0.1));
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid rgba(0, 119, 182, 0.2);
        }

        .email-display-text {
            font-weight: 600;
            color: var(--color-primary-dark);
        }

        .email-display-change {
            background: none;
            border: none;
            color: var(--color-primary);
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            transition: color 0.2s ease;
        }

        .email-display-change:hover {
            color: var(--color-secondary);
        }

        .otp-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 24px;
        }

        .otp-input {
            width: 56px;
            height: 68px;
            text-align: center;
            font-family: var(--font-display);
            font-size: 2rem;
            letter-spacing: 0;
            border: 2px solid var(--color-border);
            border-radius: 12px;
            background: var(--color-bg);
            color: var(--color-text);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .otp-input:focus {
            outline: none;
            border-color: var(--color-primary);
            background: var(--color-surface);
            box-shadow: 0 0 0 4px var(--color-primary-subtle);
            transform: scale(1.08);
        }

        .btn {
            width: 100%;
            padding: 18px 32px;
            font-family: var(--font-body);
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
            transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(0, 119, 182, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 119, 182, 0.45);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .resend-section {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--color-text-muted);
        }

        .resend-link {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .resend-link:hover {
            color: var(--color-secondary);
            text-decoration: underline;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 0.95rem;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }

        .alert-success {
            background: #DCFCE7;
            color: #16A34A;
            border: 1px solid #BBF7D0;
        }

        .login-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--color-text-muted);
        }

        .login-footer a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .login-footer a:hover {
            color: var(--color-secondary);
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .login-branding {
                display: none;
            }
            .login-form-panel {
                width: 100%;
                max-width: 480px;
                margin: 0 auto;
            }
            .login-form-panel::before {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .login-form-panel {
                padding: 40px 24px;
            }
            .otp-input {
                width: 46px;
                height: 56px;
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Left Panel - Branding with Padel Court -->
    <div class="login-branding">
        <!-- Animated Padel Court -->
        <div class="court-container">
            <svg class="padel-court" viewBox="0 0 200 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Court background (blue surface) -->
                <rect x="0" y="0" width="200" height="120" fill="#0077B6" rx="3"/>
                <!-- Outer boundary -->
                <rect x="4" y="4" width="192" height="112" fill="none" stroke="white" stroke-width="2"/>
                <!-- Center line -->
                <line x1="100" y1="4" x2="100" y2="116" stroke="white" stroke-width="2"/>
                <!-- Service lines -->
                <line x1="4" y1="40" x2="100" y2="40" stroke="white" stroke-width="1.5"/>
                <line x1="100" y1="40" x2="196" y2="40" stroke="white" stroke-width="1.5"/>
                <line x1="4" y1="80" x2="100" y2="80" stroke="white" stroke-width="1.5"/>
                <line x1="100" y1="80" x2="196" y2="80" stroke="white" stroke-width="1.5"/>
                <!-- Service boxes center lines -->
                <line x1="52" y1="4" x2="52" y2="40" stroke="white" stroke-width="1.5"/>
                <line x1="148" y1="4" x2="148" y2="40" stroke="white" stroke-width="1.5"/>
                <line x1="52" y1="80" x2="52" y2="116" stroke="white" stroke-width="1.5"/>
                <line x1="148" y1="80" x2="148" y2="116" stroke="white" stroke-width="1.5"/>
                <!-- Net -->
                <line x1="4" y1="60" x2="196" y2="60" stroke="white" stroke-width="3" stroke-dasharray="4 2"/>
                <!-- Glass wall indicators (top and bottom) -->
                <rect x="0" y="0" width="200" height="6" fill="rgba(255,255,255,0.2)" rx="2"/>
                <rect x="0" y="114" width="200" height="6" fill="rgba(255,255,255,0.2)" rx="2"/>
                <!-- Glass wall indicators (sides) -->
                <rect x="0" y="0" width="6" height="120" fill="rgba(255,255,255,0.15)" rx="2"/>
                <rect x="194" y="0" width="6" height="120" fill="rgba(255,255,255,0.15)" rx="2"/>
            </svg>
        </div>

        <!-- Floating Padel Balls -->
        <div class="float-ball float-ball-1"></div>
        <div class="float-ball float-ball-2"></div>
        <div class="float-ball float-ball-3"></div>
        <div class="float-ball float-ball-4"></div>

        <!-- Glass Wall Effect -->
        <div class="glass-lines">
            <div class="glass-line glass-line-1"></div>
            <div class="glass-line glass-line-2"></div>
            <div class="glass-line glass-line-3"></div>
        </div>

        <div class="branding-content">
            <div class="branding-logo">
                <img src="https://cdn.prod.website-files.com/66c705c26941f009cfd3255f/66c70de3185822b627ec80ac_SKYPADEL_INDIA_LOGO.png" alt="Sky Padel India">
            </div>
            <h1 class="branding-title">Client Portal</h1>
            <p class="branding-subtitle">Track your project from quote to completion</p>

            <div class="branding-features">
                <div class="branding-feature">
                    <div class="branding-feature-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <p class="branding-feature-text">Projects</p>
                </div>
                <div class="branding-feature">
                    <div class="branding-feature-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="branding-feature-text">Quotations</p>
                </div>
                <div class="branding-feature">
                    <div class="branding-feature-icon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="branding-feature-text">Payments</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel - Login Form -->
    <div class="login-form-panel">
        <div class="login-header">
            <h2 class="login-welcome">Welcome Back</h2>
            <p class="login-description">Enter your email to access your projects</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <?php echo e($success); ?>
            </div>
        <?php endif; ?>

        <!-- Email Step -->
        <div id="emailStep">
            <form id="emailForm">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" id="emailInput" class="form-input"
                           placeholder="Enter your registered email" required autocomplete="email">
                </div>
                <button type="submit" class="btn btn-primary" id="sendOtpBtn">
                    <span class="btn-text">Continue with Email</span>
                </button>
            </form>
        </div>

        <!-- OTP Step -->
        <div id="otpStep" class="otp-step">
            <div class="email-display">
                <span class="email-display-text" id="displayEmail"></span>
                <button type="button" class="email-display-change" onclick="changeEmail()">Change</button>
            </div>

            <p class="form-label" style="text-align: center; margin-bottom: 16px;">Enter 6-Digit OTP</p>

            <form id="otpForm">
                <div class="otp-container">
                    <input type="text" class="otp-input" maxlength="1" data-index="0" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" data-index="1" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" data-index="2" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" data-index="3" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" data-index="4" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" data-index="5" inputmode="numeric">
                </div>
                <button type="submit" class="btn btn-primary" id="verifyOtpBtn">
                    <span class="btn-text">Verify & Login</span>
                </button>
            </form>

            <div class="resend-section">
                <span id="resendTimer">Resend OTP in <strong id="countdown">60</strong>s</span>
                <a href="#" id="resendLink" style="display:none;" onclick="resendOTP(); return false;" class="resend-link">Resend OTP</a>
            </div>
        </div>

        <div class="login-footer">
            <p>Need help? <a href="mailto:support@skypadel.in">Contact Support</a></p>
        </div>
    </div>

    <script>
        const SITE_URL = '<?php echo SITE_URL; ?>';
        let clientEmail = '';
        let countdownInterval = null;

        // Email Form Submit
        document.getElementById('emailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            sendOTP();
        });

        // OTP Form Submit
        document.getElementById('otpForm').addEventListener('submit', function(e) {
            e.preventDefault();
            verifyOTP();
        });

        // OTP Input Handling
        const otpInputs = document.querySelectorAll('.otp-input');
        otpInputs.forEach((input, index) => {
            input.addEventListener('keyup', function(e) {
                if (this.value.length === 1 && index < 5) {
                    otpInputs[index + 1].focus();
                }
                if (e.key === 'Backspace' && index > 0 && !this.value) {
                    otpInputs[index - 1].focus();
                }
            });
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const digits = paste.replace(/\D/g, '').slice(0, 6);
                digits.split('').forEach((digit, i) => {
                    if (otpInputs[i]) otpInputs[i].value = digit;
                });
                if (digits.length === 6) {
                    verifyOTP();
                }
            });
        });

        function sendOTP() {
            const email = document.getElementById('emailInput').value.trim();
            if (!email) return;

            const btn = document.getElementById('sendOtpBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div> Sending...';

            fetch(SITE_URL + '/api/send-otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-text">Continue with Email</span>';

                if (data.success) {
                    clientEmail = email;
                    document.getElementById('displayEmail').textContent = email;
                    document.getElementById('emailStep').style.display = 'none';
                    document.getElementById('otpStep').classList.add('active');
                    otpInputs[0].focus();
                    startCountdown();

                    // DEV MODE: Show OTP in alert (remove in production!)
                    if (data.debug_otp) {
                        alert('DEV MODE - Your OTP is: ' + data.debug_otp);
                    }
                } else {
                    alert(data.message || 'Failed to send OTP');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-text">Continue with Email</span>';
                alert('Error sending OTP. Please try again.');
            });
        }

        function verifyOTP() {
            const otp = Array.from(otpInputs).map(i => i.value).join('');
            if (otp.length !== 6) {
                alert('Please enter the complete 6-digit OTP');
                return;
            }

            const btn = document.getElementById('verifyOtpBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div> Verifying...';

            fetch(SITE_URL + '/api/verify-otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: clientEmail, otp: otp })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = SITE_URL + '/dashboard.php';
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<span class="btn-text">Verify & Login</span>';
                    alert(data.message || 'Invalid OTP');
                    otpInputs.forEach(i => i.value = '');
                    otpInputs[0].focus();
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-text">Verify & Login</span>';
                alert('Error verifying OTP. Please try again.');
            });
        }

        function changeEmail() {
            document.getElementById('otpStep').classList.remove('active');
            document.getElementById('emailStep').style.display = 'block';
            otpInputs.forEach(i => i.value = '');
            clearInterval(countdownInterval);
        }

        function startCountdown() {
            let seconds = 60;
            document.getElementById('resendTimer').style.display = 'inline';
            document.getElementById('resendLink').style.display = 'none';

            countdownInterval = setInterval(() => {
                seconds--;
                document.getElementById('countdown').textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('resendTimer').style.display = 'none';
                    document.getElementById('resendLink').style.display = 'inline';
                }
            }, 1000);
        }

        function resendOTP() {
            otpInputs.forEach(i => i.value = '');

            fetch(SITE_URL + '/api/send-otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: clientEmail })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    startCountdown();
                    otpInputs[0].focus();
                    // DEV MODE: Show OTP
                    if (data.debug_otp) {
                        alert('DEV MODE - Your OTP is: ' + data.debug_otp);
                    }
                } else {
                    alert(data.message || 'Failed to resend OTP');
                }
            });
        }
    </script>
</body>
</html>

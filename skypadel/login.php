<?php
/**
 * Sky Padel India - Client Portal Login
 * Design: Midnight Court Club - Cinematic Luxury Sports Aesthetic
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&f[]=cabinet-grotesk@400,500,700&display=swap" rel="stylesheet">

    <style>
        :root {
            --noir-deep: #030508;
            --noir-base: #070b12;
            --noir-elevated: #0d1420;
            --noir-surface: #131c2e;
            --noir-glass: rgba(13, 20, 32, 0.9);

            --court-blue: #0088cc;
            --court-blue-bright: #00a8e8;
            --court-blue-glow: rgba(0, 136, 204, 0.5);
            --court-blue-subtle: rgba(0, 136, 204, 0.15);

            --turf-green: #10B981;
            --turf-green-bright: #34D399;
            --turf-green-glow: rgba(16, 185, 129, 0.5);

            --ball-yellow: #FBBF24;
            --ball-yellow-bright: #FDE68A;
            --ball-yellow-glow: rgba(251, 191, 36, 0.6);
            --ball-gradient: linear-gradient(135deg, #FDE68A 0%, #FBBF24 50%, #F59E0B 100%);

            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;

            --font-display: 'Clash Display', 'Bebas Neue', sans-serif;
            --font-body: 'Cabinet Grotesk', 'Space Grotesk', sans-serif;

            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-shine: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);

            --ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1);
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: var(--font-body);
            background: var(--noir-deep);
            color: var(--text-primary);
            line-height: 1.6;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* =====================================================
           BACKGROUND SCENE
        ===================================================== */

        .login-scene {
            position: fixed;
            inset: 0;
            overflow: hidden;
        }

        .scene-gradient {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 120% 120% at 30% 100%, rgba(0, 136, 204, 0.2) 0%, transparent 50%),
                radial-gradient(ellipse 100% 100% at 70% 0%, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse 80% 80% at 50% 50%, rgba(251, 191, 36, 0.05) 0%, transparent 50%),
                var(--noir-deep);
        }

        .scene-court {
            position: absolute;
            inset: 0;
            opacity: 0.03;
            background-image:
                linear-gradient(90deg, var(--text-primary) 1px, transparent 1px),
                linear-gradient(0deg, var(--text-primary) 1px, transparent 1px);
            background-size: 80px 80px;
            transform: perspective(800px) rotateX(55deg) translateY(30%);
            transform-origin: center bottom;
        }

        .scene-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(0, 136, 204, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 136, 204, 0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% { background-position: 0 0; }
            100% { background-position: 50px 50px; }
        }

        .light-beam {
            position: absolute;
            width: 400px;
            height: 200vh;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02) 0%, transparent 100%);
            transform-origin: top center;
            pointer-events: none;
        }

        .light-beam-1 {
            left: 5%;
            top: -100%;
            transform: rotate(-20deg);
            animation: beamSway 12s ease-in-out infinite;
        }

        .light-beam-2 {
            right: 10%;
            top: -100%;
            transform: rotate(25deg);
            animation: beamSway 15s ease-in-out infinite reverse;
        }

        @keyframes beamSway {
            0%, 100% { transform: rotate(-20deg); opacity: 0.5; }
            50% { transform: rotate(-15deg); opacity: 1; }
        }

        /* =====================================================
           FLOATING BALLS
        ===================================================== */

        .floating-ball {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            animation: floatBall 10s ease-in-out infinite;
        }

        .ball-sphere {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background:
                radial-gradient(ellipse 70% 70% at 30% 25%, rgba(255,255,255,0.9) 0%, transparent 50%),
                radial-gradient(ellipse 90% 90% at 70% 75%, rgba(0,0,0,0.4) 0%, transparent 50%),
                radial-gradient(circle at 40% 35%, #FDE68A 0%, #FBBF24 40%, #F59E0B 100%);
            box-shadow:
                0 30px 80px var(--ball-yellow-glow),
                inset 0 -10px 30px rgba(0,0,0,0.2);
        }

        .ball-1 {
            width: 120px;
            height: 120px;
            top: 10%;
            left: 8%;
            animation-delay: 0s;
        }

        .ball-2 {
            width: 70px;
            height: 70px;
            top: 60%;
            left: 15%;
            animation-delay: 2s;
        }

        .ball-3 {
            width: 90px;
            height: 90px;
            top: 20%;
            right: 10%;
            animation-delay: 1s;
        }

        .ball-4 {
            width: 50px;
            height: 50px;
            bottom: 15%;
            right: 20%;
            animation-delay: 3s;
        }

        .ball-5 {
            width: 40px;
            height: 40px;
            bottom: 30%;
            left: 5%;
            animation-delay: 4s;
        }

        @keyframes floatBall {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(20px, -30px) rotate(90deg); }
            50% { transform: translate(-10px, -15px) rotate(180deg); }
            75% { transform: translate(-20px, -40px) rotate(270deg); }
        }

        /* =====================================================
           LOGIN CONTAINER
        ===================================================== */

        .login-container {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .login-card {
            width: 100%;
            max-width: 480px;
            background: var(--noir-glass);
            backdrop-filter: blur(30px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            padding: 56px;
            position: relative;
            overflow: hidden;
            box-shadow:
                0 50px 100px -20px rgba(0, 0, 0, 0.5),
                0 0 80px rgba(0, 136, 204, 0.1);
            animation: cardEnter 0.8s var(--ease-out-expo) forwards;
            opacity: 0;
            transform: translateY(30px) scale(0.98);
        }

        @keyframes cardEnter {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--court-blue), var(--ball-yellow), var(--turf-green));
        }

        .login-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--glass-shine);
            pointer-events: none;
        }

        /* =====================================================
           LOGO & HEADER
        ===================================================== */

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-logo {
            height: 56px;
            margin-bottom: 24px;
            animation: logoEnter 0.6s var(--ease-out-expo) 0.2s forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes logoEnter {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-title {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--text-primary), var(--court-blue-bright));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-subtitle {
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        /* =====================================================
           FORMS
        ===================================================== */

        .login-form {
            animation: formEnter 0.6s var(--ease-out-expo) 0.3s forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes formEnter {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .form-input {
            width: 100%;
            padding: 18px 24px;
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--text-primary);
            background: var(--noir-elevated);
            border: 2px solid var(--glass-border);
            border-radius: 14px;
            transition: all 0.3s var(--ease-out-expo);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--court-blue);
            box-shadow: 0 0 0 4px var(--court-blue-subtle);
            background: var(--noir-surface);
        }

        /* OTP Step */
        .otp-step {
            display: none;
        }

        .otp-step.active {
            display: block;
            animation: slideIn 0.5s var(--ease-out-expo);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .email-display {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--noir-elevated);
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 28px;
            border: 1px solid var(--glass-border);
        }

        .email-display-text {
            font-weight: 600;
            color: var(--court-blue-bright);
        }

        .email-display-change {
            background: none;
            border: none;
            color: var(--ball-yellow);
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .email-display-change:hover {
            color: var(--ball-yellow-bright);
        }

        .otp-container {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 28px;
        }

        .otp-input {
            width: 56px;
            height: 68px;
            text-align: center;
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 500;
            color: var(--text-primary);
            background: var(--noir-elevated);
            border: 2px solid var(--glass-border);
            border-radius: 14px;
            transition: all 0.3s var(--ease-out-expo);
        }

        .otp-input:focus {
            outline: none;
            border-color: var(--ball-yellow);
            box-shadow: 0 0 0 4px var(--ball-yellow-glow);
            background: var(--noir-surface);
            transform: scale(1.05);
        }

        .otp-input.filled {
            border-color: var(--turf-green);
            background: rgba(16, 185, 129, 0.1);
        }

        .btn {
            width: 100%;
            padding: 18px 32px;
            font-family: var(--font-body);
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s var(--ease-out-expo);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s var(--ease-out-expo);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--ball-gradient);
            color: var(--noir-deep);
            box-shadow: 0 8px 30px var(--ball-yellow-glow);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 50px var(--ball-yellow-glow);
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
            color: var(--text-muted);
        }

        .resend-link {
            color: var(--ball-yellow);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.3s;
        }

        .resend-link:hover {
            color: var(--ball-yellow-bright);
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            animation: alertEnter 0.4s var(--ease-out-expo);
        }

        @keyframes alertEnter {
            from { opacity: 0; transform: translateY(-10px); }
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: var(--turf-green-bright);
        }

        .alert svg {
            flex-shrink: 0;
        }

        .login-footer {
            margin-top: 32px;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .login-footer a {
            color: var(--court-blue-bright);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .login-footer a:hover {
            color: var(--ball-yellow);
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

        /* =====================================================
           BACK TO HOME
        ===================================================== */

        .back-home {
            position: fixed;
            top: 32px;
            left: 32px;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            padding: 12px 20px;
            background: var(--noir-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 100px;
            transition: all 0.3s var(--ease-out-expo);
        }

        .back-home:hover {
            color: var(--text-primary);
            border-color: var(--court-blue);
            transform: translateX(-5px);
        }

        .back-home svg {
            transition: transform 0.3s var(--ease-out-expo);
        }

        .back-home:hover svg {
            transform: translateX(-3px);
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 600px) {
            .login-container {
                padding: 24px;
            }

            .login-card {
                padding: 40px 28px;
                border-radius: 24px;
            }

            .otp-input {
                width: 46px;
                height: 56px;
                font-size: 1.5rem;
            }

            .ball-1, .ball-3 {
                display: none;
            }

            .back-home {
                top: 16px;
                left: 16px;
                padding: 10px 16px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <!-- Background Scene -->
    <div class="login-scene">
        <div class="scene-gradient"></div>
        <div class="scene-court"></div>
        <div class="scene-grid"></div>
        <div class="light-beam light-beam-1"></div>
        <div class="light-beam light-beam-2"></div>

        <!-- Floating Balls -->
        <div class="floating-ball ball-1"><div class="ball-sphere"></div></div>
        <div class="floating-ball ball-2"><div class="ball-sphere"></div></div>
        <div class="floating-ball ball-3"><div class="ball-sphere"></div></div>
        <div class="floating-ball ball-4"><div class="ball-sphere"></div></div>
        <div class="floating-ball ball-5"><div class="ball-sphere"></div></div>
    </div>

    <!-- Back to Home -->
    <a href="<?php echo SITE_URL; ?>" class="back-home">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Back to Home
    </a>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="https://cdn.prod.website-files.com/66c705c26941f009cfd3255f/66c70de3185822b627ec80ac_SKYPADEL_INDIA_LOGO.png" alt="Sky Padel India" class="login-logo">
                <h1 class="login-title" id="loginTitle">Client Portal</h1>
                <p class="login-subtitle" id="loginSubtitle">Sign in to track your project</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Email Step -->
            <div id="emailStep" class="login-form">
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

                <p class="form-label" style="text-align: center; margin-bottom: 16px;">Enter 6-Digit Code</p>

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
                        <span class="btn-text">Verify & Sign In</span>
                    </button>
                </form>

                <div class="resend-section">
                    <span id="resendTimer">Resend code in <strong id="countdown">60</strong>s</span>
                    <a href="#" id="resendLink" style="display:none;" onclick="resendOTP(); return false;" class="resend-link">Resend Code</a>
                </div>
            </div>

            <div class="login-footer">
                <p>Need help? <a href="mailto:support@skypadel.in">Contact Support</a></p>
            </div>
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
            input.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 1);

                if (this.value) {
                    this.classList.add('filled');
                    if (index < 5) {
                        otpInputs[index + 1].focus();
                    }
                } else {
                    this.classList.remove('filled');
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                    otpInputs[index - 1].value = '';
                    otpInputs[index - 1].classList.remove('filled');
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const digits = paste.replace(/\D/g, '').slice(0, 6);
                digits.split('').forEach((digit, i) => {
                    if (otpInputs[i]) {
                        otpInputs[i].value = digit;
                        otpInputs[i].classList.add('filled');
                    }
                });
                if (digits.length === 6) {
                    setTimeout(() => verifyOTP(), 300);
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

                    // Update header
                    document.getElementById('loginTitle').textContent = 'Enter Code';
                    document.getElementById('loginSubtitle').textContent = 'Check your email for the code';

                    otpInputs[0].focus();
                    startCountdown();

                    // DEV MODE: Show OTP in alert
                    if (data.debug_otp) {
                        alert('DEV MODE - Your OTP is: ' + data.debug_otp);
                    }
                } else {
                    showError(data.message || 'Failed to send OTP');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-text">Continue with Email</span>';
                showError('Error sending OTP. Please try again.');
            });
        }

        function verifyOTP() {
            const otp = Array.from(otpInputs).map(i => i.value).join('');
            if (otp.length !== 6) {
                showError('Please enter the complete 6-digit code');
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
                    btn.innerHTML = '<span class="btn-text">Verify & Sign In</span>';
                    showError(data.message || 'Invalid code');
                    otpInputs.forEach(i => {
                        i.value = '';
                        i.classList.remove('filled');
                    });
                    otpInputs[0].focus();
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-text">Verify & Sign In</span>';
                showError('Error verifying code. Please try again.');
            });
        }

        function changeEmail() {
            document.getElementById('otpStep').classList.remove('active');
            document.getElementById('emailStep').style.display = 'block';
            document.getElementById('loginTitle').textContent = 'Client Portal';
            document.getElementById('loginSubtitle').textContent = 'Sign in to track your project';
            otpInputs.forEach(i => {
                i.value = '';
                i.classList.remove('filled');
            });
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
            otpInputs.forEach(i => {
                i.value = '';
                i.classList.remove('filled');
            });

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
                    if (data.debug_otp) {
                        alert('DEV MODE - Your OTP is: ' + data.debug_otp);
                    }
                } else {
                    showError(data.message || 'Failed to resend code');
                }
            });
        }

        function showError(message) {
            // Remove existing error alerts
            const existingAlerts = document.querySelectorAll('.alert-error');
            existingAlerts.forEach(a => a.remove());

            // Create new error alert
            const alert = document.createElement('div');
            alert.className = 'alert alert-error';
            alert.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                ${message}
            `;

            // Insert after header
            const header = document.querySelector('.login-header');
            header.insertAdjacentElement('afterend', alert);

            // Auto-remove after 5 seconds
            setTimeout(() => alert.remove(), 5000);
        }
    </script>
</body>
</html>

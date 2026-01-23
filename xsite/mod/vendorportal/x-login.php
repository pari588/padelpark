<?php
/**
 * Vendor Portal - Login Page
 */

include("x-vendorportal.inc.php");

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xAction']) && $_POST['xAction'] === 'LOGIN') {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['err' => 1, 'msg' => 'Please enter both email and password']);
        exit;
    }

    $result = vpLogin($email, $password);
    echo json_encode($result);
    exit;
}

// Redirect if already logged in
if (vpIsLoggedIn()) {
    header('Location: ' . VP_BASEURL . '/vendorportal/dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Portal Login | GamePark</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Outfit:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITEURL; ?>/mod/vendorportal/inc/css/vendorportal.css">

    <style>
        /* Additional login-specific styles */
        .login-page {
            overflow-x: hidden;
        }

        .login-branding h1 {
            background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-branding h1 span {
            background: linear-gradient(135deg, var(--vp-accent), var(--vp-accent-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--vp-slate-400);
            cursor: pointer;
            padding: 0;
        }

        .password-toggle:hover {
            color: var(--vp-primary);
        }

        .login-error {
            display: none;
            background: var(--vp-danger-bg);
            color: var(--vp-danger);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            font-size: 0.875rem;
            align-items: center;
            gap: var(--space-sm);
        }

        .login-error.show {
            display: flex;
        }

        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .floating-shape {
            position: absolute;
            opacity: 0.1;
            pointer-events: none;
        }

        .shape-1 {
            top: 10%;
            right: 10%;
            width: 300px;
            height: 300px;
            border: 2px solid var(--vp-accent);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .shape-2 {
            bottom: 20%;
            left: 5%;
            width: 200px;
            height: 200px;
            border: 2px solid var(--vp-primary-light);
            transform: rotate(45deg);
            animation: float 10s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
    </style>
</head>
<body class="login-page">
    <!-- Floating shapes -->
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>

    <div class="login-container">
        <div class="login-branding">
            <h1>Welcome to<br><span>Vendor Portal</span></h1>
            <p>Access your quotations, submit proposals, and manage your business relationship with GamePark all in one place.</p>

            <div class="login-features">
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <span>Submit quotations for RFQs</span>
                </div>
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span>Track your proposals in real-time</span>
                </div>
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <span>Manage awarded purchase orders</span>
                </div>
                <div class="login-feature">
                    <div class="login-feature-icon">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <span>Secure & compliant platform</span>
                </div>
            </div>
        </div>

        <div class="login-card">
            <div class="login-card-header">
                <div class="logo">VP</div>
                <h2>Sign In</h2>
                <p>Enter your credentials to access the portal</p>
            </div>

            <div class="login-error" id="loginError">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorMessage"></span>
            </div>

            <form id="loginForm" method="POST">
                <input type="hidden" name="xAction" value="LOGIN">

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div class="input-icon-wrapper">
                        <input type="email" name="email" class="form-control" placeholder="vendor@company.com" required autocomplete="email">
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-icon-wrapper">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required autocomplete="current-password" style="padding-right: 3rem;">
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="<?php echo VP_BASEURL; ?>/vendorportal/forgot-password" class="form-link">Forgot password?</a>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" id="loginBtn">
                    <i class="fas fa-arrow-right"></i>
                    Sign In
                </button>
            </form>

            <div style="text-align: center; margin-top: var(--space-xl); padding-top: var(--space-lg); border-top: 1px solid var(--vp-slate-100);">
                <p class="text-muted text-sm mb-0">
                    New vendor? <a href="<?php echo VP_BASEURL; ?>/vendorportal/register" class="form-link">Register here</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            const btn = $('#loginBtn');
            const errorBox = $('#loginError');

            btn.addClass('btn-loading').prop('disabled', true);
            errorBox.removeClass('show');

            $.ajax({
                url: '<?php echo VP_BASEURL; ?>/vendorportal/login',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.err === 0) {
                        window.location.href = '<?php echo VP_BASEURL; ?>/vendorportal/dashboard';
                    } else {
                        $('#errorMessage').text(response.msg);
                        errorBox.addClass('show');
                        btn.removeClass('btn-loading').prop('disabled', false);
                    }
                },
                error: function() {
                    $('#errorMessage').text('Connection error. Please try again.');
                    errorBox.addClass('show');
                    btn.removeClass('btn-loading').prop('disabled', false);
                }
            });
        });
    </script>
</body>
</html>

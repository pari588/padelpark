<?php
/**
 * Vendor Portal - Forgot Password
 * Password recovery for vendor portal users
 */

include_once("x-vendorportal.inc.php");

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xAction']) && $_POST['xAction'] === 'FORGOT_PASSWORD') {
    header('Content-Type: application/json');

    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['err' => 1, 'msg' => 'Please enter your email address']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['err' => 1, 'msg' => 'Please enter a valid email address']);
        exit;
    }

    // Check if email exists in portal users
    $DB->sql = "SELECT u.userID, u.vendorID, u.fullName, v.legalName
                FROM mx_vendor_portal_user u
                JOIN mx_vendor_onboarding v ON u.vendorID = v.vendorID
                WHERE u.email = ? AND u.status = 1";
    $DB->vals = [$email];
    $DB->types = "s";
    $user = $DB->dbRow();

    if (!$user) {
        // For security, don't reveal if email exists or not
        echo json_encode(['err' => 0, 'msg' => 'If this email is registered, you will receive a password reset link shortly.']);
        exit;
    }

    // Generate secure reset token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Store reset token
    $DB->sql = "UPDATE mx_vendor_portal_user
                SET passwordResetToken = ?, passwordResetExpiry = ?
                WHERE userID = ?";
    $DB->vals = [$token, $expiry, $user['userID']];
    $DB->types = "ssi";
    $DB->dbQuery();

    // In production, send email here. For now, log the reset link
    $resetLink = SITEURL . "vendorportal/reset-password?token=" . $token;

    // TODO: Send email with $resetLink to $email
    // For development, we'll just show success

    echo json_encode([
        'err' => 0,
        'msg' => 'If this email is registered, you will receive a password reset link shortly.',
        'debug_link' => $resetLink // Remove in production
    ]);
    exit;
}

// Redirect if already logged in
if (vpIsLoggedIn()) {
    header('Location: ' . SITEURL . '/vendorportal/dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Vendor Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a5f7a;
            --primary-dark: #0f3d4d;
            --primary-light: #2a7f9a;
            --accent: #f0a500;
            --text: #1e293b;
            --text-muted: #64748b;
            --bg: #f8fafc;
            --white: #ffffff;
            --success: #059669;
            --error: #dc2626;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            color: var(--text);
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background:
                radial-gradient(ellipse at 30% 20%, rgba(26, 95, 122, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 70% 80%, rgba(240, 165, 0, 0.05) 0%, transparent 50%),
                linear-gradient(180deg, var(--bg) 0%, #e2e8f0 100%);
            position: relative;
            overflow: hidden;
        }

        .page-wrapper::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(26, 95, 122, 0.03) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 20s ease-in-out infinite;
        }

        .page-wrapper::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(240, 165, 0, 0.04) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 15s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(5deg); }
            66% { transform: translate(-20px, 20px) rotate(-5deg); }
        }

        .container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            box-shadow:
                0 10px 40px rgba(26, 95, 122, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            position: relative;
        }

        .brand-icon i {
            font-size: 24px;
            color: white;
        }

        .brand-icon::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, transparent 50%);
        }

        .brand h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .brand p {
            font-size: 14px;
            color: var(--text-muted);
        }

        .card {
            background: var(--white);
            border-radius: 20px;
            box-shadow:
                0 1px 3px rgba(0, 0, 0, 0.04),
                0 6px 16px rgba(0, 0, 0, 0.04),
                0 24px 60px rgba(26, 95, 122, 0.06);
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .card-body {
            padding: 36px 32px;
        }

        .icon-header {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, rgba(26, 95, 122, 0.1) 0%, rgba(26, 95, 122, 0.05) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            position: relative;
        }

        .icon-header i {
            font-size: 28px;
            color: var(--primary);
        }

        .icon-header::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px dashed rgba(26, 95, 122, 0.2);
            animation: spin 30s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .form-intro {
            text-align: center;
            margin-bottom: 28px;
        }

        .form-intro h2 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }

        .form-intro p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            transition: color 0.2s ease;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 48px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text);
            background: var(--bg);
            border: 2px solid transparent;
            border-radius: 12px;
            transition: all 0.25s ease;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:hover {
            background: #f1f5f9;
        }

        .form-control:focus {
            outline: none;
            background: var(--white);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 95, 122, 0.1);
        }

        .form-control:focus + i,
        .input-wrapper:focus-within i {
            color: var(--primary);
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
            align-items: flex-start;
            gap: 12px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert.show { display: flex; }

        .alert i {
            font-size: 18px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px 24px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            color: var(--white);
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn:hover::before {
            opacity: 1;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26, 95, 122, 0.35);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn span, .btn i {
            position: relative;
            z-index: 1;
        }

        .card-footer {
            padding: 20px 32px;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }

        .card-footer a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .card-footer a:hover {
            color: var(--primary-dark);
            gap: 10px;
        }

        .card-footer a i {
            font-size: 12px;
        }

        /* Success State */
        .success-state {
            text-align: center;
            padding: 20px 0;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            animation: successPop 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes successPop {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        .success-icon i {
            font-size: 36px;
            color: var(--success);
        }

        .success-state h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 12px;
        }

        .success-state p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
            max-width: 300px;
            margin: 0 auto 24px;
        }

        .debug-link {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 12px;
            margin-top: 20px;
            font-size: 12px;
            color: #92400e;
            word-break: break-all;
        }

        .debug-link strong {
            display: block;
            margin-bottom: 6px;
            color: #78350f;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .page-wrapper {
                padding: 24px 16px;
            }

            .card-body {
                padding: 28px 24px;
            }

            .card-footer {
                padding: 16px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="container">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h1>Vendor Portal</h1>
                <p>Password Recovery</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="formView">
                        <div class="icon-header">
                            <i class="fas fa-key"></i>
                        </div>

                        <div class="form-intro">
                            <h2>Forgot your password?</h2>
                            <p>No worries! Enter your registered email and we'll send you instructions to reset your password.</p>
                        </div>

                        <div class="alert alert-error" id="errorAlert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span id="errorMessage"></span>
                        </div>

                        <form id="forgotForm" method="POST">
                            <input type="hidden" name="xAction" value="FORGOT_PASSWORD">

                            <div class="form-group">
                                <label>Email Address</label>
                                <div class="input-wrapper">
                                    <input type="email" name="email" class="form-control" placeholder="vendor@company.com" required autofocus>
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>

                            <button type="submit" class="btn" id="submitBtn">
                                <i class="fas fa-paper-plane"></i>
                                <span>Send Reset Link</span>
                            </button>
                        </form>
                    </div>

                    <div id="successView" style="display: none;">
                        <div class="success-state">
                            <div class="success-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <h2>Check Your Email</h2>
                            <p>If an account exists with this email, you will receive password reset instructions shortly.</p>

                            <a href="<?php echo VP_BASEURL; ?>/vendorportal/login" class="btn">
                                <i class="fas fa-arrow-left"></i>
                                <span>Back to Login</span>
                            </a>

                            <div class="debug-link" id="debugLink" style="display: none;">
                                <strong>Development Only - Reset Link:</strong>
                                <span id="resetLinkText"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer" id="footerLinks">
                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/login">
                        <i class="fas fa-arrow-left"></i>
                        Back to Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#forgotForm').on('submit', function(e) {
            e.preventDefault();

            var btn = $('#submitBtn');
            var errorBox = $('#errorAlert');

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <span>Sending...</span>');
            errorBox.removeClass('show');

            $.ajax({
                url: '<?php echo VP_BASEURL; ?>/vendorportal/forgot-password',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.err === 0) {
                        $('#formView').fadeOut(300, function() {
                            $('#successView').fadeIn(300);
                            $('#footerLinks').hide();
                        });

                        // Show debug link in development
                        if (response.debug_link) {
                            $('#resetLinkText').text(response.debug_link);
                            $('#debugLink').show();
                        }
                    } else {
                        $('#errorMessage').text(response.msg);
                        errorBox.addClass('show');
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> <span>Send Reset Link</span>');
                    }
                },
                error: function() {
                    $('#errorMessage').text('Connection error. Please try again.');
                    errorBox.addClass('show');
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> <span>Send Reset Link</span>');
                }
            });
        });
    </script>
</body>
</html>

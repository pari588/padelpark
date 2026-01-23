<?php
/**
 * Vendor Portal - Reset Password
 * Password reset form after clicking email link
 */

include_once("x-vendorportal.inc.php");

$token = $_GET['token'] ?? '';
$validToken = false;
$userEmail = '';

// Validate token
if (!empty($token)) {
    $DB->sql = "SELECT u.userID, u.email, u.passwordResetExpiry
                FROM mx_vendor_portal_user u
                WHERE u.passwordResetToken = ? AND u.status = 1";
    $DB->vals = [$token];
    $DB->types = "s";
    $user = $DB->dbRow();

    if ($user && strtotime($user['passwordResetExpiry']) > time()) {
        $validToken = true;
        $userEmail = $user['email'];
    }
}

// Handle password reset POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xAction']) && $_POST['xAction'] === 'RESET_PASSWORD') {
    header('Content-Type: application/json');

    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($token) || empty($password)) {
        echo json_encode(['err' => 1, 'msg' => 'Invalid request']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['err' => 1, 'msg' => 'Password must be at least 6 characters']);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['err' => 1, 'msg' => 'Passwords do not match']);
        exit;
    }

    // Validate token again
    $DB->sql = "SELECT userID FROM mx_vendor_portal_user
                WHERE passwordResetToken = ? AND passwordResetExpiry > NOW() AND status = 1";
    $DB->vals = [$token];
    $DB->types = "s";
    $user = $DB->dbRow();

    if (!$user) {
        echo json_encode(['err' => 1, 'msg' => 'Invalid or expired reset link. Please request a new one.']);
        exit;
    }

    // Update password and clear token
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $DB->sql = "UPDATE mx_vendor_portal_user
                SET passwordHash = ?, passwordResetToken = NULL, passwordResetExpiry = NULL
                WHERE userID = ?";
    $DB->vals = [$passwordHash, $user['userID']];
    $DB->types = "si";

    if ($DB->dbQuery()) {
        echo json_encode(['err' => 0, 'msg' => 'Password reset successful! You can now login.']);
    } else {
        echo json_encode(['err' => 1, 'msg' => 'Failed to reset password. Please try again.']);
    }
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
        }

        .container {
            width: 100%;
            max-width: 440px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
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
            box-shadow: 0 10px 40px rgba(26, 95, 122, 0.3);
        }

        .brand-icon i { font-size: 24px; color: white; }

        .brand h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
        }

        .brand p {
            font-size: 14px;
            color: var(--text-muted);
        }

        .card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 24px 60px rgba(26, 95, 122, 0.06);
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .card-body { padding: 36px 32px; }

        .icon-header {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, rgba(26, 95, 122, 0.1) 0%, rgba(26, 95, 122, 0.05) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .icon-header i { font-size: 28px; color: var(--primary); }

        .icon-header.error { background: linear-gradient(135deg, rgba(220, 38, 38, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%); }
        .icon-header.error i { color: var(--error); }

        .form-intro {
            text-align: center;
            margin-bottom: 28px;
        }

        .form-intro h2 { font-size: 18px; font-weight: 600; color: var(--text); margin-bottom: 8px; }
        .form-intro p { font-size: 14px; color: var(--text-muted); }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 8px; }

        .input-wrapper { position: relative; }
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

        .form-control:focus {
            outline: none;
            background: var(--white);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(26, 95, 122, 0.1);
        }

        .form-control:focus + i { color: var(--primary); }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
            align-items: flex-start;
            gap: 12px;
        }

        .alert.show { display: flex; }
        .alert i { font-size: 18px; flex-shrink: 0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }

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
            text-decoration: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(26, 95, 122, 0.35);
        }

        .btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none !important; }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
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
        }

        .success-state { text-align: center; padding: 20px 0; }

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

        .success-icon i { font-size: 36px; color: var(--success); }
        .success-state h2 { font-size: 20px; font-weight: 700; color: var(--text); margin-bottom: 12px; }
        .success-state p { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; }
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
                <p>Password Reset</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (!$validToken): ?>
                        <!-- Invalid/Expired Token -->
                        <div class="icon-header error">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="form-intro">
                            <h2>Invalid or Expired Link</h2>
                            <p>This password reset link is invalid or has expired. Please request a new password reset.</p>
                        </div>
                        <a href="<?php echo VP_BASEURL; ?>/vendorportal/forgot-password" class="btn">
                            <i class="fas fa-redo"></i>
                            Request New Reset Link
                        </a>
                    <?php else: ?>
                        <div id="formView">
                            <div class="icon-header">
                                <i class="fas fa-lock"></i>
                            </div>

                            <div class="form-intro">
                                <h2>Create New Password</h2>
                                <p>Enter a new password for <strong><?php echo htmlspecialchars($userEmail); ?></strong></p>
                            </div>

                            <div class="alert alert-error" id="errorAlert">
                                <i class="fas fa-exclamation-circle"></i>
                                <span id="errorMessage"></span>
                            </div>

                            <form id="resetForm" method="POST">
                                <input type="hidden" name="xAction" value="RESET_PASSWORD">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                                <div class="form-group">
                                    <label>New Password</label>
                                    <div class="input-wrapper">
                                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <div class="input-wrapper">
                                        <input type="password" name="confirmPassword" class="form-control" placeholder="Confirm new password" required>
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>

                                <button type="submit" class="btn" id="submitBtn">
                                    <i class="fas fa-save"></i>
                                    Reset Password
                                </button>
                            </form>
                        </div>

                        <div id="successView" style="display: none;">
                            <div class="success-state">
                                <div class="success-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <h2>Password Reset Complete</h2>
                                <p>Your password has been successfully reset. You can now login with your new password.</p>
                                <a href="<?php echo VP_BASEURL; ?>/vendorportal/login" class="btn">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Sign In Now
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer">
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
        $('#resetForm').on('submit', function(e) {
            e.preventDefault();

            var btn = $('#submitBtn');
            var errorBox = $('#errorAlert');
            var password = $('input[name="password"]').val();
            var confirmPassword = $('input[name="confirmPassword"]').val();

            if (password !== confirmPassword) {
                $('#errorMessage').text('Passwords do not match');
                errorBox.addClass('show');
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Resetting...');
            errorBox.removeClass('show');

            $.ajax({
                url: '<?php echo VP_BASEURL; ?>/vendorportal/reset-password',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.err === 0) {
                        $('#formView').fadeOut(300, function() {
                            $('#successView').fadeIn(300);
                        });
                    } else {
                        $('#errorMessage').text(response.msg);
                        errorBox.addClass('show');
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Reset Password');
                    }
                },
                error: function() {
                    $('#errorMessage').text('Connection error. Please try again.');
                    errorBox.addClass('show');
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Reset Password');
                }
            });
        });
    </script>
</body>
</html>

<?php
/**
 * Vendor Portal - Registration Page
 * New vendors can register here
 */

include_once("x-vendorportal.inc.php");

// Handle registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xAction']) && $_POST['xAction'] === 'REGISTER') {
    header('Content-Type: application/json');

    $legalName = trim($_POST['legalName'] ?? '');
    $tradeName = trim($_POST['tradeName'] ?? '');
    $vendorType = trim($_POST['vendorType'] ?? 'Goods');
    $contactEmail = trim($_POST['contactEmail'] ?? '');
    $contactPhone = trim($_POST['contactPhone'] ?? '');
    $contactPersonName = trim($_POST['contactPersonName'] ?? '');
    $contactDesignation = trim($_POST['contactDesignation'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $gstNumber = trim($_POST['gstNumber'] ?? '');
    $panNumber = trim($_POST['panNumber'] ?? '');
    $registeredAddress = trim($_POST['registeredAddress'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');

    // Validation
    if (empty($legalName) || empty($contactEmail) || empty($contactPhone) || empty($contactPersonName) || empty($password)) {
        echo json_encode(['err' => 1, 'msg' => 'Please fill in all required fields']);
        exit;
    }

    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['err' => 1, 'msg' => 'Please enter a valid email address']);
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

    // Check if email already exists
    $DB->sql = "SELECT vendorID FROM mx_vendor_onboarding WHERE contactEmail = ? AND status = 1";
    $DB->vals = [$contactEmail];
    $DB->types = "s";
    if ($DB->dbRow()) {
        echo json_encode(['err' => 1, 'msg' => 'A vendor with this email already exists']);
        exit;
    }

    // Generate vendor code
    $prefix = "VND-" . date("Ymd") . "-";
    $DB->sql = "SELECT vendorCode FROM mx_vendor_onboarding WHERE vendorCode LIKE ? ORDER BY vendorID DESC LIMIT 1";
    $DB->vals = [$prefix . "%"];
    $DB->types = "s";
    $lastVendor = $DB->dbRow();
    if ($lastVendor) {
        $lastNum = intval(substr($lastVendor["vendorCode"], -4));
        $newNum = $lastNum + 1;
    } else {
        $newNum = 1;
    }
    $vendorCode = $prefix . str_pad($newNum, 4, "0", STR_PAD_LEFT);

    // Insert vendor
    $DB->sql = "INSERT INTO mx_vendor_onboarding
                (vendorCode, legalName, tradeName, vendorType, contactEmail, contactPhone, contactPersonName,
                 contactDesignation, gstNumber, panNumber, registeredAddress, city, state, pincode,
                 vendorStatus, approvalStatus, registrationSource, status, created)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending', 'Public', 1, NOW())";
    $DB->vals = [$vendorCode, $legalName, $tradeName, $vendorType, $contactEmail, $contactPhone, $contactPersonName,
                 $contactDesignation, $gstNumber, $panNumber ?: 'PENDING', $registeredAddress ?: 'Not provided', $city, $state, $pincode];
    $DB->types = "ssssssssssssss";

    if ($DB->dbQuery()) {
        $vendorID = $DB->insert_id;

        // Create portal user account
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $DB->sql = "INSERT INTO mx_vendor_portal_user
                    (vendorID, email, username, passwordHash, fullName, isActive, status, created)
                    VALUES (?, ?, ?, ?, ?, 0, 1, NOW())";
        $DB->vals = [$vendorID, $contactEmail, $contactEmail, $passwordHash, $contactPersonName];
        $DB->types = "issss";
        $DB->dbQuery();

        echo json_encode([
            'err' => 0,
            'msg' => 'Registration successful! Your application is pending approval.',
            'vendorCode' => $vendorCode
        ]);
    } else {
        echo json_encode(['err' => 1, 'msg' => 'Registration failed. Please try again.']);
    }
    exit;
}

// Redirect if already logged in
if (vpIsLoggedIn()) {
    header('Location: ' . SITEURL . '/vendorportal/dashboard');
    exit;
}

// State options
$states = ["" => "-- Select State --", "Andhra Pradesh" => "Andhra Pradesh", "Arunachal Pradesh" => "Arunachal Pradesh",
           "Assam" => "Assam", "Bihar" => "Bihar", "Chhattisgarh" => "Chhattisgarh", "Delhi" => "Delhi",
           "Goa" => "Goa", "Gujarat" => "Gujarat", "Haryana" => "Haryana", "Himachal Pradesh" => "Himachal Pradesh",
           "Jharkhand" => "Jharkhand", "Karnataka" => "Karnataka", "Kerala" => "Kerala", "Madhya Pradesh" => "Madhya Pradesh",
           "Maharashtra" => "Maharashtra", "Manipur" => "Manipur", "Meghalaya" => "Meghalaya", "Mizoram" => "Mizoram",
           "Nagaland" => "Nagaland", "Odisha" => "Odisha", "Punjab" => "Punjab", "Rajasthan" => "Rajasthan",
           "Sikkim" => "Sikkim", "Tamil Nadu" => "Tamil Nadu", "Telangana" => "Telangana", "Tripura" => "Tripura",
           "Uttar Pradesh" => "Uttar Pradesh", "Uttarakhand" => "Uttarakhand", "West Bengal" => "West Bengal"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration | GamePark</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            background: #f5f5f5;
            min-height: 100vh;
        }
        .page-wrapper {
            min-height: 100vh;
            padding: 30px 15px;
            background: linear-gradient(135deg, #1a5f7a 0%, #0f3d4d 100%);
        }
        .register-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .register-header {
            text-align: center;
            margin-bottom: 24px;
            color: white;
        }
        .register-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 8px;
        }
        .register-header p {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
        }
        .register-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            color: #333;
        }
        .card-header i { margin-right: 8px; color: #1a5f7a; }
        .card-body { padding: 20px; }
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .form-section:last-child { border-bottom: none; margin-bottom: 0; }
        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a5f7a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1a5f7a;
            display: inline-block;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-row .form-group { flex: 1; }
        @media (max-width: 600px) {
            .form-row { flex-direction: column; gap: 0; }
        }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            font-weight: 500;
            color: #555;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .form-group label .req { color: #dc3545; }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fff;
        }
        .form-control:focus {
            border-color: #1a5f7a;
            box-shadow: 0 0 0 3px rgba(26, 95, 122, 0.1);
            outline: none;
        }
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
            padding-right: 35px;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 70px;
        }
        .form-hint {
            font-size: 11px;
            color: #888;
            margin-top: 4px;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert.show { display: flex; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-primary {
            background: #1a5f7a;
            color: white;
        }
        .btn-primary:hover {
            background: #134555;
        }
        .btn-block { width: 100%; }
        .btn-lg { padding: 14px 28px; font-size: 15px; }
        .btn:disabled { opacity: 0.7; cursor: not-allowed; }
        .card-footer {
            background: #f8f9fa;
            padding: 15px 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }
        .card-footer p {
            margin: 0;
            color: #666;
            font-size: 13px;
        }
        .card-footer a { color: #1a5f7a; font-weight: 500; }
        .success-content {
            text-align: center;
            padding: 40px 20px;
        }
        .success-icon {
            width: 70px;
            height: 70px;
            background: #d4edda;
            color: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 20px;
        }
        .success-content h2 {
            color: #333;
            margin: 0 0 10px;
            font-size: 22px;
        }
        .success-content p {
            color: #666;
            margin: 0 0 8px;
        }
        .vendor-code {
            display: inline-block;
            background: #e9ecef;
            padding: 10px 20px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 600;
            font-size: 16px;
            color: #1a5f7a;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="register-container">
            <div class="register-header">
                <h1><i class="fas fa-building"></i> Vendor Registration</h1>
                <p>Join our vendor network and start submitting quotations</p>
            </div>

            <div class="register-card">
                <div class="card-header">
                    <i class="fas fa-user-plus"></i> New Vendor Registration Form
                </div>

                <div id="formContent">
                    <div class="card-body">
                        <div class="alert alert-danger" id="errorAlert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span id="errorMessage"></span>
                        </div>

                        <form id="registerForm" method="POST">
                            <input type="hidden" name="xAction" value="REGISTER">

                            <!-- Company Information -->
                            <div class="form-section">
                                <div class="section-title"><i class="fas fa-building"></i> Company Information</div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Legal Name <span class="req">*</span></label>
                                        <input type="text" name="legalName" class="form-control" placeholder="Registered company name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Trade Name</label>
                                        <input type="text" name="tradeName" class="form-control" placeholder="Trading as (if different)">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Vendor Type <span class="req">*</span></label>
                                        <select name="vendorType" class="form-control" required>
                                            <option value="Goods">Goods Supplier</option>
                                            <option value="Services">Service Provider</option>
                                            <option value="Both">Both</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>GST Number</label>
                                        <input type="text" name="gstNumber" class="form-control" placeholder="22AAAAA0000A1Z5" maxlength="15">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>PAN Number</label>
                                        <input type="text" name="panNumber" class="form-control" placeholder="AAAAA0000A" maxlength="10">
                                    </div>
                                    <div class="form-group"></div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="form-section">
                                <div class="section-title"><i class="fas fa-map-marker-alt"></i> Address</div>

                                <div class="form-group">
                                    <label>Registered Address</label>
                                    <textarea name="registeredAddress" class="form-control" placeholder="Full registered address"></textarea>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" name="city" class="form-control" placeholder="City">
                                    </div>
                                    <div class="form-group">
                                        <label>State</label>
                                        <select name="state" class="form-control">
                                            <?php foreach ($states as $val => $txt): ?>
                                                <option value="<?php echo $val; ?>"><?php echo $txt; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Pincode</label>
                                        <input type="text" name="pincode" class="form-control" placeholder="000000" maxlength="6">
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="form-section">
                                <div class="section-title"><i class="fas fa-address-card"></i> Contact Information</div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Contact Person <span class="req">*</span></label>
                                        <input type="text" name="contactPersonName" class="form-control" placeholder="Full name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Designation</label>
                                        <input type="text" name="contactDesignation" class="form-control" placeholder="e.g. Sales Manager">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Email Address <span class="req">*</span></label>
                                        <input type="email" name="contactEmail" class="form-control" placeholder="vendor@company.com" required>
                                        <div class="form-hint">This will be your login email</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone Number <span class="req">*</span></label>
                                        <input type="tel" name="contactPhone" class="form-control" placeholder="+91 98765 43210" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Login Credentials -->
                            <div class="form-section">
                                <div class="section-title"><i class="fas fa-lock"></i> Login Credentials</div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Password <span class="req">*</span></label>
                                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                                    </div>
                                    <div class="form-group">
                                        <label>Confirm Password <span class="req">*</span></label>
                                        <input type="password" name="confirmPassword" class="form-control" placeholder="Confirm password" required>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Submit Registration
                            </button>
                        </form>
                    </div>

                    <div class="card-footer">
                        <p>Already registered? <a href="<?php echo VP_BASEURL; ?>/vendorportal/login">Sign in here</a> | <a href="<?php echo VP_BASEURL; ?>/vendorportal/forgot-password">Forgot password?</a></p>
                    </div>
                </div>

                <div id="successContent" class="success-content" style="display: none;">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2>Registration Submitted!</h2>
                    <p>Your vendor registration has been received.</p>
                    <p>Your vendor code is:</p>
                    <div class="vendor-code" id="vendorCode"></div>
                    <p>We will review your application and notify you via email once approved.</p>
                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/login" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-sign-in-alt"></i> Go to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#registerForm').on('submit', function(e) {
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

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
            errorBox.removeClass('show');

            $.ajax({
                url: '<?php echo VP_BASEURL; ?>/vendorportal/register',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.err === 0) {
                        $('#vendorCode').text(response.vendorCode);
                        $('#formContent').hide();
                        $('#successContent').show();
                    } else {
                        $('#errorMessage').text(response.msg);
                        errorBox.addClass('show');
                        btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Registration');
                    }
                },
                error: function() {
                    $('#errorMessage').text('Connection error. Please try again.');
                    errorBox.addClass('show');
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Submit Registration');
                }
            });
        });
    </script>
</body>
</html>

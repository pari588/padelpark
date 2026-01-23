<?php
/**
 * Vendor Portal - Company Profile
 */

$pageTitle = 'Company Profile';
include("x-header.php");

global $DB;
$vendorID = vpGetVendorID();

// Get full vendor details
$DB->sql = "SELECT * FROM mx_vendor_onboarding WHERE vendorID = ?";
$DB->vals = [$vendorID];
$DB->types = "i";
$vendorDetails = $DB->dbRow();

// Get user details
$user = vpGetUser();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xAction']) && $_POST['xAction'] === 'CHANGE_PASSWORD') {
    header('Content-Type: application/json');

    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (strlen($newPassword) < 8) {
        echo json_encode(['err' => 1, 'msg' => 'New password must be at least 8 characters']);
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(['err' => 1, 'msg' => 'New passwords do not match']);
        exit;
    }

    // Verify current password
    $DB->sql = "SELECT passwordHash FROM mx_vendor_portal_user WHERE userID = ?";
    $DB->vals = [$user['userID']];
    $DB->types = "i";
    $userRow = $DB->dbRow();

    if (!password_verify($currentPassword, $userRow['passwordHash'])) {
        echo json_encode(['err' => 1, 'msg' => 'Current password is incorrect']);
        exit;
    }

    // Update password
    $DB->tbl = "mx_vendor_portal_user";
    $DB->dbUpdate(['passwordHash' => password_hash($newPassword, PASSWORD_DEFAULT)], "userID = ?", "i", [$user['userID']]);

    echo json_encode(['err' => 0, 'msg' => 'Password changed successfully']);
    exit;
}
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Company Profile</h1>
        <p>View your company information and manage your account</p>
    </div>
</div>

<!-- Profile Header -->
<div class="card mb-lg">
    <div class="card-body">
        <div class="profile-header">
            <div class="profile-avatar-large">
                <?php echo strtoupper(substr($vendorDetails['legalName'] ?? 'V', 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h2><?php echo vpClean($vendorDetails['legalName']); ?></h2>
                <p class="vendor-code"><?php echo vpClean($vendorDetails['vendorCode']); ?></p>
                <div style="margin-top: var(--space-md);">
                    <?php echo vpGetStatusBadge($vendorDetails['approvalStatus'] ?? 'Approved'); ?>
                    <?php if ($vendorDetails['isMSME']): ?>
                        <span class="status-badge status-info">MSME Registered</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-xl);">
    <div>
        <!-- Company Information -->
        <div class="card mb-lg">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-building text-primary"></i>
                    Company Information
                </h3>
            </div>
            <div class="card-body">
                <div class="profile-section">
                    <h5 class="profile-section-title">Basic Details</h5>
                    <div class="profile-grid">
                        <div class="profile-field">
                            <div class="profile-field-label">Legal Name</div>
                            <div class="profile-field-value"><?php echo vpClean($vendorDetails['legalName']); ?></div>
                        </div>
                        <div class="profile-field">
                            <div class="profile-field-label">Trade Name</div>
                            <div class="profile-field-value"><?php echo vpClean($vendorDetails['tradeName'] ?? '-'); ?></div>
                        </div>
                        <div class="profile-field">
                            <div class="profile-field-label">PAN Number</div>
                            <div class="profile-field-value font-mono"><?php echo vpClean($vendorDetails['panNumber'] ?? '-'); ?></div>
                        </div>
                        <div class="profile-field">
                            <div class="profile-field-label">GST Number</div>
                            <div class="profile-field-value font-mono"><?php echo vpClean($vendorDetails['gstNumber'] ?? '-'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <h5 class="profile-section-title">Contact Information</h5>
                    <div class="profile-grid">
                        <div class="profile-field">
                            <div class="profile-field-label">Contact Person</div>
                            <div class="profile-field-value"><?php echo vpClean($vendorDetails['contactPerson'] ?? '-'); ?></div>
                        </div>
                        <div class="profile-field">
                            <div class="profile-field-label">Designation</div>
                            <div class="profile-field-value"><?php echo vpClean($vendorDetails['contactDesignation'] ?? '-'); ?></div>
                        </div>
                        <div class="profile-field">
                            <div class="profile-field-label">Email</div>
                            <div class="profile-field-value"><?php echo vpClean($vendorDetails['contactEmail']); ?></div>
                        </div>
                        <div class="profile-field">
                            <div class="profile-field-label">Phone</div>
                            <div class="profile-field-value"><?php echo vpClean($vendorDetails['contactPhone'] ?? '-'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <h5 class="profile-section-title">Address</h5>
                    <div class="profile-field">
                        <div class="profile-field-label">Registered Address</div>
                        <div class="profile-field-value">
                            <?php
                            $address = array_filter([
                                $vendorDetails['addressLine1'] ?? '',
                                $vendorDetails['addressLine2'] ?? '',
                                $vendorDetails['city'] ?? '',
                                $vendorDetails['state'] ?? '',
                                $vendorDetails['pincode'] ?? ''
                            ]);
                            echo vpClean(implode(', ', $address)) ?: '-';
                            ?>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <h5 class="profile-section-title">Banking Information</h5>
                    <div class="profile-grid">
                        <div class="profile-field">
                            <div class="profile-field-label">Bank Name</div>
                            <div class="profile-field-value"><?php echo vpClean($vendorDetails['bankName'] ?? '-'); ?></div>
                        </div>
                        <div class="profile-field">
                            <div class="profile-field-label">Account Number</div>
                            <div class="profile-field-value font-mono">
                                <?php
                                $accNo = $vendorDetails['bankAccountNumber'] ?? '';
                                echo $accNo ? '****' . substr($accNo, -4) : '-';
                                ?>
                            </div>
                        </div>
                        <div class="profile-field">
                            <div class="profile-field-label">IFSC Code</div>
                            <div class="profile-field-value font-mono"><?php echo vpClean($vendorDetails['bankIFSC'] ?? '-'); ?></div>
                        </div>
                        <div class="profile-field">
                            <div class="profile-field-label">Branch</div>
                            <div class="profile-field-value"><?php echo vpClean($vendorDetails['bankBranch'] ?? '-'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <!-- Account Settings -->
        <div class="card mb-lg">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user text-accent"></i>
                    Account Settings
                </h3>
            </div>
            <div class="card-body">
                <div class="profile-field mb-lg">
                    <div class="profile-field-label">Portal Email</div>
                    <div class="profile-field-value"><?php echo vpClean($user['email']); ?></div>
                </div>
                <div class="profile-field mb-lg">
                    <div class="profile-field-label">Full Name</div>
                    <div class="profile-field-value"><?php echo vpClean($user['fullName']); ?></div>
                </div>

                <hr style="border: none; border-top: 1px solid var(--vp-slate-200); margin: var(--space-lg) 0;">

                <h5 style="margin-bottom: var(--space-md);">Change Password</h5>
                <form id="passwordForm">
                    <input type="hidden" name="xAction" value="CHANGE_PASSWORD">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="currentPassword" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="newPassword" class="form-control" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirmPassword" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-key"></i>
                        Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar text-success"></i>
                    Quick Stats
                </h3>
            </div>
            <div class="card-body">
                <?php
                $stats = vpGetDashboardStats();
                ?>
                <div class="d-flex justify-between align-center mb-md">
                    <span class="text-muted">Quotes Submitted</span>
                    <strong><?php echo $stats['submittedQuotes']; ?></strong>
                </div>
                <div class="d-flex justify-between align-center mb-md">
                    <span class="text-muted">Orders Won</span>
                    <strong class="text-success"><?php echo $stats['awardedOrders']; ?></strong>
                </div>
                <div class="d-flex justify-between align-center">
                    <span class="text-muted">Member Since</span>
                    <strong><?php echo vpFormatDate($vendorDetails['createdAt'], 'M Y'); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$('#passwordForm').on('submit', function(e) {
    e.preventDefault();

    const newPass = $('input[name="newPassword"]').val();
    const confirmPass = $('input[name="confirmPassword"]').val();

    if (newPass !== confirmPass) {
        alert('New passwords do not match');
        return;
    }

    $.ajax({
        url: '<?php echo VP_BASEURL; ?>/vendorportal/profile',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            alert(response.msg);
            if (response.err === 0) {
                $('#passwordForm')[0].reset();
            }
        },
        error: function() {
            alert('Error updating password');
        }
    });
});
</script>

<?php include("x-footer.php"); ?>

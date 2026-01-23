<?php
/**
 * Vendor Approval - Add/Edit View
 * This module shows pending approvals - editing is done via vendor-onboarding
 */
$vendorID = intval($_GET["vendorID"] ?? $_GET["id"] ?? 0);
$redirectUrl = $vendorID > 0 ? ADMINURL . "vendor-onboarding-edit?vendorID=" . $vendorID : ADMINURL . "vendor-onboarding-add";
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div class="alert alert-info">
            <h5>Vendor Approval</h5>
            <p>This is the approval queue for pending vendors. To view or edit vendor details, please use the Vendor Onboarding module.</p>
            <a href="<?php echo $redirectUrl; ?>" class="btn btn-primary">
                <?php echo $vendorID > 0 ? 'View/Edit Vendor' : 'Add New Vendor'; ?>
            </a>
            <a href="<?php echo ADMINURL; ?>vendor-approval-list" class="btn btn-secondary">Back to Approval Queue</a>
        </div>
    </div>
</div>
<script>
setTimeout(function() {
    window.location.href = '<?php echo $redirectUrl; ?>';
}, 100);
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/vendor-approval/x-vendor-approval.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/vendor-approval/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>

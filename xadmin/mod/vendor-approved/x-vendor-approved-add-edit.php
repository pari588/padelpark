<?php
/**
 * Vendor Approved - Add/Edit View
 * This module shows approved vendors only - editing is done via vendor-onboarding
 */
$vendorID = intval($_GET["vendorID"] ?? $_GET["id"] ?? 0);
$redirectUrl = $vendorID > 0 ? ADMINURL . "vendor-onboarding-edit?vendorID=" . $vendorID : ADMINURL . "vendor-onboarding-add";
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div class="alert alert-info">
            <h5>Vendor Management</h5>
            <p>This is a filtered view of approved vendors. To add or edit vendors, please use the Vendor Onboarding module.</p>
            <a href="<?php echo $redirectUrl; ?>" class="btn btn-primary">
                <?php echo $vendorID > 0 ? 'Edit Vendor' : 'Add New Vendor'; ?>
            </a>
            <a href="<?php echo ADMINURL; ?>vendor-approved-list" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>
<script>
// Auto-redirect after a brief delay
setTimeout(function() {
    window.location.href = '<?php echo $redirectUrl; ?>';
}, 100);
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/vendor-approved/x-vendor-approved.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/vendor-approved/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>

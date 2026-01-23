<?php
$id = 0; $D = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["vendorID"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

// Build vendor category options
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$categoryOpt = getTableDD(["table" => $DB->pre . "vendor_category", "key" => "categoryID", "val" => "categoryName", "selected" => ($D["vendorCategory"] ?? ""), "where" => $whrArr, "order" => "sortOrder ASC"]);

// Vendor type options
$vendorTypes = array("" => "-- Select --", "Goods" => "Goods", "Services" => "Services", "Both" => "Both");
$vendorTypeOpt = "";
foreach ($vendorTypes as $val => $txt) {
    $sel = (($D["vendorType"] ?? "") == $val) ? ' selected="selected"' : '';
    $vendorTypeOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

// Status options
$statuses = array("Pending" => "Pending", "Approved" => "Approved", "Disapproved" => "Disapproved", "Blocked" => "Blocked");
$statusOpt = "";
foreach ($statuses as $val => $txt) {
    $sel = (($D["vendorStatus"] ?? "Pending") == $val) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

// State options
$states = array("" => "-- Select State --", "Andhra Pradesh" => "Andhra Pradesh", "Delhi" => "Delhi", "Gujarat" => "Gujarat", "Karnataka" => "Karnataka", "Maharashtra" => "Maharashtra", "Tamil Nadu" => "Tamil Nadu", "Telangana" => "Telangana", "West Bengal" => "West Bengal");
$stateOpt = "";
foreach ($states as $val => $txt) {
    $sel = (($D["state"] ?? "") == $val) ? ' selected="selected"' : '';
    $stateOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

// Project options
$projects = array("" => "-- Select Project --", "1" => "Sky Padel", "2" => "GamePark", "3" => "Other");
$projectOpt = "";
foreach ($projects as $val => $txt) {
    $sel = (($D["projectID"] ?? "") == $val) ? ' selected="selected"' : '';
    $projectOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "vendorCode", "value" => $D["vendorCode"] ?? "", "title" => "Vendor Code", "info" => '<span class="info">Leave blank for auto-generation</span>'),
    array("type" => "text", "name" => "legalName", "value" => $D["legalName"] ?? "", "title" => "Legal Name *", "validate" => "required"),
    array("type" => "text", "name" => "tradeName", "value" => $D["tradeName"] ?? "", "title" => "Trade Name"),
    array("type" => "select", "name" => "vendorType", "value" => $vendorTypeOpt, "title" => "Vendor Type *", "validate" => "required"),
    array("type" => "select", "name" => "vendorCategory", "value" => $categoryOpt, "title" => "Category"),
    array("type" => "select", "name" => "projectID", "value" => $projectOpt, "title" => "Project/Business Unit"),
    array("type" => "textarea", "name" => "companyDescription", "value" => $D["companyDescription"] ?? "", "title" => "Description", "params" => array("rows" => 2)),
    array("type" => "textarea", "name" => "registeredAddress", "value" => $D["registeredAddress"] ?? "", "title" => "Registered Address", "params" => array("rows" => 2)),
    array("type" => "textarea", "name" => "businessAddress", "value" => $D["businessAddress"] ?? "", "title" => "Business Address", "params" => array("rows" => 2)),
    array("type" => "text", "name" => "city", "value" => $D["city"] ?? "", "title" => "City"),
    array("type" => "select", "name" => "state", "value" => $stateOpt, "title" => "State"),
    array("type" => "text", "name" => "pincode", "value" => $D["pincode"] ?? "", "title" => "Pincode"),
    array("type" => "text", "name" => "gstNumber", "value" => $D["gstNumber"] ?? "", "title" => "GST Number"),
    array("type" => "text", "name" => "panNumber", "value" => $D["panNumber"] ?? "", "title" => "PAN Number"),
    array("type" => "checkbox", "name" => "msmeRegistered", "value" => ($D["msmeRegistered"] ?? 0), "title" => "MSME Registered"),
    array("type" => "text", "name" => "msmeNumber", "value" => $D["msmeNumber"] ?? "", "title" => "MSME Number"),
    array("type" => "text", "name" => "bankName", "value" => $D["bankName"] ?? "", "title" => "Bank Name"),
    array("type" => "text", "name" => "bankBranch", "value" => $D["bankBranch"] ?? "", "title" => "Branch"),
    array("type" => "text", "name" => "bankAccountNo", "value" => $D["bankAccountNo"] ?? "", "title" => "Account Number"),
    array("type" => "text", "name" => "ifscCode", "value" => $D["ifscCode"] ?? "", "title" => "IFSC Code"),
    array("type" => "text", "name" => "contactPersonName", "value" => $D["contactPersonName"] ?? "", "title" => "Contact Person *", "validate" => "required"),
    array("type" => "text", "name" => "contactDesignation", "value" => $D["contactDesignation"] ?? "", "title" => "Designation"),
    array("type" => "text", "name" => "contactPhone", "value" => $D["contactPhone"] ?? "", "title" => "Phone *", "validate" => "required"),
    array("type" => "text", "name" => "contactEmail", "value" => $D["contactEmail"] ?? "", "title" => "Email *", "validate" => "email"),
    array("type" => "text", "name" => "altContactName", "value" => $D["altContactName"] ?? "", "title" => "Alt Contact Name"),
    array("type" => "text", "name" => "altContactPhone", "value" => $D["altContactPhone"] ?? "", "title" => "Alt Phone"),
    array("type" => "text", "name" => "altContactEmail", "value" => $D["altContactEmail"] ?? "", "title" => "Alt Email"),
    array("type" => "select", "name" => "vendorStatus", "value" => $statusOpt, "title" => "Status"),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 3)),
    array("type" => "textarea", "name" => "internalNotes", "value" => $D["internalNotes"] ?? "", "title" => "Internal Notes", "params" => array("rows" => 2))
);

$MXFRM = new mxForm();

// Get portal user info if editing
$portalUser = null;
if ($id > 0) {
    $DB->sql = "SELECT * FROM " . $DB->pre . "vendor_portal_user WHERE vendorID = ?";
    $DB->vals = array($id);
    $DB->types = "i";
    $portalUser = $DB->dbRow();
}

// Build AJAX URL and token for portal actions
$portalAjaxUrl = ADMINURL . "/mod/vendor-onboarding/x-vendor-onboarding.inc.php";
$csrfToken = $_SESSION[SITEURL]["CSRF_TOKEN"];
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="vendorID" value="<?php echo $id; ?>">
        <div class="wrap-form f50">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>
        </div>
        <?php if ($id > 0): ?>
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <li class="item-form"><h3 style="margin:0;padding:10px 0;border-bottom:1px solid #ddd;"><i class="fa fa-globe"></i> Vendor Portal Access</h3></li>
                <li class="item-form">
                    <label>Vendor Status</label>
                    <div class="inp-form">
                        <?php
                        $vs = $D["vendorStatus"] ?? "Pending";
                        $vsBadge = array("Pending" => "badge-warning", "Approved" => "badge-success", "Disapproved" => "badge-danger", "Blocked" => "badge-dark");
                        echo '<span class="badge ' . ($vsBadge[$vs] ?? "badge-secondary") . '">' . htmlentities($vs) . '</span>';
                        ?>
                    </div>
                </li>
                <li class="item-form">
                    <label>Portal Status</label>
                    <div class="inp-form"><?php echo ($D["portalEnabled"] ?? 0) ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>'; ?></div>
                </li>
                <li class="item-form">
                    <label>Username</label>
                    <div class="inp-form"><?php echo $portalUser ? htmlentities($portalUser["username"]) : "-"; ?></div>
                </li>
                <li class="item-form">
                    <label>Last Login</label>
                    <div class="inp-form"><?php echo ($portalUser && $portalUser["lastLogin"]) ? date("d-M-Y H:i", strtotime($portalUser["lastLogin"])) : "Never"; ?></div>
                </li>
                <li class="item-form">
                    <label>Portal Actions</label>
                    <div class="inp-form">
                        <?php if (($D["vendorStatus"] ?? "") == "Approved"): ?>
                            <?php if (!($D["portalEnabled"] ?? 0)): ?>
                                <span class="btn" id="btnActivate" style="background:#28a745;color:#fff;">Activate Portal</span>
                            <?php else: ?>
                                <span class="btn" id="btnReset" style="background:#ffc107;color:#000;">Reset Password</span>
                                <span class="btn" id="btnDeactivate" style="background:#dc3545;color:#fff;">Deactivate</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#999;">Set vendor status to "Approved" first</span>
                        <?php endif; ?>
                    </div>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
<?php if ($id > 0): ?>
<script>
$(function(){
    $('#btnActivate').on('click', function(){
        if(confirm('Activate vendor portal access?')){
            $.post('<?php echo $portalAjaxUrl; ?>', {xAction:'ACTIVATE_PORTAL', vendorID:<?php echo $id; ?>, xToken:'<?php echo $csrfToken; ?>'}, function(r){
                alert(r.msg);
                if(r.err==0) location.reload();
            }, 'json');
        }
    });
    $('#btnReset').on('click', function(){
        if(confirm('Reset portal password?')){
            $.post('<?php echo $portalAjaxUrl; ?>', {xAction:'RESET_PORTAL_PASSWORD', vendorID:<?php echo $id; ?>, xToken:'<?php echo $csrfToken; ?>'}, function(r){
                alert(r.msg);
                if(r.err==0) location.reload();
            }, 'json');
        }
    });
    $('#btnDeactivate').on('click', function(){
        if(confirm('Deactivate vendor portal access?')){
            $.post('<?php echo $portalAjaxUrl; ?>', {xAction:'DEACTIVATE_PORTAL', vendorID:<?php echo $id; ?>, xToken:'<?php echo $csrfToken; ?>'}, function(r){
                alert(r.msg);
                if(r.err==0) location.reload();
            }, 'json');
        }
    });
});
</script>
<?php endif; ?>

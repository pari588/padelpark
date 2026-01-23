<?php
$id = 0;
$D = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}

// Distributor type options
$distTypes = array("Distributor", "Dealer", "Retailer", "Institutional", "Government");
$distTypeOpt = "";
$currentDistType = $D["distributorType"] ?? "Distributor";
foreach ($distTypes as $dt) {
    $sel = ($currentDistType == $dt) ? ' selected="selected"' : '';
    $distTypeOpt .= '<option value="' . $dt . '"' . $sel . '>' . $dt . '</option>';
}

// Business type options
$bizTypes = array("Proprietorship", "Partnership", "LLP", "Pvt Ltd", "Public Ltd", "Government");
$bizTypeOpt = "";
$currentBizType = $D["businessType"] ?? "Proprietorship";
foreach ($bizTypes as $bt) {
    $sel = ($currentBizType == $bt) ? ' selected="selected"' : '';
    $bizTypeOpt .= '<option value="' . $bt . '"' . $sel . '>' . $bt . '</option>';
}

// Credit status options
$creditStatuses = array("Active", "On Hold", "Blocked", "COD Only");
$creditStatusOpt = "";
$currentCreditStatus = $D["creditStatus"] ?? "Active";
foreach ($creditStatuses as $cs) {
    $sel = ($currentCreditStatus == $cs) ? ' selected="selected"' : '';
    $creditStatusOpt .= '<option value="' . $cs . '"' . $sel . '>' . $cs . '</option>';
}

// Active status options
$activeOpt = '<option value="1"' . (($D["isActive"] ?? 1) == 1 ? ' selected="selected"' : '') . '>Active</option>';
$activeOpt .= '<option value="0"' . (($D["isActive"] ?? 1) == 0 ? ' selected="selected"' : '') . '>Inactive</option>';

$arrForm = array(
    array("type" => "text", "name" => "distributorCode", "value" => $D["distributorCode"] ?? "", "title" => "Distributor Code", "info" => '<span class="info">Leave blank for auto-generation</span>'),
    array("type" => "text", "name" => "companyName", "value" => $D["companyName"] ?? "", "title" => "Company Name", "validate" => "required"),
    array("type" => "text", "name" => "tradeName", "value" => $D["tradeName"] ?? "", "title" => "Trade Name"),
    array("type" => "select", "name" => "distributorType", "value" => $distTypeOpt, "title" => "Distributor Type"),
    array("type" => "select", "name" => "businessType", "value" => $bizTypeOpt, "title" => "Business Type"),
    array("type" => "text", "name" => "gstin", "value" => $D["gstin"] ?? "", "title" => "GSTIN", "info" => '<span class="info">15-digit GSTIN</span>'),
    array("type" => "text", "name" => "panNo", "value" => $D["panNo"] ?? "", "title" => "PAN No"),
);

$arrForm1 = array(
    array("type" => "text", "name" => "contactPerson", "value" => $D["contactPerson"] ?? "", "title" => "Contact Person"),
    array("type" => "text", "name" => "designation", "value" => $D["designation"] ?? "", "title" => "Designation"),
    array("type" => "text", "name" => "mobile", "value" => $D["mobile"] ?? "", "title" => "Mobile"),
    array("type" => "text", "name" => "phone", "value" => $D["phone"] ?? "", "title" => "Phone"),
    array("type" => "text", "name" => "email", "value" => $D["email"] ?? "", "title" => "Email"),
    array("type" => "text", "name" => "website", "value" => $D["website"] ?? "", "title" => "Website"),
    array("type" => "select", "name" => "isActive", "value" => $activeOpt, "title" => "Status"),
);

$arrForm2 = array(
    array("type" => "textarea", "name" => "billingAddress", "value" => $D["billingAddress"] ?? "", "title" => "Billing Address", "params" => array("rows" => 2)),
    array("type" => "text", "name" => "billingCity", "value" => $D["billingCity"] ?? "", "title" => "City"),
    array("type" => "text", "name" => "billingState", "value" => $D["billingState"] ?? "", "title" => "State"),
    array("type" => "text", "name" => "billingStateCode", "value" => $D["billingStateCode"] ?? "", "title" => "State Code"),
    array("type" => "text", "name" => "billingPincode", "value" => $D["billingPincode"] ?? "", "title" => "Pincode"),
);

$arrForm3 = array(
    array("type" => "textarea", "name" => "shippingAddress", "value" => $D["shippingAddress"] ?? "", "title" => "Shipping Address", "params" => array("rows" => 2)),
    array("type" => "text", "name" => "shippingCity", "value" => $D["shippingCity"] ?? "", "title" => "City"),
    array("type" => "text", "name" => "shippingState", "value" => $D["shippingState"] ?? "", "title" => "State"),
    array("type" => "text", "name" => "shippingStateCode", "value" => $D["shippingStateCode"] ?? "", "title" => "State Code"),
    array("type" => "text", "name" => "shippingPincode", "value" => $D["shippingPincode"] ?? "", "title" => "Pincode"),
);

$arrForm4 = array(
    array("type" => "text", "name" => "creditLimit", "value" => $D["creditLimit"] ?? "0", "title" => "Credit Limit (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "creditDays", "value" => $D["creditDays"] ?? "30", "title" => "Credit Days", "validate" => "number"),
    array("type" => "select", "name" => "creditStatus", "value" => $creditStatusOpt, "title" => "Credit Status"),
    array("type" => "text", "name" => "baseDiscount", "value" => $D["baseDiscount"] ?? "0", "title" => "Base Discount %", "validate" => "number"),
);

$arrForm5 = array(
    array("type" => "text", "name" => "bankName", "value" => $D["bankName"] ?? "", "title" => "Bank Name"),
    array("type" => "text", "name" => "bankBranch", "value" => $D["bankBranch"] ?? "", "title" => "Branch"),
    array("type" => "text", "name" => "accountNo", "value" => $D["accountNo"] ?? "", "title" => "Account No"),
    array("type" => "text", "name" => "ifscCode", "value" => $D["ifscCode"] ?? "", "title" => "IFSC Code"),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Basic Information</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Contact Information</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Billing Address</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm2); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Shipping Address</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm3); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Credit & Payment Terms</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm4); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Bank Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm5); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/distributor/x-distributor.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/distributor/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>

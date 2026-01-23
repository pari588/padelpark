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

// Get users for assignment dropdown
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$userOpt = getTableDD([
    "table" => $DB->pre . "x_admin_user",
    "key" => "userID",
    "val" => "displayName",
    "selected" => ($D['assignedTo'] ?? 0),
    "where" => $whrArr
]);

// Build lead source options
$leadSources = array("Website", "Referral", "Direct Call", "Exhibition", "Social Media", "Walk-in", "Partner Referral", "Email Campaign", "Other");
$sourceOpt = "";
$currentSource = $D["leadSource"] ?? "";
foreach ($leadSources as $src) {
    $sel = ($currentSource == $src) ? ' selected="selected"' : '';
    $sourceOpt .= '<option value="' . $src . '"' . $sel . '>' . $src . '</option>';
}

// Build lead status options
$leadStatuses = array("New", "Contacted", "Site Visit Scheduled", "Site Visit Done", "Quotation Sent", "Converted", "Lost");
$statusOpt = "";
$currentStatus = $D["leadStatus"] ?? "New";
foreach ($leadStatuses as $stat) {
    $sel = ($currentStatus == $stat) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $stat . '"' . $sel . '>' . $stat . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "leadNo", "value" => $D["leadNo"] ?? "", "title" => "Lead No", "info" => '<span class="info">Leave blank for auto-generation</span>'),
    array("type" => "date", "name" => "leadDate", "value" => $D["leadDate"] ?? date("Y-m-d"), "title" => "Lead Date", "validate" => "required"),
    array("type" => "text", "name" => "clientName", "value" => $D["clientName"] ?? "", "title" => "Client Name", "validate" => "required"),
    array("type" => "text", "name" => "clientEmail", "value" => $D["clientEmail"] ?? "", "title" => "Client Email"),
    array("type" => "text", "name" => "clientPhone", "value" => $D["clientPhone"] ?? "", "title" => "Client Phone", "validate" => "required"),
    array("type" => "text", "name" => "clientCompany", "value" => $D["clientCompany"] ?? "", "title" => "Company Name"),
    array("type" => "textarea", "name" => "siteAddress", "value" => $D["siteAddress"] ?? "", "title" => "Site Address", "params" => array("rows" => 3)),
    array("type" => "text", "name" => "siteCity", "value" => $D["siteCity"] ?? "", "title" => "City"),
);

$arrForm1 = array(
    array("type" => "text", "name" => "siteState", "value" => $D["siteState"] ?? "", "title" => "State"),
    array("type" => "text", "name" => "pincode", "value" => $D["pincode"] ?? "", "title" => "Pincode"),
    array("type" => "text", "name" => "courtRequirement", "value" => $D["courtRequirement"] ?? "", "title" => "Court Requirement", "info" => '<span class="info">e.g., Single, Double, Triple</span>'),
    array("type" => "text", "name" => "estimatedBudget", "value" => $D["estimatedBudget"] ?? "0", "title" => "Estimated Budget (₹)", "validate" => "number"),
    array("type" => "select", "name" => "leadSource", "value" => $sourceOpt, "title" => "Source"),
    array("type" => "select", "name" => "assignedTo", "value" => $userOpt, "title" => "Assign To"),
    array("type" => "select", "name" => "leadStatus", "value" => $statusOpt, "title" => "Status", "validate" => "required"),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 4))
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Client Information</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Lead Details</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm1);
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

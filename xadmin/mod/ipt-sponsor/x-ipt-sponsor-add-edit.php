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

// Get tournaments dropdown
$DB->sql = "SELECT tournamentID, tournamentCode, tournamentName FROM " . $DB->pre . "ipt_tournament WHERE status=1 ORDER BY startDate DESC";
$tournaments = $DB->dbRows();
$tournamentOpt = '<option value="">Select Tournament</option>';
$selTournament = $D["tournamentID"] ?? ($_GET["tournamentID"] ?? "");
foreach ($tournaments as $t) {
    $sel = ($selTournament == $t["tournamentID"]) ? ' selected="selected"' : '';
    $tournamentOpt .= '<option value="' . $t["tournamentID"] . '"' . $sel . '>' . htmlspecialchars($t["tournamentCode"] . " - " . $t["tournamentName"]) . '</option>';
}

// Build type dropdown
$types = array("Title", "Presenting", "Gold", "Silver", "Bronze", "Associate", "Media", "Equipment", "Other");
$typeOpt = "";
$currentType = $D["sponsorType"] ?? "Gold";
foreach ($types as $type) {
    $sel = ($currentType == $type) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $type . '"' . $sel . '>' . $type . '</option>';
}

// Build payment status dropdown
$payStatuses = array("Pending", "Partial", "Paid");
$payOpt = "";
$currentPay = $D["paymentStatus"] ?? "Pending";
foreach ($payStatuses as $ps) {
    $sel = ($currentPay == $ps) ? ' selected="selected"' : '';
    $payOpt .= '<option value="' . $ps . '"' . $sel . '>' . $ps . '</option>';
}

$arrFormSponsor = array(
    array("type" => "select", "name" => "tournamentID", "value" => $tournamentOpt, "title" => "Tournament", "validate" => "required"),
    array("type" => "text", "name" => "sponsorName", "value" => $D["sponsorName"] ?? "", "title" => "Sponsor Name", "validate" => "required"),
    array("type" => "text", "name" => "companyName", "value" => $D["companyName"] ?? "", "title" => "Company Name"),
    array("type" => "select", "name" => "sponsorType", "value" => $typeOpt, "title" => "Sponsorship Type", "validate" => "required"),
);

$arrFormFinance = array(
    array("type" => "text", "name" => "contractValue", "value" => $D["contractValue"] ?? "0", "title" => "Contract Value (Rs.)", "validate" => "required,number"),
    array("type" => "text", "name" => "paymentReceived", "value" => $D["paymentReceived"] ?? "0", "title" => "Payment Received (Rs.)", "validate" => "number"),
    array("type" => "select", "name" => "paymentStatus", "value" => $payOpt, "title" => "Payment Status"),
);

$arrFormContact = array(
    array("type" => "text", "name" => "contactPerson", "value" => $D["contactPerson"] ?? "", "title" => "Contact Person"),
    array("type" => "text", "name" => "contactPhone", "value" => $D["contactPhone"] ?? "", "title" => "Contact Phone"),
    array("type" => "text", "name" => "contactEmail", "value" => $D["contactEmail"] ?? "", "title" => "Contact Email"),
);

$arrFormDeliverables = array(
    array("type" => "textarea", "name" => "deliverablesJSON", "value" => $D["deliverablesJSON"] ?? "", "title" => "Deliverables", "params" => array("rows" => 4), "info" => '<span class="info">Logo placement, banners, mentions, etc.</span>'),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 3)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Sponsor Information</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormSponsor); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Financial Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormFinance); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Contact Information</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormContact); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Deliverables & Notes</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormDeliverables); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<?php
$id = 0; $D = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$quotationOpt = getTableDD(["table" => $DB->pre . "sky_padel_quotation", "key" => "quotationID", "val" => "CONCAT(quotationNo, ' - ₹', FORMAT(totalAmount, 0))", "selected" => ($D['quotationID'] ?? 0), "where" => $whrArr]);
$projectOpt = getTableDD(["table" => $DB->pre . "sky_padel_project", "key" => "projectID", "val" => "CONCAT(projectNo, ' - ', projectName)", "selected" => ($D['projectID'] ?? 0), "where" => $whrArr]);

// Build payment type options
$paymentTypes = array("Advance" => "Advance Payment", "Milestone" => "Milestone Payment", "Final" => "Final Payment", "Additional" => "Additional Payment");
$paymentTypeOpt = "";
$currentPaymentType = $D["paymentType"] ?? "";
foreach ($paymentTypes as $val => $txt) {
    $sel = ($currentPaymentType == $val) ? ' selected="selected"' : '';
    $paymentTypeOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

// Build payment method options
$paymentMethods = array("Cash" => "Cash", "Cheque" => "Cheque", "NEFT" => "NEFT", "RTGS" => "RTGS", "UPI" => "UPI", "Card" => "Debit/Credit Card");
$paymentMethodOpt = "";
$currentPaymentMethod = $D["paymentMethod"] ?? "NEFT";
foreach ($paymentMethods as $val => $txt) {
    $sel = ($currentPaymentMethod == $val) ? ' selected="selected"' : '';
    $paymentMethodOpt .= '<option value="' . $val . '"' . $sel . '>' . $txt . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "paymentNo", "value" => $D["paymentNo"] ?? "", "title" => "Payment No", "info" => '<span class="info">Leave blank for auto-generation</span>'),
    array("type" => "select", "name" => "projectID", "value" => $projectOpt, "title" => "Project", "validate" => "required"),
    array("type" => "select", "name" => "quotationID", "value" => $quotationOpt, "title" => "Quotation"),
    array("type" => "date", "name" => "paymentDate", "value" => $D["paymentDate"] ?? date("Y-m-d"), "title" => "Payment Date", "validate" => "required"),
    array("type" => "select", "name" => "paymentType", "value" => $paymentTypeOpt, "title" => "Type", "validate" => "required"),
    array("type" => "text", "name" => "dueAmount", "value" => $D["dueAmount"] ?? "0", "title" => "Due Amount (₹)", "validate" => "required|number"),
    array("type" => "text", "name" => "paidAmount", "value" => $D["paidAmount"] ?? "0", "title" => "Paid Amount (₹)", "validate" => "required|number"),
    array("type" => "select", "name" => "paymentMethod", "value" => $paymentMethodOpt, "title" => "Method"),
    array("type" => "text", "name" => "transactionID", "value" => $D["transactionID"] ?? "", "title" => "Transaction ID / UTR No"),
    array("type" => "text", "name" => "referenceNo", "value" => $D["referenceNo"] ?? "", "title" => "Reference No"),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 3))
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm); ?></ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

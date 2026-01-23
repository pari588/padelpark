<?php
/**
 * Fuel Expense Add/Edit Form Page
 * Standard xadmin form page for adding/editing fuel expenses with OCR
 */

global $DB, $MXSTATUS, $TPL, $MXSET, $MXMOD;

$id = 0;
$D = array();

// Load existing expense if editing
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}

// Get vehicle dropdown
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$vehicleDD = getTableDD(array("table" => $DB->pre . "vehicle", "key" => "vehicleID", "val" => "vehicleName", "selected" => ($D["vehicleID"] ?? ""), "where" => $whrArr));

$MXFRM = new mxForm();

$arrForm = array(
    array("type" => "file", "name" => "billImage", "value" => array($D["billImage"] ?? "", $id ?? ""), "title" => "Bill Image (JPG/PNG/PDF)", "params" => array("EXT" => "jpg|jpeg|png|pdf"), "attrp" => ' width="30%"'),
    array("type" => "select", "name" => "vehicleID", "value" => $vehicleDD, "title" => "Vehicle", "validate" => "required", "attrp" => ' width="30%"'),
    array("type" => "date", "name" => "billDate", "value" => $D["billDate"] ?? "", "title" => "Bill Date", "validate" => "required", "attrp" => ' width="30%"', "params" => array("changeMonth" => true, "changeYear" => true, "yearRange" => "-100y:+1", "maxDate" => "0d")),
    array("type" => "text", "name" => "expenseAmount", "value" => $D["expenseAmount"] ?? "", "title" => "Amount (₹)", "validate" => "required,number", "attrp" => ' width="30%"'),
    array("type" => "textarea", "name" => "remarks", "value" => $D["remarks"] ?? "", "title" => "Remarks", "attrp" => ' width="30%"'),
);

// Payment status info will be displayed in the form if editing
$paymentStatusDisplay = "";

?>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f100">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script type="text/javascript" src="/xadmin/mod/fuel-expense/inc/js/x-fuel-expense.inc.js"></script>

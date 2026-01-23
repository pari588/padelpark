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

// Get locations for dropdown
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$locationOpt = getTableDD([
    "table" => $DB->pre . "pnp_location",
    "key" => "locationID",
    "val" => "locationName",
    "selected" => ($D['locationID'] ?? 0),
    "where" => $whrArr
]);

// Build equipment type options
$equipTypes = array("Racket", "Balls", "Shoes", "Accessories", "Other");
$equipTypeOpt = "";
$currentEquipType = $D["equipmentType"] ?? "Racket";
foreach ($equipTypes as $et) {
    $sel = ($currentEquipType == $et) ? ' selected="selected"' : '';
    $equipTypeOpt .= '<option value="' . $et . '"' . $sel . '>' . $et . '</option>';
}

// Build condition options
$conditions = array("Excellent", "Good", "Fair", "Poor");
$conditionOpt = "";
$currentCondition = $D["condition"] ?? "Good";
foreach ($conditions as $c) {
    $sel = ($currentCondition == $c) ? ' selected="selected"' : '';
    $conditionOpt .= '<option value="' . $c . '"' . $sel . '>' . $c . '</option>';
}

$arrForm = array(
    array("type" => "select", "name" => "locationID", "value" => $locationOpt, "title" => "Location", "validate" => "required"),
    array("type" => "text", "name" => "equipmentCode", "value" => $D["equipmentCode"] ?? "", "title" => "Equipment Code", "validate" => "required", "info" => '<span class="info">e.g., RAC-001</span>'),
    array("type" => "text", "name" => "equipmentName", "value" => $D["equipmentName"] ?? "", "title" => "Equipment Name", "validate" => "required"),
    array("type" => "select", "name" => "equipmentType", "value" => $equipTypeOpt, "title" => "Type"),
    array("type" => "text", "name" => "brand", "value" => $D["brand"] ?? "", "title" => "Brand"),
    array("type" => "text", "name" => "model", "value" => $D["model"] ?? "", "title" => "Model"),
);

$arrForm1 = array(
    array("type" => "text", "name" => "rentalRate", "value" => $D["rentalRate"] ?? "0", "title" => "Rental Rate (Rs.)", "validate" => "required,number"),
    array("type" => "text", "name" => "depositAmount", "value" => $D["depositAmount"] ?? "0", "title" => "Deposit Amount (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "damageChargeRate", "value" => $D["damageChargeRate"] ?? "0", "title" => "Damage Charge (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "totalQuantity", "value" => $D["totalQuantity"] ?? "1", "title" => "Total Quantity", "validate" => "required,number"),
    array("type" => "text", "name" => "availableQuantity", "value" => $D["availableQuantity"] ?? "1", "title" => "Available Quantity", "validate" => "number"),
    array("type" => "select", "name" => "condition", "value" => $conditionOpt, "title" => "Condition"),
    array("type" => "textarea", "name" => "description", "value" => $D["description"] ?? "", "title" => "Description", "params" => array("rows" => 2)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Equipment Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Pricing & Inventory</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

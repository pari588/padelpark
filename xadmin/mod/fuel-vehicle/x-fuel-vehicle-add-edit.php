<?php
/**
 * Fuel Vehicle Add/Edit Form Page
 * Standard xadmin form page for adding/editing vehicles
 */

global $DB, $MXSTATUS, $TPL, $MXSET, $MXMOD;

$id = 0;
$D = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}

$MXFRM = new mxForm();

// Prepare fuel type dropdown options
$fuelTypeOptions = "";
$fuelTypes = array("Petrol" => "Petrol", "Diesel" => "Diesel", "CNG" => "CNG");
$selectedFuelType = $D["fuelType"] ?? "";
foreach ($fuelTypes as $key => $val) {
    $selected = ($selectedFuelType === $key) ? " selected='selected'" : "";
    $fuelTypeOptions .= "<option value='" . htmlspecialchars($key) . "'" . $selected . ">" . htmlspecialchars($val) . "</option>";
}

$arrForm = array(
    array("type" => "text", "name" => "vehicleName", "value" => $D["vehicleName"] ?? "", "title" => "Vehicle Name", "validate" => "required", "attrp" => ' width="30%"'),
    array("type" => "text", "name" => "registrationNumber", "value" => $D["registrationNumber"] ?? "", "title" => "Registration Number", "attrp" => ' width="30%"'),
    array("type" => "select", "name" => "fuelType", "value" => $fuelTypeOptions, "title" => "Fuel Type", "validate" => "required", "attrp" => ' width="30%"'),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "attrp" => ' width="30%"'),
);

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

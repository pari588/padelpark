<?php
$id = 0;
$D = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "`=?";
    $D = $DB->dbRow();
}

// Generate warehouse code for new entries
$warehouseCode = $D["warehouseCode"] ?? "";
if (empty($warehouseCode)) {
    $DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "warehouse WHERE status=1";
    $cnt = $DB->dbRow();
    $warehouseCode = "WH-" . str_pad(($cnt["cnt"] + 1), 3, "0", STR_PAD_LEFT);
}

// Warehouse type options
$warehouseTypeOpt = "";
$types = array("Main" => "Main Warehouse", "Sub-Warehouse" => "Sub-Warehouse (Pay N Play Center)", "In-Transit" => "In-Transit", "Project-Site" => "Project Site");
$currentType = $D["warehouseType"] ?? "Sub-Warehouse";
foreach ($types as $k => $v) {
    $sel = ($currentType == $k) ? ' selected="selected"' : '';
    $warehouseTypeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// State options
$stateOpt = "";
$states = array(
    "" => "-- Select State --",
    "Andaman and Nicobar" => "Andaman and Nicobar", "Andhra Pradesh" => "Andhra Pradesh",
    "Arunachal Pradesh" => "Arunachal Pradesh", "Assam" => "Assam", "Bihar" => "Bihar",
    "Chandigarh" => "Chandigarh", "Chhattisgarh" => "Chhattisgarh", "Daman and Diu" => "Daman and Diu",
    "Delhi" => "Delhi", "Goa" => "Goa", "Gujarat" => "Gujarat", "Haryana" => "Haryana",
    "Himachal Pradesh" => "Himachal Pradesh", "Jammu and Kashmir" => "Jammu and Kashmir",
    "Jharkhand" => "Jharkhand", "Karnataka" => "Karnataka", "Kerala" => "Kerala",
    "Ladakh" => "Ladakh", "Lakshadweep" => "Lakshadweep", "Madhya Pradesh" => "Madhya Pradesh",
    "Maharashtra" => "Maharashtra", "Manipur" => "Manipur", "Meghalaya" => "Meghalaya",
    "Mizoram" => "Mizoram", "Nagaland" => "Nagaland", "Odisha" => "Odisha",
    "Puducherry" => "Puducherry", "Punjab" => "Punjab", "Rajasthan" => "Rajasthan",
    "Sikkim" => "Sikkim", "Tamil Nadu" => "Tamil Nadu", "Telangana" => "Telangana",
    "Tripura" => "Tripura", "Uttar Pradesh" => "Uttar Pradesh", "Uttarakhand" => "Uttarakhand",
    "West Bengal" => "West Bengal"
);
$currentState = $D["state"] ?? "";
foreach ($states as $k => $v) {
    $sel = ($currentState == $k) ? ' selected="selected"' : '';
    $stateOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Form arrays - Basic Info
$arrForm = array(
    array("type" => "text", "name" => "warehouseCode", "value" => $warehouseCode, "title" => "Warehouse Code", "validate" => "required"),
    array("type" => "text", "name" => "warehouseName", "value" => $D["warehouseName"] ?? "", "title" => "Warehouse Name", "validate" => "required"),
    array("type" => "select", "name" => "warehouseType", "value" => $warehouseTypeOpt, "title" => "Warehouse Type", "validate" => "required", "default" => false),
    array("type" => "text", "name" => "gstin", "value" => $D["gstin"] ?? "", "title" => "GSTIN"),
    array("type" => "text", "name" => "tradeName", "value" => $D["tradeName"] ?? "", "title" => "Trade Name"),
);

// Address
$arrForm1 = array(
    array("type" => "text", "name" => "addressLine1", "value" => $D["addressLine1"] ?? "", "title" => "Address Line 1", "validate" => "required"),
    array("type" => "text", "name" => "addressLine2", "value" => $D["addressLine2"] ?? "", "title" => "Address Line 2"),
    array("type" => "text", "name" => "city", "value" => $D["city"] ?? "", "title" => "City", "validate" => "required"),
    array("type" => "select", "name" => "state", "value" => $stateOpt, "title" => "State", "validate" => "required", "default" => false),
    array("type" => "text", "name" => "pincode", "value" => $D["pincode"] ?? "", "title" => "Pincode", "validate" => "required"),
);

// Contact
$arrForm2 = array(
    array("type" => "text", "name" => "contactPerson", "value" => $D["contactPerson"] ?? "", "title" => "Contact Person"),
    array("type" => "text", "name" => "contactPhone", "value" => $D["contactPhone"] ?? "", "title" => "Contact Phone"),
    array("type" => "text", "name" => "contactEmail", "value" => $D["contactEmail"] ?? "", "title" => "Contact Email"),
    array("type" => "text", "name" => "latitude", "value" => $D["latitude"] ?? "", "title" => "Latitude"),
    array("type" => "text", "name" => "longitude", "value" => $D["longitude"] ?? "", "title" => "Longitude"),
);

// Settings
$arrForm3 = array(
    array("type" => "checkbox", "name" => "isActive", "value" => $D["isActive"] ?? 1, "title" => "Active"),
    array("type" => "checkbox", "name" => "canReceiveStock", "value" => $D["canReceiveStock"] ?? 1, "title" => "Can Receive Stock"),
    array("type" => "checkbox", "name" => "canDispatchStock", "value" => $D["canDispatchStock"] ?? 1, "title" => "Can Dispatch Stock"),
    array("type" => "checkbox", "name" => "isDefaultWarehouse", "value" => $D["isDefaultWarehouse"] ?? 0, "title" => "Default Warehouse"),
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
            <h2 class="form-head">Address Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Contact & Location</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm2); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Settings</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm3); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

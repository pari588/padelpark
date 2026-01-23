<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-product-inquiry.inc.js'); ?>"></script>
<?php
$id = 0;
$D = array();
//Start: Prepared arrays to select box checkbox and radio button.
$dutyArr = array("1" => "S1", "2" => "S2", "3" => "S3", "4" => "S4", "5" => "Other");
$MountingArr = array("1" => "B3 - FOOT", "2" => "B5 - FLANGE", "3" => "B35 - FOOT CUM FLANGE", "4" => "V1 - VERTICAL FLANGE", "5" => "B14 - FACE MOUNTED", "6" => "Other");
$typeOfMotorArr = array("1" => "TEFC - SAFE AREA STANDARD", "2" => "FLAME PROOF - GAS GROUP IIA/IIB", "3" => "FLAME PROOF - GAS GROUP IIC", "4" => "INCREASED SAFETY - Ex'e'", "5" => "NON SPARKING - Ex'n'", "6" => "Other");
$rotorTypeArr = array("1" => "SQUIRREL CAGE", "2" => "SLIP RING");
$voltageArr = array("1" => "415", "2" => "380", "3" => "440", "4" => "460", "4" => "480", "5" => "Other");
$frequencyArr = array("1" => "50", "2" => "60");
$shaftExtensionArr = array("1" => "SINGLE", "2" => "DOUBLE", "3" => "Other");
$expectedDeliveryTimeArr = array("1" => "EX.STOCK", "2" => "1-4 WEEKS", "3" => "4-8 WEEKS", "4" => "MORE THAN 8 WEEKS", "5" => "Other");
$offerRequirementIsArr = array("1" => "Estimated", "2" => "Firm");
$requirementForRplcArr = array("1" => "Yes", "0" => "No");
$poleArr = array("1" => "2", "2" => "4", "3" => "6", "4" => "8");
// End.
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}

$arrForm = array(
    array("type" => "text", "name" => "companyName", "value" => $D["companyName"], "title" => "Company Name", "attr" => ' readonly', true),
    array("type" => "text", "name" => "userName", "value" => $D["userName"], "title" => "Name", "attr" => ' readonly'),
    array("type" => "text", "name" => "userEmail", "value" => $D["userEmail"], "title" => "Email", "attr" => ' readonly'),
    array("type" => "text", "name" => "userMobile", "value" => $D["userMobile"], "title" => "Mobile", "attr" => ' readonly'),
    array("type" => "text", "name" => "makeOfMotor", "value" => $D["makeOfMotor"], "title" => "Make of Motor", "attr" => ' readonly'),
    array("type" => "text", "name" => "kw", "value" => $D["kw"], "title" => "KW", "attr" => ' readonly'),
    array("type" => "text", "name" => "hp", "value" => $D["hp"], "title" => "HP", "attr" => ' readonly'),
    array("type" => "select", "name" => "dutyID", "value" =>getArrayDD(["data" => array("data" => $dutyArr), "selected" => ($D["dutyID"] ??0)]), "title" => "Duty:", "attrp" => ' class="other" otherName="duty-other" rel="' . $dutyArr[$D["dutyID"]] . '" id="dutyID"'),
    array("type" => "text", "name" => "dutyOther", "value" => $D["dutyOther"], "attrp" => ' class="duty-other"  style="display:none"'),
    array("type" => "text", "name" => "rpm", "value" => $D["rpm"], "title" => "RPM", "attr" => ' readonly'),
    array("type" => "select", "name" => "mountingID", "value" => getArrayDD(["data" => array("data" => $MountingArr), "selected" => ( $D["mountingID"] ??0)]), "title" => "Mounting:", "attrp" => ' class="other" otherName="mounting-other" rel="' . $MountingArr[$D["mountingID"]] . '" id="mountingID"'),
    array("type" => "text", "name" => "mountingOther", "value" => $D["mountingOther"], "attrp" => ' class="mounting-other" style="display:none"'),
    array("type" => "select", "name" => "typeOfMotorID", "value" =>getArrayDD(["data" => array("data" => $typeOfMotorArr), "selected" => ( $D["typeOfMotorID"] ??0)]), "title" => "Type of Motor:", "attrp" => ' class="other" otherName="typeOfMotor-other" rel="' . $typeOfMotorArr[$D["typeOfMotorID"]] . '" id="typeOfMotorID"'),
    array("type" => "text", "name" => "typeOfMotorOther", "value" => $D["typeOfMotorOther"], "attrp" => ' class="typeOfMotor-other" style="display:none"'),

);
$arrForm3 = array(
    array("type" => "text", "name" => "rotorTypeID", "value" => $rotorTypeArr[$D["rotorTypeID"]], "title" => "Rotor Type"),
    array("type" => "select", "name" => "voltageID", "value" => getArrayDD(["data" => array("data" => $voltageArr), "selected" => ($D["voltageID"] ??0)]), "title" => "Voltage:", "attrp" => ' class="other" otherName="voltage-other" rel="' . $voltageArr[$D["voltageID"]] . '" id="voltageID"'),
    array("type" => "text", "name" => "voltageOther", "value" => $D["voltageOther"], "attrp" => ' class="voltage-other" style="display:none"'),
    array("type" => "text", "name" => "frequencyID", "value" => $frequencyArr[$D["frequencyID"]], "title" => "Frequency"),
    array("type" => "select", "name" => "shaftExtensionID", "value" =>  getArrayDD(["data" => array("data" => $shaftExtensionArr), "selected" => ( $D["shaftExtensionID"] ??0)]), "title" => "Shaft Extension:", "attrp" => ' class="other" otherName="shaft-extension-other" rel="' . $shaftExtensionArr[$D["shaftExtensionID"]] . '" id="shaftExtensionID"'),
    array("type" => "text", "name" => "shaftExtensionOther", "value" => $D["shaftExtensionOther"], "attrp" => ' class="shaft-extension-other" style="display:none"'),
    array("type" => "select", "name" => "expectedDeliveryTimeID", "value" => getArrayDD(["data" => array("data" => $expectedDeliveryTimeArr), "selected" => ( $D["expectedDeliveryTimeID"] ??0)]), "title" => "Expected Delivery Time:", "attrp" => ' class="other" otherName="expect-delivery-time" rel="' . $expectedDeliveryTimeID[$D["expectedDeliveryTimeID"]] . '" id="expectedDeliveryTimeID"'),
    array("type" => "text", "name" => "expectedDeliveryTimeOther", "value" => $D["expectedDeliveryTimeOther"], "attrp" => ' class="expect-delivery-time" style="display:none"'),
    array("type" => "checkbox", "name" => "offerRequirementIs", "value" => array($offerRequirementIsArr, explode(",", $D["offerRequirementIs"])), "title" => "Offer Requirement Is"),
    array("type" => "file", "name" => "uploadFile", "value" => array($D["uploadFile"], $id), "title" => "upload File"),
    array("type" => "radio", "name" => "requirementIsForRplc", "value" => array($requirementForRplcArr, $D["requirementIsForRplc"]), "title" => "Requirement Is For Replacement", "attrp" => ' class="requirement-replacement" rel="' . $requirementForRplcArr[$D["requirementIsForRplc"]] . '" id="requirementIsForRplc"'),
);

$arrFrom1 = array(
    array("type" => "text", "name" => "makeOfMotorD", "value" => $D["makeOfMotorD"], "title" => "Make of Motor", "attrp" => ' class="c2" ', "attr" => ' readonly'),
    array("type" => "text", "name" => "kwD", "title" => "KW", "value" => $D["kwD"], "attrp" => ' class="c2" ', "attr" => ' readonly'),
    array("type" => "text", "name" => "hpD", "title" => "HP", "value" => $D["hpD"], "attrp" => ' class="c2" ', "attr" => ' readonly'),
    array("type" => "text", "name" => "rpmD", "title" => "RPM", "value" => $D["rpmD"], "attrp" => ' class="c2" ', "attr" => ' readonly'),
    array("type" => "text", "name" => "mounting", "title" => "Mounting", "value" => $D["mounting"], "attrp" => ' class="c2" ', "attr" => ' readonly'),
    array("type" => "text", "name" => "poleID", "value" => $poleArr[$D["poleID"]], "title" => "Pole", "attrp" => ' class="c2" ', "attr" => ' readonly'),
    array("type" => "text", "name" => "application", "title" => "Application", "value" => $D["application"], "attrp" => ' class="c2" ', "attr" => ' readonly'),
    array("type" => "file", "name" => "uploadFileD", "value" => array($D["uploadFileD"], $id), "title" => "upload File", "attrp" => ' class="c2" ', "attr" => ' readonly')
);
$arrFrom2 = array(
    array("type" => "text", "name" => "otherSpec", "value" => $D["otherSpec"], "title" => "Any Other Specification", "attr" => ' readonly')
);
$MXFRM = new mxForm();
?>
<div class="wrap-right">

    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Product Contact Info</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Product Contact Info</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm3);
                ?>
            </ul>
            <div class="tbl-form motor-details" style='display:none'>
                <h4 class="form-head m-detail">Provide Existing Motor details</h4>
                <ul class="tbl-form">
                    <?php
                    echo $MXFRM->getForm($arrFrom1);
                    ?>
                </ul>
            </div>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrFrom2);
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
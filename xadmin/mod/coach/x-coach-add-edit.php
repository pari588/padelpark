<?php
$arrWhere    = array("sql" => "status=? ", "types" => "i", "vals" => array(1));
$params = ["table" => $DB->pre . "state", "key" => "stateID", "val" => "stateName", "where" => $arrWhere];
$arrState  = getDataArray($params);

$id = 0;
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT SS.*,S.stateName  FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS SS 
                LEFT JOIN " . $DB->pre . "state AS S ON SS.stateID = S.stateID  
                WHERE SS.status=? AND SS.vendorID =?";
    $D = $DB->dbRow();
}

if (!isset($D['isActive']))
    $D['isActive'] = 1;

$arrForm = array(
    array("type" => "text", "name" => "vendorName", "value" => htmlentities($D["vendorName"] ?? ""), "title" => "Company Name", "validate" => "required", "attrp" => " class='c2'"),
    array("type" => "text", "name" => "contactPerson",  "value" => htmlentities($D["contactPerson"] ?? ""), "title" => "Contact Person", "attrp" => " class='c2'"),
    array("type" => "text", "name" => "phoneNo",  "value" => $D["phoneNo"] ?? "", "title" => "Contact No","validate" => "number,minlen:10,maxlen:10", "attrp" => " class='c2'"),
    array("type" => "text", "name" => "emailID", "validate" => "email", "value" => $D["emailID"] ?? "", "title" => "Email", "validate" => "email", "attrp" => " class='c2'"),
    array("type" => "textarea", "name" => "addressLine", "value" => htmlentities($D["addressLine"] ?? ""), "title" => "Address Line"),


);

$arrForm1 = array(
    array("type" => "select", "name" => "stateID", "value" => getArrayDD(["data" => $arrState, "selected" => $D["stateID"] ?? 0]), "validate" => "required", "title" => "Select State", "attrp" => " class='c1'"),
    array("type" => "text", "name" => "cityName", "value" => htmlentities($D["cityName"] ?? ""), "title" => "City Name", "attrp" => " class='c1'"),
    array("type" => "text", "name" => "postalCode", "value" => $D["postalCode"] ?? "", "title" => "Postal Code", "validate" => "number,minlen:6,maxlen:6", "attrp" => " class='c1'"),
    array("type" => "text", "name" => "gstNumber", "value" => $D["gstNumber"] ?? "", "title" => "GST Number","validate" => "minlen:15,maxlen:15", "attrp" => " class='c1'","attr" => " class='gstNumber'"),
    array("type" => "text", "name" => "panNumber", "value" => $D["panNumber"] ?? "", "title" => "Pan Number","validate" => "minlen:10,maxlen:10", "attrp" => " class='c1'","attr" => " class='panNumber'"),

    array("type" => "checkbox", "name" => "isActive", "value" => $D["isActive"] ?? "", "title" => "Is Active", "attrp" => " class='c2'", "attrp" => " class='c1'"),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f70">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm, array("orgID" => $D["orgID"] ?? 0)); ?></ul>
        </div>
        <div class="wrap-form vendor f30">
            <ul class="tbl-form"><?php echo $MXFRM->getForm($arrForm1); ?></ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
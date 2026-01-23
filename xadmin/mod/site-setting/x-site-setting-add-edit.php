<?php
$id = 0;
$D = array();

$arrWhere    = array("sql" => "status=? ", "types" => "i", "vals" => array(1));
$params = ["table" => $DB->pre . "state", "key" => "stateID", "val" => "stateName", "where" => $arrWhere];
$arrState  = getDataArray($params);

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}
$arrForm = array(
    array("type" => "text", "name" => "contactNo", "value" => $D["contactNo"] ?? "", "title" => "Contact Number", "validate" => "required"),
    array("type" => "text", "name" => "contactMail", "value" => $D["contactMail"] ?? "", "title" => "Contact Mail"),
    array("type" => "text", "name" => "twitterUrl", "value" => $D["twitterUrl"] ?? "", "title" => "Twitter URL"),
    array("type" => "text", "name" => "facebookUrl", "value" => $D["facebookUrl"] ?? "", "title" => "Facebook URL"),
    array("type" => "text", "name" => "pintrestUrl", "value" => $D["pintrestUrl"] ?? "", "title" => "Pintrest URL"),
    array("type" => "text", "name" => "instaUrl", "value" => $D["instaUrl"] ?? "", "title" => "Instagram URL"),
    array("type" => "editor", "name" => "siteFooterInfo", "value" => $D["siteFooterInfo"], "title" => "Site Footer Information", "params" => array("toolbar" => "basic", "height" => 240), "attrp" => ' width="40%"')

);
$arrForm1 = array(
    array("type" => "text", "name" => "invoiceTitle", "value" => $D["invoiceTitle"] ?? "", "title" => "Invoice Title"),
    array("type" => "textarea", "name" => "invoiceAddr", "value" => $D["invoiceAddr"] ?? "", "title" => "Invoice Address"),
    array("type" => "text", "name" => "pinCode", "value" => $D["pinCode"] ?? "", "title" => "Pin Code"),
    array("type" => "select", "name" => "stateID", "value" => getArrayDD(["data" => $arrState, "selected" => $D["stateID"] ?? 0]), "validate" => "required", "title" => "Select State", "attrp" => " class='c1'"),
    array("type" => "text", "name" => "webUrl", "value" => $D["webUrl"] ?? "", "title" => "Website URL"),
    array("type" => "text", "name" => "communicationEmail", "value" => $D["communicationEmail"] ?? "", "title" => "Communication Email")

);
$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Site Common Information</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Invoice Information</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm1);
                ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
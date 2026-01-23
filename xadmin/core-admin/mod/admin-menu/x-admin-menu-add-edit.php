<?php
$arrP = array();
$strOpt = "";

$D = array("menuTitle" => "", "xOrder" => "", "params" => "", "hideMenu" => "", "forceNav" => "", "seoUri" => "", "parentID" => "");
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"]);
    $DB->types = "ii";
    $DB->vals = array(1, $id);
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND " . $MXMOD["PK"] . "=?";
    $D = $DB->dbRow();
    if ($D["menuType"] == 1) {
        $optAmWhere = array("sql" => "status=? AND adminMenuID != ? AND menuType!=?", "types" => "iii", "vals" => array(1, $D["adminMenuID"], 1));
        $strOpt  = getTableDD(["table" => $DB->pre . "x_admin_menu", "key" => "seoUri", "val" => "menuTitle", "selected" => ($D["seoUri"] ?? ""), "where" => $optAmWhere, "lang" => false]);
        $arrP = array("type" => "select", "name" => "seoUri", "value" => $strOpt, "title" => "Deafault Menu");
    } else {
        $optAmWhere = array("sql" => "parentID=? AND status=? AND adminMenuID != ?", "types" => "iii", "vals" => array(0, 1, $D["adminMenuID"]));
        $strOpt  = getTableDD(["table" => $DB->pre . "x_admin_menu", "key" => "adminMenuID", "val" => "menuTitle", "selected" => ($D["parentID"] ?? ""), "where" => $optAmWhere, "lang" => false]);
        $arrP = array("type" => "select", "name" => "parentID", "value" => $strOpt, "title" => "Menu Group");
    }
} else {
    $arrP = array("type" => "select", "name" => "seoUri", "value" => $strOpt, "title" => "Deafault Menu", "prop" => '');
}


$arrFN = ["data" => ["add" => "Add Page", "edit" => "Edit Page", "list" => "List Page"]];
$strOpt = getArrayDD(array("data" => $arrFN, "selected" => ($D['forceNav'] ?? "")));

$arrForm1 = array(
    array("type" => "text", "name" => "menuTitle", "value" => $D["menuTitle"], "title" => "Menu Title", "validate" => "required,name"),
    array("type" => "text", "name" => "xOrder", "value" => $D["xOrder"], "title" => "Menu Order"),
);

$arrForm2 = array(
    array("type" => "text", "name" => "params", "value" => $D["params"], "title" => "Params", "info" => '<span class="info">eg: ?x=200</span>'),
    array("type" => "checkbox", "name" => "hideMenu", "value" => $D["hideMenu"], "title" => "Hide Menu From Sidebar"),
    array("type" => "select", "name" => "forceNav", "value" => $strOpt, "title" => "Force Navigation to")
);

if ($arrP)
    array_unshift($arrForm1, $arrP);
$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm2); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
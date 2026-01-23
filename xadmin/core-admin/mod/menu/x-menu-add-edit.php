<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . 'menu.inc.js'); ?>"></script>
<?php
$menuType = "module";
$id = 0;
$D = array("menuTitle" => "", "parentID" => "", "menuType" => "", "seoUri" => "", "templateIDD" => 0, "templateIDS" => 0, "menuClass" => "", "xOrder" => "", "menuTarget" => "", "menuImage" => "");
if ($TPL->pageType == "edit") {
    $id = intval($_GET["id"]);
    $DB->vals = array($MXSTATUS, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` = ?";
    $D = $DB->dbRow();
}

$DB->sql = "SELECT menuID,menuTitle,parentID FROM `" . $DB->pre . "x_menu` ORDER BY xOrder ASC";
$arrCats = $DB->dbRows();
$strOpt = getTreeDD($arrCats, "menuID", "menuTitle", "parentID", ($D['parentID'] ?? ""));

$arrMenuType = ["data"=>["dynamic" => "Dynamic", "static" => "Static", "exlink" => "Ext. Link", "other" => "Other"]];

$exlink = "";
if ($D["menuType"] == "exlink")
    $exlink = $D["seoUri"];

if ($D["menuType"] == "dynamic")
    $D["templateIDD"] = $D["templateID"];

if ($D["menuType"] == "static")
    $D["templateIDS"] = $D["templateID"];

if (!isset($D['templateIDD']))
    $D["templateIDD"] = "";

if (!isset($D['templateIDS']))
    $D["templateIDS"] = "";

    
$arrForm = array(
    array("type" => "hidden", "name" => "seoUri", "value" => $D["seoUri"]),
    array("type" => "select", "name" => "parentID", "value" => $strOpt, "title" => "Menu Parent"),
    array("type" => "select", "name" => "menuType", "value" => getArrayDD(["data" => $arrMenuType, "selected" => $D['menuType'] ?? ""]), "title" => "Menu Type", "validate" => "required"),
    array("type" => "select", "name" => "templateIDD", "value" => getArrayDD(["data" => getTemplateMod(0), "selected" => $D['templateIDD'] ?? ""]), "title" => "Dynamic Module", "attrp" => ' class="dynamic" style="display:none;"'),
    array("type" => "autocomplete", "name" => "seoUriAC", "value" => $D["seoUri"], "title" => "Enter text to search", "attrp" => ' class="dynamic" style="display:none;"', "params" => array("send" => "templateIDD")),
    array("type" => "select", "name" => "templateIDS", "value" => getArrayDD(["data" => getTemplateMod(1), "selected" => $D['templateIDS'] ?? ""]), "title" => "Static Module", "attrp" => ' class="static" style="display:none;"'),
    array("type" => "text", "name" => "exlink", "value" => $exlink, "title" => "External Url", "attrp" => ' class="exlink" style="display:none;"'),
    array("type" => "text", "name" => "menuTitle", "value" => $D["menuTitle"], "title" => "Menu Title", "validate" => "required"),
);

$arrForm1 = array(
    array("type" => "text", "name" => "menuClass", "value" => $D["menuClass"], "title" => "Menu Class"),
    array("type" => "text", "name" => "xOrder", "value" => $D["xOrder"], "title" => "Menu Order"),
    array("type" => "checkbox", "name" => "menuTarget", "value" => $D["menuTarget"], "title" => "Open New Window"),
    array("type" => "file", "name" => "menuImage", "value" => array($D["menuImage"], $id), "title" => "Menu Image", "validate" => "")
);
$MXFRM = new mxForm();
$MXFRM->meta = false;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm, array("orgID" => $D["orgID"] ?? 0)); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
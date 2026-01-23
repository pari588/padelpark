<?php
$id = 0;

$orgVals = array(1,0);
$orgTypes = "ii";
$orgWhere = "";
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = $ORGID = intval($_GET["id"]);
    $DB->types = "ii";
    $DB->vals = array(1, $id);
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND " . $MXMOD["PK"] . "=?";
    $D = $DB->dbRow();
    if(isset($D["parentID"]) && $D["parentID"] >= 0)
        $PARENTID = $D["parentID"];
    array_push($orgVals, $D["orgID"]);
    $orgTypes = $orgTypes . "i";
    $DB->types = "i";
    $orgWhere = " AND orgID != ?";
}



$DB->types = $orgTypes;
$DB->vals = $orgVals;
$DB->sql = "SELECT orgID,orgName,parentID FROM `" . $DB->pre . "x_organization` WHERE status=? AND parentID=?" . $orgWhere;
$arrCats = $DB->dbRows();
$strOptOrg = getTreeDD($arrCats, "orgID", "orgName", "parentID", $D['parentID'] ?? 0);

$arrForm = array(
    array("type" => "select", "name" => "parentID", "value" => $strOptOrg, "title" => "Parent Organization", "attrp" => ' class="c4"'),
    array("type" => "text", "name" => "orgName", "value" => $D["orgName"] ?? "", "title" => "Organization Name", "validate" => "required,name,minlen:5", "attrp" => ' class="c4"'),
    array("type" => "text", "name" => "orgEmail", "value" => $D["orgEmail"] ?? "", "title" => "Email", "validate" => "email", "attrp" => ' class="c4"'),
    array("type" => "file", "name" => "orgImage", "value" => array($D["orgImage"] ?? "", $id ?? 0), "title" => "photo/logo", "validate," => "image", "attrp" => ' class="c4"'),
);

$MXFRM = new mxForm();

?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <?php
            $orgSubMod = "";
            if (isset($MXSET["ORGPARENTMOD"]) && $MXSET["ORGPARENTMOD"] != "" && $PARENTID == 0) {
                $orgSubMod = $MXSET["ORGPARENTMOD"];
            }
            if (isset($MXSET["ORGCHILDMOD"]) && $MXSET["ORGCHILDMOD"] != "" && $PARENTID > 0) {
                $orgSubMod = $MXSET["ORGCHILDMOD"];
            }
            if ($orgSubMod !== "")
                require(ADMINPATH . "/mod/$orgSubMod/x-$orgSubMod-add-edit.php");
        ?>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
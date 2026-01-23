<script language="javascript" type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-admin-user.inc.js'); ?>"></script>
<?php
$vPass = "required,";
$vCPass = "required,";
$id = 0;

$D = array("displayName" => "", "userName" => "", "userEmail" => "", "imageName" => "", "roleID" => 0);
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"]);
    $DB->types = "ii";
    $DB->vals = array(1, $id);
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND " . $MXMOD["PK"] . "=?";
    $D = $DB->dbRow();
    $vPass = "";
    $vCPass = "";
}
$arrWhere = array("sql" => "status = ?", "types" => "i", "vals" => array(1));
$params = ["table" => $DB->pre . "x_admin_role", "key" => "roleID", "val" => "roleName", "where" => $arrWhere, "lang" => false];
$arrRole  = getDataArray($params);

$DB->sql = "SELECT roleID,roleName,parentID FROM `" . $DB->pre . "x_admin_role` ORDER BY xOrder ASC";
$arrRole = $DB->dbRows();
$strRoleOpt = getTreeDD($arrRole, "roleID", "roleName", "parentID", ($D['roleID'] ?? ""));

$arrForm = array(
    array("type" => "text", "name" => "displayName", "value" => ($D["displayName"] ?? ""), "title" => "Full Name", "validate" => "required,name,minlen:5"),
    array("type" => "text", "name" => "userName", "value" => ($D["userName"] ?? ""), "title" => "Login Name", "validate" => "required,loginname"),
    array("type" => "text", "name" => "userMobile", "value" => ($D["userMobile"] ?? ""), "title" => "Login Mobile No"),
    array("type" => "text", "name" => "userEmail", "value" => ($D["userEmail"] ?? ""), "title" => "Login Email", "validate" => "email"),
    array("type" => "password", "name" => "userPass", "value" => "", "title" => "Password", "validate" => $vPass . "password"),
    array("type" => "password", "name" => "userPass1", "value" => "", "title" => "Varify Password", "validate" => $vCPass . "password,equalto:userPass")  
);

$arrFormS = array(
    array("type" => "select", "name" => "roleID", "value" => $strRoleOpt, "title" => "User Role", "validate" => "required"),
    array("type" => "file", "name" => "imageName", "value" => array(($D["imageName"] ?? ""), $id), "title" => "Photo", "validate," => "image"),
);

$arrLeaveForm = array(
    array("type" => "mxstring", "value" => '<span> Unauthorized : '.($D['unauthorized'] ?? 0).'</span>', "attrp" => ' class="c2"'),
    array("type" => "mxstring", "value" => '<a href="#" data-id='.($D['userID'] ?? 0).' class="btn fa-reset resetLeaveCount o"> RESET</a>', "attrp" => ' class="c3"'),
    array("type" => "text", "name" => "totalLeaves", "value" => ($D["totalLeaves"] ?? ""), "title" => "User total leaves", "validate" => "number,min:0,max:100","attrp" => ' class="c1"'),
    array("type" => "text", "name" => "userPin", "value" => ($D["userPin"] ?? ""), "title" => "User Pin", "validate" => "number,min:0,maxlen:4","attrp" => ' class="c1"'),
    array("type" => "checkbox", "name" => "isLeaveManager", "value" => $D["isLeaveManager"] ?? 0, "title" => "Is Leave Manager", "nolabel" => true, "attrp" => ' class="c1"'),
    array("type" => "checkbox", "name" => "techIlliterate", "value" => $D["techIlliterate"] ?? 0, "title" => "Technologically illiterate", "nolabel" => true, "attrp" => ' class="c1"'),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data" auto="false">
        <div class="wrap-form  f70">
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <div class="wrap-form f30">
            <ul class="tbl-form">
                    <?php echo $MXFRM->getForm($arrFormS, array("orgID" => $D["orgID"] ?? 0)); ?>
                <fieldset>
                    <p>LEAVES SETTING</p>
                    <?php echo $MXFRM->getForm($arrLeaveForm, array("orgID" => $D["orgID"] ?? 0)); ?>
                </fieldset>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
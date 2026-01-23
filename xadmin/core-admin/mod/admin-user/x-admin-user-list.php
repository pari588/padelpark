<?php
$arrWhere = array("sql" => "status = ?", "types" => "i", "vals" => array(1));
$roleOpt  = getTableDD(["table" => $DB->pre . "x_admin_role", "key" => "roleID", "val" => "roleName", "selected" => ($_GET["roleID"] ?? ""), "where" => $arrWhere, "lang" => false]);

$arrSearch = array(
    array("type" => "text", "name" => "userID",  "title" => "#ID", "where" => "AND U.userID=?", "dtype" => "i"),
    array("type" => "text", "name" => "displayName",  "title" => "Full Name", "where" => "AND U.displayName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userName", "title" => "Login Name", "where" => "AND U.userName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userEmail", "title" => "Email", "where" => "AND U.userEmail LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "userMobile", "title" => "Mobile", "where" => "AND U.userMobile LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "roleID", "value" => $roleOpt, "title" => "User ROle", "where" => "AND U.roleID=?", "dtype" => "i")
);
$MXFRM = new mxForm(false, true);
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT U." . $MXMOD['PK'] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS U LEFT JOIN `" . $DB->pre . "x_admin_role` AS T ON U.roleID = T.roleID WHERE U.status=?" . $MXFRM->where . mxWhere("U.", false);
$arrExport = array("vals" => $DB->vals, "types" => $DB->types, "sql" => $DB->sql);
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if ($MXTOTREC < 1) {
    if ((!isset($MXFRM->where) || $MXFRM->where == "")) {
        $strSearch = "";
    }
} else {
    if (function_exists("setModVars")) setModVars(array("EXPSQL" => $arrExport, "EXPCOLS" => array("U.userID" => "#ID", "U.displayName" => "Display Name", "U.userName" => "Login Name", "U.userEmail" => "Email", "T.roleName" => "User Role")));
}

echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "userID", ' width="1%" align="center"'),
                array("Image", "imageName", ' width="1%" align="center"'),
                array("Name", "displayName", ' align="left"', true),
                array("Login Name", "userName", ' align="left"'),
                array("Email", "userEmail", ' align="left" width="1%" nowrap'),
                array("Mobile No", "userMobile", ' align="left" width="1%" nowrap'),
                array("Role/Type", "roleName", ' align="left" width="1%" nowrap'),
                array("Last Login", "dateLogin", ' align="center" width="1%" nowrap')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT U.*,T.roleName FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS U LEFT JOIN `" . $DB->pre . "x_admin_role` AS T ON U.roleID=T.roleID WHERE U.status= ?" . $MXFRM->where . mxWhere("U.", false) . mxOrderBy("U.userID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list no-resp">
                <thead>
                    <tr>
                        <?php echo getListTitle($MXCOLS); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                        if ($d["imageName"] != "") {
                            $arrFile = explode(",", $d["imageName"]);
                            $d["imageName"] = getFile(array("path" => "x_admin_user/" . $arrFile[0], "title" => $d["displayName"]));
                        }
                    ?>
                        <tr>
                            <?php echo getMAction("mid", $d["userID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3]) {
                                        echo getViewEditUrl("id=" . $d["userID"], $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]];
                                    }
                                    ?></td>
                                <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>
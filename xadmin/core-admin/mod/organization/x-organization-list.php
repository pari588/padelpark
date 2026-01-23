<?php
$arrSearch = array(
    array("type" => "text", "name" => "orgID",  "title" => "#ID", "where" => "AND orgID=?", "dtype" => "i"),
    array("type" => "text", "name" => "orgName",  "title" => "Full Name", "where" => "AND orgName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "orgEmail", "title" => "Email", "where" => "AND orgEmail LIKE CONCAT('%',?,'%')", "dtype" => "s")
);
$MXFRM = new mxForm(false, true);
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT " . $MXMOD['PK'] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=?" . $MXFRM->where;
$arrExport = array("vals" => $DB->vals, "types" => $DB->types, "sql" => $DB->sql);
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if ($MXTOTREC < 1) {
    if ((!isset($MXFRM->where) || $MXFRM->where == "")) {
        $strSearch = "";
    }
} else {
    if (function_exists("setModVars")) setModVars(array("EXPSQL" => $arrExport, "EXPCOLS" => array("orgID" => "#ID", "orgName" => "Display Name", "userName" => "Login Name", "orgEmail" => "Email", "T.roleName" => "User Role")));
}

echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "orgID", ' width="1%" align="center"'),
                array("Logo/Image", "orgImage", ' width="1%" align="center"'),
                array("Organization Name", "orgName", ' align="left"', true),
                array("SEORUI", "seoUri", ' align="left"'),
                array("Email", "orgEmail", ' align="left" width="1%" nowrap')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status= ?" . $MXFRM->where . mxWhere("", false) . mxOrderBy("orgID DESC") . mxQryLimit();
            $DB->dbRows();
            $arrD = getDepthArray($DB->rows, "parentID", "orgID");
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list no-resp">
                <thead>
                    <tr>
                        <?php echo getListTitle($MXCOLS); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($arrD as $d) {
                        if ($d["orgImage"] != "") {
                            $arrFile = explode(",", $d["orgImage"]);
                            $d["orgImage"] = getFile(array("path" => "organization/" . $arrFile[0], "title" => $d["orgName"]));
                        }
                        if (isset($d["depth"]) && $d["depth"] > 0)
                            $d["orgName"] = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $d["depth"]) . "|&rArr; " . $d["orgName"];
                    ?>
                        <tr>
                            <?php echo getMAction("mid", $d["orgID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3]) {
                                        echo getViewEditUrl("id=" . $d["orgID"], $d[$v[1]]);
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
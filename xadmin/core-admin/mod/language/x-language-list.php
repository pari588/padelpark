<?php
$arrSearch = array(
    array("type" => "text", "name" => "langID", "title" => "#ID", "where" => "AND langID=?", "dtype" => "i"),
    array("type" => "text", "name" => "langName", "title" => "Language Name", "where" => "AND langName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "langPrefix", "title" => "Login Name", "where" => "AND langPrefix LIKE CONCAT('%',?,'%')", "dtype" => "s")
);
$MXFRM = new mxForm(false, true);
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT " . $MXMOD['PK'] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? $MXFRM->where";
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if ($MXTOTREC < 1) {
    if ((!isset($MXFRM->where) || $MXFRM->where == "")) {
        $strSearch = "";
    }
}
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "langID", ' width="1%"  align="center"'),
                array("Image", "imageName", ' width="1%" align="center"'),
                array("Language", "langName", ' align="left"', true),
                array("Code", "langPrefix", ' align="center" width="1%"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status= ?" . $MXFRM->where .  mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        if ($d["imageName"] != "") {
                            $arrFile = explode(",", $d["imageName"]);
                            $d["imageName"] = getFile(array("path" => $MXMOD["UDIR"]["imageName"] . "/" . $arrFile[0], "title" => $d["langName"]));
                        }
                    ?>
                        <tr> <?php echo getMAction("mid", $d["langID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3]) {
                                        echo getViewEditUrl("id=" . $d["langID"], $d[$v[1]]);
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
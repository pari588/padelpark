<?php
$D = array("pageTitle" => "");
$arrSearch = array(
    array("type" => "text", "name" => "pageID",  "title" => "#ID", "where" => "AND pageID=?", "dtype" => "i"),
    //array("type" => "text", "name" => "pageID", "title" => "#ID", "where" => "AND pageID='SVAL'", "dtype" => "i"),
    array("type" => "text", "name" => "pageTitle", "title" => "Page Title", "where" => "AND pageTitle LIKE CONCAT('%',?,'%')", "dtype" => "s")
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$expsql = $DB->sql = "SELECT " . $MXMOD['PK'] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=?" . $MXFRM->where . mxWhere();

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
                array("#ID", "pageID", ' width="2%" align="center" title="ID"', true),
                array("Image", "pageImage", ' width="1%" align="center" title="Image"',"","nosort"),
                array("Name", "pageTitle", ' align="left" title="Name"'),
                array("Template File", "templateFile", ' align="left" title="Template File"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=?" . $MXFRM->where . mxWhere() . mxOrderBy(" pageID DESC ") . " LIMIT $MXOFFSET,$MXSHOWREC";
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="6" class="tbl-list">
                <thead>
                    <tr>
                        <?php echo getListTitle($MXCOLS); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                        if ($d["pageImage"] != "") {
                            $arrFile = explode(",", $d["pageImage"]);
                            $d["pageImage"] = getFile(array("path" => "page/" . $arrFile[0], "title" => $d["pageTitle"]));
                        }
                        //$d["changeHistory"] = '<a href="#" class="fa-history btn ico" title="Change History" rel="'.$d["pageID"].'"></a>';
                    ?>
                        <tr>
                            <?php echo getMAction("mid", $d["pageID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $d["pageID"], $d[$v[1]], $d["langChild"]);
                                    } else {
                                        echo $d[$v[1]];
                                    }
                                    ?>
                                    </td>
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
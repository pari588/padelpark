<?php
$arrSearch = array(
    array("type" => "text", "name" => "knowledgeCenterID",  "title" => "#ID", "where" => "AND knowledgeCenterID= ? ", "dtype" => "i"),
    array("type" => "text", "name" => "knowledgeCenterTitle", "title" => "Title", "where" => "AND knowledgeCenterTitle LIKE CONCAT('%',?,'%')", "dtype" => "s"),
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT A." . $MXMOD['PK'] . "   FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS A
            WHERE A.status=? " . $MXFRM->where . mxWhere("A.");
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
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT A.* FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS A   
            WHERE A.status=?" . $MXFRM->where . mxWhere("A.") . mxOrderBy("A.knowledgeCenterID DESC ") . mxQryLimit();
            $DB->dbRows();
            $MXCOLS = array(
                array("#ID", "knowledgeCenterID", ' width="1%" align="center"', true),
                array("Image", "knowledgeCenterImage", ' width="1%" align="center" nowrap'),
                array("Knowledge Center Title", "knowledgeCenterTitle", ' align="left"'),
                array("Source", "knowledgeCenterSource", ' nowrap="nowrap"  align="left"'),
                array("Publish Date", "datePublish", ' nowrap="nowrap"  align="left"'),
            );


        ?>
            <table border="0" cellspacing="0" width="100%" cellpadding="8" class="tbl-list">
                <thead>
                    <tr>
                        <?php echo getListTitle($MXCOLS); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {

                        if ($d["knowledgeCenterImage"] != "") {
                            $arrFile = explode(",", $d["knowledgeCenterImage"]);
                            $d["knowledgeCenterImage"] = getFile(array("path" => "knowledge-center/" . $arrFile[0]));
                        }

                    ?>
                        <tr>
                            <?php echo getMAction("mid", $d["knowledgeCenterID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2]; ?>>
                                    <?php if (isset($v[3]) && $v[3]) {
                                        echo getViewEditUrl("id=" . $d["knowledgeCenterID"], $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]];
                                    } ?>
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
<?php
// START : search array
$arrSearch = array(
    array("type" => "text", "name" => "pettyCashCatID",  "title" => "#ID", "where" => "AND pettyCashCatID=?", "dtype" => "i"),
    array("type" => "text", "name" => "pettyCashCat",  "title" => "category", "where" => "AND pettyCashCat LIKE CONCAT('%',?,'%')", "dtype" => "s", "attr" => ' style= "width:150px;"'),
);
// END
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT " . $MXMOD["PK"] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "`  WHERE status=?" . $MXFRM->where . mxWhere();
$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";

echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "pettyCashCatID", ' width="1%" align="center"', true),
                array("category", "pettyCashCat", '  nowrap align="left"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT pettyCashCat,pettyCashCatID FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? " . $MXFRM->where . mxWhere() . mxOrderBy(" pettyCashCatID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {

                    ?>
                        <tr> <?php echo getMAction("mid", $d["pettyCashCatID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $d["pettyCashCatID"], $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]] ?? "";
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
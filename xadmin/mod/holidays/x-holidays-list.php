<?php
$arrSearch = array(
    array("type" => "text", "name" => "ahID",  "title" => "#ID", "where" => "AND ahID=?", "dtype" => "i"),
    array("type" => "date", "name" => "ahDate",  "title" => "Holiday Date", "where" => "AND ahDate LIKE CONCAT('%',?,'%')", "dtype" => "s")
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT " . $MXMOD["PK"] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "`  WHERE status=? AND YEAR(ahDate) = '" . date('Y') . "'" . $MXFRM->where;
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
                array("#ID", "ahID", ' width="4%" align="center"', true),
                array("Holiday Date", "ahDate", ' width="16%" nowrap align="left"'),
                array("Holiday Reason", "ahReason", ' width="16%" nowrap align="left"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND YEAR(ahDate) = '" . date('Y') . "'" . $MXFRM->where . mxOrderBy("ahDate ASC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) { ?>
                        <tr> <?php echo getMAction("mid", $d["ahID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $d["ahID"], $d[$v[1]]);
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
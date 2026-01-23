<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . 'inc/js/x-expense-type.inc.js'); ?>"></script>
<?php
$arrSearch = array(
    array("type" => "text", "name" => "expenseTypeID",  "title" => "#ID", "where" => "AND expenseTypeID=?", "dtype" => "i"),
    array("type" => "text", "name" => "expenceTitle",  "title" => "Expense Type Title", "where" => "AND expenceTitle LIKE CONCAT('%',?,'%')", "dtype" => "s","attr" => "style='width:170px;'"),
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT " . $MXMOD["PK"] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "`  WHERE status=?" . $MXFRM->where;

$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";
echo $strSearch;

?>
<div class="wrap-right">
    <?php echo getPageNav();  ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "expenseTypeID", ' width="2%" align="center"', true),
                array("Expense Type Title", "expenceTitle", ' nowrap align="left"'),
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? " . $MXFRM->where . mxOrderBy("expenseTypeID DESC ") . mxQryLimit();
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
                        <tr> <?php echo getMAction("mid", $d["expenseTypeID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $d["expenseTypeID"], $d[$v[1]]);
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
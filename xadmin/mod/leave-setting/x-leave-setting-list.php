<?php
// START : search array

// var mySelect = $('#years');
$startYear = 2022;

$currently_selected = date('Y');
$nextYear = date('Y', strtotime(' + 1 years'));
$latest_year = date('Y');
// echo $latest_year;

foreach (range($latest_year, $nextYear) as $i) {
    $finanacialYear = array("April 2022" . " " . "-" . "March" . " " . $latest_year => "April 2022" . " " . "-" . "March" . " " . $latest_year, "April" . " " . $latest_year . "-" . "March" . " " . $nextYear => "April" . " " . $latest_year . "-" . "March" . " " .  $nextYear);
}

$arrSearch = array(
    array("type" => "text", "name" => "leaveSettingID",  "title" => "#ID", "where" => "AND leaveSettingID=?", "dtype" => "i"),
    array("type" => "select", "name" => "FYStartDate", "value" =>  getFinancialArrayDD($_GET["FYStartDate"] ?? ""), "title" => "financial From Year", "where" => "AND YEAR(FYStartDate)=? ", "dtype" => "s", "attr" => "style='width:200px;'"),
    array("type" => "select", "name" => "FYEndDate", "value" =>  getFinancialArrayDD($_GET["FYEndDate"] ?? ""), "title" => "financial To Year", "where" => " AND YEAR(FYEndDate) >= ?", "dtype" => "s", "attr" => "style='width:180px;'")
);

// END

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
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "leaveSettingID", ' width="1%" align="center" title="Leave Setting ID"', true),
                array("financial Year", "FYStartDate", ' width="5%" align="center" title="Financial Year"'),
                array("financial Year", "FYEndDate", ' width="5%" align="center" title="Financial Year"'),

                array("total Leave", "totalLeave", ' width="2%" align="center" title="Total Leave"'),
                array("Date Added", "dateAdded", ' width="4%" align="center" title="Date Of Added"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT *  FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? " . $MXFRM->where . mxOrderBy(" leaveSettingID DESC ") . mxQryLimit();

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
                        <tr> <?php echo getMAction("mid", $d["leaveSettingID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $d["leaveSettingID"], $d[$v[1]]);
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
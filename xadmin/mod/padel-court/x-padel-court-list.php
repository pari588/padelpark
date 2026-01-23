<?php
// START : search array
$arrSearch = array(
    array("type" => "text", "name" => "courtID",  "title" => "#ID", "where" => "AND courtID=?", "dtype" => "i"),
    array("type" => "text", "name" => "courtName",  "title" => "Court Name", "where" => "AND courtName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "centerName",  "title" => "Center Name", "where" => "AND centerName LIKE CONCAT('%',?,'%')", "dtype" => "s")

);
// END
$categoryWhr = array("sql" => "status=? ", "types" => "i", "vals" => array(1));
$params = ["table" => $DB->pre . "court_type", "key" => "typeID", "val" => "typeName", "where" => $categoryWhr];
$categoryArr  = getDataArray($params);

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
                array("#ID", "courtID", ' width="1%" align="center"', true),
                array("Image", "courtImage", ' width="1%" align="center"', "", "nosort"),
                array("Court Name", "courtName", ' width="20%" nowrap align="left"'),
                array("Center Name", "centerName", ' width="20%" align="left"'),
                array("Court Type", "courtType", ' width="15%" align="left"'),
                array("Price/Hour", "pricePerHour", ' width="10%" align="right"'),
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT courtID,courtName,centerName,courtType,pricePerHour,courtImage  FROM `" . $DB->pre . $MXMOD["TBL"] . "`WHERE status=? " . $MXFRM->where . mxOrderBy(" courtID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                        if ($d["courtImage"] != "") {
                            $arrFile = explode(",", $d["courtImage"]);
                            $d["courtImage"] = getFile(array("path" => "padel-court/" . $arrFile[0], "title" => $d["courtImage"]));
                        }
                        // Format price
                        if (isset($d["pricePerHour"])) {
                            $d["pricePerHour"] = "₹" . number_format($d["pricePerHour"], 2);
                        }
                    ?>
                        <tr> <?php echo getMAction("mid", $d["courtID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3])&& $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["courtID"], $d[$v[1]]);
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
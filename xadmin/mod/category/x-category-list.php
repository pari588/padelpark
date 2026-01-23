<?php
$arrSearch = array(
    array("type" => "text", "name" => "categoryID", "title" => "#ID", "where" => "AND categoryID=?", "dtype" => "i"),
    array("type" => "text", "name" => "categoryTitle", "title" => "Category Title", "where" => "AND categoryTitle LIKE CONCAT('%',?,'%')", "dtype" => "s"),
);
$MXFRM = new mxForm(false, true);
$strSearch = $MXFRM->getFormS($arrSearch, false, true);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=?" . $MXFRM->where . mxWhere() . mxOrderBy("parentCategoryID DESC");
$data = $DB->dbRows();
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
                array("#ID", "categoryID", ' width="1%" align="center"', true),
                array("Category Title", "categoryTitle", ' align="left"'),
                array("Current Stock", "currentStock", '  align="left"'),
            );
            $arrD = getDepthArray($data, "parentCategoryID", "categoryID");
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr>
                        <?php echo getListTitle($MXCOLS); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($arrD as $d) {
                        if ($d['parentCategoryID'] == 0) {
                            $d['currentStock'] = '';
                            array_push($MXNOTRASHID, $d['categoryID']);
                        }
                        if (isset($d["depth"])) {
                            $d["categoryTitle"] = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $d["depth"]) . "|&rArr; " . $d["categoryTitle"];
                        }
                    ?>
                        <tr>
                            <?php echo getMAction("mid", $d["categoryID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?> title="<?php echo $v[0]; ?>">
                                    <?php if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["categoryID"], $d[$v[1]]);
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
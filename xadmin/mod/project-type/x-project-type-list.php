<?php
$arrSearch = array(
    array("type" => "text", "name" => "categoryPID", "title" => "#ID", "where" => "AND categoryPID=?", "dtype" => "i"),
    array("type" => "text", "name" => "categoryTitle", "title" => "Category Title", "where" => "AND categoryTitle LIKE CONCAT('%',?,'%')", "dtype" => "s"),
);
$MXFRM = new mxForm(false, true);
$strSearch = $MXFRM->getFormS($arrSearch, false, true);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=?" . $MXFRM->where . mxWhere() . mxOrderBy("parentID DESC");
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
                array("#ID", "categoryPID", ' width="1%" align="center"', true),
                array("Image", "imageName", ' width="1%" align="center" nowrap',"","nosort"),
                array("Category Title", "categoryTitle", ' align="left"'),
                array("Category URI", "seoUri", ' align="left"'),
                array("Display Order", "xOrder", ' width="1%" nowrap align="center"')
            );
            if ($_SESSION[SITEURL]["MXID"] == "SUPER") {
                array_push($MXCOLS, array("Template File", "templateFile", ' align="left"'));
            }
            $arrD = getDepthArray($data, "parentID", "categoryPID");
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

                        if ($d["imageName"] != "") {
                            $arrFile = explode(",", $d["imageName"]);
                            $d["imageName"] = getFile(array("path" => "pump_category/" . $arrFile[0], "title" => $d["categoryTitle"]));
                        }
                          if (isset($d["depth"])) {
                            $d["categoryTitle"] = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $d["depth"]) . "|&rArr; " . $d["categoryTitle"];
                        }
                    ?>
                        <tr>
                            <?php echo getMAction("mid", $d["categoryPID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?> title="<?php echo $v[0]; ?>">
                                    <?php if (isset($v[3])&& $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["categoryPID"], $d[$v[1]], $d["langChild"]);
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
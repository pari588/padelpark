<?php
// Build category type dropdown
$typeArr = array("" => "All Types", "Singles" => "Singles", "Doubles" => "Doubles", "Mixed" => "Mixed");
$typeOpt = '';
$selType = $_GET["categoryType"] ?? "";
foreach ($typeArr as $k => $v) {
    $sel = ($selType == $k) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build age group dropdown
$ageArr = array("" => "All Ages", "Open" => "Open", "U-12" => "U-12", "U-14" => "U-14", "U-16" => "U-16", "U-18" => "U-18", "U-21" => "U-21", "Senior" => "Senior", "Veterans" => "Veterans");
$ageOpt = '';
$selAge = $_GET["ageGroup"] ?? "";
foreach ($ageArr as $k => $v) {
    $sel = ($selAge == $k) ? ' selected="selected"' : '';
    $ageOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "categoryID", "title" => "#ID", "where" => "AND categoryID=?", "dtype" => "i"),
    array("type" => "text", "name" => "categoryName", "title" => "Name", "where" => "AND categoryName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "categoryType", "title" => "Type", "where" => "AND categoryType=?", "dtype" => "s", "value" => $typeOpt, "default" => false),
    array("type" => "select", "name" => "ageGroup", "title" => "Age", "where" => "AND ageGroup=?", "dtype" => "s", "value" => $ageOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT categoryID FROM `" . $DB->pre . "ipt_category` WHERE status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "categoryID", ' width="5%" align="center"', true),
                array("Code", "categoryCode", ' width="10%" align="center"'),
                array("Category Name", "categoryName", ' width="25%" align="left"'),
                array("Type", "categoryType", ' width="12%" align="center"'),
                array("Gender", "genderRestriction", ' width="10%" align="center"'),
                array("Age Group", "ageGroup", ' width="10%" align="center"'),
                array("Skill Level", "skillLevel", ' width="12%" align="center"'),
                array("Players", "maxPlayers", ' width="8%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . "ipt_category` WHERE status=?" . $MXFRM->where . mxOrderBy("sortOrder ASC, categoryName ASC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Type badge
                        $typeColors = array("Singles" => "badge-info", "Doubles" => "badge-primary", "Mixed" => "badge-warning");
                        $d["categoryType"] = '<span class="badge ' . ($typeColors[$d["categoryType"]] ?? "badge-secondary") . '">' . $d["categoryType"] . '</span>';

                        // Age badge
                        $d["ageGroup"] = '<span class="badge badge-secondary">' . $d["ageGroup"] . '</span>';

                        // Skill badge
                        $skillColors = array("Beginner" => "badge-success", "Intermediate" => "badge-info", "Advanced" => "badge-warning", "Pro" => "badge-danger", "Open" => "badge-secondary");
                        $d["skillLevel"] = '<span class="badge ' . ($skillColors[$d["skillLevel"]] ?? "badge-secondary") . '">' . $d["skillLevel"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["categoryID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["categoryID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-trophy" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No categories found. Create tournament categories like Men's Open, Women's Doubles, etc.</p>
            </div>
        <?php } ?>
    </div>
</div>

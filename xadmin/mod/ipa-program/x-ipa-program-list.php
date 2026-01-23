<?php
// Build program type dropdown
$typeArr = array("" => "All Types", "Clinic" => "Clinic", "Private" => "Private", "Group" => "Group", "Camp" => "Camp", "Masterclass" => "Masterclass");
$typeOpt = '';
$selType = $_GET["programType"] ?? "";
foreach ($typeArr as $k => $v) {
    $sel = ($selType == $k) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build skill level dropdown
$levelArr = array("" => "All Levels", "Beginner" => "Beginner", "Intermediate" => "Intermediate", "Advanced" => "Advanced", "All Levels" => "All Levels");
$levelOpt = '';
$selLevel = $_GET["skillLevel"] ?? "";
foreach ($levelArr as $k => $v) {
    $sel = ($selLevel == $k) ? ' selected="selected"' : '';
    $levelOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "programCode", "title" => "Code", "where" => "AND p.programCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "programName", "title" => "Name", "where" => "AND p.programName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "programType", "title" => "Type", "where" => "AND p.programType=?", "dtype" => "s", "value" => $typeOpt, "default" => false),
    array("type" => "select", "name" => "skillLevel", "title" => "Level", "where" => "AND p.skillLevel=?", "dtype" => "s", "value" => $levelOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT p.programID FROM `" . $DB->pre . "ipa_program` p WHERE p.status=?" . $MXFRM->where;
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
                array("Code", "programCode", ' width="12%" align="center"', true),
                array("Program Name", "programName", ' width="25%" align="left"'),
                array("Type", "programType", ' width="12%" align="center"'),
                array("Level", "skillLevel", ' width="12%" align="center"'),
                array("Duration", "sessionDuration", ' width="10%" align="center"'),
                array("Capacity", "capacity", ' width="10%" align="center"'),
                array("Price", "pricePerSession", ' width="10%" align="right"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT p.*
                        FROM `" . $DB->pre . "ipa_program` p
                        WHERE p.status=?" . $MXFRM->where . mxOrderBy("p.programID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format type badge
                        $typeColors = array("Clinic" => "info", "Private" => "warning", "Group" => "success", "Camp" => "primary", "Masterclass" => "danger");
                        $d["programType"] = '<span class="badge badge-' . ($typeColors[$d["programType"]] ?? "secondary") . '">' . $d["programType"] . '</span>';

                        // Format level badge
                        $levelColors = array("Beginner" => "secondary", "Intermediate" => "info", "Advanced" => "warning", "All Levels" => "success");
                        $d["skillLevel"] = '<span class="badge badge-' . ($levelColors[$d["skillLevel"]] ?? "secondary") . '">' . $d["skillLevel"] . '</span>';

                        // Format duration
                        $d["sessionDuration"] = $d["sessionDuration"] . ' mins';

                        // Format capacity
                        $d["capacity"] = $d["minParticipants"] . '-' . $d["maxParticipants"];

                        // Format price
                        $d["pricePerSession"] = 'Rs. ' . number_format($d["pricePerSession"], 0);
                    ?>
                        <tr><?php echo getMAction("mid", $d["programID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["programID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-book" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No programs found. Create your first coaching program.</p>
            </div>
        <?php } ?>
    </div>
</div>

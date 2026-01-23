<?php
// Build coach dropdown
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName FROM " . $DB->pre . "ipa_coach WHERE status=1 ORDER BY firstName";
$coachRows = $DB->dbRows();
$coachOpt = '<option value="">All Coaches</option>';
$selCoach = $_GET["coachID"] ?? "";
foreach ($coachRows as $c) {
    $sel = ($selCoach == $c["coachID"]) ? ' selected="selected"' : '';
    $coachOpt .= '<option value="' . $c["coachID"] . '"' . $sel . '>' . htmlspecialchars($c["coachName"]) . '</option>';
}

// Build player dropdown
$DB->sql = "SELECT playerID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as playerName FROM " . $DB->pre . "ipa_player WHERE status=1 ORDER BY firstName";
$playerRows = $DB->dbRows();
$playerOpt = '<option value="">All Players</option>';
$selPlayer = $_GET["playerID"] ?? "";
foreach ($playerRows as $p) {
    $sel = ($selPlayer == $p["playerID"]) ? ' selected="selected"' : '';
    $playerOpt .= '<option value="' . $p["playerID"] . '"' . $sel . '>' . htmlspecialchars($p["playerName"]) . '</option>';
}

// Build type dropdown
$typeArr = array("" => "All Types", "Session" => "Session", "Monthly" => "Monthly", "Quarterly" => "Quarterly", "Level-Test" => "Level Test");
$typeOpt = '';
$selType = $_GET["assessmentType"] ?? "";
foreach ($typeArr as $k => $v) {
    $sel = ($selType == $k) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "select", "name" => "coachID", "title" => "Coach", "where" => "AND a.coachID=?", "dtype" => "i", "value" => $coachOpt, "default" => false),
    array("type" => "select", "name" => "playerID", "title" => "Player", "where" => "AND a.playerID=?", "dtype" => "i", "value" => $playerOpt, "default" => false),
    array("type" => "select", "name" => "assessmentType", "title" => "Type", "where" => "AND a.assessmentType=?", "dtype" => "s", "value" => $typeOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT a.assessmentID FROM `" . $DB->pre . "ipa_coach_assessment` a WHERE a.status=?" . $MXFRM->where;
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
                array("Date", "assessmentDate", ' width="12%"', true),
                array("Player", "playerName", ' width="18%"'),
                array("Coach", "coachName", ' width="15%"'),
                array("Type", "assessmentType", ' width="12%" align="center"'),
                array("Score", "overallScore", ' width="10%" align="center"'),
                array("Level", "currentLevel", ' width="12%" align="center"'),
                array("Recommended", "recommendedLevel", ' width="12%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT a.*,
                        CONCAT(p.firstName, ' ', IFNULL(p.lastName,'')) as playerName,
                        CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName
                        FROM `" . $DB->pre . "ipa_coach_assessment` a
                        LEFT JOIN `" . $DB->pre . "ipa_player` p ON a.playerID = p.playerID
                        LEFT JOIN `" . $DB->pre . "ipa_coach` c ON a.coachID = c.coachID
                        WHERE a.status=?" . $MXFRM->where . mxOrderBy("a.assessmentDate DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["assessmentDate"] = date("d M Y", strtotime($d["assessmentDate"]));
                        $d["assessmentType"] = '<span class="badge badge-info">' . $d["assessmentType"] . '</span>';
                        $scoreColor = $d["overallScore"] >= 4 ? '#22c55e' : ($d["overallScore"] >= 3 ? '#3b82f6' : '#f59e0b');
                        $d["overallScore"] = '<span style="font-weight:700;color:' . $scoreColor . ';">' . number_format($d["overallScore"], 1) . '</span>/5';
                        $levelColors = array("Beginner" => "secondary", "Intermediate" => "info", "Advanced" => "warning", "Pro" => "success");
                        $d["currentLevel"] = '<span class="badge badge-' . ($levelColors[$d["currentLevel"]] ?? "secondary") . '">' . $d["currentLevel"] . '</span>';
                        if ($d["levelChangeRecommended"]) {
                            $d["recommendedLevel"] = '<span style="color:#8b5cf6;font-weight:600;"><i class="fa fa-arrow-up"></i> ' . $d["recommendedLevel"] . '</span>';
                        } else {
                            $d["recommendedLevel"] = '-';
                        }
                    ?>
                        <tr><?php echo getMAction("mid", $d["assessmentID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["assessmentID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-clipboard" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888;">No assessments found</p>
            </div>
        <?php } ?>
    </div>
</div>

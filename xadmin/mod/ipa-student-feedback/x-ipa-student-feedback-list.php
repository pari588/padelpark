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

$arrSearch = array(
    array("type" => "select", "name" => "coachID", "title" => "Coach", "where" => "AND f.coachID=?", "dtype" => "i", "value" => $coachOpt, "default" => false),
    array("type" => "select", "name" => "playerID", "title" => "Player", "where" => "AND f.playerID=?", "dtype" => "i", "value" => $playerOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT f.feedbackID FROM `" . $DB->pre . "ipa_student_feedback` f WHERE f.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div style="padding:10px 15px; border-bottom:1px solid #eee;">
            <a href="<?php echo ADMINURL; ?>/ipa-student-feedback-list/" class="btn btn-primary btn-sm"><i class="fa fa-list"></i> Feedback List</a>
            <a href="<?php echo ADMINURL; ?>/ipa-student-feedback-generate/" class="btn btn-default btn-sm"><i class="fa fa-link"></i> Generate Links</a>
        </div>
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("Date", "feedbackDate", ' width="12%"', true),
                array("Player", "playerName", ' width="18%"'),
                array("Coach", "coachName", ' width="15%"'),
                array("Overall", "overallRating", ' width="10%" align="center"'),
                array("Technical", "technicalSkillsRating", ' width="10%" align="center"'),
                array("Comms", "communicationRating", ' width="10%" align="center"'),
                array("Punctual", "punctualityRating", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT f.*,
                        CONCAT(p.firstName, ' ', IFNULL(p.lastName,'')) as playerName,
                        CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName
                        FROM `" . $DB->pre . "ipa_student_feedback` f
                        LEFT JOIN `" . $DB->pre . "ipa_player` p ON f.playerID = p.playerID
                        LEFT JOIN `" . $DB->pre . "ipa_coach` c ON f.coachID = c.coachID
                        WHERE f.status=?" . $MXFRM->where . mxOrderBy("f.feedbackDate DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["feedbackDate"] = date("d M Y", strtotime($d["feedbackDate"]));
                        $d["overallRating"] = '<span style="font-weight:700;color:#f59e0b;">' . number_format($d["overallRating"], 1) . '</span>';
                        $d["technicalSkillsRating"] = number_format($d["technicalSkillsRating"], 1);
                        $d["communicationRating"] = number_format($d["communicationRating"], 1);
                        $d["punctualityRating"] = number_format($d["punctualityRating"], 1);
                    ?>
                        <tr><?php echo getMAction("mid", $d["feedbackID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["feedbackID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-comments" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888;">No feedback found</p>
            </div>
        <?php } ?>
    </div>
</div>

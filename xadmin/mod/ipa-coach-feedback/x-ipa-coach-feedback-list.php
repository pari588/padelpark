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

// Progress status dropdown
$statusOpt = '<option value="">All Status</option>';
$selStatus = $_GET["progressStatus"] ?? "";
foreach (["Excellent", "Good", "Average", "Needs Work"] as $st) {
    $sel = ($selStatus == $st) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $st . '"' . $sel . '>' . $st . '</option>';
}

$arrSearch = array(
    array("type" => "select", "name" => "coachID", "title" => "Coach", "where" => "AND f.coachID=?", "dtype" => "i", "value" => $coachOpt, "default" => false),
    array("type" => "select", "name" => "playerID", "title" => "Player", "where" => "AND f.playerID=?", "dtype" => "i", "value" => $playerOpt, "default" => false),
    array("type" => "select", "name" => "progressStatus", "title" => "Progress", "where" => "AND f.progressStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT f.feedbackID FROM `" . $DB->pre . "ipa_coach_session_feedback` f WHERE f.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div style="padding:10px 15px; border-bottom:1px solid #eee;">
            <a href="<?php echo ADMINURL; ?>/ipa-coach-feedback-list/" class="btn btn-primary btn-sm"><i class="fa fa-list"></i> Feedback List</a>
            <a href="<?php echo ADMINURL; ?>/ipa-coach-feedback-session/" class="btn btn-default btn-sm"><i class="fa fa-users"></i> Session Feedback</a>
        </div>
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("Date", "feedbackDate", ' width="12%"', true),
                array("Player", "playerName", ' width="18%"'),
                array("Coach", "coachName", ' width="15%"'),
                array("Session", "sessionCode", ' width="12%"'),
                array("Overall", "overallRating", ' width="8%" align="center"'),
                array("Progress", "progressStatus", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT f.*,
                        CONCAT(p.firstName, ' ', IFNULL(p.lastName,'')) as playerName,
                        CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName,
                        s.sessionCode
                        FROM `" . $DB->pre . "ipa_coach_session_feedback` f
                        LEFT JOIN `" . $DB->pre . "ipa_player` p ON f.playerID = p.playerID
                        LEFT JOIN `" . $DB->pre . "ipa_coach` c ON f.coachID = c.coachID
                        LEFT JOIN `" . $DB->pre . "ipa_session` s ON f.sessionID = s.sessionID
                        WHERE f.status=?" . $MXFRM->where . mxOrderBy("f.feedbackDate DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["feedbackDate"] = date("d M Y", strtotime($d["feedbackDate"]));
                        $d["overallRating"] = '<span style="font-weight:700;color:#f59e0b;">' . number_format($d["overallRating"], 1) . '</span>';

                        // Progress status badge
                        $statusColors = [
                            "Excellent" => "#10b981",
                            "Good" => "#3b82f6",
                            "Average" => "#f59e0b",
                            "Needs Work" => "#ef4444"
                        ];
                        $statusColor = $statusColors[$d["progressStatus"]] ?? "#6b7280";
                        $d["progressStatus"] = '<span style="background:' . $statusColor . ';color:#fff;padding:3px 8px;border-radius:4px;font-size:11px;">' . $d["progressStatus"] . '</span>';
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
                <i class="fa fa-star" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0 0 15px; color:#888;">No coach feedback found</p>
                <a href="<?php echo ADMINURL; ?>/ipa-coach-feedback-session/" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Session Feedback
                </a>
            </div>
        <?php } ?>
    </div>
</div>

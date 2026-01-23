<?php
// Get coaches for dropdown
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName FROM " . $DB->pre . "ipa_coach WHERE status=1 ORDER BY firstName";
$coaches = $DB->dbRows();
$coachOpt = '<option value="">All Coaches</option>';
$selCoach = $_GET["coachID"] ?? "";
foreach ($coaches as $c) {
    $sel = ($selCoach == $c["coachID"]) ? ' selected="selected"' : '';
    $coachOpt .= '<option value="' . $c["coachID"] . '"' . $sel . '>' . htmlspecialchars($c["coachName"]) . '</option>';
}

// Build session status dropdown
$statusArr = array("" => "All Status", "Scheduled" => "Scheduled", "In-Progress" => "In Progress", "Completed" => "Completed", "Cancelled" => "Cancelled");
$statusOpt = '';
$selStatus = $_GET["sessionStatus"] ?? "";
foreach ($statusArr as $k => $v) {
    $sel = ($selStatus == $k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "sessionCode", "title" => "Code", "where" => "AND s.sessionCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "date", "name" => "sessionDate", "title" => "Date", "where" => "AND s.sessionDate=?", "dtype" => "s"),
    array("type" => "select", "name" => "coachID", "title" => "Coach", "where" => "AND s.coachID=?", "dtype" => "i", "value" => $coachOpt, "default" => false),
    array("type" => "select", "name" => "sessionStatus", "title" => "Status", "where" => "AND s.sessionStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT s.sessionID FROM `" . $DB->pre . "ipa_session` s WHERE s.status=?" . $MXFRM->where;
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
                array("Code", "sessionCode", ' width="12%" align="center"', true),
                array("Date", "sessionDate", ' width="10%" align="center"'),
                array("Time", "timeSlot", ' width="12%" align="center"'),
                array("Coach", "coachName", ' width="15%" align="left"'),
                array("Program", "programName", ' width="15%" align="left"'),
                array("Enrolled", "enrolledCount", ' width="8%" align="center"'),
                array("Attended", "attendedCount", ' width="8%" align="center"'),
                array("Status", "sessionStatus", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT s.*, CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName, p.programName
                        FROM `" . $DB->pre . "ipa_session` s
                        LEFT JOIN `" . $DB->pre . "ipa_coach` c ON s.coachID = c.coachID
                        LEFT JOIN `" . $DB->pre . "ipa_program` p ON s.programID = p.programID
                        WHERE s.status=?" . $MXFRM->where . mxOrderBy("s.sessionDate DESC, s.startTime DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format date
                        $d["sessionDate"] = date("d M Y", strtotime($d["sessionDate"]));

                        // Format time slot
                        $d["timeSlot"] = date("h:i A", strtotime($d["startTime"])) . ' - ' . date("h:i A", strtotime($d["endTime"]));

                        // Format status badge
                        $statusColors = array("Scheduled" => "info", "In-Progress" => "warning", "Completed" => "success", "Cancelled" => "danger", "No-Show" => "secondary");
                        $d["sessionStatus"] = '<span class="badge badge-' . ($statusColors[$d["sessionStatus"]] ?? "secondary") . '">' . $d["sessionStatus"] . '</span>';

                        // Format enrolled/attended
                        $d["enrolledCount"] = '<span class="badge badge-info">' . intval($d["enrolledCount"]) . '</span>';
                        $d["attendedCount"] = '<span class="badge badge-success">' . intval($d["attendedCount"]) . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["sessionID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["sessionID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-calendar" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No sessions found. Schedule your first coaching session.</p>
            </div>
        <?php } ?>
    </div>
</div>

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

// Build period dropdown
$periodArr = array("" => "All Periods", "Monthly" => "Monthly", "Quarterly" => "Quarterly", "Annual" => "Annual", "Probation" => "Probation");
$periodOpt = '';
$selPeriod = $_GET["reviewPeriod"] ?? "";
foreach ($periodArr as $k => $v) {
    $sel = ($selPeriod == $k) ? ' selected="selected"' : '';
    $periodOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build status dropdown
$statusArr = array("" => "All Status", "Draft" => "Draft", "Submitted" => "Submitted", "Acknowledged" => "Acknowledged", "Disputed" => "Disputed");
$statusOpt = '';
$selStatus = $_GET["reviewStatus"] ?? "";
foreach ($statusArr as $k => $v) {
    $sel = ($selStatus == $k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "select", "name" => "coachID", "title" => "Coach", "where" => "AND r.coachID=?", "dtype" => "i", "value" => $coachOpt, "default" => false),
    array("type" => "select", "name" => "reviewPeriod", "title" => "Period", "where" => "AND r.reviewPeriod=?", "dtype" => "s", "value" => $periodOpt, "default" => false),
    array("type" => "select", "name" => "reviewStatus", "title" => "Status", "where" => "AND r.reviewStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT r.reviewID FROM `" . $DB->pre . "ipa_coach_review` r WHERE r.status=?" . $MXFRM->where;
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
                array("Date", "reviewDate", ' width="12%"', true),
                array("Coach", "coachName", ' width="18%"'),
                array("Period", "reviewPeriod", ' width="12%" align="center"'),
                array("Score", "overallRating", ' width="10%" align="center"'),
                array("Performance", "performanceCategory", ' width="15%" align="center"'),
                array("Status", "reviewStatus", ' width="12%" align="center"'),
                array("Reviewer", "reviewerName", ' width="15%"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT r.*,
                        CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName, c.certificationLevel,
                        CONCAT(rev.firstName, ' ', IFNULL(rev.lastName,'')) as reviewerName
                        FROM `" . $DB->pre . "ipa_coach_review` r
                        LEFT JOIN `" . $DB->pre . "ipa_coach` c ON r.coachID = c.coachID
                        LEFT JOIN `" . $DB->pre . "ipa_coach` rev ON r.reviewerID = rev.coachID
                        WHERE r.status=?" . $MXFRM->where . mxOrderBy("r.reviewDate DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["reviewDate"] = date("d M Y", strtotime($d["reviewDate"]));
                        $d["reviewPeriod"] = '<span class="badge badge-info">' . $d["reviewPeriod"] . '</span>';

                        $perfColors = array('Excellent' => '#22c55e', 'Good' => '#3b82f6', 'Satisfactory' => '#f59e0b', 'Needs Improvement' => '#f97316', 'Unsatisfactory' => '#ef4444');
                        $perfColor = $perfColors[$d["performanceCategory"]] ?? '#6b7280';
                        $d["overallRating"] = '<span style="font-weight:700;color:' . $perfColor . ';">' . number_format($d["overallRating"], 1) . '</span>/5';
                        $d["performanceCategory"] = '<span style="display:inline-block;padding:3px 8px;border-radius:8px;font-size:11px;font-weight:600;background:' . $perfColor . '22;color:' . $perfColor . ';">' . $d["performanceCategory"] . '</span>';

                        $statusColors = array("Draft" => "secondary", "Submitted" => "info", "Acknowledged" => "success", "Disputed" => "danger");
                        $d["reviewStatus"] = '<span class="badge badge-' . ($statusColors[$d["reviewStatus"]] ?? "secondary") . '">' . $d["reviewStatus"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["reviewID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["reviewID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-clipboard" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888;">No reviews found</p>
            </div>
        <?php } ?>
    </div>
</div>

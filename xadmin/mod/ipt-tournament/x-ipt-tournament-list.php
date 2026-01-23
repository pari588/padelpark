<?php
// Build tournament type dropdown
$typeArr = array("" => "All Types", "Local" => "Local", "State" => "State", "National" => "National", "International" => "International", "Corporate" => "Corporate", "Club" => "Club");
$typeOpt = '';
$selType = $_GET["tournamentType"] ?? "";
foreach ($typeArr as $k => $v) {
    $sel = ($selType == $k) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build status dropdown
$statusArr = array("" => "All", "Draft" => "Draft", "Open" => "Open", "Registration-Closed" => "Registration Closed", "In-Progress" => "In Progress", "Completed" => "Completed", "Cancelled" => "Cancelled");
$statusOpt = '';
$selStatus = $_GET["tournamentStatus"] ?? "";
foreach ($statusArr as $k => $v) {
    $sel = ($selStatus == $k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "tournamentID", "title" => "#ID", "where" => "AND t.tournamentID=?", "dtype" => "i"),
    array("type" => "text", "name" => "tournamentCode", "title" => "Code", "where" => "AND t.tournamentCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "tournamentName", "title" => "Name", "where" => "AND t.tournamentName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "tournamentType", "title" => "Type", "where" => "AND t.tournamentType=?", "dtype" => "s", "value" => $typeOpt, "default" => false),
    array("type" => "select", "name" => "tournamentStatus", "title" => "Status", "where" => "AND t.tournamentStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false),
    array("type" => "date", "name" => "startDate", "title" => "Date", "where" => "AND t.startDate=?", "dtype" => "s")
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT t.tournamentID FROM `" . $DB->pre . "ipt_tournament` t WHERE t.status=?" . $MXFRM->where;
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
                array("#ID", "tournamentID", ' width="4%" align="center"', true),
                array("Code", "tournamentCode", ' width="10%" align="center"'),
                array("Tournament Name", "tournamentName", ' width="22%" align="left"'),
                array("Type", "tournamentType", ' width="8%" align="center"'),
                array("Dates", "dateRange", ' width="14%" align="center"'),
                array("Venue", "venueCity", ' width="10%" align="left"'),
                array("Participants", "participantCount", ' width="8%" align="center"'),
                array("Prize Pool", "totalPrizePurse", ' width="10%" align="right"'),
                array("Status", "tournamentStatus", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT t.*,
                        (SELECT COUNT(*) FROM " . $DB->pre . "ipt_participant p WHERE p.tournamentID=t.tournamentID AND p.status=1) as participantCount
                        FROM `" . $DB->pre . "ipt_tournament` t
                        WHERE t.status=?" . $MXFRM->where . mxOrderBy("t.startDate DESC, t.tournamentID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format dates
                        $d["dateRange"] = date("d M", strtotime($d["startDate"])) . " - " . date("d M Y", strtotime($d["endDate"]));

                        // Format prize purse
                        $d["totalPrizePurse"] = "Rs. " . number_format($d["totalPrizePurse"], 0);

                        // Participant count with max
                        $d["participantCount"] = $d["participantCount"] . "/" . $d["maxParticipants"];

                        // Type badge
                        $typeColors = array("Local" => "badge-secondary", "State" => "badge-info", "National" => "badge-primary", "International" => "badge-success", "Corporate" => "badge-warning", "Club" => "badge-light");
                        $d["tournamentType"] = '<span class="badge ' . ($typeColors[$d["tournamentType"]] ?? "badge-secondary") . '">' . $d["tournamentType"] . '</span>';

                        // Status badge
                        $statusColors = array(
                            "Draft" => "badge-secondary",
                            "Open" => "badge-success",
                            "Registration-Closed" => "badge-warning",
                            "In-Progress" => "badge-primary",
                            "Completed" => "badge-info",
                            "Cancelled" => "badge-danger"
                        );
                        $originalStatus = $d["tournamentStatus"];
                        $d["tournamentStatus"] = '<span class="badge ' . ($statusColors[$originalStatus] ?? "badge-secondary") . '">' . $originalStatus . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["tournamentID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["tournamentID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-trophy" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No tournaments found. Create your first tournament to get started.</p>
            </div>
        <?php } ?>
    </div>
</div>

<?php
// Get tournaments for filter dropdown
$DB->sql = "SELECT tournamentID, tournamentName, tournamentCode FROM " . $DB->pre . "ipt_tournament WHERE status=1 ORDER BY startDate DESC";
$tournaments = $DB->dbRows();
$tournamentOpt = '<option value="">All Tournaments</option>';
$selTournament = $_GET["tournamentID"] ?? "";
foreach ($tournaments as $t) {
    $sel = ($selTournament == $t["tournamentID"]) ? ' selected="selected"' : '';
    $tournamentOpt .= '<option value="' . $t["tournamentID"] . '"' . $sel . '>' . htmlspecialchars($t["tournamentCode"] . " - " . $t["tournamentName"]) . '</option>';
}

// Build status dropdown
$statusArr = array("" => "All", "Registered" => "Registered", "Confirmed" => "Confirmed", "Checked-In" => "Checked-In", "Active" => "Active", "Eliminated" => "Eliminated", "Withdrawn" => "Withdrawn");
$statusOpt = '';
$selStatus = $_GET["participantStatus"] ?? "";
foreach ($statusArr as $k => $v) {
    $sel = ($selStatus == $k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build payment status dropdown
$payArr = array("" => "All", "Pending" => "Pending", "Paid" => "Paid", "Refunded" => "Refunded", "Waived" => "Waived");
$payOpt = '';
$selPay = $_GET["paymentStatus"] ?? "";
foreach ($payArr as $k => $v) {
    $sel = ($selPay == $k) ? ' selected="selected"' : '';
    $payOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "participantID", "title" => "#ID", "where" => "AND p.participantID=?", "dtype" => "i"),
    array("type" => "select", "name" => "tournamentID", "title" => "Tournament", "where" => "AND p.tournamentID=?", "dtype" => "i", "value" => $tournamentOpt, "default" => false),
    array("type" => "text", "name" => "player1Name", "title" => "Player", "where" => "AND (p.player1Name LIKE CONCAT('%',?,'%') OR p.player2Name LIKE CONCAT('%',?,'%'))", "dtype" => "ss"),
    array("type" => "select", "name" => "participantStatus", "title" => "Status", "where" => "AND p.participantStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false),
    array("type" => "select", "name" => "paymentStatus", "title" => "Payment", "where" => "AND p.paymentStatus=?", "dtype" => "s", "value" => $payOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT p.participantID FROM `" . $DB->pre . "ipt_participant` p WHERE p.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>

    <?php
    // Show check-in stats if tournament is selected
    if ($selTournament) {
        $DB->vals = array($selTournament, 1);
        $DB->types = "ii";
        $DB->sql = "SELECT
            COUNT(*) as total,
            SUM(CASE WHEN participantStatus='Registered' THEN 1 ELSE 0 END) as registered,
            SUM(CASE WHEN participantStatus='Confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN participantStatus='Checked-In' THEN 1 ELSE 0 END) as checkedIn,
            SUM(CASE WHEN participantStatus='Active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN participantStatus='Eliminated' THEN 1 ELSE 0 END) as eliminated
            FROM " . $DB->pre . "ipt_participant WHERE tournamentID=? AND status=?";
        $stats = $DB->dbRow();
    ?>
    <div class="wrap-data" style="margin-bottom:15px; padding:15px; background:#f8f9fa; border-radius:8px;">
        <h4 style="margin:0 0 15px 0;"><i class="fa fa-chart-bar"></i> Check-In Status</h4>
        <div style="display:flex; gap:15px; flex-wrap:wrap;">
            <div style="text-align:center; padding:10px 20px; background:#fff; border-radius:6px; border-left:4px solid #6c757d;">
                <div style="font-size:24px; font-weight:bold;"><?php echo $stats["total"]; ?></div>
                <div style="font-size:12px; color:#666;">Total</div>
            </div>
            <div style="text-align:center; padding:10px 20px; background:#fff; border-radius:6px; border-left:4px solid #6c757d;">
                <div style="font-size:24px; font-weight:bold; color:#6c757d;"><?php echo $stats["registered"]; ?></div>
                <div style="font-size:12px; color:#666;">Registered</div>
            </div>
            <div style="text-align:center; padding:10px 20px; background:#fff; border-radius:6px; border-left:4px solid #17a2b8;">
                <div style="font-size:24px; font-weight:bold; color:#17a2b8;"><?php echo $stats["confirmed"]; ?></div>
                <div style="font-size:12px; color:#666;">Confirmed</div>
            </div>
            <div style="text-align:center; padding:10px 20px; background:#fff; border-radius:6px; border-left:4px solid #28a745;">
                <div style="font-size:24px; font-weight:bold; color:#28a745;"><?php echo $stats["checkedIn"]; ?></div>
                <div style="font-size:12px; color:#666;">Checked In</div>
            </div>
            <div style="text-align:center; padding:10px 20px; background:#fff; border-radius:6px; border-left:4px solid #007bff;">
                <div style="font-size:24px; font-weight:bold; color:#007bff;"><?php echo $stats["active"]; ?></div>
                <div style="font-size:12px; color:#666;">Active</div>
            </div>
            <div style="text-align:center; padding:10px 20px; background:#fff; border-radius:6px; border-left:4px solid #dc3545;">
                <div style="font-size:24px; font-weight:bold; color:#dc3545;"><?php echo $stats["eliminated"]; ?></div>
                <div style="font-size:12px; color:#666;">Eliminated</div>
            </div>
        </div>
    </div>
    <?php } ?>

    <div class="wrap-data">
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "participantID", ' width="4%" align="center"', true),
                array("Reg No", "registrationNo", ' width="10%" align="center"'),
                array("Tournament", "tournamentCode", ' width="12%" align="center"'),
                array("Category", "categoryName", ' width="12%" align="left"'),
                array("Team/Player", "teamName", ' width="18%" align="left"'),
                array("Seed", "seedNumber", ' width="5%" align="center"'),
                array("Payment", "paymentStatus", ' width="8%" align="center"'),
                array("Status", "participantStatus", ' width="10%" align="center"'),
                array("Actions", "actions", ' width="12%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT p.*, t.tournamentCode, t.tournamentName, c.categoryName
                        FROM `" . $DB->pre . "ipt_participant` p
                        LEFT JOIN `" . $DB->pre . "ipt_tournament` t ON p.tournamentID=t.tournamentID
                        LEFT JOIN `" . $DB->pre . "ipt_tournament_category` tc ON p.tcID=tc.tcID
                        LEFT JOIN `" . $DB->pre . "ipt_category` c ON tc.categoryID=c.categoryID
                        WHERE p.status=?" . $MXFRM->where . mxOrderBy("p.registrationDate DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Seed display
                        $d["seedNumber"] = $d["seedNumber"] > 0 ? "[" . $d["seedNumber"] . "]" : "-";

                        // Payment status badge
                        $payColors = array("Pending" => "badge-warning", "Paid" => "badge-success", "Refunded" => "badge-info", "Waived" => "badge-secondary");
                        $d["paymentStatus"] = '<span class="badge ' . ($payColors[$d["paymentStatus"]] ?? "badge-secondary") . '">' . $d["paymentStatus"] . '</span>';

                        // Status badge
                        $statusColors = array(
                            "Registered" => "badge-secondary",
                            "Confirmed" => "badge-info",
                            "Checked-In" => "badge-primary",
                            "Active" => "badge-success",
                            "Eliminated" => "badge-danger",
                            "Withdrawn" => "badge-dark"
                        );
                        $originalStatus = $d["participantStatus"];
                        $d["participantStatus"] = '<span class="badge ' . ($statusColors[$originalStatus] ?? "badge-secondary") . '">' . $originalStatus . '</span>';

                        // Action buttons
                        $d["actions"] = '';
                        if ($originalStatus == "Registered") {
                            $d["actions"] .= '<a href="javascript:void(0);" onclick="confirmParticipant(' . $d["participantID"] . ')" class="btn btn-sm btn-info" title="Confirm Registration"><i class="fa fa-check"></i></a> ';
                        }
                        if ($originalStatus == "Confirmed") {
                            $d["actions"] .= '<a href="javascript:void(0);" onclick="checkInParticipant(' . $d["participantID"] . ')" class="btn btn-sm btn-success" title="Check In at Venue"><i class="fa fa-sign-in-alt"></i> Check In</a> ';
                        }
                        if ($originalStatus == "Checked-In") {
                            $d["actions"] .= '<span class="badge badge-success"><i class="fa fa-check-circle"></i> Ready</span>';
                        }
                    ?>
                        <tr><?php echo getMAction("mid", $d["participantID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["participantID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-users" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No participants found. Register participants for your tournaments.</p>
            </div>
        <?php } ?>
    </div>
</div>

<script>
function confirmParticipant(participantID) {
    if (confirm('Confirm this participant registration?')) {
        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/ipt-participant/x-ipt-participant.inc.php',
            type: 'POST',
            data: {
                xAction: 'CONFIRM',
                participantID: participantID,
                xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"]; ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.err == 0) {
                    location.reload();
                } else {
                    alert('Error: ' + (res.msg || 'Unknown error'));
                }
            }
        });
    }
}

function checkInParticipant(participantID) {
    if (confirm('Check in this participant?')) {
        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/ipt-participant/x-ipt-participant.inc.php',
            type: 'POST',
            data: {
                xAction: 'CHECK_IN',
                participantID: participantID,
                xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"]; ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.err == 0) {
                    location.reload();
                } else {
                    alert('Error: ' + (res.msg || 'Unknown error'));
                }
            }
        });
    }
}
</script>

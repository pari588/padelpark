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

// Build position dropdown
$posArr = array("" => "All Positions", "Winner" => "Winner", "Runner-Up" => "Runner-Up", "Semi-Finalist" => "Semi-Finalist", "Quarter-Finalist" => "Quarter-Finalist");
$posOpt = '';
$selPos = $_GET["position"] ?? "";
foreach ($posArr as $k => $v) {
    $sel = ($selPos == $k) ? ' selected="selected"' : '';
    $posOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build disbursement status dropdown
$payArr = array("" => "All", "Pending" => "Pending", "Processed" => "Processed", "Paid" => "Paid");
$payOpt = '';
$selPay = $_GET["disbursementStatus"] ?? "";
foreach ($payArr as $k => $v) {
    $sel = ($selPay == $k) ? ' selected="selected"' : '';
    $payOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "prizeID", "title" => "#ID", "where" => "AND p.prizeID=?", "dtype" => "i"),
    array("type" => "select", "name" => "tournamentID", "title" => "Tournament", "where" => "AND p.tournamentID=?", "dtype" => "i", "value" => $tournamentOpt, "default" => false),
    array("type" => "text", "name" => "winnerName", "title" => "Winner", "where" => "AND p.winnerName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "position", "title" => "Position", "where" => "AND p.position=?", "dtype" => "s", "value" => $posOpt, "default" => false),
    array("type" => "select", "name" => "disbursementStatus", "title" => "Status", "where" => "AND p.disbursementStatus=?", "dtype" => "s", "value" => $payOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT p.prizeID FROM `" . $DB->pre . "ipt_prize` p WHERE p.status=?" . $MXFRM->where;
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
                array("#ID", "prizeID", ' width="4%" align="center"', true),
                array("Tournament", "tournamentCode", ' width="10%" align="center"'),
                array("Category", "categoryName", ' width="12%" align="left"'),
                array("Position", "position", ' width="10%" align="center"'),
                array("Winner", "winnerName", ' width="16%" align="left"'),
                array("Prize Amount", "prizeAmount", ' width="10%" align="right"'),
                array("TDS", "tdsDeducted", ' width="8%" align="right"'),
                array("Net Amount", "netAmount", ' width="10%" align="right"'),
                array("Status", "disbursementStatus", ' width="10%" align="center"'),
                array("Action", "actions", ' width="8%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT p.*, t.tournamentCode, t.tournamentName, c.categoryName
                        FROM `" . $DB->pre . "ipt_prize` p
                        LEFT JOIN `" . $DB->pre . "ipt_tournament` t ON p.tournamentID=t.tournamentID
                        LEFT JOIN `" . $DB->pre . "ipt_tournament_category` tc ON p.tcID=tc.tcID
                        LEFT JOIN `" . $DB->pre . "ipt_category` c ON tc.categoryID=c.categoryID
                        WHERE p.status=?" . $MXFRM->where . mxOrderBy("p.prizeID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format amounts
                        $d["prizeAmount"] = "Rs. " . number_format($d["prizeAmount"], 0);
                        $d["tdsDeducted"] = $d["tdsDeducted"] > 0 ? "Rs. " . number_format($d["tdsDeducted"], 0) : "-";
                        $d["netAmount"] = "Rs. " . number_format($d["netAmount"], 0);

                        // Position badge
                        $posColors = array(
                            "Winner" => "badge-success",
                            "Runner-Up" => "badge-primary",
                            "Semi-Finalist" => "badge-info",
                            "Quarter-Finalist" => "badge-secondary"
                        );
                        $origPosition = $d["position"];
                        $d["position"] = '<span class="badge ' . ($posColors[$origPosition] ?? "badge-secondary") . '">' . $origPosition . '</span>';

                        // Disbursement status badge
                        $payColors = array(
                            "Pending" => "badge-warning",
                            "Processed" => "badge-info",
                            "Paid" => "badge-success"
                        );
                        $originalPayStatus = $d["disbursementStatus"];
                        $d["disbursementStatus"] = '<span class="badge ' . ($payColors[$originalPayStatus] ?? "badge-secondary") . '">' . $originalPayStatus . '</span>';

                        // Action button
                        $d["actions"] = '';
                        if ($originalPayStatus == "Pending") {
                            $d["actions"] = '<a href="javascript:void(0);" onclick="markPaid(' . $d["prizeID"] . ')" class="btn btn-sm btn-success" title="Mark Paid"><i class="fa fa-check"></i></a>';
                        }
                    ?>
                        <tr><?php echo getMAction("mid", $d["prizeID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["prizeID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-medal" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No prize distributions found. Add prizes for tournament winners.</p>
            </div>
        <?php } ?>
    </div>
</div>

<script>
function markPaid(prizeID) {
    if (confirm('Mark this prize as paid?')) {
        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/ipt-prize/x-ipt-prize.inc.php',
            type: 'POST',
            data: {
                xAction: 'MARK_PAID',
                prizeID: prizeID,
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

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

// Build type dropdown
$typeArr = array("" => "All Types", "Title" => "Title", "Presenting" => "Presenting", "Gold" => "Gold", "Silver" => "Silver", "Bronze" => "Bronze", "Associate" => "Associate");
$typeOpt = '';
$selType = $_GET["sponsorType"] ?? "";
foreach ($typeArr as $k => $v) {
    $sel = ($selType == $k) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build payment status dropdown
$payArr = array("" => "All", "Pending" => "Pending", "Partial" => "Partial", "Paid" => "Paid");
$payOpt = '';
$selPay = $_GET["paymentStatus"] ?? "";
foreach ($payArr as $k => $v) {
    $sel = ($selPay == $k) ? ' selected="selected"' : '';
    $payOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "sponsorID", "title" => "#ID", "where" => "AND s.sponsorID=?", "dtype" => "i"),
    array("type" => "select", "name" => "tournamentID", "title" => "Tournament", "where" => "AND s.tournamentID=?", "dtype" => "i", "value" => $tournamentOpt, "default" => false),
    array("type" => "text", "name" => "sponsorName", "title" => "Sponsor", "where" => "AND s.sponsorName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "sponsorType", "title" => "Type", "where" => "AND s.sponsorType=?", "dtype" => "s", "value" => $typeOpt, "default" => false),
    array("type" => "select", "name" => "paymentStatus", "title" => "Payment", "where" => "AND s.paymentStatus=?", "dtype" => "s", "value" => $payOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT s.sponsorID FROM `" . $DB->pre . "ipt_sponsor` s WHERE s.status=?" . $MXFRM->where;
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
                array("#ID", "sponsorID", ' width="4%" align="center"', true),
                array("Tournament", "tournamentCode", ' width="12%" align="center"'),
                array("Sponsor Name", "sponsorName", ' width="20%" align="left"'),
                array("Type", "sponsorType", ' width="10%" align="center"'),
                array("Contract Value", "contractValue", ' width="12%" align="right"'),
                array("Received", "paymentReceived", ' width="12%" align="right"'),
                array("Contact", "contactPerson", ' width="15%" align="left"'),
                array("Payment", "paymentStatus", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT s.*, t.tournamentCode, t.tournamentName
                        FROM `" . $DB->pre . "ipt_sponsor` s
                        LEFT JOIN `" . $DB->pre . "ipt_tournament` t ON s.tournamentID=t.tournamentID
                        WHERE s.status=?" . $MXFRM->where . mxOrderBy("s.sponsorID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format amounts
                        $d["contractValue"] = "Rs. " . number_format($d["contractValue"], 0);
                        $d["paymentReceived"] = "Rs. " . number_format($d["paymentReceived"], 0);

                        // Type badge with colors
                        $typeColors = array(
                            "Title" => "badge-danger",
                            "Presenting" => "badge-dark",
                            "Gold" => "badge-warning",
                            "Silver" => "badge-secondary",
                            "Bronze" => "badge-info",
                            "Associate" => "badge-light"
                        );
                        $d["sponsorType"] = '<span class="badge ' . ($typeColors[$d["sponsorType"]] ?? "badge-secondary") . '">' . $d["sponsorType"] . '</span>';

                        // Payment status badge
                        $payColors = array(
                            "Paid" => "badge-success",
                            "Partial" => "badge-warning",
                            "Pending" => "badge-danger"
                        );
                        $d["paymentStatus"] = '<span class="badge ' . ($payColors[$d["paymentStatus"]] ?? "badge-secondary") . '">' . $d["paymentStatus"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["sponsorID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["sponsorID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-handshake" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No sponsors found. Add sponsors to your tournaments.</p>
            </div>
        <?php } ?>
    </div>
</div>

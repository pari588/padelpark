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

// Status dropdown
$statusOpt = '<option value="">All Status</option>';
$selStatus = $_GET["commissionStatus"] ?? "";
foreach (["Pending", "Approved", "Paid", "Cancelled"] as $st) {
    $sel = ($selStatus == $st) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $st . '"' . $sel . '>' . $st . '</option>';
}

$arrSearch = array(
    array("type" => "select", "name" => "coachID", "title" => "Coach", "where" => "AND c.coachID=?", "dtype" => "i", "value" => $coachOpt, "default" => false),
    array("type" => "select", "name" => "commissionStatus", "title" => "Status", "where" => "AND c.commissionStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT c.commissionID FROM `" . $DB->pre . "ipa_coach_commission` c WHERE c.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div style="padding:10px 15px; border-bottom:1px solid #eee;">
            <a href="<?php echo ADMINURL; ?>/ipa-commission-list/" class="btn btn-primary btn-sm"><i class="fa fa-list"></i> Commission List</a>
            <a href="<?php echo ADMINURL; ?>/ipa-commission-add/" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add Commission</a>
            <a href="<?php echo ADMINURL; ?>/ipa-commission-report/" class="btn btn-default btn-sm"><i class="fa fa-chart-bar"></i> Summary Report</a>
        </div>
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("Date", "saleDate", ' width="12%"', true),
                array("Coach", "coachName", ' width="20%"'),
                array("Sale Amount", "saleAmount", ' width="15%" align="right"'),
                array("Rate", "commissionRate", ' width="8%" align="center"'),
                array("Commission", "commissionAmount", ' width="15%" align="right"'),
                array("Status", "commissionStatus", ' width="12%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT c.*,
                        CONCAT(co.firstName, ' ', IFNULL(co.lastName,'')) as coachName
                        FROM `" . $DB->pre . "ipa_coach_commission` c
                        LEFT JOIN `" . $DB->pre . "ipa_coach` co ON c.coachID = co.coachID
                        WHERE c.status=?" . $MXFRM->where . mxOrderBy("c.saleDate DESC, c.commissionID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["saleDate"] = date("d M Y", strtotime($d["saleDate"]));
                        $d["saleAmount"] = '<span style="font-family:monospace;">&#8377; ' . number_format($d["saleAmount"], 2) . '</span>';
                        $d["commissionRate"] = $d["commissionRate"] . '%';
                        $d["commissionAmount"] = '<strong style="color:#10b981;font-family:monospace;">&#8377; ' . number_format($d["commissionAmount"], 2) . '</strong>';

                        // Status badge
                        $statusColors = [
                            "Pending" => "#f59e0b",
                            "Approved" => "#3b82f6",
                            "Paid" => "#10b981",
                            "Cancelled" => "#ef4444"
                        ];
                        $statusColor = $statusColors[$d["commissionStatus"]] ?? "#6b7280";
                        $d["commissionStatus"] = '<span style="background:' . $statusColor . ';color:#fff;padding:3px 10px;border-radius:4px;font-size:11px;">' . $d["commissionStatus"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["commissionID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["commissionID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-percentage" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0 0 15px; color:#888;">No commission records found</p>
                <a href="<?php echo ADMINURL; ?>/ipa-commission-add/" class="btn btn-success">
                    <i class="fa fa-plus"></i> Add Commission
                </a>
            </div>
        <?php } ?>
    </div>
</div>

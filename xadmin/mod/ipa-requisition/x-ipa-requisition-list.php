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
$selStatus = $_GET["requisitionStatus"] ?? "";
foreach (["Draft", "Submitted", "Approved", "Rejected", "Fulfilled", "Partial"] as $st) {
    $sel = ($selStatus == $st) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $st . '"' . $sel . '>' . $st . '</option>';
}

$arrSearch = array(
    array("type" => "select", "name" => "coachID", "title" => "Coach", "where" => "AND r.coachID=?", "dtype" => "i", "value" => $coachOpt, "default" => false),
    array("type" => "select", "name" => "requisitionStatus", "title" => "Status", "where" => "AND r.requisitionStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT r.requisitionID FROM `" . $DB->pre . "ipa_requisition` r WHERE r.status=?" . $MXFRM->where;
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
                array("Req No", "requisitionNo", ' width="15%"', true),
                array("Date", "requisitionDate", ' width="10%"'),
                array("Coach", "coachName", ' width="15%"'),
                array("Items", "totalItems", ' width="8%" align="center"'),
                array("Required By", "requiredByDate", ' width="10%"'),
                array("Status", "requisitionStatus", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT r.*,
                        CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName
                        FROM `" . $DB->pre . "ipa_requisition` r
                        LEFT JOIN `" . $DB->pre . "ipa_coach` c ON r.coachID = c.coachID
                        WHERE r.status=?" . $MXFRM->where . mxOrderBy("r.requisitionDate DESC, r.requisitionID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["requisitionDate"] = date("d M Y", strtotime($d["requisitionDate"]));
                        $d["requiredByDate"] = $d["requiredByDate"] ? date("d M Y", strtotime($d["requiredByDate"])) : "-";

                        // Status badge
                        $statusColors = [
                            "Draft" => "#6b7280",
                            "Submitted" => "#3b82f6",
                            "Approved" => "#10b981",
                            "Rejected" => "#ef4444",
                            "Fulfilled" => "#8b5cf6",
                            "Partial" => "#f59e0b"
                        ];
                        $statusColor = $statusColors[$d["requisitionStatus"]] ?? "#6b7280";
                        $d["requisitionStatus"] = '<span style="background:' . $statusColor . ';color:#fff;padding:3px 8px;border-radius:4px;font-size:11px;">' . $d["requisitionStatus"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["requisitionID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["requisitionID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div style="text-align:center; padding:60px 20px;">
                <i class="fa fa-clipboard-list" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888;">No requisitions found</p>
            </div>
        <?php } ?>
    </div>
</div>

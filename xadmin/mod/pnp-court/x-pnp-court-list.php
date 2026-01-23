<?php
// Get locations for filter dropdown
$DB->sql = "SELECT locationID, locationName FROM " . $DB->pre . "pnp_location WHERE status=1 ORDER BY locationName";
$locations = $DB->dbRows();
$locationOpt = '<option value="">All Locations</option>';
$selLoc = $_GET["locationID"] ?? "";
foreach ($locations as $loc) {
    $sel = ($selLoc == $loc["locationID"]) ? ' selected="selected"' : '';
    $locationOpt .= '<option value="' . $loc["locationID"] . '"' . $sel . '>' . htmlspecialchars($loc["locationName"]) . '</option>';
}

// Build court type dropdown
$typeArr = array("" => "All Types", "Indoor" => "Indoor", "Outdoor" => "Outdoor", "Covered" => "Covered");
$typeOpt = '';
$selType = $_GET["courtType"] ?? "";
foreach ($typeArr as $k => $v) {
    $sel = ($selType == $k) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build maintenance status dropdown
$maintArr = array("" => "All", "Active" => "Active", "Under Maintenance" => "Under Maintenance", "Closed" => "Closed");
$maintOpt = '';
$selMaint = $_GET["maintenanceStatus"] ?? "";
foreach ($maintArr as $k => $v) {
    $sel = ($selMaint == $k) ? ' selected="selected"' : '';
    $maintOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "courtID", "title" => "#ID", "where" => "AND c.courtID=?", "dtype" => "i"),
    array("type" => "select", "name" => "locationID", "title" => "Location", "where" => "AND c.locationID=?", "dtype" => "i", "value" => $locationOpt, "default" => false),
    array("type" => "text", "name" => "courtName", "title" => "Court Name", "where" => "AND c.courtName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "courtType", "title" => "Court Type", "where" => "AND c.courtType=?", "dtype" => "s", "value" => $typeOpt, "default" => false),
    array("type" => "select", "name" => "maintenanceStatus", "title" => "Status", "where" => "AND c.maintenanceStatus=?", "dtype" => "s", "value" => $maintOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT c.courtID FROM `" . $DB->pre . "pnp_court` c WHERE c.status=?" . $MXFRM->where;
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
                array("#ID", "courtID", ' width="5%" align="center"', true),
                array("Code", "courtCode", ' width="7%" align="center"'),
                array("Court Name", "courtName", ' width="15%" align="left"'),
                array("Location", "locationName", ' width="18%" align="left"'),
                array("Type", "courtType", ' width="10%" align="center"'),
                array("Hourly Rate", "hourlyRate", ' width="10%" align="right"'),
                array("Peak Rate", "peakHourlyRate", ' width="10%" align="right"'),
                array("Status", "maintenanceStatus", ' width="12%" align="center"'),
                array("Today", "todayBookings", ' width="8%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT c.*, l.locationName,
                        (SELECT COUNT(*) FROM " . $DB->pre . "pnp_booking b WHERE b.courtID=c.courtID AND b.bookingDate=CURDATE() AND b.status=1) as todayBookings
                        FROM `" . $DB->pre . "pnp_court` c
                        LEFT JOIN `" . $DB->pre . "pnp_location` l ON c.locationID=l.locationID
                        WHERE c.status=? " . $MXFRM->where . mxOrderBy("l.locationName, c.sortOrder, c.courtName") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format rates
                        $d["hourlyRate"] = "Rs. " . number_format($d["hourlyRate"], 0);
                        $d["peakHourlyRate"] = "Rs. " . number_format($d["peakHourlyRate"], 0);

                        // Court type badge
                        $typeColors = array("Indoor" => "badge-primary", "Outdoor" => "badge-success", "Covered" => "badge-info");
                        $d["courtType"] = '<span class="badge ' . ($typeColors[$d["courtType"]] ?? "badge-secondary") . '">' . $d["courtType"] . '</span>';

                        // Status badge
                        $statusColors = array("Active" => "badge-success", "Under Maintenance" => "badge-warning", "Closed" => "badge-danger");
                        $d["maintenanceStatus"] = '<span class="badge ' . ($statusColors[$d["maintenanceStatus"]] ?? "badge-secondary") . '">' . $d["maintenanceStatus"] . '</span>';

                        // Today's bookings
                        $d["todayBookings"] = '<span class="badge badge-info">' . $d["todayBookings"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["courtID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["courtID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No courts found. Add locations first, then add courts.</div>
        <?php } ?>
    </div>
</div>

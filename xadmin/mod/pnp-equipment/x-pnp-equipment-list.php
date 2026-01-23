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

// Build equipment type dropdown
$typeArr = array("" => "All Types", "Racket" => "Racket", "Balls" => "Balls", "Shoes" => "Shoes", "Accessories" => "Accessories", "Other" => "Other");
$typeOpt = '';
$selType = $_GET["equipmentType"] ?? "";
foreach ($typeArr as $k => $v) {
    $sel = ($selType == $k) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "equipmentID", "title" => "#ID", "where" => "AND e.equipmentID=?", "dtype" => "i"),
    array("type" => "select", "name" => "locationID", "title" => "Location", "where" => "AND e.locationID=?", "dtype" => "i", "value" => $locationOpt, "default" => false),
    array("type" => "text", "name" => "equipmentName", "title" => "Name", "where" => "AND e.equipmentName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "equipmentType", "title" => "Type", "where" => "AND e.equipmentType=?", "dtype" => "s", "value" => $typeOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT e.equipmentID FROM `" . $DB->pre . "pnp_equipment` e WHERE e.status=?" . $MXFRM->where;
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
                array("#ID", "equipmentID", ' width="5%" align="center"', true),
                array("Code", "equipmentCode", ' width="8%" align="center"'),
                array("Equipment", "equipmentName", ' width="18%" align="left"'),
                array("Location", "locationName", ' width="14%" align="left"'),
                array("Type", "equipmentType", ' width="10%" align="center"'),
                array("Brand", "brand", ' width="10%" align="left"'),
                array("Rental Rate", "rentalRate", ' width="10%" align="right"'),
                array("Available", "availability", ' width="12%" align="center"'),
                array("Condition", "condition", ' width="10%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT e.*, l.locationName
                        FROM `" . $DB->pre . "pnp_equipment` e
                        LEFT JOIN `" . $DB->pre . "pnp_location` l ON e.locationID=l.locationID
                        WHERE e.status=? " . $MXFRM->where . mxOrderBy("l.locationName, e.equipmentName") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format rental rate
                        $d["rentalRate"] = "Rs. " . number_format($d["rentalRate"], 0);

                        // Type badge
                        $typeColors = array("Racket" => "badge-primary", "Balls" => "badge-success", "Shoes" => "badge-info", "Accessories" => "badge-warning");
                        $d["equipmentType"] = '<span class="badge ' . ($typeColors[$d["equipmentType"]] ?? "badge-secondary") . '">' . $d["equipmentType"] . '</span>';

                        // Availability
                        $availPercent = $d["totalQuantity"] > 0 ? round(($d["availableQuantity"] / $d["totalQuantity"]) * 100) : 0;
                        $availClass = $availPercent > 50 ? 'badge-success' : ($availPercent > 0 ? 'badge-warning' : 'badge-danger');
                        $d["availability"] = '<span class="badge ' . $availClass . '">' . $d["availableQuantity"] . ' / ' . $d["totalQuantity"] . '</span>';

                        // Condition badge
                        $condColors = array("Excellent" => "badge-success", "Good" => "badge-info", "Fair" => "badge-warning", "Poor" => "badge-danger");
                        $d["condition"] = '<span class="badge ' . ($condColors[$d["condition"]] ?? "badge-secondary") . '">' . $d["condition"] . '</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["equipmentID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["equipmentID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No equipment found. Add rental equipment to get started.</div>
        <?php } ?>
    </div>
</div>

<?php
// Build location type dropdown
$typeArr = array("" => "All", "Owned" => "Owned", "Franchise" => "Franchise", "Partner" => "Partner");
$typeOpt = '';
$selType = $_GET["locationType"] ?? "";
foreach ($typeArr as $k => $v) {
    $sel = ($selType == $k) ? ' selected="selected"' : '';
    $typeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "locationID", "title" => "#ID", "where" => "AND l.locationID=?", "dtype" => "i"),
    array("type" => "text", "name" => "locationName", "title" => "Location Name", "where" => "AND l.locationName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "city", "title" => "City", "where" => "AND l.city LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "locationType", "title" => "Type", "where" => "AND l.locationType=?", "dtype" => "s", "value" => $typeOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT l.locationID FROM `" . $DB->pre . "pnp_location` l WHERE l.status=?" . $MXFRM->where;
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
                array("#ID", "locationID", ' width="4%" align="center"', true),
                array("Code", "locationCode", ' width="7%" align="center"'),
                array("Location Name", "locationName", ' width="16%" align="left"'),
                array("Type", "locationType", ' width="8%" align="center"'),
                array("City", "city", ' width="10%" align="left"'),
                array("Contact", "contactPerson", ' width="12%" align="left"'),
                array("Warehouse", "warehouseName", ' width="15%" align="left"'),
                array("Courts", "courtCount", ' width="8%" align="center"'),
                array("Status", "locationStatus", ' width="8%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT l.*, w.warehouseCode, w.warehouseName,
                        (SELECT COUNT(*) FROM " . $DB->pre . "pnp_court c WHERE c.locationID=l.locationID AND c.status=1) as courtCount
                        FROM `" . $DB->pre . "pnp_location` l
                        LEFT JOIN `" . $DB->pre . "warehouse` w ON l.warehouseID = w.warehouseID
                        WHERE l.status=? " . $MXFRM->where . mxOrderBy("l.locationID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Type badge
                        $typeColors = array("Owned" => "badge-success", "Franchise" => "badge-primary", "Partner" => "badge-info");
                        $typeClass = $typeColors[$d["locationType"]] ?? "badge-secondary";
                        $d["locationType"] = '<span class="badge ' . $typeClass . '">' . $d["locationType"] . '</span>';

                        // Warehouse info
                        if (!empty($d["warehouseName"])) {
                            $d["warehouseName"] = '<a href="' . ADMINURL . '/warehouse-edit/id=' . $d["warehouseID"] . '/" style="color:#1a5f7a;">' . htmlspecialchars($d["warehouseCode"]) . '</a>';
                        } else {
                            $d["warehouseName"] = '<span style="color:#999;">-</span>';
                        }

                        // Court count badge
                        $d["courtCount"] = '<span class="badge badge-info">' . $d["courtCount"] . ' Courts</span>';

                        // Status indicator
                        $d["locationStatus"] = '<span class="badge badge-success">Active</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["locationID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["locationID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No locations found. Add your first location to get started.</div>
        <?php } ?>
    </div>
</div>

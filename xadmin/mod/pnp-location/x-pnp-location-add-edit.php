<?php
$id = 0;
$D = array();
$warehouse = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT l.*, w.warehouseID, w.warehouseCode, w.warehouseName
                FROM `" . $DB->pre . $MXMOD["TBL"] . "` l
                LEFT JOIN `" . $DB->pre . "warehouse` w ON l.warehouseID = w.warehouseID
                WHERE l.status=? AND l.`" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
    if (!empty($D["warehouseID"])) {
        $warehouse = array(
            "warehouseID" => $D["warehouseID"],
            "warehouseCode" => $D["warehouseCode"],
            "warehouseName" => $D["warehouseName"]
        );
    }
}

// Build location type options
$locTypes = array("Owned", "Franchise", "Partner");
$locTypeOpt = "";
$currentLocType = $D["locationType"] ?? "Owned";
foreach ($locTypes as $lt) {
    $sel = ($currentLocType == $lt) ? ' selected="selected"' : '';
    $locTypeOpt .= '<option value="' . $lt . '"' . $sel . '>' . $lt . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "locationCode", "value" => $D["locationCode"] ?? "", "title" => "Location Code", "info" => '<span class="info">Leave blank for auto-generation</span>'),
    array("type" => "text", "name" => "locationName", "value" => $D["locationName"] ?? "", "title" => "Location Name", "validate" => "required"),
    array("type" => "select", "name" => "locationType", "value" => $locTypeOpt, "title" => "Location Type"),
    array("type" => "textarea", "name" => "address", "value" => $D["address"] ?? "", "title" => "Address", "params" => array("rows" => 3)),
    array("type" => "text", "name" => "city", "value" => $D["city"] ?? "", "title" => "City", "validate" => "required"),
    array("type" => "text", "name" => "state", "value" => $D["state"] ?? "", "title" => "State"),
    array("type" => "text", "name" => "pincode", "value" => $D["pincode"] ?? "", "title" => "Pincode"),
);

$arrForm1 = array(
    array("type" => "text", "name" => "contactPerson", "value" => $D["contactPerson"] ?? "", "title" => "Contact Person"),
    array("type" => "text", "name" => "contactPhone", "value" => $D["contactPhone"] ?? "", "title" => "Contact Phone"),
    array("type" => "text", "name" => "contactEmail", "value" => $D["contactEmail"] ?? "", "title" => "Contact Email"),
    array("type" => "text", "name" => "operatingHoursStart", "value" => $D["operatingHoursStart"] ?? "06:00:00", "title" => "Opening Time", "info" => '<span class="info">Format: HH:MM:SS</span>'),
    array("type" => "text", "name" => "operatingHoursEnd", "value" => $D["operatingHoursEnd"] ?? "23:00:00", "title" => "Closing Time"),
    array("type" => "text", "name" => "gstNo", "value" => $D["gstNo"] ?? "", "title" => "GST Number"),
    array("type" => "text", "name" => "hudelLocationID", "value" => $D["hudelLocationID"] ?? "", "title" => "Hudle Location ID", "info" => '<span class="info">For API integration</span>'),
);

// Warehouse info (display only for edit mode)
$warehouseInfo = "";
if (!empty($warehouse["warehouseID"])) {
    $warehouseInfo = '<a href="' . ADMINURL . '/warehouse-edit/id=' . $warehouse["warehouseID"] . '/" style="color:#1a5f7a;font-weight:bold;">'
                   . htmlspecialchars($warehouse["warehouseCode"]) . ' - ' . htmlspecialchars($warehouse["warehouseName"])
                   . '</a>';
} elseif ($TPL->pageType == "add") {
    $warehouseInfo = '<span style="color:#666;">Warehouse will be auto-created on save</span>';
} else {
    $warehouseInfo = '<span style="color:#999;">Not linked</span>';
}

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Location Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Contact & Operations</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
                <li>
                    <div class="frm-lbl">Linked Warehouse</div>
                    <div class="frm-fld"><?php echo $warehouseInfo; ?></div>
                </li>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<?php if ($D["locationID"] ?? 0) { ?>
<div class="wrap-right" style="margin-top: 20px;">
    <div class="wrap-data">
        <h2 class="form-head">Courts at this Location</h2>
        <?php
        $DB->vals = array($D["locationID"]);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "pnp_court WHERE locationID=? AND status=1 ORDER BY sortOrder, courtName";
        $courts = $DB->dbRows();

        if (count($courts) > 0) { ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="10" class="tbl-list">
            <thead>
                <tr>
                    <th>Court Name</th>
                    <th>Type</th>
                    <th>Hourly Rate</th>
                    <th>Peak Rate</th>
                    <th>Status</th>
                    <th width="100">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courts as $c) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($c["courtName"]); ?></td>
                    <td><?php echo $c["courtType"]; ?></td>
                    <td>Rs. <?php echo number_format($c["hourlyRate"], 0); ?></td>
                    <td>Rs. <?php echo number_format($c["peakHourlyRate"], 0); ?></td>
                    <td>
                        <span class="badge <?php echo $c["maintenanceStatus"] == 'Active' ? 'badge-success' : 'badge-warning'; ?>">
                            <?php echo $c["maintenanceStatus"]; ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo ADMINURL; ?>/pnp-court-edit/?id=<?php echo $c["courtID"]; ?>" class="btn btn-sm btn-primary">Edit</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } else { ?>
        <p style="padding: 20px; color: #666;">No courts added yet.</p>
        <?php } ?>
        <div style="padding: 15px;">
            <a href="<?php echo ADMINURL; ?>/pnp-court-add/?locationID=<?php echo $D["locationID"]; ?>" class="btn btn-success">
                <i class="fa fa-plus"></i> Add New Court
            </a>
        </div>
    </div>
</div>
<?php } ?>

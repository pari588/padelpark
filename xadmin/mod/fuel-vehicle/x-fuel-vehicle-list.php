<?php
/**
 * Fuel Vehicle List Page
 * Displays list of all vehicles with search
 * Uses xadmin standard look and feel
 */

global $DB, $MXFRM, $MXSTATUS, $TPL, $MXMOD;

// Initialize form handler
$MXFRM = new mxForm();

// Define search fields
$arrSearch = array(
    array("type" => "text", "name" => "vehicleName", "value" => $_GET["vehicleName"] ?? "",
          "title" => "Vehicle Name", "where" => "AND vehicleName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "registrationNumber", "value" => $_GET["registrationNumber"] ?? "",
          "title" => "Registration Number", "where" => "AND registrationNumber LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "fuelType",
          "value" => array($_GET["fuelType"] ?? "", "Petrol" => "Petrol", "Diesel" => "Diesel", "CNG" => "CNG"),
          "title" => "Fuel Type", "where" => "AND fuelType=?", "dtype" => "s"),
);

// Generate search form
$strSearch = $MXFRM->getFormS($arrSearch);

// Build query for count
$DB->vals = array($MXSTATUS);
$DB->types = "i";

foreach ($MXFRM->arrVals as $val) {
    $DB->vals[] = $val;
}
$DB->types .= $MXFRM->types;

$DB->sql = "SELECT vehicleID FROM `" . $DB->pre . "vehicle` WHERE status=?" . $MXFRM->where;
$DB->dbRows();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";

echo $strSearch;
?>

<div class="wrap-right">
    <?php echo getPageNav('', '', array("add")); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("ID", "vehicleID", ' width="8%" align="center"'),
                array("Vehicle Name", "vehicleName", ' width="35%" align="left"'),
                array("Registration #", "registrationNumber", ' width="25%" align="left"'),
                array("Fuel Type", "fuelType", ' width="15%" align="center"'),
                array("Added Date", "createdDate", ' width="17%" align="center"'),
            );

            $DB->vals = array($MXSTATUS);
            $DB->types = "i";

            foreach ($MXFRM->arrVals as $val) {
                $DB->vals[] = $val;
            }
            $DB->types .= $MXFRM->types;

            $DB->sql = "SELECT vehicleID, vehicleName, registrationNumber, fuelType, createdDate FROM `" . $DB->pre . "vehicle`
                        WHERE status=?" . $MXFRM->where . mxOrderBy("vehicleID DESC ") . mxQryLimit();
            $rt = $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr><?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $vehicle) {
                    ?>
                        <tr><?php echo getMAction("mid", $vehicle["vehicleID"]); ?>
                            <td width="8%" align="center"><?php echo intval($vehicle["vehicleID"]); ?></td>
                            <td width="35%" align="left">
                                <?php echo getViewEditUrl("id=" . $vehicle["vehicleID"], $vehicle["vehicleName"]); ?>
                            </td>
                            <td width="25%" align="left"><?php echo htmlspecialchars($vehicle["registrationNumber"] ?? "N/A"); ?></td>
                            <td width="15%" align="center">
                                <span style="background-color: #17a2b8; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.85rem;">
                                    <?php echo htmlspecialchars($vehicle["fuelType"]); ?>
                                </span>
                            </td>
                            <td width="17%" align="center"><?php echo date('d-M-Y', strtotime($vehicle["createdDate"])); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } else { ?>
            <div class="no-records">No vehicles found</div>
        <?php } ?>
    </div>
</div>

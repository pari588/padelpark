<?php
// warehouseType dropdown
$warehouseTypeArr = array("" => "All Types", "Main" => "Main Warehouse", "Sub-Warehouse" => "Sub-Warehouse (Center)", "In-Transit" => "In-Transit", "Project-Site" => "Project Site");
$warehouseTypeOpt = '';
$selWarehouseType = $_GET["warehouseType"] ?? "";
foreach ($warehouseTypeArr as $k => $v) {
    $sel = ($selWarehouseType == $k) ? ' selected="selected"' : '';
    $warehouseTypeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// isActive dropdown
$isActiveArr = array("" => "All", "1" => "Active", "0" => "Inactive");
$isActiveOpt = '';
$selIsActive = $_GET["isActive"] ?? "";
foreach ($isActiveArr as $k => $v) {
    $sel = ($selIsActive == $k) ? ' selected="selected"' : '';
    $isActiveOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Search filters
$arrSearch = array(
    array("type" => "text", "name" => "warehouseID", "title" => "#ID", "where" => "AND w.warehouseID=?", "dtype" => "i"),
    array("type" => "text", "name" => "warehouseCode", "title" => "Code", "where" => "AND w.warehouseCode LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "warehouseName", "title" => "Name", "where" => "AND w.warehouseName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "warehouseType", "title" => "Type", "where" => "AND w.warehouseType=?", "dtype" => "s",
          "value" => $warehouseTypeOpt, "default" => false),
    array("type" => "text", "name" => "city", "title" => "City", "where" => "AND w.city LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "isActive", "title" => "Status", "where" => "AND w.isActive=?", "dtype" => "i",
          "value" => $isActiveOpt, "default" => false)
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT w.warehouseID FROM `" . $DB->pre . "warehouse` w WHERE w.status=?" . $MXFRM->where;
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
                array("#ID", "warehouseID", ' width="4%" align="center"', true),
                array("Code", "warehouseCode", ' width="8%" align="left"'),
                array("Warehouse Name", "warehouseName", ' width="18%" align="left"'),
                array("Type", "warehouseType", ' width="10%" align="center"'),
                array("City", "city", ' width="10%" align="left"'),
                array("State", "state", ' width="10%" align="left"'),
                array("GSTIN", "gstin", ' width="12%" align="left"'),
                array("Contact", "contactPerson", ' width="12%" align="left"'),
                array("Status", "isActive", ' width="8%" align="center"'),
                array("Default", "isDefaultWarehouse", ' width="6%" align="center"')
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT w.* FROM `" . $DB->pre . "warehouse` w WHERE w.status=?" . $MXFRM->where . mxOrderBy("w.warehouseType ASC, w.warehouseName ASC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format warehouse type with badges
                        $typeClasses = array(
                            "Main" => "badge-primary",
                            "Sub-Warehouse" => "badge-info",
                            "In-Transit" => "badge-warning",
                            "Project-Site" => "badge-secondary"
                        );
                        $typeClass = $typeClasses[$d["warehouseType"]] ?? "badge-secondary";
                        $d["warehouseType"] = '<span class="badge ' . $typeClass . '">' . $d["warehouseType"] . '</span>';

                        // Format active status
                        if ($d["isActive"] == 1) {
                            $d["isActive"] = '<span class="badge badge-success">Active</span>';
                        } else {
                            $d["isActive"] = '<span class="badge badge-danger">Inactive</span>';
                        }

                        // Format default warehouse
                        if ($d["isDefaultWarehouse"] == 1) {
                            $d["isDefaultWarehouse"] = '<span class="badge badge-warning"><i class="fa fa-star"></i></span>';
                        } else {
                            $d["isDefaultWarehouse"] = '';
                        }

                        // Format GSTIN (show partial if exists)
                        $d["gstin"] = !empty($d["gstin"]) ? $d["gstin"] : '<span style="color:#999;">Not Set</span>';

                        // Format contact with phone
                        $contactDisplay = $d["contactPerson"] ?? '';
                        if (!empty($d["contactPhone"])) {
                            $contactDisplay .= $contactDisplay ? '<br><small>' . $d["contactPhone"] . '</small>' : $d["contactPhone"];
                        }
                        $d["contactPerson"] = $contactDisplay ?: '<span style="color:#999;">-</span>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["warehouseID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["warehouseID"], strip_tags($d[$v[1]] ?? '')) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No warehouses found.</div>
        <?php } ?>
    </div>
</div>

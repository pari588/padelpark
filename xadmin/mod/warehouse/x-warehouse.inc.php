<?php
/*
addWarehouse = To save new warehouse/location.
updateWarehouse = To update warehouse/location data.
deleteWarehouse = To soft delete warehouse.
getWarehouseDetails = Get warehouse details for AJAX calls.
*/

/**
 * Add new warehouse
 */
function addWarehouse()
{
    global $DB;

    // Type casting
    if (isset($_POST["padelCenterID"])) $_POST["padelCenterID"] = intval($_POST["padelCenterID"]) ?: null;
    if (isset($_POST["latitude"])) $_POST["latitude"] = floatval($_POST["latitude"]) ?: null;
    if (isset($_POST["longitude"])) $_POST["longitude"] = floatval($_POST["longitude"]) ?: null;
    if (isset($_POST["isActive"])) $_POST["isActive"] = intval($_POST["isActive"]);
    if (isset($_POST["canReceiveStock"])) $_POST["canReceiveStock"] = intval($_POST["canReceiveStock"]);
    if (isset($_POST["canDispatchStock"])) $_POST["canDispatchStock"] = intval($_POST["canDispatchStock"]);
    if (isset($_POST["isDefaultWarehouse"])) $_POST["isDefaultWarehouse"] = intval($_POST["isDefaultWarehouse"]);

    // Check for duplicate warehouse code
    $DB->vals = array(1, $_POST["warehouseCode"]);
    $DB->types = "is";
    $DB->sql = "SELECT warehouseID FROM " . $DB->pre . "warehouse WHERE status=? AND warehouseCode=?";
    $existing = $DB->dbRow();
    if ($existing) {
        setResponse(array("err" => 1, "errMsg" => "Warehouse code already exists!"));
        return;
    }

    // If setting as default, unset other defaults
    if (!empty($_POST["isDefaultWarehouse"])) {
        $DB->vals = array(0);
        $DB->types = "i";
        $DB->sql = "UPDATE " . $DB->pre . "warehouse SET isDefaultWarehouse=?";
        $DB->dbQuery();
    }

    // Get state code from state name if not provided
    if (empty($_POST["stateCode"]) && !empty($_POST["state"])) {
        $_POST["stateCode"] = getStateCode($_POST["state"]);
    }

    $DB->table = $DB->pre . "warehouse";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $warehouseID = $DB->insertID;
        setResponse(array("err" => 0, "param" => "id=$warehouseID"));
    } else {
        setResponse(array("err" => 1));
    }
}

/**
 * Update warehouse
 */
function updateWarehouse()
{
    global $DB;
    $warehouseID = intval($_POST["warehouseID"]);

    // Type casting
    if (isset($_POST["padelCenterID"])) $_POST["padelCenterID"] = intval($_POST["padelCenterID"]) ?: null;
    if (isset($_POST["latitude"])) $_POST["latitude"] = floatval($_POST["latitude"]) ?: null;
    if (isset($_POST["longitude"])) $_POST["longitude"] = floatval($_POST["longitude"]) ?: null;
    if (isset($_POST["isActive"])) $_POST["isActive"] = intval($_POST["isActive"]);
    if (isset($_POST["canReceiveStock"])) $_POST["canReceiveStock"] = intval($_POST["canReceiveStock"]);
    if (isset($_POST["canDispatchStock"])) $_POST["canDispatchStock"] = intval($_POST["canDispatchStock"]);
    if (isset($_POST["isDefaultWarehouse"])) $_POST["isDefaultWarehouse"] = intval($_POST["isDefaultWarehouse"]);

    // Check for duplicate warehouse code (excluding current)
    $DB->vals = array(1, $_POST["warehouseCode"], $warehouseID);
    $DB->types = "isi";
    $DB->sql = "SELECT warehouseID FROM " . $DB->pre . "warehouse WHERE status=? AND warehouseCode=? AND warehouseID!=?";
    $existing = $DB->dbRow();
    if ($existing) {
        setResponse(array("err" => 1, "errMsg" => "Warehouse code already exists!"));
        return;
    }

    // If setting as default, unset other defaults
    if (!empty($_POST["isDefaultWarehouse"])) {
        $DB->vals = array(0, $warehouseID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "warehouse SET isDefaultWarehouse=? WHERE warehouseID!=?";
        $DB->dbQuery();
    }

    // Get state code from state name if not provided
    if (empty($_POST["stateCode"]) && !empty($_POST["state"])) {
        $_POST["stateCode"] = getStateCode($_POST["state"]);
    }

    $DB->table = $DB->pre . "warehouse";
    $DB->data = $_POST;
    if ($DB->dbUpdate("warehouseID=?", "i", array($warehouseID))) {
        setResponse(array("err" => 0, "param" => "id=$warehouseID"));
    } else {
        setResponse(array("err" => 1));
    }
}

/**
 * Get warehouse details (for AJAX)
 */
function getWarehouseDetails()
{
    global $DB;
    $warehouseID = intval($_POST["warehouseID"] ?? $_GET["id"] ?? 0);

    $DB->vals = array(1, $warehouseID);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM " . $DB->pre . "warehouse WHERE status=? AND warehouseID=?";
    $warehouse = $DB->dbRow();

    if ($warehouse) {
        echo json_encode(array("err" => 0, "data" => $warehouse));
    } else {
        echo json_encode(array("err" => 1, "msg" => "Warehouse not found"));
    }
    exit;
}

/**
 * Get state code from state name
 */
function getStateCode($stateName)
{
    $stateCodes = array(
        'Andaman and Nicobar' => 'AN',
        'Andhra Pradesh' => 'AP',
        'Arunachal Pradesh' => 'AR',
        'Assam' => 'AS',
        'Bihar' => 'BR',
        'Chandigarh' => 'CH',
        'Chhattisgarh' => 'CT',
        'Daman and Diu' => 'DD',
        'Delhi' => 'DL',
        'Goa' => 'GA',
        'Gujarat' => 'GJ',
        'Himachal Pradesh' => 'HP',
        'Haryana' => 'HR',
        'Jharkhand' => 'JH',
        'Jammu and Kashmir' => 'JK',
        'Karnataka' => 'KA',
        'Kerala' => 'KL',
        'Ladakh' => 'LA',
        'Lakshadweep' => 'LD',
        'Maharashtra' => 'MH',
        'Meghalaya' => 'ML',
        'Manipur' => 'MN',
        'Madhya Pradesh' => 'MP',
        'Mizoram' => 'MZ',
        'Nagaland' => 'NL',
        'Odisha' => 'OR',
        'Punjab' => 'PB',
        'Puducherry' => 'PY',
        'Rajasthan' => 'RJ',
        'Sikkim' => 'SK',
        'Tamil Nadu' => 'TN',
        'Telangana' => 'TS',
        'Tripura' => 'TR',
        'Uttarakhand' => 'UK',
        'Uttar Pradesh' => 'UP',
        'West Bengal' => 'WB'
    );

    return $stateCodes[$stateName] ?? '';
}

/**
 * Get GST state code (2-digit numeric)
 */
function getGSTStateCode($stateCode)
{
    $gstCodes = array(
        'AN' => '35', 'AP' => '37', 'AR' => '12', 'AS' => '18', 'BR' => '10',
        'CH' => '04', 'CT' => '22', 'DD' => '26', 'DL' => '07', 'GA' => '30',
        'GJ' => '24', 'HP' => '02', 'HR' => '06', 'JH' => '20', 'JK' => '01',
        'KA' => '29', 'KL' => '32', 'LA' => '38', 'LD' => '31', 'MH' => '27',
        'ML' => '17', 'MN' => '14', 'MP' => '23', 'MZ' => '15', 'NL' => '13',
        'OR' => '21', 'PB' => '03', 'PY' => '34', 'RJ' => '08', 'SK' => '11',
        'TN' => '33', 'TS' => '36', 'TR' => '16', 'UK' => '05', 'UP' => '09',
        'WB' => '19'
    );

    return $gstCodes[$stateCode] ?? '';
}

/**
 * Get all warehouses for dropdown
 */
function getWarehouseDropdown($selectedID = 0, $excludeID = 0, $type = '')
{
    global $DB;

    $where = "status=1 AND isActive=1";
    $vals = array();
    $types = "";

    if ($type) {
        $where .= " AND warehouseType=?";
        $vals[] = $type;
        $types .= "s";
    }

    if ($excludeID > 0) {
        $where .= " AND warehouseID!=?";
        $vals[] = $excludeID;
        $types .= "i";
    }

    $DB->vals = $vals;
    $DB->types = $types;
    $DB->sql = "SELECT warehouseID, warehouseCode, warehouseName, warehouseType, city
                FROM " . $DB->pre . "warehouse
                WHERE $where
                ORDER BY warehouseType, warehouseName";
    $warehouses = $DB->dbRows();

    $opt = '';
    $currentType = '';
    foreach ($warehouses as $w) {
        if ($currentType !== $w['warehouseType']) {
            if ($currentType !== '') {
                $opt .= '</optgroup>';
            }
            $opt .= '<optgroup label="' . htmlspecialchars($w['warehouseType']) . '">';
            $currentType = $w['warehouseType'];
        }
        $sel = ($selectedID == $w['warehouseID']) ? ' selected="selected"' : '';
        $opt .= '<option value="' . $w['warehouseID'] . '"' . $sel . '>'
              . htmlspecialchars($w['warehouseCode'] . ' - ' . $w['warehouseName'] . ' (' . $w['city'] . ')')
              . '</option>';
    }
    if ($currentType !== '') {
        $opt .= '</optgroup>';
    }

    return $opt;
}

/**
 * Get stock summary for a warehouse
 */
function getWarehouseStockSummary($warehouseID)
{
    global $DB;

    $DB->vals = array($warehouseID);
    $DB->types = "i";
    $DB->sql = "SELECT
                    COUNT(DISTINCT productID) as totalProducts,
                    SUM(availableQty) as totalAvailableQty,
                    SUM(reservedQty) as totalReservedQty,
                    SUM(inTransitQty) as totalInTransitQty,
                    SUM(totalValue) as totalStockValue
                FROM " . $DB->pre . "inventory_stock
                WHERE warehouseID=?";
    return $DB->dbRow();
}

// Action handler
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addWarehouse();
                break;
            case "UPDATE":
                updateWarehouse();
                break;
            case "GET_DETAILS":
                getWarehouseDetails();
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "warehouse", "PK" => "warehouseID"));
    }
}
?>

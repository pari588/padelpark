<?php
/*
addLocation = To save Location data.
updateLocation = To update Location data.
createWarehouseFromLocation = Auto-create warehouse for PNP location.
*/

/**
 * Get state code from state name
 */
function getStateCode($stateName)
{
    $stateCodes = array(
        'Andaman and Nicobar' => 'AN', 'Andhra Pradesh' => 'AP', 'Arunachal Pradesh' => 'AR',
        'Assam' => 'AS', 'Bihar' => 'BR', 'Chandigarh' => 'CH', 'Chhattisgarh' => 'CT',
        'Daman and Diu' => 'DD', 'Delhi' => 'DL', 'Goa' => 'GA', 'Gujarat' => 'GJ',
        'Himachal Pradesh' => 'HP', 'Haryana' => 'HR', 'Jharkhand' => 'JH', 'Jammu and Kashmir' => 'JK',
        'Karnataka' => 'KA', 'Kerala' => 'KL', 'Ladakh' => 'LA', 'Lakshadweep' => 'LD',
        'Maharashtra' => 'MH', 'Meghalaya' => 'ML', 'Manipur' => 'MN', 'Madhya Pradesh' => 'MP',
        'Mizoram' => 'MZ', 'Nagaland' => 'NL', 'Odisha' => 'OR', 'Punjab' => 'PB',
        'Puducherry' => 'PY', 'Rajasthan' => 'RJ', 'Sikkim' => 'SK', 'Tamil Nadu' => 'TN',
        'Telangana' => 'TS', 'Tripura' => 'TR', 'Uttarakhand' => 'UK', 'Uttar Pradesh' => 'UP',
        'West Bengal' => 'WB'
    );
    return $stateCodes[$stateName] ?? '';
}

/**
 * Create warehouse from PNP location data
 */
function createWarehouseFromLocation($locationID, $locationData)
{
    global $DB;

    // Generate warehouse code from location code
    $warehouseCode = "WH-" . $locationData["locationCode"];

    // Check if warehouse code already exists
    $DB->vals = array(1, $warehouseCode);
    $DB->types = "is";
    $DB->sql = "SELECT warehouseID FROM " . $DB->pre . "warehouse WHERE status=? AND warehouseCode=?";
    $existing = $DB->dbRow();
    if ($existing) {
        // Already exists, just link it
        return $existing["warehouseID"];
    }

    // Prepare warehouse data
    $warehouseData = array(
        "warehouseCode" => $warehouseCode,
        "warehouseName" => $locationData["locationName"],
        "warehouseType" => "Sub-Warehouse",
        "padelCenterID" => $locationID,
        "addressLine1" => $locationData["address"] ?? "",
        "city" => $locationData["city"] ?? "",
        "state" => $locationData["state"] ?? "",
        "stateCode" => getStateCode($locationData["state"] ?? ""),
        "pincode" => $locationData["pincode"] ?? "",
        "latitude" => $locationData["latitude"] ?? null,
        "longitude" => $locationData["longitude"] ?? null,
        "gstin" => $locationData["gstNo"] ?? "",
        "contactPerson" => $locationData["contactPerson"] ?? "",
        "contactPhone" => $locationData["contactPhone"] ?? "",
        "contactEmail" => $locationData["contactEmail"] ?? "",
        "isActive" => 1,
        "canReceiveStock" => 1,
        "canDispatchStock" => 1,
        "status" => 1
    );

    $DB->table = $DB->pre . "warehouse";
    $DB->data = $warehouseData;
    if ($DB->dbInsert()) {
        return $DB->insertID;
    }
    return null;
}

/**
 * Update warehouse from PNP location data
 */
function updateWarehouseFromLocation($warehouseID, $locationData)
{
    global $DB;

    // Prepare warehouse data
    $warehouseData = array(
        "warehouseName" => $locationData["locationName"],
        "addressLine1" => $locationData["address"] ?? "",
        "city" => $locationData["city"] ?? "",
        "state" => $locationData["state"] ?? "",
        "stateCode" => getStateCode($locationData["state"] ?? ""),
        "pincode" => $locationData["pincode"] ?? "",
        "latitude" => $locationData["latitude"] ?? null,
        "longitude" => $locationData["longitude"] ?? null,
        "gstin" => $locationData["gstNo"] ?? "",
        "contactPerson" => $locationData["contactPerson"] ?? "",
        "contactPhone" => $locationData["contactPhone"] ?? "",
        "contactEmail" => $locationData["contactEmail"] ?? ""
    );

    $DB->table = $DB->pre . "warehouse";
    $DB->data = $warehouseData;
    $DB->dbUpdate("warehouseID=?", "i", array($warehouseID));
}

function addLocation()
{
    global $DB;

    // Generate location code if not provided
    if (empty($_POST["locationCode"])) {
        $cityCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $_POST["city"] ?? "LOC"), 0, 3));
        $DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "pnp_location WHERE locationCode LIKE '" . $cityCode . "%'";
        $row = $DB->dbRow();
        $_POST["locationCode"] = $cityCode . str_pad(($row["cnt"] + 1), 3, "0", STR_PAD_LEFT);
    }

    // Store location data for warehouse creation
    $locationData = $_POST;

    $DB->table = $DB->pre . "pnp_location";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $locationID = $DB->insertID;

        // Auto-create warehouse for this location
        $warehouseID = createWarehouseFromLocation($locationID, $locationData);

        // Update location with warehouse ID
        if ($warehouseID) {
            $DB->vals = array($warehouseID, $locationID);
            $DB->types = "ii";
            $DB->sql = "UPDATE " . $DB->pre . "pnp_location SET warehouseID=? WHERE locationID=?";
            $DB->dbQuery();
        }

        setResponse(array("err" => 0, "param" => "id=" . $locationID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateLocation()
{
    global $DB;
    $locationID = intval($_POST["locationID"]);

    // Get current location data to find linked warehouse
    $DB->vals = array($locationID);
    $DB->types = "i";
    $DB->sql = "SELECT warehouseID FROM " . $DB->pre . "pnp_location WHERE locationID=?";
    $current = $DB->dbRow();
    $warehouseID = $current["warehouseID"] ?? null;

    // Store location data for warehouse update
    $locationData = $_POST;

    $DB->table = $DB->pre . "pnp_location";
    $DB->data = $_POST;
    if ($DB->dbUpdate("locationID=?", "i", array($locationID))) {
        // If no warehouse linked, create one
        if (!$warehouseID) {
            $warehouseID = createWarehouseFromLocation($locationID, $locationData);
            if ($warehouseID) {
                $DB->vals = array($warehouseID, $locationID);
                $DB->types = "ii";
                $DB->sql = "UPDATE " . $DB->pre . "pnp_location SET warehouseID=? WHERE locationID=?";
                $DB->dbQuery();
            }
        } else {
            // Update the linked warehouse
            updateWarehouseFromLocation($warehouseID, $locationData);
        }

        setResponse(array("err" => 0, "param" => "id=" . $locationID));
    } else {
        setResponse(array("err" => 1));
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addLocation(); break;
            case "UPDATE": updateLocation(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "pnp_location", "PK" => "locationID"));
}

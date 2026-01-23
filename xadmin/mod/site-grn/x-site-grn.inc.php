<?php
/*
Site GRN Module - Core Functions
Goods Received Note at Project Site
*/

/**
 * Generate unique GRN number: SGRN-YYYYMMDD-XXXX
 */
function generateSiteGrnNo()
{
    global $DB;
    $prefix = "SGRN-" . date("Ymd") . "-";

    $DB->sql = "SELECT grnNo FROM " . $DB->pre . "site_grn
                WHERE grnNo LIKE ? ORDER BY grnID DESC LIMIT 1";
    $DB->vals = array($prefix . "%");
    $DB->types = "s";
    $last = $DB->dbRow();

    if ($last && !empty($last["grnNo"])) {
        $parts = explode("-", $last["grnNo"]);
        $seq = intval(end($parts)) + 1;
    } else {
        $seq = 1;
    }

    return $prefix . str_pad($seq, 4, "0", STR_PAD_LEFT);
}

/**
 * Add new site GRN
 */
function addSiteGrn()
{
    global $DB;

    $projectID = intval($_POST["projectID"]);
    $allocationID = intval($_POST["allocationID"] ?? 0);

    if (!$projectID) {
        setResponse(array("err" => 1, "errMsg" => "Project is required"));
        return;
    }

    $grnNo = generateSiteGrnNo();

    // Create GRN header
    $DB->table = $DB->pre . "site_grn";
    $DB->data = array(
        "grnNo" => $grnNo,
        "projectID" => $projectID,
        "allocationID" => $allocationID ?: null,
        "grnDate" => $_POST["grnDate"] ?? date("Y-m-d"),
        "grnType" => $_POST["grnType"] ?? "From-Warehouse",
        "supplierID" => intval($_POST["supplierID"] ?? 0) ?: null,
        "supplierName" => $_POST["supplierName"] ?? "",
        "invoiceNo" => $_POST["invoiceNo"] ?? "",
        "invoiceDate" => !empty($_POST["invoiceDate"]) ? $_POST["invoiceDate"] : null,
        "vehicleNo" => $_POST["vehicleNo"] ?? "",
        "transporterName" => $_POST["transporterName"] ?? "",
        "lrNumber" => $_POST["lrNumber"] ?? "",
        "receivedBy" => $_SESSION["mxAdminUserID"] ?? 0,
        "receiverName" => $_POST["receiverName"] ?? "",
        "grnStatus" => "Draft",
        "latitude" => floatval($_POST["latitude"] ?? 0) ?: null,
        "longitude" => floatval($_POST["longitude"] ?? 0) ?: null,
        "notes" => $_POST["notes"] ?? "",
        "createdBy" => $_SESSION["mxAdminUserID"] ?? 0
    );

    if (!$DB->dbInsert()) {
        setResponse(array("err" => 1, "errMsg" => "Failed to create GRN"));
        return;
    }

    $grnID = $DB->insertID;

    // Add items
    if (isset($_POST["items"]) && is_array($_POST["items"])) {
        $totalItems = 0;
        $totalQty = 0;
        $totalValue = 0;

        foreach ($_POST["items"] as $item) {
            $productID = intval($item["productID"]);
            $receivedQty = floatval($item["receivedQty"]);

            if ($productID <= 0 || $receivedQty <= 0) continue;

            $expectedQty = floatval($item["expectedQty"] ?? $receivedQty);
            $unitCost = floatval($item["unitCost"] ?? 0);
            $totalCost = $receivedQty * $unitCost;

            $DB->table = $DB->pre . "site_grn_item";
            $DB->data = array(
                "grnID" => $grnID,
                "allocationItemID" => intval($item["allocationItemID"] ?? 0) ?: null,
                "productID" => $productID,
                "productName" => $item["productName"] ?? "",
                "productSKU" => $item["productSKU"] ?? "",
                "unit" => $item["unit"] ?? "Unit",
                "expectedQty" => $expectedQty,
                "receivedQty" => $receivedQty,
                "acceptedQty" => $receivedQty, // Default to received
                "rejectedQty" => 0,
                "shortageQty" => max(0, $expectedQty - $receivedQty),
                "excessQty" => max(0, $receivedQty - $expectedQty),
                "unitCost" => $unitCost,
                "totalCost" => $totalCost,
                "itemCondition" => $item["itemCondition"] ?? "Good",
                "storageLocation" => $item["storageLocation"] ?? "",
                "notes" => $item["notes"] ?? ""
            );
            $DB->dbInsert();

            $totalItems++;
            $totalQty += $receivedQty;
            $totalValue += $totalCost;
        }

        // Update GRN totals
        $DB->vals = array($totalItems, $totalQty, $totalValue, $grnID);
        $DB->types = "iddi";
        $DB->sql = "UPDATE " . $DB->pre . "site_grn SET totalItems=?, totalQty=?, totalValue=? WHERE grnID=?";
        $DB->dbQuery();
    }

    setResponse(array("err" => 0, "grnID" => $grnID, "grnNo" => $grnNo, "param" => "id=$grnID"));
}

/**
 * Update site GRN
 */
function updateSiteGrn()
{
    global $DB;
    $grnID = intval($_POST["grnID"]);

    // Get existing GRN
    $DB->vals = array($grnID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "site_grn WHERE grnID=?";
    $grn = $DB->dbRow();

    if (!$grn) {
        setResponse(array("err" => 1, "errMsg" => "GRN not found"));
        return;
    }

    if ($grn["grnStatus"] == "Accepted") {
        setResponse(array("err" => 1, "errMsg" => "Cannot modify accepted GRN"));
        return;
    }

    // Update header
    $DB->vals = array(
        $_POST["grnDate"] ?? $grn["grnDate"],
        $_POST["vehicleNo"] ?? "",
        $_POST["transporterName"] ?? "",
        $_POST["lrNumber"] ?? "",
        $_POST["receiverName"] ?? "",
        $_POST["notes"] ?? "",
        $grnID
    );
    $DB->types = "ssssssi";
    $DB->sql = "UPDATE " . $DB->pre . "site_grn SET
                grnDate=?, vehicleNo=?, transporterName=?, lrNumber=?, receiverName=?, notes=?
                WHERE grnID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "param" => "id=$grnID"));
}

/**
 * Accept GRN (confirm receipt and update inventory)
 */
function acceptSiteGrn()
{
    global $DB;
    $grnID = intval($_POST["grnID"]);

    // Get GRN
    $DB->vals = array($grnID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "site_grn WHERE grnID=? AND status=1";
    $grn = $DB->dbRow();

    if (!$grn) {
        setResponse(array("err" => 1, "errMsg" => "GRN not found"));
        return;
    }

    if ($grn["grnStatus"] == "Accepted") {
        setResponse(array("err" => 1, "errMsg" => "Already accepted"));
        return;
    }

    // Get GRN items
    $DB->vals = array($grnID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "site_grn_item WHERE grnID=? AND status=1";
    $DB->dbRows();
    $items = $DB->rows;

    // Update allocation items (mark as received)
    if ($grn["allocationID"]) {
        foreach ($items as $item) {
            if ($item["allocationItemID"]) {
                // Update allocation item received qty
                $DB->vals = array($item["receivedQty"], $item["allocationItemID"]);
                $DB->types = "di";
                $DB->sql = "UPDATE " . $DB->pre . "stock_allocation_item SET receivedQty = receivedQty + ? WHERE itemID=?";
                $DB->dbQuery();
            }

            // Clear in-transit qty from source warehouse
            $DB->vals = array($grn["allocationID"]);
            $DB->types = "i";
            $DB->sql = "SELECT warehouseID FROM " . $DB->pre . "stock_allocation WHERE allocationID=?";
            $allocation = $DB->dbRow();

            if ($allocation) {
                $DB->vals = array($item["receivedQty"], $allocation["warehouseID"], $item["productID"]);
                $DB->types = "dii";
                $DB->sql = "UPDATE " . $DB->pre . "inventory_stock
                            SET inTransitQty = GREATEST(0, inTransitQty - ?)
                            WHERE warehouseID=? AND productID=?";
                $DB->dbQuery();
            }
        }
    }

    // Update GRN status
    $DB->vals = array(
        "Accepted",
        $_SESSION["mxAdminUserID"] ?? 0,
        date("Y-m-d H:i:s"),
        $grnID
    );
    $DB->types = "sisi";
    $DB->sql = "UPDATE " . $DB->pre . "site_grn SET
                grnStatus='Accepted', acceptedBy=?, acceptedDate=?
                WHERE grnID=?";
    $DB->dbQuery();

    setResponse(array("err" => 0, "msg" => "GRN accepted successfully"));
}

/**
 * Get GRN details
 */
function getSiteGrnDetails($grnID)
{
    global $DB;

    $DB->vals = array($grnID);
    $DB->types = "i";
    $DB->sql = "SELECT g.*, p.projectNo, p.projectName, p.clientName, p.siteCity, p.siteAddress,
                       a.allocationNo
                FROM " . $DB->pre . "site_grn g
                LEFT JOIN " . $DB->pre . "sky_padel_project p ON g.projectID = p.projectID
                LEFT JOIN " . $DB->pre . "stock_allocation a ON g.allocationID = a.allocationID
                WHERE g.grnID = ?";
    $grn = $DB->dbRow();

    if ($grn) {
        // Get items
        $DB->vals = array($grnID);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "site_grn_item WHERE grnID=? AND status=1 ORDER BY itemID";
        $DB->dbRows();
        $grn["items"] = $DB->rows;
    }

    return $grn;
}

/**
 * Get allocation items for GRN creation
 */
function getAllocationItems()
{
    global $DB;
    $allocationID = intval($_POST["allocationID"] ?? $_GET["allocationID"] ?? 0);

    if (!$allocationID) {
        echo json_encode(array("err" => 1, "msg" => "Allocation not specified"));
        exit;
    }

    // Get allocation
    $DB->vals = array($allocationID);
    $DB->types = "i";
    $DB->sql = "SELECT a.*, p.projectNo, p.projectName, p.projectID
                FROM " . $DB->pre . "stock_allocation a
                LEFT JOIN " . $DB->pre . "sky_padel_project p ON a.projectID = p.projectID
                WHERE a.allocationID=?";
    $allocation = $DB->dbRow();

    // Get items
    $DB->vals = array($allocationID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "stock_allocation_item WHERE allocationID=? AND status=1";
    $DB->dbRows();

    echo json_encode(array("err" => 0, "allocation" => $allocation, "items" => $DB->rows));
    exit;
}

// Handle AJAX requests
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addSiteGrn(); break;
            case "UPDATE": updateSiteGrn(); break;
            case "ACCEPT": acceptSiteGrn(); break;
            case "GET_ALLOCATION": getAllocationItems(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "site_grn", "PK" => "grnID"));
    }
}
?>

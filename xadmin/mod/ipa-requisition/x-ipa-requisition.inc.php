<?php
/*
IPA Requisition Module
Coaches request supplies/equipment - synced with warehouse products
*/

if (isset($_POST["xAction"])) {
    ob_start();
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean();

    mxCheckRequest(true, true);

    $xAction = $_POST["xAction"];

    if ($xAction == "ADD") {
        $coachID = intval($_POST["coachID"] ?? 0);
        $centerID = intval($_POST["centerID"] ?? 0);
        $requisitionDate = $_POST["requisitionDate"] ?? date("Y-m-d");
        $requiredByDate = $_POST["requiredByDate"] ?? null;
        $notes = trim($_POST["notes"] ?? "");
        $items = json_decode($_POST["items"] ?? "[]", true);

        if ($coachID < 1) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Please select a coach"));
            exit;
        }

        if (!is_array($items) || empty($items)) {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Please add at least one item"));
            exit;
        }

        // Generate requisition number
        $DB->sql = "SELECT MAX(requisitionID) as maxID FROM " . $DB->pre . "ipa_requisition";
        $DB->dbRows();
        $maxID = ($DB->rows[0]["maxID"] ?? 0) + 1;
        $requisitionNo = "REQ-" . date("Ymd") . "-" . str_pad($maxID, 4, "0", STR_PAD_LEFT);

        // Insert requisition
        $DB->vals = array($requisitionNo, $coachID, $centerID > 0 ? $centerID : null, $requisitionDate, $requiredByDate ?: null, "Draft", count($items), $notes);
        $DB->types = "siisssiss";
        $DB->sql = "INSERT INTO " . $DB->pre . "ipa_requisition
                    (requisitionNo, coachID, centerID, requisitionDate, requiredByDate, requisitionStatus, totalItems, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $DB->dbQuery();
        $requisitionID = $DB->insertID;

        // Insert items with productID
        foreach ($items as $item) {
            $productID = intval($item["productID"] ?? 0);
            $requestedQty = intval($item["requestedQty"] ?? 1);
            $itemNotes = trim($item["notes"] ?? "");

            if ($productID > 0 && $requestedQty > 0) {
                // Get product details
                $DB->vals = array($productID);
                $DB->types = "i";
                $DB->sql = "SELECT productName, uom FROM " . $DB->pre . "product WHERE productID=?";
                $DB->dbRows();
                $product = !empty($DB->rows) ? $DB->rows[0] : array("productName" => "", "uom" => "Pcs");

                $DB->vals = array($requisitionID, $productID, $product["productName"], $requestedQty, $product["uom"], $itemNotes);
                $DB->types = "iisiss";
                $DB->sql = "INSERT INTO " . $DB->pre . "ipa_requisition_item
                            (requisitionID, productID, productName, requestedQty, unit, notes)
                            VALUES (?, ?, ?, ?, ?, ?)";
                $DB->dbQuery();
            }
        }

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Requisition created: " . $requisitionNo, "id" => $requisitionID));
        exit;
    }

    if ($xAction == "UPDATE") {
        $requisitionID = intval($_POST["requisitionID"] ?? 0);
        $requiredByDate = $_POST["requiredByDate"] ?? null;
        $notes = trim($_POST["notes"] ?? "");
        $items = json_decode($_POST["items"] ?? "[]", true);

        // Check if can be edited (only Draft status)
        $DB->vals = array($requisitionID);
        $DB->types = "i";
        $DB->sql = "SELECT requisitionStatus FROM " . $DB->pre . "ipa_requisition WHERE requisitionID=?";
        $DB->dbRows();
        if (empty($DB->rows) || $DB->rows[0]["requisitionStatus"] != "Draft") {
            header('Content-Type: application/json');
            echo json_encode(array("err" => 1, "msg" => "Only draft requisitions can be edited"));
            exit;
        }

        // Update requisition
        $DB->vals = array($requiredByDate ?: null, count($items), $notes, $requisitionID);
        $DB->types = "sisi";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_requisition SET requiredByDate=?, totalItems=?, notes=? WHERE requisitionID=?";
        $DB->dbQuery();

        // Delete existing items and re-insert
        $DB->vals = array($requisitionID);
        $DB->types = "i";
        $DB->sql = "DELETE FROM " . $DB->pre . "ipa_requisition_item WHERE requisitionID=?";
        $DB->dbQuery();

        foreach ($items as $item) {
            $productID = intval($item["productID"] ?? 0);
            $requestedQty = intval($item["requestedQty"] ?? 1);
            $itemNotes = trim($item["notes"] ?? "");

            if ($productID > 0 && $requestedQty > 0) {
                // Get product details
                $DB->vals = array($productID);
                $DB->types = "i";
                $DB->sql = "SELECT productName, uom FROM " . $DB->pre . "product WHERE productID=?";
                $DB->dbRows();
                $product = !empty($DB->rows) ? $DB->rows[0] : array("productName" => "", "uom" => "Pcs");

                $DB->vals = array($requisitionID, $productID, $product["productName"], $requestedQty, $product["uom"], $itemNotes);
                $DB->types = "iisiss";
                $DB->sql = "INSERT INTO " . $DB->pre . "ipa_requisition_item
                            (requisitionID, productID, productName, requestedQty, unit, notes)
                            VALUES (?, ?, ?, ?, ?, ?)";
                $DB->dbQuery();
            }
        }

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Requisition updated"));
        exit;
    }

    if ($xAction == "SUBMIT") {
        $requisitionID = intval($_POST["requisitionID"] ?? 0);

        $DB->vals = array("Submitted", $requisitionID, "Draft");
        $DB->types = "sis";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_requisition SET requisitionStatus=? WHERE requisitionID=? AND requisitionStatus=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Requisition submitted for approval"));
        exit;
    }

    if ($xAction == "APPROVE") {
        $requisitionID = intval($_POST["requisitionID"] ?? 0);
        $approvedBy = intval($_SESSION["mxAdminUserID"] ?? 0);
        $items = json_decode($_POST["items"] ?? "[]", true);

        // Update approved quantities
        foreach ($items as $item) {
            $itemID = intval($item["itemID"] ?? 0);
            $approvedQty = intval($item["approvedQty"] ?? 0);

            $DB->vals = array($approvedQty, $itemID);
            $DB->types = "ii";
            $DB->sql = "UPDATE " . $DB->pre . "ipa_requisition_item SET approvedQty=? WHERE itemID=?";
            $DB->dbQuery();
        }

        // Update requisition status
        $DB->vals = array("Approved", $approvedBy, $requisitionID, "Submitted");
        $DB->types = "siis";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_requisition SET requisitionStatus=?, approvedBy=?, approvedDate=NOW() WHERE requisitionID=? AND requisitionStatus=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Requisition approved"));
        exit;
    }

    if ($xAction == "REJECT") {
        $requisitionID = intval($_POST["requisitionID"] ?? 0);
        $rejectionReason = trim($_POST["rejectionReason"] ?? "");

        $DB->vals = array("Rejected", $rejectionReason, $requisitionID, "Submitted");
        $DB->types = "ssis";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_requisition SET requisitionStatus=?, rejectionReason=? WHERE requisitionID=? AND requisitionStatus=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Requisition rejected"));
        exit;
    }

    if ($xAction == "DELETE") {
        $requisitionID = intval($_POST["requisitionID"] ?? 0);

        $DB->vals = array(0, $requisitionID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "ipa_requisition SET status=? WHERE requisitionID=?";
        $DB->dbQuery();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "msg" => "Requisition deleted"));
        exit;
    }

    if ($xAction == "GET_ITEMS") {
        $requisitionID = intval($_POST["requisitionID"] ?? 0);

        $DB->vals = array($requisitionID);
        $DB->types = "i";
        $DB->sql = "SELECT ri.*, p.productSKU,
                           COALESCE((SELECT SUM(s.availableQty) FROM " . $DB->pre . "inventory_stock s WHERE s.productID = ri.productID), 0) as stockQty
                    FROM " . $DB->pre . "ipa_requisition_item ri
                    LEFT JOIN " . $DB->pre . "product p ON ri.productID = p.productID
                    WHERE ri.requisitionID=? AND ri.status=1";
        $DB->dbRows();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "items" => $DB->rows ?: array()));
        exit;
    }

    if ($xAction == "GET_PRODUCTS") {
        // Get all active products for dropdown
        $DB->sql = "SELECT p.productID, p.productName, p.productSKU, p.uom,
                           COALESCE((SELECT SUM(s.availableQty) FROM " . $DB->pre . "inventory_stock s WHERE s.productID = p.productID), 0) as stockQty
                    FROM " . $DB->pre . "product p
                    WHERE p.status=1 AND p.isStockable=1
                    ORDER BY p.productName";
        $DB->dbRows();

        header('Content-Type: application/json');
        echo json_encode(array("err" => 0, "products" => $DB->rows ?: array()));
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(array("err" => 1, "msg" => "Invalid action"));
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_requisition", "PK" => "requisitionID"));
    }
}

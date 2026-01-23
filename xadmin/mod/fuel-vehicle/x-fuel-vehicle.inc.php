<?php
/**
 * Fuel Vehicle Management Module
 * Handles vehicle CRUD operations for fuel expense tracking
 *
 * Project: Fuel Expenses Management System
 * Date: November 2025
 */

// Handle AJAX requests
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");

    $MXRES = mxCheckRequest();

    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addFuelVehicle();
                break;
            case "UPDATE":
                updateFuelVehicle();
                break;
            case "DELETE":
                deleteFuelVehicle();
                break;
            default:
                $MXRES["err"] = 1;
                $MXRES["msg"] = "Unknown action";
                break;
        }
    }

    echo json_encode($MXRES);
    exit;
}

/**
 * Add new vehicle
 */
function addFuelVehicle() {
    global $DB, $MXRES;

    // Validate required fields
    if (empty($_POST["vehicleName"])) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Vehicle name is required";
        return;
    }

    // Clean vehicle name
    $_POST["vehicleName"] = trim(htmlspecialchars($_POST["vehicleName"]));
    $_POST["registrationNumber"] = isset($_POST["registrationNumber"]) ? trim(htmlspecialchars($_POST["registrationNumber"])) : "";
    $_POST["fuelType"] = isset($_POST["fuelType"]) ? $_POST["fuelType"] : "Petrol";
    $_POST["notes"] = isset($_POST["notes"]) ? trim(htmlspecialchars($_POST["notes"])) : "";
    $_POST["status"] = 1;

    // Insert into database
    $DB->table = $DB->pre . "vehicle";
    $DB->data = $_POST;

    if ($DB->dbInsert()) {
        $vehicleID = $DB->insertID;
        $MXRES["err"] = 0;
        $MXRES["msg"] = "Vehicle added successfully";
        $MXRES["param"] = "id=" . $vehicleID;
    } else {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Failed to add vehicle";
    }
}

/**
 * Update existing vehicle
 */
function updateFuelVehicle() {
    global $DB, $MXRES;

    $vehicleID = intval($_POST["vehicleID"]);

    if ($vehicleID <= 0) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Invalid vehicle ID";
        return;
    }

    if (empty($_POST["vehicleName"])) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Vehicle name is required";
        return;
    }

    // Clean input
    $_POST["vehicleName"] = trim(htmlspecialchars($_POST["vehicleName"]));
    $_POST["registrationNumber"] = isset($_POST["registrationNumber"]) ? trim(htmlspecialchars($_POST["registrationNumber"])) : "";
    $_POST["fuelType"] = isset($_POST["fuelType"]) ? $_POST["fuelType"] : "Petrol";
    $_POST["notes"] = isset($_POST["notes"]) ? trim(htmlspecialchars($_POST["notes"])) : "";

    // Update database
    $DB->table = $DB->pre . "vehicle";
    $DB->data = $_POST;

    if ($DB->dbUpdate("vehicleID=?", "i", array($vehicleID))) {
        $MXRES["err"] = 0;
        $MXRES["msg"] = "Vehicle updated successfully";
        $MXRES["param"] = "id=" . $vehicleID;
    } else {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Failed to update vehicle";
    }
}

/**
 * Delete (soft delete) vehicle
 */
function deleteFuelVehicle() {
    global $DB, $MXRES;

    $vehicleID = intval($_POST["vehicleID"]);

    if ($vehicleID <= 0) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Invalid vehicle ID";
        return;
    }

    // Soft delete - set status to 0
    $DB->vals = array(0, $vehicleID);
    $DB->types = "ii";
    $DB->sql = "UPDATE `" . $DB->pre . "vehicle` SET status=? WHERE vehicleID=?";

    if ($DB->dbQuery()) {
        $MXRES["err"] = 0;
        $MXRES["msg"] = "Vehicle deleted successfully";
    } else {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Failed to delete vehicle";
    }
}

// Module configuration - Only when not handling AJAX
if (function_exists("setModVars")) {
    setModVars(array(
        "TBL" => "vehicle",
        "PK" => "vehicleID"
    ));
}

?>

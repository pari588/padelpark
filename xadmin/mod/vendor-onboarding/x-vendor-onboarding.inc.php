<?php
/**
 * Vendor Onboarding Module - Backend Logic
 */

function generateVendorCode() {
    global $DB;
    $prefix = "VND-" . date("Ymd") . "-";
    $DB->sql = "SELECT vendorCode FROM " . $DB->pre . "vendor_onboarding WHERE vendorCode LIKE ? ORDER BY vendorID DESC LIMIT 1";
    $DB->vals = array($prefix . "%");
    $DB->types = "s";
    $row = $DB->dbRow();
    $newNum = $row ? intval(substr($row["vendorCode"], -4)) + 1 : 1;
    return $prefix . str_pad($newNum, 4, "0", STR_PAD_LEFT);
}

function addVendor() {
    global $DB, $MXRES;

    // Validate required fields
    $required = array("legalName", "contactPersonName", "contactPhone", "contactEmail");
    $missing = array();
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        $MXRES = array("err" => 1, "msg" => "Missing required fields: " . implode(", ", $missing));
        return;
    }

    $vendorCode = !empty($_POST["vendorCode"]) ? $_POST["vendorCode"] : generateVendorCode();
    $_POST["vendorCode"] = $vendorCode;

    // Set defaults for required fields
    if (!isset($_POST["vendorStatus"]) || empty($_POST["vendorStatus"])) $_POST["vendorStatus"] = "Pending";
    if (!isset($_POST["approvalStatus"]) || empty($_POST["approvalStatus"])) $_POST["approvalStatus"] = $_POST["vendorStatus"];
    if (!isset($_POST["registrationSource"]) || empty($_POST["registrationSource"])) $_POST["registrationSource"] = "Admin";
    if (!isset($_POST["vendorType"]) || empty($_POST["vendorType"])) $_POST["vendorType"] = "Goods";
    if (!isset($_POST["status"])) $_POST["status"] = 1;

    // Handle empty optional fields - convert to null
    if (isset($_POST["projectID"]) && $_POST["projectID"] === "") $_POST["projectID"] = null;
    if (isset($_POST["vendorCategory"]) && $_POST["vendorCategory"] === "") $_POST["vendorCategory"] = null;

    // Ensure required text fields have values
    if (empty($_POST["registeredAddress"])) $_POST["registeredAddress"] = $_POST["businessAddress"] ?? "Not provided";
    if (empty($_POST["panNumber"])) $_POST["panNumber"] = "NOTPROVIDED";

    $DB->table = $DB->pre . "vendor_onboarding";
    $DB->data = $_POST;

    try {
        if ($DB->dbInsert()) {
            $vendorID = $DB->insertID;
            $MXRES = array("err" => 0, "msg" => "Vendor added successfully!", "param" => "vendorID=$vendorID");
        } else {
            $MXRES = array("err" => 1, "msg" => "Failed to add vendor. Check all required fields.");
        }
    } catch (Exception $e) {
        $MXRES = array("err" => 1, "msg" => "Database error: " . $e->getMessage());
    }
}

function updateVendor() {
    global $DB, $MXRES;
    $vendorID = intval($_POST["vendorID"]);

    // Sync approvalStatus with vendorStatus if vendorStatus is being changed
    if (isset($_POST["vendorStatus"])) {
        $_POST["approvalStatus"] = $_POST["vendorStatus"];
        // Set timestamps based on status
        if ($_POST["vendorStatus"] == "Approved") {
            $_POST["approvedAt"] = date("Y-m-d H:i:s");
        }
    }

    // Handle projectID - convert empty to null
    if (isset($_POST["projectID"]) && $_POST["projectID"] === "") {
        $_POST["projectID"] = null;
    }

    // Handle vendorCategory - convert empty to null
    if (isset($_POST["vendorCategory"]) && $_POST["vendorCategory"] === "") {
        $_POST["vendorCategory"] = null;
    }

    $DB->table = $DB->pre . "vendor_onboarding";
    $DB->data = $_POST;

    try {
        if ($DB->dbUpdate("vendorID=?", "i", array($vendorID))) {
            $MXRES = array("err" => 0, "msg" => "Vendor updated successfully!", "param" => "vendorID=$vendorID");
        } else {
            $MXRES = array("err" => 1, "msg" => "Failed to update vendor.");
        }
    } catch (Exception $e) {
        $MXRES = array("err" => 1, "msg" => "Error: " . $e->getMessage());
    }
}

function approveVendor() {
    global $DB, $MXRES;
    $vendorID = intval($_POST["vendorID"]);

    // Check if this is a public registration with an existing portal user
    $DB->sql = "SELECT registrationSource FROM " . $DB->pre . "vendor_onboarding WHERE vendorID = ?";
    $DB->vals = array($vendorID);
    $DB->types = "i";
    $vendor = $DB->dbRow();

    $DB->table = $DB->pre . "vendor_onboarding";
    $DB->data = array(
        "vendorStatus" => "Approved",
        "approvalStatus" => "Approved",
        "approvedAt" => date("Y-m-d H:i:s")
    );

    // For public registrations, auto-activate portal (they already have a password)
    if ($vendor && $vendor["registrationSource"] === "Public") {
        // Check if portal user exists
        $DB->sql = "SELECT userID FROM " . $DB->pre . "vendor_portal_user WHERE vendorID = ?";
        $DB->vals = array($vendorID);
        $DB->types = "i";
        $portalUser = $DB->dbRow();

        if ($portalUser) {
            // Activate portal user
            $DB->table = $DB->pre . "vendor_portal_user";
            $DB->data = array("isActive" => 1);
            $DB->dbUpdate("vendorID=?", "i", array($vendorID));

            // Enable portal for vendor
            $DB->table = $DB->pre . "vendor_onboarding";
            $DB->data["portalEnabled"] = 1;
            $DB->data["portalActivatedAt"] = date("Y-m-d H:i:s");
        }
    }

    $DB->table = $DB->pre . "vendor_onboarding";
    if ($DB->dbUpdate("vendorID=?", "i", array($vendorID))) {
        $msg = "Vendor approved successfully!";
        if ($vendor && $vendor["registrationSource"] === "Public") {
            $msg .= " Portal access has been automatically activated.";
        }
        $MXRES = array("err" => 0, "msg" => $msg);
    } else {
        $MXRES = array("err" => 1, "msg" => "Failed to approve vendor.");
    }
}

function disapproveVendor() {
    global $DB, $MXRES;
    $vendorID = intval($_POST["vendorID"]);
    $reason = isset($_POST["reason"]) ? $_POST["reason"] : "";
    $DB->table = $DB->pre . "vendor_onboarding";
    $DB->data = array(
        "vendorStatus" => "Disapproved",
        "approvalStatus" => "Disapproved",
        "disapprovalReason" => $reason
    );
    if ($DB->dbUpdate("vendorID=?", "i", array($vendorID))) {
        $MXRES = array("err" => 0, "msg" => "Vendor disapproved.");
    } else {
        $MXRES = array("err" => 1, "msg" => "Failed to disapprove vendor.");
    }
}

function blockVendor() {
    global $DB, $MXRES;
    $vendorID = intval($_POST["vendorID"]);
    $reason = isset($_POST["reason"]) ? $_POST["reason"] : "";
    $DB->table = $DB->pre . "vendor_onboarding";
    $DB->data = array(
        "vendorStatus" => "Blocked",
        "approvalStatus" => "Blocked",
        "blockReason" => $reason,
        "blockedAt" => date("Y-m-d H:i:s"),
        "portalEnabled" => 0
    );
    if ($DB->dbUpdate("vendorID=?", "i", array($vendorID))) {
        $MXRES = array("err" => 0, "msg" => "Vendor blocked.");
    } else {
        $MXRES = array("err" => 1, "msg" => "Failed to block vendor.");
    }
}

function unblockVendor() {
    global $DB, $MXRES;
    $vendorID = intval($_POST["vendorID"]);
    $DB->table = $DB->pre . "vendor_onboarding";
    $DB->data = array(
        "vendorStatus" => "Approved",
        "approvalStatus" => "Approved",
        "blockReason" => null,
        "blockedAt" => null
    );
    if ($DB->dbUpdate("vendorID=?", "i", array($vendorID))) {
        $MXRES = array("err" => 0, "msg" => "Vendor unblocked.");
    } else {
        $MXRES = array("err" => 1, "msg" => "Failed to unblock vendor.");
    }
}

function activatePortal() {
    global $DB, $MXRES;
    $vendorID = intval($_POST["vendorID"]);

    // Get vendor info
    $DB->sql = "SELECT vendorStatus, contactEmail, portalEnabled FROM " . $DB->pre . "vendor_onboarding WHERE vendorID = ?";
    $DB->vals = array($vendorID);
    $DB->types = "i";
    $vendor = $DB->dbRow();

    if (!$vendor) {
        $MXRES = array("err" => 1, "msg" => "Vendor not found");
        return;
    }
    if ($vendor["vendorStatus"] !== "Approved") {
        $MXRES = array("err" => 1, "msg" => "Only approved vendors can have portal access");
        return;
    }
    if ($vendor["portalEnabled"]) {
        $MXRES = array("err" => 1, "msg" => "Portal already enabled");
        return;
    }

    // Generate password
    $tempPassword = bin2hex(random_bytes(4));
    $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

    // Create or update portal user
    $DB->sql = "SELECT userID FROM " . $DB->pre . "vendor_portal_user WHERE vendorID = ?";
    $DB->vals = array($vendorID);
    $DB->types = "i";
    $existingUser = $DB->dbRow();

    try {
        $DB->table = $DB->pre . "vendor_portal_user";
        if ($existingUser) {
            $DB->data = array("passwordHash" => $passwordHash, "isActive" => 1);
            $DB->dbUpdate("vendorID=?", "i", array($vendorID));
        } else {
            $DB->data = array(
                "vendorID" => $vendorID,
                "username" => $vendor["contactEmail"],
                "passwordHash" => $passwordHash,
                "isActive" => 1
            );
            if (!$DB->dbInsert()) {
                $MXRES = array("err" => 1, "msg" => "Failed to create portal user. Email may already exist.");
                return;
            }
        }

        // Update vendor
        $DB->table = $DB->pre . "vendor_onboarding";
        $DB->data = array("portalEnabled" => 1, "portalActivatedAt" => date("Y-m-d H:i:s"));
        if ($DB->dbUpdate("vendorID=?", "i", array($vendorID))) {
            $MXRES = array("err" => 0, "msg" => "Portal activated! Temp password: " . $tempPassword);
        } else {
            $MXRES = array("err" => 1, "msg" => "Failed to update vendor portal status");
        }
    } catch (Exception $e) {
        $MXRES = array("err" => 1, "msg" => "Error: " . $e->getMessage());
    }
}

function resetPortalPassword() {
    global $DB, $MXRES;
    $vendorID = intval($_POST["vendorID"]);

    // Check if portal user exists
    $DB->sql = "SELECT userID, username FROM " . $DB->pre . "vendor_portal_user WHERE vendorID = ?";
    $DB->vals = array($vendorID);
    $DB->types = "i";
    $portalUser = $DB->dbRow();

    if (!$portalUser) {
        $MXRES = array("err" => 1, "msg" => "Portal user not found");
        return;
    }

    // Generate new password
    $tempPassword = bin2hex(random_bytes(4));
    $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

    $DB->table = $DB->pre . "vendor_portal_user";
    $DB->data = array("passwordHash" => $passwordHash);
    if ($DB->dbUpdate("vendorID=?", "i", array($vendorID))) {
        $MXRES = array("err" => 0, "msg" => "Password reset! New temp password: " . $tempPassword);
    } else {
        $MXRES = array("err" => 1, "msg" => "Failed to reset password");
    }
}

function deactivatePortal() {
    global $DB, $MXRES;
    $vendorID = intval($_POST["vendorID"]);

    // Deactivate portal user
    $DB->table = $DB->pre . "vendor_portal_user";
    $DB->data = array("isActive" => 0);
    $DB->dbUpdate("vendorID=?", "i", array($vendorID));

    // Update vendor record
    $DB->table = $DB->pre . "vendor_onboarding";
    $DB->data = array("portalEnabled" => 0);
    if ($DB->dbUpdate("vendorID=?", "i", array($vendorID))) {
        $MXRES = array("err" => 0, "msg" => "Portal access deactivated");
    } else {
        $MXRES = array("err" => 1, "msg" => "Failed to deactivate portal");
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true);
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addVendor(); break;
            case "UPDATE": updateVendor(); break;
            case "APPROVE": approveVendor(); break;
            case "DISAPPROVE": disapproveVendor(); break;
            case "BLOCK": blockVendor(); break;
            case "UNBLOCK": unblockVendor(); break;
            case "ACTIVATE_PORTAL": activatePortal(); break;
            case "RESET_PORTAL_PASSWORD": resetPortalPassword(); break;
            case "DEACTIVATE_PORTAL": deactivatePortal(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "vendor_onboarding", "PK" => "vendorID"));
}
?>

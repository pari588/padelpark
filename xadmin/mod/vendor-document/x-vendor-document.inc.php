<?php
/**
 * Vendor Document Module - Backend Logic
 */

function getDocumentStatusBadge($status) {
    $badges = array("Pending" => "badge-warning", "Verified" => "badge-success", "Rejected" => "badge-danger");
    $class = isset($badges[$status]) ? $badges[$status] : "badge-secondary";
    return '<span class="badge ' . $class . '">' . htmlentities($status) . '</span>';
}

function addDocument() {
    global $DB;
    $vendorID = intval($_POST["vendorID"] ?? 0);
    if (!$vendorID) {
        setResponse(array("err" => 1, "msg" => "Please select a vendor"));
        return;
    }

    $DB->table = $DB->pre . "vendor_document";
    $DB->data = array(
        "vendorID" => $vendorID,
        "documentType" => $_POST["documentType"] ?? "",
        "documentName" => $_POST["documentName"] ?? "",
        "documentNumber" => $_POST["documentNumber"] ?? "",
        "issueDate" => !empty($_POST["issueDate"]) ? $_POST["issueDate"] : null,
        "expiryDate" => !empty($_POST["expiryDate"]) ? $_POST["expiryDate"] : null,
        "notes" => $_POST["notes"] ?? "",
        "verificationStatus" => "Pending"
    );

    if ($DB->dbInsert()) {
        $documentID = $DB->insertID;
        setResponse(array("err" => 0, "msg" => "Document added!", "param" => "documentID=$documentID"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to add document."));
    }
}

function updateDocument() {
    global $DB;
    $documentID = intval($_POST["documentID"]);
    $DB->table = $DB->pre . "vendor_document";
    $DB->data = array(
        "documentType" => $_POST["documentType"] ?? "",
        "documentName" => $_POST["documentName"] ?? "",
        "documentNumber" => $_POST["documentNumber"] ?? "",
        "issueDate" => !empty($_POST["issueDate"]) ? $_POST["issueDate"] : null,
        "expiryDate" => !empty($_POST["expiryDate"]) ? $_POST["expiryDate"] : null,
        "notes" => $_POST["notes"] ?? ""
    );
    if ($DB->dbUpdate("documentID=?", "i", array($documentID))) {
        setResponse(array("err" => 0, "msg" => "Document updated!", "param" => "documentID=$documentID"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to update document."));
    }
}

function verifyDocument() {
    global $DB;
    $documentID = intval($_POST["documentID"]);
    $DB->table = $DB->pre . "vendor_document";
    $DB->data = array("verificationStatus" => "Verified", "verifiedAt" => date("Y-m-d H:i:s"));
    if ($DB->dbUpdate("documentID=?", "i", array($documentID))) {
        setResponse(array("err" => 0, "msg" => "Document verified!"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to verify document."));
    }
}

function rejectDocument() {
    global $DB;
    $documentID = intval($_POST["documentID"]);
    $reason = $_POST["rejectionReason"] ?? "";
    $DB->table = $DB->pre . "vendor_document";
    $DB->data = array("verificationStatus" => "Rejected", "rejectionReason" => $reason, "verifiedAt" => date("Y-m-d H:i:s"));
    if ($DB->dbUpdate("documentID=?", "i", array($documentID))) {
        setResponse(array("err" => 0, "msg" => "Document rejected."));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to reject document."));
    }
}

function deleteDocument() {
    global $DB;
    $documentID = intval($_POST["documentID"]);
    $DB->table = $DB->pre . "vendor_document";
    $DB->data = array("status" => 0);
    if ($DB->dbUpdate("documentID=?", "i", array($documentID))) {
        setResponse(array("err" => 0, "msg" => "Document deleted."));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to delete document."));
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addDocument(); break;
            case "UPDATE": updateDocument(); break;
            case "VERIFY": verifyDocument(); break;
            case "REJECT": rejectDocument(); break;
            case "DELETE": deleteDocument(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "vendor_document", "PK" => "documentID"));
}
?>

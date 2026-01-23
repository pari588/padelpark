<?php
/**
 * Vendor Quote Module - Backend Logic
 * Handles quote review, shortlisting, and award management
 */

function getQuoteStatusBadge($status) {
    $badges = array(
        "Draft" => "badge-secondary",
        "Submitted" => "badge-info",
        "Under Review" => "badge-warning",
        "Shortlisted" => "badge-primary",
        "Accepted" => "badge-success",
        "Rejected" => "badge-danger",
        "Expired" => "badge-dark"
    );
    $class = isset($badges[$status]) ? $badges[$status] : "badge-secondary";
    return '<span class="badge ' . $class . '">' . htmlentities($status) . '</span>';
}

function shortlistQuote() {
    global $DB;
    $quoteID = intval($_POST["quoteID"] ?? 0);
    $DB->table = $DB->pre . "vendor_quote";
    $DB->data = array("quoteStatus" => "Shortlisted");
    if ($DB->dbUpdate("quoteID=?", "i", array($quoteID))) {
        setResponse(array("err" => 0, "msg" => "Quote shortlisted!"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to shortlist quote."));
    }
}

function rejectQuote() {
    global $DB;
    $quoteID = intval($_POST["quoteID"] ?? 0);
    $reason = $_POST["rejectionReason"] ?? "";
    $DB->table = $DB->pre . "vendor_quote";
    $DB->data = array("quoteStatus" => "Rejected", "rejectionReason" => $reason);
    if ($DB->dbUpdate("quoteID=?", "i", array($quoteID))) {
        setResponse(array("err" => 0, "msg" => "Quote rejected."));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to reject quote."));
    }
}

function requestRevision() {
    global $DB;
    $quoteID = intval($_POST["quoteID"] ?? 0);
    $comments = $_POST["revisionComments"] ?? "";
    $DB->table = $DB->pre . "vendor_quote";
    $DB->data = array("quoteStatus" => "Under Review", "internalNotes" => $comments);
    if ($DB->dbUpdate("quoteID=?", "i", array($quoteID))) {
        setResponse(array("err" => 0, "msg" => "Revision requested."));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to request revision."));
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "SHORTLIST": shortlistQuote(); break;
            case "REJECT": rejectQuote(); break;
            case "REQUEST_REVISION": requestRevision(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "vendor_quote", "PK" => "quoteID"));
}
?>

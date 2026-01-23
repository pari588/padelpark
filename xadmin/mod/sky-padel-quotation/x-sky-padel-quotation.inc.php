<?php
/*
addQuotation = To save Quotation data with milestones.
updateQuotation = To update Quotation data with milestones.
saveQuotationMilestones = Save payment milestones for quotation.
sendQuotationEmail = Send quotation email to client.
*/

// Save quotation milestones
function saveQuotationMilestones($quotationID)
{
    global $DB;

    // Delete existing milestones
    $DB->vals = array($quotationID);
    $DB->types = "i";
    $DB->sql = "DELETE FROM " . $DB->pre . "sky_padel_quotation_milestone WHERE quotationID=?";
    $DB->dbQuery();

    // Insert new milestones
    if (isset($_POST["milestoneName"]) && is_array($_POST["milestoneName"])) {
        for ($i = 0; $i < count($_POST["milestoneName"]); $i++) {
            if (!empty($_POST["milestoneName"][$i])) {
                $DB->table = $DB->pre . "sky_padel_quotation_milestone";
                $DB->data = array(
                    "quotationID" => $quotationID,
                    "milestoneName" => $_POST["milestoneName"][$i],
                    "milestoneDescription" => $_POST["milestoneDescription"][$i] ?? "",
                    "paymentPercentage" => floatval($_POST["paymentPercentage"][$i] ?? 0),
                    "paymentAmount" => floatval($_POST["paymentAmount"][$i] ?? 0),
                    "dueAfterDays" => intval($_POST["dueAfterDays"][$i] ?? 0),
                    "sortOrder" => $i
                );
                $DB->dbInsert();
            }
        }
    }
}

function addQuotation()
{
    global $DB;
    if (isset($_POST["leadID"])) $_POST["leadID"] = intval($_POST["leadID"]);
    if (isset($_POST["reportID"])) $_POST["reportID"] = intval($_POST["reportID"]);
    if (isset($_POST["subtotal"])) $_POST["subtotal"] = floatval($_POST["subtotal"]);
    if (isset($_POST["taxAmount"])) $_POST["taxAmount"] = floatval($_POST["taxAmount"]);
    if (isset($_POST["totalAmount"])) $_POST["totalAmount"] = floatval($_POST["totalAmount"]);
    if (isset($_POST["validUntil"])) $_POST["validUntil"] = $_POST["validUntil"];

    // Handle revision fields
    if (isset($_POST["parentQuotationID"])) $_POST["parentQuotationID"] = intval($_POST["parentQuotationID"]);
    if (isset($_POST["revisionNumber"])) $_POST["revisionNumber"] = intval($_POST["revisionNumber"]);

    // If this is a revision, mark old revisions as not latest
    if (!empty($_POST["parentQuotationID"])) {
        $parentID = $_POST["parentQuotationID"];
        $DB->vals = array(0, $parentID, $parentID);
        $DB->types = "iii";
        $DB->sql = "UPDATE " . $DB->pre . "sky_padel_quotation SET isLatestRevision=0 WHERE parentQuotationID=? OR quotationID=?";
        $DB->dbQuery();
        $_POST["isLatestRevision"] = 1;
    }

    $DB->table = $DB->pre . "sky_padel_quotation";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $quotationID = $DB->insertID;

        // Save milestones
        saveQuotationMilestones($quotationID);

        // Update lead status based on quotation status
        $leadStatus = "Quotation Sent";
        if (!empty($_POST["parentQuotationID"])) {
            $leadStatus = "Revision in Progress";
        }
        $DB->vals = array($leadStatus, $_POST["leadID"]);
        $DB->types = "si";
        $DB->sql = "UPDATE " . $DB->pre . "sky_padel_lead SET leadStatus=? WHERE leadID=?";
        $DB->dbQuery();

        // Send email to client (if quotationStatus is Sent)
        if ($_POST["quotationStatus"] == "Sent") {
            sendQuotationEmail($quotationID);
        }

        setResponse(array("err" => 0, "param" => "id=$quotationID"));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateQuotation()
{
    global $DB;
    $quotationID = intval($_POST["quotationID"]);
    if (isset($_POST["leadID"])) $_POST["leadID"] = intval($_POST["leadID"]);
    if (isset($_POST["reportID"])) $_POST["reportID"] = intval($_POST["reportID"]);
    if (isset($_POST["subtotal"])) $_POST["subtotal"] = floatval($_POST["subtotal"]);
    if (isset($_POST["taxAmount"])) $_POST["taxAmount"] = floatval($_POST["taxAmount"]);
    if (isset($_POST["totalAmount"])) $_POST["totalAmount"] = floatval($_POST["totalAmount"]);

    $oldStatus = "";
    $DB->vals = array($quotationID);
    $DB->types = "i";
    $DB->sql = "SELECT quotationStatus, leadID, proformaGenerated FROM " . $DB->pre . "sky_padel_quotation WHERE quotationID=?";
    $oldData = $DB->dbRow();
    $oldStatus = $oldData["quotationStatus"] ?? "";
    $proformaAlreadyGenerated = $oldData["proformaGenerated"] ?? 0;

    $DB->table = $DB->pre . "sky_padel_quotation";
    $DB->data = $_POST;
    if ($DB->dbUpdate("quotationID=?", "i", array($quotationID))) {
        // Save milestones
        saveQuotationMilestones($quotationID);

        // Update lead status based on quotation status
        $leadID = $oldData["leadID"] ?? $_POST["leadID"];

        if ($_POST["quotationStatus"] == "Approved" && $oldStatus != "Approved") {
            // Update lead status
            $DB->vals = array("Quotation Approved", $leadID);
            $DB->types = "si";
            $DB->sql = "UPDATE " . $DB->pre . "sky_padel_lead SET leadStatus=? WHERE leadID=?";
            $DB->dbQuery();

            // Auto-generate proforma invoice if not already generated
            if (!$proformaAlreadyGenerated) {
                try {
                    $proformaFile = __DIR__ . "/../sky-padel-proforma/x-sky-padel-proforma.inc.php";
                    if (file_exists($proformaFile)) {
                        require_once($proformaFile);
                        if (function_exists('generateProformaFromQuotation')) {
                            $proformaID = generateProformaFromQuotation($quotationID);
                            if ($proformaID > 0) {
                                // Update quotation with proforma reference
                                $DB->vals = array(1, $proformaID, $quotationID);
                                $DB->types = "iii";
                                $DB->sql = "UPDATE " . $DB->pre . "sky_padel_quotation SET proformaGenerated=?, proformaID=? WHERE quotationID=?";
                                $DB->dbQuery();
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Log error but don't fail the whole update
                    error_log("Proforma generation error: " . $e->getMessage());
                }
            }
        } elseif ($_POST["quotationStatus"] == "Rejected" && $oldStatus != "Rejected") {
            $DB->vals = array("Quotation Rejected", $leadID);
            $DB->types = "si";
            $DB->sql = "UPDATE " . $DB->pre . "sky_padel_lead SET leadStatus=? WHERE leadID=?";
            $DB->dbQuery();
        } elseif ($_POST["quotationStatus"] == "Sent" && $oldStatus != "Sent") {
            sendQuotationEmail($quotationID);
        }

        setResponse(array("err" => 0, "param" => "id=$quotationID"));
    } else {
        setResponse(array("err" => 1));
    }
}

function sendQuotationEmail($quotationID)
{
    global $DB;
    // Get quotation details
    $DB->vals = array($quotationID);
    $DB->types = "i";
    $DB->sql = "SELECT q.*, l.clientName, l.clientEmail, l.clientPhone
                FROM " . $DB->pre . "sky_padel_quotation q
                LEFT JOIN " . $DB->pre . "sky_padel_lead l ON q.leadID=l.leadID
                WHERE q.quotationID=?";
    $q = $DB->dbRow();

    if (!empty($q["clientEmail"])) {
        $to = $q["clientEmail"];
        $subject = "Sky Padel - Quotation #" . $q["quotationNo"];
        $message = "Dear " . $q["clientName"] . ",\n\n";
        $message .= "Please find attached your quotation for Sky Padel court installation.\n\n";
        $message .= "Quotation No: " . $q["quotationNo"] . "\n";
        $message .= "Date: " . date("d-M-Y", strtotime($q["quotationDate"])) . "\n";
        $message .= "Valid Until: " . date("d-M-Y", strtotime($q["validUntil"])) . "\n";
        $message .= "Total Amount: ₹" . number_format($q["totalAmount"], 2) . "\n\n";
        $message .= "To approve or reject this quotation, please click the link below:\n";
        $message .= SITEURL . "/quotation-approval?id=" . $quotationID . "\n\n";
        $message .= "Thank you for choosing Sky Padel.\n\n";
        $message .= "Best regards,\nSky Padel Team";

        // Send email using existing mail function
        if (function_exists('sendMail')) {
            sendMail($to, $subject, $message);
        }
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addQuotation(); break;
            case "UPDATE": updateQuotation(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "sky_padel_quotation", "PK" => "quotationID"));
}
?>

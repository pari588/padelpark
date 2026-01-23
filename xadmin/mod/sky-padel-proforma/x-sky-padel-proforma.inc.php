<?php
/*
addProforma = To save Proforma Invoice data.
updateProforma = To update Proforma Invoice data.
generateProformaFromQuotation = Auto-generate proforma when quotation is approved.
generateProformaNo = Generate unique proforma number (PI-YYYYMMDD-XXX).
copyQuotationItemsToProforma = Copy line items from quotation to proforma.
copyQuotationMilestonesToProforma = Copy payment milestones from quotation to proforma.
*/

// Generate unique proforma number
function generateProformaNo()
{
    global $DB;
    $prefix = "PI-" . date("Ymd") . "-";
    $DB->sql = "SELECT proformaNo FROM " . $DB->pre . "sky_padel_proforma_invoice
                WHERE proformaNo LIKE '" . $prefix . "%'
                ORDER BY proformaNo DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['proformaNo'], -3));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 3, "0", STR_PAD_LEFT);
}

// Convert amount to words (Indian numbering system)
function convertAmountToWords($amount)
{
    $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');

    $amount = round($amount, 2);
    $rupees = floor($amount);
    $paise = round(($amount - $rupees) * 100);

    if ($rupees == 0) return "Zero Rupees Only";

    $words = '';

    // Crores
    if ($rupees >= 10000000) {
        $words .= convertAmountToWords(floor($rupees / 10000000)) . ' Crore ';
        $rupees %= 10000000;
    }
    // Lakhs
    if ($rupees >= 100000) {
        $words .= convertAmountToWords(floor($rupees / 100000)) . ' Lakh ';
        $rupees %= 100000;
    }
    // Thousands
    if ($rupees >= 1000) {
        $words .= convertAmountToWords(floor($rupees / 1000)) . ' Thousand ';
        $rupees %= 1000;
    }
    // Hundreds
    if ($rupees >= 100) {
        $words .= $ones[floor($rupees / 100)] . ' Hundred ';
        $rupees %= 100;
    }
    // Tens and Ones
    if ($rupees >= 20) {
        $words .= $tens[floor($rupees / 10)] . ' ';
        $rupees %= 10;
    }
    if ($rupees > 0) {
        $words .= $ones[$rupees] . ' ';
    }

    $words = trim($words) . ' Rupees';
    if ($paise > 0) {
        $words .= ' and ' . $ones[$paise] . ' Paise';
    }
    $words .= ' Only';

    return $words;
}

// Copy quotation items to proforma (if quotation_item table exists)
function copyQuotationItemsToProforma($quotationID, $proformaID)
{
    global $DB;
    // Check if quotation_item table exists
    $DB->sql = "SHOW TABLES LIKE '" . $DB->pre . "sky_padel_quotation_item'";
    $DB->dbQuery();
    if ($DB->numRows == 0) {
        return; // Table doesn't exist, skip
    }

    $DB->vals = array($quotationID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_quotation_item WHERE quotationID=?";
    $items = $DB->dbRows();

    foreach ($items as $item) {
        $DB->table = $DB->pre . "sky_padel_proforma_item";
        $DB->data = array(
            "proformaID" => $proformaID,
            "itemDescription" => $item["itemDescription"],
            "quantity" => $item["quantity"],
            "unitPrice" => $item["unitPrice"],
            "totalPrice" => $item["totalPrice"],
            "sortOrder" => $item["sortOrder"]
        );
        $DB->dbInsert();
    }
}

// Copy quotation milestones to proforma
function copyQuotationMilestonesToProforma($quotationID, $proformaID)
{
    global $DB;
    $DB->vals = array($quotationID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_quotation_milestone WHERE quotationID=? ORDER BY sortOrder";
    $milestones = $DB->dbRows();

    foreach ($milestones as $m) {
        $DB->table = $DB->pre . "sky_padel_proforma_milestone";
        $DB->data = array(
            "proformaID" => $proformaID,
            "milestoneName" => $m["milestoneName"],
            "milestoneDescription" => $m["milestoneDescription"] ?? "",
            "paymentPercentage" => $m["paymentPercentage"],
            "paymentAmount" => $m["paymentAmount"],
            "dueAfterDays" => $m["dueAfterDays"],
            "sortOrder" => $m["sortOrder"]
        );
        $DB->dbInsert();
    }
}

// Generate proforma invoice from approved quotation
function generateProformaFromQuotation($quotationID)
{
    global $DB;

    // Get quotation details with lead info
    $DB->vals = array($quotationID);
    $DB->types = "i";
    $DB->sql = "SELECT q.*, l.clientName, l.clientEmail, l.clientPhone, l.clientCompany,
                       l.siteAddress, l.siteCity, l.siteState, l.pincode as sitePincode
                FROM " . $DB->pre . "sky_padel_quotation q
                LEFT JOIN " . $DB->pre . "sky_padel_lead l ON q.leadID = l.leadID
                WHERE q.quotationID=?";
    $q = $DB->dbRow();

    if (!$q) return 0;

    // Generate proforma number
    $proformaNo = generateProformaNo();

    // Calculate tax amounts (assuming 18% GST split as 9% CGST + 9% SGST)
    $subtotal = floatval($q["subtotal"] ?? $q["totalAmount"]);
    $taxAmount = floatval($q["taxAmount"] ?? 0);
    $cgstAmount = $taxAmount / 2;
    $sgstAmount = $taxAmount / 2;
    $totalAmount = floatval($q["totalAmount"]);

    // Create proforma invoice
    $proformaData = array(
        "proformaNo" => $proformaNo,
        "quotationID" => $quotationID,
        "leadID" => $q["leadID"],
        "clientName" => $q["clientName"],
        "clientEmail" => $q["clientEmail"],
        "clientPhone" => $q["clientPhone"],
        "clientCompany" => $q["clientCompany"] ?? "",
        "clientAddress" => $q["siteAddress"] ?? "",
        "clientCity" => $q["siteCity"] ?? "",
        "clientState" => $q["siteState"] ?? "",
        "clientPincode" => $q["sitePincode"] ?? "",
        "invoiceDate" => date("Y-m-d"),
        "validUntil" => date("Y-m-d", strtotime("+30 days")),
        "courtConfiguration" => $q["courtConfiguration"] ?? "",
        "scopeOfWork" => $q["scopeOfWork"] ?? "",
        "subtotal" => $subtotal,
        "taxableAmount" => $subtotal,
        "cgstRate" => 9,
        "cgstAmount" => $cgstAmount,
        "sgstRate" => 9,
        "sgstAmount" => $sgstAmount,
        "totalTaxAmount" => $taxAmount,
        "totalAmount" => $totalAmount,
        "amountInWords" => convertAmountToWords($totalAmount),
        "paymentTerms" => $q["terms"] ?? "",
        "termsAndConditions" => $q["terms"] ?? "",
        "invoiceStatus" => "Generated",
        "generatedBy" => $_SESSION[SITEURL]["userID"] ?? 0,
        "status" => 1
    );

    $DB->table = $DB->pre . "sky_padel_proforma_invoice";
    $DB->data = $proformaData;

    if ($DB->dbInsert()) {
        $proformaID = $DB->insertID;

        // Copy quotation items to proforma
        copyQuotationItemsToProforma($quotationID, $proformaID);

        // Copy quotation milestones to proforma
        copyQuotationMilestonesToProforma($quotationID, $proformaID);

        return $proformaID;
    }

    return 0;
}

// Add new proforma invoice
function addProforma()
{
    global $DB;

    if (isset($_POST["quotationID"])) $_POST["quotationID"] = intval($_POST["quotationID"]);
    if (isset($_POST["leadID"])) $_POST["leadID"] = intval($_POST["leadID"]);
    if (isset($_POST["subtotal"])) $_POST["subtotal"] = floatval($_POST["subtotal"]);
    if (isset($_POST["totalAmount"])) $_POST["totalAmount"] = floatval($_POST["totalAmount"]);
    if (isset($_POST["cgstAmount"])) $_POST["cgstAmount"] = floatval($_POST["cgstAmount"]);
    if (isset($_POST["sgstAmount"])) $_POST["sgstAmount"] = floatval($_POST["sgstAmount"]);

    // Generate proforma number if not provided
    if (empty($_POST["proformaNo"])) {
        $_POST["proformaNo"] = generateProformaNo();
    }

    // Calculate amount in words
    if (!empty($_POST["totalAmount"])) {
        $_POST["amountInWords"] = convertAmountToWords($_POST["totalAmount"]);
    }

    $_POST["generatedBy"] = $_SESSION[SITEURL]["userID"] ?? 0;

    $DB->table = $DB->pre . "sky_padel_proforma_invoice";
    $DB->data = $_POST;

    if ($DB->dbInsert()) {
        $proformaID = $DB->insertID;

        // Save milestones
        if (isset($_POST["milestoneName"]) && is_array($_POST["milestoneName"])) {
            for ($i = 0; $i < count($_POST["milestoneName"]); $i++) {
                if (!empty($_POST["milestoneName"][$i])) {
                    $DB->table = $DB->pre . "sky_padel_proforma_milestone";
                    $DB->data = array(
                        "proformaID" => $proformaID,
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

        setResponse(array("err" => 0, "param" => "id=$proformaID"));
    } else {
        setResponse(array("err" => 1));
    }
}

// Update proforma invoice
function updateProforma()
{
    global $DB;
    $proformaID = intval($_POST["proformaID"]);

    if (isset($_POST["quotationID"])) $_POST["quotationID"] = intval($_POST["quotationID"]);
    if (isset($_POST["leadID"])) $_POST["leadID"] = intval($_POST["leadID"]);
    if (isset($_POST["subtotal"])) $_POST["subtotal"] = floatval($_POST["subtotal"]);
    if (isset($_POST["totalAmount"])) $_POST["totalAmount"] = floatval($_POST["totalAmount"]);
    if (isset($_POST["cgstAmount"])) $_POST["cgstAmount"] = floatval($_POST["cgstAmount"]);
    if (isset($_POST["sgstAmount"])) $_POST["sgstAmount"] = floatval($_POST["sgstAmount"]);

    // Calculate amount in words
    if (!empty($_POST["totalAmount"])) {
        $_POST["amountInWords"] = convertAmountToWords($_POST["totalAmount"]);
    }

    $DB->table = $DB->pre . "sky_padel_proforma_invoice";
    $DB->data = $_POST;

    if ($DB->dbUpdate("proformaID=?", "i", array($proformaID))) {
        // Delete existing milestones and re-insert
        $DB->vals = array($proformaID);
        $DB->types = "i";
        $DB->sql = "DELETE FROM " . $DB->pre . "sky_padel_proforma_milestone WHERE proformaID=?";
        $DB->dbQuery();

        // Save milestones
        if (isset($_POST["milestoneName"]) && is_array($_POST["milestoneName"])) {
            for ($i = 0; $i < count($_POST["milestoneName"]); $i++) {
                if (!empty($_POST["milestoneName"][$i])) {
                    $DB->table = $DB->pre . "sky_padel_proforma_milestone";
                    $DB->data = array(
                        "proformaID" => $proformaID,
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

        setResponse(array("err" => 0, "param" => "id=$proformaID"));
    } else {
        setResponse(array("err" => 1));
    }
}

// Send proforma email to client
function sendProformaEmail($proformaID)
{
    global $DB;

    $DB->vals = array($proformaID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_proforma_invoice WHERE proformaID=?";
    $p = $DB->dbRow();

    if (!empty($p["clientEmail"])) {
        $to = $p["clientEmail"];
        $subject = "Sky Padel - Proforma Invoice #" . $p["proformaNo"];
        $message = "Dear " . $p["clientName"] . ",\n\n";
        $message .= "Please find attached your Proforma Invoice for Sky Padel court installation.\n\n";
        $message .= "Proforma Invoice No: " . $p["proformaNo"] . "\n";
        $message .= "Date: " . date("d-M-Y", strtotime($p["invoiceDate"])) . "\n";
        $message .= "Valid Until: " . date("d-M-Y", strtotime($p["validUntil"])) . "\n";
        $message .= "Total Amount: ₹" . number_format($p["totalAmount"], 2) . "\n";
        $message .= "Amount in Words: " . $p["amountInWords"] . "\n\n";
        $message .= "Thank you for choosing Sky Padel.\n\n";
        $message .= "Best regards,\nSky Padel Team";

        if (function_exists('sendMail')) {
            sendMail($to, $subject, $message);
        }

        // Update sent date and status
        $DB->vals = array("Sent", date("Y-m-d H:i:s"), $proformaID);
        $DB->types = "ssi";
        $DB->sql = "UPDATE " . $DB->pre . "sky_padel_proforma_invoice SET invoiceStatus=?, sentDate=? WHERE proformaID=?";
        $DB->dbQuery();

        setResponse(array("err" => 0, "msg" => "Email sent successfully"));
    } else {
        setResponse(array("err" => 1, "msg" => "No email address found"));
    }
}

// Check if this is an AJAX action call specifically for proforma
$isProformaAction = isset($_POST["xAction"]) &&
                    isset($_POST["modName"]) &&
                    $_POST["modName"] === "sky-padel-proforma";

if ($isProformaAction) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addProforma();
                break;
            case "UPDATE":
                updateProforma();
                break;
            case "SEND_EMAIL":
                sendProformaEmail(intval($_POST["proformaID"]));
                break;
        }
    }
    echo json_encode($MXRES);
} else if (!isset($_POST["xAction"])) {
    // Set module vars when loading pages (not during AJAX calls)
    if (function_exists("setModVars")) setModVars(array("TBL" => "sky_padel_proforma_invoice", "PK" => "proformaID"));
}

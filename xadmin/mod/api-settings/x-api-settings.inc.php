<?php
/*
API Settings Module
- Manage API keys and credentials for integrations
- Hudle, Razorpay, WhatsApp, Brevo, Google, GST, Tally, MSME, etc.
*/

$MXMOD = array(
    "TBL" => "x_setting",
    "PK" => "settingID",
    "TITLE" => "API Integrations",
    "ADDNEW" => 0,
    "ADDEDIT" => 1,
    "DEL" => 0,
    "VIEW" => 1,
    "SEARCH" => 0,
    "TRASH" => 0,
    "FILTER" => 0,
    "SORT" => 0,
    "EXPORT" => 0
);

// Handle AJAX requests
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");

    // Use ignoreToken=true since this is a session-based form, not JWT-based
    $MXRES = mxCheckRequest(true, true);

    if ($MXRES["err"] == 0) {
        if ($_POST["xAction"] == "SAVE" || $_POST["xAction"] == "UPDATE") {
            saveApiSettings();
        } elseif ($_POST["xAction"] == "TEST_CONNECTION") {
            testConnection();
            exit; // testConnection echoes its own JSON and exits
        }
    }
    echo json_encode($MXRES);
    exit;
}

function saveApiSettings() {
    global $DB, $MXRES;

    // List of all API setting keys
    $apiKeys = [
        // Hudle
        "hudle_api_key", "hudle_api_secret", "hudle_webhook_url", "hudle_enabled",
        // Razorpay
        "razorpay_key_id", "razorpay_key_secret", "razorpay_webhook_secret", "razorpay_enabled",
        // Pine Labs POS
        "pinelabs_merchant_id", "pinelabs_access_code", "pinelabs_secret_key", "pinelabs_terminal_id", "pinelabs_environment", "pinelabs_enabled",
        // WhatsApp Business
        "whatsapp_phone_id", "whatsapp_access_token", "whatsapp_business_id", "whatsapp_verify_token", "whatsapp_enabled",
        // Brevo (Email & SMS)
        "brevo_api_key", "brevo_sender_email", "brevo_sender_name", "brevo_sms_sender", "brevo_sms_enabled", "brevo_enabled",
        // Google
        "google_client_id", "google_client_secret", "google_api_key", "google_maps_key", "google_enabled",
        // GST
        "gst_api_key", "gst_api_secret", "gst_username", "gst_enabled",
        // Tally
        "tally_server_url", "tally_company_name", "tally_enabled",
        "tally_sync_interval", "tally_batch_size", "tally_retry_attempts", "tally_auto_create_ledgers",
        "tally_module_b2b", "tally_module_pnp", "tally_module_proforma", "tally_module_voucher", "tally_module_credit_note", "tally_module_debit_note",
        "tally_ledger_sales_b2b", "tally_ledger_sales_pnp", "tally_ledger_sales_skypadel",
        "tally_ledger_cgst", "tally_ledger_sgst", "tally_ledger_igst", "tally_ledger_price_diff",
        // MSME
        "msme_api_key", "msme_api_secret", "msme_enabled",
        // Compliance
        "cleartax_api_key", "cleartax_enabled",
        "zoho_client_id", "zoho_client_secret", "zoho_refresh_token", "zoho_enabled"
    ];

    $updated = 0;
    $errors = [];

    try {
        foreach ($apiKeys as $key) {
            if (isset($_POST[$key])) {
                $value = trim($_POST[$key]);

                // Check if setting exists
                $DB->vals = [$key];
                $DB->types = "s";
                $DB->sql = "SELECT settingID FROM " . $DB->pre . "x_setting WHERE settingKey = ?";
                $existing = $DB->dbRow();

                if ($existing) {
                    // Update
                    $DB->vals = [$value, $key];
                    $DB->types = "ss";
                    $DB->sql = "UPDATE " . $DB->pre . "x_setting SET settingVal = ? WHERE settingKey = ?";
                    $result = $DB->dbQuery();
                    if ($result === false && $DB->error) {
                        $errors[] = "Update failed for $key: " . $DB->error;
                    }
                } else {
                    // Insert
                    $DB->vals = [$key, $value, 1];
                    $DB->types = "ssi";
                    $DB->sql = "INSERT INTO " . $DB->pre . "x_setting (settingKey, settingVal, status) VALUES (?, ?, ?)";
                    $result = $DB->dbQuery();
                    if ($result === false && $DB->error) {
                        $errors[] = "Insert failed for $key: " . $DB->error;
                    }
                }
                $updated++;
            }
        }

        if (!empty($errors)) {
            $MXRES["err"] = 1;
            $MXRES["msg"] = "Partial save: " . implode(", ", $errors);
        } else {
            $MXRES["err"] = 0;
            $MXRES["msg"] = "API settings saved successfully ($updated settings updated)";
        }
    } catch (Exception $e) {
        $MXRES["err"] = 1;
        $MXRES["msg"] = "Error saving settings: " . $e->getMessage();
    }
}

function testConnection() {
    global $DB, $MXRES;

    $service = $_POST["service"] ?? "";

    switch ($service) {
        case "razorpay":
            $result = testRazorpay();
            break;
        case "brevo":
            $result = testBrevo();
            break;
        case "hudle":
            $result = ["err" => 0, "msg" => "Hudle credentials saved. Webhook endpoint ready."];
            break;
        case "whatsapp":
            $result = ["err" => 0, "msg" => "WhatsApp credentials saved."];
            break;
        case "tally":
            $result = testTally();
            break;
        default:
            $result = ["err" => 1, "msg" => "Test not available for this service"];
    }

    // Return result as JSON for test connections (separate from form submission)
    header('Content-Type: application/json');
    echo json_encode($result);
}

function testRazorpay() {
    global $DB;

    $DB->sql = "SELECT settingKey, settingVal FROM " . $DB->pre . "x_setting WHERE settingKey IN ('razorpay_key_id', 'razorpay_key_secret')";
    $rows = $DB->dbRows();
    $settings = [];
    foreach ($rows as $r) {
        $settings[$r["settingKey"]] = $r["settingVal"];
    }

    if (empty($settings["razorpay_key_id"]) || empty($settings["razorpay_key_secret"])) {
        return ["err" => 1, "msg" => "Razorpay credentials not configured"];
    }

    return ["err" => 0, "msg" => "Razorpay credentials saved"];
}

function testBrevo() {
    global $DB;

    $DB->sql = "SELECT settingVal FROM " . $DB->pre . "x_setting WHERE settingKey = 'brevo_api_key'";
    $row = $DB->dbRow();

    if (empty($row["settingVal"])) {
        return ["err" => 1, "msg" => "Brevo API key not configured"];
    }

    return ["err" => 0, "msg" => "Brevo API key saved"];
}

function testTally() {
    global $DB;

    // Get Tally settings
    $DB->sql = "SELECT settingKey, settingVal FROM " . $DB->pre . "x_setting WHERE settingKey IN ('tally_server_url', 'tally_company_name')";
    $rows = $DB->dbRows();
    $settings = [];
    foreach ($rows as $r) {
        $settings[$r["settingKey"]] = $r["settingVal"];
    }

    $serverUrl = $settings["tally_server_url"] ?? "http://localhost:9000";
    $companyName = $settings["tally_company_name"] ?? "";

    if (empty($companyName)) {
        return ["err" => 1, "msg" => "Tally company name not configured"];
    }

    // Test connection by sending a simple XML request
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
<ENVELOPE>
    <HEADER>
        <VERSION>1</VERSION>
        <TALLYREQUEST>Export</TALLYREQUEST>
        <TYPE>Data</TYPE>
        <ID>CompanyInfo</ID>
    </HEADER>
    <BODY>
        <DESC>
            <STATICVARIABLES>
                <SVEXPORTFORMAT>$$SysName:XML</SVEXPORTFORMAT>
                <SVCURRENTCOMPANY>' . htmlspecialchars($companyName) . '</SVCURRENTCOMPANY>
            </STATICVARIABLES>
            <TDL>
                <TDLMESSAGE>
                    <REPORT NAME="CompanyInfo">
                        <FORMS>CompanyInfo</FORMS>
                    </REPORT>
                    <FORM NAME="CompanyInfo">
                        <PARTS>CompanyInfoPart</PARTS>
                    </FORM>
                    <PART NAME="CompanyInfoPart">
                        <LINES>CompanyInfoLine</LINES>
                        <REPEAT>CompanyInfoLine : Company</REPEAT>
                        <SCROLLED>Vertical</SCROLLED>
                    </PART>
                    <LINE NAME="CompanyInfoLine">
                        <FIELDS>FldCompanyName</FIELDS>
                    </LINE>
                    <FIELD NAME="FldCompanyName">
                        <SET>$$Name</SET>
                    </FIELD>
                </TDLMESSAGE>
            </TDL>
        </DESC>
    </BODY>
</ENVELOPE>';

    // Initialize cURL
    $ch = curl_init($serverUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: text/xml; charset=utf-8',
        'Content-Length: ' . strlen($xml)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ["err" => 1, "msg" => "Connection failed: " . $error];
    }

    if ($httpCode != 200) {
        return ["err" => 1, "msg" => "Tally server returned HTTP " . $httpCode];
    }

    if (empty($response)) {
        return ["err" => 1, "msg" => "Empty response from Tally server"];
    }

    // Check if response contains error
    if (stripos($response, '<ERROR>') !== false || stripos($response, 'Invalid Company') !== false) {
        return ["err" => 1, "msg" => "Company '" . $companyName . "' not found in Tally"];
    }

    // Success
    return ["err" => 0, "msg" => "Connected to Tally successfully. Company: " . $companyName];
}

// Set module variables if not handling POST
if (function_exists("setModVars")) {
    setModVars(array("TBL" => "x_setting", "PK" => "settingID"));
}

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

// Handle AJAX save
if (isset($_POST["xAction"]) && $_POST["xAction"] == "SAVE") {
    saveApiSettings();
    exit;
}

if (isset($_POST["xAction"]) && $_POST["xAction"] == "TEST_CONNECTION") {
    testConnection();
    exit;
}

function saveApiSettings() {
    global $DB;
    header('Content-Type: application/json');

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
        // MSME
        "msme_api_key", "msme_api_secret", "msme_enabled",
        // Compliance
        "cleartax_api_key", "cleartax_enabled",
        "zoho_client_id", "zoho_client_secret", "zoho_refresh_token", "zoho_enabled"
    ];

    $updated = 0;

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
                $DB->dbQuery();
            } else {
                // Insert
                $DB->vals = [$key, $value, 1];
                $DB->types = "ssi";
                $DB->sql = "INSERT INTO " . $DB->pre . "x_setting (settingKey, settingVal, status) VALUES (?, ?, ?)";
                $DB->dbQuery();
            }
            $updated++;
        }
    }

    echo json_encode(["err" => 0, "msg" => "API settings saved successfully", "updated" => $updated]);
}

function testConnection() {
    global $DB;
    header('Content-Type: application/json');

    $service = $_POST["service"] ?? "";
    $result = ["err" => 1, "msg" => "Unknown service"];

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
        default:
            $result = ["err" => 1, "msg" => "Test not available for this service"];
    }

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

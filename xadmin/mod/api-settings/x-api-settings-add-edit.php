<?php
// Get all API settings
$DB->sql = "SELECT settingKey, settingVal FROM " . $DB->pre . "x_setting WHERE settingKey LIKE '%api%' OR settingKey LIKE '%key%' OR settingKey LIKE '%secret%' OR settingKey LIKE '%token%' OR settingKey LIKE '%hudle%' OR settingKey LIKE '%razorpay%' OR settingKey LIKE '%pinelabs%' OR settingKey LIKE '%whatsapp%' OR settingKey LIKE '%brevo%' OR settingKey LIKE '%google%' OR settingKey LIKE '%gst%' OR settingKey LIKE '%tally%' OR settingKey LIKE '%msme%' OR settingKey LIKE '%cleartax%' OR settingKey LIKE '%zoho%' OR settingKey LIKE '%sms%' OR settingKey LIKE '%enabled%'";
$rows = $DB->dbRows();
$S = [];
foreach ($rows as $r) {
    $S[$r["settingKey"]] = $r["settingVal"];
}

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post">
        <input type="hidden" name="xAction" value="SAVE">

        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%" valign="top" style="padding-right:15px;">

                    <!-- Hudle Integration -->
                    <h2 class="form-head">Hudle Integration (Court Bookings)</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">API Key</td>
                            <td><input type="text" name="hudle_api_key" value="<?php echo htmlspecialchars($S["hudle_api_key"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>API Secret</td>
                            <td><input type="password" name="hudle_api_secret" value="<?php echo htmlspecialchars($S["hudle_api_secret"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Webhook URL</td>
                            <td><input type="text" name="hudle_webhook_url" value="<?php echo SITEURL; ?>/api/hudle-webhook.php" class="inp1" style="width:95%;" readonly></td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="hudle_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["hudle_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["hudle_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                                <button type="button" onclick="testConnection('hudle')" class="btn">Test</button>
                            </td>
                        </tr>
                    </table>

                    <!-- Razorpay -->
                    <h2 class="form-head">Razorpay (Payments)</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">Key ID</td>
                            <td><input type="text" name="razorpay_key_id" value="<?php echo htmlspecialchars($S["razorpay_key_id"] ?? ""); ?>" class="inp1" style="width:95%;" placeholder="rzp_live_xxxxx"></td>
                        </tr>
                        <tr>
                            <td>Key Secret</td>
                            <td><input type="password" name="razorpay_key_secret" value="<?php echo htmlspecialchars($S["razorpay_key_secret"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Webhook Secret</td>
                            <td><input type="password" name="razorpay_webhook_secret" value="<?php echo htmlspecialchars($S["razorpay_webhook_secret"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="razorpay_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["razorpay_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["razorpay_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                                <button type="button" onclick="testConnection('razorpay')" class="btn">Test</button>
                            </td>
                        </tr>
                    </table>

                    <!-- Pine Labs POS -->
                    <h2 class="form-head">Pine Labs POS</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">Merchant ID</td>
                            <td><input type="text" name="pinelabs_merchant_id" value="<?php echo htmlspecialchars($S["pinelabs_merchant_id"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Access Code</td>
                            <td><input type="password" name="pinelabs_access_code" value="<?php echo htmlspecialchars($S["pinelabs_access_code"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Secret Key</td>
                            <td><input type="password" name="pinelabs_secret_key" value="<?php echo htmlspecialchars($S["pinelabs_secret_key"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Terminal ID</td>
                            <td><input type="text" name="pinelabs_terminal_id" value="<?php echo htmlspecialchars($S["pinelabs_terminal_id"] ?? ""); ?>" class="inp1" style="width:200px;" placeholder="For physical terminal"></td>
                        </tr>
                        <tr>
                            <td>Environment</td>
                            <td>
                                <select name="pinelabs_environment" class="inp1">
                                    <option value="sandbox" <?php echo ($S["pinelabs_environment"] ?? "sandbox") == "sandbox" ? "selected" : ""; ?>>Sandbox</option>
                                    <option value="production" <?php echo ($S["pinelabs_environment"] ?? "") == "production" ? "selected" : ""; ?>>Production</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="pinelabs_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["pinelabs_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["pinelabs_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <!-- WhatsApp Business -->
                    <h2 class="form-head">WhatsApp Business API</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">Phone Number ID</td>
                            <td><input type="text" name="whatsapp_phone_id" value="<?php echo htmlspecialchars($S["whatsapp_phone_id"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Access Token</td>
                            <td><input type="password" name="whatsapp_access_token" value="<?php echo htmlspecialchars($S["whatsapp_access_token"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Business Account ID</td>
                            <td><input type="text" name="whatsapp_business_id" value="<?php echo htmlspecialchars($S["whatsapp_business_id"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Webhook Verify Token</td>
                            <td><input type="text" name="whatsapp_verify_token" value="<?php echo htmlspecialchars($S["whatsapp_verify_token"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="whatsapp_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["whatsapp_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["whatsapp_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                                <button type="button" onclick="testConnection('whatsapp')" class="btn">Test</button>
                            </td>
                        </tr>
                    </table>

                    <!-- Brevo (Email & SMS) -->
                    <h2 class="form-head">Brevo (Email & SMS)</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">API Key</td>
                            <td><input type="password" name="brevo_api_key" value="<?php echo htmlspecialchars($S["brevo_api_key"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="background:#f5f5f5;font-weight:600;">Email Settings</td>
                        </tr>
                        <tr>
                            <td>Sender Email</td>
                            <td><input type="email" name="brevo_sender_email" value="<?php echo htmlspecialchars($S["brevo_sender_email"] ?? ""); ?>" class="inp1" style="width:95%;" placeholder="noreply@example.com"></td>
                        </tr>
                        <tr>
                            <td>Sender Name</td>
                            <td><input type="text" name="brevo_sender_name" value="<?php echo htmlspecialchars($S["brevo_sender_name"] ?? ""); ?>" class="inp1" style="width:95%;" placeholder="GamePark"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="background:#f5f5f5;font-weight:600;">SMS Settings</td>
                        </tr>
                        <tr>
                            <td>SMS Sender ID</td>
                            <td><input type="text" name="brevo_sms_sender" value="<?php echo htmlspecialchars($S["brevo_sms_sender"] ?? ""); ?>" class="inp1" style="width:200px;" placeholder="GMPARK"></td>
                        </tr>
                        <tr>
                            <td>SMS Enabled</td>
                            <td>
                                <select name="brevo_sms_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["brevo_sms_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["brevo_sms_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="brevo_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["brevo_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["brevo_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                                <button type="button" onclick="testConnection('brevo')" class="btn">Test</button>
                            </td>
                        </tr>
                    </table>

                </td>
                <td width="50%" valign="top" style="padding-left:15px;">

                    <!-- Google -->
                    <h2 class="form-head">Google APIs</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">Client ID</td>
                            <td><input type="text" name="google_client_id" value="<?php echo htmlspecialchars($S["google_client_id"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Client Secret</td>
                            <td><input type="password" name="google_client_secret" value="<?php echo htmlspecialchars($S["google_client_secret"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>API Key</td>
                            <td><input type="text" name="google_api_key" value="<?php echo htmlspecialchars($S["google_api_key"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Maps API Key</td>
                            <td><input type="text" name="google_maps_key" value="<?php echo htmlspecialchars($S["google_maps_key"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="google_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["google_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["google_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <!-- GST -->
                    <h2 class="form-head">GST API (E-Invoicing)</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">API Key</td>
                            <td><input type="password" name="gst_api_key" value="<?php echo htmlspecialchars($S["gst_api_key"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>API Secret</td>
                            <td><input type="password" name="gst_api_secret" value="<?php echo htmlspecialchars($S["gst_api_secret"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Username (GSTIN)</td>
                            <td><input type="text" name="gst_username" value="<?php echo htmlspecialchars($S["gst_username"] ?? ""); ?>" class="inp1" style="width:95%;" placeholder="27AAAAA0000A1Z5"></td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="gst_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["gst_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["gst_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <!-- Tally -->
                    <h2 class="form-head">Tally Integration</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">Server URL</td>
                            <td><input type="text" name="tally_server_url" value="<?php echo htmlspecialchars($S["tally_server_url"] ?? "http://localhost:9000"); ?>" class="inp1" style="width:95%;" placeholder="http://localhost:9000"></td>
                        </tr>
                        <tr>
                            <td>Company Name</td>
                            <td><input type="text" name="tally_company_name" value="<?php echo htmlspecialchars($S["tally_company_name"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="tally_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["tally_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["tally_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <!-- MSME -->
                    <h2 class="form-head">MSME Udyam API</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">API Key</td>
                            <td><input type="password" name="msme_api_key" value="<?php echo htmlspecialchars($S["msme_api_key"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>API Secret</td>
                            <td><input type="password" name="msme_api_secret" value="<?php echo htmlspecialchars($S["msme_api_secret"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="msme_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["msme_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["msme_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <!-- ClearTax -->
                    <h2 class="form-head">ClearTax (Tax Filing)</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">API Key</td>
                            <td><input type="password" name="cleartax_api_key" value="<?php echo htmlspecialchars($S["cleartax_api_key"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="cleartax_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["cleartax_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["cleartax_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <!-- Zoho -->
                    <h2 class="form-head">Zoho Integration</h2>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <tr>
                            <td width="35%">Client ID</td>
                            <td><input type="text" name="zoho_client_id" value="<?php echo htmlspecialchars($S["zoho_client_id"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Client Secret</td>
                            <td><input type="password" name="zoho_client_secret" value="<?php echo htmlspecialchars($S["zoho_client_secret"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Refresh Token</td>
                            <td><input type="password" name="zoho_refresh_token" value="<?php echo htmlspecialchars($S["zoho_refresh_token"] ?? ""); ?>" class="inp1" style="width:95%;"></td>
                        </tr>
                        <tr>
                            <td>Enabled</td>
                            <td>
                                <select name="zoho_enabled" class="inp1">
                                    <option value="0" <?php echo ($S["zoho_enabled"] ?? "0") == "0" ? "selected" : ""; ?>>No</option>
                                    <option value="1" <?php echo ($S["zoho_enabled"] ?? "0") == "1" ? "selected" : ""; ?>>Yes</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>

        <p style="margin-top:20px;">
            <button type="submit" class="btn">Save All Settings</button>
        </p>

    </form>
</div>

<script>
document.getElementById('frmAddEdit').addEventListener('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);

    fetch('<?php echo ADMINURL; ?>/mod/api-settings/x-api-settings.inc.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.err === 0) {
            alert(data.msg);
        } else {
            alert('Error: ' + (data.msg || 'Failed to save settings'));
        }
    })
    .catch(error => {
        alert('Error saving settings');
        console.error(error);
    });
});

function testConnection(service) {
    fetch('<?php echo ADMINURL; ?>/mod/api-settings/x-api-settings.inc.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'xAction=TEST_CONNECTION&service=' + service
    })
    .then(response => response.json())
    .then(data => {
        if (data.err === 0) {
            alert('Success: ' + data.msg);
        } else {
            alert('Failed: ' + data.msg);
        }
    })
    .catch(error => {
        alert('Connection test failed');
        console.error(error);
    });
}
</script>

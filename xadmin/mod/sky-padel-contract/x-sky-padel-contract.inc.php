<?php
/*
Sky Padel Contract Module - Core Functions

generateContractNo() - Generate unique contract number
generateContract($quotationID) - Create contract from approved quotation
signContract($contractID, $signedBy, $clientIP) - Sign contract from client portal
generateContractPDF($contractID, $output) - Generate contract PDF
copyMilestonesToContract($contractID, $quotationID) - Copy milestones from quotation
getDefaultContractTerms() - Get default legal terms and conditions
getDefaultPaymentTerms() - Get default payment terms
*/

/**
 * Get default legal terms and conditions for padel court installation contract
 */
function getDefaultContractTerms()
{
    return "1. DEFINITIONS AND INTERPRETATION
1.1 \"Contractor\" means Sky Padel India, including its authorized representatives and sub-contractors.
1.2 \"Client\" means the party entering into this contract for the installation of padel court facilities.
1.3 \"Work\" means the supply, installation, and commissioning of padel court(s) as per the agreed specifications.
1.4 \"Site\" means the location where the padel court installation is to be performed.
1.5 \"Contract Price\" means the total amount payable as specified in this contract.

2. SCOPE OF WORK
2.1 The Contractor shall supply all materials, labor, equipment, and services necessary for the complete installation of the padel court(s) as per the specifications provided.
2.2 The scope includes but is not limited to: site preparation (if included), structural framework, glass panels, artificial turf, lighting system (if included), drainage system, and all necessary accessories.
2.3 Any work not explicitly mentioned in the quotation shall be considered as additional work and will be charged separately.

3. CLIENT OBLIGATIONS
3.1 The Client shall provide unobstructed access to the Site during working hours.
3.2 The Client shall ensure availability of water and electricity connections at the Site at their own cost.
3.3 The Client shall obtain all necessary permits, licenses, and approvals required for the installation.
3.4 The Client shall ensure the Site is clear of any obstructions, debris, or hazardous materials.
3.5 The Client shall designate a representative authorized to make decisions on their behalf during the installation.

4. TIMELINE AND DELAYS
4.1 The estimated timeline for completion shall be communicated separately and is subject to Site conditions.
4.2 The Contractor shall not be liable for delays caused by: force majeure events, weather conditions, Client-caused delays, permit delays, or changes in scope requested by the Client.
4.3 Any delay caused by the Client may result in additional charges and extension of the completion timeline.

5. VARIATIONS AND CHANGES
5.1 Any variation or change to the scope of work must be agreed upon in writing by both parties.
5.2 Variations may affect the Contract Price and completion timeline.
5.3 The Contractor reserves the right to make minor modifications to specifications if necessary for technical or safety reasons, without affecting the overall quality.

6. QUALITY AND MATERIALS
6.1 All materials used shall meet or exceed industry standards for padel court construction.
6.2 The Contractor guarantees the quality of workmanship for a period specified in the warranty section.
6.3 Material specifications may vary slightly based on availability, but equivalent quality materials will be used.

7. INSPECTION AND ACCEPTANCE
7.1 The Client or their representative shall inspect the completed work within 7 days of completion notification.
7.2 Any defects or non-conformities must be reported in writing within this period.
7.3 Failure to report defects within the specified period shall constitute acceptance of the work.

8. WARRANTY
8.1 Structural framework: 5 years warranty against manufacturing defects.
8.2 Glass panels: 2 years warranty against defects (excluding damage from impact or misuse).
8.3 Artificial turf: 5 years warranty on material, 2 years on installation.
8.4 Lighting system (if included): 2 years warranty on fixtures, subject to manufacturer's terms.
8.5 Warranty does not cover: damage due to misuse, negligence, unauthorized modifications, natural disasters, or normal wear and tear.
8.6 Warranty claims must be submitted in writing with photographic evidence.

9. LIABILITY AND INDEMNIFICATION
9.1 The Contractor's total liability under this contract shall not exceed the Contract Price.
9.2 The Contractor shall not be liable for any indirect, consequential, or incidental damages.
9.3 The Client shall indemnify the Contractor against any claims arising from the Client's failure to obtain necessary permits or approvals.

10. INSURANCE
10.1 The Contractor shall maintain adequate insurance coverage during the installation period.
10.2 The Client is advised to obtain appropriate insurance for the completed installation.

11. INTELLECTUAL PROPERTY
11.1 All designs, drawings, and specifications provided by the Contractor remain their intellectual property.
11.2 The Client may not reproduce or share these materials without prior written consent.

12. CONFIDENTIALITY
12.1 Both parties agree to keep confidential any proprietary information shared during the course of this contract.

13. TERMINATION
13.1 Either party may terminate this contract with 30 days written notice.
13.2 In case of termination by the Client, payments made shall be non-refundable and payment for work completed shall be due.
13.3 The Contractor may terminate immediately if payments are overdue by more than 30 days.

14. DISPUTE RESOLUTION
14.1 Any disputes shall first be attempted to be resolved through mutual negotiation.
14.2 If negotiation fails, disputes shall be referred to arbitration in accordance with the Arbitration and Conciliation Act, 1996.
14.3 The seat of arbitration shall be Mumbai, Maharashtra.
14.4 The language of arbitration shall be English.

15. GOVERNING LAW
15.1 This contract shall be governed by and construed in accordance with the laws of India.
15.2 The courts in Mumbai shall have exclusive jurisdiction over any legal proceedings.

16. FORCE MAJEURE
16.1 Neither party shall be liable for failure to perform due to causes beyond their reasonable control, including but not limited to: natural disasters, war, terrorism, government actions, pandemics, or civil unrest.
16.2 The affected party shall notify the other party promptly and take reasonable steps to mitigate the effects.

17. ENTIRE AGREEMENT
17.1 This contract, along with the quotation and any annexures, constitutes the entire agreement between the parties.
17.2 No oral agreements or representations shall be binding unless incorporated into this contract in writing.

18. AMENDMENTS
18.1 Any amendments to this contract must be made in writing and signed by both parties.

19. SEVERABILITY
19.1 If any provision of this contract is found to be invalid or unenforceable, the remaining provisions shall continue in full force and effect.

20. NOTICES
20.1 All notices under this contract shall be in writing and delivered by email, registered post, or courier to the addresses specified herein.
20.2 Notices shall be deemed delivered: immediately if by email (with confirmation), 3 days after posting if by registered post, or upon receipt if by courier.";
}

/**
 * Get default payment terms for contract
 */
function getDefaultPaymentTerms()
{
    return "PAYMENT TERMS AND CONDITIONS

1. PAYMENT SCHEDULE
   Payments shall be made as per the milestone schedule specified in this contract.

2. PAYMENT METHODS
   - Bank Transfer (NEFT/RTGS/IMPS) to the account specified in the invoice
   - Cheque payable to \"Sky Padel India\" (subject to clearance)
   - UPI payments accepted for amounts up to Rs. 1,00,000

3. ADVANCE PAYMENT
   - The advance payment (as specified) is required to confirm the order and commence material procurement.
   - Work shall commence only after receipt of the advance payment.
   - Advance payment is non-refundable once material procurement has begun.

4. MILESTONE PAYMENTS
   - Each milestone payment is due within 7 days of milestone completion notification.
   - Milestone completion shall be communicated via email with supporting documentation/photographs.
   - Inspection by the Client shall be completed within 3 days of notification.

5. FINAL PAYMENT
   - The final payment is due upon completion and handover of the project.
   - Final payment must be cleared before handover of the completed facility.

6. LATE PAYMENT
   - Payments overdue by more than 7 days shall attract interest at 1.5% per month.
   - Work may be suspended if payments are overdue by more than 15 days.
   - The Contractor reserves the right to withdraw from the project if payments are overdue by more than 30 days.

7. TAXES
   - All prices are exclusive of GST unless otherwise specified.
   - GST at the applicable rate shall be charged on all invoices.
   - Any changes in tax rates after quotation shall be borne by the Client.

8. PRICE VALIDITY
   - The Contract Price is valid for 30 days from the date of this contract.
   - Prices may be revised if there are significant changes in material costs or exchange rates.

9. REFUND POLICY
   - Advance payments are non-refundable once material procurement has commenced.
   - In case of project cancellation by the Client, charges shall apply based on work completed.
   - Refunds, if applicable, shall be processed within 30 working days.

10. BANK DETAILS
    Account Name: Sky Padel India
    Bank: [Bank Name]
    Account No: [Account Number]
    IFSC Code: [IFSC Code]
    Branch: [Branch Name]";
}

/**
 * Generate unique contract number: CON-YYYYMMDD-XXXX
 */
function generateContractNo()
{
    global $DB;
    $prefix = "CON-" . date("Ymd") . "-";

    $DB->sql = "SELECT contractNo FROM " . $DB->pre . "sky_padel_contract
                WHERE contractNo LIKE ? ORDER BY contractID DESC LIMIT 1";
    $DB->vals = array($prefix . "%");
    $DB->types = "s";
    $last = $DB->dbRow();

    if ($last && !empty($last["contractNo"])) {
        $parts = explode("-", $last["contractNo"]);
        $seq = intval(end($parts)) + 1;
    } else {
        $seq = 1;
    }

    return $prefix . str_pad($seq, 4, "0", STR_PAD_LEFT);
}

/**
 * Generate contract from approved quotation
 * @param int $quotationID
 * @return int $contractID or 0 on failure
 */
function generateContract($quotationID)
{
    global $DB;

    // Fetch quotation with lead details
    $DB->vals = array($quotationID);
    $DB->types = "i";
    $DB->sql = "SELECT q.*, l.clientName, l.clientEmail, l.clientPhone, l.siteAddress, l.siteCity, l.siteState
                FROM " . $DB->pre . "sky_padel_quotation q
                LEFT JOIN " . $DB->pre . "sky_padel_lead l ON q.leadID = l.leadID
                WHERE q.quotationID = ?";
    $quotation = $DB->dbRow();

    if (!$quotation) {
        return 0;
    }

    // Generate contract number
    $contractNo = generateContractNo();

    // Build client address
    $clientAddress = implode(", ", array_filter([
        $quotation["siteAddress"] ?? "",
        $quotation["siteCity"] ?? "",
        $quotation["siteState"] ?? ""
    ]));

    // Get terms - use quotation terms if available, otherwise use defaults
    $termsAndConditions = !empty($quotation["termsAndConditions"])
        ? $quotation["termsAndConditions"]
        : getDefaultContractTerms();

    $paymentTerms = !empty($quotation["paymentTerms"])
        ? $quotation["paymentTerms"]
        : getDefaultPaymentTerms();

    // Create contract record
    $DB->table = $DB->pre . "sky_padel_contract";
    $DB->data = array(
        "contractNo" => $contractNo,
        "quotationID" => $quotationID,
        "leadID" => $quotation["leadID"],
        "clientName" => $quotation["clientName"],
        "clientEmail" => $quotation["clientEmail"],
        "clientPhone" => $quotation["clientPhone"],
        "clientAddress" => $clientAddress,
        "contractDate" => date("Y-m-d"),
        "projectDescription" => $quotation["scopeOfWork"] ?? "",
        "courtConfiguration" => $quotation["courtConfiguration"] ?? "",
        "contractAmount" => $quotation["totalAmount"],
        "advanceAmount" => $quotation["advanceAmount"] ?? ($quotation["totalAmount"] * 0.5),
        "advancePercentage" => $quotation["advancePercentage"] ?? 50,
        "termsAndConditions" => $termsAndConditions,
        "paymentTerms" => $paymentTerms,
        "scopeOfWork" => $quotation["scopeOfWork"] ?? "",
        "contractStatus" => "Pending Signature",
        "createdBy" => $_SESSION["mxAdminUserID"] ?? 0
    );

    if ($DB->dbInsert()) {
        $contractID = $DB->insertID;

        // Copy milestones from quotation
        copyMilestonesToContract($contractID, $quotationID);

        // Update quotation with contract reference
        $DB->vals = array($contractID, $quotationID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "sky_padel_quotation SET contractID = ? WHERE quotationID = ?";
        $DB->dbQuery();

        return $contractID;
    }

    return 0;
}

/**
 * Copy milestones from quotation to contract
 */
function copyMilestonesToContract($contractID, $quotationID)
{
    global $DB;

    // Get quotation milestones
    $DB->vals = array($quotationID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_quotation_milestone WHERE quotationID = ? ORDER BY sortOrder";
    $DB->dbRows();
    $milestones = $DB->rows;

    foreach ($milestones as $m) {
        $DB->table = $DB->pre . "sky_padel_contract_milestone";
        $DB->data = array(
            "contractID" => $contractID,
            "milestoneName" => $m["milestoneName"],
            "milestoneDescription" => $m["milestoneDescription"] ?? "",
            "paymentPercentage" => $m["paymentPercentage"],
            "paymentAmount" => $m["paymentAmount"],
            "dueAfterDays" => $m["dueAfterDays"] ?? 0,
            "sortOrder" => $m["sortOrder"]
        );
        $DB->dbInsert();
    }
}

/**
 * Generate and send OTP for contract signing
 * @param int $contractID
 * @param string $clientEmail
 * @param string $clientPhone
 * @return array Result
 */
function sendContractSigningOTP($contractID, $clientEmail = null, $clientPhone = null)
{
    global $DB;

    // Verify contract exists and is pending
    $DB->vals = array($contractID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_contract WHERE contractID = ? AND contractStatus = 'Pending Signature' AND status = 1";
    $contract = $DB->dbRow();

    if (!$contract) {
        return array("err" => 1, "msg" => "Contract not found or already signed");
    }

    // Check OTP attempt limit (max 5 attempts per hour)
    $DB->vals = array($contractID);
    $DB->types = "i";
    $DB->sql = "SELECT otpAttempts, otpSentAt FROM " . $DB->pre . "sky_padel_contract WHERE contractID = ?";
    $otpInfo = $DB->dbRow();

    if ($otpInfo["otpAttempts"] >= 5 && $otpInfo["otpSentAt"]) {
        $lastSent = strtotime($otpInfo["otpSentAt"]);
        if ((time() - $lastSent) < 3600) { // Within 1 hour
            return array("err" => 1, "msg" => "Too many OTP requests. Please try again after an hour.");
        }
        // Reset attempts if more than 1 hour has passed
        $DB->vals = array(0, $contractID);
        $DB->types = "ii";
        $DB->sql = "UPDATE " . $DB->pre . "sky_padel_contract SET otpAttempts = ? WHERE contractID = ?";
        $DB->dbQuery();
    }

    // Generate 6-digit OTP
    $otp = str_pad(random_int(100000, 999999), 6, "0", STR_PAD_LEFT);

    // Store OTP in database
    $DB->vals = array($otp, date("Y-m-d H:i:s"), $contractID);
    $DB->types = "ssi";
    $DB->sql = "UPDATE " . $DB->pre . "sky_padel_contract SET otpCode = ?, otpSentAt = ?, otpAttempts = otpAttempts + 1 WHERE contractID = ?";
    $DB->dbQuery();

    // Send OTP via email
    $email = $clientEmail ?: $contract["clientEmail"];
    $phone = $clientPhone ?: $contract["clientPhone"];

    $emailSent = sendOTPEmail($email, $otp, $contract["contractNo"], $contract["clientName"]);

    // Also try SMS if phone is available
    $smsSent = false;
    if ($phone) {
        $smsSent = sendOTPSMS($phone, $otp, $contract["contractNo"]);
    }

    if ($emailSent || $smsSent) {
        $maskedEmail = maskEmail($email);
        $maskedPhone = $phone ? maskPhone($phone) : "";
        $sentTo = $maskedEmail;
        if ($smsSent && $maskedPhone) {
            $sentTo .= " and " . $maskedPhone;
        }

        return array(
            "err" => 0,
            "msg" => "OTP sent successfully",
            "sentTo" => $sentTo,
            "expiresIn" => 10 // minutes
        );
    }

    return array("err" => 1, "msg" => "Failed to send OTP. Please try again.");
}

/**
 * Verify OTP for contract signing
 * @param int $contractID
 * @param string $enteredOTP
 * @return array Result
 */
function verifyContractOTP($contractID, $enteredOTP)
{
    global $DB;

    // Get contract with OTP info
    $DB->vals = array($contractID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_contract WHERE contractID = ? AND contractStatus = 'Pending Signature' AND status = 1";
    $contract = $DB->dbRow();

    if (!$contract) {
        return array("err" => 1, "msg" => "Contract not found or already signed");
    }

    // Check if OTP exists
    if (empty($contract["otpCode"])) {
        return array("err" => 1, "msg" => "No OTP generated. Please request a new OTP.");
    }

    // Check OTP expiry (10 minutes)
    $otpSentAt = strtotime($contract["otpSentAt"]);
    if ((time() - $otpSentAt) > 600) { // 10 minutes
        return array("err" => 1, "msg" => "OTP has expired. Please request a new OTP.");
    }

    // Verify OTP
    if ($contract["otpCode"] !== $enteredOTP) {
        return array("err" => 1, "msg" => "Invalid OTP. Please try again.");
    }

    // Mark OTP as verified
    $DB->vals = array(date("Y-m-d H:i:s"), $contractID);
    $DB->types = "si";
    $DB->sql = "UPDATE " . $DB->pre . "sky_padel_contract SET otpVerifiedAt = ? WHERE contractID = ?";
    $DB->dbQuery();

    return array("err" => 0, "msg" => "OTP verified successfully", "verified" => true);
}

/**
 * Send OTP via email
 */
function sendOTPEmail($email, $otp, $contractNo, $clientName)
{
    // Use existing email system
    $subject = "OTP for Contract Signing - " . $contractNo;

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); padding: 30px; text-align: center;'>
            <h1 style='color: #fff; margin: 0;'>Sky Padel India</h1>
        </div>
        <div style='padding: 30px; background: #f8f9fa;'>
            <h2 style='color: #333;'>Contract Signing Verification</h2>
            <p>Dear " . htmlspecialchars($clientName) . ",</p>
            <p>Your One-Time Password (OTP) for signing contract <strong>" . htmlspecialchars($contractNo) . "</strong> is:</p>
            <div style='background: #fff; border: 2px dashed #0d9488; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;'>
                <span style='font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #0d9488;'>" . $otp . "</span>
            </div>
            <p style='color: #666;'>This OTP is valid for <strong>10 minutes</strong>.</p>
            <p style='color: #666;'>If you did not request this OTP, please ignore this email or contact us immediately.</p>
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            <p style='color: #999; font-size: 12px;'>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>";

    // Try to use mailing function if available
    if (function_exists("sendMail")) {
        return sendMail($email, $subject, $body);
    }

    // Fallback to basic mail
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Sky Padel India <noreply@skypadel.in>\r\n";

    return @mail($email, $subject, $body, $headers);
}

/**
 * Send OTP via SMS (placeholder - integrate with SMS gateway)
 */
function sendOTPSMS($phone, $otp, $contractNo)
{
    // Format phone number
    $phone = preg_replace("/[^0-9]/", "", $phone);
    if (strlen($phone) == 10) {
        $phone = "91" . $phone;
    }

    // TODO: Integrate with SMS gateway (MSG91, Twilio, etc.)
    // For now, return false as SMS is not configured
    // Example MSG91 integration:
    /*
    $apiKey = "your_msg91_api_key";
    $senderId = "SKYPDL";
    $templateId = "your_template_id";
    $message = "Your OTP for signing Sky Padel contract " . $contractNo . " is " . $otp . ". Valid for 10 minutes.";

    $url = "https://api.msg91.com/api/v5/otp";
    $data = array(
        "mobile" => $phone,
        "authkey" => $apiKey,
        "otp" => $otp,
        "sender" => $senderId,
        "template_id" => $templateId
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);
    curl_close($ch);

    return strpos($response, 'success') !== false;
    */

    return false;
}

/**
 * Mask email for display (jo***@example.com)
 */
function maskEmail($email)
{
    $parts = explode("@", $email);
    if (count($parts) != 2) return $email;

    $name = $parts[0];
    $domain = $parts[1];

    if (strlen($name) <= 2) {
        $maskedName = $name[0] . "***";
    } else {
        $maskedName = substr($name, 0, 2) . str_repeat("*", strlen($name) - 2);
    }

    return $maskedName . "@" . $domain;
}

/**
 * Mask phone for display (******1234)
 */
function maskPhone($phone)
{
    $phone = preg_replace("/[^0-9]/", "", $phone);
    if (strlen($phone) < 4) return $phone;

    return str_repeat("*", strlen($phone) - 4) . substr($phone, -4);
}

/**
 * Sign contract with OTP verification (called from client portal)
 * @param int $contractID
 * @param string $signedBy - Client typed name
 * @param string $clientIP
 * @param bool $requireOTP - Whether OTP verification is required
 * @return array Result
 */
function signContractPortalWithOTP($contractID, $signedBy, $clientIP, $requireOTP = true)
{
    global $DB;

    // Verify contract exists and is pending
    $DB->vals = array($contractID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_contract WHERE contractID = ? AND contractStatus = 'Pending Signature' AND status = 1";
    $contract = $DB->dbRow();

    if (!$contract) {
        return array("err" => 1, "msg" => "Contract not found or already signed");
    }

    // Check OTP verification
    if ($requireOTP) {
        if (empty($contract["otpVerifiedAt"])) {
            return array("err" => 1, "msg" => "OTP verification required before signing");
        }

        // Check if OTP was verified within the last 15 minutes
        $verifiedAt = strtotime($contract["otpVerifiedAt"]);
        if ((time() - $verifiedAt) > 900) { // 15 minutes
            return array("err" => 1, "msg" => "OTP verification has expired. Please verify again.");
        }
    }

    // Update contract with signature
    $signatureMethod = $requireOTP ? "OTP-Verified" : "Basic";
    $DB->vals = array($signedBy, date("Y-m-d H:i:s"), $clientIP, 1, 1, "Signed", $signatureMethod, $contractID);
    $DB->types = "sssiissl";
    $DB->sql = "UPDATE " . $DB->pre . "sky_padel_contract
                SET signedBy = ?, signedAt = ?, signatureIP = ?, agreementAccepted = ?, authorizationAccepted = ?, contractStatus = ?, signatureMethod = ?
                WHERE contractID = ?";

    if ($DB->dbQuery()) {
        // Clear OTP fields
        $DB->vals = array(null, null, null, 0, $contractID);
        $DB->types = "sssii";
        $DB->sql = "UPDATE " . $DB->pre . "sky_padel_contract SET otpCode = ?, otpSentAt = ?, otpVerifiedAt = ?, otpAttempts = ? WHERE contractID = ?";
        $DB->dbQuery();

        // Auto-generate proforma invoice after contract is signed
        try {
            $proformaFile = __DIR__ . "/../sky-padel-proforma/x-sky-padel-proforma.inc.php";
            if (file_exists($proformaFile)) {
                require_once($proformaFile);
                if (function_exists('generateProformaFromQuotation')) {
                    $proformaID = generateProformaFromQuotation($contract["quotationID"]);
                    if ($proformaID > 0) {
                        // Update quotation with proforma reference
                        $DB->vals = array(1, $proformaID, $contract["quotationID"]);
                        $DB->types = "iii";
                        $DB->sql = "UPDATE " . $DB->pre . "sky_padel_quotation SET proformaGenerated=?, proformaID=? WHERE quotationID=?";
                        $DB->dbQuery();
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Proforma generation error after contract sign: " . $e->getMessage());
        }

        return array("err" => 0, "msg" => "Contract signed successfully with OTP verification");
    }

    return array("err" => 1, "msg" => "Failed to sign contract");
}

/**
 * Sign contract (called from client portal) - Legacy function maintained for backward compatibility
 * @param int $contractID
 * @param string $signedBy - Client typed name
 * @param string $clientIP
 * @return array Result
 */
function signContractPortal($contractID, $signedBy, $clientIP)
{
    global $DB;

    // Verify contract exists and is pending
    $DB->vals = array($contractID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_contract WHERE contractID = ? AND contractStatus = 'Pending Signature' AND status = 1";
    $contract = $DB->dbRow();

    if (!$contract) {
        return array("err" => 1, "msg" => "Contract not found or already signed");
    }

    // Update contract with signature
    $DB->vals = array($signedBy, date("Y-m-d H:i:s"), $clientIP, 1, 1, "Signed", $contractID);
    $DB->types = "sssiisd";
    $DB->sql = "UPDATE " . $DB->pre . "sky_padel_contract
                SET signedBy = ?, signedAt = ?, signatureIP = ?, agreementAccepted = ?, authorizationAccepted = ?, contractStatus = ?
                WHERE contractID = ?";

    if ($DB->dbQuery()) {
        // Auto-generate proforma invoice after contract is signed
        try {
            $proformaFile = __DIR__ . "/../sky-padel-proforma/x-sky-padel-proforma.inc.php";
            if (file_exists($proformaFile)) {
                require_once($proformaFile);
                if (function_exists('generateProformaFromQuotation')) {
                    $proformaID = generateProformaFromQuotation($contract["quotationID"]);
                    if ($proformaID > 0) {
                        // Update quotation with proforma reference
                        $DB->vals = array(1, $proformaID, $contract["quotationID"]);
                        $DB->types = "iii";
                        $DB->sql = "UPDATE " . $DB->pre . "sky_padel_quotation SET proformaGenerated=?, proformaID=? WHERE quotationID=?";
                        $DB->dbQuery();
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Proforma generation error after contract sign: " . $e->getMessage());
        }

        return array("err" => 0, "msg" => "Contract signed successfully");
    }

    return array("err" => 1, "msg" => "Failed to sign contract");
}

/**
 * Cancel contract
 */
function cancelContract()
{
    global $DB;
    $contractID = intval($_POST["contractID"]);

    $DB->vals = array("Cancelled", $contractID);
    $DB->types = "si";
    $DB->sql = "UPDATE " . $DB->pre . "sky_padel_contract SET contractStatus = ? WHERE contractID = ?";

    if ($DB->dbQuery()) {
        setResponse(array("err" => 0, "msg" => "Contract cancelled"));
    } else {
        setResponse(array("err" => 1, "msg" => "Failed to cancel contract"));
    }
}

/**
 * Save contract (edit)
 */
function saveContract()
{
    global $DB;
    $contractID = intval($_POST["contractID"]);

    // Check if contract is already signed
    $DB->vals = array($contractID);
    $DB->types = "i";
    $DB->sql = "SELECT contractStatus FROM " . $DB->pre . "sky_padel_contract WHERE contractID = ?";
    $existing = $DB->dbRow();

    if (!$existing) {
        setResponse(array("err" => 1, "msg" => "Contract not found"));
        return;
    }

    if ($existing["contractStatus"] === "Signed") {
        setResponse(array("err" => 1, "msg" => "Cannot edit a signed contract"));
        return;
    }

    // Update contract
    $DB->vals = array(
        $_POST["contractDate"] ?? date("Y-m-d"),
        $_POST["courtConfiguration"] ?? "",
        $_POST["clientName"] ?? "",
        $_POST["clientEmail"] ?? "",
        $_POST["clientPhone"] ?? "",
        $_POST["clientAddress"] ?? "",
        floatval($_POST["contractAmount"] ?? 0),
        floatval($_POST["advancePercentage"] ?? 0),
        floatval($_POST["advanceAmount"] ?? 0),
        $_POST["scopeOfWork"] ?? "",
        $_POST["termsAndConditions"] ?? "",
        $_POST["paymentTerms"] ?? "",
        $contractID
    );
    $DB->types = "ssssssdddsss" . "i";
    $DB->sql = "UPDATE " . $DB->pre . "sky_padel_contract SET
                contractDate = ?,
                courtConfiguration = ?,
                clientName = ?,
                clientEmail = ?,
                clientPhone = ?,
                clientAddress = ?,
                contractAmount = ?,
                advancePercentage = ?,
                advanceAmount = ?,
                scopeOfWork = ?,
                termsAndConditions = ?,
                paymentTerms = ?
                WHERE contractID = ?";

    if (!$DB->dbQuery()) {
        setResponse(array("err" => 1, "msg" => "Failed to update contract"));
        return;
    }

    // Update milestones
    if (isset($_POST["milestones"]) && is_array($_POST["milestones"])) {
        // Get existing milestone IDs
        $DB->vals = array($contractID);
        $DB->types = "i";
        $DB->sql = "SELECT milestoneID FROM " . $DB->pre . "sky_padel_contract_milestone WHERE contractID = ?";
        $DB->dbRows();
        $existingMilestones = $DB->rows;
        $existingIDs = array_column($existingMilestones, "milestoneID");

        $submittedIDs = array();
        $sortOrder = 1;

        foreach ($_POST["milestones"] as $m) {
            $milestoneID = intval($m["id"] ?? 0);
            $milestoneName = trim($m["name"] ?? "");

            if (empty($milestoneName)) continue;

            if ($milestoneID > 0 && in_array($milestoneID, $existingIDs)) {
                // Update existing milestone
                $DB->vals = array(
                    $milestoneName,
                    $m["description"] ?? "",
                    floatval($m["percentage"] ?? 0),
                    floatval($m["amount"] ?? 0),
                    intval($m["dueAfterDays"] ?? 0),
                    $sortOrder,
                    $milestoneID
                );
                $DB->types = "ssddiis";
                $DB->sql = "UPDATE " . $DB->pre . "sky_padel_contract_milestone SET
                            milestoneName = ?, milestoneDescription = ?, paymentPercentage = ?,
                            paymentAmount = ?, dueAfterDays = ?, sortOrder = ?
                            WHERE milestoneID = ?";
                $DB->dbQuery();
                $submittedIDs[] = $milestoneID;
            } else {
                // Insert new milestone
                $DB->table = $DB->pre . "sky_padel_contract_milestone";
                $DB->data = array(
                    "contractID" => $contractID,
                    "milestoneName" => $milestoneName,
                    "milestoneDescription" => $m["description"] ?? "",
                    "paymentPercentage" => floatval($m["percentage"] ?? 0),
                    "paymentAmount" => floatval($m["amount"] ?? 0),
                    "dueAfterDays" => intval($m["dueAfterDays"] ?? 0),
                    "sortOrder" => $sortOrder
                );
                $DB->dbInsert();
                $submittedIDs[] = $DB->insertID;
            }
            $sortOrder++;
        }

        // Delete removed milestones
        $toDelete = array_diff($existingIDs, $submittedIDs);
        if (!empty($toDelete)) {
            $DB->sql = "DELETE FROM " . $DB->pre . "sky_padel_contract_milestone WHERE milestoneID IN (" . implode(",", array_map("intval", $toDelete)) . ")";
            $DB->dbQuery();
        }
    }

    setResponse(array("err" => 0, "msg" => "Contract saved successfully"));
}

/**
 * Get contract details with related data
 */
function getContractDetails($contractID)
{
    global $DB;

    $DB->vals = array($contractID);
    $DB->types = "i";
    $DB->sql = "SELECT c.*, q.quotationNo, q.quotationDate, q.courtConfiguration as qCourtConfig
                FROM " . $DB->pre . "sky_padel_contract c
                LEFT JOIN " . $DB->pre . "sky_padel_quotation q ON c.quotationID = q.quotationID
                WHERE c.contractID = ?";
    $contract = $DB->dbRow();

    if ($contract) {
        // Get milestones
        $DB->vals = array($contractID);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_contract_milestone WHERE contractID = ? ORDER BY sortOrder";
        $DB->dbRows();
        $contract["milestones"] = $DB->rows;
    }

    return $contract;
}

// Handle AJAX requests
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "CANCEL": cancelContract(); break;
            case "SAVE": saveContract(); break;
            case "GENERATE":
                $contractID = generateContract(intval($_POST["quotationID"]));
                if ($contractID) {
                    setResponse(array("err" => 0, "contractID" => $contractID));
                } else {
                    setResponse(array("err" => 1, "msg" => "Failed to generate contract"));
                }
                break;
            case "SEND_OTP":
                $result = sendContractSigningOTP(
                    intval($_POST["contractID"]),
                    $_POST["email"] ?? null,
                    $_POST["phone"] ?? null
                );
                setResponse($result);
                break;
            case "VERIFY_OTP":
                $result = verifyContractOTP(
                    intval($_POST["contractID"]),
                    trim($_POST["otp"] ?? "")
                );
                setResponse($result);
                break;
            case "SIGN_WITH_OTP":
                $clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                if (strpos($clientIP, ',') !== false) {
                    $clientIP = trim(explode(',', $clientIP)[0]);
                }
                $result = signContractPortalWithOTP(
                    intval($_POST["contractID"]),
                    trim($_POST["signedBy"] ?? ""),
                    $clientIP,
                    true
                );
                setResponse($result);
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "sky_padel_contract", "PK" => "contractID"));
}
?>

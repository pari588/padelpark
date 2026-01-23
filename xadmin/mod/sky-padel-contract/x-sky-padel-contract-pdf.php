<?php
/**
 * Sky Padel Contract PDF Generator
 * Indian Legal Document Style
 */

// Load dependencies
if (!isset($DB)) {
    require_once(__DIR__ . "/../../../core/core.inc.php");
    require_once(__DIR__ . "/../../inc/site.inc.php");
}

// Check for mPDF
$mpdfPath = __DIR__ . "/../../../vendor/autoload.php";
if (file_exists($mpdfPath)) {
    require_once($mpdfPath);
}

$id = intval($_GET["id"] ?? 0);
$style = $_GET["style"] ?? "legal"; // legal (new) or simple (legacy)

if ($id == 0) {
    die("Invalid contract ID");
}

// Get contract details with lead info
$DB->vals = array(1, $id);
$DB->types = "ii";
$DB->sql = "SELECT c.*, q.quotationNo, q.courtConfiguration,
                   l.clientName, l.clientEmail, l.clientPhone,
                   l.siteAddress, l.siteCity, l.siteState
            FROM " . $DB->pre . "sky_padel_contract c
            LEFT JOIN " . $DB->pre . "sky_padel_quotation q ON c.quotationID = q.quotationID
            LEFT JOIN " . $DB->pre . "sky_padel_lead l ON c.leadID = l.leadID
            WHERE c.status=? AND c.contractID=?";
$C = $DB->dbRow();

if (!$C) {
    die("Contract not found");
}

// Get milestones
$DB->vals = array($id);
$DB->types = "i";
$DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_contract_milestone WHERE contractID=? ORDER BY sortOrder";
$DB->dbRows();
$milestones = $DB->rows;

// Company details from settings or defaults
$company = array(
    "name" => "SKY PADEL INDIA PRIVATE LIMITED",
    "address" => "501, Business Hub, Andheri East, Mumbai, Maharashtra - 400069",
    "cin" => "U74999MH2024PTC421234",
    "gstin" => "27AADCS1234A1ZF",
    "pan" => "AADCS1234A",
    "phone" => "+91 22 4567 8900",
    "email" => "contracts@skypadelindia.com"
);

// Number to words (Indian system)
function numberToWordsIndian($number) {
    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
             'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    $number = (int)$number;
    if ($number < 20) return $ones[$number];
    if ($number < 100) return $tens[(int)($number/10)] . ($number % 10 ? ' ' . $ones[$number % 10] : '');
    if ($number < 1000) return $ones[(int)($number/100)] . ' Hundred' . ($number % 100 ? ' and ' . numberToWordsIndian($number % 100) : '');
    if ($number < 100000) return numberToWordsIndian((int)($number/1000)) . ' Thousand' . ($number % 1000 ? ' ' . numberToWordsIndian($number % 1000) : '');
    if ($number < 10000000) return numberToWordsIndian((int)($number/100000)) . ' Lakh' . ($number % 100000 ? ' ' . numberToWordsIndian($number % 100000) : '');
    return numberToWordsIndian((int)($number/10000000)) . ' Crore' . ($number % 10000000 ? ' ' . numberToWordsIndian($number % 10000000) : '');
}

$amountInWords = numberToWordsIndian((int)$C['contractAmount']) . ' Rupees Only';
$contractDate = date('jS F, Y', strtotime($C['contractDate']));

// Build Indian Legal Style HTML
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 12mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Serif", Georgia, serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #1a1a1a;
            background: #faf6ed;
        }

        .document-wrapper {
            border: 2px solid #1a5c3a;
            padding: 8mm;
            position: relative;
            background: #faf6ed;
        }

        .document-wrapper::before {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            right: 3px;
            bottom: 3px;
            border: 1px solid #2d7a52;
            pointer-events: none;
        }

        /* Document Title */
        .document-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .contract-title {
            font-size: 18pt;
            font-weight: bold;
            color: #0f3d26;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .contract-number {
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 9pt;
            color: #1a5c3a;
            letter-spacing: 0.08em;
            padding: 4px 12px;
            background: #f0e9d8;
            border: 1px solid #c9b896;
            display: inline-block;
            margin-bottom: 5px;
        }

        .contract-date {
            font-size: 10pt;
            color: #4a4a4a;
            font-style: italic;
        }

        /* Parties Section */
        .parties-section {
            margin-bottom: 15px;
            padding: 12px;
            background: rgba(26, 92, 58, 0.03);
            border: 1px solid #c9b896;
        }

        .party-block {
            margin-bottom: 10px;
        }

        .party-block:last-child {
            margin-bottom: 0;
        }

        .party-label {
            font-size: 8pt;
            font-weight: bold;
            color: #1a5c3a;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 4px;
        }

        .party-name {
            font-size: 11pt;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 3px;
        }

        .party-details {
            font-size: 9pt;
            color: #4a4a4a;
            line-height: 1.4;
        }

        .party-connector {
            text-align: center;
            font-style: italic;
            color: #4a4a4a;
            margin: 8px 0;
            font-size: 9pt;
        }

        /* Section Headings */
        .section-heading {
            font-size: 11pt;
            font-weight: bold;
            color: #0f3d26;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin: 15px 0 10px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #c9b896;
        }

        /* Contract Value Box */
        .value-box {
            background: linear-gradient(135deg, #1a5c3a 0%, #0f3d26 100%);
            color: white;
            padding: 12px 15px;
            margin: 15px 0;
        }

        .value-box table {
            width: 100%;
        }

        .value-label {
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            opacity: 0.9;
            color: #ffffff;
        }

        .value-amount {
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 16pt;
            font-weight: bold;
            color: #ffffff;
        }

        .value-words {
            font-size: 9pt;
            font-style: italic;
            opacity: 0.9;
            margin-top: 3px;
            color: #ffffff;
        }

        .value-badge {
            background: #b8860b;
            color: #ffffff;
            padding: 8px 12px;
            text-align: center;
        }

        /* Recitals */
        .whereas-clause {
            margin-bottom: 8px;
            text-align: justify;
            text-indent: 1.5em;
        }

        .whereas-clause strong {
            font-weight: bold;
            color: #1a5c3a;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9pt;
        }

        .data-table th {
            background: #1a5c3a;
            color: white;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #0f3d26;
        }

        .data-table th:last-child {
            text-align: right;
        }

        .data-table td {
            padding: 8px 10px;
            border: 1px solid #c9b896;
            background: white;
        }

        .data-table tr:nth-child(even) td {
            background: #faf6ed;
        }

        .data-table tfoot td {
            background: #f0e9d8 !important;
            font-weight: bold;
            border-top: 2px solid #1a5c3a;
        }

        /* Terms Section */
        .terms-content {
            font-size: 9pt;
            line-height: 1.6;
            text-align: justify;
            padding: 10px;
            background: #faf6ed;
            border: 1px solid #c9b896;
        }

        .clause {
            margin-bottom: 6px;
        }

        .clause-number {
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 8pt;
            color: #1a5c3a;
            font-weight: bold;
            margin-right: 5px;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signature-intro {
            text-align: center;
            font-style: italic;
            margin-bottom: 15px;
            padding: 10px;
            background: #f0e9d8;
            border: 1px solid #c9b896;
            font-size: 9pt;
        }

        .signature-table {
            width: 100%;
        }

        .signature-block {
            padding: 12px;
            border: 1px solid #c9b896;
            background: white;
        }

        .signature-block-title {
            font-size: 9pt;
            font-weight: bold;
            color: #1a5c3a;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #c9b896;
        }

        .signature-line {
            border-bottom: 1px solid #1a1a1a;
            height: 40px;
            margin-bottom: 5px;
        }

        .signature-label {
            font-size: 8pt;
            color: #4a4a4a;
            font-style: italic;
        }

        .signature-details {
            margin-top: 12px;
        }

        .signature-field {
            margin-bottom: 5px;
            font-size: 9pt;
        }

        .signature-field-label {
            color: #4a4a4a;
            display: inline-block;
            width: 60px;
        }

        .signature-field-value {
            border-bottom: 1px dotted #c9b896;
            display: inline-block;
            min-width: 120px;
            padding-bottom: 2px;
        }

        .signed-name {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 14pt;
            font-style: italic;
            color: #1a5c3a;
            margin-bottom: 5px;
        }

        .signed-meta {
            font-size: 8pt;
            color: #4a4a4a;
        }

        /* Seal Area */
        .seal-area {
            text-align: center;
            margin: 15px 0;
            padding: 12px;
            border: 2px dashed #c9b896;
            background: rgba(184, 134, 11, 0.05);
        }

        .seal-placeholder {
            width: 80px;
            height: 80px;
            border: 2px solid #b8860b;
            border-radius: 50%;
            margin: 0 auto 8px;
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            font-size: 7pt;
            color: #b8860b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .seal-text {
            font-size: 8pt;
            color: #4a4a4a;
            font-style: italic;
        }

        /* Witness Section */
        .witness-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #c9b896;
        }

        .witness-title {
            font-size: 10pt;
            font-weight: bold;
            color: #0f3d26;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
            text-align: center;
        }

        .witness-block {
            padding: 8px;
            background: #faf6ed;
            border: 1px dashed #c9b896;
        }

        .witness-number {
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 8pt;
            color: #1a5c3a;
            margin-bottom: 5px;
        }

        /* Footer */
        .document-footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #1a5c3a;
            text-align: center;
            font-size: 8pt;
            color: #4a4a4a;
        }

        .jurisdiction {
            font-style: italic;
            margin-bottom: 5px;
        }

        .page-number {
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 7pt;
            color: #1a5c3a;
        }

    </style>
</head>
<body>
    <div class="document-wrapper">
        <!-- Document Header -->
        <div class="document-header">
            <div class="contract-title">Contract Agreement</div>
            <div class="contract-number">' . htmlspecialchars($C['contractNo']) . '</div>
            <div class="contract-date">Executed on this ' . $contractDate . '</div>
        </div>

        <!-- Parties Section -->
        <div class="parties-section">
            <div class="party-block">
                <div class="party-label">&#9670; First Party (Service Provider)</div>
                <div class="party-name">' . htmlspecialchars($company['name']) . '</div>
                <div class="party-details">
                    A company incorporated under the Companies Act, 2013<br>
                    CIN: ' . htmlspecialchars($company['cin']) . ' | GSTIN: ' . htmlspecialchars($company['gstin']) . '<br>
                    Registered Office: ' . htmlspecialchars($company['address']) . '
                </div>
            </div>

            <div class="party-connector">&mdash; AND &mdash;</div>

            <div class="party-block">
                <div class="party-label">&#9670; Second Party (Client)</div>
                <div class="party-name">' . htmlspecialchars($C['clientName']) . '</div>
                <div class="party-details">
                    Address: ' . htmlspecialchars($C['siteAddress'] ?? '') . '<br>
                    ' . htmlspecialchars($C['siteCity'] ?? '') . ', ' . htmlspecialchars($C['siteState'] ?? '') . '<br>
                    Email: ' . htmlspecialchars($C['clientEmail']) . ' | Phone: ' . htmlspecialchars($C['clientPhone']) . '
                </div>
            </div>
        </div>

        <!-- Recitals -->
        <div class="section-heading">&sect; Recitals</div>
        <div class="whereas-clause">
            <strong>WHEREAS</strong> the First Party is engaged in the business of designing, manufacturing, supplying, and installing professional padel tennis courts and related sports infrastructure;
        </div>
        <div class="whereas-clause">
            <strong>AND WHEREAS</strong> the Second Party has approached the First Party for the design, supply, and installation of padel court(s) at the site specified herein, as per the specifications agreed upon;
        </div>
        <div class="whereas-clause">
            <strong>AND WHEREAS</strong> both parties have agreed to enter into this Contract Agreement on the terms and conditions set forth hereinafter;
        </div>
        <div class="whereas-clause">
            <strong>NOW, THEREFORE</strong>, in consideration of the mutual covenants and agreements herein contained, the parties agree as follows:
        </div>

        <!-- Contract Value -->
        <div class="value-box">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td width="70%">
                        <div class="value-label">Total Contract Value</div>
                        <div class="value-amount">&#8377; ' . number_format($C['contractAmount'], 2) . '</div>
                        <div class="value-words">(' . $amountInWords . ')</div>
                    </td>
                    <td width="30%" style="text-align: right;">
                        <div class="value-badge">
                            <div class="value-label">Advance Payment</div>
                            <div style="font-size: 12pt; font-weight: bold;">&#8377; ' . number_format($C['advanceAmount'] ?? 0, 2) . '</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>';

// Scope of Work
if (!empty($C['scopeOfWork'])) {
    $html .= '
        <div class="section-heading">&sect; Scope of Work</div>
        <div class="terms-content">
            <div class="clause">
                <span class="clause-number">1.</span>
                <strong>Project Description:</strong><br>
                ' . nl2br(htmlspecialchars($C['scopeOfWork'])) . '
            </div>
            <div class="clause">
                <span class="clause-number">2.</span>
                <strong>Configuration:</strong> ' . htmlspecialchars($C['courtConfiguration'] ?? 'As per quotation specifications') . '
            </div>
        </div>';
}

// Payment Schedule
if (count($milestones) > 0) {
    $html .= '
        <div class="section-heading">&sect; Payment Schedule</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th>Milestone</th>
                    <th style="width: 12%; text-align: center;">%</th>
                    <th style="width: 12%; text-align: center;">Due</th>
                    <th style="width: 18%; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($milestones as $i => $m) {
        $html .= '
                <tr>
                    <td style="text-align: center;">' . ($i + 1) . '</td>
                    <td>
                        <strong>' . htmlspecialchars($m['milestoneName']) . '</strong>';
        if (!empty($m['milestoneDescription'])) {
            $html .= '<br><span style="font-size: 8pt; color: #4a4a4a;">' . htmlspecialchars($m['milestoneDescription']) . '</span>';
        }
        $html .= '
                    </td>
                    <td style="text-align: center;">' . $m['paymentPercentage'] . '%</td>
                    <td style="text-align: center;">' . ($m['dueAfterDays'] > 0 ? $m['dueAfterDays'] . ' days' : 'On Order') . '</td>
                    <td style="text-align: right; font-family: DejaVu Sans Mono, monospace;">&#8377; ' . number_format($m['paymentAmount'], 2) . '</td>
                </tr>';
    }

    $html .= '
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Total Contract Value</strong></td>
                    <td style="text-align: right; font-family: DejaVu Sans Mono, monospace;">&#8377; ' . number_format($C['contractAmount'], 2) . '</td>
                </tr>
            </tfoot>
        </table>';
}

// Terms and Conditions
if (!empty($C['termsAndConditions'])) {
    $html .= '
        <div class="section-heading">&sect; Terms &amp; Conditions</div>
        <div class="terms-content">
            ' . nl2br(htmlspecialchars($C['termsAndConditions'])) . '
        </div>';
}

// Payment Terms
if (!empty($C['paymentTerms'])) {
    $html .= '
        <div class="section-heading">&sect; Payment Terms</div>
        <div class="terms-content">
            ' . nl2br(htmlspecialchars($C['paymentTerms'])) . '
        </div>';
}

// Signature Section
$html .= '
        <div class="signature-section">
            <div class="signature-intro">
                <strong>IN WITNESS WHEREOF</strong>, the parties hereto have executed this Agreement as of the date first above written,
                having read, understood, and agreed to all terms and conditions contained herein.
            </div>

            <table class="signature-table" cellpadding="10" cellspacing="0">
                <tr>
                    <td width="50%">
                        <div class="signature-block">
                            <div class="signature-block-title">For &amp; On Behalf of First Party</div>
                            <div class="signature-line"></div>
                            <div class="signature-label">Signature</div>
                            <div class="signature-details">
                                <div class="signature-field">
                                    <span class="signature-field-label">Name:</span>
                                    <span class="signature-field-value"></span>
                                </div>
                                <div class="signature-field">
                                    <span class="signature-field-label">Designation:</span>
                                    <span class="signature-field-value"></span>
                                </div>
                                <div class="signature-field">
                                    <span class="signature-field-label">Date:</span>
                                    <span class="signature-field-value"></span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td width="50%">
                        <div class="signature-block">
                            <div class="signature-block-title">For &amp; On Behalf of Second Party</div>';

if ($C['contractStatus'] == 'Signed' && !empty($C['signedBy'])) {
    $html .= '
                            <div class="signed-name">' . htmlspecialchars($C['signedBy']) . '</div>
                            <div class="signed-meta">
                                Signed on: ' . date('d/m/Y, h:i A', strtotime($C['signedAt'])) . '<br>
                                Verification: ' . htmlspecialchars($C['signatureMethod'] ?? 'OTP Verified') . '<br>
                                IP: ' . htmlspecialchars($C['signatureIP'] ?? '') . '
                            </div>';
} else {
    $html .= '
                            <div class="signature-line"></div>
                            <div class="signature-label">Signature</div>
                            <div class="signature-details">
                                <div class="signature-field">
                                    <span class="signature-field-label">Name:</span>
                                    <span class="signature-field-value">' . htmlspecialchars($C['clientName']) . '</span>
                                </div>
                                <div class="signature-field">
                                    <span class="signature-field-label">Date:</span>
                                    <span class="signature-field-value"></span>
                                </div>
                            </div>';
}

$html .= '
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Seal Area -->
            <div class="seal-area">
                <table width="100%">
                    <tr>
                        <td style="text-align: center;">
                            <div style="width: 80px; height: 80px; border: 2px solid #b8860b; border-radius: 50%; margin: 0 auto 8px; line-height: 80px; font-size: 7pt; color: #b8860b; text-transform: uppercase; letter-spacing: 0.05em;">Company<br>Seal</div>
                            <div class="seal-text">Affix Company Seal / Stamp Here</div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Witness Section -->
            <div class="witness-section">
                <div class="witness-title">Witnesses</div>
                <table width="100%" cellpadding="8" cellspacing="0">
                    <tr>
                        <td width="50%">
                            <div class="witness-block">
                                <div class="witness-number">Witness 1</div>
                                <div class="signature-field">
                                    <span class="signature-field-label">Name:</span>
                                    <span class="signature-field-value"></span>
                                </div>
                                <div class="signature-field">
                                    <span class="signature-field-label">Address:</span>
                                    <span class="signature-field-value"></span>
                                </div>
                                <div class="signature-field">
                                    <span class="signature-field-label">Signature:</span>
                                    <span class="signature-field-value"></span>
                                </div>
                            </div>
                        </td>
                        <td width="50%">
                            <div class="witness-block">
                                <div class="witness-number">Witness 2</div>
                                <div class="signature-field">
                                    <span class="signature-field-label">Name:</span>
                                    <span class="signature-field-value"></span>
                                </div>
                                <div class="signature-field">
                                    <span class="signature-field-label">Address:</span>
                                    <span class="signature-field-value"></span>
                                </div>
                                <div class="signature-field">
                                    <span class="signature-field-label">Signature:</span>
                                    <span class="signature-field-value"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="document-footer">
            <div class="jurisdiction">
                This Agreement shall be governed by and construed in accordance with the laws of India.
                Any disputes arising shall be subject to the exclusive jurisdiction of the courts at Maharashtra.
            </div>
            <div class="page-number">Contract No: ' . htmlspecialchars($C['contractNo']) . ' | Generated: ' . date('d M Y, h:i A') . '</div>
        </div>
    </div>
</body>
</html>';

// Generate PDF
try {
    if (class_exists('Mpdf\Mpdf')) {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'default_font' => 'dejavuserif'
        ]);

        $mpdf->SetTitle('Contract - ' . $C['contractNo']);
        $mpdf->SetAuthor($company['name']);
        $mpdf->SetCreator('Sky Padel India Contract System');

        $mpdf->WriteHTML($html);

        $filename = 'Contract_' . str_replace('/', '-', $C['contractNo']) . '.pdf';
        $mpdf->Output($filename, 'I'); // I = Inline, D = Download
    } else {
        // Fallback: output HTML for browser printing
        echo $html;
    }
} catch (Exception $e) {
    die("Error generating PDF: " . $e->getMessage());
}
?>

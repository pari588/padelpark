<?php
require_once("../../../core/core.inc.php");
require_once("../../inc/site.inc.php");
require_once("../../../vendor/autoload.php");

$id = intval($_GET["id"] ?? 0);
if ($id == 0) {
    die("Invalid proforma ID");
}

// Get proforma details
$DB->vals = array(1, $id);
$DB->types = "ii";
$DB->sql = "SELECT p.*, q.quotationNo
            FROM " . $DB->pre . "sky_padel_proforma_invoice p
            LEFT JOIN " . $DB->pre . "sky_padel_quotation q ON p.quotationID = q.quotationID
            WHERE p.status=? AND p.proformaID=?";
$P = $DB->dbRow();

if (!$P) {
    die("Proforma not found");
}

// Get milestones
$DB->vals = array($id);
$DB->types = "i";
$DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_proforma_milestone WHERE proformaID=? ORDER BY sortOrder";
$milestones = $DB->dbRows();

// Company details (you can make this configurable)
$company = array(
    "name" => "Sky Padel",
    "address" => "123 Sports Complex, Business District",
    "city" => "Mumbai, Maharashtra - 400001",
    "phone" => "+91 98765 43210",
    "email" => "info@skypadel.com",
    "gstin" => "27XXXXX1234X1ZX",
    "pan" => "XXXXX1234X"
);

// Build HTML for PDF
$html = '
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #333;
    }
    .header-table {
        width: 100%;
        border-bottom: 3px solid #f59e0b;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .company-name {
        color: #f59e0b;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .company-details {
        color: #666;
        font-size: 10px;
    }
    .invoice-title {
        text-align: right;
        font-size: 20px;
        font-weight: bold;
        color: #333;
    }
    .invoice-no {
        font-size: 14px;
        color: #f59e0b;
        font-weight: bold;
    }
    .meta-table {
        width: 100%;
        margin-bottom: 20px;
    }
    .meta-box {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 5px;
    }
    .meta-title {
        color: #f59e0b;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: bold;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
        margin-bottom: 8px;
    }
    .section-title {
        background: #f59e0b;
        color: #fff;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: bold;
        margin: 15px 0 10px 0;
    }
    .totals-table {
        width: 250px;
        margin-left: auto;
        margin-bottom: 15px;
    }
    .totals-table td {
        padding: 6px 8px;
        border-bottom: 1px solid #eee;
    }
    .totals-table .label {
        text-align: right;
        color: #666;
    }
    .totals-table .value {
        text-align: right;
        font-weight: bold;
    }
    .grand-total {
        background: #f59e0b;
        color: #fff;
    }
    .grand-total td {
        font-size: 12px;
        padding: 10px 8px !important;
    }
    .amount-words {
        background: #f8f9fa;
        padding: 10px 12px;
        border-left: 4px solid #f59e0b;
        margin-bottom: 15px;
        font-style: italic;
    }
    .milestones-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    .milestones-table th {
        background: #6c757d;
        color: #fff;
        padding: 8px;
        text-align: left;
        font-size: 10px;
    }
    .milestones-table td {
        padding: 8px;
        border-bottom: 1px solid #eee;
    }
    .bank-details {
        background: #e8f4fd;
        padding: 12px;
        border: 1px solid #b8daff;
        border-radius: 5px;
        margin-top: 15px;
    }
    .bank-title {
        color: #004085;
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 8px;
    }
    .terms-section {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 5px;
        margin-top: 15px;
    }
    .terms-title {
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 8px;
    }
    .signature-table {
        width: 100%;
        margin-top: 40px;
        border-top: 1px solid #ddd;
        padding-top: 15px;
    }
    .signature-box {
        width: 180px;
        text-align: center;
    }
    .signature-line {
        border-top: 1px solid #333;
        margin-top: 40px;
        padding-top: 5px;
        font-size: 10px;
    }
</style>

<!-- Header -->
<table class="header-table" cellpadding="0" cellspacing="0">
    <tr>
        <td width="60%" valign="top">
            <div class="company-name">' . htmlspecialchars($company["name"]) . '</div>
            <div class="company-details">
                ' . htmlspecialchars($company["address"]) . '<br>
                ' . htmlspecialchars($company["city"]) . '<br>
                Phone: ' . htmlspecialchars($company["phone"]) . ' | Email: ' . htmlspecialchars($company["email"]) . '<br>
                GSTIN: ' . htmlspecialchars($company["gstin"]) . ' | PAN: ' . htmlspecialchars($company["pan"]) . '
            </div>
        </td>
        <td width="40%" valign="top" align="right">
            <div class="invoice-title">PROFORMA INVOICE</div>
            <div class="invoice-no">' . htmlspecialchars($P["proformaNo"]) . '</div>
        </td>
    </tr>
</table>

<!-- Bill To and Invoice Details -->
<table class="meta-table" cellpadding="0" cellspacing="0">
    <tr>
        <td width="48%" valign="top">
            <div class="meta-box">
                <div class="meta-title">Bill To</div>
                <strong>' . htmlspecialchars($P["clientName"]) . '</strong><br>';

if (!empty($P["clientCompany"])) {
    $html .= htmlspecialchars($P["clientCompany"]) . '<br>';
}
if (!empty($P["clientAddress"])) {
    $html .= htmlspecialchars($P["clientAddress"]) . '<br>';
}
$cityStatePin = trim(($P["clientCity"] ?? "") . ", " . ($P["clientState"] ?? "") . " - " . ($P["clientPincode"] ?? ""), ", - ");
if (!empty($cityStatePin)) {
    $html .= htmlspecialchars($cityStatePin) . '<br>';
}
if (!empty($P["clientPhone"])) {
    $html .= 'Phone: ' . htmlspecialchars($P["clientPhone"]) . '<br>';
}
if (!empty($P["clientEmail"])) {
    $html .= 'Email: ' . htmlspecialchars($P["clientEmail"]) . '<br>';
}
if (!empty($P["clientGSTIN"])) {
    $html .= 'GSTIN: ' . htmlspecialchars($P["clientGSTIN"]);
}

$html .= '
            </div>
        </td>
        <td width="4%">&nbsp;</td>
        <td width="48%" valign="top">
            <div class="meta-box">
                <div class="meta-title">Invoice Details</div>
                <strong>Invoice Date:</strong> ' . date("d-M-Y", strtotime($P["invoiceDate"])) . '<br>
                <strong>Valid Until:</strong> ' . date("d-M-Y", strtotime($P["validUntil"])) . '<br>';

if (!empty($P["quotationNo"])) {
    $html .= '<strong>Quotation Ref:</strong> ' . htmlspecialchars($P["quotationNo"]) . '<br>';
}
$html .= '<strong>Status:</strong> ' . htmlspecialchars($P["invoiceStatus"]);
if (!empty($P["courtConfiguration"])) {
    $html .= '<br><strong>Configuration:</strong> ' . htmlspecialchars($P["courtConfiguration"]);
}

$html .= '
            </div>
        </td>
    </tr>
</table>';

// Scope of Work
if (!empty($P["scopeOfWork"])) {
    $html .= '
    <div class="section-title">Scope of Work</div>
    <div style="padding: 12px; background: #f8f9fa;">
        ' . nl2br(htmlspecialchars($P["scopeOfWork"])) . '
    </div>';
}

// Financial Summary
$html .= '
<div class="section-title">Financial Summary</div>
<table class="totals-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="label">Subtotal:</td>
        <td class="value">Rs. ' . number_format($P["subtotal"], 2) . '</td>
    </tr>';

if (($P["discountAmount"] ?? 0) > 0) {
    $html .= '
    <tr>
        <td class="label">Discount:</td>
        <td class="value">- Rs. ' . number_format($P["discountAmount"], 2) . '</td>
    </tr>';
}

$html .= '
    <tr>
        <td class="label">CGST (' . ($P["cgstRate"] ?? 9) . '%):</td>
        <td class="value">Rs. ' . number_format($P["cgstAmount"] ?? 0, 2) . '</td>
    </tr>
    <tr>
        <td class="label">SGST (' . ($P["sgstRate"] ?? 9) . '%):</td>
        <td class="value">Rs. ' . number_format($P["sgstAmount"] ?? 0, 2) . '</td>
    </tr>
    <tr class="grand-total">
        <td class="label">Total Amount:</td>
        <td class="value">Rs. ' . number_format($P["totalAmount"], 2) . '</td>
    </tr>
</table>

<div class="amount-words">
    <strong>Amount in Words:</strong> ' . htmlspecialchars($P["amountInWords"] ?? "") . '
</div>';

// Payment Milestones
if (count($milestones) > 0) {
    $html .= '
    <div class="section-title">Payment Schedule</div>
    <table class="milestones-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">Milestone</th>
                <th width="30%">Description</th>
                <th width="12%">Percentage</th>
                <th width="15%">Amount</th>
                <th width="13%">Due After</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($milestones as $idx => $m) {
        $html .= '
            <tr>
                <td>' . ($idx + 1) . '</td>
                <td>' . htmlspecialchars($m["milestoneName"]) . '</td>
                <td>' . htmlspecialchars($m["milestoneDescription"] ?? "") . '</td>
                <td>' . $m["paymentPercentage"] . '%</td>
                <td>Rs. ' . number_format($m["paymentAmount"], 2) . '</td>
                <td>' . $m["dueAfterDays"] . ' days</td>
            </tr>';
    }

    $html .= '
        </tbody>
    </table>';
}

// Bank Details
if (!empty($P["bankDetails"])) {
    $html .= '
    <div class="bank-details">
        <div class="bank-title">Bank Details for Payment</div>
        ' . nl2br(htmlspecialchars($P["bankDetails"])) . '
    </div>';
}

// Terms and Conditions
if (!empty($P["termsAndConditions"]) || !empty($P["paymentTerms"])) {
    $html .= '
    <div class="terms-section">
        <div class="terms-title">Terms & Conditions</div>';

    if (!empty($P["paymentTerms"])) {
        $html .= '<strong>Payment Terms:</strong><br>' . nl2br(htmlspecialchars($P["paymentTerms"])) . '<br><br>';
    }
    if (!empty($P["termsAndConditions"])) {
        $html .= nl2br(htmlspecialchars($P["termsAndConditions"]));
    }

    $html .= '</div>';
}

// Signature Section
$html .= '
<table class="signature-table" cellpadding="0" cellspacing="0">
    <tr>
        <td width="50%" valign="bottom">
            <div class="signature-box">
                <div class="signature-line">Client Signature</div>
            </div>
        </td>
        <td width="50%" valign="bottom" align="right">
            <div class="signature-box">
                <div class="signature-line">For ' . htmlspecialchars($company["name"]) . '</div>
            </div>
        </td>
    </tr>
</table>';

// Generate PDF using mPDF
try {
    $mpdf = new \Mpdf\Mpdf([
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15,
        'format' => 'A4'
    ]);

    $mpdf->SetTitle('Proforma Invoice - ' . $P["proformaNo"]);
    $mpdf->SetAuthor('Sky Padel');
    $mpdf->SetCreator('Sky Padel Admin');

    $mpdf->WriteHTML($html);

    // Output the PDF
    $filename = 'Proforma_Invoice_' . $P["proformaNo"] . '.pdf';
    $mpdf->Output($filename, 'D'); // D = Download

} catch (Exception $e) {
    die("Error generating PDF: " . $e->getMessage());
}

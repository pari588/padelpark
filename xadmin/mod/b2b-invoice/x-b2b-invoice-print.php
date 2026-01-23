<?php
require_once("../../../core/core.inc.php");
require_once("../../inc/site.inc.php");

$id = intval($_GET["id"] ?? 0);
$download = isset($_GET["download"]) && $_GET["download"] == 1;

if ($id <= 0) {
    die("Invalid invoice ID");
}

// Get invoice data
$DB->vals = array(1, $id);
$DB->types = "ii";
$DB->sql = "SELECT i.*, d.companyName, d.distributorCode, d.gstin as distGstin, d.billingAddress as distAddress,
                   d.billingCity, d.billingState, d.billingPincode, d.mobile, d.phone, d.email,
                   w.warehouseName, w.addressLine1 as warehouseAddress, w.city as whCity, w.state as whState, w.pincode as whPincode
            FROM " . $DB->pre . "b2b_invoice i
            LEFT JOIN " . $DB->pre . "distributor d ON i.distributorID = d.distributorID
            LEFT JOIN " . $DB->pre . "warehouse w ON i.warehouseID = w.warehouseID
            WHERE i.status=? AND i.invoiceID=?";
$D = $DB->dbRow();

if (!$D) {
    die("Invoice not found");
}

// Get items
$DB->vals = array($id, 1);
$DB->types = "ii";
$DB->sql = "SELECT * FROM " . $DB->pre . "b2b_invoice_item WHERE invoiceID=? AND status=?";
$items = $DB->dbRows();

// Get company settings
$DB->sql = "SELECT settingKey, settingVal FROM " . $DB->pre . "x_setting WHERE settingKey IN ('companyName', 'companyAddress', 'companyGSTIN', 'companyPhone', 'companyEmail', 'bankName', 'bankAccountNo', 'bankIFSC', 'bankBranch', 'siteTitle')";
$settingsRows = $DB->dbRows();
$settings = array();
foreach ($settingsRows as $s) {
    $settings[$s["settingKey"]] = $s["settingVal"];
}
if (empty($settings["companyName"]) && !empty($settings["siteTitle"])) {
    $settings["companyName"] = $settings["siteTitle"];
}

// Number to words function
function numberToWords($num) {
    $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');

    if ($num == 0) return 'Zero';

    $num = number_format($num, 2, '.', '');
    $parts = explode('.', $num);
    $rupees = intval($parts[0]);
    $paise = intval($parts[1] ?? 0);

    $words = '';

    if ($rupees >= 10000000) {
        $words .= numberToWords(floor($rupees / 10000000)) . ' Crore ';
        $rupees %= 10000000;
    }
    if ($rupees >= 100000) {
        $words .= numberToWords(floor($rupees / 100000)) . ' Lakh ';
        $rupees %= 100000;
    }
    if ($rupees >= 1000) {
        $words .= numberToWords(floor($rupees / 1000)) . ' Thousand ';
        $rupees %= 1000;
    }
    if ($rupees >= 100) {
        $words .= $ones[floor($rupees / 100)] . ' Hundred ';
        $rupees %= 100;
    }
    if ($rupees >= 20) {
        $words .= $tens[floor($rupees / 10)] . ' ';
        $rupees %= 10;
    }
    if ($rupees > 0) {
        $words .= $ones[$rupees] . ' ';
    }

    $words = trim($words) . ' Rupees';

    if ($paise > 0) {
        $words .= ' and ';
        if ($paise >= 20) {
            $words .= $tens[floor($paise / 10)] . ' ';
            $paise %= 10;
        }
        if ($paise > 0) {
            $words .= $ones[$paise] . ' ';
        }
        $words .= 'Paise';
    }

    return trim($words) . ' Only';
}

$amountInWords = numberToWords($D["totalAmount"]);
$companyName = $settings["companyName"] ?? "Sky Padel";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo $D["invoiceNo"]; ?> | <?php echo $companyName; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0a1628;
            --navy-light: #1a2d4a;
            --gold: #c9a962;
            --gold-dark: #b8860b;
            --gold-light: #e8d5a3;
            --cream: #faf8f5;
            --ivory: #f5f0e8;
            --charcoal: #2d3436;
            --slate: #636e72;
            --border: #e0dcd4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: var(--charcoal);
            background: var(--cream);
            -webkit-font-smoothing: antialiased;
        }

        .invoice-page {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            position: relative;
            box-shadow: 0 4px 60px rgba(10, 22, 40, 0.1);
        }

        /* Decorative border */
        .invoice-page::before {
            content: '';
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            bottom: 12px;
            border: 1px solid var(--gold-light);
            pointer-events: none;
            z-index: 1;
        }

        .invoice-content {
            padding: 40px 50px;
            position: relative;
            z-index: 2;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-family: 'Cormorant Garamond', serif;
            font-size: 120px;
            font-weight: 600;
            color: rgba(201, 169, 98, 0.04);
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
            letter-spacing: 20px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 30px;
            border-bottom: 2px solid var(--navy);
            margin-bottom: 30px;
            position: relative;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--gold-light));
        }

        .brand {
            flex: 1;
        }

        .brand-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            font-weight: 600;
            color: var(--navy);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .brand-tagline {
            font-size: 10px;
            color: var(--gold-dark);
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 500;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 14px;
            font-weight: 400;
            color: var(--slate);
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .invoice-number {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px;
            font-weight: 600;
            color: var(--navy);
            letter-spacing: 1px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            border-radius: 2px;
            margin-top: 10px;
        }

        .status-paid {
            background: linear-gradient(135deg, #1a472a, #2d5a3d);
            color: #d4edda;
        }

        .status-pending {
            background: linear-gradient(135deg, var(--gold-dark), var(--gold));
            color: #fff;
        }

        .status-overdue {
            background: linear-gradient(135deg, #721c24, #a02a33);
            color: #f8d7da;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-bottom: 35px;
        }

        .info-block {
            position: relative;
        }

        .info-block::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 30px;
            height: 2px;
            background: var(--gold);
        }

        .info-label {
            font-size: 9px;
            font-weight: 600;
            color: var(--gold-dark);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
            padding-top: 12px;
        }

        .info-company {
            font-family: 'Cormorant Garamond', serif;
            font-size: 16px;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 6px;
        }

        .info-text {
            color: var(--slate);
            line-height: 1.7;
        }

        .info-text strong {
            color: var(--charcoal);
            font-weight: 600;
        }

        .info-date {
            font-family: 'Cormorant Garamond', serif;
            font-size: 15px;
            font-weight: 500;
            color: var(--navy);
        }

        /* Items Table */
        .items-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 12px;
            font-weight: 600;
            color: var(--navy);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table thead th {
            background: var(--navy);
            color: #fff;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 14px 12px;
            text-align: left;
        }

        .items-table thead th:first-child {
            border-radius: 3px 0 0 0;
        }

        .items-table thead th:last-child {
            border-radius: 0 3px 0 0;
        }

        .items-table thead th.text-center {
            text-align: center;
        }

        .items-table thead th.text-right {
            text-align: right;
        }

        .items-table tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .items-table tbody tr:hover {
            background: var(--ivory);
        }

        .items-table tbody tr:last-child td {
            border-bottom: 2px solid var(--navy);
        }

        .product-sku {
            font-weight: 600;
            color: var(--navy);
            font-size: 11px;
        }

        .product-name {
            color: var(--slate);
            font-size: 10px;
            margin-top: 2px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .amount {
            font-weight: 600;
            color: var(--navy);
        }

        /* Totals */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 25px;
        }

        .totals-table {
            width: 280px;
        }

        .totals-table tr td {
            padding: 8px 0;
        }

        .totals-table tr td:first-child {
            color: var(--slate);
            font-size: 11px;
        }

        .totals-table tr td:last-child {
            text-align: right;
            font-weight: 500;
            color: var(--charcoal);
        }

        .totals-table .divider td {
            border-top: 1px solid var(--border);
            padding-top: 12px;
        }

        .totals-table .grand-total td {
            padding-top: 15px;
            border-top: 2px solid var(--navy);
        }

        .totals-table .grand-total td:first-child {
            font-family: 'Cormorant Garamond', serif;
            font-size: 14px;
            font-weight: 600;
            color: var(--navy);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .totals-table .grand-total td:last-child {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--navy);
        }

        /* Amount in Words */
        .amount-words {
            background: linear-gradient(135deg, var(--ivory), var(--cream));
            padding: 18px 24px;
            border-left: 3px solid var(--gold);
            margin-bottom: 30px;
        }

        .amount-words-label {
            font-size: 9px;
            font-weight: 600;
            color: var(--gold-dark);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .amount-words-text {
            font-family: 'Cormorant Garamond', serif;
            font-size: 14px;
            font-weight: 500;
            color: var(--navy);
            font-style: italic;
        }

        /* Bank Details */
        .bank-section {
            background: var(--navy);
            color: #fff;
            padding: 25px 30px;
            margin: 0 -50px 30px;
            position: relative;
        }

        .bank-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50px;
            width: 50px;
            height: 3px;
            background: var(--gold);
        }

        .bank-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--gold-light);
            margin-bottom: 18px;
        }

        .bank-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .bank-item-label {
            font-size: 9px;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .bank-item-value {
            font-size: 12px;
            font-weight: 500;
            color: #fff;
        }

        /* Footer */
        .footer {
            display: grid;
            grid-template-columns: 1fr 200px;
            gap: 40px;
            padding-top: 25px;
            border-top: 1px solid var(--border);
        }

        .terms-title {
            font-size: 9px;
            font-weight: 600;
            color: var(--navy);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .terms-list {
            list-style: none;
            counter-reset: terms;
        }

        .terms-list li {
            counter-increment: terms;
            font-size: 10px;
            color: var(--slate);
            line-height: 1.8;
            padding-left: 20px;
            position: relative;
        }

        .terms-list li::before {
            content: counter(terms) ".";
            position: absolute;
            left: 0;
            color: var(--gold-dark);
            font-weight: 600;
        }

        .signature-block {
            text-align: center;
            padding-top: 20px;
        }

        .signature-line {
            width: 100%;
            height: 1px;
            background: var(--navy);
            margin-top: 60px;
            margin-bottom: 10px;
        }

        .signature-label {
            font-size: 10px;
            color: var(--slate);
        }

        .signature-company {
            font-family: 'Cormorant Garamond', serif;
            font-size: 12px;
            font-weight: 600;
            color: var(--navy);
            margin-top: 4px;
        }

        /* Action Buttons */
        .actions {
            text-align: center;
            padding: 30px;
            background: var(--cream);
        }

        .btn {
            display: inline-block;
            padding: 14px 32px;
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 8px;
        }

        .btn-primary {
            background: var(--navy);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--navy-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(10, 22, 40, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: var(--navy);
            border: 1px solid var(--navy);
        }

        .btn-secondary:hover {
            background: var(--navy);
            color: #fff;
        }

        /* Print Styles */
        @media print {
            body {
                background: #fff;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .invoice-page {
                box-shadow: none;
                max-width: 100%;
            }

            .invoice-page::before {
                border-color: var(--gold-light) !important;
            }

            .actions {
                display: none !important;
            }

            .watermark {
                display: none;
            }

            .header::after,
            .info-block::before,
            .bank-section::before {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .items-table thead th {
                background: var(--navy) !important;
                color: #fff !important;
            }

            .bank-section {
                background: var(--navy) !important;
                color: #fff !important;
                margin: 0 -50px 30px;
            }

            .status-badge {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        @page {
            size: A4;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="actions no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <div class="invoice-page">
        <div class="watermark">INVOICE</div>

        <div class="invoice-content">
            <!-- Header -->
            <header class="header">
                <div class="brand">
                    <div class="brand-name"><?php echo $companyName; ?></div>
                    <div class="brand-tagline">Official Bullpadel Distributor</div>
                </div>
                <div class="invoice-meta">
                    <div class="invoice-title">Tax Invoice</div>
                    <div class="invoice-number"><?php echo $D["invoiceNo"]; ?></div>
                    <?php
                    $statusClass = "status-pending";
                    if ($D["invoiceStatus"] == "Paid") $statusClass = "status-paid";
                    elseif ($D["invoiceStatus"] == "Overdue") $statusClass = "status-overdue";
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo $D["invoiceStatus"]; ?></span>
                </div>
            </header>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-block">
                    <div class="info-label">Bill To</div>
                    <div class="info-company"><?php echo $D["companyName"]; ?></div>
                    <div class="info-text">
                        <?php echo $D["distAddress"]; ?><br>
                        <?php echo $D["billingCity"]; ?>, <?php echo $D["billingState"]; ?> - <?php echo $D["billingPincode"]; ?>
                        <?php if ($D["distGstin"]): ?>
                        <br><strong>GSTIN:</strong> <?php echo $D["distGstin"]; ?>
                        <?php endif; ?>
                        <?php if ($D["mobile"]): ?>
                        <br><strong>Phone:</strong> <?php echo $D["mobile"]; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-block">
                    <div class="info-label">Invoice Date</div>
                    <div class="info-date"><?php echo date("d F Y", strtotime($D["invoiceDate"])); ?></div>
                    <div class="info-label" style="margin-top: 20px;">Due Date</div>
                    <div class="info-date"><?php echo date("d F Y", strtotime($D["dueDate"])); ?></div>
                </div>
                <div class="info-block">
                    <div class="info-label">Ship From</div>
                    <div class="info-company"><?php echo $D["warehouseName"]; ?></div>
                    <?php if ($D["warehouseAddress"]): ?>
                    <div class="info-text">
                        <?php echo $D["warehouseAddress"]; ?><br>
                        <?php echo $D["whCity"]; ?>, <?php echo $D["whState"]; ?> <?php echo $D["whPincode"]; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Items -->
            <div class="items-section">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 32%;">Description</th>
                            <th style="width: 10%;" class="text-center">HSN</th>
                            <th style="width: 10%;" class="text-center">Qty</th>
                            <th style="width: 15%;" class="text-right">Rate</th>
                            <th style="width: 10%;" class="text-center">GST</th>
                            <th style="width: 18%;" class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sn = 0;
                        foreach ($items as $item):
                            $sn++;
                        ?>
                        <tr>
                            <td><?php echo str_pad($sn, 2, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <div class="product-sku"><?php echo $item["productSKU"]; ?></div>
                                <div class="product-name"><?php echo $item["productName"]; ?></div>
                            </td>
                            <td class="text-center"><?php echo $item["hsnCode"] ?? "—"; ?></td>
                            <td class="text-center"><?php echo number_format($item["quantity"], 0); ?> <?php echo $item["uom"]; ?></td>
                            <td class="text-right"><?php echo number_format($item["unitPrice"], 2); ?></td>
                            <td class="text-center"><?php echo ($item["gstRate"] ?? 18); ?>%</td>
                            <td class="text-right amount"><?php echo number_format($item["totalAmount"], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td>Subtotal</td>
                        <td><?php echo number_format($D["subtotal"], 2); ?></td>
                    </tr>
                    <?php if ($D["discountAmount"] > 0): ?>
                    <tr>
                        <td>Discount</td>
                        <td>- <?php echo number_format($D["discountAmount"], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($D["cgstAmount"] > 0): ?>
                    <tr class="divider">
                        <td>CGST</td>
                        <td><?php echo number_format($D["cgstAmount"], 2); ?></td>
                    </tr>
                    <tr>
                        <td>SGST</td>
                        <td><?php echo number_format($D["sgstAmount"], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($D["igstAmount"] > 0): ?>
                    <tr class="divider">
                        <td>IGST</td>
                        <td><?php echo number_format($D["igstAmount"], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="grand-total">
                        <td>Total</td>
                        <td>₹<?php echo number_format($D["totalAmount"], 2); ?></td>
                    </tr>
                </table>
            </div>

            <!-- Amount in Words -->
            <div class="amount-words">
                <div class="amount-words-label">Amount in Words</div>
                <div class="amount-words-text"><?php echo $amountInWords; ?></div>
            </div>

            <!-- Bank Details -->
            <div class="bank-section">
                <div class="bank-title">Bank Details for Payment</div>
                <div class="bank-grid">
                    <div>
                        <div class="bank-item-label">Bank Name</div>
                        <div class="bank-item-value"><?php echo $settings["bankName"] ?? "HDFC Bank"; ?></div>
                    </div>
                    <div>
                        <div class="bank-item-label">Account Number</div>
                        <div class="bank-item-value"><?php echo $settings["bankAccountNo"] ?? "—"; ?></div>
                    </div>
                    <div>
                        <div class="bank-item-label">IFSC Code</div>
                        <div class="bank-item-value"><?php echo $settings["bankIFSC"] ?? "—"; ?></div>
                    </div>
                    <div>
                        <div class="bank-item-label">Branch</div>
                        <div class="bank-item-value"><?php echo $settings["bankBranch"] ?? "—"; ?></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <div class="terms">
                    <div class="terms-title">Terms & Conditions</div>
                    <ul class="terms-list">
                        <li>Payment is due within <?php echo $D["creditDays"] ?? 30; ?> days from invoice date.</li>
                        <li>Please quote invoice number when making payment.</li>
                        <li>All disputes are subject to jurisdiction of local courts.</li>
                        <li>E. & O.E. — Errors and Omissions Excepted.</li>
                    </ul>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Authorized Signatory</div>
                    <div class="signature-company"><?php echo $companyName; ?></div>
                </div>
            </footer>
        </div>
    </div>

    <?php if ($download): ?>
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
    <?php endif; ?>
</body>
</html>

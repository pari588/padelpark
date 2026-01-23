<?php
/**
 * PNP Invoice Print/View Page
 */
require_once("../../../core/core.inc.php");
require_once("../../inc/site.inc.php");

$invoiceID = intval($_GET['id'] ?? 0);
if (!$invoiceID) {
    die('Invalid invoice ID');
}

// Get invoice details
$DB->vals = array($invoiceID);
$DB->types = "i";
$DB->sql = "SELECT i.*, l.locationName, l.address as locationAddress, l.city as locationCity, l.gstNo as locationGST
            FROM " . $DB->pre . "pnp_invoice i
            LEFT JOIN " . $DB->pre . "pnp_location l ON i.locationID = l.locationID
            WHERE i.invoiceID = ?";
$invoice = $DB->dbRow();

if (!$invoice) {
    die('Invoice not found');
}

// Get sale items if linked to a retail sale
$items = [];
if ($invoice['saleID']) {
    $DB->vals = array($invoice['saleID']);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "pnp_retail_sale_item WHERE saleID = ? AND status = 1";
    $items = $DB->dbRows();
}

$download = isset($_GET['download']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo htmlspecialchars($invoice['invoiceNo']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #333; background: #f5f5f5; }
        .invoice-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 2px solid #1a5f7a; padding-bottom: 20px; }
        .company-info h1 { font-size: 24px; color: #1a5f7a; margin-bottom: 5px; }
        .company-info p { color: #666; line-height: 1.5; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { font-size: 28px; color: #1a5f7a; margin-bottom: 10px; }
        .invoice-title .invoice-no { font-size: 14px; color: #666; }
        .invoice-meta { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .meta-box { background: #f8f9fa; padding: 15px; border-radius: 5px; width: 48%; }
        .meta-box h3 { font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .meta-box p { line-height: 1.6; }
        .meta-box strong { color: #1a5f7a; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #1a5f7a; color: #fff; padding: 12px 10px; text-align: left; font-weight: 500; }
        .items-table th:last-child, .items-table td:last-child { text-align: right; }
        .items-table td { padding: 12px 10px; border-bottom: 1px solid #eee; }
        .items-table tr:nth-child(even) { background: #f9f9f9; }
        .totals { margin-left: auto; width: 300px; }
        .totals table { width: 100%; }
        .totals td { padding: 8px 10px; }
        .totals tr:last-child { background: #1a5f7a; color: #fff; font-weight: bold; font-size: 14px; }
        .totals .label { text-align: left; }
        .totals .value { text-align: right; }
        .payment-info { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0; display: flex; justify-content: space-between; }
        .payment-info.pending { background: #fff3e0; }
        .payment-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-paid { background: #28a745; color: #fff; }
        .badge-pending { background: #ffc107; color: #000; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 11px; }
        .print-btn { position: fixed; top: 20px; right: 20px; background: #1a5f7a; color: #fff; border: none; padding: 12px 25px; cursor: pointer; border-radius: 5px; font-size: 14px; }
        .print-btn:hover { background: #0d4a5f; }
        @media print {
            body { background: #fff; }
            .invoice-container { box-shadow: none; margin: 0; padding: 20px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()"><i class="fa fa-print"></i> Print Invoice</button>

    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <h1>Pay N Play</h1>
                <p>
                    <?php echo htmlspecialchars($invoice['locationName'] ?? 'Location'); ?><br>
                    <?php if ($invoice['locationAddress']): ?>
                        <?php echo htmlspecialchars($invoice['locationAddress']); ?><br>
                    <?php endif; ?>
                    <?php if ($invoice['locationCity']): ?>
                        <?php echo htmlspecialchars($invoice['locationCity']); ?><br>
                    <?php endif; ?>
                    <?php if ($invoice['locationGST']): ?>
                        GSTIN: <?php echo htmlspecialchars($invoice['locationGST']); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="invoice-title">
                <h2>TAX INVOICE</h2>
                <div class="invoice-no">
                    <strong>#<?php echo htmlspecialchars($invoice['invoiceNo']); ?></strong><br>
                    Date: <?php echo date('d-M-Y', strtotime($invoice['invoiceDate'])); ?>
                </div>
            </div>
        </div>

        <div class="invoice-meta">
            <div class="meta-box">
                <h3>Bill To</h3>
                <p>
                    <strong><?php echo htmlspecialchars($invoice['customerName'] ?: 'Walk-in Customer'); ?></strong><br>
                    <?php if ($invoice['customerPhone']): ?>
                        Phone: <?php echo htmlspecialchars($invoice['customerPhone']); ?><br>
                    <?php endif; ?>
                    <?php if ($invoice['customerEmail']): ?>
                        Email: <?php echo htmlspecialchars($invoice['customerEmail']); ?><br>
                    <?php endif; ?>
                    <?php if ($invoice['customerGSTIN']): ?>
                        GSTIN: <?php echo htmlspecialchars($invoice['customerGSTIN']); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="meta-box">
                <h3>Invoice Details</h3>
                <p>
                    <strong>Invoice Type:</strong> <?php echo htmlspecialchars($invoice['invoiceType']); ?><br>
                    <strong>Payment Method:</strong> <?php echo htmlspecialchars($invoice['paymentMethod']); ?><br>
                    <?php if ($invoice['paymentReference']): ?>
                        <strong>Reference:</strong> <?php echo htmlspecialchars($invoice['paymentReference']); ?><br>
                    <?php endif; ?>
                    <strong>Status:</strong> <span class="payment-badge badge-<?php echo strtolower($invoice['paymentStatus']); ?>"><?php echo $invoice['paymentStatus']; ?></span>
                </p>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="40%">Description</th>
                    <th width="15%">SKU</th>
                    <th width="10%">Qty</th>
                    <th width="15%">Unit Price</th>
                    <th width="15%">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php $i = 1; foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($item['productName']); ?></td>
                        <td><?php echo htmlspecialchars($item['productSKU']); ?></td>
                        <td><?php echo number_format($item['quantity'], 0); ?></td>
                        <td>Rs. <?php echo number_format($item['unitPrice'], 2); ?></td>
                        <td>Rs. <?php echo number_format($item['totalAmount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>1</td>
                        <td><?php echo htmlspecialchars($invoice['invoiceType']); ?> - <?php echo htmlspecialchars($invoice['notes'] ?: 'Service/Product'); ?></td>
                        <td>-</td>
                        <td>1</td>
                        <td>Rs. <?php echo number_format($invoice['subtotal'], 2); ?></td>
                        <td>Rs. <?php echo number_format($invoice['subtotal'], 2); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value">Rs. <?php echo number_format($invoice['subtotal'], 2); ?></td>
                </tr>
                <?php if ($invoice['discountAmount'] > 0): ?>
                <tr>
                    <td class="label">Discount</td>
                    <td class="value">- Rs. <?php echo number_format($invoice['discountAmount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="label">CGST (<?php echo $invoice['cgstRate']; ?>%)</td>
                    <td class="value">Rs. <?php echo number_format($invoice['cgstAmount'], 2); ?></td>
                </tr>
                <tr>
                    <td class="label">SGST (<?php echo $invoice['sgstRate']; ?>%)</td>
                    <td class="value">Rs. <?php echo number_format($invoice['sgstAmount'], 2); ?></td>
                </tr>
                <tr>
                    <td class="label">Total Amount</td>
                    <td class="value">Rs. <?php echo number_format($invoice['totalAmount'], 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="payment-info <?php echo $invoice['paymentStatus'] == 'Pending' ? 'pending' : ''; ?>">
            <div>
                <strong>Amount Paid:</strong> Rs. <?php echo number_format($invoice['paidAmount'], 2); ?>
            </div>
            <div>
                <strong>Balance Due:</strong> Rs. <?php echo number_format(max(0, $invoice['totalAmount'] - $invoice['paidAmount']), 2); ?>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p style="margin-top:5px;">This is a computer generated invoice and does not require signature.</p>
        </div>
    </div>

    <?php if ($download): ?>
    <script>window.print();</script>
    <?php endif; ?>
</body>
</html>

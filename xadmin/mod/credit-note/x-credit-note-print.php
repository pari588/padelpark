<?php
/**
 * Credit Note Print/View Page
 */
require_once("../../../core/core.inc.php");
require_once("../../inc/site.inc.php");

$creditNoteID = intval($_GET['id'] ?? 0);
if (!$creditNoteID) {
    die('Invalid Credit Note ID');
}

// Get credit note details
$DB->vals = array($creditNoteID);
$DB->types = "i";
$DB->sql = "SELECT cn.*, w.warehouseName
            FROM " . $DB->pre . "credit_note cn
            LEFT JOIN " . $DB->pre . "warehouse w ON cn.warehouseID = w.warehouseID
            WHERE cn.creditNoteID = ?";
$cn = $DB->dbRow();

if (!$cn) {
    die('Credit Note not found');
}

// Get items
$DB->vals = array($creditNoteID, 1);
$DB->types = "ii";
$DB->sql = "SELECT * FROM " . $DB->pre . "credit_note_item WHERE creditNoteID = ? AND status = ?";
$items = $DB->dbRows();

// Get company settings
$DB->sql = "SELECT settingKey, settingVal FROM " . $DB->pre . "x_setting WHERE settingKey IN ('companyName', 'companyAddress', 'companyGSTIN', 'companyPhone', 'companyEmail', 'PAGETITLE') AND status = 1";
$settingsRows = $DB->dbRows();
$settings = array();
foreach ($settingsRows as $s) {
    $settings[$s["settingKey"]] = $s["settingVal"];
}
$companyName = $settings["companyName"] ?? $settings["PAGETITLE"] ?? 'Company Name';
$companyAddress = $settings["companyAddress"] ?? '';
$companyGST = $settings["companyGSTIN"] ?? '';

$download = isset($_GET['download']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Note #<?php echo htmlspecialchars($cn['creditNoteNo']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #333; background: #f5f5f5; }
        .cn-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .cn-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 3px solid #dc3545; padding-bottom: 20px; }
        .company-info h1 { font-size: 24px; color: #dc3545; margin-bottom: 5px; }
        .company-info p { color: #666; line-height: 1.5; }
        .cn-title { text-align: right; }
        .cn-title h2 { font-size: 28px; color: #dc3545; margin-bottom: 10px; }
        .cn-title .cn-no { font-size: 14px; color: #666; }
        .cn-meta { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .meta-box { background: #f8f9fa; padding: 15px; border-radius: 5px; width: 48%; }
        .meta-box h3 { font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .meta-box p { line-height: 1.6; }
        .meta-box strong { color: #dc3545; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #dc3545; color: #fff; padding: 12px 10px; text-align: left; font-weight: 500; }
        .items-table th:last-child, .items-table td:last-child { text-align: right; }
        .items-table td { padding: 12px 10px; border-bottom: 1px solid #eee; }
        .items-table tr:nth-child(even) { background: #f9f9f9; }
        .totals { margin-left: auto; width: 300px; }
        .totals table { width: 100%; }
        .totals td { padding: 8px 10px; }
        .totals tr:last-child { background: #dc3545; color: #fff; font-weight: bold; font-size: 14px; }
        .totals .label { text-align: left; }
        .totals .value { text-align: right; }
        .status-box { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; display: flex; justify-content: space-between; border: 1px solid #ffc107; }
        .status-box.approved { background: #d4edda; border-color: #28a745; }
        .status-box.cancelled { background: #f8d7da; border-color: #dc3545; }
        .reason-box { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff; }
        .reason-box h4 { color: #007bff; margin-bottom: 5px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 11px; }
        .print-btn { position: fixed; top: 20px; right: 20px; background: #dc3545; color: #fff; border: none; padding: 12px 25px; cursor: pointer; border-radius: 5px; font-size: 14px; }
        .print-btn:hover { background: #c82333; }
        @media print {
            body { background: #fff; }
            .cn-container { box-shadow: none; margin: 0; padding: 20px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print Credit Note</button>

    <div class="cn-container">
        <div class="cn-header">
            <div class="company-info">
                <h1><?php echo htmlspecialchars($companyName); ?></h1>
                <p>
                    <?php if ($companyAddress): ?>
                        <?php echo nl2br(htmlspecialchars($companyAddress)); ?><br>
                    <?php endif; ?>
                    <?php if ($companyGST): ?>
                        GSTIN: <?php echo htmlspecialchars($companyGST); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="cn-title">
                <h2>CREDIT NOTE</h2>
                <div class="cn-no">
                    <strong>#<?php echo htmlspecialchars($cn['creditNoteNo']); ?></strong><br>
                    Date: <?php echo date('d-M-Y', strtotime($cn['creditNoteDate'])); ?>
                </div>
            </div>
        </div>

        <div class="cn-meta">
            <div class="meta-box">
                <h3>Credit To (<?php echo $cn['entityType']; ?>)</h3>
                <p>
                    <strong><?php echo htmlspecialchars($cn['entityName'] ?: 'N/A'); ?></strong><br>
                    <?php if ($cn['entityGSTIN']): ?>
                        GSTIN: <?php echo htmlspecialchars($cn['entityGSTIN']); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="meta-box">
                <h3>Credit Note Details</h3>
                <p>
                    <strong>Status:</strong> <?php echo $cn['creditNoteStatus']; ?><br>
                    <strong>Reason:</strong> <?php echo htmlspecialchars($cn['reason']); ?><br>
                    <?php if ($cn['invoiceNo']): ?>
                        <strong>Original Invoice:</strong> <?php echo $cn['invoiceType']; ?> - <?php echo htmlspecialchars($cn['invoiceNo']); ?><br>
                    <?php endif; ?>
                    <?php if ($cn['warehouseName']): ?>
                        <strong>Warehouse:</strong> <?php echo htmlspecialchars($cn['warehouseName']); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if ($cn['reasonDetails']): ?>
        <div class="reason-box">
            <h4>Reason Details</h4>
            <p><?php echo nl2br(htmlspecialchars($cn['reasonDetails'])); ?></p>
        </div>
        <?php endif; ?>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="35%">Description</th>
                    <th width="12%">HSN/SKU</th>
                    <th width="10%">Qty</th>
                    <th width="12%">Unit Price</th>
                    <th width="10%">GST</th>
                    <th width="16%">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php $i = 1; foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($item['productName']); ?></td>
                        <td><?php echo htmlspecialchars($item['productSKU'] ?: $item['hsnCode']); ?></td>
                        <td align="center"><?php echo number_format($item['quantity'], 0); ?> <?php echo $item['uom']; ?></td>
                        <td align="right">Rs. <?php echo number_format($item['unitPrice'], 2); ?></td>
                        <td align="center"><?php echo $item['gstRate']; ?>%</td>
                        <td align="right">Rs. <?php echo number_format($item['totalAmount'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td>1</td>
                        <td><?php echo htmlspecialchars($cn['reason']); ?> - <?php echo htmlspecialchars($cn['reasonDetails'] ?: 'Credit adjustment'); ?></td>
                        <td>-</td>
                        <td align="center">1</td>
                        <td align="right">Rs. <?php echo number_format($cn['subtotal'], 2); ?></td>
                        <td align="center"><?php echo ($cn['cgstRate'] + $cn['sgstRate']); ?>%</td>
                        <td align="right">Rs. <?php echo number_format($cn['totalAmount'], 2); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value">Rs. <?php echo number_format($cn['subtotal'], 2); ?></td>
                </tr>
                <?php if ($cn['discountAmount'] > 0): ?>
                <tr>
                    <td class="label">Discount</td>
                    <td class="value">- Rs. <?php echo number_format($cn['discountAmount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($cn['cgstAmount'] > 0): ?>
                <tr>
                    <td class="label">CGST (<?php echo $cn['cgstRate']; ?>%)</td>
                    <td class="value">Rs. <?php echo number_format($cn['cgstAmount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($cn['sgstAmount'] > 0): ?>
                <tr>
                    <td class="label">SGST (<?php echo $cn['sgstRate']; ?>%)</td>
                    <td class="value">Rs. <?php echo number_format($cn['sgstAmount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($cn['igstAmount'] > 0): ?>
                <tr>
                    <td class="label">IGST (<?php echo $cn['igstRate']; ?>%)</td>
                    <td class="value">Rs. <?php echo number_format($cn['igstAmount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="label">Total Credit Amount</td>
                    <td class="value">Rs. <?php echo number_format($cn['totalAmount'], 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="status-box <?php echo strtolower($cn['creditNoteStatus']); ?>">
            <div>
                <strong>Credit Amount:</strong> Rs. <?php echo number_format($cn['totalAmount'], 2); ?>
            </div>
            <div>
                <strong>Adjusted:</strong> Rs. <?php echo number_format($cn['adjustedAmount'], 2); ?>
            </div>
            <div>
                <strong>Balance:</strong> Rs. <?php echo number_format($cn['balanceAmount'], 2); ?>
            </div>
        </div>

        <?php if ($cn['notes']): ?>
        <div class="reason-box">
            <h4>Notes</h4>
            <p><?php echo nl2br(htmlspecialchars($cn['notes'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>This is a computer generated credit note.</p>
            <p style="margin-top:5px;">Generated on: <?php echo date('d-M-Y H:i'); ?></p>
        </div>
    </div>

    <?php if ($download): ?>
    <script>window.print();</script>
    <?php endif; ?>
</body>
</html>

<?php
/**
 * Debit Note Print/View Page
 */
require_once("../../../core/core.inc.php");
require_once("../../inc/site.inc.php");

$debitNoteID = intval($_GET['id'] ?? 0);
if (!$debitNoteID) {
    die('Invalid Debit Note ID');
}

// Get debit note details
$DB->vals = array($debitNoteID);
$DB->types = "i";
$DB->sql = "SELECT dn.*, w.warehouseName
            FROM " . $DB->pre . "debit_note dn
            LEFT JOIN " . $DB->pre . "warehouse w ON dn.warehouseID = w.warehouseID
            WHERE dn.debitNoteID = ?";
$dn = $DB->dbRow();

if (!$dn) {
    die('Debit Note not found');
}

// Get items
$DB->vals = array($debitNoteID, 1);
$DB->types = "ii";
$DB->sql = "SELECT * FROM " . $DB->pre . "debit_note_item WHERE debitNoteID = ? AND status = ?";
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
    <title>Debit Note #<?php echo htmlspecialchars($dn['debitNoteNo']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #333; background: #f5f5f5; }
        .dn-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .dn-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 3px solid #fd7e14; padding-bottom: 20px; }
        .company-info h1 { font-size: 24px; color: #fd7e14; margin-bottom: 5px; }
        .company-info p { color: #666; line-height: 1.5; }
        .dn-title { text-align: right; }
        .dn-title h2 { font-size: 28px; color: #fd7e14; margin-bottom: 10px; }
        .dn-title .dn-no { font-size: 14px; color: #666; }
        .dn-meta { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .meta-box { background: #f8f9fa; padding: 15px; border-radius: 5px; width: 48%; }
        .meta-box h3 { font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .meta-box p { line-height: 1.6; }
        .meta-box strong { color: #fd7e14; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #fd7e14; color: #fff; padding: 12px 10px; text-align: left; font-weight: 500; }
        .items-table th:last-child, .items-table td:last-child { text-align: right; }
        .items-table td { padding: 12px 10px; border-bottom: 1px solid #eee; }
        .items-table tr:nth-child(even) { background: #f9f9f9; }
        .totals { margin-left: auto; width: 300px; }
        .totals table { width: 100%; }
        .totals td { padding: 8px 10px; }
        .totals tr:last-child { background: #fd7e14; color: #fff; font-weight: bold; font-size: 14px; }
        .totals .label { text-align: left; }
        .totals .value { text-align: right; }
        .status-box { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; display: flex; justify-content: space-between; border: 1px solid #ffc107; }
        .status-box.approved { background: #d4edda; border-color: #28a745; }
        .status-box.cancelled { background: #f8d7da; border-color: #dc3545; }
        .status-box.collected { background: #cce5ff; border-color: #007bff; }
        .reason-box { background: #fff8e1; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #fd7e14; }
        .reason-box h4 { color: #fd7e14; margin-bottom: 5px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 11px; }
        .print-btn { position: fixed; top: 20px; right: 20px; background: #fd7e14; color: #fff; border: none; padding: 12px 25px; cursor: pointer; border-radius: 5px; font-size: 14px; }
        .print-btn:hover { background: #e96b00; }
        @media print {
            body { background: #fff; }
            .dn-container { box-shadow: none; margin: 0; padding: 20px; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print Debit Note</button>

    <div class="dn-container">
        <div class="dn-header">
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
            <div class="dn-title">
                <h2>DEBIT NOTE</h2>
                <div class="dn-no">
                    <strong>#<?php echo htmlspecialchars($dn['debitNoteNo']); ?></strong><br>
                    Date: <?php echo date('d-M-Y', strtotime($dn['debitNoteDate'])); ?>
                </div>
            </div>
        </div>

        <div class="dn-meta">
            <div class="meta-box">
                <h3>Debit To (<?php echo $dn['entityType']; ?>)</h3>
                <p>
                    <strong><?php echo htmlspecialchars($dn['entityName'] ?: 'N/A'); ?></strong><br>
                    <?php if ($dn['entityGSTIN']): ?>
                        GSTIN: <?php echo htmlspecialchars($dn['entityGSTIN']); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="meta-box">
                <h3>Debit Note Details</h3>
                <p>
                    <strong>Status:</strong> <?php echo $dn['debitNoteStatus']; ?><br>
                    <strong>Reason:</strong> <?php echo htmlspecialchars($dn['reason']); ?><br>
                    <?php if ($dn['invoiceNo']): ?>
                        <strong>Related Invoice:</strong> <?php echo $dn['invoiceType']; ?> - <?php echo htmlspecialchars($dn['invoiceNo']); ?><br>
                    <?php endif; ?>
                    <?php if ($dn['warehouseName']): ?>
                        <strong>Warehouse:</strong> <?php echo htmlspecialchars($dn['warehouseName']); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if ($dn['reasonDetails']): ?>
        <div class="reason-box">
            <h4>Reason Details</h4>
            <p><?php echo nl2br(htmlspecialchars($dn['reasonDetails'])); ?></p>
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
                        <td><?php echo htmlspecialchars($dn['reason']); ?> - <?php echo htmlspecialchars($dn['reasonDetails'] ?: 'Debit adjustment'); ?></td>
                        <td>-</td>
                        <td align="center">1</td>
                        <td align="right">Rs. <?php echo number_format($dn['subtotal'], 2); ?></td>
                        <td align="center"><?php echo ($dn['cgstRate'] + $dn['sgstRate']); ?>%</td>
                        <td align="right">Rs. <?php echo number_format($dn['totalAmount'], 2); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value">Rs. <?php echo number_format($dn['subtotal'], 2); ?></td>
                </tr>
                <?php if ($dn['discountAmount'] > 0): ?>
                <tr>
                    <td class="label">Discount</td>
                    <td class="value">- Rs. <?php echo number_format($dn['discountAmount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($dn['cgstAmount'] > 0): ?>
                <tr>
                    <td class="label">CGST (<?php echo $dn['cgstRate']; ?>%)</td>
                    <td class="value">Rs. <?php echo number_format($dn['cgstAmount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($dn['sgstAmount'] > 0): ?>
                <tr>
                    <td class="label">SGST (<?php echo $dn['sgstRate']; ?>%)</td>
                    <td class="value">Rs. <?php echo number_format($dn['sgstAmount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($dn['igstAmount'] > 0): ?>
                <tr>
                    <td class="label">IGST (<?php echo $dn['igstRate']; ?>%)</td>
                    <td class="value">Rs. <?php echo number_format($dn['igstAmount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="label">Total Debit Amount</td>
                    <td class="value">Rs. <?php echo number_format($dn['totalAmount'], 2); ?></td>
                </tr>
            </table>
        </div>

        <div class="status-box <?php echo $dn['debitNoteStatus'] == 'Fully Collected' ? 'collected' : (strtolower($dn['debitNoteStatus']) == 'cancelled' ? 'cancelled' : ''); ?>">
            <div>
                <strong>Total Due:</strong> Rs. <?php echo number_format($dn['totalAmount'], 2); ?>
            </div>
            <div>
                <strong>Collected:</strong> Rs. <?php echo number_format($dn['collectedAmount'], 2); ?>
            </div>
            <div>
                <strong>Balance:</strong> Rs. <?php echo number_format($dn['balanceAmount'], 2); ?>
            </div>
        </div>

        <?php if ($dn['notes']): ?>
        <div class="reason-box">
            <h4>Notes</h4>
            <p><?php echo nl2br(htmlspecialchars($dn['notes'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p>This is a computer generated debit note.</p>
            <p style="margin-top:5px;">Generated on: <?php echo date('d-M-Y H:i'); ?></p>
        </div>
    </div>

    <?php if ($download): ?>
    <script>window.print();</script>
    <?php endif; ?>
</body>
</html>

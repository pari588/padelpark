<?php
/**
 * Vendor Portal - Orders (Awarded Quotes)
 */

$pageTitle = 'Purchase Orders';
include("x-header.php");

$vendorID = vpGetVendorID();

// Get awarded orders
$DB->sql = "SELECT q.*, r.rfqNumber, r.title as rfqTitle, r.expectedDeliveryDate
            FROM mx_vendor_quote q
            JOIN mx_vendor_rfq r ON q.rfqID = r.rfqID
            WHERE q.vendorID = ? AND q.quoteStatus = 'Accepted' AND q.status = 1
            ORDER BY q.evaluatedAt DESC";
$DB->vals = [$vendorID];
$DB->types = "i";
$orders = $DB->dbRows();
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Purchase Orders</h1>
        <p>View and manage your awarded orders</p>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (count($orders) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order Details</th>
                        <th>RFQ Reference</th>
                        <th>Amount</th>
                        <th>Delivery Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <div class="table-cell-main font-mono"><?php echo vpClean($order['quoteNumber']); ?></div>
                                <div class="table-cell-sub">Awarded: <?php echo vpFormatDate($order['evaluatedAt']); ?></div>
                            </td>
                            <td>
                                <div class="table-cell-main"><?php echo vpClean($order['rfqTitle']); ?></div>
                                <div class="table-cell-sub font-mono"><?php echo vpClean($order['rfqNumber']); ?></div>
                            </td>
                            <td>
                                <strong style="font-size: 1.1em;"><?php echo vpFormatCurrency($order['totalAmount']); ?></strong>
                            </td>
                            <td>
                                <?php echo vpFormatDate($order['expectedDeliveryDate']); ?>
                            </td>
                            <td>
                                <?php echo vpGetStatusBadge('Accepted'); ?>
                            </td>
                            <td>
                                <a href="<?php echo VP_BASEURL; ?>/vendorportal/quote-view?id=<?php echo $order['quoteID']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3>No Orders Yet</h3>
                <p>You don't have any awarded orders yet. Submit competitive quotes to win orders!</p>
                <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list" class="btn btn-primary" style="margin-top: var(--space-md);">
                    <i class="fas fa-file-invoice"></i> Browse Available RFQs
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include("x-footer.php"); ?>

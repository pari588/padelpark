<?php
/**
 * Vendor Portal - My Quotes List
 */

$pageTitle = 'My Quotes';
include("x-header.php");

$quotes = vpGetMyQuotes(100);
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>My Quotes</h1>
        <p>View and manage all your submitted quotations</p>
    </div>
</div>

<div class="card">
    <?php if (count($quotes) > 0): ?>
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quote Number</th>
                        <th>RFQ Reference</th>
                        <th>Submitted</th>
                        <th style="text-align: right;">Total Amount</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotes as $quote): ?>
                        <tr>
                            <td>
                                <div class="table-cell-main font-mono"><?php echo vpClean($quote['quoteNumber']); ?></div>
                            </td>
                            <td>
                                <div class="table-cell-main"><?php echo vpClean($quote['rfqTitle']); ?></div>
                                <div class="table-cell-sub font-mono"><?php echo vpClean($quote['rfqNumber']); ?></div>
                            </td>
                            <td>
                                <?php echo $quote['submittedAt'] ? vpFormatDate($quote['submittedAt'], 'd M Y, H:i') : '-'; ?>
                            </td>
                            <td style="text-align: right;">
                                <strong><?php echo vpFormatCurrency($quote['totalAmount']); ?></strong>
                            </td>
                            <td>
                                <?php
                                if ($quote['validUntil']) {
                                    $isExpired = strtotime($quote['validUntil']) < time();
                                    echo '<span class="' . ($isExpired ? 'text-danger' : '') . '">';
                                    echo vpFormatDate($quote['validUntil']);
                                    echo '</span>';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <?php echo vpGetStatusBadge($quote['quoteStatus']); ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?php echo VP_BASEURL; ?>/vendorportal/quote-view?id=<?php echo $quote['quoteID']; ?>" class="btn btn-ghost btn-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($quote['quoteStatus'] === 'Draft'): ?>
                                        <a href="<?php echo VP_BASEURL; ?>/vendorportal/quote-submit?rfqID=<?php echo $quote['rfqID']; ?>" class="btn btn-ghost btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <h3>No Quotes Yet</h3>
            <p>You haven't created any quotes yet. Browse available RFQs to submit your first quotation.</p>
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list" class="btn btn-primary">
                <i class="fas fa-search"></i>
                Browse RFQs
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include("x-footer.php"); ?>

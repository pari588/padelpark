<?php
/**
 * Vendor Portal - Quote View Details
 */

include("x-vendorportal.inc.php");
vpRequireAuth();

global $DB;

$quoteID = intval($_GET['id'] ?? 0);
$vendorID = vpGetVendorID();

// Get quote details
$DB->sql = "SELECT q.*, r.rfqNumber, r.title as rfqTitle, r.rfqType, r.description as rfqDescription,
            r.submissionDeadline, r.expectedDeliveryDate
            FROM mx_vendor_quote q
            JOIN mx_vendor_rfq r ON q.rfqID = r.rfqID
            WHERE q.quoteID = ? AND q.vendorID = ?";
$DB->vals = [$quoteID, $vendorID];
$DB->types = "ii";
$quote = $DB->dbRow();

if (!$quote) {
    header('Location: ' . SITEURL . '/vendorportal/quotes');
    exit;
}

// Get quote items
$DB->sql = "SELECT qi.*, ri.itemDescription, ri.specifications, ri.unit, ri.quantity as rfqQuantity
            FROM mx_vendor_quote_item qi
            LEFT JOIN mx_vendor_rfq_item ri ON qi.rfqItemID = ri.itemID
            WHERE qi.quoteID = ? AND qi.status = 1
            ORDER BY qi.itemID";
$DB->vals = [$quoteID];
$DB->types = "i";
$items = $DB->dbRows();

$pageTitle = 'Quote Details';
include("x-header.php");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Quote Details</h1>
        <p class="font-mono"><?php echo vpClean($quote['quoteNumber']); ?></p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo VP_BASEURL; ?>/vendorportal/quotes" class="btn btn-ghost">
            <i class="fas fa-arrow-left"></i>
            Back to Quotes
        </a>
        <?php if ($quote['quoteStatus'] === 'Draft'): ?>
            <a href="<?php echo VP_BASEURL; ?>/vendorportal/quote-submit?rfqID=<?php echo $quote['rfqID']; ?>" class="btn btn-accent">
                <i class="fas fa-edit"></i>
                Edit Quote
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Quote Status Banner -->
<div class="card mb-lg">
    <div class="card-body" style="padding: var(--space-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div class="text-muted text-xs mb-sm">Status</div>
                <?php echo vpGetStatusBadge($quote['quoteStatus']); ?>
            </div>
            <div style="text-align: right;">
                <div class="text-muted text-xs mb-sm">Grand Total</div>
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--vp-primary);">
                    <?php echo vpFormatCurrency($quote['totalAmount']); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--space-xl);">
    <div>
        <!-- RFQ Reference -->
        <div class="card mb-lg">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice text-primary"></i>
                    RFQ Reference
                </h3>
            </div>
            <div class="card-body">
                <div class="profile-grid">
                    <div class="profile-field">
                        <div class="profile-field-label">RFQ Number</div>
                        <div class="profile-field-value font-mono"><?php echo vpClean($quote['rfqNumber']); ?></div>
                    </div>
                    <div class="profile-field">
                        <div class="profile-field-label">Type</div>
                        <div class="profile-field-value"><?php echo vpClean($quote['rfqType']); ?></div>
                    </div>
                </div>
                <div class="profile-field" style="margin-top: var(--space-md);">
                    <div class="profile-field-label">Title</div>
                    <div class="profile-field-value"><?php echo vpClean($quote['rfqTitle']); ?></div>
                </div>
            </div>
        </div>

        <!-- Quote Items -->
        <div class="card mb-lg">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list text-accent"></i>
                    Quote Items
                </h3>
            </div>
            <div class="quote-items-table" style="border: none; border-radius: 0;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Description</th>
                            <th style="width: 80px;">Unit</th>
                            <th style="width: 100px;">Qty</th>
                            <th style="width: 120px;">Rate</th>
                            <th style="width: 130px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $subTotal = 0;
                        foreach ($items as $idx => $item):
                            $amount = $item['quantity'] * $item['unitRate'];
                            $subTotal += $amount;
                        ?>
                            <tr>
                                <td><?php echo $idx + 1; ?></td>
                                <td>
                                    <div class="item-description"><?php echo vpClean($item['itemDescription']); ?></div>
                                    <?php if ($item['vendorRemarks']): ?>
                                        <div class="item-specs" style="color: var(--vp-info);">
                                            <i class="fas fa-comment"></i> <?php echo vpClean($item['vendorRemarks']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo vpClean($item['unit']); ?></td>
                                <td class="text-right"><?php echo number_format($item['quantity'], 2); ?></td>
                                <td class="text-right"><?php echo vpFormatCurrency($item['unitRate']); ?></td>
                                <td class="text-right"><strong><?php echo vpFormatCurrency($amount); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--vp-slate-50);">
                            <td colspan="5" style="text-align: right; font-weight: 600;">Sub Total:</td>
                            <td style="text-align: right; font-weight: 600;"><?php echo vpFormatCurrency($subTotal); ?></td>
                        </tr>
                        <?php if ($quote['taxAmount'] > 0): ?>
                        <tr style="background: var(--vp-slate-50);">
                            <td colspan="5" style="text-align: right;">Tax (<?php echo $quote['taxPercentage']; ?>%):</td>
                            <td style="text-align: right;"><?php echo vpFormatCurrency($quote['taxAmount']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($quote['discountAmount'] > 0): ?>
                        <tr style="background: var(--vp-slate-50);">
                            <td colspan="5" style="text-align: right;">Discount:</td>
                            <td style="text-align: right;">-<?php echo vpFormatCurrency($quote['discountAmount']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr style="background: var(--vp-primary); color: white;">
                            <td colspan="5" style="text-align: right; font-weight: 700; font-size: 1.125rem;">Grand Total:</td>
                            <td style="text-align: right; font-weight: 700; font-size: 1.125rem;"><?php echo vpFormatCurrency($quote['totalAmount']); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <?php if ($quote['remarks']): ?>
        <div class="card mb-lg">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sticky-note text-warning"></i>
                    Remarks
                </h3>
            </div>
            <div class="card-body">
                <p class="mb-0"><?php echo nl2br(vpClean($quote['remarks'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div>
        <!-- Quote Summary -->
        <div class="card mb-lg">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle text-primary"></i>
                    Quote Summary
                </h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-between align-center mb-md">
                    <span class="text-muted">Delivery Time</span>
                    <strong><?php echo $quote['deliveryDays'] ? $quote['deliveryDays'] . ' Days' : '-'; ?></strong>
                </div>
                <div class="d-flex justify-between align-center mb-md">
                    <span class="text-muted">Valid Until</span>
                    <strong><?php echo vpFormatDate($quote['validUntil']); ?></strong>
                </div>
                <div class="d-flex justify-between align-center mb-md">
                    <span class="text-muted">Payment Terms</span>
                    <strong><?php echo vpClean($quote['paymentTerms'] ?? '-'); ?></strong>
                </div>
                <hr style="border: none; border-top: 1px solid var(--vp-slate-200); margin: var(--space-md) 0;">
                <div class="d-flex justify-between align-center mb-md">
                    <span class="text-muted">Submitted At</span>
                    <strong><?php echo $quote['submittedAt'] ? vpFormatDate($quote['submittedAt'], 'd M Y, H:i') : '-'; ?></strong>
                </div>
                <div class="d-flex justify-between align-center">
                    <span class="text-muted">Status</span>
                    <?php echo vpGetStatusBadge($quote['quoteStatus']); ?>
                </div>
            </div>
        </div>

        <!-- Status Messages -->
        <?php if ($quote['quoteStatus'] === 'Accepted'): ?>
        <div class="alert alert-success">
            <i class="fas fa-trophy alert-icon"></i>
            <div>
                <strong>Congratulations!</strong><br>
                Your quote has been accepted. A purchase order will be generated soon.
            </div>
        </div>
        <?php elseif ($quote['quoteStatus'] === 'Rejected'): ?>
        <div class="alert alert-danger">
            <i class="fas fa-times-circle alert-icon"></i>
            <div>
                <strong>Quote Rejected</strong><br>
                Unfortunately, your quote was not selected for this RFQ.
            </div>
        </div>
        <?php elseif ($quote['quoteStatus'] === 'Shortlisted'): ?>
        <div class="alert alert-info">
            <i class="fas fa-star alert-icon"></i>
            <div>
                <strong>Shortlisted!</strong><br>
                Your quote has been shortlisted for final consideration.
            </div>
        </div>
        <?php elseif ($quote['quoteStatus'] === 'Under Review'): ?>
        <div class="alert alert-warning">
            <i class="fas fa-clock alert-icon"></i>
            <div>
                <strong>Under Review</strong><br>
                Your quote is currently being reviewed by the team.
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include("x-footer.php"); ?>

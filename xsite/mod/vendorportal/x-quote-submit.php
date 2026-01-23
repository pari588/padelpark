<?php
/**
 * Vendor Portal - Quote Submission Form
 */

include("x-vendorportal.inc.php");
vpRequireAuth();

global $DB;

$rfqID = intval($_GET['rfqID'] ?? 0);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $xAction = $_POST['xAction'] ?? '';
    $vendorID = vpGetVendorID();

    if ($xAction === 'SAVE_QUOTE' || $xAction === 'SUBMIT_QUOTE') {
        $rfqID = intval($_POST['rfqID'] ?? 0);
        $quoteID = intval($_POST['quoteID'] ?? 0);

        // Validate RFQ
        $rfq = vpGetRFQ($rfqID);
        if (!$rfq || ($rfq['rfqStatus'] !== 'Published' && $xAction === 'SUBMIT_QUOTE')) {
            echo json_encode(['err' => 1, 'msg' => 'RFQ is not available for quotation']);
            exit;
        }

        // Calculate totals
        $items = $_POST['items'] ?? [];
        $subTotal = 0;

        foreach ($items as $item) {
            $qty = floatval($item['quantity'] ?? 0);
            $rate = floatval($item['unitRate'] ?? 0);
            $subTotal += $qty * $rate;
        }

        $taxPercentage = floatval($_POST['taxPercentage'] ?? 0);
        $taxAmount = $subTotal * ($taxPercentage / 100);
        $discountAmount = floatval($_POST['discountAmount'] ?? 0);
        $totalAmount = $subTotal + $taxAmount - $discountAmount;

        $quoteStatus = ($xAction === 'SUBMIT_QUOTE') ? 'Submitted' : 'Draft';
        $submittedAt = ($xAction === 'SUBMIT_QUOTE') ? date('Y-m-d H:i:s') : null;

        $quoteData = [
            'rfqID' => $rfqID,
            'vendorID' => $vendorID,
            'quoteNumber' => vpGenerateQuoteNumber(),
            'quoteStatus' => $quoteStatus,
            'subTotal' => $subTotal,
            'taxPercentage' => $taxPercentage,
            'taxAmount' => $taxAmount,
            'discountAmount' => $discountAmount,
            'totalAmount' => $totalAmount,
            'deliveryDays' => intval($_POST['deliveryDays'] ?? 0),
            'validUntil' => $_POST['validUntil'] ?? null,
            'paymentTerms' => vpClean($_POST['paymentTerms'] ?? ''),
            'remarks' => vpClean($_POST['remarks'] ?? ''),
            'submittedAt' => $submittedAt
        ];

        $DB->tbl = "mx_vendor_quote";

        if ($quoteID > 0) {
            // Update existing quote
            unset($quoteData['quoteNumber']);
            $DB->dbUpdate($quoteData, "quoteID = ? AND vendorID = ?", "ii", [$quoteID, $vendorID]);
        } else {
            // Insert new quote
            $quoteID = $DB->dbInsert($quoteData);
        }

        if ($quoteID) {
            // Delete old items and insert new ones
            $DB->sql = "DELETE FROM mx_vendor_quote_item WHERE quoteID = ?";
            $DB->vals = [$quoteID];
            $DB->types = "i";
            $DB->dbExecute();

            // Insert quote items
            $DB->tbl = "mx_vendor_quote_item";
            foreach ($items as $item) {
                $itemData = [
                    'quoteID' => $quoteID,
                    'rfqItemID' => intval($item['rfqItemID'] ?? 0),
                    'quantity' => floatval($item['quantity'] ?? 0),
                    'unitRate' => floatval($item['unitRate'] ?? 0),
                    'vendorRemarks' => vpClean($item['remarks'] ?? '')
                ];
                $DB->dbInsert($itemData);
            }

            $msg = ($xAction === 'SUBMIT_QUOTE') ? 'Quote submitted successfully!' : 'Quote saved as draft.';
            echo json_encode(['err' => 0, 'msg' => $msg, 'quoteID' => $quoteID]);
        } else {
            echo json_encode(['err' => 1, 'msg' => 'Failed to save quote']);
        }
        exit;
    }
}

// Get RFQ details
$rfq = vpGetRFQ($rfqID);
if (!$rfq) {
    header('Location: ' . SITEURL . '/vendorportal/rfq-list');
    exit;
}

$rfqItems = vpGetRFQItems($rfqID);

// Check if vendor already has a quote for this RFQ
$vendorID = vpGetVendorID();
$DB->sql = "SELECT * FROM mx_vendor_quote WHERE rfqID = ? AND vendorID = ? AND status = 1 LIMIT 1";
$DB->vals = [$rfqID, $vendorID];
$DB->types = "ii";
$existingQuote = $DB->dbRow();

$quoteItems = [];
if ($existingQuote) {
    $DB->sql = "SELECT * FROM mx_vendor_quote_item WHERE quoteID = ? AND status = 1";
    $DB->vals = [$existingQuote['quoteID']];
    $DB->types = "i";
    $quoteItems = $DB->dbRows();
}

$pageTitle = 'Submit Quote';
include("x-header.php");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1>Submit Quotation</h1>
        <p class="font-mono"><?php echo vpClean($rfq['rfqNumber']); ?> - <?php echo vpClean($rfq['title']); ?></p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo VP_BASEURL; ?>/vendorportal/rfq-list" class="btn btn-ghost">
            <i class="fas fa-arrow-left"></i>
            Back to RFQs
        </a>
    </div>
</div>

<!-- RFQ Details Card -->
<div class="card mb-lg">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-invoice text-primary"></i>
            RFQ Details
        </h3>
        <?php echo vpGetStatusBadge($rfq['rfqStatus']); ?>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-lg);">
            <div>
                <div class="text-muted text-xs mb-sm">RFQ Type</div>
                <div class="font-medium"><?php echo vpClean($rfq['rfqType']); ?></div>
            </div>
            <div>
                <div class="text-muted text-xs mb-sm">Category</div>
                <div class="font-medium"><?php echo vpClean($rfq['categoryName'] ?? 'General'); ?></div>
            </div>
            <div>
                <div class="text-muted text-xs mb-sm">Deadline</div>
                <div class="font-medium text-accent"><?php echo vpFormatDate($rfq['submissionDeadline'], 'd M Y, H:i'); ?></div>
            </div>
            <div>
                <div class="text-muted text-xs mb-sm">Expected Delivery</div>
                <div class="font-medium"><?php echo vpFormatDate($rfq['expectedDeliveryDate']); ?></div>
            </div>
        </div>

        <?php if ($rfq['description']): ?>
            <div style="margin-top: var(--space-lg); padding-top: var(--space-lg); border-top: 1px solid var(--vp-slate-100);">
                <div class="text-muted text-xs mb-sm">Description</div>
                <p class="mb-0"><?php echo nl2br(vpClean($rfq['description'])); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($rfq['specialRequirements']): ?>
            <div class="alert alert-info" style="margin-top: var(--space-lg);">
                <i class="fas fa-info-circle alert-icon"></i>
                <div>
                    <strong>Special Requirements:</strong><br>
                    <?php echo nl2br(vpClean($rfq['specialRequirements'])); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quote Form -->
<form id="quoteForm" method="POST">
    <input type="hidden" name="rfqID" value="<?php echo $rfqID; ?>">
    <input type="hidden" name="quoteID" value="<?php echo $existingQuote['quoteID'] ?? 0; ?>">

    <!-- Line Items -->
    <div class="card mb-lg">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list text-accent"></i>
                Quote Items
            </h3>
        </div>
        <div class="quote-items-table">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Item Description</th>
                        <th style="width: 80px;">Unit</th>
                        <th style="width: 100px;">RFQ Qty</th>
                        <th style="width: 120px;">Your Qty</th>
                        <th style="width: 120px;">Unit Rate</th>
                        <th style="width: 130px;">Amount</th>
                        <th style="width: 200px;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rfqItems as $idx => $item):
                        // Find existing quote item
                        $existingItem = null;
                        foreach ($quoteItems as $qi) {
                            if ($qi['rfqItemID'] == $item['itemID']) {
                                $existingItem = $qi;
                                break;
                            }
                        }
                    ?>
                        <tr>
                            <td><?php echo $idx + 1; ?></td>
                            <td>
                                <div class="item-description"><?php echo vpClean($item['itemDescription']); ?></div>
                                <?php if ($item['specifications']): ?>
                                    <div class="item-specs"><?php echo nl2br(vpClean($item['specifications'])); ?></div>
                                <?php endif; ?>
                                <input type="hidden" name="items[<?php echo $idx; ?>][rfqItemID]" value="<?php echo $item['itemID']; ?>">
                            </td>
                            <td><?php echo vpClean($item['unit']); ?></td>
                            <td class="text-right font-medium"><?php echo number_format($item['quantity'], 2); ?></td>
                            <td>
                                <input type="number"
                                       name="items[<?php echo $idx; ?>][quantity]"
                                       class="item-qty"
                                       step="0.01"
                                       min="0"
                                       value="<?php echo $existingItem['quantity'] ?? $item['quantity']; ?>"
                                       data-index="<?php echo $idx; ?>"
                                       onchange="calculateRowTotal(<?php echo $idx; ?>)">
                            </td>
                            <td>
                                <input type="number"
                                       name="items[<?php echo $idx; ?>][unitRate]"
                                       class="item-rate"
                                       step="0.01"
                                       min="0"
                                       placeholder="0.00"
                                       value="<?php echo $existingItem['unitRate'] ?? ''; ?>"
                                       data-index="<?php echo $idx; ?>"
                                       onchange="calculateRowTotal(<?php echo $idx; ?>)">
                            </td>
                            <td class="text-right">
                                <strong class="row-total" id="rowTotal<?php echo $idx; ?>">0.00</strong>
                            </td>
                            <td>
                                <input type="text"
                                       name="items[<?php echo $idx; ?>][remarks]"
                                       placeholder="Optional..."
                                       value="<?php echo vpClean($existingItem['vendorRemarks'] ?? ''); ?>"
                                       style="text-align: left;">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 400px; gap: var(--space-xl);">
        <!-- Additional Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog text-primary"></i>
                    Quote Details
                </h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-lg);">
                    <div class="form-group">
                        <label class="form-label">Delivery Days</label>
                        <input type="number"
                               name="deliveryDays"
                               class="form-control"
                               min="1"
                               placeholder="e.g., 15"
                               value="<?php echo $existingQuote['deliveryDays'] ?? ''; ?>">
                        <small class="text-muted">Days from order confirmation</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quote Valid Until</label>
                        <input type="date"
                               name="validUntil"
                               class="form-control"
                               value="<?php echo $existingQuote['validUntil'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Terms</label>
                        <select name="paymentTerms" class="form-control">
                            <option value="">-- Select --</option>
                            <option value="Advance" <?php echo ($existingQuote['paymentTerms'] ?? '') === 'Advance' ? 'selected' : ''; ?>>100% Advance</option>
                            <option value="50-50" <?php echo ($existingQuote['paymentTerms'] ?? '') === '50-50' ? 'selected' : ''; ?>>50% Advance, 50% on Delivery</option>
                            <option value="30-70" <?php echo ($existingQuote['paymentTerms'] ?? '') === '30-70' ? 'selected' : ''; ?>>30% Advance, 70% on Delivery</option>
                            <option value="Net15" <?php echo ($existingQuote['paymentTerms'] ?? '') === 'Net15' ? 'selected' : ''; ?>>Net 15 Days</option>
                            <option value="Net30" <?php echo ($existingQuote['paymentTerms'] ?? '') === 'Net30' ? 'selected' : ''; ?>>Net 30 Days</option>
                            <option value="COD" <?php echo ($existingQuote['paymentTerms'] ?? '') === 'COD' ? 'selected' : ''; ?>>Cash on Delivery</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tax Percentage (%)</label>
                        <input type="number"
                               name="taxPercentage"
                               id="taxPercentage"
                               class="form-control"
                               step="0.01"
                               min="0"
                               max="100"
                               value="<?php echo $existingQuote['taxPercentage'] ?? '18'; ?>"
                               onchange="calculateTotals()">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Remarks / Notes</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Any additional notes or clarifications..."><?php echo vpClean($existingQuote['remarks'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Quote Summary -->
        <div>
            <div class="quote-summary">
                <h4 style="margin-bottom: var(--space-lg); color: white;">Quote Summary</h4>

                <div class="quote-summary-row">
                    <span>Sub Total:</span>
                    <span id="subTotal">0.00</span>
                </div>
                <div class="quote-summary-row">
                    <span>Tax (<span id="taxPctDisplay">18</span>%):</span>
                    <span id="taxAmount">0.00</span>
                </div>
                <div class="quote-summary-row">
                    <span>Discount:</span>
                    <input type="number"
                           name="discountAmount"
                           id="discountAmount"
                           style="width: 100px; text-align: right; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 4px 8px; border-radius: 4px;"
                           step="0.01"
                           min="0"
                           value="<?php echo $existingQuote['discountAmount'] ?? 0; ?>"
                           onchange="calculateTotals()">
                </div>
                <div class="quote-summary-row total">
                    <span>Grand Total:</span>
                    <span id="grandTotal">0.00</span>
                </div>
            </div>

            <div style="margin-top: var(--space-lg); display: flex; flex-direction: column; gap: var(--space-sm);">
                <?php if ($rfq['rfqStatus'] === 'Published'): ?>
                    <button type="button" class="btn btn-accent btn-lg btn-block" onclick="submitQuote('SUBMIT_QUOTE')">
                        <i class="fas fa-paper-plane"></i>
                        Submit Quote
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-outline btn-lg btn-block" onclick="submitQuote('SAVE_QUOTE')">
                    <i class="fas fa-save"></i>
                    Save as Draft
                </button>
            </div>

            <?php if ($existingQuote && $existingQuote['quoteStatus'] !== 'Draft'): ?>
                <div class="alert alert-info" style="margin-top: var(--space-lg);">
                    <i class="fas fa-info-circle"></i>
                    <span>Quote already submitted. Status: <?php echo vpGetStatusBadge($existingQuote['quoteStatus']); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
// Calculate row total
function calculateRowTotal(index) {
    const qty = parseFloat($(`input[name="items[${index}][quantity]"]`).val()) || 0;
    const rate = parseFloat($(`input[name="items[${index}][unitRate]"]`).val()) || 0;
    const total = qty * rate;

    $(`#rowTotal${index}`).text(total.toFixed(2));
    calculateTotals();
}

// Calculate all totals
function calculateTotals() {
    let subTotal = 0;

    $('.row-total').each(function() {
        subTotal += parseFloat($(this).text()) || 0;
    });

    const taxPct = parseFloat($('#taxPercentage').val()) || 0;
    const taxAmount = subTotal * (taxPct / 100);
    const discount = parseFloat($('#discountAmount').val()) || 0;
    const grandTotal = subTotal + taxAmount - discount;

    $('#subTotal').text(subTotal.toFixed(2));
    $('#taxPctDisplay').text(taxPct);
    $('#taxAmount').text(taxAmount.toFixed(2));
    $('#grandTotal').text(grandTotal.toFixed(2));
}

// Submit quote
function submitQuote(action) {
    const form = $('#quoteForm');
    const formData = form.serialize() + '&xAction=' + action;

    // Validate
    let hasItems = false;
    $('.item-rate').each(function() {
        if (parseFloat($(this).val()) > 0) {
            hasItems = true;
        }
    });

    if (!hasItems && action === 'SUBMIT_QUOTE') {
        alert('Please enter at least one item rate before submitting.');
        return;
    }

    $.ajax({
        url: '<?php echo VP_BASEURL; ?>/vendorportal/quote-submit?rfqID=<?php echo $rfqID; ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            alert(response.msg);
            if (response.err === 0) {
                if (action === 'SUBMIT_QUOTE') {
                    window.location.href = '<?php echo VP_BASEURL; ?>/vendorportal/quotes';
                } else {
                    $('input[name="quoteID"]').val(response.quoteID);
                }
            }
        },
        error: function() {
            alert('Error saving quote. Please try again.');
        }
    });
}

// Initialize calculations on page load
$(document).ready(function() {
    <?php foreach ($rfqItems as $idx => $item): ?>
    calculateRowTotal(<?php echo $idx; ?>);
    <?php endforeach; ?>
});
</script>

<?php include("x-footer.php"); ?>

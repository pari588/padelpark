<?php
/**
 * Vendor Quote - View/Edit (Admin Review)
 * Note: Quotes are submitted by vendors through the Vendor Portal.
 * This page is for viewing and reviewing submitted quotes only.
 */

// Note: x-vendor-quote.inc.php is already included by xadmin framework

$quoteID = intval($_GET["quoteID"] ?? 0);

if (!$quoteID) {
    // Redirect to list if no quote ID - quotes cannot be created from admin
    echo '<div class="alert alert-warning">
            <i class="fa fa-info-circle"></i>
            <strong>Note:</strong> Vendor quotes are submitted through the Vendor Portal.
            This page is for reviewing submitted quotes only.
          </div>
          <div class="text-center mt-4">
            <a href="' . ADMINURL . 'vendor-quote-list" class="btn btn-primary">
                <i class="fa fa-list"></i> View All Quotes
            </a>
            <a href="' . ADMINURL . 'vendor-rfq-list" class="btn btn-info">
                <i class="fa fa-file-text"></i> View RFQs
            </a>
          </div>';
    return;
}

// Get quote details
$DB->sql = "SELECT q.*, v.legalName, v.vendorCode, v.contactEmail, v.contactPhone, v.gstNumber,
            r.rfqNumber, r.title as rfqTitle, r.rfqStatus, r.rfqType
            FROM mx_vendor_quote q
            JOIN mx_vendor_onboarding v ON q.vendorID = v.vendorID
            JOIN mx_vendor_rfq r ON q.rfqID = r.rfqID
            WHERE q.quoteID = ?";
$DB->vals = [$quoteID];
$DB->types = "i";
$row = $DB->dbRow();

if (!$row) {
    echo '<div class="alert alert-danger">Quote not found</div>';
    return;
}

// Get quote items
$DB->sql = "SELECT qi.*, ri.itemDescription as rfqItemDescription, ri.unit as rfqUnit, ri.quantity as rfqQuantity
            FROM mx_vendor_quote_item qi
            LEFT JOIN mx_vendor_rfq_item ri ON qi.rfqItemID = ri.itemID
            WHERE qi.quoteID = ? AND qi.status = 1
            ORDER BY qi.itemID";
$DB->vals = [$quoteID];
$DB->types = "i";
$items = $DB->dbRows();

$statusBadge = getQuoteStatusBadge($row["quoteStatus"]);
?>

<div class="form-header d-flex justify-content-between align-items-center">
    <div>
        <h4>Quote: <?php echo htmlentities($row["quoteNumber"]); ?></h4>
        <small class="text-muted">For RFQ: <?php echo htmlentities($row["rfqNumber"]); ?> - <?php echo htmlentities($row["rfqTitle"]); ?></small>
    </div>
    <div>
        <?php echo $statusBadge; ?>
    </div>
</div>

<!-- Vendor Information -->
<div class="card mb-3">
    <div class="card-header bg-info text-white">
        <i class="fa fa-building"></i> Vendor Information
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <p><strong>Vendor Name:</strong><br><?php echo htmlentities($row["legalName"]); ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>Vendor Code:</strong><br><?php echo htmlentities($row["vendorCode"]); ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>GST Number:</strong><br><?php echo htmlentities($row["gstNumber"] ?? "-"); ?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <p><strong>Contact Email:</strong><br><?php echo htmlentities($row["contactEmail"]); ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>Contact Phone:</strong><br><?php echo htmlentities($row["contactPhone"] ?? "-"); ?></p>
            </div>
            <div class="col-md-4">
                <p><strong>Submitted At:</strong><br><?php echo $row["submittedAt"] ? date("d-M-Y H:i", strtotime($row["submittedAt"])) : "-"; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Quote Summary -->
<div class="card mb-3">
    <div class="card-header bg-primary text-white">
        <i class="fa fa-file-text"></i> Quote Summary
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-light text-center p-3">
                    <h5 class="mb-0"><?php echo number_format($row["totalAmount"], 2); ?></h5>
                    <small>Total Amount</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light text-center p-3">
                    <h5 class="mb-0"><?php echo $row["deliveryDays"] ?? "-"; ?> Days</h5>
                    <small>Delivery Time</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light text-center p-3">
                    <h5 class="mb-0"><?php echo $row["validUntil"] ? date("d-M-Y", strtotime($row["validUntil"])) : "-"; ?></h5>
                    <small>Valid Until</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light text-center p-3">
                    <h5 class="mb-0"><?php echo htmlentities($row["paymentTerms"] ?? "-"); ?></h5>
                    <small>Payment Terms</small>
                </div>
            </div>
        </div>

        <?php if ($row["remarks"]): ?>
            <div class="mt-3">
                <strong>Vendor Remarks:</strong>
                <p class="mb-0"><?php echo nl2br(htmlentities($row["remarks"])); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($row["attachmentPath"]): ?>
            <div class="mt-3">
                <strong>Attachment:</strong>
                <a href="<?php echo UPLOADURL; ?>vendor-quote/<?php echo $row["attachmentPath"]; ?>" target="_blank" class="btn btn-sm btn-outline-primary ml-2">
                    <i class="fa fa-download"></i> <?php echo $row["attachmentPath"]; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quote Line Items -->
<div class="card mb-3">
    <div class="card-header bg-success text-white">
        <i class="fa fa-list"></i> Quote Line Items
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:50px">#</th>
                    <th>Description</th>
                    <th style="width:80px">Unit</th>
                    <th style="width:80px">RFQ Qty</th>
                    <th style="width:80px">Quoted Qty</th>
                    <th style="width:100px">Unit Rate</th>
                    <th style="width:100px">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalAmount = 0;
                foreach ($items as $idx => $item):
                    $amount = $item["quantity"] * $item["unitRate"];
                    $totalAmount += $amount;
                ?>
                    <tr>
                        <td><?php echo $idx + 1; ?></td>
                        <td>
                            <strong><?php echo htmlentities($item["rfqItemDescription"] ?? $item["itemDescription"] ?? "Item"); ?></strong>
                            <?php if ($item["vendorRemarks"]): ?>
                                <br><small class="text-info"><i class="fa fa-comment"></i> <?php echo htmlentities($item["vendorRemarks"]); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlentities($item["rfqUnit"] ?? $item["unit"]); ?></td>
                        <td class="text-right"><?php echo number_format($item["rfqQuantity"] ?? 0, 2); ?></td>
                        <td class="text-right"><?php echo number_format($item["quantity"], 2); ?></td>
                        <td class="text-right"><?php echo number_format($item["unitRate"], 2); ?></td>
                        <td class="text-right"><?php echo number_format($amount, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-info">
                    <td colspan="6" class="text-right"><strong>Sub Total:</strong></td>
                    <td class="text-right"><strong><?php echo number_format($totalAmount, 2); ?></strong></td>
                </tr>
                <?php if ($row["taxAmount"] > 0): ?>
                    <tr>
                        <td colspan="6" class="text-right">Tax (<?php echo $row["taxPercentage"] ?? 0; ?>%):</td>
                        <td class="text-right"><?php echo number_format($row["taxAmount"], 2); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($row["discountAmount"] > 0): ?>
                    <tr>
                        <td colspan="6" class="text-right">Discount:</td>
                        <td class="text-right">-<?php echo number_format($row["discountAmount"], 2); ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="table-success">
                    <td colspan="6" class="text-right"><strong>Grand Total:</strong></td>
                    <td class="text-right"><strong><?php echo number_format($row["totalAmount"], 2); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Internal Notes (Admin Only) -->
<div class="card mb-3">
    <div class="card-header bg-warning">
        <i class="fa fa-sticky-note"></i> Internal Notes (Not visible to vendor)
    </div>
    <div class="card-body">
        <form id="frmNotes">
            <input type="hidden" name="quoteID" value="<?php echo $quoteID; ?>">
            <div class="form-group">
                <textarea name="internalNotes" class="form-control" rows="3" placeholder="Add internal notes about this quote..."><?php echo htmlentities($row["internalNotes"] ?? ""); ?></textarea>
            </div>
            <button type="submit" class="btn btn-sm btn-warning">Save Notes</button>
        </form>
    </div>
</div>

<!-- Action Buttons -->
<div class="form-actions mb-4">
    <a href="<?php echo ADMINURL; ?>vendor-quote-list<?php echo $row["rfqID"] ? '?rfqID=' . $row["rfqID"] : ''; ?>" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back to List
    </a>
    <a href="<?php echo ADMINURL; ?>vendor-rfq-edit?rfqID=<?php echo $row["rfqID"]; ?>" class="btn btn-info">
        <i class="fa fa-file-text"></i> View RFQ
    </a>

    <?php if ($row["quoteStatus"] == "Submitted"): ?>
        <button class="btn btn-primary" onclick="shortlistQuote()"><i class="fa fa-star"></i> Shortlist</button>
        <button class="btn btn-warning" onclick="requestRevision()"><i class="fa fa-edit"></i> Request Revision</button>
        <button class="btn btn-danger" onclick="rejectQuote()"><i class="fa fa-times"></i> Reject</button>
    <?php endif; ?>

    <?php if ($row["quoteStatus"] == "Shortlisted"): ?>
        <button class="btn btn-success" onclick="awardQuote()"><i class="fa fa-trophy"></i> Award to Vendor</button>
        <button class="btn btn-danger" onclick="rejectQuote()"><i class="fa fa-times"></i> Reject</button>
    <?php endif; ?>

    <?php if ($row["quoteStatus"] == "Accepted"): ?>
        <span class="badge badge-success p-2"><i class="fa fa-check"></i> This quote has been accepted</span>
    <?php endif; ?>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="frmReject">
                <input type="hidden" name="xAction" value="REJECT">
                <input type="hidden" name="quoteID" value="<?php echo $quoteID; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Quote</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="rejectionReason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Quote</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Revision Request Modal -->
<div class="modal fade" id="revisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="frmRevision">
                <input type="hidden" name="xAction" value="REQUEST_REVISION">
                <input type="hidden" name="quoteID" value="<?php echo $quoteID; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Request Quote Revision</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Revision Comments <span class="text-danger">*</span></label>
                        <textarea name="revisionComments" class="form-control" rows="3" placeholder="Specify what changes you need..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Send Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function shortlistQuote() {
    if (confirm('Shortlist this quote for final consideration?')) {
        $.post('<?php echo ADMINURL; ?>vendor-quote-edit', {
            xAction: 'SHORTLIST',
            quoteID: <?php echo $quoteID; ?>,
            xToken: '<?php echo $_SESSION[SITEURL][CSRF_TOKEN]; ?>'
        }, function(response) {
            alert(response.msg);
            if (response.err == 0) location.reload();
        }, 'json');
    }
}

function rejectQuote() {
    $('#rejectModal').modal('show');
}

function requestRevision() {
    $('#revisionModal').modal('show');
}

function awardQuote() {
    if (confirm('Award this RFQ to the vendor? All other quotes will be rejected.')) {
        $.post('<?php echo ADMINURL; ?>vendor-rfq-edit', {
            xAction: 'AWARD',
            rfqID: <?php echo $row["rfqID"]; ?>,
            quoteID: <?php echo $quoteID; ?>,
            vendorID: <?php echo $row["vendorID"]; ?>,
            xToken: '<?php echo $_SESSION[SITEURL][CSRF_TOKEN]; ?>'
        }, function(response) {
            alert(response.msg);
            if (response.err == 0) location.reload();
        }, 'json');
    }
}

$('#frmReject').submit(function(e) {
    e.preventDefault();
    $.post('<?php echo ADMINURL; ?>vendor-quote-edit', $(this).serialize() + '&xToken=<?php echo $_SESSION[SITEURL][CSRF_TOKEN]; ?>', function(response) {
        alert(response.msg);
        if (response.err == 0) location.reload();
    }, 'json');
});

$('#frmRevision').submit(function(e) {
    e.preventDefault();
    $.post('<?php echo ADMINURL; ?>vendor-quote-edit', $(this).serialize() + '&xToken=<?php echo $_SESSION[SITEURL][CSRF_TOKEN]; ?>', function(response) {
        alert(response.msg);
        if (response.err == 0) location.reload();
    }, 'json');
});

$('#frmNotes').submit(function(e) {
    e.preventDefault();
    // TODO: Save notes via AJAX
    alert('Notes saved!');
});
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/vendor-quote/x-vendor-quote.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/vendor-quote/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>

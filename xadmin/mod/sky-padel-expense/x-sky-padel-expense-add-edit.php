<?php
$MXID = isset($_REQUEST["id"]) ? intval($_REQUEST["id"]) : 0;
$MXROW = array();

// Get expense data if editing
if ($MXID > 0) {
    $DB->vals = array($MXID);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE " . $MXMOD["PK"] . "=?";
    $MXROW = $DB->dbRow();
}

// Get project from URL if provided
$presetProjectID = isset($_GET["projectID"]) ? intval($_GET["projectID"]) : ($MXROW["projectID"] ?? 0);

$categories = $GLOBALS["EXPENSE_CATEGORIES"];
?>

<style>
.expense-form { max-width: 900px; }
.expense-form .form-row { display: flex; gap: 16px; margin-bottom: 16px; }
.expense-form .form-group { flex: 1; }
.expense-form .form-group.small { flex: 0 0 150px; }
.expense-form label { display: block; font-weight: 600; margin-bottom: 6px; color: #374151; }
.expense-form input, .expense-form select, .expense-form textarea {
    width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;
}
.expense-form input:focus, .expense-form select:focus, .expense-form textarea:focus {
    border-color: #0d9488; outline: none; box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
}
.amount-display {
    background: #f0fdf4; border: 2px solid #22c55e; padding: 16px; border-radius: 8px; text-align: center;
}
.amount-display .label { font-size: 12px; color: #6b7280; text-transform: uppercase; }
.amount-display .value { font-size: 28px; font-weight: 700; color: #059669; }
.section-title {
    font-size: 14px; font-weight: 700; color: #6b7280; text-transform: uppercase;
    letter-spacing: 1px; margin: 24px 0 16px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;
}
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <form id="expenseForm" class="expense-form" method="post">
            <input type="hidden" name="expenseID" value="<?php echo $MXID; ?>">

            <div class="section-title">Project & Basic Info</div>

            <div class="form-row">
                <div class="form-group">
                    <label>Project *</label>
                    <select name="projectID" id="projectID" required>
                        <?php echo getProjectDropdownForExpense($presetProjectID); ?>
                    </select>
                </div>
                <div class="form-group small">
                    <label>Date *</label>
                    <input type="date" name="expenseDate" value="<?php echo $MXROW["expenseDate"] ?? date("Y-m-d"); ?>" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="expenseCategory" required>
                        <?php foreach ($categories as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo (($MXROW["expenseCategory"] ?? "") == $key) ? "selected" : ""; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" value="<?php echo htmlspecialchars($MXROW["description"] ?? ""); ?>" placeholder="Brief description of expense">
                </div>
            </div>

            <div class="section-title">Vendor & Invoice</div>

            <div class="form-row">
                <div class="form-group">
                    <label>Vendor Name</label>
                    <input type="text" name="vendorName" value="<?php echo htmlspecialchars($MXROW["vendorName"] ?? ""); ?>" placeholder="Vendor or supplier name">
                </div>
                <div class="form-group small">
                    <label>Invoice No</label>
                    <input type="text" name="invoiceNo" value="<?php echo htmlspecialchars($MXROW["invoiceNo"] ?? ""); ?>" placeholder="INV-XXX">
                </div>
            </div>

            <div class="section-title">Amount Details</div>

            <div class="form-row">
                <div class="form-group">
                    <label>Amount (Before Tax) *</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0" value="<?php echo $MXROW["amount"] ?? ""; ?>" required placeholder="0.00" onchange="calculateTotal()">
                </div>
                <div class="form-group">
                    <label>Tax Amount</label>
                    <input type="number" name="taxAmount" id="taxAmount" step="0.01" min="0" value="<?php echo $MXROW["taxAmount"] ?? 0; ?>" placeholder="0.00" onchange="calculateTotal()">
                </div>
                <div class="form-group">
                    <div class="amount-display">
                        <div class="label">Total Amount</div>
                        <div class="value" id="totalDisplay">₹0</div>
                        <input type="hidden" name="totalAmount" id="totalAmount" value="<?php echo $MXROW["totalAmount"] ?? 0; ?>">
                    </div>
                </div>
            </div>

            <div class="section-title">Payment Status</div>

            <div class="form-row">
                <div class="form-group small">
                    <label>Payment Status</label>
                    <select name="paymentStatus" id="paymentStatus" onchange="togglePaymentFields()">
                        <option value="Pending" <?php echo (($MXROW["paymentStatus"] ?? "") == "Pending") ? "selected" : ""; ?>>Pending</option>
                        <option value="Partial" <?php echo (($MXROW["paymentStatus"] ?? "") == "Partial") ? "selected" : ""; ?>>Partial</option>
                        <option value="Paid" <?php echo (($MXROW["paymentStatus"] ?? "") == "Paid") ? "selected" : ""; ?>>Paid</option>
                    </select>
                </div>
                <div class="form-group" id="paidAmountGroup">
                    <label>Paid Amount</label>
                    <input type="number" name="paidAmount" id="paidAmount" step="0.01" min="0" value="<?php echo $MXROW["paidAmount"] ?? 0; ?>" placeholder="0.00">
                </div>
                <div class="form-group" id="paymentModeGroup">
                    <label>Payment Mode</label>
                    <select name="paymentMode">
                        <option value="">Select</option>
                        <option value="Cash" <?php echo (($MXROW["paymentMode"] ?? "") == "Cash") ? "selected" : ""; ?>>Cash</option>
                        <option value="Bank Transfer" <?php echo (($MXROW["paymentMode"] ?? "") == "Bank Transfer") ? "selected" : ""; ?>>Bank Transfer</option>
                        <option value="UPI" <?php echo (($MXROW["paymentMode"] ?? "") == "UPI") ? "selected" : ""; ?>>UPI</option>
                        <option value="Cheque" <?php echo (($MXROW["paymentMode"] ?? "") == "Cheque") ? "selected" : ""; ?>>Cheque</option>
                    </select>
                </div>
                <div class="form-group" id="paymentRefGroup">
                    <label>Payment Reference</label>
                    <input type="text" name="paymentRef" value="<?php echo htmlspecialchars($MXROW["paymentRef"] ?? ""); ?>" placeholder="Transaction ID / Cheque No">
                </div>
            </div>

            <div class="section-title">Notes</div>

            <div class="form-row">
                <div class="form-group">
                    <textarea name="notes" rows="3" placeholder="Additional notes..."><?php echo htmlspecialchars($MXROW["notes"] ?? ""); ?></textarea>
                </div>
            </div>

            <div class="form-row" style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 32px; background: #0d9488; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <?php echo $MXID > 0 ? "Update Expense" : "Add Expense"; ?>
                </button>
                <a href="<?php echo ADMINURL; ?>/sky-padel-expense-list/" class="btn btn-secondary" style="padding: 12px 32px; background: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-weight: 600; text-decoration: none; margin-left: 12px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function calculateTotal() {
    var amount = parseFloat(document.getElementById('amount').value) || 0;
    var tax = parseFloat(document.getElementById('taxAmount').value) || 0;
    var total = amount + tax;

    document.getElementById('totalAmount').value = total.toFixed(2);
    document.getElementById('totalDisplay').textContent = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function togglePaymentFields() {
    var status = document.getElementById('paymentStatus').value;
    var showPayment = (status === 'Partial' || status === 'Paid');

    document.getElementById('paidAmountGroup').style.display = showPayment ? 'block' : 'none';
    document.getElementById('paymentModeGroup').style.display = showPayment ? 'block' : 'none';
    document.getElementById('paymentRefGroup').style.display = showPayment ? 'block' : 'none';

    if (status === 'Paid') {
        document.getElementById('paidAmount').value = document.getElementById('totalAmount').value;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
    togglePaymentFields();
});
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/sky-padel-expense/x-sky-padel-expense.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/sky-padel-expense/';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
var ADMINURL = '<?php echo ADMINURL; ?>';
</script>

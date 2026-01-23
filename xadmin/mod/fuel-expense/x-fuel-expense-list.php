<div style="position:fixed;top:0;left:0;width:10px;height:10px;background:red;z-index:99999;" title="LIST FILE"></div>
<?php
/**
 * Fuel Expense List & Report Page
 * Displays monthly summary report with payment status breakdown
 * Uses xadmin standard look and feel
 */

global $DB, $MXFRM, $MXSTATUS, $TPL;

// Initialize form handler
$MXFRM = new mxForm();

// Get list of vehicles for dropdown
$vehicleOptions = array("" => "All Vehicles");
$DB->sql = "SELECT vehicleID, vehicleName FROM `" . $DB->pre . "vehicle` WHERE status=1 ORDER BY vehicleName";
$DB->dbRows();
if (isset($DB->rows) && is_array($DB->rows)) {
    foreach ($DB->rows as $v) {
        $vehicleOptions[$v["vehicleID"]] = $v["vehicleName"];
    }
}

// Build vehicle dropdown for search
$vehicleDD = getArrayDD(array("data" => array("data" => $vehicleOptions), "selected" => ($_GET["vehicleID"] ?? "")));

// Build payment status dropdown for search
$statusOptions = array("" => "All Status", "Paid" => "Paid", "Unpaid" => "Unpaid");
$statusDD = getArrayDD(array("data" => array("data" => $statusOptions), "selected" => ($_GET["paymentStatus"] ?? "")));

// Define search fields
$arrSearch = array(
    array("type" => "select", "name" => "vehicleID",
          "value" => $vehicleDD,
          "title" => "Vehicle", "where" => "AND vehicleID=?", "dtype" => "s"),
    array("type" => "select", "name" => "paymentStatus",
          "value" => $statusDD,
          "title" => "Payment Status", "where" => "AND paymentStatus=?", "dtype" => "s"),
    array("type" => "date", "name" => "fromDate", "value" => $_GET["fromDate"] ?? "",
          "title" => "From Date", "where" => "AND billDate >= ?", "dtype" => "s"),
    array("type" => "date", "name" => "toDate", "value" => $_GET["toDate"] ?? "",
          "title" => "To Date", "where" => "AND billDate <= ?", "dtype" => "s"),
);

// Generate search form
$strSearch = $MXFRM->getFormS($arrSearch);

// Build count query - use mxFramework values directly
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;

$DB->sql = "SELECT fuelExpenseID FROM `" . $DB->pre . "fuel_expense`
            WHERE status=?" . $MXFRM->where;
$DB->dbRows();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";

echo $strSearch;

// Build the Mark as Paid button for bulk actions
$moreRButtons = '<button id="bulkMarkPaidBtn" class="btn fa-check" onclick="bulkMarkAsPaid()" title="Mark Selected as Paid"> Mark Paid</button>';
?>

<div class="wrap-right">
    <?php echo getPageNav('', $moreRButtons, array("add")); ?>

    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array('<input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"/>', "", ' width="5%" align="center"'),
                array("Date", "billDate", ' width="11%" align="center"'),
                array("Vehicle", "vehicleName", ' width="20%" align="left"'),
                array("Amount", "expenseAmount", ' width="12%" align="right"'),
                array("Status", "paymentStatus", ' width="10%" align="center"'),
                array("Paid Date", "paidDate", ' width="11%" align="center"'),
                array("Bill Image", "billImage", ' width="10%" align="center"'),
                array("Remarks", "remarks", ' width="13%" align="left"'),
            );

            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;

            $DB->sql = "SELECT fe.fuelExpenseID, fe.billDate, fe.expenseAmount, fe.paymentStatus, fe.paidDate, fe.remarks, fe.billImage, v.vehicleName
                        FROM `" . $DB->pre . "fuel_expense` fe
                        LEFT JOIN `" . $DB->pre . "vehicle` v ON fe.vehicleID = v.vehicleID
                        WHERE fe.status=?" . $MXFRM->where . mxOrderBy("fe.billDate DESC ") . mxQryLimit();
            $rt = $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr><?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    $totalPaid = 0;
                    $totalUnpaid = 0;
                    foreach ($DB->rows as $expense) {
                        $isPaid = $expense["paymentStatus"] === "Paid";
                        if ($isPaid) {
                            $totalPaid += $expense["expenseAmount"];
                        } else {
                            $totalUnpaid += $expense["expenseAmount"];
                        }

                        $statusBadge = $isPaid ?
                            '<span style="background-color: #28a745; color: white; padding: 3px 8px; border-radius: 3px; cursor: pointer;" onclick="updatePaymentStatus(' . $expense["fuelExpenseID"] . ', \'Unpaid\')">PAID</span>' :
                            '<span style="background-color: #ffc107; color: black; padding: 3px 8px; border-radius: 3px; cursor: pointer;" onclick="updatePaymentStatus(' . $expense["fuelExpenseID"] . ', \'Paid\')">UNPAID</span>';

                        // Bill image download link
                        $billImageLink = "";
                        if (!empty($expense["billImage"])) {
                            $fileExt = strtolower(pathinfo($expense["billImage"], PATHINFO_EXTENSION));
                            $icon = ($fileExt === 'pdf') ? '📄' : '🖼️';
                            $fileType = ($fileExt === 'pdf') ? 'PDF' : 'Image';
                            $billImageLink = '<a href="/uploads/fuel-expense/' . htmlspecialchars($expense["billImage"]) . '" target="_blank" download style="background-color: #007bff; color: white; padding: 4px 8px; border-radius: 3px; text-decoration: none; font-size: 0.85rem;">' . $icon . ' ' . $fileType . '</a>';
                        } else {
                            $billImageLink = '<span style="color: #999;">No file</span>';
                        }

                        // Show checkbox for all expenses (both paid and unpaid)
                        $checkboxCell = '<input type="checkbox" class="expense-checkbox" value="' . $expense["fuelExpenseID"] . '" data-status="' . $expense["paymentStatus"] . '" />';
                    ?>
                        <tr>
                            <td width="5%" align="center"><?php echo $checkboxCell; ?></td>
                            <?php echo getMAction("mid", $expense["fuelExpenseID"]); ?>
                            <td width="11%" align="center" title="Date"><?php echo date('d-M-Y', strtotime($expense["billDate"])); ?></td>
                            <td width="20%" align="left" title="Vehicle">
                                <?php echo getViewEditUrl("id=" . $expense["fuelExpenseID"], $expense["vehicleName"] ?? "Unknown"); ?>
                            </td>
                            <td width="12%" align="right" title="Amount">₹ <?php echo number_format($expense["expenseAmount"], 2); ?></td>
                            <td width="10%" align="center" title="Status"><?php echo $statusBadge; ?></td>
                            <td width="11%" align="center" title="Paid Date"><?php echo $expense["paidDate"] ? date('d-M-Y', strtotime($expense["paidDate"])) : "-"; ?></td>
                            <td width="10%" align="center" title="Bill Image"><?php echo $billImageLink; ?></td>
                            <td width="13%" align="left" title="Remarks"><?php echo htmlspecialchars(substr($expense["remarks"] ?? "", 0, 30)); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr style='text-align:right;' class='trcolspan'>
                        <th colspan='3'>&nbsp;</th>
                        <th>Total Unpaid:</th>
                        <th style="color: black;">₹ <?php echo number_format($totalUnpaid, 2); ?></th>
                        <th>Total Paid:</th>
                        <th style="color: black;">₹ <?php echo number_format($totalPaid, 2); ?></th>
                        <th colspan='3'>&nbsp;</th>
                    </tr>
                </tfoot>
            </table>

        <?php } else { ?>
            <div class="no-records">No expenses found</div>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">

// Toggle select all checkboxes
function toggleSelectAll(checkbox) {
    var checkboxes = document.querySelectorAll('.expense-checkbox');
    checkboxes.forEach(function(cb) {
        cb.checked = checkbox.checked;
    });
    updateBulkButtonState();
}

// Update checkbox listeners and button state
function initializeCheckboxes() {
    var checkboxes = document.querySelectorAll('.expense-checkbox');
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateBulkButtonState);
    });
}

// Update bulk action button state (just check if any selected)
function updateBulkButtonState() {
    // Button stays visible - just validates on click
}

// Bulk mark selected expenses as paid or unpaid
function bulkMarkAsPaid() {
    var checkboxes = document.querySelectorAll('.expense-checkbox:checked');

    if (checkboxes.length === 0) {
        alert('Please select at least one expense');
        return;
    }

    // Separate paid and unpaid expenses
    var unpaidIDs = [];
    var paidIDs = [];

    checkboxes.forEach(function(cb) {
        var status = cb.getAttribute('data-status');
        if (status === 'Paid') {
            paidIDs.push(cb.value);
        } else {
            unpaidIDs.push(cb.value);
        }
    });

    // Determine action based on selection
    var actionText = '';
    var allIDs = [];
    var action = '';

    if (unpaidIDs.length > 0 && paidIDs.length === 0) {
        // All selected are unpaid - mark as paid
        actionText = 'Mark ' + unpaidIDs.length + ' selected expense(s) as PAID?';
        allIDs = unpaidIDs;
        action = 'MARK_PAID';
    } else if (paidIDs.length > 0 && unpaidIDs.length === 0) {
        // All selected are paid - mark as unpaid
        actionText = 'Mark ' + paidIDs.length + ' selected expense(s) as UNPAID?';
        allIDs = paidIDs;
        action = 'MARK_UNPAID';
    } else {
        // Mixed selection
        alert('Please select expenses with the same status (all Paid or all Unpaid)');
        return;
    }

    if (!confirm(actionText)) {
        return;
    }

    // Show progress
    var bulkBtn = document.getElementById('bulkMarkPaidBtn');
    var originalText = bulkBtn.textContent;
    bulkBtn.textContent = 'Processing...';
    bulkBtn.disabled = true;

    // Process each expense
    var successCount = 0;
    var failureCount = 0;
    var processed = 0;

    function processNext() {
        if (processed >= allIDs.length) {
            // All done
            bulkBtn.textContent = originalText;
            bulkBtn.disabled = false;

            var message = 'Completed: ' + successCount + ' updated';
            if (failureCount > 0) {
                message += ', ' + failureCount + ' failed';
            }
            alert(message);

            // Reload page after a short delay
            setTimeout(function() {
                location.reload();
            }, 500);
            return;
        }

        var expenseID = allIDs[processed];
        var formData = new FormData();
        formData.append('xAction', action);
        formData.append('fuelExpenseID', expenseID);

        fetch('<?php echo ADMINURL . "/mod/fuel-expense/x-fuel-expense.inc.php"; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.err === 0) {
                successCount++;
            } else {
                failureCount++;
            }
            processed++;
            processNext();
        })
        .catch(error => {
            failureCount++;
            processed++;
            processNext();
        });
    }

    processNext();
}

// Update payment status directly via database (single item)
function updatePaymentStatus(fuelExpenseID, newStatus) {
    if (!confirm('Mark this expense as ' + newStatus + '?')) {
        return;
    }

    // Use fetch API to submit POST data without page redirect
    var formData = new FormData();
    formData.append('xAction', newStatus === 'Paid' ? 'MARK_PAID' : 'MARK_UNPAID');
    formData.append('fuelExpenseID', fuelExpenseID);

    fetch('<?php echo ADMINURL . "/mod/fuel-expense/x-fuel-expense.inc.php"; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.err === 0) {
            // Success - reload the page to show updated status
            setTimeout(function() {
                location.reload();
            }, 500);
        } else {
            alert('Error: ' + (data.msg || 'Failed to update status'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCheckboxes();
});

</script>


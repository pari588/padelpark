<?php
// Get pending requisitions
$DB->sql = "SELECT r.*, CONCAT(c.firstName, ' ', IFNULL(c.lastName,'')) as coachName
            FROM " . $DB->pre . "ipa_requisition r
            LEFT JOIN " . $DB->pre . "ipa_coach c ON r.coachID = c.coachID
            WHERE r.status=1 AND r.requisitionStatus='Submitted'
            ORDER BY r.requisitionDate DESC, r.requisitionID DESC";
$DB->dbRows();
$pendingRequisitions = $DB->rows ?: array();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <div class="wrap-form">
            <h2 class="form-head">Pending Approvals</h2>
        </div>

        <?php if (!empty($pendingRequisitions)): ?>
        <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
            <thead>
                <tr>
                    <th align="left">Req No</th>
                    <th align="center" width="12%">Date</th>
                    <th align="left" width="18%">Coach</th>
                    <th align="center" width="10%">Items</th>
                    <th align="center" width="12%">Required By</th>
                    <th align="center" width="15%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingRequisitions as $r): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r["requisitionNo"]); ?></strong></td>
                    <td align="center"><?php echo date("d M Y", strtotime($r["requisitionDate"])); ?></td>
                    <td><?php echo htmlspecialchars($r["coachName"]); ?></td>
                    <td align="center"><span class="badge badge-info"><?php echo $r["totalItems"]; ?></span></td>
                    <td align="center"><?php echo $r["requiredByDate"] ? date("d M Y", strtotime($r["requiredByDate"])) : "-"; ?></td>
                    <td align="center">
                        <button type="button" class="btn btn-sm btn-primary" onclick="viewRequisition(<?php echo $r['requisitionID']; ?>)">
                            <i class="fa fa-eye"></i> Review
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align:center; padding:60px 20px;">
            <i class="fa fa-check-circle" style="font-size:48px;color:#10b981;margin-bottom:15px;display:block;"></i>
            <p style="margin:0; color:#888;">No pending approvals</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; overflow:auto;">
    <div style="background:#fff; max-width:800px; margin:50px auto; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.2);">
        <div style="padding:15px 20px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0;" id="modalTitle">Review Requisition</h3>
            <button type="button" onclick="closeModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:#999;">&times;</button>
        </div>
        <div id="modalContent" style="padding:20px; max-height:60vh; overflow-y:auto;">
            <!-- Content loaded via AJAX -->
        </div>
        <div style="padding:15px 20px; border-top:1px solid #eee; background:#f8f9fa;">
            <button type="button" class="btn btn-success" onclick="approveRequisition()">
                <i class="fa fa-check"></i> Approve
            </button>
            <button type="button" class="btn btn-danger" onclick="showRejectForm()">
                <i class="fa fa-times"></i> Reject
            </button>
            <button type="button" class="btn btn-default" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000; overflow:auto;">
    <div style="background:#fff; max-width:400px; margin:100px auto; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.2);">
        <div style="padding:15px 20px; border-bottom:1px solid #eee;">
            <h3 style="margin:0; color:#dc2626;">Reject Requisition</h3>
        </div>
        <div style="padding:20px;">
            <label style="display:block; margin-bottom:8px; font-weight:500;">Reason for Rejection</label>
            <textarea id="rejectionReason" rows="3" class="inp-fld" style="width:100%;" placeholder="Please provide a reason..."></textarea>
        </div>
        <div style="padding:15px 20px; border-top:1px solid #eee; background:#f8f9fa;">
            <button type="button" class="btn btn-danger" onclick="confirmReject()">
                <i class="fa fa-times"></i> Confirm Rejection
            </button>
            <button type="button" class="btn btn-default" onclick="$('#rejectModal').hide();">Cancel</button>
        </div>
    </div>
</div>

<script>
var currentRequisitionID = 0;

function viewRequisition(requisitionID) {
    currentRequisitionID = requisitionID;

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-requisition/x-ipa-requisition.inc.php',
        type: 'POST',
        data: {xAction: 'GET_ITEMS', requisitionID: requisitionID},
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                renderApprovalForm(res.items);
                $('#approvalModal').show();
            } else {
                alert('Error loading requisition');
            }
        }
    });
}

function renderApprovalForm(items) {
    var html = '<p style="margin-bottom:15px; color:#666;">Review items and adjust approved quantities as needed:</p>';

    html += '<table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">';
    html += '<thead><tr>';
    html += '<th align="left">Product</th>';
    html += '<th align="center" width="80">SKU</th>';
    html += '<th align="center" width="80">Stock</th>';
    html += '<th align="center" width="80">Requested</th>';
    html += '<th align="center" width="100">Approved</th>';
    html += '</tr></thead>';
    html += '<tbody id="approvalItems">';

    items.forEach(function(item) {
        var stockQty = parseFloat(item.stockQty) || 0;
        var stockClass = stockQty >= item.requestedQty ? 'color:#10b981;' : 'color:#ef4444;';

        html += '<tr data-item-id="' + item.itemID + '">';
        html += '<td><strong>' + item.productName + '</strong></td>';
        html += '<td align="center"><small>' + (item.productSKU || '-') + '</small></td>';
        html += '<td align="center" style="' + stockClass + 'font-weight:600;">' + stockQty.toFixed(0) + '</td>';
        html += '<td align="center">' + item.requestedQty + ' ' + item.unit + '</td>';
        html += '<td align="center"><input type="number" class="inp-fld approved-qty" min="0" max="' + item.requestedQty + '" value="' + item.requestedQty + '" style="width:70px; text-align:center;"></td>';
        html += '</tr>';
    });

    html += '</tbody></table>';
    $('#modalContent').html(html);
}

function closeModal() {
    $('#approvalModal').hide();
    currentRequisitionID = 0;
}

function approveRequisition() {
    if (!currentRequisitionID) return;

    var items = [];
    $('#approvalItems tr').each(function() {
        items.push({
            itemID: $(this).data('item-id'),
            approvedQty: $(this).find('.approved-qty').val()
        });
    });

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-requisition/x-ipa-requisition.inc.php',
        type: 'POST',
        data: {
            xAction: 'APPROVE',
            requisitionID: currentRequisitionID,
            items: JSON.stringify(items)
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert('Requisition approved');
                location.reload();
            } else {
                alert('Error: ' + res.msg);
            }
        }
    });
}

function showRejectForm() {
    $('#rejectModal').show();
}

function confirmReject() {
    var reason = $('#rejectionReason').val();
    if (!reason.trim()) {
        alert('Please provide a rejection reason');
        return;
    }

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-requisition/x-ipa-requisition.inc.php',
        type: 'POST',
        data: {
            xAction: 'REJECT',
            requisitionID: currentRequisitionID,
            rejectionReason: reason
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert('Requisition rejected');
                location.reload();
            } else {
                alert('Error: ' + res.msg);
            }
        }
    });
}

// Close modals on outside click
$('#approvalModal, #rejectModal').on('click', function(e) {
    if (e.target === this) {
        $(this).hide();
    }
});
</script>

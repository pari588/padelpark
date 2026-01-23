<?php
$id = 0;
$D = array();

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT c.*, CONCAT(co.firstName, ' ', IFNULL(co.lastName,'')) as coachName
                FROM " . $DB->pre . "ipa_coach_commission c
                LEFT JOIN " . $DB->pre . "ipa_coach co ON c.coachID = co.coachID
                WHERE c.status=? AND c.commissionID=?";
    $D = $DB->dbRow();
}

// Build coach dropdown with their commission rates
$DB->sql = "SELECT coachID, CONCAT(firstName, ' ', IFNULL(lastName,'')) as coachName, commissionRate FROM " . $DB->pre . "ipa_coach WHERE status=1 ORDER BY firstName";
$coachRows = $DB->dbRows() ?: array();
$coachOpt = '<option value="" data-rate="5">-- Select Coach --</option>';
$selCoach = $D["coachID"] ?? "";
foreach ($coachRows as $c) {
    $sel = ($selCoach == $c["coachID"]) ? ' selected="selected"' : '';
    $coachOpt .= '<option value="' . $c["coachID"] . '" data-rate="' . $c["commissionRate"] . '"' . $sel . '>' . htmlspecialchars($c["coachName"]) . ' (' . $c["commissionRate"] . '%)</option>';
}

$canEdit = !$id || in_array(($D["commissionStatus"] ?? ""), ["Pending"]);

$arrFormDetails = array(
    array("type" => "select", "name" => "coachID", "value" => $coachOpt, "title" => "Coach", "validate" => "required", "params" => array("id" => "coachID", "onchange" => "updateRate()")),
    array("type" => "date", "name" => "saleDate", "value" => $D["saleDate"] ?? date("Y-m-d"), "title" => "Sale Date", "validate" => "required"),
    array("type" => "text", "name" => "saleAmount", "value" => $D["saleAmount"] ?? "", "title" => "Sale Amount (Rs.)", "validate" => "required,number", "params" => array("id" => "saleAmount", "onkeyup" => "calculateCommission()")),
    array("type" => "text", "name" => "commissionRate", "value" => $D["commissionRate"] ?? "5", "title" => "Commission Rate (%)", "params" => array("id" => "commissionRate", "onkeyup" => "calculateCommission()")),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 2)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post">
        <input type="hidden" name="commissionID" value="<?php echo $id; ?>">

        <?php if ($id): ?>
        <div class="wrap-form">
            <h2 class="form-head">Commission Status</h2>
            <ul class="tbl-form">
                <li>
                    <label>Current Status</label>
                    <div class="field-val">
                        <?php
                        $statusColors = ["Pending" => "#f59e0b", "Approved" => "#3b82f6", "Paid" => "#10b981", "Cancelled" => "#ef4444"];
                        $statusColor = $statusColors[$D["commissionStatus"]] ?? "#6b7280";
                        ?>
                        <span style="background:<?php echo $statusColor; ?>;color:#fff;padding:4px 12px;border-radius:4px;font-size:12px;">
                            <?php echo $D["commissionStatus"]; ?>
                        </span>

                        <?php if ($D["commissionStatus"] == "Pending"): ?>
                        <button type="button" class="btn btn-sm btn-success" style="margin-left:10px;" onclick="approveCommission()">
                            <i class="fa fa-check"></i> Approve
                        </button>
                        <?php endif; ?>

                        <?php if ($D["commissionStatus"] == "Approved"): ?>
                        <button type="button" class="btn btn-sm btn-primary" style="margin-left:10px;" onclick="$('#paymentModal').show();">
                            <i class="fa fa-money-bill"></i> Mark as Paid
                        </button>
                        <?php endif; ?>
                    </div>
                </li>
                <?php if ($D["commissionStatus"] == "Paid" && $D["paymentDate"]): ?>
                <li>
                    <label>Payment Info</label>
                    <div class="field-val">
                        Paid on <?php echo date("d M Y", strtotime($D["paymentDate"])); ?>
                        <?php if ($D["paymentReference"]): ?>(Ref: <?php echo htmlspecialchars($D["paymentReference"]); ?>)<?php endif; ?>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="wrap-form">
            <h2 class="form-head"><?php echo $id ? "Edit Commission" : "Add Commission"; ?></h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormDetails); ?>
                <li>
                    <label>Commission Amount (Rs.)</label>
                    <div class="field-val">
                        <input type="text" id="commissionAmountDisplay" class="inp-fld" value="<?php echo number_format($D["commissionAmount"] ?? 0, 2); ?>" disabled style="background:#f0fdf4; font-weight:700; color:#10b981; width:150px;">
                    </div>
                </li>
            </ul>
        </div>

        <?php if ($canEdit): ?>
        <div class="wrap-form">
            <ul class="tbl-form">
                <li class="frm-btn">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary" onclick="saveCommission()">
                        <i class="fa fa-save"></i> <?php echo $id ? "Update" : "Save"; ?> Commission
                    </button>
                </li>
            </ul>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- Payment Modal -->
<?php if ($id && $D["commissionStatus"] == "Approved"): ?>
<div id="paymentModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="background:#fff; max-width:400px; margin:100px auto; border-radius:8px; box-shadow:0 4px 20px rgba(0,0,0,0.2);">
        <div style="padding:15px 20px; border-bottom:1px solid #eee;">
            <h3 style="margin:0;">Mark as Paid</h3>
        </div>
        <div style="padding:20px;">
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px;">Payment Date</label>
                <input type="date" id="paymentDate" class="inp-fld" value="<?php echo date("Y-m-d"); ?>" style="width:100%;">
            </div>
            <div>
                <label style="display:block; margin-bottom:5px;">Payment Reference</label>
                <input type="text" id="paymentReference" class="inp-fld" placeholder="e.g., Bank Ref, Cheque No" style="width:100%;">
            </div>
        </div>
        <div style="padding:15px 20px; border-top:1px solid #eee; background:#f8f9fa;">
            <button type="button" class="btn btn-success" onclick="markPaid()">
                <i class="fa fa-check"></i> Confirm Payment
            </button>
            <button type="button" class="btn btn-default" onclick="$('#paymentModal').hide();">Cancel</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
    calculateCommission();

    <?php if (!$canEdit): ?>
    $('#frmAddEdit select, #frmAddEdit input, #frmAddEdit textarea').prop('disabled', true);
    <?php endif; ?>
});

function updateRate() {
    var rate = $('#coachID option:selected').data('rate') || 5;
    $('#commissionRate').val(rate);
    calculateCommission();
}

function calculateCommission() {
    var amount = parseFloat($('#saleAmount').val()) || 0;
    var rate = parseFloat($('#commissionRate').val()) || 0;
    var commission = (amount * rate / 100).toFixed(2);
    $('#commissionAmountDisplay').val(commission);
}

function saveCommission() {
    var coachID = $('#coachID').val();
    if (!coachID) {
        alert('Please select a coach');
        return;
    }

    var saleAmount = $('#saleAmount').val();
    if (!saleAmount || parseFloat(saleAmount) <= 0) {
        alert('Please enter a valid sale amount');
        return;
    }

    var commissionID = $('input[name="commissionID"]').val();

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-commission/x-ipa-commission.inc.php',
        type: 'POST',
        data: {
            xAction: commissionID > 0 ? 'UPDATE' : 'ADD',
            commissionID: commissionID,
            coachID: coachID,
            saleDate: $('input[name="saleDate"]').val(),
            saleAmount: saleAmount,
            commissionRate: $('#commissionRate').val(),
            notes: $('textarea[name="notes"]').val()
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert(res.msg);
                window.location.href = '<?php echo ADMINURL; ?>/ipa-commission-list/';
            } else {
                alert('Error: ' + res.msg);
            }
        },
        error: function(xhr) {
            console.log('Error:', xhr.responseText);
            alert('Request failed');
        }
    });
}

<?php if ($id): ?>
function approveCommission() {
    if (!confirm('Approve this commission?')) return;

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-commission/x-ipa-commission.inc.php',
        type: 'POST',
        data: {xAction: 'APPROVE', commissionID: <?php echo $id; ?>},
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert('Commission approved');
                location.reload();
            } else {
                alert('Error: ' + res.msg);
            }
        }
    });
}

function markPaid() {
    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipa-commission/x-ipa-commission.inc.php',
        type: 'POST',
        data: {
            xAction: 'MARK_PAID',
            commissionID: <?php echo $id; ?>,
            paymentDate: $('#paymentDate').val(),
            paymentReference: $('#paymentReference').val()
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                alert('Commission marked as paid');
                location.reload();
            } else {
                alert('Error: ' + res.msg);
            }
        }
    });
}
<?php endif; ?>
</script>

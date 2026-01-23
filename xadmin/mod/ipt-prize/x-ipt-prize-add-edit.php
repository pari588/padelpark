<?php
$id = 0;
$D = array();
if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();
}

// Get tournaments dropdown
$DB->sql = "SELECT tournamentID, tournamentCode, tournamentName FROM " . $DB->pre . "ipt_tournament WHERE status=1 ORDER BY startDate DESC";
$tournaments = $DB->dbRows();
$tournamentOpt = '<option value="">Select Tournament</option>';
$selTournament = $D["tournamentID"] ?? ($_GET["tournamentID"] ?? "");
foreach ($tournaments as $t) {
    $sel = ($selTournament == $t["tournamentID"]) ? ' selected="selected"' : '';
    $tournamentOpt .= '<option value="' . $t["tournamentID"] . '"' . $sel . '>' . htmlspecialchars($t["tournamentCode"] . " - " . $t["tournamentName"]) . '</option>';
}

// Get tournament categories based on selected tournament
$categoryOpt = '<option value="">Select Category</option>';
if ($selTournament) {
    $DB->vals = array($selTournament);
    $DB->types = "i";
    $DB->sql = "SELECT tc.tcID, c.categoryName, c.categoryType
                FROM " . $DB->pre . "ipt_tournament_category tc
                JOIN " . $DB->pre . "ipt_category c ON tc.categoryID=c.categoryID
                WHERE tc.tournamentID=? AND tc.status=1
                ORDER BY c.categoryName";
    $categories = $DB->dbRows();
    $selCategory = $D["tcID"] ?? "";
    foreach ($categories as $c) {
        $sel = ($selCategory == $c["tcID"]) ? ' selected="selected"' : '';
        $categoryOpt .= '<option value="' . $c["tcID"] . '"' . $sel . '>' . htmlspecialchars($c["categoryName"] . " (" . $c["categoryType"] . ")") . '</option>';
    }
}

// Build position dropdown
$positions = array("Winner", "Runner-Up", "Semi-Finalist", "Quarter-Finalist");
$posOpt = "";
$currentPos = $D["position"] ?? "Winner";
foreach ($positions as $pos) {
    $sel = ($currentPos == $pos) ? ' selected="selected"' : '';
    $posOpt .= '<option value="' . $pos . '"' . $sel . '>' . $pos . '</option>';
}

// Build disbursement status dropdown
$payStatuses = array("Pending", "Processed", "Paid");
$payOpt = "";
$currentPay = $D["disbursementStatus"] ?? "Pending";
foreach ($payStatuses as $ps) {
    $sel = ($currentPay == $ps) ? ' selected="selected"' : '';
    $payOpt .= '<option value="' . $ps . '"' . $sel . '>' . $ps . '</option>';
}

$arrFormTournament = array(
    array("type" => "select", "name" => "tournamentID", "value" => $tournamentOpt, "title" => "Tournament", "validate" => "required", "params" => array("id" => "tournamentID", "onchange" => "loadCategories(this.value)")),
    array("type" => "select", "name" => "tcID", "value" => $categoryOpt, "title" => "Category", "validate" => "required", "params" => array("id" => "tcID")),
    array("type" => "select", "name" => "position", "value" => $posOpt, "title" => "Position", "validate" => "required"),
);

$arrFormRecipient = array(
    array("type" => "text", "name" => "winnerParticipantID", "value" => $D["winnerParticipantID"] ?? "", "title" => "Participant ID"),
    array("type" => "text", "name" => "winnerName", "value" => $D["winnerName"] ?? "", "title" => "Winner Name", "validate" => "required"),
    array("type" => "text", "name" => "prizeDescription", "value" => $D["prizeDescription"] ?? "", "title" => "Prize Description"),
);

$arrFormAmount = array(
    array("type" => "text", "name" => "prizeAmount", "value" => $D["prizeAmount"] ?? "0", "title" => "Prize Amount (Rs.)", "validate" => "required,number", "params" => array("id" => "prizeAmount", "onchange" => "calculateTDS()")),
);

$arrFormPayment = array(
    array("type" => "select", "name" => "disbursementStatus", "value" => $payOpt, "title" => "Disbursement Status"),
    array("type" => "date", "name" => "disbursementDate", "value" => $D["disbursementDate"] ?? "", "title" => "Disbursement Date"),
    array("type" => "text", "name" => "disbursementReference", "value" => $D["disbursementReference"] ?? "", "title" => "Payment Reference"),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Tournament & Position</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormTournament); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Prize Amount</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormAmount); ?>
                <li>
                    <div class="frm-lbl">TDS (31.24%)</div>
                    <div class="frm-fld">
                        <span id="tdsDisplay">Rs. <?php echo number_format($D["tdsDeducted"] ?? 0, 2); ?></span>
                        <small class="text-muted">(auto-calculated for prizes > Rs. 10,000)</small>
                    </div>
                </li>
                <li>
                    <div class="frm-lbl">Net Amount</div>
                    <div class="frm-fld">
                        <span id="netDisplay" style="font-weight:bold; color:#28a745;">Rs. <?php echo number_format($D["netAmount"] ?? 0, 2); ?></span>
                    </div>
                </li>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Winner Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormRecipient); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Payment Information</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormPayment); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
function loadCategories(tournamentID) {
    if (!tournamentID) {
        document.getElementById('tcID').innerHTML = '<option value="">Select Category</option>';
        return;
    }

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/ipt-participant/x-ipt-participant.inc.php',
        type: 'POST',
        data: {
            xAction: 'GET_CATEGORIES',
            tournamentID: tournamentID,
            xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"]; ?>'
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0 && res.categories) {
                var html = '<option value="">Select Category</option>';
                res.categories.forEach(function(c) {
                    html += '<option value="' + c.tcID + '">' + c.categoryName + ' (' + c.categoryType + ')</option>';
                });
                document.getElementById('tcID').innerHTML = html;
            }
        }
    });
}

function calculateTDS() {
    var prizeAmount = parseFloat(document.getElementById('prizeAmount').value) || 0;
    var tds = 0;
    var net = prizeAmount;

    if (prizeAmount > 10000) {
        tds = prizeAmount * 0.3124;
        net = prizeAmount - tds;
    }

    document.getElementById('tdsDisplay').innerHTML = 'Rs. ' + tds.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    document.getElementById('netDisplay').innerHTML = 'Rs. ' + net.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/ipt-prize/x-ipt-prize.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/ipt-prize/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>

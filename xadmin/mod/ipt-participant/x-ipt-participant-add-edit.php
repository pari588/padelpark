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
$DB->sql = "SELECT tournamentID, tournamentCode, tournamentName FROM " . $DB->pre . "ipt_tournament WHERE status=1 AND tournamentStatus IN ('Open','Registration-Closed','In-Progress') ORDER BY startDate DESC";
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
    $DB->sql = "SELECT c.categoryID, c.categoryName, c.categoryType
                FROM " . $DB->pre . "ipt_tournament_category tc
                JOIN " . $DB->pre . "ipt_category c ON tc.categoryID=c.categoryID
                WHERE tc.tournamentID=? AND tc.status=1
                ORDER BY c.categoryName";
    $DB->dbRows();
    $categories = $DB->rows ?: array();
    $selCategory = $D["categoryID"] ?? "";
    foreach ($categories as $c) {
        $sel = ($selCategory == $c["categoryID"]) ? ' selected="selected"' : '';
        $categoryOpt .= '<option value="' . $c["categoryID"] . '"' . $sel . '>' . htmlspecialchars($c["categoryName"] . " (" . $c["categoryType"] . ")") . '</option>';
    }
}

// Build participant status dropdown
$statuses = array("Registered", "Confirmed", "Checked-In", "Active", "Eliminated", "Withdrawn");
$statusOpt = "";
$currentStatus = $D["participantStatus"] ?? "Registered";
foreach ($statuses as $st) {
    $sel = ($currentStatus == $st) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $st . '"' . $sel . '>' . $st . '</option>';
}

// Build payment status dropdown
$payStatuses = array("Pending", "Paid", "Refunded", "Waived");
$payOpt = "";
$currentPay = $D["paymentStatus"] ?? "Pending";
foreach ($payStatuses as $ps) {
    $sel = ($currentPay == $ps) ? ' selected="selected"' : '';
    $payOpt .= '<option value="' . $ps . '"' . $sel . '>' . $ps . '</option>';
}

// Build payment method dropdown
$payMethods = array("Cash", "UPI", "Card", "Bank Transfer", "Online");
$payMethodOpt = '<option value="">Select Method</option>';
$currentMethod = $D["paymentMethod"] ?? "";
foreach ($payMethods as $pm) {
    $sel = ($currentMethod == $pm) ? ' selected="selected"' : '';
    $payMethodOpt .= '<option value="' . $pm . '"' . $sel . '>' . $pm . '</option>';
}

$arrFormTournament = array(
    array("type" => "select", "name" => "tournamentID", "value" => $tournamentOpt, "title" => "Tournament", "validate" => "required"),
    array("type" => "select", "name" => "categoryID", "value" => $categoryOpt, "title" => "Category", "validate" => "required"),
    array("type" => "text", "name" => "registrationNo", "value" => $D["registrationNo"] ?? "", "title" => "Registration No", "info" => '<span class="info">Leave blank for auto-generation</span>'),
);

$arrFormPlayer1 = array(
    array("type" => "text", "name" => "player1Name", "value" => $D["player1Name"] ?? "", "title" => "Player 1 Name", "validate" => "required"),
    array("type" => "text", "name" => "player1Phone", "value" => $D["player1Phone"] ?? "", "title" => "Phone"),
    array("type" => "text", "name" => "player1Email", "value" => $D["player1Email"] ?? "", "title" => "Email"),
    array("type" => "text", "name" => "player1IPAID", "value" => $D["player1IPAID"] ?? "", "title" => "IPA ID"),
    array("type" => "text", "name" => "player1Ranking", "value" => $D["player1Ranking"] ?? "", "title" => "Ranking"),
);

$arrFormPlayer2 = array(
    array("type" => "text", "name" => "player2Name", "value" => $D["player2Name"] ?? "", "title" => "Player 2 Name", "info" => '<span class="info">For doubles/mixed only</span>'),
    array("type" => "text", "name" => "player2Phone", "value" => $D["player2Phone"] ?? "", "title" => "Phone"),
    array("type" => "text", "name" => "player2Email", "value" => $D["player2Email"] ?? "", "title" => "Email"),
    array("type" => "text", "name" => "player2IPAID", "value" => $D["player2IPAID"] ?? "", "title" => "IPA ID"),
    array("type" => "text", "name" => "player2Ranking", "value" => $D["player2Ranking"] ?? "", "title" => "Ranking"),
);

$arrFormTeam = array(
    array("type" => "text", "name" => "teamName", "value" => $D["teamName"] ?? "", "title" => "Team/Display Name", "info" => '<span class="info">Auto-generated if blank</span>'),
    array("type" => "text", "name" => "seedNumber", "value" => $D["seedNumber"] ?? "0", "title" => "Seed Number", "info" => '<span class="info">0 = Unseeded</span>'),
    array("type" => "select", "name" => "participantStatus", "value" => $statusOpt, "title" => "Status"),
);

$arrFormPayment = array(
    array("type" => "select", "name" => "paymentStatus", "value" => $payOpt, "title" => "Payment Status"),
    array("type" => "text", "name" => "entryFeePaid", "value" => $D["entryFeePaid"] ?? "0", "title" => "Amount Paid (Rs.)", "validate" => "number"),
    array("type" => "select", "name" => "paymentMethod", "value" => $payMethodOpt, "title" => "Payment Method"),
    array("type" => "text", "name" => "paymentReference", "value" => $D["paymentReference"] ?? "", "title" => "Payment Reference"),
    array("type" => "date", "name" => "paymentDate", "value" => $D["paymentDate"] ?? "", "title" => "Payment Date"),
);

$arrFormNotes = array(
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 3)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Tournament & Category</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormTournament); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Team/Seeding</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormTeam); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Player 1 Details</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormPlayer1); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Player 2 Details (Doubles/Mixed)</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormPlayer2); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Payment Information</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormPayment); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Additional Notes</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrFormNotes); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
$(function() {
    $('select[name="tournamentID"]').on('change', function() {
        var tid = $(this).val();
        var $cat = $('select[name="categoryID"]');
        if (!tid) {
            $cat.html('<option value="">Select Category</option>');
            return;
        }
        $cat.html('<option value="">Loading...</option>');
        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/ipt-participant/x-ipt-participant.inc.php',
            type: 'POST',
            data: {xAction: 'GET_CATEGORIES', tournamentID: tid},
            dataType: 'json',
            success: function(r) {
                if (r.err == 0 && r.categories && r.categories.length > 0) {
                    var h = '<option value="">Select Category</option>';
                    for (var i = 0; i < r.categories.length; i++) {
                        var c = r.categories[i];
                        h += '<option value="' + c.categoryID + '">' + c.categoryName + (c.categoryType ? ' (' + c.categoryType + ')' : '') + '</option>';
                    }
                    $cat.html(h);
                } else {
                    $cat.html('<option value="">No categories found</option>');
                }
            },
            error: function() {
                $cat.html('<option value="">Error loading categories</option>');
            }
        });
    });
});
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/ipt-participant/x-ipt-participant.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/ipt-participant/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>

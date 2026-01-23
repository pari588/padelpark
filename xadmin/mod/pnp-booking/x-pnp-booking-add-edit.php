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

// Get locations for dropdown
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$locationOpt = getTableDD([
    "table" => $DB->pre . "pnp_location",
    "key" => "locationID",
    "val" => "locationName",
    "selected" => ($D['locationID'] ?? 0),
    "where" => $whrArr
]);

// Get courts (will be filtered by JS)
$DB->sql = "SELECT courtID, courtName, locationID FROM " . $DB->pre . "pnp_court WHERE status=1 AND maintenanceStatus='Active' ORDER BY locationID, courtName";
$courts = $DB->dbRows();
$courtOpt = "";
$selectedCourt = $D["courtID"] ?? 0;
foreach ($courts as $c) {
    $sel = ($c["courtID"] == $selectedCourt) ? ' selected="selected"' : '';
    $courtOpt .= '<option value="' . $c["courtID"] . '" data-location="' . $c["locationID"] . '"' . $sel . '>' . $c["courtName"] . '</option>';
}

// Build booking source options
$bookingSources = array("Hudle", "Walk-in", "Phone", "Website", "App");
$sourceOpt = "";
$currentSource = $D["bookingSource"] ?? "Walk-in";
foreach ($bookingSources as $src) {
    $sel = ($currentSource == $src) ? ' selected="selected"' : '';
    $sourceOpt .= '<option value="' . $src . '"' . $sel . '>' . $src . '</option>';
}

// Build customer type options
$customerTypes = array("Guest", "Member", "Corporate");
$custTypeOpt = "";
$currentCustType = $D["customerType"] ?? "Guest";
foreach ($customerTypes as $ct) {
    $sel = ($currentCustType == $ct) ? ' selected="selected"' : '';
    $custTypeOpt .= '<option value="' . $ct . '"' . $sel . '>' . $ct . '</option>';
}

// Build payment status options
$paymentStatuses = array("Pending", "Paid", "Partial", "Refunded");
$payStatusOpt = "";
$currentPayStatus = $D["paymentStatus"] ?? "Pending";
foreach ($paymentStatuses as $ps) {
    $sel = ($currentPayStatus == $ps) ? ' selected="selected"' : '';
    $payStatusOpt .= '<option value="' . $ps . '"' . $sel . '>' . $ps . '</option>';
}

// Build payment method options
$paymentMethods = array("Cash", "Card", "UPI", "Hudle", "Wallet");
$payMethodOpt = "";
$currentPayMethod = $D["paymentMethod"] ?? "Cash";
foreach ($paymentMethods as $pm) {
    $sel = ($currentPayMethod == $pm) ? ' selected="selected"' : '';
    $payMethodOpt .= '<option value="' . $pm . '"' . $sel . '>' . $pm . '</option>';
}

// Build booking status options
$bookingStatuses = array("Confirmed", "Checked-In", "In-Progress", "Completed", "No-Show", "Cancelled");
$bookStatusOpt = "";
$currentBookStatus = $D["bookingStatus"] ?? "Confirmed";
foreach ($bookingStatuses as $bs) {
    $sel = ($currentBookStatus == $bs) ? ' selected="selected"' : '';
    $bookStatusOpt .= '<option value="' . $bs . '"' . $sel . '>' . $bs . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "bookingNo", "value" => $D["bookingNo"] ?? "", "title" => "Booking No", "info" => '<span class="info">Leave blank for auto-generation</span>'),
    array("type" => "select", "name" => "bookingSource", "value" => $sourceOpt, "title" => "Source"),
    array("type" => "select", "name" => "locationID", "value" => $locationOpt, "title" => "Location", "validate" => "required"),
    array("type" => "select", "name" => "courtID", "value" => $courtOpt, "title" => "Court", "validate" => "required"),
    array("type" => "text", "name" => "customerName", "value" => $D["customerName"] ?? "", "title" => "Customer Name", "validate" => "required"),
    array("type" => "text", "name" => "customerPhone", "value" => $D["customerPhone"] ?? "", "title" => "Phone", "validate" => "required"),
    array("type" => "text", "name" => "customerEmail", "value" => $D["customerEmail"] ?? "", "title" => "Email"),
    array("type" => "select", "name" => "customerType", "value" => $custTypeOpt, "title" => "Customer Type"),
);

$arrForm1 = array(
    array("type" => "date", "name" => "bookingDate", "value" => $D["bookingDate"] ?? date("Y-m-d"), "title" => "Booking Date", "validate" => "required"),
    array("type" => "text", "name" => "startTime", "value" => $D["startTime"] ?? "", "title" => "Start Time", "validate" => "required", "info" => '<span class="info">Format: HH:MM (24hr)</span>'),
    array("type" => "text", "name" => "endTime", "value" => $D["endTime"] ?? "", "title" => "End Time", "validate" => "required"),
    array("type" => "text", "name" => "numberOfPlayers", "value" => $D["numberOfPlayers"] ?? "2", "title" => "Number of Players", "validate" => "number"),
    array("type" => "text", "name" => "baseAmount", "value" => $D["baseAmount"] ?? "0", "title" => "Base Amount (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "discountAmount", "value" => $D["discountAmount"] ?? "0", "title" => "Discount (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "taxAmount", "value" => $D["taxAmount"] ?? "0", "title" => "Tax (Rs.)", "validate" => "number"),
    array("type" => "text", "name" => "totalAmount", "value" => $D["totalAmount"] ?? "0", "title" => "Total Amount (Rs.)", "validate" => "required,number"),
);

$arrForm2 = array(
    array("type" => "select", "name" => "paymentStatus", "value" => $payStatusOpt, "title" => "Payment Status"),
    array("type" => "select", "name" => "paymentMethod", "value" => $payMethodOpt, "title" => "Payment Method"),
    array("type" => "text", "name" => "paymentReference", "value" => $D["paymentReference"] ?? "", "title" => "Payment Reference"),
    array("type" => "select", "name" => "bookingStatus", "value" => $bookStatusOpt, "title" => "Booking Status"),
    array("type" => "text", "name" => "hudelBookingID", "value" => $D["hudelBookingID"] ?? "", "title" => "Hudle Booking ID"),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 2)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Booking & Customer Info</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Schedule & Pricing</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
        </div>
        <div class="wrap-form f100">
            <h2 class="form-head">Payment & Status</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm2); ?>
            </ul>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
var courtsData = <?php echo json_encode($courts); ?>;

document.querySelector('[name="locationID"]').addEventListener('change', function() {
    var locationID = this.value;
    var courtSelect = document.querySelector('[name="courtID"]');
    courtSelect.innerHTML = '<option value="">-- Select Court --</option>';

    courtsData.forEach(function(court) {
        if (court.locationID == locationID) {
            var opt = document.createElement('option');
            opt.value = court.courtID;
            opt.textContent = court.courtName;
            courtSelect.appendChild(opt);
        }
    });
});

// Auto-calculate total
function calculateTotal() {
    var base = parseFloat(document.querySelector('[name="baseAmount"]').value) || 0;
    var discount = parseFloat(document.querySelector('[name="discountAmount"]').value) || 0;
    var tax = parseFloat(document.querySelector('[name="taxAmount"]').value) || 0;
    document.querySelector('[name="totalAmount"]').value = (base - discount + tax).toFixed(2);
}

document.querySelector('[name="baseAmount"]').addEventListener('input', calculateTotal);
document.querySelector('[name="discountAmount"]').addEventListener('input', calculateTotal);
document.querySelector('[name="taxAmount"]').addEventListener('input', calculateTotal);
</script>

<script>
// Define required JavaScript variables for form submission
var MODINCURL = '<?php echo ADMINURL; ?>/mod/pnp-booking/x-pnp-booking.inc.php';
var MODURL = '<?php echo ADMINURL; ?>/mod/pnp-booking/';
var ADMINURL = '<?php echo ADMINURL; ?>';
var PAGETYPE = '<?php echo $TPL->pageType ?? "add"; ?>';
</script>

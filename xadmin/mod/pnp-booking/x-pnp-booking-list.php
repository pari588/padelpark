<?php
// Auto-complete bookings when time has passed (runs on every list view)
require_once(__DIR__ . "/x-pnp-booking.inc.php");
autoCompleteBookings();

// Get locations for filter dropdown
$DB->sql = "SELECT locationID, locationName FROM " . $DB->pre . "pnp_location WHERE status=1 ORDER BY locationName";
$locations = $DB->dbRows();
$locationOpt = '<option value="">All Locations</option>';
$selLoc = $_GET["locationID"] ?? "";
foreach ($locations as $loc) {
    $sel = ($selLoc == $loc["locationID"]) ? ' selected="selected"' : '';
    $locationOpt .= '<option value="' . $loc["locationID"] . '"' . $sel . '>' . htmlspecialchars($loc["locationName"]) . '</option>';
}

// Build status dropdown
$statusArr = array("" => "All", "Confirmed" => "Confirmed", "Checked-In" => "Checked-In", "In-Progress" => "In-Progress", "Completed" => "Completed", "No-Show" => "No-Show", "Cancelled" => "Cancelled");
$statusOpt = '';
$selStatus = $_GET["bookingStatus"] ?? "";
foreach ($statusArr as $k => $v) {
    $sel = ($selStatus == $k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Build source dropdown
$sourceArr = array("" => "All", "Hudle" => "Hudle", "Walk-in" => "Walk-in", "Phone" => "Phone", "Website" => "Website", "App" => "App");
$sourceOpt = '';
$selSource = $_GET["bookingSource"] ?? "";
foreach ($sourceArr as $k => $v) {
    $sel = ($selSource == $k) ? ' selected="selected"' : '';
    $sourceOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "bookingID", "title" => "#ID", "where" => "AND b.bookingID=?", "dtype" => "i"),
    array("type" => "text", "name" => "bookingNo", "title" => "Booking No", "where" => "AND b.bookingNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "locationID", "title" => "Location", "where" => "AND b.locationID=?", "dtype" => "i", "value" => $locationOpt, "default" => false),
    array("type" => "text", "name" => "customerName", "title" => "Customer", "where" => "AND b.customerName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "date", "name" => "bookingDate", "title" => "Date", "where" => "AND b.bookingDate=?", "dtype" => "s"),
    array("type" => "select", "name" => "bookingStatus", "title" => "Status", "where" => "AND b.bookingStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false),
    array("type" => "select", "name" => "bookingSource", "title" => "Source", "where" => "AND b.bookingSource=?", "dtype" => "s", "value" => $sourceOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT b.bookingID FROM `" . $DB->pre . "pnp_booking` b WHERE b.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <!-- Pull from Hudle Button -->
        <div style="margin-bottom: 15px; padding: 10px 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 4px; color: #fff;">
            <i class="fa fa-sync"></i>
            <strong style="margin-left: 5px;">Hudle Integration</strong>
            <span style="margin-left: 10px; opacity: 0.9; font-size: 13px;">Sync bookings from Hudle</span>
            <button type="button" id="btnPullHudle" style="float: right; background: #fff; color: #667eea; padding: 6px 15px; border-radius: 4px; font-weight: 600; border: none; cursor: pointer; font-size: 13px;">
                <i class="fa fa-cloud-download-alt"></i> Pull from Hudle
            </button>
            <div style="clear: both;"></div>
        </div>
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "bookingID", ' width="4%" align="center"', true),
                array("Booking No", "bookingNo", ' width="10%" align="left"'),
                array("Location", "locationName", ' width="12%" align="left"'),
                array("Court", "courtName", ' width="8%" align="center"'),
                array("Customer", "customerName", ' width="14%" align="left"'),
                array("Date", "bookingDate", ' width="9%" align="center"'),
                array("Time", "timeSlot", ' width="10%" align="center"'),
                array("Amount", "totalAmount", ' width="8%" align="right"'),
                array("Source", "bookingSource", ' width="7%" align="center"'),
                array("Status", "bookingStatus", ' width="9%" align="center"'),
                array("Actions", "actions", ' width="9%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT b.*, l.locationName, c.courtName
                        FROM `" . $DB->pre . "pnp_booking` b
                        LEFT JOIN `" . $DB->pre . "pnp_location` l ON b.locationID=l.locationID
                        LEFT JOIN `" . $DB->pre . "pnp_court` c ON b.courtID=c.courtID
                        WHERE b.status=? " . $MXFRM->where . mxOrderBy("b.bookingDate DESC, b.startTime DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format date
                        $d["bookingDate"] = date("d-M-Y", strtotime($d["bookingDate"]));

                        // Format time slot
                        $d["timeSlot"] = date("h:i A", strtotime($d["startTime"])) . " - " . date("h:i A", strtotime($d["endTime"]));

                        // Format amount
                        $d["totalAmount"] = "Rs. " . number_format($d["totalAmount"], 0);

                        // Source badge
                        $sourceColors = array("Hudle" => "badge-primary", "Walk-in" => "badge-success", "Phone" => "badge-info", "Website" => "badge-warning");
                        $d["bookingSource"] = '<span class="badge ' . ($sourceColors[$d["bookingSource"]] ?? "badge-secondary") . '">' . $d["bookingSource"] . '</span>';

                        // Status badge
                        $statusColors = array(
                            "Confirmed" => "badge-info",
                            "Checked-In" => "badge-primary",
                            "In-Progress" => "badge-warning",
                            "Completed" => "badge-success",
                            "No-Show" => "badge-danger",
                            "Cancelled" => "badge-dark"
                        );
                        $originalStatus = $d["bookingStatus"];
                        $d["bookingStatus"] = '<span class="badge ' . ($statusColors[$originalStatus] ?? "badge-secondary") . '">' . $originalStatus . '</span>';

                        // Action buttons
                        $d["actions"] = '';
                        if ($originalStatus == "Confirmed") {
                            $d["actions"] .= '<a href="javascript:void(0);" onclick="checkInBooking(' . $d["bookingID"] . ')" class="btn-action btn-sm" title="Check In" style="background:#28a745;color:#fff;padding:3px 8px;border-radius:3px;margin-right:3px;text-decoration:none;font-size:11px;"><i class="fa fa-sign-in-alt"></i></a>';
                        }
                        if ($originalStatus == "Checked-In" || $originalStatus == "In-Progress") {
                            $d["actions"] .= '<a href="javascript:void(0);" onclick="checkOutBooking(' . $d["bookingID"] . ')" class="btn-action btn-sm" title="Complete Session" style="background:#17a2b8;color:#fff;padding:3px 8px;border-radius:3px;margin-right:3px;text-decoration:none;font-size:11px;"><i class="fa fa-check"></i></a>';
                        }
                        // Rental button
                        $d["actions"] .= '<a href="' . ADMINURL . '/pnp-rental-add/?bookingID=' . $d["bookingID"] . '" class="btn-action btn-sm" title="Add Rental" style="background:#ffc107;color:#000;padding:3px 8px;border-radius:3px;text-decoration:none;font-size:11px;"><i class="fa fa-hand-holding"></i></a>';
                    ?>
                        <tr><?php echo getMAction("mid", $d["bookingID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["bookingID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records" style="text-align:center; padding:60px 20px;">
                <i class="fa fa-calendar-times" style="font-size:48px;color:#ddd;margin-bottom:15px;display:block;"></i>
                <p style="margin:0; color:#888; font-size:15px;">No bookings found. Bookings will appear here once synced from Hudle.</p>
            </div>
        <?php } ?>
    </div>
</div>

<script>
// Pull from Hudle
document.getElementById('btnPullHudle').addEventListener('click', function() {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Syncing...';

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/pnp-booking/x-pnp-booking.inc.php',
        type: 'POST',
        data: {
            xAction: 'PULL_HUDLE',
            modName: 'pnp-booking',
            xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"]; ?>'
        },
        dataType: 'json',
        success: function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-cloud-download-alt"></i> Pull from Hudle';
            if (res.err == 0) {
                alert(res.msg || 'Sync completed!');
                if (res.data && res.data.status !== 'pending') {
                    location.reload();
                }
            } else {
                alert('Error: ' + (res.msg || 'Unknown error'));
            }
        },
        error: function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-cloud-download-alt"></i> Pull from Hudle';
            alert('Error connecting to server');
        }
    });
});

// Check In
function checkInBooking(bookingID) {
    if (confirm('Check in this booking?')) {
        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/pnp-booking/x-pnp-booking.inc.php',
            type: 'POST',
            data: {
                xAction: 'CHECK_IN',
                modName: 'pnp-booking',
                bookingID: bookingID,
                xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"]; ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.err == 0) {
                    location.reload();
                } else {
                    alert('Error: ' + (res.msg || 'Unknown error'));
                }
            }
        });
    }
}

// Check Out / Complete
function checkOutBooking(bookingID) {
    if (confirm('Mark this session as completed?')) {
        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/pnp-booking/x-pnp-booking.inc.php',
            type: 'POST',
            data: {
                xAction: 'CHECK_OUT',
                modName: 'pnp-booking',
                bookingID: bookingID,
                xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"]; ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.err == 0) {
                    location.reload();
                } else {
                    alert('Error: ' + (res.msg || 'Unknown error'));
                }
            }
        });
    }
}
</script>

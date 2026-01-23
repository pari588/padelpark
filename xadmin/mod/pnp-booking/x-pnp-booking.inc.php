<?php
/*
addBooking = To save Booking data (manual entry).
updateBooking = To update Booking data.
pullFromHudle = Placeholder for Hudle API integration.
checkInBooking = Mark booking as checked in.
checkOutBooking = Mark booking as completed.
generateBookingNo = Generate unique booking number.
*/

function autoCompleteBookings()
{
    global $DB;

    // Auto-complete bookings where end time has passed
    // Update In-Progress and Checked-In to Completed
    $DB->sql = "UPDATE " . $DB->pre . "pnp_booking
                SET bookingStatus = 'Completed',
                    checkOutTime = CONCAT(bookingDate, ' ', endTime)
                WHERE bookingStatus IN ('In-Progress', 'Checked-In')
                AND CONCAT(bookingDate, ' ', endTime) < NOW()";
    $DB->dbQuery();
    $completedCount = $DB->affectedRows ?? 0;

    // Mark Confirmed bookings as No-Show if ended 30+ minutes ago without check-in
    $DB->sql = "UPDATE " . $DB->pre . "pnp_booking
                SET bookingStatus = 'No-Show'
                WHERE bookingStatus = 'Confirmed'
                AND CONCAT(bookingDate, ' ', endTime) < DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
    $DB->dbQuery();
    $noShowCount = $DB->affectedRows ?? 0;

    return array('completed' => $completedCount, 'noShow' => $noShowCount);
}

function generateBookingNo()
{
    global $DB;
    $prefix = "BK-" . date("Ymd") . "-";
    $DB->sql = "SELECT bookingNo FROM " . $DB->pre . "pnp_booking
                WHERE bookingNo LIKE '" . $prefix . "%'
                ORDER BY bookingNo DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['bookingNo'], -4));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 4, "0", STR_PAD_LEFT);
}

function addBooking()
{
    global $DB;

    // Generate booking number
    if (empty($_POST["bookingNo"])) {
        $_POST["bookingNo"] = generateBookingNo();
    }

    // Calculate duration
    if (!empty($_POST["startTime"]) && !empty($_POST["endTime"])) {
        $start = strtotime($_POST["startTime"]);
        $end = strtotime($_POST["endTime"]);
        $_POST["duration"] = ($end - $start) / 60;
    }

    $_POST["createdBy"] = $_SESSION[SITEURL]["userID"] ?? 0;

    $DB->table = $DB->pre . "pnp_booking";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateBooking()
{
    global $DB;
    $bookingID = intval($_POST["bookingID"]);

    // Calculate duration
    if (!empty($_POST["startTime"]) && !empty($_POST["endTime"])) {
        $start = strtotime($_POST["startTime"]);
        $end = strtotime($_POST["endTime"]);
        $_POST["duration"] = ($end - $start) / 60;
    }

    $DB->table = $DB->pre . "pnp_booking";
    $DB->data = $_POST;
    if ($DB->dbUpdate("bookingID=?", "i", array($bookingID))) {
        setResponse(array("err" => 0, "param" => "id=" . $bookingID));
    } else {
        setResponse(array("err" => 1));
    }
}

function checkInBooking()
{
    global $DB;
    $bookingID = intval($_POST["bookingID"]);

    $DB->vals = array("Checked-In", date("Y-m-d H:i:s"), $_SESSION[SITEURL]["userID"] ?? 0, $bookingID);
    $DB->types = "ssii";
    $DB->sql = "UPDATE " . $DB->pre . "pnp_booking SET bookingStatus=?, checkInTime=?, checkedInBy=? WHERE bookingID=?";
    if ($DB->dbQuery()) {
        setResponse(array("err" => 0, "msg" => "Checked in successfully"));
    } else {
        setResponse(array("err" => 1));
    }
}

function checkOutBooking()
{
    global $DB;
    $bookingID = intval($_POST["bookingID"]);

    $DB->vals = array("Completed", date("Y-m-d H:i:s"), $bookingID);
    $DB->types = "ssi";
    $DB->sql = "UPDATE " . $DB->pre . "pnp_booking SET bookingStatus=?, checkOutTime=? WHERE bookingID=?";
    if ($DB->dbQuery()) {
        setResponse(array("err" => 0, "msg" => "Session completed"));
    } else {
        setResponse(array("err" => 1));
    }
}

function pullFromHudle()
{
    global $DB;

    /*
    HUDLE API INTEGRATION PLACEHOLDER

    When Hudle API credentials are available, this function will:

    1. PULL LOCATIONS
       - Fetch all locations from Hudle API
       - Insert/Update mx_pnp_location table
       - Map hudelLocationID to local locationID

    2. PULL COURTS
       - Fetch all courts from Hudle API
       - Insert/Update mx_pnp_court table
       - Map hudelCourtID to local courtID
       - Link to locations via hudelLocationID

    3. PULL BOOKINGS
       - Fetch bookings from Hudle API (date range filter)
       - Insert/Update mx_pnp_booking table
       - Map hudelBookingID to local bookingID
       - Link to courts and locations

    4. PULL RECEIPTS -> INVOICES
       - Fetch payment receipts from Hudle API
       - Convert to mx_pnp_invoice records
       - Map hudelReceiptID and hudelTransactionID
       - Link to bookings via hudelBookingID

    API Endpoints (Example - adjust based on actual Hudle API docs):
    - GET /api/v1/locations
    - GET /api/v1/courts
    - GET /api/v1/bookings?from_date=YYYY-MM-DD&to_date=YYYY-MM-DD
    - GET /api/v1/transactions

    Required Configuration (to be stored in settings):
    - hudle_api_key
    - hudle_api_secret
    - hudle_base_url
    - hudle_location_ids (comma-separated list of Hudle location IDs to sync)
    */

    // For now, return placeholder message
    setResponse(array(
        "err" => 0,
        "msg" => "Hudle API integration pending. Configure API credentials in Settings to enable sync.",
        "data" => array(
            "status" => "pending",
            "message" => "Hudle API will sync: Locations, Courts, Bookings, and Receipts (Invoices)",
            "required_settings" => array(
                "hudle_api_key" => "Your Hudle API Key",
                "hudle_api_secret" => "Your Hudle API Secret",
                "hudle_base_url" => "https://api.hudle.in/v1",
                "hudle_location_ids" => "Comma-separated Hudle location IDs"
            )
        )
    ));
}

function syncHudleLocations($apiData)
{
    global $DB;
    $synced = 0;

    // Example sync logic - adjust based on actual API response structure
    foreach ($apiData as $loc) {
        $DB->vals = array($loc["id"]);
        $DB->types = "s";
        $DB->sql = "SELECT locationID FROM " . $DB->pre . "pnp_location WHERE hudelLocationID=?";
        $existing = $DB->dbRow();

        $data = array(
            "locationName" => $loc["name"] ?? "",
            "address" => $loc["address"] ?? "",
            "city" => $loc["city"] ?? "",
            "hudelLocationID" => $loc["id"],
            "contactPhone" => $loc["phone"] ?? "",
            "contactEmail" => $loc["email"] ?? ""
        );

        if ($existing) {
            $DB->table = $DB->pre . "pnp_location";
            $DB->data = $data;
            $DB->dbUpdate("locationID=?", "i", array($existing["locationID"]));
        } else {
            $data["locationCode"] = "HUD-" . substr($loc["id"], 0, 6);
            $DB->table = $DB->pre . "pnp_location";
            $DB->data = $data;
            $DB->dbInsert();
        }
        $synced++;
    }
    return $synced;
}

function syncHudleCourts($apiData)
{
    global $DB;
    $synced = 0;

    foreach ($apiData as $court) {
        // Find local location by Hudle ID
        $DB->vals = array($court["location_id"]);
        $DB->types = "s";
        $DB->sql = "SELECT locationID FROM " . $DB->pre . "pnp_location WHERE hudelLocationID=?";
        $location = $DB->dbRow();
        if (!$location) continue;

        $DB->vals = array($court["id"]);
        $DB->types = "s";
        $DB->sql = "SELECT courtID FROM " . $DB->pre . "pnp_court WHERE hudelCourtID=?";
        $existing = $DB->dbRow();

        $data = array(
            "locationID" => $location["locationID"],
            "courtName" => $court["name"] ?? "",
            "courtType" => $court["type"] ?? "Indoor",
            "hourlyRate" => $court["price"] ?? 0,
            "hudelCourtID" => $court["id"]
        );

        if ($existing) {
            $DB->table = $DB->pre . "pnp_court";
            $DB->data = $data;
            $DB->dbUpdate("courtID=?", "i", array($existing["courtID"]));
        } else {
            $data["courtCode"] = "C" . str_pad(rand(1, 99), 2, "0", STR_PAD_LEFT);
            $DB->table = $DB->pre . "pnp_court";
            $DB->data = $data;
            $DB->dbInsert();
        }
        $synced++;
    }
    return $synced;
}

function syncHudleBookings($apiData)
{
    global $DB;
    $synced = 0;

    foreach ($apiData as $booking) {
        // Find local location and court by Hudle IDs
        $DB->vals = array($booking["location_id"]);
        $DB->types = "s";
        $DB->sql = "SELECT locationID FROM " . $DB->pre . "pnp_location WHERE hudelLocationID=?";
        $location = $DB->dbRow();

        $DB->vals = array($booking["court_id"]);
        $DB->types = "s";
        $DB->sql = "SELECT courtID FROM " . $DB->pre . "pnp_court WHERE hudelCourtID=?";
        $court = $DB->dbRow();

        if (!$location || !$court) continue;

        // Check if booking exists
        $DB->vals = array($booking["id"]);
        $DB->types = "s";
        $DB->sql = "SELECT bookingID FROM " . $DB->pre . "pnp_booking WHERE hudelBookingID=?";
        $existing = $DB->dbRow();

        $data = array(
            "locationID" => $location["locationID"],
            "courtID" => $court["courtID"],
            "hudelBookingID" => $booking["id"],
            "hudelTransactionID" => $booking["transaction_id"] ?? "",
            "bookingSource" => "Hudle",
            "customerName" => $booking["customer_name"] ?? "",
            "customerPhone" => $booking["customer_phone"] ?? "",
            "customerEmail" => $booking["customer_email"] ?? "",
            "bookingDate" => $booking["date"] ?? date("Y-m-d"),
            "startTime" => $booking["start_time"] ?? "00:00:00",
            "endTime" => $booking["end_time"] ?? "00:00:00",
            "totalAmount" => $booking["amount"] ?? 0,
            "paymentStatus" => ($booking["payment_status"] == "completed") ? "Paid" : "Pending",
            "paymentMethod" => "Hudle",
            "bookingStatus" => mapHudleBookingStatus($booking["status"] ?? "")
        );

        if ($existing) {
            $DB->table = $DB->pre . "pnp_booking";
            $DB->data = $data;
            $DB->dbUpdate("bookingID=?", "i", array($existing["bookingID"]));
        } else {
            $data["bookingNo"] = generateBookingNo();
            $DB->table = $DB->pre . "pnp_booking";
            $DB->data = $data;
            $DB->dbInsert();
        }
        $synced++;
    }
    return $synced;
}

function syncHudleReceipts($apiData)
{
    global $DB;
    $synced = 0;

    foreach ($apiData as $receipt) {
        // Find local booking by Hudle booking ID
        $DB->vals = array($receipt["booking_id"]);
        $DB->types = "s";
        $DB->sql = "SELECT bookingID, locationID, customerName, customerPhone, customerEmail FROM " . $DB->pre . "pnp_booking WHERE hudelBookingID=?";
        $booking = $DB->dbRow();

        if (!$booking) continue;

        // Check if invoice exists
        $DB->vals = array($receipt["id"]);
        $DB->types = "s";
        $DB->sql = "SELECT invoiceID FROM " . $DB->pre . "pnp_invoice WHERE hudelReceiptID=?";
        $existing = $DB->dbRow();

        $amount = floatval($receipt["amount"] ?? 0);
        $taxable = round($amount / 1.18, 2); // Assuming 18% GST inclusive
        $cgst = round($taxable * 0.09, 2);
        $sgst = round($taxable * 0.09, 2);

        $data = array(
            "bookingID" => $booking["bookingID"],
            "locationID" => $booking["locationID"],
            "hudelReceiptID" => $receipt["id"],
            "hudelTransactionID" => $receipt["transaction_id"] ?? "",
            "customerName" => $booking["customerName"],
            "customerPhone" => $booking["customerPhone"],
            "customerEmail" => $booking["customerEmail"],
            "invoiceDate" => $receipt["date"] ?? date("Y-m-d"),
            "invoiceType" => "Booking",
            "subtotal" => $taxable,
            "taxableAmount" => $taxable,
            "cgstAmount" => $cgst,
            "sgstAmount" => $sgst,
            "totalAmount" => $amount,
            "paymentMethod" => "Hudle",
            "paymentStatus" => "Paid",
            "paidAmount" => $amount,
            "invoiceStatus" => "Generated"
        );

        if ($existing) {
            $DB->table = $DB->pre . "pnp_invoice";
            $DB->data = $data;
            $DB->dbUpdate("invoiceID=?", "i", array($existing["invoiceID"]));
        } else {
            require_once(__DIR__ . "/../pnp-invoice/x-pnp-invoice.inc.php");
            $data["invoiceNo"] = generateInvoiceNo();
            $DB->table = $DB->pre . "pnp_invoice";
            $DB->data = $data;
            $DB->dbInsert();
        }
        $synced++;
    }
    return $synced;
}

function mapHudleBookingStatus($hudleStatus)
{
    $map = array(
        "confirmed" => "Confirmed",
        "checked_in" => "Checked-In",
        "in_progress" => "In-Progress",
        "completed" => "Completed",
        "cancelled" => "Cancelled",
        "no_show" => "No-Show"
    );
    return $map[strtolower($hudleStatus)] ?? "Confirmed";
}

// Handle direct AJAX actions
$isBookingAction = isset($_POST["xAction"]) &&
                   isset($_POST["modName"]) &&
                   $_POST["modName"] === "pnp-booking";

if ($isBookingAction) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addBooking(); break;
            case "UPDATE": updateBooking(); break;
            case "CHECK_IN": checkInBooking(); break;
            case "CHECK_OUT": checkOutBooking(); break;
            case "PULL_HUDLE": pullFromHudle(); break;
        }
    }
    echo json_encode($MXRES);
} else if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addBooking(); break;
            case "UPDATE": updateBooking(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "pnp_booking", "PK" => "bookingID"));
}

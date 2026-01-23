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

// Get booking info if passed
$bookingInfo = null;
$bookingID = $D["bookingID"] ?? ($_GET["bookingID"] ?? 0);
if ($bookingID) {
    $DB->vals = array($bookingID);
    $DB->types = "i";
    $DB->sql = "SELECT b.*, l.locationName FROM " . $DB->pre . "pnp_booking b
                LEFT JOIN " . $DB->pre . "pnp_location l ON b.locationID=l.locationID
                WHERE b.bookingID=?";
    $bookingInfo = $DB->dbRow();
}

// Pre-fill from booking
$defaultLocation = $D["locationID"] ?? ($bookingInfo["locationID"] ?? "");
$defaultCustomer = $D["customerName"] ?? ($bookingInfo["customerName"] ?? "");
$defaultPhone = $D["customerPhone"] ?? ($bookingInfo["customerPhone"] ?? "");
$defaultEmail = $D["customerEmail"] ?? ($bookingInfo["customerEmail"] ?? "");

// Override location dropdown selection
if ($defaultLocation) {
    $locationOpt = getTableDD([
        "table" => $DB->pre . "pnp_location",
        "key" => "locationID",
        "val" => "locationName",
        "selected" => $defaultLocation,
        "where" => $whrArr
    ]);
}

// Get equipment for the location
$DB->sql = "SELECT e.*, l.locationName FROM " . $DB->pre . "pnp_equipment e
            LEFT JOIN " . $DB->pre . "pnp_location l ON e.locationID=l.locationID
            WHERE e.status=1 AND e.availableQuantity > 0
            ORDER BY l.locationName, e.equipmentName";
$allEquipment = $DB->dbRows();

// Get existing rental items
$rentalItems = array();
if ($D["rentalID"] ?? 0) {
    $DB->vals = array($D["rentalID"]);
    $DB->types = "i";
    $DB->sql = "SELECT ri.*, e.equipmentName, e.brand FROM " . $DB->pre . "pnp_rental_item ri
                LEFT JOIN " . $DB->pre . "pnp_equipment e ON ri.equipmentID=e.equipmentID
                WHERE ri.rentalID=?";
    $rentalItems = $DB->dbRows();
}

// Build payment method options
$payMethods = array("Cash", "Card", "UPI", "Wallet");
$payMethodOpt = "";
$currentPayMethod = $D["paymentMethod"] ?? "Cash";
foreach ($payMethods as $pm) {
    $sel = ($currentPayMethod == $pm) ? ' selected="selected"' : '';
    $payMethodOpt .= '<option value="' . $pm . '"' . $sel . '>' . $pm . '</option>';
}

// Build rental status options
$rentalStatuses = array("Reserved", "Issued", "Returned", "Returned-Damaged", "Lost");
$rentalStatusOpt = "";
$currentRentalStatus = $D["rentalStatus"] ?? "Reserved";
foreach ($rentalStatuses as $rs) {
    $sel = ($currentRentalStatus == $rs) ? ' selected="selected"' : '';
    $rentalStatusOpt .= '<option value="' . $rs . '"' . $sel . '>' . $rs . '</option>';
}

$arrForm = array(
    array("type" => "text", "name" => "rentalNo", "value" => $D["rentalNo"] ?? "", "title" => "Rental No", "info" => '<span class="info">Leave blank for auto-generation</span>'),
    array("type" => "select", "name" => "locationID", "value" => $locationOpt, "title" => "Location", "validate" => "required"),
    array("type" => "text", "name" => "customerName", "value" => $defaultCustomer, "title" => "Customer Name", "validate" => "required"),
    array("type" => "text", "name" => "customerPhone", "value" => $defaultPhone, "title" => "Phone", "validate" => "required"),
    array("type" => "text", "name" => "customerEmail", "value" => $defaultEmail, "title" => "Email"),
    array("type" => "date", "name" => "rentalDate", "value" => $D["rentalDate"] ?? date("Y-m-d"), "title" => "Rental Date"),
);

$arrForm1 = array(
    array("type" => "text", "name" => "depositAmount", "value" => $D["depositAmount"] ?? "0", "title" => "Deposit Amount (Rs.)", "validate" => "number"),
    array("type" => "select", "name" => "paymentMethod", "value" => $payMethodOpt, "title" => "Payment Method"),
    array("type" => "text", "name" => "paymentReference", "value" => $D["paymentReference"] ?? "", "title" => "Payment Reference"),
    array("type" => "select", "name" => "rentalStatus", "value" => $rentalStatusOpt, "title" => "Status"),
    array("type" => "hidden", "name" => "bookingID", "value" => $bookingID),
    array("type" => "textarea", "name" => "notes", "value" => $D["notes"] ?? "", "title" => "Notes", "params" => array("rows" => 2)),
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>

    <?php if ($bookingInfo) { ?>
    <div style="background:#e3f2fd; padding:15px 20px; border-radius:8px; margin-bottom:15px; border-left:4px solid #2196f3;">
        <strong><i class="fa fa-link"></i> Linked Booking:</strong> <?php echo htmlspecialchars($bookingInfo["bookingNo"]); ?>
        <span style="margin-left:15px;"><?php echo htmlspecialchars($bookingInfo["locationName"]); ?></span>
        <span style="margin-left:15px;"><?php echo date("d-M-Y", strtotime($bookingInfo["bookingDate"])); ?></span>
    </div>
    <?php } ?>

    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Rental & Customer Info</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm); ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Payment & Status</h2>
            <ul class="tbl-form">
                <?php echo $MXFRM->getForm($arrForm1); ?>
            </ul>
        </div>

        <!-- Equipment Items Section -->
        <div class="wrap-form f100">
            <h2 class="form-head">Equipment Items</h2>
            <div style="padding: 15px;">
                <table width="100%" border="0" cellspacing="0" cellpadding="10" class="tbl-list" id="equipmentTable">
                    <thead>
                        <tr>
                            <th width="40%">Equipment</th>
                            <th width="15%">Quantity</th>
                            <th width="20%">Rate (Rs.)</th>
                            <th width="15%">Total</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="equipmentRows">
                        <?php if (count($rentalItems) > 0) {
                            foreach ($rentalItems as $item) { ?>
                        <tr class="equipment-row">
                            <td>
                                <select name="equipmentID[]" class="equipment-select" style="width:100%; padding:8px;">
                                    <option value="">-- Select --</option>
                                    <?php foreach ($allEquipment as $eq) { ?>
                                    <option value="<?php echo $eq["equipmentID"]; ?>" data-rate="<?php echo $eq["rentalRate"]; ?>" data-location="<?php echo $eq["locationID"]; ?>" <?php echo $eq["equipmentID"] == $item["equipmentID"] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($eq["equipmentName"] . ($eq["brand"] ? " - " . $eq["brand"] : "")); ?> (Rs. <?php echo number_format($eq["rentalRate"], 0); ?>)
                                    </option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]" class="qty-input" style="width:100%; padding:8px;" value="<?php echo $item["quantity"]; ?>" min="1"></td>
                            <td><input type="number" name="rentalRateItem[]" class="rate-input" style="width:100%; padding:8px;" value="<?php echo $item["rentalRate"]; ?>" step="0.01"></td>
                            <td class="row-total">Rs. <?php echo number_format($item["totalAmount"], 0); ?></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>
                        </tr>
                        <?php }
                        } else { ?>
                        <tr class="equipment-row">
                            <td>
                                <select name="equipmentID[]" class="equipment-select" style="width:100%; padding:8px;">
                                    <option value="">-- Select Equipment --</option>
                                    <?php foreach ($allEquipment as $eq) { ?>
                                    <option value="<?php echo $eq["equipmentID"]; ?>" data-rate="<?php echo $eq["rentalRate"]; ?>" data-location="<?php echo $eq["locationID"]; ?>">
                                        <?php echo htmlspecialchars($eq["equipmentName"] . ($eq["brand"] ? " - " . $eq["brand"] : "")); ?> (Rs. <?php echo number_format($eq["rentalRate"], 0); ?>)
                                    </option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]" class="qty-input" style="width:100%; padding:8px;" value="1" min="1"></td>
                            <td><input type="number" name="rentalRateItem[]" class="rate-input" style="width:100%; padding:8px;" value="0" step="0.01"></td>
                            <td class="row-total">Rs. 0</td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" align="right"><strong>Total Rental:</strong></td>
                            <td id="grandTotal"><strong>Rs. 0</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div style="margin-top:15px;">
                    <button type="button" class="btn btn-success" onclick="addEquipmentRow()">
                        <i class="fa fa-plus"></i> Add Equipment
                    </button>
                </div>
            </div>
        </div>

        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>

<script>
var equipmentData = <?php echo json_encode($allEquipment); ?>;

function addEquipmentRow() {
    var locationID = document.querySelector('[name="locationID"]').value;
    var tbody = document.getElementById('equipmentRows');
    var row = document.createElement('tr');
    row.className = 'equipment-row';

    var options = '<option value="">-- Select Equipment --</option>';
    equipmentData.forEach(function(eq) {
        if (!locationID || eq.locationID == locationID) {
            options += '<option value="' + eq.equipmentID + '" data-rate="' + eq.rentalRate + '" data-location="' + eq.locationID + '">' +
                eq.equipmentName + (eq.brand ? ' - ' + eq.brand : '') + ' (Rs. ' + eq.rentalRate + ')</option>';
        }
    });

    row.innerHTML = '<td><select name="equipmentID[]" class="equipment-select" style="width:100%; padding:8px;">' + options + '</select></td>' +
        '<td><input type="number" name="quantity[]" class="qty-input" style="width:100%; padding:8px;" value="1" min="1"></td>' +
        '<td><input type="number" name="rentalRateItem[]" class="rate-input" style="width:100%; padding:8px;" value="0" step="0.01"></td>' +
        '<td class="row-total">Rs. 0</td>' +
        '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>';
    tbody.appendChild(row);
    bindRowEvents(row);
}

function removeRow(btn) {
    var rows = document.querySelectorAll('.equipment-row');
    if (rows.length > 1) {
        btn.closest('tr').remove();
        calculateGrandTotal();
    }
}

function bindRowEvents(row) {
    var select = row.querySelector('.equipment-select');
    var qtyInput = row.querySelector('.qty-input');
    var rateInput = row.querySelector('.rate-input');

    select.addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        var rate = opt.getAttribute('data-rate') || 0;
        rateInput.value = rate;
        calculateRowTotal(row);
    });

    qtyInput.addEventListener('input', function() { calculateRowTotal(row); });
    rateInput.addEventListener('input', function() { calculateRowTotal(row); });
}

function calculateRowTotal(row) {
    var qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    var rate = parseFloat(row.querySelector('.rate-input').value) || 0;
    var total = qty * rate;
    row.querySelector('.row-total').textContent = 'Rs. ' + total.toLocaleString();
    calculateGrandTotal();
}

function calculateGrandTotal() {
    var total = 0;
    document.querySelectorAll('.equipment-row').forEach(function(row) {
        var qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        var rate = parseFloat(row.querySelector('.rate-input').value) || 0;
        total += qty * rate;
    });
    document.getElementById('grandTotal').innerHTML = '<strong>Rs. ' + total.toLocaleString() + '</strong>';
}

// Bind events to existing rows
document.querySelectorAll('.equipment-row').forEach(bindRowEvents);
calculateGrandTotal();
</script>

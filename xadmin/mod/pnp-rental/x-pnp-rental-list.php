<?php
// Get locations for filter dropdown
$DB->sql = "SELECT locationID, locationName FROM " . $DB->pre . "pnp_location WHERE status=1 ORDER BY locationName";
$locations = $DB->dbRows();
$locationOpt = '<option value="">All Locations</option>';
$selLoc = $_GET["locationID"] ?? "";
foreach ($locations as $loc) {
    $sel = ($selLoc == $loc["locationID"]) ? ' selected="selected"' : '';
    $locationOpt .= '<option value="' . $loc["locationID"] . '"' . $sel . '>' . htmlspecialchars($loc["locationName"]) . '</option>';
}

// Build rental status dropdown
$statusArr = array("" => "All", "Reserved" => "Reserved", "Issued" => "Issued", "Returned" => "Returned", "Returned-Damaged" => "Returned-Damaged", "Lost" => "Lost");
$statusOpt = '';
$selStatus = $_GET["rentalStatus"] ?? "";
foreach ($statusArr as $k => $v) {
    $sel = ($selStatus == $k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$arrSearch = array(
    array("type" => "text", "name" => "rentalID", "title" => "#ID", "where" => "AND r.rentalID=?", "dtype" => "i"),
    array("type" => "text", "name" => "rentalNo", "title" => "Rental No", "where" => "AND r.rentalNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "locationID", "title" => "Location", "where" => "AND r.locationID=?", "dtype" => "i", "value" => $locationOpt, "default" => false),
    array("type" => "text", "name" => "customerName", "title" => "Customer", "where" => "AND r.customerName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "date", "name" => "rentalDate", "title" => "Date", "where" => "AND r.rentalDate=?", "dtype" => "s"),
    array("type" => "select", "name" => "rentalStatus", "title" => "Status", "where" => "AND r.rentalStatus=?", "dtype" => "s", "value" => $statusOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT r.rentalID FROM `" . $DB->pre . "pnp_rental` r WHERE r.status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if (!$MXFRM->where && $MXTOTREC < 1) $strSearch = "";
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "rentalID", ' width="4%" align="center"', true),
                array("Rental No", "rentalNo", ' width="10%" align="left"'),
                array("Location", "locationName", ' width="14%" align="left"'),
                array("Customer", "customerName", ' width="14%" align="left"'),
                array("Phone", "customerPhone", ' width="10%" align="center"'),
                array("Date", "rentalDate", ' width="9%" align="center"'),
                array("Amount", "totalAmount", ' width="9%" align="right"'),
                array("Status", "rentalStatus", ' width="10%" align="center"'),
                array("Actions", "actions", ' width="16%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT r.*, l.locationName
                        FROM `" . $DB->pre . "pnp_rental` r
                        LEFT JOIN `" . $DB->pre . "pnp_location` l ON r.locationID=l.locationID
                        WHERE r.status=? " . $MXFRM->where . mxOrderBy("r.rentalDate DESC, r.rentalID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        // Format date
                        $d["rentalDate"] = date("d-M-Y", strtotime($d["rentalDate"]));

                        // Format amount
                        $d["totalAmount"] = "Rs. " . number_format($d["totalAmount"], 0);

                        // Status badge
                        $statusColors = array(
                            "Reserved" => "badge-info",
                            "Issued" => "badge-warning",
                            "Returned" => "badge-success",
                            "Returned-Damaged" => "badge-danger",
                            "Lost" => "badge-dark"
                        );
                        $originalStatus = $d["rentalStatus"];
                        $d["rentalStatus"] = '<span class="badge ' . ($statusColors[$originalStatus] ?? "badge-secondary") . '">' . $originalStatus . '</span>';

                        // Action buttons
                        $d["actions"] = '';

                        if ($originalStatus == "Reserved") {
                            $d["actions"] .= '<a href="javascript:void(0);" onclick="issueEquipment(' . $d["rentalID"] . ')" class="btn-action" title="Issue Equipment" style="background:#28a745;color:#fff;padding:4px 10px;border-radius:4px;margin-right:4px;text-decoration:none;font-size:11px;"><i class="fa fa-hand-holding"></i> Issue</a>';
                        }

                        if ($originalStatus == "Issued") {
                            $d["actions"] .= '<a href="javascript:void(0);" onclick="showReturnModal(' . $d["rentalID"] . ')" class="btn-action" title="Return Equipment" style="background:#17a2b8;color:#fff;padding:4px 10px;border-radius:4px;margin-right:4px;text-decoration:none;font-size:11px;"><i class="fa fa-undo"></i> Return</a>';
                        }

                        if ($originalStatus == "Returned-Damaged") {
                            $d["actions"] .= '<span class="badge badge-danger" title="Damage: Rs. ' . number_format($d["damageCharge"], 0) . '"><i class="fa fa-exclamation-triangle"></i> Damaged</span>';
                        }
                    ?>
                        <tr><?php echo getMAction("mid", $d["rentalID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["rentalID"], $d[$v[1]]) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No rental transactions found.</div>
        <?php } ?>
    </div>
</div>

<!-- Return Modal -->
<div id="returnModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:30px; border-radius:10px; width:400px; max-width:90%;">
        <h3 style="margin-bottom:20px;">Return Equipment</h3>
        <input type="hidden" id="returnRentalID">
        <div style="margin-bottom:15px;">
            <label>Return Condition:</label>
            <select id="returnCondition" class="form-control" style="width:100%; padding:8px;">
                <option value="Good">Good - No Damage</option>
                <option value="Damaged">Damaged</option>
            </select>
        </div>
        <div id="damageSection" style="display:none; margin-bottom:15px;">
            <label>Damage Charge (Rs.):</label>
            <input type="number" id="damageCharge" class="form-control" style="width:100%; padding:8px;" value="0">
        </div>
        <div id="damageNotesSection" style="display:none; margin-bottom:15px;">
            <label>Damage Notes:</label>
            <textarea id="damageNotes" class="form-control" style="width:100%; padding:8px;" rows="2"></textarea>
        </div>
        <div style="text-align:right;">
            <button type="button" onclick="closeReturnModal()" class="btn btn-secondary" style="margin-right:10px;">Cancel</button>
            <button type="button" onclick="returnEquipment()" class="btn btn-primary">Confirm Return</button>
        </div>
    </div>
</div>

<script>
// Issue Equipment
function issueEquipment(rentalID) {
    if (confirm('Issue equipment to customer?')) {
        $.ajax({
            url: '<?php echo ADMINURL; ?>/mod/pnp-rental/x-pnp-rental.inc.php',
            type: 'POST',
            data: {
                xAction: 'ISSUE',
                modName: 'pnp-rental',
                rentalID: rentalID,
                xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"]; ?>'
            },
            dataType: 'json',
            success: function(res) {
                if (res.err == 0) {
                    alert(res.msg || 'Equipment issued!');
                    location.reload();
                } else {
                    alert('Error: ' + (res.msg || 'Unknown error'));
                }
            }
        });
    }
}

// Show Return Modal
function showReturnModal(rentalID) {
    document.getElementById('returnRentalID').value = rentalID;
    document.getElementById('returnCondition').value = 'Good';
    document.getElementById('damageCharge').value = '0';
    document.getElementById('damageNotes').value = '';
    document.getElementById('damageSection').style.display = 'none';
    document.getElementById('damageNotesSection').style.display = 'none';
    document.getElementById('returnModal').style.display = 'block';
}

function closeReturnModal() {
    document.getElementById('returnModal').style.display = 'none';
}

// Toggle damage fields
document.getElementById('returnCondition').addEventListener('change', function() {
    var isDamaged = this.value === 'Damaged';
    document.getElementById('damageSection').style.display = isDamaged ? 'block' : 'none';
    document.getElementById('damageNotesSection').style.display = isDamaged ? 'block' : 'none';
});

// Return Equipment
function returnEquipment() {
    var rentalID = document.getElementById('returnRentalID').value;
    var condition = document.getElementById('returnCondition').value;
    var damageCharge = document.getElementById('damageCharge').value || 0;
    var damageNotes = document.getElementById('damageNotes').value || '';

    $.ajax({
        url: '<?php echo ADMINURL; ?>/mod/pnp-rental/x-pnp-rental.inc.php',
        type: 'POST',
        data: {
            xAction: 'RETURN',
            modName: 'pnp-rental',
            rentalID: rentalID,
            returnCondition: condition,
            damageCharge: damageCharge,
            damageNotes: damageNotes,
            xToken: '<?php echo $_SESSION[SITEURL]["CSRF_TOKEN"]; ?>'
        },
        dataType: 'json',
        success: function(res) {
            closeReturnModal();
            if (res.err == 0) {
                alert(res.msg || 'Equipment returned!');
                location.reload();
            } else {
                alert('Error: ' + (res.msg || 'Unknown error'));
            }
        }
    });
}
</script>

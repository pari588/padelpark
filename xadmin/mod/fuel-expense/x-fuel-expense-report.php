<script type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/js/list.inc.js'); ?>"></script>
<?php
/**
 * Fuel Expense Report Page
 * Displays monthly summary report
 * Updated: <?php echo date("Y-m-d H:i:s"); ?>
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

// DATE DEFAULTS (Like x-report-list.php)
$fromDate = (isset($_GET['fromDate'])) ? $_GET['fromDate'] : date('Y-01-01');
$toDate = (isset($_GET['toDate'])) ? $_GET['toDate'] :  date('Y-m-t', strtotime('today'));

$dateWhere = "";
$dateVals = array();
$dateTypes = "";

if ($fromDate != "") {
    $dateTypes .= "s";
    $dateWhere .= " AND billDate >= ?";
    array_push($dateVals, $fromDate);
}

if ($toDate != "") {
    $dateTypes .= "s";
    $dateWhere .= " AND billDate <= ?";
    array_push($dateVals, $toDate);
}

// Define search fields - Exact copy of style from x-report-list.php where possible
$arrSearch = array(
    array("type" => "select", "name" => "vehicleID",
          "value" => $vehicleDD,
          "title" => "Vehicle", "where" => "AND vehicleID=?", "dtype" => "s"),
    array("type" => "select", "name" => "paymentStatus",
          "value" => $statusDD,
          "title" => "Payment Status", "where" => "AND paymentStatus=?", "dtype" => "s"),
    array("type" => "date", "name" => "fromDate", "value" => "", 
          "title" => "From Date", "validate" => "required", "where" => "AND billDate >= ?", "dtype" => "s", "params" => array("yearRange" => "-100:+0", "maxDate" => "0d")),
    array("type" => "date", "name" => "toDate", "value" => "",
          "title" => "To Date", "validate" => "required", "where" => "AND billDate <= ?", "dtype" => "s", "params" => array("yearRange" => "-100:+0", "maxDate" => "0d")),
);

// Generate search form
$strSearch = $MXFRM->getFormS($arrSearch);

// Build count query - use mxFramework values directly
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->vals  = array_merge($DB->vals, $dateVals);
$DB->types = "i" . $MXFRM->types . $dateTypes;

// Base SQL without LIMIT (for Export)
$sqlBase = "SELECT fe.fuelExpenseID, fe.billDate, fe.expenseAmount, fe.paymentStatus, fe.paidDate, fe.remarks, v.vehicleName
        FROM `" . $DB->pre . "fuel_expense` fe
        LEFT JOIN `" . $DB->pre . "vehicle` v ON fe.vehicleID = v.vehicleID
        WHERE fe.status=?" . $MXFRM->where . $dateWhere . mxOrderBy("fe.billDate DESC ");

// --- EXPORT CONFIGURATION (Custom Scope to bypass stale session) ---
$exportModName = "fuel-expense-custom"; // Unique key for this report
$arrExp = array(
    "fe.billDate" => "Date",
    "v.vehicleName" => "Vehicle",
    "fe.expenseAmount" => "Amount",
    "fe.paymentStatus" => "Status",
    "fe.paidDate" => "Paid Date",
    "fe.remarks" => "Remarks"
);
$_SESSION[SITEURL][$exportModName]["EXPCOLS"] = $arrExp;
$_SESSION[SITEURL][$exportModName]["EXPSQL"] = array(
    "sql" => $sqlBase, // EXPSQL MUST NOT have LIMIT, export.inc.php adds it
    "vals" => $DB->vals,
    "types" => $DB->types
);
// ------------------------------------------------------------------

// Execute Query WITH Limit for display
$DB->sql = $sqlBase . mxQryLimit();
$DB->dbRows();
$MXTOTREC = $DB->numRows;

// Ensure search form is always visible
if (!$MXFRM->where && !$dateWhere && $MXTOTREC < 1)
    // $strSearch = "";
    $MXFRM->where = "&fromDate='" . $fromDate . "'";

echo $strSearch;
?>

<div class="wrap-right">
    <?php 
    // Manually generate standard xadmin buttons including Standard Export
    $standardButtons = '<a href="#" class="fa-search btn search" title="Search"> Search</a>';
    if ($MXTOTREC > 0) {
        $standardButtons .= '<a href="#" class="fa-print btn print" title="Print"> Print</a>';
        // Standard Export Button
        $standardButtons .= '<a href="#" class="fa-export btn export" title="Export"> Export</a>';
    }
    echo getPageNav('', $standardButtons, array()); 
    ?>

    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("Date", "billDate", ' width="12%" align="center"'),
                array("Vehicle", "vehicleName", ' width="20%" align="left"'),
                array("Amount", "expenseAmount", ' width="15%" align="right"'),
                array("Status", "paymentStatus", ' width="10%" align="center"'),
                array("Paid Date", "paidDate", ' width="11%" align="center"'),
                array("Remarks", "remarks", ' width="22%" align="left"'),
            );
            
            // Data is already fetched in $DB->rows
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list report" id="reportTable">
                <thead>
                    <tr><?php echo getListTitle($MXCOLS, false); ?></tr>
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
                    ?>
                        <tr>
                            <td width="12%" align="center" title="Date"><?php echo date('d-M-Y', strtotime($expense["billDate"])); ?></td>
                            <td width="20%" align="left" title="Vehicle"><?php echo $expense["vehicleName"] ?? "Unknown"; ?></td>
                            <td width="15%" align="right" title="Amount">₹ <?php echo number_format($expense["expenseAmount"], 2); ?></td>
                            <td width="10%" align="center" title="Status"><?php echo $expense["paymentStatus"]; ?></td>
                            <td width="11%" align="center" title="Paid Date"><?php echo $expense["paidDate"] ? date('d-M-Y', strtotime($expense["paidDate"])) : "-"; ?></td>
                            <td width="22%" align="left" title="Remarks"><?php echo htmlspecialchars(substr($expense["remarks"] ?? "", 0, 50)); ?></td>
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
                    </tr>
                </tfoot>
            </table>

        <?php } else { ?>
            <div class="no-records">No expenses found</div>
        <?php } ?>
    </div>
</div>

<!-- Export Popup HTML -->
<div class="mxdialog export-popup" style="display: none;">
    <div class="body" style="width: 500px;">
        <a href="#" class="close del"></a>
        <h2>EXPORT DETAILS</h2>
        <form class="wrap-data" name="frmExport" id="frmExport" action="" method="post" enctype="multipart/form-data">
            <div class="content">
                <ul class="attndt-info export-popup">
                    <li class="c2">
                        <label>Start From Record<em></em></label>
                        <input type="text" class="only-numeric" name="offset" id="offset" value="0" min="0" max="<?php echo ($MXTOTREC - 1); ?>" title="1" placeholder="Enter Start Record">
                    </li>
                    <li class="c2">
                        <label>End Record (TOTAL: <?php echo $MXTOTREC; ?>)<em></em></label>
                        <input type="text" class="only-numeric" name="showrec" id="showrec" value="<?php echo $MXTOTREC; ?>" min="2" max="<?php echo $MXTOTREC; ?>" title="<?php echo $MXTOTREC; ?>" placeholder="Enter End Record">
                    </li>
                    <li class="linear"><label>Select Export Type <em>*</em></label>
                        <ul class="mx-list" xtype="radio">
                            <li><i class="rdo">XLSX <input type="radio" name="xAction" value="exportXLSX"><em></em></i></li>
                            <li><i class="rdo">CSV <input type="radio" name="xAction" value="exportCSV" checked="checked"><em></em></i></li>
                        </ul>
                    </li>
                    <li class="message">
                        <p class="e"></p>
                    </li>
                    <li class="cta">
                        <!-- Use the custom export mod name -->
                        <input type="hidden" name="modName" id="modName" value="<?php echo $exportModName; ?>" />
                        <input type="button" class="btn" id="btnExport" value="EXPORT" />
                    </li>
                </ul>
            </div>
        </form>
    </div>
</div>

<script>
$(function() { 
    initListPage(); 
});
</script>
<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-driver-management.inc.js'); ?>"></script>
<?php
// START : search array
$arrSearch = array(
    array("type" => "text", "name" => "driverManagementID",  "title" => "#ID", "where" => "AND DM.driverManagementID=?", "dtype" => "i"),
    array("type" => "text", "name" => "userName",  "title" => "Driver Name", "where" => "AND U.userName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "date", "name" => "fromDate", "title" => "From Date", "where" => "AND DATE(DM.dmDate) >=?", "dtype" => "s", "attr" => "style='width:160px;'"),
    array("type" => "date", "name" => "toDate", "title" => "To Date", "where" => "AND DATE(DM.dmDate) <=?", "dtype" => "s", "attr" => "style='width:140px;'"),
);
// END
$categoryWhr = array("sql" => "status=? ", "types" => "i", "vals" => array(1));
$params = ["table" => $DB->pre . "motor_category", "key" => "categoryMID", "val" => "categoryTitle", "where" => $categoryWhr];
$categoryArr  = getDataArray($params);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT DM." . $MXMOD["PK"] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS DM
LEFT JOIN `" . $DB->pre . "user` AS U ON U.userID=DM.userID
WHERE DM.status=? " . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1) {
    $strSearch = "";
}
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav('<a href="javascript:void(0)" class="button" id="download-report">Download Report</a>,<a href="javascript:void(0)" class="button" id="settleDriverWelfare">Settle Payment</a>'); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array('<input type="checkbox" id="settlePayAll" class="settlePayAll" title="Select All" style="float: none;"', "settlePay", ' align="center"', "", "nosort"),
                array("#ID", "driverManagementID", ' width="2%" align="center"', true),
                array("Driver Name", "userName", ' nowrap align="center"'),
                array("Date", "dmDate", ' nowrap align="center"'),
                array("From Date Time", "fromTime", ' align="center"'),
                array("To Date Time", "toTime", ' align="center"'),
                array("Other Expense", "otherExpense", ' align="left"', false, 'nosort'),
                array("Document", "supportingDoc", ' width="1%" align="center"', false, 'nosort'),
                array("Other Expense Amt", "expenseAmt", ' align="right"', false, 'nosort'),
                array("Overtime Hours", "overtimeHrs", ' align="right"', false, 'nosort'),
                array("Overtime Pay", "totalOvertimePay", ' align="right"', false, 'nosort'),
                array("DA", "dinnerAllowance", ' align="right"', false, 'nosort'),
                array("TA", "taxiAllowance", ' align="right"', false, 'nosort'),
                array("Off Day Allowance", "sunAllowance", ' align="right"', false, 'nosort'),
                array("Total Pay", "totalPay", ' align="right"', false, 'nosort'),
                array("Action", "action", ' align="center"', false, 'nosort'),
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT DM.*,U.userName FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS DM 
            LEFT JOIN `" . $DB->pre . "user` AS U ON U.userID=DM.userID
            WHERE DM.status=? " . $MXFRM->where . mxOrderBy(" DM.driverManagementID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    $expenseAmt = 0;
                    $overtimeHrs = 0;
                    $totalOvertimePay = 0;
                    $taxiAllowance = 0;
                    $sunAllowance = 0;
                    $dinnerAllowance = 0;
                    $totalPay = 0;
                    foreach ($DB->rows as $d) {
                        $disabled = '';
                        if ((isset($d["isSettled"]) && $d["isSettled"] == 1) || (isset($d["totalPay"]) && $d["totalPay"] <= 0)) {
                            $disabled = 'disabled';
                        }
                        if ($d["supportingDoc"] != "") {
                            $arrFile = explode(",", $d["supportingDoc"]);
                            $d["supportingDoc"] = getFile(array("path" => "driver-management/" . $arrFile[0], "title" => $d["supportingDoc"]));
                        }
                        $expenseAmt += $d["expenseAmt"];
                        $d["expenseAmt"] =  number_format($d["expenseAmt"], 2) ?? 0.00;
                        $overtimeHrs += $d["overtimeHrs"];
                        $d["overtimeHrs"] = $d["overtimeHrs"] . ' ' .  "hr";
                        $totalOvertimePay += $d["totalOvertimePay"];
                        $d["totalOvertimePay"] =  number_format($d["totalOvertimePay"], 2) ?? 0.00;
                        $dinnerAllowance += $d["dinnerAllowance"];
                        $d["dinnerAllowance"] =  number_format($d["dinnerAllowance"], 2) ?? 0.00;
                        $taxiAllowance += $d["taxiAllowance"];
                        $d["taxiAllowance"] =  number_format($d["taxiAllowance"], 2) ?? 0.00;
                        $sunAllowance += $d["sunAllowance"];
                        $d["sunAllowance"] =  number_format($d["sunAllowance"], 2) ?? 0.00;
                        $totalPay += $d["totalPay"];
                        $totalwelfarePay = $d["totalPay"];
                        $d["totalPay"] =  number_format($d["totalPay"], 2) ?? 0.00;
                        $d['action'] = "--";
                        if (intval($d['recordType']) == 1 && $d['isVerify'] == 0 && $d['toTime'] != "") {
                            $d['action'] = "<a href='javascript:void(0);' class='btn verify-btn' rel='" . $d['driverManagementID'] . "'>Verify</a>";
                        }

                    ?>
                        <tr> <?php echo getMAction("mid", $d["driverManagementID"]);
                                $settlePay = '';
                                ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["driverManagementID"], $d[$v[1]]);
                                    } else if ($v[1] == "settlePay") {
                                        $settlePay = $v[1];
                                    ?>
                                        <input type="checkbox" value=<?php echo $d["driverManagementID"]; ?> data-welfare-amount=<?php echo $totalwelfarePay; ?> class="settlePay" <?php echo $disabled; ?> />
                                    <?php
                                    } else {
                                        echo $d[$v[1]] ?? "";
                                    }
                                    ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <?php
                    $settlePayTh = ($settlePay == 'settlePay') ? ('<th></th>') : ('');
                    echo "<tr style='text-align:right;' class='trcolspan'>
                        " . $settlePayTh . "
                       <th class='action'>&nbsp;</th>
                        <th class='action'>&nbsp;</th>
                        <th colspan='5'>&nbsp;</th>
                        <th>Total</th>
                        <th>" . number_format($expenseAmt, 2) . "</th>
                        <th>" . $overtimeHrs . 'hr' . "</th>
                        <th>" . number_format($totalOvertimePay, 2) . "</th>
                        <th>" . number_format($dinnerAllowance, 2) . "</th>
                        <th>" . number_format($taxiAllowance, 2) . "</th>
                        <th>" . number_format($sunAllowance, 2) . "</th>
                        <th>" . number_format($totalPay, 2) . "</th>
                        <th></th>
                    </tr>";
                    ?>
                </tfoot>
            </table>

        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>
<div id="popup-wrap" class="open-move-popup mxdialog" style="display:none;">
    <div class="popup-data body" style="width: 800px;">
        <a href="javascript:void(0)" class="close del"></a>
        <h2>Driver Report</h2>
        <div class="view-result-details content qb-test thankyou">
            <div class="wrap">
                <form name="frm-memInvite" id="frm-memInvite">
                    <div class="sortBy driver-rpt">
                        <div class="input-field select-box ">
                            <?php
                            $DB->vals = array(1);
                            $DB->types = "i";
                            $DB->sql = "SELECT  MIN(YEAR(dmDate)) as fromYear FROM " . $DB->pre . "driver_management  WHERE status=?";
                            $fromYear = $DB->dbRow();
                            $currentYear = date('Y');
                            ?>
                            <select name="year" id="year" class="year">
                                <option value=''>--Select Year--</option>
                                <?php for ($year = $fromYear["fromYear"]; $year <= $currentYear; $year++) { ?>
                                    <option value='<?php echo $year ?>'><?php echo $year ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="input-field select-box ">
                            <select name="month" id="month" class="month">
                                <option value=''>--Select Month--</option>
                                <option value='1'>Janaury</option>
                                <option value='2'>February</option>
                                <option value='3'>March</option>
                                <option value='4'>April</option>
                                <option value='5'>May</option>
                                <option value='6'>June</option>
                                <option value='7'>July</option>
                                <option value='8'>August</option>
                                <option value='9'>September</option>
                                <option value='10'>October</option>
                                <option value='11'>November</option>
                                <option value='12'>December</option>
                            </select>
                        </div>
                        <div class="input-field select-box ">
                            <select name="week" class="week" id="week">
                                <option value="">-Select Week-</option>
                                <option value="1,7"> 1st Week</option>
                                <option value="8,14"> 2nd Week</option>
                                <option value="15,21"> 3rd Week</option>
                                <option value="22,28"> 4th Week</option>
                                <option value="29,31"> 5th Week</option>
                            </select>
                        </div>
                        <input type="hidden" name="xAction" class="xAction" value="driverReport" />
                        <a href="javascript:void();" class="excel-export btn driver-csv-report">Download<i class="fa file-excel"></i></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
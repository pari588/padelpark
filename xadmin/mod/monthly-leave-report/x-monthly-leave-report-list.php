<?php
// START : search array
$where = '';
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array("1"));
//$roleData = getRoleInfo('SUPER');
//$allowAllAccess = $roleData['allowAllAccess'];
//if ($allowAllAccess != 1) {
//   $where = ' AND AU.userID = "' . $_SESSION['MXID'] . '"';
//}

$arradmin = array("0" => "Admin");
$adminIDArr = getRoleIDS($arradmin);
$_SESSION[SITEURL]['MXROLE'];
if ($_SESSION[SITEURL]['MXROLE'] != 'SUPER' && !in_array($_SESSION[SITEURL]['MXROLE'], $adminIDArr)) {
    $where = ' AND AU.userID = "' . $_SESSION[SITEURL]['MXID'] . '"';
}



if (isset($_GET["financialYear"])) {
    $financialYear = $_GET["financialYear"];
} else {
    $startYear = date("Y");
    if (date("m") <= 3)
        $startYear--;
    $yearArr = getFinancialArray($startYear);
    $financialYear = array_search("Year " . $startYear, $yearArr);
    $where .= ' AND YEAR(UL.leaveDate) = "' .  $financialYear . '"';
}

$arrSearch = array(
    array("type" => "text", "name" => "userLeavesID", "title" => "#ID", "where" => "AND userLeavesID=?", "dtype" => "i"),
    array("type" => "text", "name" => "firstName",  "title" => "User Name", "where" => "AND AU.displayName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    // array("type" => "date", "name" => "fromDate", "title" => "Date From", "where" => "AND DATE(UL.leaveDate) >=?", "dtype" => "s", "attr" => "style='width:160px;'"),
    // array("type" => "date", "name" => "toDate", "title" => "Date To", "where" => "AND DATE(UL.leaveDate) >=?", "dtype" => "s", "attr" => "style='width:160px;'"),
    array("type" => "select", "name" => "financialYear", "value" => getFinancialArrayDD($financialYear), "title" => "Financial Year", "where" => " AND YEAR(UL.leaveDate) = ?", "dtype" => "i"),
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;

$DB->sql = "SELECT UL.*,AU.displayName,AU.userID as userID FROM `" . $DB->pre . "user_leaves` AS UL 
            LEFT JOIN `" . $DB->pre . "x_admin_user` AS AU ON UL.userID=AU.userID WHERE UL.status=? " . $MXFRM->where . $where;

$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";

echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav("", "", array("add", "trash")); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {

            $MXCOLS = array(
                array("#ID", "userLeavesID", ' width="1%" align="center" title="Leave Setting ID"'),
                array("Name", "displayName", ' width="5%" align="center" title="Name of Employee"'),
                array("Date", "leaveDate", ' width="5%" align="center" title="Leave Date"'),
                array("Year alloted", "yrAllowedLeaves", ' width="2%" align="center" title="Total Leave"'),
                array("Monthly leaves", "monAllowedLeaves", ' width="4%" align="center" title="Monthly Leave"'),
                // array("Monthly Paid", "leaveDays", ' width="4%" align="center" title="Date Of Added"'),
                array("Remaining Bal", "yrBalanceLeaves", ' width="4%" align="center" title="Remaining leave"'),
                // array("Monthly Unpaid", "leaveDays", ' width="4%" align="center" title="Date Of Added"'),
                // array("Tot Unpaid", "unpaidLeaves", ' width="1%" nowrap align="right" title="Total Unpaid"')


            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;

            $DB->sql = "SELECT UL.*,AU.displayName,AU.userID as userID FROM `" . $DB->pre . "user_leaves` AS UL 
            LEFT JOIN `" . $DB->pre . "x_admin_user` AS AU ON UL.userID=AU.userID WHERE UL.status=? " . $MXFRM->where . $where . mxOrderBy(" userLeavesID  DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>

            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS, false); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                        $empStatus = $d['empStatus'] ?? 0;
                        // $d['displayName'] = "<a target='_blank' href='" . SITEURL . "/admin-user-list/?userID=" . $d['userID'] . "&empStatus=" . $empStatus . "'>" . shortEmpName($d['displayName']) . "</a>";
                        $d['monthlyPaid'] = $d['absentDays'] - $d['leaveDays'];
                        $d['leaveDate'] = date("M", strtotime($d['leaveDate'])) . " " . date("Y", strtotime($d['leaveDate']));
                        if ($d['yrBalanceLeaves'] < 0) {
                            $d['yrBalanceLeaves'] = 0;
                        }
                    ?>
                        <tr> <?php echo getMAction("mid", $d["colorID"] ?? 0, false); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    $colorID = $d["colorID"] ?? 0;
                                    if (isset($v[3])) {
                                        echo getViewEditUrl("id=" . $colorID, $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]] ?? "";
                                    }
                                    ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>
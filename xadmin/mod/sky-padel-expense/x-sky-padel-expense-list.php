<?php
// START : search array
$arrSearch = array(
    array("type" => "text", "name" => "expenseID", "title" => "#ID", "where" => "AND expenseID=?", "dtype" => "i"),
    array("type" => "text", "name" => "projectID", "title" => "Project ID", "where" => "AND projectID=?", "dtype" => "i"),
    array("type" => "select", "name" => "expenseCategory", "title" => "Category",
          "value" => '<option value="">All</option><option value="Material">Material</option><option value="Labor">Labor</option><option value="Transport">Transport</option><option value="Equipment">Equipment</option><option value="Subcontractor">Subcontractor</option><option value="Other">Other</option>', "default" => false,
          "where" => "AND expenseCategory=?", "dtype" => "s"),
    array("type" => "text", "name" => "vendorName", "title" => "Vendor", "where" => "AND vendorName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "select", "name" => "paymentStatus", "title" => "Payment",
          "value" => '<option value="">All</option><option value="Pending">Pending</option><option value="Partial">Partial</option><option value="Paid">Paid</option>', "default" => false,
          "where" => "AND paymentStatus=?", "dtype" => "s")
);
// END

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT " . $MXMOD["PK"] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=?" . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";

echo $strSearch;
?>
<style>
.expense-amount { font-weight: 700; color: #dc2626; }
.expense-paid { color: #059669; }
.category-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.category-badge.material { background: #dbeafe; color: #1e40af; }
.category-badge.labor { background: #fef3c7; color: #92400e; }
.category-badge.transport { background: #d1fae5; color: #065f46; }
.category-badge.equipment { background: #e0e7ff; color: #3730a3; }
.category-badge.subcontractor { background: #fce7f3; color: #9d174d; }
.category-badge.other { background: #f3f4f6; color: #374151; }
.payment-pending { background: #fef3c7; color: #92400e; }
.payment-partial { background: #dbeafe; color: #1e40af; }
.payment-paid { background: #d1fae5; color: #065f46; }
</style>

<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "expenseID", ' width="1%" align="center"', true),
                array("Project", "projectInfo", ' width="15%" align="left"'),
                array("Date", "expenseDate", ' width="8%" align="center"'),
                array("Category", "expenseCategory", ' width="10%" align="center"'),
                array("Description", "description", ' width="18%" align="left"'),
                array("Vendor", "vendorName", ' width="12%" align="left"'),
                array("Amount", "totalAmount", ' width="10%" align="right"'),
                array("Paid", "paidAmount", ' width="8%" align="right"'),
                array("Status", "paymentStatus", ' width="8%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT e.*, p.projectNo, p.projectName
                        FROM `" . $DB->pre . $MXMOD["TBL"] . "` e
                        LEFT JOIN `" . $DB->pre . "sky_padel_project` p ON e.projectID = p.projectID
                        WHERE e.status=? " . $MXFRM->where . mxOrderBy("e.expenseDate DESC, e.expenseID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                        // Project info
                        $d["projectInfo"] = '<strong>' . htmlspecialchars($d["projectNo"]) . '</strong><br><small>' . htmlspecialchars($d["projectName"]) . '</small>';

                        // Format date
                        $d["expenseDate"] = date("d-M-Y", strtotime($d["expenseDate"]));

                        // Category badge
                        $catClass = strtolower($d["expenseCategory"]);
                        $d["expenseCategory"] = '<span class="category-badge ' . $catClass . '">' . $d["expenseCategory"] . '</span>';

                        // Amounts
                        $d["totalAmount"] = '<span class="expense-amount">₹' . number_format($d["totalAmount"], 0) . '</span>';
                        $d["paidAmount"] = '<span class="expense-paid">₹' . number_format($d["paidAmount"], 0) . '</span>';

                        // Payment status
                        $statusClass = 'payment-' . strtolower($d["paymentStatus"]);
                        $d["paymentStatus"] = '<span class="category-badge ' . $statusClass . '">' . $d["paymentStatus"] . '</span>';
                    ?>
                        <tr> <?php echo getMAction("mid", $d["expenseID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2]; ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["expenseID"], $d["expenseID"]);
                                    } else {
                                        echo $d[$v[1]] ?? "";
                                    }
                                    ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Summary -->
            <?php
            $DB->sql = "SELECT
                            COUNT(*) as totalCount,
                            SUM(totalAmount) as totalExpense,
                            SUM(paidAmount) as totalPaid,
                            SUM(totalAmount - paidAmount) as totalPending
                        FROM `" . $DB->pre . $MXMOD["TBL"] . "`
                        WHERE status = 1";
            $DB->dbRow();
            $summary = $DB->row;
            ?>
            <div style="background: #f8fafc; padding: 16px; margin-top: 16px; border-radius: 8px; display: flex; gap: 32px;">
                <div><strong>Total Expenses:</strong> ₹<?php echo number_format($summary["totalExpense"], 0); ?></div>
                <div><strong>Paid:</strong> <span style="color: #059669;">₹<?php echo number_format($summary["totalPaid"], 0); ?></span></div>
                <div><strong>Pending:</strong> <span style="color: #dc2626;">₹<?php echo number_format($summary["totalPending"], 0); ?></span></div>
            </div>

        <?php } else { ?>
            <div class="no-records">No expense records found</div>
        <?php } ?>
    </div>
</div>

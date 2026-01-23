<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . 'inc/js/x-expense-entry.inc.js'); ?>"></script>
<script type="text/javascript" src="<?php echo SITEURL . '/lib/fancy-box/source/jquery.fancybox.pack.js'; ?>"></script>
<link href="<?php echo SITEURL; ?>/lib/fancy-box/source/jquery.fancybox.css" rel="stylesheet" type="text/css">
<style>
    .fancybox-nav span {
        visibility: visible !important;
    }

    .expense-image-popup {
        z-index: 999 !important;
    }
</style>
<?php
// START : search array
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array("1"));
$categoryOptDDArr = getTableDD(["table" => $DB->pre . "expense_type", "key" => "expenseTypeID", "val" => "expenceTitle", "selected" => ($_GET["expenseTypeID"] ?? ""), "where" =>  $whrArr]);

$arrSearch = array(
    array("type" => "text", "name" => "expenseEntryID",  "title" => "#ID", "where" => "AND expenseEntryID=?", "dtype" => "i"),
    array("type" => "date", "name" => "fromDate", "value" => $_GET["expenseEntryDate"] ?? "", "title" => "From Date", "where" => " AND EE.expenseEntryDate >= ?", "dtype" => "s"),
    array("type" => "date", "name" => "toDate", "value" => $_GET["expenseEntryDate"] ?? "", "title" => "To Date", "where" => " AND EE.expenseEntryDate <= ?", "dtype" => "s"),

    array("type" => "select", "name" => "expenseTypeID", "value" => $categoryOptDDArr, "title" => "Expense Type", "where" => "AND EE.expenseTypeID = ?", "dtype" => "i", "attr" => '  style="width:180px"'),

    array("type" => "text", "name" => "particulars",  "title" => "Particulars", "where" => "AND EE.particulars LIKE CONCAT('%',?,'%')", "dtype" => "s"),
);
// END

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "`AS EE
LEFT JOIN `" . $DB->pre . "expense_type` AS ET ON EE.expenseTypeID=ET.expenseTypeID
WHERE EE.status=? " . $MXFRM->where;
$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";

echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $MXCOLS = array(
                array("#ID", "expenseEntryID", ' width="5%" align="center"', true),
                array("Expense Entry Date", "expenseEntryDate", ' width="20%"  nowrap align="left"'),
                array("expense Type", "expenceTitle", ' width="20%"  nowrap align="left"'),
                array("Particulars", "particulars", ' width="20%"  nowrap align="left"'),
                array("Amount", "amount", ' width="20%"  nowrap align="right"'),
                array("Action", "action", ' width="10%"  nowrap align="centre"', false, 'nosort'),
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "`AS EE
            LEFT JOIN `" . $DB->pre . "expense_type` AS ET ON EE.expenseTypeID=ET.expenseTypeID
            WHERE EE.status=? " . $MXFRM->where . mxOrderBy("EE.expenseEntryID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    $amountTotal = 0;
                    foreach ($DB->rows as $d) {
                        $d['amountTotal'] = $d['amount'];
                        $amountTotal += $d["amountTotal"];

                        $d['action'] = '<a class="expense-image btn" rel=' . $d["expenseEntryID"] . '>View Documents</a>';
                    ?>
                        <tr> <?php echo getMAction("mid", $d["expenseEntryID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["expenseEntryID"], $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]] ?? "";
                                    }
                                    ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <?php
                    echo "<tr style='text-align:right;' class='trcolspan'>
                        <th class='action' colspan='5'>&nbsp;Total</th>
                        <th>" . number_format($amountTotal, 2) . "</th>
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

<!-- For added for multiple images || 23 Feb 2024 || Pramod Badgujar -->
<div class="mxdialog expense-image-popup" style="display: none;">
    <div class="body" style="width: 500px;">
        <a href="#" class="close del"></a>
        <h2>Documents</h2>
        <div class="content">
            <div class="expense-details">

            </div>
        </div>
    </div>
</div>
<!-- End case-popup -->
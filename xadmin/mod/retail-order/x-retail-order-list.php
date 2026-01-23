<script type="text/javascript" src="<?php echo ADMINURL; ?>/mod/sales/inc/js/x-sales.inc.js"></script>
<?php
$customerOpt = getCustomerDD(($_GET["customerID"] ?? 0));
// $currencyOpt = getCurrencyDD(($_GET["currencyID"] ?? 0));
$arrSearch = array(
    array("type" => "text", "name" => "salesID", "value" => "", "title" => "#ID", "where" => "AND I.salesID = ? ", "dtype" => "i"),
    array("type" => "select", "name" => "customerID", "value" => $customerOpt, "title" => "Customer", "where" => "AND CV.customerID = ?", "dtype" => "i"),
);

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT I.salesID FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS I
            LEFT JOIN " . $DB->pre . "customer AS CV ON I.customerID = CV.customerID 
            WHERE I.status=?" . $MXFRM->where . mxWhere("I.");

$DB->dbQuery();
$MXTOTREC = $DB->numRows;
if ($MXTOTREC < 1) {
    if ((!isset($MXFRM->where) || $MXFRM->where == "")) {
        $strSearch = "";
    }
}
echo $strSearch;
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT I.* ,CV.customerID , CV.customerName,CV.emailID FROM `" . $DB->pre . $MXMOD["TBL"] . "` AS I 
            LEFT JOIN " . $DB->pre . "customer AS CV ON I.customerID = CV.customerID 
            WHERE I.status=?" . $MXFRM->where . mxWhere("I.") . mxOrderBy("I.salesID DESC ") . mxQryLimit();
            $DB->dbRows();
            $MXCOLS = array(
                array("#ID", "salesID", ' width="1%" nowrap align="center"'),
                array("Customer", "customerName", ' nowrap align="left"  ', true),
                array("Tot Quantity", "totQuantity", ' nowrap align="right" '),
                array("Tot Amount", "totProductAmt", ' nowrap align="right" '),
                array("Tot CGST", "totCGST", ' nowrap align="right"'),
                array("Tot SGST", "totSGST", ' nowrap align="right"'),
                array("Tot IGST", "totIGST", ' nowrap align="right"'),
                array("Total", "grandTotal", ' nowrap align="right"'),
            );
            $arrTot = [];
        ?>
            <table border="0" cellspacing="0" width="100%" cellpadding="8" class="tbl-list">
                <thead>
                    <tr>
                        <?php echo getListTitle($MXCOLS); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {

                        $arrTot["totQuantity"] = (($arrTot["totQuantity"] ?? 0) + $d["totQuantity"]);
                        $arrTot["totProductAmt"] = (($arrTot["totProductAmt"] ?? 0) + $d["totProductAmt"]);
                        //$arrTot["totTaxableAmt"] = round(($arrTot["totTaxableAmt"] ?? 0) + $d["totTaxableAmt"]);
                        $arrTot["totCGST"] = (($arrTot["totCGST"] ?? 0) + $d["totCGST"]);
                        $arrTot["totSGST"] = (($arrTot["totSGST"] ?? 0) + $d["totSGST"]);
                        $arrTot["totIGST"] = (($arrTot["totIGST"] ?? 0) + $d["totIGST"]);
                        $arrTot["grandTotal"] = (($arrTot["grandTotal"] ?? 0) + $d["grandTotal"]);
                        $d['totProductAmt'] = number_format($d['totProductAmt'], 2);
                        //$d['totTaxableAmt'] = number_format($d['totTaxableAmt'], 2);
                        $d['totCGST'] = number_format($d['totCGST'], 2);
                        $d['totSGST'] = number_format($d['totSGST'], 2);
                        $d['totIGST'] = number_format($d['totIGST'], 2);
                        $d['grandTotal'] = number_format($d['grandTotal'], 2);

                        $bgColor = "";

                    ?>
                        <tr>
                            <?php echo getMAction("mid", $d["salesID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2]; ?>>
                                    <?php if (isset($v[3]) && $v[3]) {
                                        echo getViewEditUrl("id=" . $d["salesID"], $d[$v[1]]);
                                    } else {
                                        echo $d[$v[1]];
                                    } ?>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr style="text-align:right;" class="trcolspan">
                        <th class="action">&nbsp;</th>
                        <th colspan="1">&nbsp;</th>
                        <th>Total</th>
                        <th><?php echo number_format($arrTot["totQuantity"], 2, '.', ''); ?></th>
                        <th><?php echo number_format($arrTot["totProductAmt"], 2, '.', ''); ?></th>
                        <th><?php echo number_format($arrTot["totCGST"], 2, '.', ''); ?></th>
                        <th><?php echo number_format($arrTot["totSGST"], 2, '.', ''); ?></th>
                        <th><?php echo number_format($arrTot["totIGST"], 2, '.', ''); ?></th>
                        <th><?php echo number_format($arrTot["grandTotal"], 2, '.', ''); ?></th>
                    </tr>
                </tfoot>
            </table>
        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>
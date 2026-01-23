<script type="text/javascript" src="<?php echo mxGetUrl($TPL->modUrl . '/inc/js/x-profit-loss.inc.js'); ?>"></script>

<?php
$arrWhere = array("sql" => "status = ? AND parentCategoryID > ?", "types" => "ii", "vals" => array(1, 0));
$params = ["table" => $DB->pre . "category", "key" => "categoryID", "val" => "categoryTitle", "where" => $arrWhere, "order" => "categoryTitle ASC"];
$categoryArrOpt  = getDataArray($params);

$fromDate = (isset($_GET['fromDate'])) ? $_GET['fromDate'] : date('Y-m-d', strtotime('-15 days'));
$toDate = (isset($_GET['toDate'])) ? $_GET['toDate'] : date('Y-m-d');
// START : search array
$arrSearch = array(
    array("type" => "select", "name" => "categoryID", "value" => getArrayDD(array("data" => $categoryArrOpt, "selected" => ($_GET['categoryID'] ?? ""))), "title" => "Category", "where" => "AND C.categoryID=?", "dtype" => "i"),
    array("type" => "text", "name" => "productSku",  "title" => "Product-Sku", "where" => "AND productSku LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "date", "name" => "fromDate", "value" => $_GET["fromDate"] ?? date('Y-m-d', strtotime('-15 days')), "title" => "From Date", "validate" => "required", "params" => array("yearRange" => "-100:+0", "maxDate" => "0d")),
    array("type" => "date", "name" => "toDate", "value" => $_GET["toDate"] ?? date('Y-m-d'), "title" => "To Date", "validate" => "required", "params" => array("yearRange" => "-100:+0", "maxDate" => "0d")),
);
// END
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);


$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $fromDate, $toDate, 1, $fromDate, $toDate, 1, $MXSTATUS, 0);
$DB->types = "ssissiii" . $MXFRM->types;

$DB->sql = "SELECT PS.productSkuID,
                    PS.productSku AS productSku,C.categoryTitle,
                    COALESCE(total_sales_revenue, 0) AS total_sales_revenue,
                    COALESCE(total_purchase_cost, 0) AS total_purchase_cost,
                    COALESCE(total_sales_revenue, 0) - COALESCE(total_purchase_cost, 0) AS profit_loss
                FROM
                    " . $DB->pre . "product_sku AS PS
                LEFT JOIN
                    (SELECT
                        productID,
                        SUM(CASE WHEN salesDate BETWEEN ? AND ? THEN totalAmt ELSE 0 END) AS total_sales_revenue
                    FROM
                        " . $DB->pre . "sales_details
                    WHERE
                        status = ?
                    GROUP BY
                        productID) AS sales_summary ON PS.productSkuID = sales_summary.productID
                LEFT JOIN
                    (SELECT
                        productID,
                        SUM(CASE WHEN purchaseDate BETWEEN ? AND ? THEN totalAmt ELSE 0 END) AS total_purchase_cost
                    FROM
                        " . $DB->pre . "purchase_details
                    WHERE
                        status = ?
                    GROUP BY
                        productID) AS purchase_summary ON PS.productSkuID = purchase_summary.productID
                        LEFT JOIN  " . $DB->pre . "category AS C ON C.categoryID=PS.categoryID 
                        WHERE
                        PS.status = ? AND  COALESCE(total_sales_revenue, 0) - COALESCE(total_purchase_cost, 0) != ? " . $MXFRM->where;

// $arrExport = array("vals" => $DB->vals, "types" => $DB->types, "sql" => $DB->sql);

$DB->dbQuery();
$MXTOTREC = $DB->numRows;

if (!$MXFRM->where && $MXTOTREC < 1)
    $strSearch = "";

echo $strSearch;

// $MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav('<a href="javascript:void(0)" class="button" id="exportBtn">Download Report</a>', '', array('trash', 'export')); ?>

    <div class="wrap-data">
        <?php
        if ($MXTOTREC > 0) {

            $MXCOLS = array(
                // array("#ID", "productSkuID", ' width="1%" align="center"'),
                array("SR NO", "srNo", ' width="1%" nowrap align="center"', false, 'nosort'),
                array("Category", "categoryTitle", ' align="left"', false, 'nosort'),
                array("Product Sku", "productSku", ' align="left"', false, 'nosort'),
                array("Total Purchase Cost", "total_purchase_cost", ' nowrap align="right"', false, 'nosort'),
                array("Total Sales Revenue", "total_sales_revenue", ' nowrap align="right"', false, 'nosort'),
                array("Profit Loss", "profit_loss", ' nowrap align="right"', false, 'nosort'),
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $fromDate, $toDate, 1, $fromDate, $toDate, 1, $MXSTATUS, 0);
            $DB->types = "ssissiii" . $MXFRM->types;
            $DB->sql = "SELECT PS.productSkuID,
                    PS.productSku AS productSku,C.categoryTitle,
                    COALESCE(total_sales_revenue, 0) AS total_sales_revenue,
                    COALESCE(total_purchase_cost, 0) AS total_purchase_cost,
                    COALESCE(total_sales_revenue, 0) - COALESCE(total_purchase_cost, 0) AS profit_loss
                FROM
                    " . $DB->pre . "product_sku AS PS
                LEFT JOIN
                    (SELECT
                        productID,
                        SUM(CASE WHEN salesDate BETWEEN ? AND ? THEN totalAmt ELSE 0 END) AS total_sales_revenue
                    FROM
                        " . $DB->pre . "sales_details
                    WHERE
                        status = ?
                    GROUP BY
                        productID) AS sales_summary ON PS.productSkuID = sales_summary.productID
                LEFT JOIN
                    (SELECT
                        productID,
                        SUM(CASE WHEN purchaseDate BETWEEN ? AND ? THEN totalAmt ELSE 0 END) AS total_purchase_cost
                    FROM
                        " . $DB->pre . "purchase_details
                    WHERE
                        status = ?
                    GROUP BY
                        productID) AS purchase_summary ON PS.productSkuID = purchase_summary.productID
                        LEFT JOIN  " . $DB->pre . "category AS C ON C.categoryID=PS.categoryID 
                        WHERE
                        PS.status = ? AND  COALESCE(total_sales_revenue, 0) - COALESCE(total_purchase_cost, 0) != ? " . $MXFRM->where . mxOrderBy("PS.productSkuID DESC ") . mxQryLimit();
            $data = $DB->dbRows();

        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list report">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php

                    $totalSaleQtyVal = 0;
                    $totalPurchaseQtyVal = 0;
                    $totalBalanceQtyVal = 0;
                    $srNo = 1;

                    foreach ($DB->rows as $d) {

                        $totalSaleQtyVal += floatVal($d['total_sales_revenue']);
                        $totalPurchaseQtyVal += floatVal($d['total_purchase_cost']);
                        $totalBalanceQtyVal += floatVal($d['profit_loss']);
                        $d['srNo'] = $srNo;
                        $srNo++;

                    ?>
                        <tr> <?php echo getMAction("mid", $d["productSkuID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["productSkuID"], $d[$v[1]]);
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
                        <th class='' colspan='3' >&nbsp;Total</th>
                        <th>" . number_format($totalPurchaseQtyVal, 2) . "</th>
                        <th>" . number_format($totalSaleQtyVal, 2) . "</th>                    
                        <th>" . number_format($totalBalanceQtyVal, 2) . "</th>
                    </tr>";
                    ?>
                </tfoot>
            </table>

        <?php } else { ?>
            <div class="no-records">No records found</div>
        <?php } ?>
    </div>
</div>
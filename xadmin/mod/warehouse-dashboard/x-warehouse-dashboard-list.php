<?php
// Warehouse Dashboard - Simple Overview

// Get warehouse count
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "warehouse WHERE status=? AND isActive=?";
$warehouseCount = $DB->dbRow()["cnt"] ?? 0;

// Get product count
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "product WHERE status=? AND isActive=?";
$productCount = $DB->dbRow()["cnt"] ?? 0;

// Get total stock value
$DB->sql = "SELECT SUM(s.quantity * COALESCE(p.basePrice, 0)) as totalValue, SUM(s.quantity) as totalQty
            FROM " . $DB->pre . "inventory_stock s
            LEFT JOIN " . $DB->pre . "product p ON s.productID = p.productID
            WHERE s.status = 1";
$stockData = $DB->dbRow();
$totalStockValue = $stockData["totalValue"] ?? 0;
$totalStockQty = $stockData["totalQty"] ?? 0;

// Get distributor count
$DB->vals = array(1, 1);
$DB->types = "ii";
$DB->sql = "SELECT COUNT(*) as cnt FROM " . $DB->pre . "distributor WHERE status=? AND isActive=?";
$distributorCount = $DB->dbRow()["cnt"] ?? 0;

// Get pending orders count
$DB->vals = array(1);
$DB->types = "i";
$DB->sql = "SELECT COUNT(*) as cnt, SUM(totalAmount) as total FROM " . $DB->pre . "b2b_sales_order WHERE status=? AND orderStatus IN ('Draft', 'Confirmed', 'Processing')";
$pendingOrders = $DB->dbRow();
$pendingOrderCount = $pendingOrders["cnt"] ?? 0;
$pendingOrderValue = $pendingOrders["total"] ?? 0;

// Get overdue invoices
$today = date("Y-m-d");
$DB->vals = array(1, $today);
$DB->types = "is";
$DB->sql = "SELECT COUNT(*) as cnt, SUM(balanceAmount) as total FROM " . $DB->pre . "b2b_invoice WHERE status=? AND invoiceStatus NOT IN ('Paid', 'Cancelled') AND dueDate < ?";
$overdueInvoices = $DB->dbRow();
$overdueCount = $overdueInvoices["cnt"] ?? 0;
$overdueValue = $overdueInvoices["total"] ?? 0;

// Get recent stock movements
$DB->sql = "SELECT sl.*, w.warehouseName, p.productSKU, p.productName
            FROM " . $DB->pre . "stock_ledger sl
            LEFT JOIN " . $DB->pre . "warehouse w ON sl.warehouseID = w.warehouseID
            LEFT JOIN " . $DB->pre . "product p ON sl.productID = p.productID
            ORDER BY sl.created DESC LIMIT 10";
$recentMovements = $DB->dbRows();

// Get low stock items
$DB->sql = "SELECT s.quantity, p.productSKU, p.productName, p.reorderLevel, w.warehouseName
            FROM " . $DB->pre . "inventory_stock s
            JOIN " . $DB->pre . "product p ON s.productID = p.productID
            JOIN " . $DB->pre . "warehouse w ON s.warehouseID = w.warehouseID
            WHERE s.status = 1 AND s.quantity > 0 AND p.reorderLevel > 0 AND s.quantity <= p.reorderLevel
            ORDER BY (s.quantity / p.reorderLevel) ASC LIMIT 10";
$lowStockItems = $DB->dbRows();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <!-- KPI Summary -->
        <h2 class="form-head">Overview</h2>
        <table width="100%" border="0" cellspacing="0" cellpadding="15" class="tbl-list">
            <tr>
                <td align="center" width="16%"><strong><?php echo $warehouseCount; ?></strong><br><small>Warehouses</small></td>
                <td align="center" width="16%"><strong><?php echo $productCount; ?></strong><br><small>Products</small></td>
                <td align="center" width="18%"><strong>Rs. <?php echo number_format($totalStockValue, 0); ?></strong><br><small>Stock Value</small></td>
                <td align="center" width="16%"><strong><?php echo $distributorCount; ?></strong><br><small>Distributors</small></td>
                <td align="center" width="16%"><strong><?php echo $pendingOrderCount; ?></strong><br><small>Pending Orders</small></td>
                <td align="center" width="18%"><strong><?php echo $overdueCount; ?></strong><br><small>Overdue Invoices</small></td>
            </tr>
        </table>

        <!-- Quick Actions -->
        <h2 class="form-head">Quick Actions</h2>
        <p>
            <a href="<?php echo ADMINURL; ?>/b2b-sales-order-add/" class="btn">+ Sales Order</a>
            <a href="<?php echo ADMINURL; ?>/distributor-add/" class="btn">+ Distributor</a>
            <a href="<?php echo ADMINURL; ?>/warehouse-add/" class="btn">+ Warehouse</a>
        </p>

        <!-- Two Column Layout -->
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%" valign="top" style="padding-right:10px;">
                    <h2 class="form-head">Low Stock Alert</h2>
                    <?php if (count($lowStockItems) > 0): ?>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <thead><tr><th align="left">Product</th><th width="100" align="right">Stock</th></tr></thead>
                        <tbody>
                            <?php foreach ($lowStockItems as $item): ?>
                            <tr>
                                <td><strong><?php echo $item["productSKU"]; ?></strong><br><small><?php echo $item["productName"]; ?></small></td>
                                <td align="right"><?php echo number_format($item["quantity"], 0); ?> / <?php echo $item["reorderLevel"]; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <table width="100%" border="0" cellspacing="0" cellpadding="20" class="tbl-list">
                        <tr><td align="center">All items are well stocked</td></tr>
                    </table>
                    <?php endif; ?>
                </td>
                <td width="50%" valign="top" style="padding-left:10px;">
                    <h2 class="form-head">Recent Stock Movements</h2>
                    <?php if (count($recentMovements) > 0): ?>
                    <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                        <thead><tr><th align="left">Product</th><th width="80">Type</th><th width="60" align="right">Qty</th></tr></thead>
                        <tbody>
                            <?php foreach ($recentMovements as $move):
                                $moveQty = ($move["qtyIn"] ?? 0) > 0 ? '+' . number_format($move["qtyIn"], 0) : '-' . number_format($move["qtyOut"] ?? 0, 0);
                            ?>
                            <tr>
                                <td><strong><?php echo $move["productSKU"]; ?></strong><br><small><?php echo $move["productName"]; ?></small></td>
                                <td><?php echo $move["transactionType"]; ?></td>
                                <td align="right"><?php echo $moveQty; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <table width="100%" border="0" cellspacing="0" cellpadding="20" class="tbl-list">
                        <tr><td align="center">No recent movements</td></tr>
                    </table>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
</div>

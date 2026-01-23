<?php
$MXFRM = new mxForm();

// Build WHERE clause
$whrArr = array("sql" => "dn.status=?", "types" => "i", "vals" => array(1));

// Search filters
if (!empty($_GET["q"])) {
    $q = "%" . $_GET["q"] . "%";
    $whrArr["sql"] .= " AND (dn.debitNoteNo LIKE ? OR dn.entityName LIKE ? OR dn.invoiceNo LIKE ?)";
    $whrArr["types"] .= "sss";
    $whrArr["vals"][] = $q;
    $whrArr["vals"][] = $q;
    $whrArr["vals"][] = $q;
}

if (!empty($_GET["entityType"])) {
    $whrArr["sql"] .= " AND dn.entityType=?";
    $whrArr["types"] .= "s";
    $whrArr["vals"][] = $_GET["entityType"];
}

if (!empty($_GET["debitNoteStatus"])) {
    $whrArr["sql"] .= " AND dn.debitNoteStatus=?";
    $whrArr["types"] .= "s";
    $whrArr["vals"][] = $_GET["debitNoteStatus"];
}

if (!empty($_GET["invoiceType"])) {
    $whrArr["sql"] .= " AND dn.invoiceType=?";
    $whrArr["types"] .= "s";
    $whrArr["vals"][] = $_GET["invoiceType"];
}

if (!empty($_GET["fromDate"])) {
    $whrArr["sql"] .= " AND dn.debitNoteDate>=?";
    $whrArr["types"] .= "s";
    $whrArr["vals"][] = $_GET["fromDate"];
}

if (!empty($_GET["toDate"])) {
    $whrArr["sql"] .= " AND dn.debitNoteDate<=?";
    $whrArr["types"] .= "s";
    $whrArr["vals"][] = $_GET["toDate"];
}

// Pagination
$limit = 20;
$page = max(1, intval($_GET["page"] ?? 1));
$offset = ($page - 1) * $limit;

// Get total count
$DB->vals = $whrArr["vals"];
$DB->types = $whrArr["types"];
$DB->sql = "SELECT COUNT(*) as total FROM " . $DB->pre . "debit_note dn WHERE " . $whrArr["sql"];
$countRow = $DB->dbRow();
$totalRows = intval($countRow["total"]);
$totalPages = ceil($totalRows / $limit);

// Get debit notes
$DB->vals = $whrArr["vals"];
$DB->types = $whrArr["types"];
$DB->sql = "SELECT dn.*,
            (SELECT COUNT(*) FROM " . $DB->pre . "note_adjustment WHERE noteType='Debit' AND noteID=dn.debitNoteID) as collectionCount
            FROM " . $DB->pre . "debit_note dn
            WHERE " . $whrArr["sql"] . "
            ORDER BY dn.debitNoteID DESC
            LIMIT " . $offset . ", " . $limit;
$rows = $DB->dbRows();

// Build filter options
$entityTypes = array("" => "All Types", "Distributor" => "Distributor", "Customer" => "Customer", "Location" => "Location");
$entityTypeOpt = "";
foreach ($entityTypes as $k => $v) {
    $sel = (($_GET["entityType"] ?? "") == $k) ? ' selected="selected"' : '';
    $entityTypeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$statuses = array("" => "All Status", "Draft" => "Draft", "Approved" => "Approved", "Partially Collected" => "Partially Collected", "Fully Collected" => "Fully Collected", "Cancelled" => "Cancelled");
$statusOpt = "";
foreach ($statuses as $k => $v) {
    $sel = (($_GET["debitNoteStatus"] ?? "") == $k) ? ' selected="selected"' : '';
    $statusOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

$invoiceTypes = array("" => "All Invoice Types", "B2B" => "B2B", "PNP" => "PNP", "Other" => "Other");
$invoiceTypeOpt = "";
foreach ($invoiceTypes as $k => $v) {
    $sel = (($_GET["invoiceType"] ?? "") == $k) ? ' selected="selected"' : '';
    $invoiceTypeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}

// Status badge colors
function getDNStatusBadge($status) {
    $colors = array(
        "Draft" => "secondary",
        "Approved" => "success",
        "Partially Collected" => "info",
        "Fully Collected" => "primary",
        "Cancelled" => "danger"
    );
    $color = $colors[$status] ?? "secondary";
    return '<span class="badge badge-' . $color . '">' . htmlspecialchars($status) . '</span>';
}
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <div class="wrap-data">
        <!-- Search Form -->
        <form name="frmSearch" id="frmSearch" action="" method="get">
            <ul class="tbl-form tbl-search">
                <?php
                $arrSearch = array(
                    array("type" => "text", "name" => "q", "value" => $_GET["q"] ?? "", "title" => "Search", "placeholder" => "DN No, Entity, Invoice..."),
                    array("type" => "select", "name" => "entityType", "value" => $entityTypeOpt, "title" => "Entity Type"),
                    array("type" => "select", "name" => "debitNoteStatus", "value" => $statusOpt, "title" => "Status"),
                    array("type" => "select", "name" => "invoiceType", "value" => $invoiceTypeOpt, "title" => "Invoice Type"),
                    array("type" => "date", "name" => "fromDate", "value" => $_GET["fromDate"] ?? "", "title" => "From Date"),
                    array("type" => "date", "name" => "toDate", "value" => $_GET["toDate"] ?? "", "title" => "To Date"),
                );
                echo $MXFRM->getForm($arrSearch);
                ?>
            </ul>
        </form>

        <!-- List Header -->
        <?php echo getListTitle(); ?>

        <!-- Data Table -->
        <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="12%">DN No</th>
                    <th width="10%">Date</th>
                    <th width="10%">Entity Type</th>
                    <th width="18%">Entity Name</th>
                    <th width="10%">Invoice</th>
                    <th width="10%" align="right">Amount</th>
                    <th width="10%" align="right">Balance</th>
                    <th width="8%">Status</th>
                    <th width="7%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="10" align="center">No debit notes found</td>
                </tr>
                <?php else: ?>
                <?php $sn = $offset; foreach ($rows as $row): $sn++; ?>
                <tr>
                    <td><?php echo $sn; ?></td>
                    <td>
                        <a href="<?php echo ADMINURL; ?>/debit-note-edit/?id=<?php echo $row["debitNoteID"]; ?>">
                            <strong><?php echo htmlspecialchars($row["debitNoteNo"]); ?></strong>
                        </a>
                    </td>
                    <td><?php echo date("d-M-Y", strtotime($row["debitNoteDate"])); ?></td>
                    <td><span class="badge badge-warning"><?php echo $row["entityType"]; ?></span></td>
                    <td><?php echo htmlspecialchars($row["entityName"]); ?></td>
                    <td>
                        <?php if ($row["invoiceNo"]): ?>
                        <span class="badge badge-secondary"><?php echo $row["invoiceType"]; ?></span>
                        <?php echo htmlspecialchars($row["invoiceNo"]); ?>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                    </td>
                    <td align="right">Rs. <?php echo number_format($row["totalAmount"], 2); ?></td>
                    <td align="right">
                        <?php if ($row["balanceAmount"] > 0): ?>
                        <strong>Rs. <?php echo number_format($row["balanceAmount"], 2); ?></strong>
                        <?php else: ?>
                        <span class="text-muted">Rs. 0.00</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo getDNStatusBadge($row["debitNoteStatus"]); ?></td>
                    <td>
                        <?php echo getMAction($row["debitNoteID"], array(
                            "edit" => true,
                            "delete" => ($row["debitNoteStatus"] == "Draft"),
                            "print" => true,
                            "custom" => array(
                                array(
                                    "url" => ADMINURL . "/mod/debit-note/x-debit-note-print.php?id=" . $row["debitNoteID"],
                                    "icon" => "print",
                                    "title" => "Print",
                                    "target" => "_blank"
                                )
                            )
                        )); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($k) { return $k != 'page'; }, ARRAY_FILTER_USE_KEY)); ?>"
               class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <!-- Summary -->
        <div class="list-summary">
            <p>Showing <?php echo count($rows); ?> of <?php echo $totalRows; ?> debit notes</p>
        </div>
    </div>
</div>

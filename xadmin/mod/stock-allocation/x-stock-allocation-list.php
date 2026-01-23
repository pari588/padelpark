<?php
// allocationType dropdown
$allocationTypeArr = array("" => "All", "Reserved" => "Reserved", "Dispatched" => "Dispatched", "Partial" => "Partial", "Returned" => "Returned");
$allocationTypeOpt = '';
$selAllocationType = $_GET["allocationType"] ?? "";
foreach ($allocationTypeArr as $k => $v) {
    $sel = ($selAllocationType == $k) ? ' selected="selected"' : '';
    $allocationTypeOpt .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
}


$arrSearch = array(
    array("type" => "text", "name" => "allocationID", "title" => "#ID", "where" => "AND a.allocationID=?", "dtype" => "i"),
    array("type" => "text", "name" => "allocationNo", "title" => "Allocation No", "where" => "AND a.allocationNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "projectName", "title" => "Project", "where" => "AND (p.projectNo LIKE CONCAT('%',?,'%') OR p.projectName LIKE CONCAT('%',?,'%'))", "dtype" => "ss"),
    array("type" => "select", "name" => "allocationType", "title" => "Status", "where" => "AND a.allocationType=?", "dtype" => "s", "value" => $allocationTypeOpt, "default" => false)
);
$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);
$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT a.allocationID FROM `" . $DB->pre . "stock_allocation` a
            LEFT JOIN `" . $DB->pre . "sky_padel_project` p ON a.projectID = p.projectID
            WHERE a.status=?" . $MXFRM->where;
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
                array("#ID", "allocationID", ' width="5%" align="center"', true),
                array("Allocation No", "allocationNo", ' width="12%" align="left"'),
                array("Project", "projectInfo", ' width="20%" align="left"'),
                array("Warehouse", "warehouseName", ' width="12%" align="left"'),
                array("Date", "allocationDate", ' width="8%" align="center"'),
                array("Items", "totalItems", ' width="6%" align="center"'),
                array("Value", "totalValue", ' width="10%" align="right"'),
                array("Status", "allocationType", ' width="10%" align="center"'),
                array("Actions", "actions", ' width="12%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT a.*, p.projectNo, p.projectName, p.siteCity, w.warehouseCode, w.warehouseName
                        FROM `" . $DB->pre . "stock_allocation` a
                        LEFT JOIN `" . $DB->pre . "sky_padel_project` p ON a.projectID = p.projectID
                        LEFT JOIN `" . $DB->pre . "warehouse` w ON a.warehouseID = w.warehouseID
                        WHERE a.status=? " . $MXFRM->where . mxOrderBy("a.allocationID DESC") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead><tr><?php echo getListTitle($MXCOLS); ?></tr></thead>
                <tbody>
                    <?php foreach ($DB->rows as $d) {
                        $d["allocationDate"] = $d["allocationDate"] != "0000-00-00" ? date("d-M-Y", strtotime($d["allocationDate"])) : "-";
                        $d["totalValue"] = "₹" . number_format($d["totalValue"] ?? 0, 2);
                        $d["projectInfo"] = '<strong>' . htmlspecialchars($d["projectNo"]) . '</strong><br><small>' . htmlspecialchars($d["projectName"]) . '</small>';

                        // Status badge
                        $statusClasses = array(
                            "Reserved" => "badge-warning",
                            "Dispatched" => "badge-success",
                            "Partial" => "badge-info",
                            "Returned" => "badge-secondary"
                        );
                        $statusClass = $statusClasses[$d["allocationType"]] ?? "badge-secondary";
                        $d["allocationType"] = '<span class="badge ' . $statusClass . '">' . $d["allocationType"] . '</span>';

                        // Actions
                        $d["actions"] = '';
                        $d["actions"] .= '<a href="' . ADMINURL . '/stock-allocation-view/?id=' . $d["allocationID"] . '" class="btn-action" title="View"><i class="fa fa-eye"></i></a> ';

                        if ($d["allocationType"] == '<span class="badge badge-warning">Reserved</span>') {
                            $d["actions"] .= '<a href="' . ADMINURL . '/stock-allocation-edit/?id=' . $d["allocationID"] . '" class="btn-action" title="Edit"><i class="fa fa-edit"></i></a> ';
                            $d["actions"] .= '<button onclick="dispatchAllocation(' . $d["allocationID"] . ')" class="btn-action" title="Dispatch" style="border:none;background:none;cursor:pointer;"><i class="fa fa-truck" style="color:#28a745;"></i></button>';
                        }
                    ?>
                        <tr><?php echo getMAction("mid", $d["allocationID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td<?php echo $v[2]; ?>><?php echo isset($v[3]) && $v[3] ? getViewEditUrl("id=" . $d["allocationID"], strip_tags($d[$v[1]])) : ($d[$v[1]] ?? ""); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="no-records">No allocations found</div>
        <?php } ?>
    </div>
</div>

<script>
function dispatchAllocation(allocationID) {
    if (confirm('Are you sure you want to dispatch this allocation? This will move items to in-transit.')) {
        $.mxajax({
            url: MODURL + 'x-stock-allocation.inc.php',
            data: { xAction: 'DISPATCH', allocationID: allocationID }
        }).done(function(res) {
            if (res.err == 0) {
                alert('Allocation dispatched successfully!');
                location.reload();
            } else {
                alert(res.msg || res.errMsg || 'Failed to dispatch');
            }
        });
    }
}
</script>

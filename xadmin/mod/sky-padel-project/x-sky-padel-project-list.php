<?php
// START : search array
$arrSearch = array(
    array("type" => "text", "name" => "projectID",  "title" => "#ID", "where" => "AND projectID=?", "dtype" => "i"),
    array("type" => "text", "name" => "projectNo",  "title" => "Project No", "where" => "AND projectNo LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "projectName",  "title" => "Project Name", "where" => "AND projectName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "clientName",  "title" => "Client Name", "where" => "AND clientName LIKE CONCAT('%',?,'%')", "dtype" => "s"),
    array("type" => "text", "name" => "siteCity",  "title" => "City", "where" => "AND siteCity LIKE CONCAT('%',?,'%')", "dtype" => "s")
);
// END

$MXFRM = new mxForm();
$strSearch = $MXFRM->getFormS($arrSearch);

$DB->vals = $MXFRM->vals;
array_unshift($DB->vals, $MXSTATUS);
$DB->types = "i" . $MXFRM->types;
$DB->sql = "SELECT " . $MXMOD["PK"] . " FROM `" . $DB->pre . $MXMOD["TBL"] . "`  WHERE status=?" . $MXFRM->where;
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
                array("#ID", "projectID", ' width="1%" align="center"', true),
                array("Project No", "projectNo", ' width="10%" nowrap align="left"'),
                array("Project Name", "projectName", ' width="18%" align="left"'),
                array("Client", "clientName", ' width="13%" align="left"'),
                array("City", "siteCity", ' width="8%" align="left"'),
                array("Contract Amount", "contractAmount", ' width="10%" align="right"'),
                array("Status", "projectStatus", ' width="8%" align="center"'),
                array("Start Date", "startDate", ' width="8%" align="center"'),
                array("Actions", "actions", ' width="8%" align="center"')
            );
            $DB->vals = $MXFRM->vals;
            array_unshift($DB->vals, $MXSTATUS);
            $DB->types = "i" . $MXFRM->types;
            $DB->sql = "SELECT projectID,projectNo,projectName,clientName,siteCity,contractAmount,projectStatus,startDate FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? " . $MXFRM->where . mxOrderBy("projectID DESC ") . mxQryLimit();
            $DB->dbRows();
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="8" class="tbl-list">
                <thead>
                    <tr> <?php echo getListTitle($MXCOLS); ?></tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($DB->rows as $d) {
                        // Format contract amount
                        if (isset($d["contractAmount"])) {
                            $d["contractAmount"] = "₹" . number_format($d["contractAmount"], 2);
                        }
                        // Format date
                        if (isset($d["startDate"]) && $d["startDate"] != "0000-00-00") {
                            $d["startDate"] = date("d-M-Y", strtotime($d["startDate"]));
                        } else {
                            $d["startDate"] = "-";
                        }
                        // Status badge
                        $statusClass = "";
                        switch($d["projectStatus"]) {
                            case "Lead": $statusClass = "badge-info"; break;
                            case "Quoted": $statusClass = "badge-warning"; break;
                            case "Active": $statusClass = "badge-success"; break;
                            case "On Hold": $statusClass = "badge-secondary"; break;
                            case "Completed": $statusClass = "badge-primary"; break;
                            case "Cancelled": $statusClass = "badge-danger"; break;
                        }
                        $d["projectStatus"] = '<span class="badge ' . $statusClass . '">' . $d["projectStatus"] . '</span>';

                        // Actions column with Gantt chart link
                        $d["actions"] = '<a href="' . ADMINURL . '/sky-padel-project-gantt/?id=' . $d["projectID"] . '" class="btn-action" title="Gantt Chart"><i class="fa fa-project-diagram"></i></a> ';
                        $d["actions"] .= '<a href="' . ADMINURL . '/sky-padel-project-edit/?id=' . $d["projectID"] . '" class="btn-action" title="Edit"><i class="fa fa-edit"></i></a>';
                    ?>
                        <tr> <?php echo getMAction("mid", $d["projectID"]); ?>
                            <?php foreach ($MXCOLS as $v) { ?>
                                <td <?php echo $v[2];
                                    ?> title="<?php echo $v[0]; ?>">
                                    <?php
                                    if (isset($v[3]) && $v[3] != "") {
                                        echo getViewEditUrl("id=" . $d["projectID"], strip_tags($d[$v[1]]));
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
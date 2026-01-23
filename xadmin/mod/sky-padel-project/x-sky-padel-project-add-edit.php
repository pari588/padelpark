<?php
$id = 0;
$D = array();
$arrMilestones = array();

// Check if quotationID is passed (converting quotation to project)
$quotationID = isset($_GET['quotationID']) ? intval($_GET['quotationID']) : 0;
if ($quotationID > 0 && $TPL->pageType == "add") {
    // Get quotation and lead details to pre-fill the form
    $DB->vals = array(1, $quotationID);
    $DB->types = "ii";
    $DB->sql = "SELECT q.*, l.clientName, l.clientEmail, l.clientPhone, l.siteAddress, l.siteCity, l.siteState, l.leadID
                FROM `" . $DB->pre . "sky_padel_quotation` q
                LEFT JOIN `" . $DB->pre . "sky_padel_lead` l ON q.leadID = l.leadID
                WHERE q.status=? AND q.quotationID=?";
    $quotData = $DB->dbRow();

    if ($quotData) {
        $D = array(
            "leadID" => $quotData["leadID"] ?? 0,
            "quotationID" => $quotationID,
            "projectName" => $quotData["clientName"] . " - " . $quotData["courtConfiguration"],
            "clientName" => $quotData["clientName"] ?? "",
            "clientEmail" => $quotData["clientEmail"] ?? "",
            "clientPhone" => $quotData["clientPhone"] ?? "",
            "siteAddress" => $quotData["siteAddress"] ?? "",
            "siteCity" => $quotData["siteCity"] ?? "",
            "siteState" => $quotData["siteState"] ?? "",
            "courtConfiguration" => $quotData["courtConfiguration"] ?? "",
            "quotationAmount" => $quotData["totalAmount"] ?? 0,
            "contractAmount" => $quotData["totalAmount"] ?? 0,
            "advanceReceived" => $quotData["advanceAmount"] ?? 0
        );
    }
}

if ($TPL->pageType == "edit" || $TPL->pageType == "view") {
    $id = intval($_GET["id"] ?? 0);
    $DB->vals = array(1, $id);
    $DB->types = "ii";
    $DB->sql = "SELECT * FROM `" . $DB->pre . $MXMOD["TBL"] . "` WHERE status=? AND `" . $MXMOD["PK"] . "` =?";
    $D = $DB->dbRow();

    // Get project milestones
    $DB->vals = array($id);
    $DB->types = "i";
    $DB->sql = "SELECT * FROM " . $DB->pre . "project_milestone WHERE projectID=? ORDER BY sortOrder";
    $data = $DB->dbRows();

    foreach ($data as $k => $v) {
        $arrMilestones[$k] = $v;
    }
}

if (count($arrMilestones) < 1) {
    $v = array();
    $arrMilestones[] = $v;
}

// Get users for project manager and sales person dropdowns
$whrArr = array("sql" => "status=?", "types" => "i", "vals" => array(1));
$managerOpt = getTableDD(["table" => $DB->pre . "x_admin_user", "key" => "userID", "val" => "displayName", "selected" => ($D['projectManagerID'] ?? 0), "where" => $whrArr]);
$salesOpt = getTableDD(["table" => $DB->pre . "x_admin_user", "key" => "userID", "val" => "displayName", "selected" => ($D['salesPersonID'] ?? 0), "where" => $whrArr]);

// Get workflow-related dropdowns
$leadOpt = getTableDD(["table" => $DB->pre . "sky_padel_lead", "key" => "leadID", "val" => "CONCAT(leadNo, ' - ', clientName)", "selected" => ($D['leadID'] ?? 0), "where" => $whrArr]);
$quotationOpt = getTableDD(["table" => $DB->pre . "sky_padel_quotation", "key" => "quotationID", "val" => "CONCAT(quotationNo, ' - ', DATE_FORMAT(quotationDate, '%d-%b-%Y'))", "selected" => ($D['quotationID'] ?? 0), "where" => $whrArr]);

$arrForm = array(
    array("type" => "text", "name" => "projectNo", "value" => $D["projectNo"] ?? "", "title" => "Project No", "info" => '<span class="info">Leave blank for auto-generation</span>'),
    array("type" => "select", "name" => "leadID", "value" => $leadOpt, "title" => "Lead", "info" => '<span class="info">Link to original lead</span>'),
    array("type" => "select", "name" => "quotationID", "value" => $quotationOpt, "title" => "Quotation", "info" => '<span class="info">Link to approved quotation</span>'),
    array("type" => "text", "name" => "projectName", "value" => $D["projectName"] ?? "", "title" => "Project Name", "validate" => "required"),
    array("type" => "text", "name" => "clientName", "value" => $D["clientName"] ?? "", "title" => "Client Name", "validate" => "required"),
    array("type" => "text", "name" => "clientEmail", "value" => $D["clientEmail"] ?? "", "title" => "Client Email"),
    array("type" => "text", "name" => "clientPhone", "value" => $D["clientPhone"] ?? "", "title" => "Client Phone"),
    array("type" => "textarea", "name" => "siteAddress", "value" => $D["siteAddress"] ?? "", "title" => "Site Address", "params" => array("rows" => 3)),
    array("type" => "text", "name" => "siteCity", "value" => $D["siteCity"] ?? "", "title" => "City"),
    array("type" => "text", "name" => "siteState", "value" => $D["siteState"] ?? "", "title" => "State"),
);

$arrForm1 = array(
    array("type" => "text", "name" => "courtConfiguration", "value" => $D["courtConfiguration"] ?? "", "title" => "Court Configuration", "info" => '<span class="info">e.g., Single, Double, Triple</span>'),
    array("type" => "text", "name" => "quotationAmount", "value" => $D["quotationAmount"] ?? "0", "title" => "Quotation Amount (₹)", "validate" => "number"),
    array("type" => "text", "name" => "contractAmount", "value" => $D["contractAmount"] ?? "0", "title" => "Contract Amount (₹)", "validate" => "number"),
    array("type" => "text", "name" => "advanceReceived", "value" => $D["advanceReceived"] ?? "0", "title" => "Advance Received (₹)", "validate" => "number"),
    array("type" => "text", "name" => "totalCost", "value" => $D["totalCost"] ?? "0", "title" => "Total Cost (₹)", "validate" => "number"),
    array("type" => "text", "name" => "profitAmount", "value" => $D["profitAmount"] ?? "0", "title" => "Profit Amount (₹)", "validate" => "number"),
    array("type" => "date", "name" => "startDate", "value" => $D["startDate"] ?? "", "title" => "Start Date"),
    array("type" => "date", "name" => "expectedEndDate", "value" => $D["expectedEndDate"] ?? "", "title" => "Expected End Date"),
    array("type" => "date", "name" => "actualEndDate", "value" => $D["actualEndDate"] ?? "", "title" => "Actual End Date"),
    array("type" => "select", "name" => "projectStatus", "value" => (function() use ($D) {
        $statuses = array("Lead", "Quoted", "Active", "On Hold", "Completed", "Cancelled");
        $opt = "";
        $current = $D["projectStatus"] ?? "Lead";
        foreach ($statuses as $s) {
            $sel = ($current == $s) ? ' selected="selected"' : '';
            $opt .= '<option value="' . $s . '"' . $sel . '>' . $s . '</option>';
        }
        return $opt;
    })(), "title" => "Status", "validate" => "required"),
    array("type" => "select", "name" => "projectManagerID", "value" => $managerOpt, "title" => "Project Manager"),
    array("type" => "select", "name" => "salesPersonID", "value" => $salesOpt, "title" => "Sales Person"),
    array("type" => "file", "name" => "projectImage", "value" => array($D["projectImage"] ?? "", $id ?? 0), "title" => "Project Image", "params" => array("EXT" => "jpg|jpeg|png|gif|webp")),
    array("type" => "editor", "name" => "projectDescription", "value" => $D["projectDescription"] ?? "", "title" => "Project Description", "params" => array("toolbar" => "basic", "height" => 150)),
);

$arrMilestoneFields = array(
    array("type" => "hidden", "name" => "milestoneID"),
    array("type" => "text", "name" => "milestoneName", "title" => "Milestone Name"),
    array("type" => "textarea", "name" => "milestoneDescription", "title" => "Description", "attr" => ' rows="2"'),
    array("type" => "date", "name" => "targetDate", "title" => "Target Date"),
    array("type" => "text", "name" => "completionPercentage", "title" => "Completion %"),
    array("type" => "checkbox", "name" => "isCompleted", "title" => "Completed", "value" => array(array("val" => "1", "txt" => "Yes")))
);

$MXFRM = new mxForm();
?>
<div class="wrap-right">
    <?php echo getPageNav(); ?>
    <form class="wrap-data" name="frmAddEdit" id="frmAddEdit" action="" method="post" enctype="multipart/form-data">
        <div class="wrap-form f50">
            <h2 class="form-head">Project Information</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm);
                ?>
            </ul>
        </div>
        <div class="wrap-form f50">
            <h2 class="form-head">Financial & Timeline</h2>
            <ul class="tbl-form">
                <?php
                echo $MXFRM->getForm($arrForm1);
                ?>
            </ul>
        </div>
        <div class="wrap-form">
            <h2 class="form-head">Project Milestones</h2>
            <?php
            echo $MXFRM->getFormG(array("flds" => $arrMilestoneFields, "vals" => $arrMilestones, "type" => 0, "addDel" => true, "class" => " small"));
            ?>
        </div>
        <?php echo $MXFRM->closeForm(); ?>
    </form>
</div>
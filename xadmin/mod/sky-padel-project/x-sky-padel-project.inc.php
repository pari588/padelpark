<?php
/*
addProject = To save Project data.
updateProject = To update Project data.
addUpdateProjectMilestone = To add and update Project's milestone data.
copyProformaMilestonesToProject = Copy milestones from proforma to project.
*/

// Copy milestones from proforma invoice to project milestones
function copyProformaMilestonesToProject($quotationID, $projectID, $startDate)
{
    global $DB;

    // Get proforma ID from quotation
    $DB->vals = array($quotationID);
    $DB->types = "i";
    $DB->sql = "SELECT proformaID FROM " . $DB->pre . "sky_padel_quotation WHERE quotationID=?";
    $quotation = $DB->dbRow();

    if (empty($quotation["proformaID"])) {
        // Try to get milestones from quotation milestones instead
        $DB->vals = array($quotationID);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_quotation_milestone WHERE quotationID=? ORDER BY sortOrder";
        $milestones = $DB->dbRows();
    } else {
        // Get milestones from proforma
        $DB->vals = array($quotation["proformaID"]);
        $DB->types = "i";
        $DB->sql = "SELECT * FROM " . $DB->pre . "sky_padel_proforma_milestone WHERE proformaID=? ORDER BY sortOrder";
        $milestones = $DB->dbRows();
    }

    if (!empty($milestones)) {
        $startDateObj = new DateTime($startDate ?: date("Y-m-d"));

        foreach ($milestones as $m) {
            // Calculate due date based on dueAfterDays
            $dueDate = null;
            if (!empty($m["dueAfterDays"])) {
                $dueDateObj = clone $startDateObj;
                $dueDateObj->add(new DateInterval('P' . intval($m["dueAfterDays"]) . 'D'));
                $dueDate = $dueDateObj->format('Y-m-d');
            }

            $DB->table = $DB->pre . "project_milestone";
            $DB->data = array(
                "projectID" => $projectID,
                "milestoneName" => $m["milestoneName"],
                "milestoneDescription" => $m["milestoneDescription"] ?? "",
                "targetDate" => $dueDate,
                "completionPercentage" => 0,
                "isCompleted" => 0,
                "paymentPercentage" => $m["paymentPercentage"] ?? 0,
                "paymentAmount" => $m["paymentAmount"] ?? 0,
                "dueDate" => $dueDate,
                "paymentStatus" => "Pending",
                "sortOrder" => $m["sortOrder"] ?? 0
            );
            $DB->dbInsert();
        }
        return true;
    }
    return false;
}

//Start: To save Project data.
function addProject()
{
    global $DB;

    if (isset($_POST["projectName"]))
        $_POST["projectName"] = cleanTitle($_POST["projectName"]);
    if (isset($_POST["clientName"]))
        $_POST["clientName"] = cleanTitle($_POST["clientName"]);
    if (isset($_POST["projectDescription"]))
        $_POST["projectDescription"] = cleanHtml($_POST["projectDescription"]);
    if (isset($_POST["quotationAmount"]))
        $_POST["quotationAmount"] = floatval($_POST["quotationAmount"]);
    if (isset($_POST["contractAmount"]))
        $_POST["contractAmount"] = floatval($_POST["contractAmount"]);
    if (isset($_POST["advanceReceived"]))
        $_POST["advanceReceived"] = floatval($_POST["advanceReceived"]);
    if (isset($_POST["totalCost"]))
        $_POST["totalCost"] = floatval($_POST["totalCost"]);
    if (isset($_POST["profitAmount"]))
        $_POST["profitAmount"] = floatval($_POST["profitAmount"]);
    if (isset($_POST["projectManagerID"]))
        $_POST["projectManagerID"] = intval($_POST["projectManagerID"]);
    if (isset($_POST["salesPersonID"]))
        $_POST["salesPersonID"] = intval($_POST["salesPersonID"]);
    if (isset($_POST["leadID"]))
        $_POST["leadID"] = intval($_POST["leadID"]);
    if (isset($_POST["quotationID"]))
        $_POST["quotationID"] = intval($_POST["quotationID"]);

    $_POST["projectImage"] = mxGetFileName("projectImage");

    // Generate projectNo if not provided
    if (empty($_POST["projectNo"])) {
        $_POST["projectNo"] = "PRJ-" . date("Ymd") . "-" . rand(1000, 9999);
    }

    $DB->table = $DB->pre . "sky_padel_project";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $projectID = $DB->insertID;
        if ($projectID) {
            // If quotationID is provided, copy milestones from proforma/quotation
            $quotationID = intval($_POST["quotationID"] ?? 0);
            $startDate = $_POST["startDate"] ?? date("Y-m-d");

            if ($quotationID > 0) {
                // Try to copy milestones from proforma first
                $copied = copyProformaMilestonesToProject($quotationID, $projectID, $startDate);
                if (!$copied) {
                    // Fall back to manually entered milestones
                    addUpdateProjectMilestone($projectID);
                }

                // Update proforma with project ID
                $DB->vals = array($quotationID);
                $DB->types = "i";
                $DB->sql = "SELECT proformaID FROM " . $DB->pre . "sky_padel_quotation WHERE quotationID=?";
                $q = $DB->dbRow();
                if (!empty($q["proformaID"])) {
                    $DB->vals = array($projectID, $q["proformaID"]);
                    $DB->types = "ii";
                    $DB->sql = "UPDATE " . $DB->pre . "sky_padel_proforma_invoice SET projectID=? WHERE proformaID=?";
                    $DB->dbQuery();
                }
            } else {
                // No quotation, use manually entered milestones
                addUpdateProjectMilestone($projectID);
            }

            setResponse(array("err" => 0, "param" => "id=$projectID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.

//Start: To update Project data.
function  updateProject()
{
    global $DB;
    $projectID = intval($_POST["projectID"]);
    if (isset($_POST["projectName"]))
        $_POST["projectName"] = cleanTitle($_POST["projectName"]);
    if (isset($_POST["clientName"]))
        $_POST["clientName"] = cleanTitle($_POST["clientName"]);
    if (isset($_POST["projectDescription"]))
        $_POST["projectDescription"] = cleanHtml($_POST["projectDescription"]);
    if (isset($_POST["quotationAmount"]))
        $_POST["quotationAmount"] = floatval($_POST["quotationAmount"]);
    if (isset($_POST["contractAmount"]))
        $_POST["contractAmount"] = floatval($_POST["contractAmount"]);
    if (isset($_POST["advanceReceived"]))
        $_POST["advanceReceived"] = floatval($_POST["advanceReceived"]);
    if (isset($_POST["totalCost"]))
        $_POST["totalCost"] = floatval($_POST["totalCost"]);
    if (isset($_POST["profitAmount"]))
        $_POST["profitAmount"] = floatval($_POST["profitAmount"]);
    if (isset($_POST["projectManagerID"]))
        $_POST["projectManagerID"] = intval($_POST["projectManagerID"]);
    if (isset($_POST["salesPersonID"]))
        $_POST["salesPersonID"] = intval($_POST["salesPersonID"]);
    if (isset($_POST["leadID"]))
        $_POST["leadID"] = intval($_POST["leadID"]);
    if (isset($_POST["quotationID"]))
        $_POST["quotationID"] = intval($_POST["quotationID"]);

    $_POST["projectImage"] = mxGetFileName("projectImage");

    $DB->table = $DB->pre . "sky_padel_project";
    $DB->data = $_POST;
    if ($DB->dbUpdate("projectID=?", "i", array($projectID))) {
        if ($projectID) {
            $DB->vals = array($projectID);
            $DB->types = "i";
            $DB->sql = "DELETE FROM " . $DB->pre . "project_milestone WHERE projectID=?";
            $DB->dbQuery();
            addUpdateProjectMilestone($projectID);
            setResponse(array("err" => 0, "param" => "id=$projectID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
//End.
//Start: To  Add and Update Project Milestone data.
function addUpdateProjectMilestone($projectID = 0)
{
    global $DB;
    if (intval($projectID) > 0) {
        if (isset($_POST["milestoneID"]) && count($_POST["milestoneID"]) > 0) {
            for ($i = 0; $i < count($_POST["milestoneID"]); $i++) {
                if ($_POST["milestoneName"][$i] != "") {
                    $arrIn = array(
                        "projectID" => $projectID,
                        "milestoneName" => $_POST["milestoneName"][$i],
                        "milestoneDescription" => $_POST["milestoneDescription"][$i] ?? "",
                        "targetDate" => $_POST["targetDate"][$i] ?? null,
                        "completionPercentage" => intval($_POST["completionPercentage"][$i] ?? 0),
                        "isCompleted" => intval($_POST["isCompleted"][$i] ?? 0),
                        "sortOrder" => $i
                    );
                    $DB->table = $DB->pre . "project_milestone";
                    $DB->data = $arrIn;
                    $DB->dbInsert();
                }
            }
        }
    }
}
//End
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addProject();
                break;
            case "UPDATE":
                updateProject();
                break;
            case "mxDelFile":
                $param = array("dir" => "sky-padel-project", "tbl" => "sky_padel_project", "pk" => "projectID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "sky_padel_project", "PK" => "projectID", "UDIR" => array("projectImage" => "sky-padel-project")));
}

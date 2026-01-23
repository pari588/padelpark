<?php
/*
addProgram = To save Program data.
updateProgram = To update Program data.
generateProgramCode = Generate unique program code.
*/

function generateProgramCode()
{
    global $DB;
    $prefix = "IPA-PRG-";
    $DB->sql = "SELECT programCode FROM " . $DB->pre . "ipa_program
                WHERE programCode LIKE '" . $prefix . "%'
                ORDER BY programID DESC LIMIT 1";
    $row = $DB->dbRow();
    $nextNum = 1;
    if ($DB->numRows > 0) {
        $lastNum = intval(substr($row['programCode'], -3));
        $nextNum = $lastNum + 1;
    }
    return $prefix . str_pad($nextNum, 3, "0", STR_PAD_LEFT);
}

function addProgram()
{
    global $DB;

    // Generate program code if empty
    if (empty($_POST["programCode"])) {
        $_POST["programCode"] = generateProgramCode();
    }

    $DB->table = $DB->pre . "ipa_program";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateProgram()
{
    global $DB;
    $programID = intval($_POST["programID"]);

    $DB->table = $DB->pre . "ipa_program";
    $DB->data = $_POST;
    if ($DB->dbUpdate("programID=?", "i", array($programID))) {
        setResponse(array("err" => 0, "param" => "id=" . $programID));
    } else {
        setResponse(array("err" => 1));
    }
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    ob_start();
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    ob_end_clean();

    header('Content-Type: application/json');
    $MXRES = mxCheckRequest(true, true);
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addProgram(); break;
            case "UPDATE": updateProgram(); break;
        }
    }
    echo json_encode($MXRES);
    exit;
} else {
    if (function_exists("setModVars")) {
        setModVars(array("TBL" => "ipa_program", "PK" => "programID"));
    }
}

<?php
/*
addCategory = To save Category data.
updateCategory = To update Category data.
*/

function addCategory()
{
    global $DB;

    $DB->table = $DB->pre . "ipt_category";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateCategory()
{
    global $DB;
    $categoryID = intval($_POST["categoryID"]);

    $DB->table = $DB->pre . "ipt_category";
    $DB->data = $_POST;
    if ($DB->dbUpdate("categoryID=?", "i", array($categoryID))) {
        setResponse(array("err" => 0, "param" => "id=" . $categoryID));
    } else {
        setResponse(array("err" => 1));
    }
}

// Handle AJAX actions
if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addCategory(); break;
            case "UPDATE": updateCategory(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "ipt_category", "PK" => "categoryID"));
}

<?php
/*
addEquipment = To save Equipment data.
updateEquipment = To update Equipment data.
*/

function addEquipment()
{
    global $DB;

    $locationID = intval($_POST["locationID"]);
    if ($locationID == 0) {
        setResponse(array("err" => 1, "msg" => "Location is required"));
        return;
    }

    // Set available = total for new equipment
    $_POST["availableQuantity"] = $_POST["totalQuantity"] ?? 1;

    $DB->table = $DB->pre . "pnp_equipment";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(array("err" => 0, "param" => "id=" . $DB->insertID));
    } else {
        setResponse(array("err" => 1));
    }
}

function updateEquipment()
{
    global $DB;
    $equipmentID = intval($_POST["equipmentID"]);

    $DB->table = $DB->pre . "pnp_equipment";
    $DB->data = $_POST;
    if ($DB->dbUpdate("equipmentID=?", "i", array($equipmentID))) {
        setResponse(array("err" => 0, "param" => "id=" . $equipmentID));
    } else {
        setResponse(array("err" => 1));
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addEquipment(); break;
            case "UPDATE": updateEquipment(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "pnp_equipment", "PK" => "equipmentID"));
}

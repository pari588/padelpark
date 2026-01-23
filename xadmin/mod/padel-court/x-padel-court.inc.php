<?php
/*
addCourt = To save court data.
updateCourt = To update court data.
*/

// Start: To save court data
function addCourt()
{
    global $DB;
    if (isset($_POST["courtName"]))
        $_POST["courtName"] = cleanTitle($_POST["courtName"]);
    if (isset($_POST["centerName"]))
        $_POST["centerName"] = cleanTitle($_POST["centerName"]);
    if (isset($_POST["courtDescription"]))
        $_POST["courtDescription"] = trim($_POST["courtDescription"]);
    if (isset($_POST["pricePerHour"]))
        $_POST["pricePerHour"] = floatval($_POST["pricePerHour"]);

    $_POST["courtImage"] = mxGetFileName("courtImage");

    $DB->table = $DB->pre . "padel_court";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $courtID = $DB->insertID;
        if ($courtID) {
            setResponse(array("err" => 0, "param" => "id=$courtID"));
        }
    } else {
        setResponse(array("err" => 1));
    }
}
// End

// Start: To update court data
function updateCourt()
{
    global $DB;
    $courtID = intval($_POST["courtID"]);
    if (isset($_POST["courtName"]))
        $_POST["courtName"] = cleanTitle($_POST["courtName"]);
    if (isset($_POST["centerName"]))
        $_POST["centerName"] = cleanTitle($_POST["centerName"]);
    if (isset($_POST["courtDescription"]))
        $_POST["courtDescription"] = trim($_POST["courtDescription"]);
    if (isset($_POST["pricePerHour"]))
        $_POST["pricePerHour"] = floatval($_POST["pricePerHour"]);

    $_POST["courtImage"] = mxGetFileName("courtImage");

    $DB->table = $DB->pre . "padel_court";
    $DB->data = $_POST;
    if ($DB->dbUpdate("courtID=?", "i", array($courtID))) {
        setResponse(array("err" => 0, "param" => "id=$courtID"));
    } else {
        setResponse(array("err" => 1));
    }
}
// End

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addCourt();
                break;
            case "UPDATE":
                updateCourt();
                break;
            case "mxDelFile":
                $param = array("dir" => "padel-court", "tbl" => "padel_court", "pk" => "courtID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "padel_court", "PK" => "courtID", "UDIR" => array("courtImage" => "padel-court")));
}
?>

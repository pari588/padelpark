<?php
function addVendorCategory() {
    global $DB;
    $_POST["categoryName"] = cleanTitle($_POST["categoryName"]);
    $DB->table = $DB->pre . "vendor_category";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        setResponse(["err" => 0, "param" => "id=" . $DB->insertID]);
    } else {
        setResponse(["err" => 1]);
    }
}

function updateVendorCategory() {
    global $DB;
    $id = intval($_POST["categoryID"]);
    $DB->table = $DB->pre . "vendor_category";
    $DB->data = $_POST;
    if ($DB->dbUpdate("categoryID=?", "i", array($id))) {
        setResponse(["err" => 0, "param" => "id=$id"]);
    } else {
        setResponse(["err" => 1]);
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest(true, true); // Session-based auth
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD": addVendorCategory(); break;
            case "UPDATE": updateVendorCategory(); break;
        }
    }
    echo json_encode($MXRES);
} else {
    setModVars(["TBL" => "vendor_category", "PK" => "categoryID"]);
}

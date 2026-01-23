<?php
// Start: To add category data.
function addCategory()
{
    global $DB, $TPL;
    $DB->table = $DB->pre . "category";
    $_POST['dateAdded']=date("Y-m-d H:i:s");
    $_POST['dateModified']=date("Y-m-d H:i:s");
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $categoryID = $DB->insertID;
        if ($categoryID) {
            setResponse(array("err" => 0, "param" => "id=$categoryID"));
        }
    } else {
        setResponse(1);
    }
}
// End.
// Start: To update category data.
function updateCategory()
{
    global $DB;
    $_POST['dateModified']=date("Y-m-d H:i:s");      
    $categoryID = intval($_POST["categoryID"]);
    $DB->table = $DB->pre . "category";
    $DB->data = $_POST;
    if ($DB->dbUpdate("categoryID=?", "i", array($categoryID))) {
        setResponse(array("err" => 0, "param" => "id=$categoryID"));
    } else {
        setResponse(1);
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    require_once("../../inc/site.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addCategory();
                break;
            case "UPDATE":
                updateCategory();
                break;
            case "mxDelFile":
                $param = array("dir" => "category", "tbl" => "category", "pk" => "categoryID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {
    if (function_exists("setModVars")) setModVars(array("TBL" => "category", "PK" => "categoryID"));
}

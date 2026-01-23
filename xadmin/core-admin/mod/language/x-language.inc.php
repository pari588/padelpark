<?php

function addLanguage() {
    global $DB, $TPL;
    $_POST["imageName"] = mxGetFileName("imageName");
    $_POST["langPrefix"] = strtolower($_POST["langPrefix"]);
    $DB->table = $DB->pre . "x_language";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $langID = $DB->insertID;
        if ($langID) {
            setResponse(["err"=>0,"param"=>"id=$langID"]);
        }
    } else {
        setResponse(["err"=>1]);
    }
}

function updateLanguage() {
    global $DB, $TPL;
    $langID = intval($_POST["langID"]);
    $_POST["imageName"] = mxGetFileName("imageName");
    $_POST["langPrefix"] = strtolower($_POST["langPrefix"]);
    $DB->table = $DB->pre . "x_language";
    $DB->data = $_POST;
    if ($DB->dbUpdate("langID=?","i",array($langID))) {
        setResponse(["err"=>0,"param"=>"id=$langID"]);
    } else {
        setResponse(["err"=>1]);
    }
}

if (isset($_POST["xAction"])) {
    require_once( "../../../../core/core.inc.php" );
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addLanguage();
                break;
            case "UPDATE":
                updateLanguage();
                break;
            case "mxDelFile":
                $param = array("dir" => "language", "tbl" => "x_language", "pk" => "langID");
                mxDelFile(array_merge($_REQUEST,$param));
                break;
        }
    }
    echo json_encode($MXRES);
} else { // Following all required
     if(function_exists("setModVars")) setModVars(array("TBL" => "x_language", "PK" => "langID", "UDIR" => array("imageName" => "language")));
}
?>
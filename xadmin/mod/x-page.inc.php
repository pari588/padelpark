<?php
function addPage()
{
    global $DB;
    $_POST["seoUri"] = makeSeoUri($_POST["pageTitle"]);
    $_POST["pageImage"] = mxGetFileName("pageImage");
    $_POST["pageTitle"] = cleanTitle($_POST["pageTitle"]);
    $_POST["synopsis"] = cleanHtml($_POST["synopsis"]);
    $_POST["pageContent"] = cleanHtml($_POST["pageContent"]);
    $_POST["templateFile"] = cleanTitle($_POST["templateFile"]);
    $DB->table = $DB->pre . "page";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $pageID = $DB->insertID;
        if ($pageID) {
            saveMeta(array("metaValue" => $pageID));
            //setResponse(0, "id=$pageID");
            setResponse(array("err" => 0, "param" => "id=$pageID"));
        }
    } else {
       // setResponse(1);
        setResponse(array("err" => 1));
    }
}

function updatePage()
{
    global $DB;
    $_POST["seoUri"] = makeSeoUri($_POST["pageTitle"]);
    $_POST["pageImage"] = mxGetFileName("pageImage");
    $_POST["pageTitle"] = cleanTitle($_POST["pageTitle"]);
    $_POST["synopsis"] = cleanHtml($_POST["synopsis"]);
    $_POST["pageContent"] = cleanHtml($_POST["pageContent"]);
    if ($_POST["templateFile"])
        $_POST["templateFile"] = cleanTitle($_POST["templateFile"]);

    $pageID = intval($_POST["pageID"]);
    $DB->table = $DB->pre . "page";
    $DB->data = $_POST;
    if ($DB->dbUpdate("pageID=?", "i", array($pageID))) {
        saveMeta(array("metaValue" => $pageID));
        //setResponse(0, "id=$pageID");
         setResponse(array("err" => 0, "param" => "id=$pageID"));
    } else {
       // setResponse(1);
       setResponse(array("err" =>1));
    }
}

function getPageTemplates()
{
    $arr = array();
    if ($dir = @opendir(SITEPATH . "/mod/page/")) {
        $skMod = array("x-page.inc.php", "x-page.php", ".DS_Store");
        while (false !== ($file = readdir($dir))) {
            if (!is_dir($file) && !in_array($file, $skMod)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if ($ext == "php") {
                    $name = str_replace(array("x-", ".php"), "", $file);
                    $arr[$name] = $file;
                }
            }
        }
    }
    return $arr;
}

if (isset($_POST["xAction"])) {
    require_once("../../../core/core.inc.php");
    $MXRES = mxCheckRequest();
    if ($MXRES["err"] == 0) {
        switch ($_POST["xAction"]) {
            case "ADD":
                addPage();
                break;
            case "UPDATE":
                updatePage();
                break;
            case 'getAutocomplete':
                echo getAutocomplete();
                exit;
                break;
            case "mxDelFile":
                $param = array("dir" => "page", "tbl" => "page", "pk" => "pageID");
                mxDelFile(array_merge($_REQUEST, $param));
                break;
        }
    }
    echo json_encode($MXRES);
} else {

    if (function_exists("setModVars")) setModVars(array("TBL" => "page", "PK" => "pageID", "UDIR" => array("pageImage" => "page")));
}

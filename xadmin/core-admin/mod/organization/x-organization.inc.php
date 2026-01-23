<?php
$ORGID = 0;
$PARENTID = 0;
function addOrg()
{
    global $DB, $ORGID;
    $_POST["seoUri"] = makeSeoUri($_POST["orgName"]);
    $_POST["orgImage"] = mxGetFileName("orgImage");
    $DB->table = $DB->pre . "x_organization";
    $DB->data = $_POST;
    if ($DB->dbInsert()) {
        $ORGID = $orgID = $DB->insertID;
        setResponse(["err"=>0,"param"=>"id=$orgID"]);
    } else {
        setResponse(["err"=>1]);
    }
}

function updateOrg()
{
    global $DB, $ORGID;
    //$_POST["seoUri"] = makeSeoUri($_POST["orgName"]);
    $_POST["orgImage"] = mxGetFileName("orgImage");
    $orgID = intval($_POST["orgID"]);

    $DB->table = $DB->pre . "x_organization";
    $DB->data = $_POST;
    if ($DB->dbUpdate("orgID='$orgID'")) {
        $ORGID = $orgID;
        setResponse(["err"=>0,"param"=>"id=$orgID"]);
    } else {
        setResponse(["err"=>1]);
    }
}

if (isset($_POST["xAction"])) {
    require_once("../../../../core/core.inc.php");
    if (isset($MXSET["MULTIORG"]) && $MXSET["MULTIORG"] == 1) {
        
        $MXRES = mxCheckRequest();
        if ($MXRES["err"] == 0) {
            switch ($_POST["xAction"]) {
                case "ADD":
                    addOrg();
                    break;
                case "UPDATE":
                    updateOrg();
                    break;
                case "mxDelFile":
                    if ($_REQUEST['fld'] == "orgImage") {
                        $param = array("dir" => "x_organization", "tbl" => "x_organization", "pk" => "orgID");
                        mxDelFile(array_merge($_REQUEST, $param));
                    }
                    break;
            }
        }
        $orgSubMod = "";
        if (isset($MXSET["ORGPARENTMOD"]) && $MXSET["ORGPARENTMOD"] != "" && $PARENTID == 0) {
            $orgSubMod = $MXSET["ORGPARENTMOD"];
        }
        if (isset($MXSET["ORGCHILDMOD"]) && $MXSET["ORGCHILDMOD"] != "" && $PARENTID > 0) {
            $orgSubMod = $MXSET["ORGCHILDMOD"];
        }
        if ($orgSubMod !== "")
            require(ADMINPATH . "/mod/$orgSubMod/x-$orgSubMod.inc.php");
            
        echo json_encode($MXRES);
    }
} else {
    if (isset($MXSET["MULTIORG"]) && $MXSET["MULTIORG"] == 1) {
        $orgSubMod = "";

        if (isset($MXSET["ORGPARENTMOD"]) && $MXSET["ORGPARENTMOD"] != "" && $PARENTID == 0) {
            $orgSubMod = $MXSET["ORGPARENTMOD"];
        }
        if (isset($MXSET["ORGCHILDMOD"]) && $MXSET["ORGCHILDMOD"] != "" && $PARENTID > 0) {
            $orgSubMod = $MXSET["ORGCHILDMOD"];
        }
        if ($orgSubMod !== "")
            require(ADMINPATH . "/mod/$orgSubMod/x-$orgSubMod.inc.php");

        if (function_exists("setModVars")) setModVars(array("TBL" => "x_organization", "PK" => "orgID", "UDIR" => array("orgImage" => "organization")));
    }
}

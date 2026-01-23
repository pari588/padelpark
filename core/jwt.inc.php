<?php
require_once((__DIR__) . "/../lib/jwt/vendor/autoload.php");

use \Firebase\JWT\JWT;

\Firebase\JWT\JWT::$leeway = 10;

function mxGetAuthorizationHeader()
{
    $headers = "";
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else  if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

function mxGenerateJwtToken()
{
    global $MXSET;
    $TOKENID = 0;
    $TOKENAGE = 10;

    if (isset($_SESSION[SITEURL]) && isset($_SESSION[SITEURL][$MXSET["TOKENID"]]) && isset($MXSET["TOKENID"]) && $MXSET["TOKENID"] != "")
        $TOKENID = $_SESSION[SITEURL][$MXSET["TOKENID"]];

    if (isset($MXSET["TOKENAGE"]) && $MXSET["TOKENAGE"] != "")
        $TOKENAGE = $MXSET["TOKENAGE"];

    $issuer_claim = "THE_ISSUER"; // this can be the servername
    $audience_claim = "THE_AUDIENCE";
    $issuedat_claim = time(); // issued at
    $notbefore_claim = $issuedat_claim + 10; //not before in seconds
    $expire_claim = $issuedat_claim + $TOKENAGE; // expire time in seconds
    $token = array(
        "iss" => $issuer_claim,
        "aud" => $audience_claim,
        "iat" => $issuedat_claim,
        "nbf" => $notbefore_claim,
        "exp" => $expire_claim,
        "data" => $TOKENID
    );
    $token = JWT::encode($token, $MXSET["TOKENSECRET"]);
    return $token;
}

function mxValidateJwtToken()
{
    global $MXSET, $MXRES;
    $MXRES = array("err" => 0, "msg" => "Sorry! Cannot complete the request", "data" => array());
    $isValid = false;
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    //Get bearer token from header 
    $bearerToken = "";
    $authHeader = mxGetAuthorizationHeader();
    $headerArr = explode(" ", $authHeader);
    if (isset($headerArr[1]))
        $bearerToken = $headerArr[1];

    if ($bearerToken) {
        try {
            $token = JWT::decode($bearerToken, $MXSET["TOKENSECRET"], array('HS256'));
            $isValid = true;
        } catch (\Exception $e) {
            $MXRES["err"] = strtolower($e->getMessage()) === 'expired token' ? 401 : 1;
            $MXRES["msg"] = $e->getMessage();
            $MXRES["validtoken"] = 0;
            $isValid = false;
        }
    } else {
        $MXRES["err"] = 400;
        $MXRES["msg"] = 'No token found';
        $MXRES["validtoken"] = 0;
        $isValid = false;
    }
    return $isValid;
}

if (isset($_POST["xAction"]) && ($_POST['xAction'] == 'mxGenerateJwtToken' || $_POST['xAction'] == 'mxValidateJwtToken')) {
    $xAction = $_POST["xAction"];
    require_once("core.inc.php");
    switch ($xAction) {
        case 'mxGenerateJwtToken':
            $MXRES = mxCheckRequest(false, true);
            if ($MXRES["err"] == 0) {
                $MXRES["mxtoken"] = mxGenerateJwtToken();
                $MXRES["err"] = 0;
                $MXRES["msg"] = "New token generated";
            }
            break;
        case 'mxValidateJwtToken':
            $MXRES = mxCheckRequest(false, false);
            if ($MXRES["err"] == 0) {
                $MXRES["isValid"] = mxValidateJwtToken();
                $MXRES["err"] = 0;
            }
            break;
    }
    echo json_encode($MXRES);
}

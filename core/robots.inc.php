<?php
require_once("core.inc.php");
header("Content-Type:text/plain");
if(MXCON == "LIVE"){
    echo $MXSET["ROBOTSL"];
} else {
    echo $MXSET["ROBOTSD"];
}
?>
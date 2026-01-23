<?php
$MXTOTREC = 0;
$MXPGINFO["PK"] = "userLeavesID";
$MXPGINFO["TBL"] = "user_leaves";

function shortEmpName($displayName = ""){
	list($f,$m,$l) = explode(" ",$displayName);
	if(!isset($l)) { $l = $m; }
	$displayName = $f." ".$l;
	return $displayName;
}
?>
<?php
require_once("../config.inc.php");
$const = get_defined_constants(true);
if ($const['user']) {
    $arrSkipC = array("SITEPATH", "COREPATH", "ADMINPATH", "UPLOADPATH", "MXCON", "ROOTPATH", "LIBPATH");
    foreach ($const['user'] as $k => $v) {
        if (!in_array($k, $arrSkipC))
            echo "\nvar $k = '$v';";
    }

    foreach ($_GET as $k => $v) {
        if (filter_var($v, FILTER_VALIDATE_URL))
            if (strpos($v, SITEURL) === FALSE)
                continue;

        $v = str_replace("\n", " ", htmlspecialchars($v));
        echo "\nvar $k = '$v';";
    }
}

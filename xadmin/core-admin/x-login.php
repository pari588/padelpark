<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0,user-scalable=0, shrink-to-fit=no">
    <title>
        <?php echo $MXSET["PAGETITLE"] . '>' . $TPL->tplTitle; ?>
    </title>
    <link href="<?php echo UPLOADURL; ?>/setting/<?php echo $MXSET['FAVICON']; ?>" rel="SHORTCUT ICON" type="images/icon" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/font-awesome.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/style.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/theme.css'); ?>" />
    <link id="theme-css" rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/color.css.php?color='.$MXSET["COLOR".strtoupper($MXSET["THEME"])]); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/login.css'); ?>" />
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/config.js.php', getJsVars()); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(LIBURL . '/js/jquery-3.3.1.min.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/dialog.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/common.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/validate.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/form.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/js/common.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/js/login.inc.js'); ?>"></script>
</head>

<body class="<?php echo $MXSET["THEME"]; ?> <?php echo $MXSET["FONT"]; ?>">
    <span class="mxcon <?php echo strtolower($MXDBPRE); ?>" style="position: absolute; right:5px; top:0px;"></span>
    <?php
    if (!isset($_REQUEST["redirect"]))
        $_REQUEST["redirect"] = "";

    if (isset($_SESSION[SITEURL]["locked"])) {
        $difference = time() - $_SESSION[SITEURL]["locked"];
        if ($difference > 30) {
            unset($_SESSION[SITEURL]["locked"]);
            unset($_SESSION[SITEURL]["login_attempts"]);
        }
    }

    $loginAttrp = $loginAttr = "";
    if (isset($_SESSION[SITEURL]["login_attempts"]) && $_SESSION[SITEURL]["login_attempts"] > 2) {
        $loginAttrp = " class='login-limit-exceed' ";
        $loginAttr = " readonly='readonly'";
    }
    $arrForm = array(
        array("type" => "text", "name" => "userName", "value" => "", "title" => "Login Name", "validate" => "required", "attrp" => $loginAttrp, "attr" => ' autofill=false  autocomplete="new-username" ' . $loginAttr),
        array("type" => "password", "name" => "userPass", "value" => "", "title" => "Login Password", "validate" => "required", "attrp" => $loginAttrp, "attr" => ' autofill=false  autocomplete="new-password" ' . $loginAttr)
    );
    $MXFRM = new mxForm();
    $MXFRM->xAction = "xLogin"
    ?>
    <div class="login-container">
        <div class="login-box">
            <div class="welcome"><img src="<?php echo UPLOADURL; ?>/setting/<?php echo $MXSET["THEME"] . "logo.png"; ?>" /></div>
            <div class="login-wrap">
                <form name="frmLogin" id="frmLogin" method="post" action="" autocomplete="off">
                    <ul id="wrap-login">
                        <?php echo $MXFRM->getForm($arrForm); ?>
                        <!-- START : CODE ADDED BY AATIF SHAIKH ON 21-07-2021 FOR USER LOGIN FAILED for 3 TIMES -->
                        <?php
                        if (isset($_SESSION[SITEURL]["login_attempts"]) && $_SESSION[SITEURL]["login_attempts"] > 2) {
                            //$time = 30 - $difference;
                            echo "<li>Please wait for 30 seconds.</li>";
                        } else {
                        ?>
                            <li>
                                <input type="submit" name="btnLogin" id="btnLogin" class="btn" value="Login" rel="frmLogin" />
                                <input type="hidden" name="redirect" id="redirectMe" value="<?php echo htmlspecialchars(strip_tags($_REQUEST["redirect"])); ?>" />
                            </li>
                        <?php } ?>
                        <!-- END : CODE ADDED BY AATIF SHAIKH ON 21-07-2021 FOR USER LOGIN FAILED for 3 TIMES -->
                    </ul>
                    <?php echo $MXFRM->closeForm(); ?>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
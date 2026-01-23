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
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/inside.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/theme.css'); ?>" />
    <!-- <link id="theme-css" rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/color.css.php?color=' . $MXSET["COLOR" . strtoupper($MXSET["THEME"])]); ?>" /> -->
    <link id="theme-css" rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/color.css.php', array("color" => $MXSET["COLOR" . strtoupper($MXSET["THEME"])])); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/inc/site.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/device.css'); ?>" />
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/config.js.php', getJsVars()); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(LIBURL . '/js/jquery-3.3.1.min.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/dialog.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/common.inc.js'); ?>"></script>
    <?php if ($TPL->pageType == "list" || $TPL->pageType == "trash") { ?>
        <link href="<?php echo mxGetUrl(LIBURL . '/js/magnific-popup/magnific-popup.css'); ?>" rel="stylesheet">
        <script src="<?php echo mxGetUrl(LIBURL . '/js/magnific-popup/jquery.magnific-popup.min.js'); ?>" type="text/javascript"></script>
        <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/js/list.inc.js'); ?>"></script>
    <?php } else { ?>
        <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/validate.inc.js'); ?>"></script>
    <?php } ?>
    <?php if ($TPL->pageType == "list" &&  $TPL->modName == "employee-leave") { ?>
        <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/validate.inc.js'); ?>"></script>
    <?php } ?>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/form.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/js/common.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/core-admin/js/inside.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(ADMINURL . '/inc/js/site.inc.js'); ?>"></script>
    <?php
    if (function_exists("setLogoCss")) {
        echo setLogoCss();
    }
    ?>
</head>

<body class="<?php echo $MXSET["THEME"]; ?> <?php echo $MXSET["FONT"]; ?>">
    <?php if ($TPL->pageType == "list" || $TPL->pageType == "trash") { ?>
        <input type="hidden" name="modName" id="modName" value="<?php echo $TPL->modName; ?>" />
        <input type="hidden" name="pageType" id="pageType" value="<?php echo $TPL->pageType; ?>" />
    <?php } ?>
    <div class="header">
        <a class="fa-th fa" href="#"></a><label>Welcome : <?php echo $_SESSION[SITEURL]["MXNAME"]; ?></label><span class="mxcon <?php echo strtolower($MXDBPRE); ?>"></span>
        <a href="#" class="hamburger"><span></span><span></span><span></span></a>
        <a class="logo" href="<?php echo ADMINURL . '/' . $TPL->tplDefault; ?>/" title="<?php echo $TPL->tplTitle; ?>"></a>
        <div class="core-nav"> <a href="#" class="del"></a>
            <ul>
                <li><a class="fa-lock" href="<?php echo ADMINURL; ?>/?xAction=xLogout">Logout</a></li>
                <?php echo getAdminMenu(); ?>
                <li class="theme"><?php echo mxgetThemes(); ?><span> THEME:</span></li>
                <li class="font"><?php echo mxgetFonts(); ?><span> FONT:</span></li>
            </ul>
        </div>
    </div>
    <div class="wrapper">
        <div class="wrap-left">
            <ul class="main-nav">
                <?php echo getAdminSMenu(); ?>
            </ul>
        </div>
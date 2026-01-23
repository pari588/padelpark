<?php
require_once("../../../core/core.inc.php");
$dirPath = "/images";
if(!file_exists(UPLOADPATH . $dirPath)){
    echo "<h1>Missing directory: ".UPLOADPATH . $dirPath."</h1><h2>Please create diretory named 'images'</h2>"; exit;
}

require_once("filebrowser.inc.php");


$dirFiles = scanpath($dirPath);
$TOTREC = count($dirFiles);
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>MAXDIGI FILE BROWSER</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/font-awesome.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/style.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/inside.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/theme.css'); ?>" />
    <link id="theme-css" rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(ADMINURL . '/css/color.css.php?color=' . $MXSET["COLOR" . strtoupper($MXSET["THEME"])]); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(COREURL . '/js/filebrowser/pagination.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(COREURL . '/js/filebrowser/filebrowser.css'); ?>" />
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/config.js.php', $MXSETTINGSJ); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(LIBURL . '/js/jquery-3.3.1.min.js'); ?>"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo mxGetUrl(LIBURL . '/js/jquery-ui/jquery-ui.min.css'); ?>" />
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(LIBURL . '/js/jquery-ui/jquery-ui.min.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(LIBURL . '/js/file-upload/jquery.fileupload.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(LIBURL . '/js/file-upload/jquery.iframe-transport.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/common.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/dialog.inc.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/filebrowser/pagination.min.js'); ?>"></script>
    <script language="javascript" type="text/javascript" src="<?php echo mxGetUrl(COREURL . '/js/filebrowser/filebrowser.js'); ?>"></script>
    <script>
        var TOTREC = <?php echo $TOTREC; ?>;
        var DIRPATH = '<?php echo $dirPath; ?>';
        <?php if ($TOTREC > 0) { ?>
            ITEMS = $.parseJSON('<?php echo json_encode($dirFiles); ?>');
        <?php } ?>
    </script>
</head>

<body class="<?php echo $_SESSION[SITEURL]["THEME"]; ?>">
    <div class="wrapper">
        <div class="wrap-left">
            <ul class="main-nav"></ul>
        </div>
        <div class="wrap-right">
            <div class="page-nav" id="page-nav">
                <div class="nav-right" id="nav-right"></div>
                <div id="nav-left" class="nav-left">
                    <div class="fl-action">
                        <i class="chk">All<input type="checkbox" class="chkAll" title="Select All"><em></em></i>
                        <a href="#" class="fa-trash-o btn trash action" rel="trash"></a>
                        <a href="" class="fa-add btn" title="Add"> UPLOAD</a>

                    </div>
                    <div class="fl-search">
                        <a class="sort" id="sortname" href="#" sortBy='n' sort="ASC">BY NAME</a>
                        <a class="sort" id="sortsize" href="#" sortBy='s' sort="ASC">BY SIZE</a>
                        <input type="text" name="showRec" id="showRec" title="SHOW FILES" value="20" style="float:left; width:38px;" />
                        <input type="text" name="fileName" id="fileName" value="" placeholder="Enter file name" style="float:left; width:120px" />
                    </div>
                </div>
            </div>
            <div class="wrap-data column">
                <div class="upload-wrap">
                    <div class="wrap-form f70">
                        <form>
                            <input type="hidden" name="xAction" id="xAction" value="uploadFile" />
                            <input type="hidden" name="dirPath" id="dirPath" value="<?php echo $dirPath; ?>" />
                            <div class="mx-file-upload" xtype="file" params="">
                                <input type="button" class="btn" name="btnUploadFile" id="btnUploadFile" value="UPLOAD" />
                                <div class="drop"><a>CLICK<br /> TO <br /> BROWSE</a><input type="file" name="" multiple /></div>
                                <ul>
                                </ul>
                            </div>
                        </form>
                    </div>
                    <div class="wrap-form f30">
                        <div class="create-folder" xtype="file" params="">
                            <div class="dir-path"><?php echo $dirPath; ?>/</div>
                            <input type="text" name="dirName" id="dirName" value="" placeholder="Enter directory name" />
                            <input type="button" class="btn" name="btnCreateDir" id="btnCreateDir" value="CREATE" />
                        </div>
                    </div>
                </div>
                <div class="thumbnail-wrap">
                    <div class="wrap-form">
                        <ul class="item-list"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
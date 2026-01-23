<?php
include("../../config.inc.php");
$color = "#e64446";
if(isset($_GET["color"]) && $_GET["color"] !== ""){
    $color = "#".trim($_GET["color"]);
}
header("Content-Type: text/css; charset=utf-8"); 
?> 
/*TEXT COLOR==================================================*/

a:hover{color:<?php echo $color; ?>;}
.mxcon.dev_, .mxcon.demo_{border: 1px solid <?php echo $color; ?>;}

.btn:hover, .btn.l:hover{background:<?php echo $color; ?>;}
.button:hover, .button.l:hover{background:<?php echo $color; ?> !important;}
/*----------*/
h1.pg-ttl,
ul.main-nav li ul li:hover a,
ul.main-nav li ul li.active a,
ul.main-nav li ul li.active a.add,
div.mxdialog h2,
.tbl-list .btn.ico:hover,
.tbl-list .btn.ico.active{color:<?php echo $color; ?> !important;}

/*BACKGROUND COLOR==================================================*/

a.del:hover,a.add:hover{background:<?php echo $color; ?> !important;}
a.del.rs{background:<?php echo $color; ?>; }
/*----------*/
.spinner div{ color:<?php echo $color; ?>;}
div.progress{ background:<?php echo $color; ?>;}
div.progress span{ background-color:<?php echo $color; ?>;}
/*----------*/

/*BACKGROUND COLOR==================================================*/
div.nav-left:after,
ul.main-nav li ul li:hover a.add:before,
ul.main-nav li ul li:hover a.add:after,
ul.main-nav li ul li.active a.add:before,
ul.main-nav li ul li.active a.add:after,
ul.main-nav li:hover > a.add,
ul.main-nav li.active > a.add,
ul.main-nav li:hover > a.down-arrow,
ul.main-nav li.active > a.down-arrow,
div.mxpaging a:hover,
div.mx-file-upload div.drop:hover,
div.veiw-edit div.ve-wrap a.view:hover,
div.veiw-edit div a.edit:hover,
div.veiw-edit div.ve-wrap a:hover,
i.rdo input:checked ~ em,
i.chk input:checked ~ em,
div.core-nav li.theme a:hover,
div.core-nav li.font a:hover,
div.core-nav li.theme a.active,
div.core-nav li.font a.active{background-color:<?php echo $color; ?> !important;}

/*----------*/
div#mxmsg{color:<?php echo $color; ?>;}
/*----------*/

body .ui-state-active,
body .ui-widget-content .ui-state-active,
body .ui-widget-header .ui-state-active,
body a.ui-button:active,
body .ui-button:active,
body .ui-button.ui-state-active:hover{ border: 1px solid <?php echo $color; ?>;color:<?php echo $color; ?>;}
/*----------*/
body .ui-state-highlight,
body .ui-widget-content .ui-state-highlight,
body .ui-widget-header .ui-state-highlight{border: 1px solid <?php echo $color; ?>; background:<?php echo $color; ?>;}
body .ui-state-active,
body .ui-widget-content .ui-state-active,
body .ui-widget-header .ui-state-active{color:<?php echo $color; ?>;}

/*DIALOGUE DARK THEME=====================================================================*/
body.dark .cke_dialog_title{ color:<?php echo $color; ?>;}
body.dark a.cke_dialog_ui_button_ok:hover,
body.dark a.cke_dialog_ui_button:hover, body.dark .ui-datepicker .ui-datepicker-buttonpane button:hover{background:<?php echo $color; ?>!important;}

body.dark .ui-widget-content .ui-state-default.ui-state-active{color:<?php echo $color; ?>!important;border:1px solid <?php echo $color; ?>!important}
body.dark  .ui-widget-content .ui-state-default.ui-state-highlight{background:<?php echo $color; ?>!important;border:1px solid <?php echo $color; ?>!important}
ul.item-list i.chk input:checked ~ em{background-color:<?php echo $color; ?>;}
ul.item-list a.del{background:red;}
.paginationjs .paginationjs-go-button>input[type=button]:hover{border:1px solid <?php echo $color; ?>; background-color:<?php echo $color; ?>;}
.paginationjs .paginationjs-pages li:hover>a,
.paginationjs .paginationjs-pages li.active>a,
.paginationjs .paginationjs-pages li.disabled>a:hover{background-color:<?php echo $color; ?>;}
body.light .paginationjs .paginationjs-go-button>input:hover[type=button],
body.light .paginationjs .paginationjs-pages li>a:hover{background-color:<?php echo $color; ?>}
body.dark .upload-wrap div.mx-file-upload  div.drop a:hover{background-color:<?php echo $color; ?> !important;}

.upload-wrap div.mx-file-upload div.drop a:hover{background-color:<?php echo $color; ?> !important;}

.upload-wrap div.mx-file-upload div.drop a:hover{background-color:<?php echo $color; ?>;}
body.dark .upload-wrap div.mx-file-upload div.drop a:hover{background-color:<?php echo $color; ?>;}
body.dark .upload-wrap div.mx-file-upload div.drop a:hover {
    background-color: <?php echo $color; ?>;
}

body.dark div.mx-file-upload div.drop a:hover{background-color:<?php echo $color; ?>;}

.spinner div{ background-image:url(<?php echo SITEURL; ?>/uploads/setting/logo-m.png); }
body.moderate div.header a.logo{background-image:url(<?php echo SITEURL; ?>/uploads/setting/moderatelogo.png);}
body.light div.header a.logo{background-image:url(<?php echo SITEURL; ?>/uploads/setting/lightlogo.png);}
body.dark div.header a.logo{background-image:url(<?php echo SITEURL; ?>/uploads/setting/darklogo.png);}

/*-----FOR FILE BROWSER-----*/

div.mx-file-upload .btn{background-image:url(<?php echo ADMINURL; ?>/images/ico-upload-file.png);}
ul.item-list li[ext="xlsx"]{ background-image:url(<?php echo ADMINURL; ?>/images/ico-excel.png);}
ul.item-list li[ext="docx"]{ background-image:url(<?php echo ADMINURL; ?>/images/ico-doc.png);}
ul.item-list li[ext="pdf"]{ background-image:url(<?php echo ADMINURL; ?>/images/ico-pdf.png);}
ul.item-list li[ext="pptx"]{ background-image:url(<?php echo ADMINURL; ?>/images/ico-ppt.png);}
ul.item-list li[ext="csv"]{ background-image:url(<?php echo ADMINURL; ?>/images/ico-csv.png);}
ul.item-list li[ext="zip"]{ background-image:url(<?php echo ADMINURL; ?>/images/ico-zip.png);}
ul.item-list li[ext="rar"]{ background-image:url(<?php echo ADMINURL; ?>/images/ico-rar.png);}


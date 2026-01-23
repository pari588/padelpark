<?php
$MXFORCENAV = "";
$MXSET["TOKENID"] = "CSRF_TOKEN"; $MXDBLOG = true;
$MXTHEMES = array("D" => "dark", "M" => "moderate", "L" => "light");
$MXFONTS = array("L" => "large", "M" => "medium", "S" => "small");
$MXACCESS = array("view", "add", "edit", "delete", "trash", "restore");
$MXACTION = array("view" => array("view"), "add" => array("add"), "edit" => array("edit"), "trash" => array("view"), "list" => array("view"), "report" => array("view"), "pdf" => array("view"), "generate" => array("view"), "session" => array("view"), "approve" => array("view"));
$MXMACTION = array("list" => array("trash"), "trash" => array("restore", "delete"), "report" => array(), "pdf" => array(), "generate" => array(), "session" => array(), "approve" => array());
$MXPGFILE = array("view" => "add-edit", "add" => "add-edit", "edit" => "add-edit", "list" => "list", "trash" => "list", "report" => "report", "pdf" => "pdf", "generate" => "generate", "session" => "session", "approve" => "approve");
$MXPGMENU = array("view" => array("list", "trash", "add"), "add" => array("list", "trash"), "edit" => array("add", "list", "trash"), "list" => array("add", "trash"), "trash" => array("add", "list"), "report" => array("list", "trash"), "generate" => array("list"), "session" => array("list"), "approve" => array("list"));
$MXADMINMENU = array(
    '100000' => array("menuTitle" => "organization", "seoUri" => "organization", "dUri" => "organization-list", "class" => "fa-group"),
    '100001' => array("menuTitle" => "Admin Roles", "seoUri" => "admin-role", "dUri" => "admin-role-list", "class" => "fa-key"),
    '100002' => array("menuTitle" => "Admin Users", "seoUri" => "admin-user", "dUri" => "admin-user-list", "class" => "fa-user"),
    '100003' => array("menuTitle" => "Admin Menus", "seoUri" => "admin-menu", "dUri" => "admin-menu-list", "class" => "fa-navicon"),
    '100004' => array("menuTitle" => "Admin Settings", "seoUri" => "setting", "dUri" => "setting-edit", "class" => "fa-gear"),
    '100005' => array("menuTitle" => "DB Settings", "seoUri" => "db-setting", "dUri" => "db-setting-edit", "class" => "fa-gear"),
    '100006' => array("menuTitle" => "TPL & META Settings", "seoUri" => "template", "dUri" => "template-list", "class" => "fa-gear"),
    '100007' => array("menuTitle" => "Site Menu", "seoUri" => "menu", "dUri" => "menu-list", "class" => "fa-navicon"),
    '100008' => array("menuTitle" => "Optimize Uploads", "seoUri" => "optimize", "dUri" => "optimize-edit", "class" => "fa-archive"),
    '100009' => array("menuTitle" => "Language", "seoUri" => "language", "dUri" => "language-list", "class" => "fa-language")
);
$MXADMIN = array("user" => "xadmin", "pass" => "4acefbcacf55283b72fa8c522252e091");

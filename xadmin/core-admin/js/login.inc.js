$(document).ready(function () {
    var frm = $("form#frmLogin");
    frm.mxinitform({ button: "input#btnLogin", url: ADMINURL + "/core-admin/ajax.inc.php" });
    localStorage.removeItem(SITEURL);
    setTheme("undefined",true);
});

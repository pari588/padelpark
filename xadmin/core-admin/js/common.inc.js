function saveThemeLogin(theme) {
    var key = SITEURL + "/theme";
    var themeLS = localStorage.getItem(key);
    if (themeLS) {
        localStorage.removeItem(key)
    }
    localStorage.setItem(key, theme);
}

function saveFontLogin(fontSize) {
    var key = SITEURL + "/font";
    var fontLS = localStorage.getItem(key);
    if (fontLS) {
        localStorage.removeItem(key)
    }
    localStorage.setItem(key, fontSize);
}

function setTheme(cke, login) {
    $("body").removeAttr("class");
    if (login) {
        var themeLS = localStorage.getItem(SITEURL + "/theme");
        if (themeLS) {
            THEME = themeLS;
        }
        var fontLS = localStorage.getItem(SITEURL + "/font");
        if (fontLS) {
            FONT = fontLS;
        }
    } else {
        THEME = $("div.core-nav li.theme a.active").attr("title");
        FONT = $("div.core-nav li.font a.active").attr("title");
    }

    if (typeof THEME !== "undefined") {
        var hashColor = eval("COLOR" + THEME.toUpperCase());
        var cssUrl = ADMINURL + "/css/color.css.php?color=" + hashColor;
        $("link#theme-css").attr("href", cssUrl);

        $("body").addClass(THEME);
        $("body").addClass(FONT);

        if (typeof (cke) !== "undefined") {
            initEditors();
        }
    }
}
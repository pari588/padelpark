var MXTRASHPRE;
var MXTRASHPOST;

function mxtrash(params) {
    showMxLoader();
    // Changed from $.mxajax() to $.ajax() to prevent infinite retry loops
    $.ajax({
        type: "POST",
        dataType: "json",
        url: ADMINURL + "/core-admin/ajax.inc.php",
        data: params,
    }).then(function (result) {
        hideMxLoader();
        if (result.err == 0) {
            if (typeof MXTRASHPOST == "function") {
                MXTRASHPOST(result, params);
            }

            if ($("ul.dragsort-list").length > 0) {
                $("ul.dragsort-list input.list-item").each(function () {
                    if ($(this).is(":checked")) {
                        var wrap = $(this).closest("li.dragsort-item");
                        wrap.slideUp(function () {
                            wrap.remove();
                        })
                    }
                });
            } else {

                $("table.tbl-list tr:not(:first)").each(function () {
                    var el = $(this);
                    if (el.find("input:eq(0)").is(":checked")) {
                        el.children("td").each(function () {
                            $(this).wrapInner("<div/>").children("div").slideUp(function () {
                                el.remove();
                            })
                        });
                    }
                });
            }
        } else {
            // Show error message if trash/restore fails
            $.mxalert({ msg: result.msg || "Failed to perform action" });
        }
    }).catch(function(error) {
        hideMxLoader();
        $.mxalert({ msg: "An error occurred. Please try again." });
        console.error("Trash action failed:", error);
    });
}

$(document).ready(function () {
    $("div.header a.fa-th,div.core-nav a.del").click(function () {
        $("div.core-nav").toggleClass("active");
        return false;
    });

    var liA = $("ul.main-nav li.active");
    if (liA.length > 0) {
        $(".wrap-left").animate({
            scrollTop: liA.offset().top - ($(".page-nav").offset().top)
        }, 300);
    }

    $(document).on("click", function (event) {
        var $trigger = $("div.core-nav");
        if ($trigger !== event.target && !$trigger.has(event.target).length) {
            $("div.core-nav").removeClass("active");
        }
    });

    $('ul.main-nav li a.down-arrow').click(function () {
        $(this).parent().find("ul").slideToggle(250);
        return false;
    });

    $('ul.main-nav li li.active').closest("ul").slideToggle(250);

    if (PAGETYPE == "list" || PAGETYPE == "trash") {
        initListPage();
    } else {
        initFormSubmit();
    }

    $("div.header a.hamburger").click(function () {
        $("div.wrapper").toggleClass("active");
        return false;
    });

    var mxtimeout;
    $(document).mousemove(function (event) {
        if (event.pageX <= 30 && event.pageX >= 0) {
            if ($("div.wrapper").hasClass("active")) {
                if (mxtimeout)
                    clearTimeout(mxtimeout);
                mxtimeout = setTimeout(function () {
                    $("div.wrapper").removeClass("active");
                    clearTimeout(mxtimeout);
                }, 500);
            }
        } else {
            clearTimeout(mxtimeout);
        }
    });

    $("div.core-nav li.theme a").click(function () {
        var el = $(this);
        var type = $(this).attr("title");

        $.mxajax({
            url: ADMINURL + "/core-admin/ajax.inc.php",
            data: { xAction: "setTheme", type: type },
            type: 'post',
            dataType: "json"
        }).then(function (data) {
            $("div.core-nav li.theme a").removeClass("active");
            el.addClass("active");
            setTheme(1, false);
            saveThemeLogin(type);
        });
        return false;
    });

    $("div.core-nav li.font a").click(function () {
        var el = $(this);
        var type = $(this).attr("title");
        $.mxajax({
            url: ADMINURL + "/core-admin/ajax.inc.php",
            data: { xAction: "setFontSize", type: type },
            type: 'post',
            dataType: "json"
        }).then(function (data) {
            $("div.core-nav li.font a").removeClass("active");
            el.addClass("active");
            setTheme("undefined", false);
            saveFontLogin(type);
        });
        return false;
    });
});

$(window).resize(function () {
    clearTimeout($.data(this, 'timer'));
    $.data(this, 'timer', setTimeout(function () {
        setWindow();
    }, 300));
});
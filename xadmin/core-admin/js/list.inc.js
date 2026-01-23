function updateURL(currUrl, param, paramVal) {
    var url = currUrl
    var newAdditionalURL = "";
    var tempArray = url.split("?");
    var baseURL = tempArray[0];
    var aditionalURL = tempArray[1];
    var temp = "";
    if (aditionalURL) {
        var tempArray = aditionalURL.split("&");
        for (i = 0; i < tempArray.length; i++) {
            if (tempArray[i].split('=')[0] != param) {
                newAdditionalURL += temp + tempArray[i];
                temp = "&";
            }
        }
    }
    var rows_txt = temp + "" + param + "=" + paramVal;
    var finalURL = baseURL + "?" + newAdditionalURL + rows_txt;
    return finalURL;
}

function isSearched() {
    var flg = false;

    if ($("#frmSearch").find("input:text,select").filter(function () { return $(this).val(); }).length > 0) {
        flg = true;
    }
    if ($("#frmSearch input:radio:checked").length > 0) {
        flg = true;
    }
    if ($("#frmSearch input:radio:checked").length > 0) {
        flg = true;
    }
    return flg;
}

function initListPage() {
    initMagnific();

    $('div.mxpaging a.next,div.mxpaging a.prev').click(function () {
        var showRec = parseInt($("input#showRecP").val());
        if (showRec) {
            var newUrl = updateURL($(this).attr("href"), "showRec", showRec);
            window.location.href = newUrl;
        }
        return false;
    });

    $('div.mxpaging input#showRecP').change(function () {
        $("#frmSearch input#showRec").val($(this).val());
    });

    $('.chkAll').on("click", function () {
        var status = $(this).prop('checked');
        if (!status)
            status = false;
        $("table.tbl-list input.list-item,ul.dragsort-list input.list-item").each(function () {
            $(this).prop('checked', status);
        });

    });

    if ($("ul.dragsort-list").length > 0) {
        $("ul.dragsort-list").find('input.list-item').on("click", function () {
            var currEl = $(this);
            var flg = 0;
            $(this).parents(".dragsort-item").find('input.list-item').each(function () {
                if (!$(this).is(currEl)) {
                    if ($(this).is(":checked"))
                        flg++;
                } else {
                    return false;
                }
            });

            if (flg > 0)
                return false;

            var status = $(this).prop('checked');
            if (!status)
                status = false;

            $(this).closest(".dragsort-item").find('input.list-item').prop('checked', status);

            if (PAGETYPE == "trash")
                $(this).parents(".dragsort-item").find('input.list-item').prop('checked', status);
        });
    }

    $("div#nav-left a.action").on("click", function () {
        var action = $(this).attr('rel');
        var arrID = [];

        $("table.tbl-list input.list-item,ul.dragsort-list input.list-item").each(function () {
            var input = $(this);
            if (input.is(":checked")) {
                if (input.val())
                    arrID.push(input.val());
            }
        });
        if (arrID.length > 0) {
            var strID = arrID.join(",");
            var params = { xAction: action, id: strID, modName: $("input#modName").val() };
            if (typeof MXTRASHPRE == "function") {
                MXTRASHPRE(params);
            } else {
                mxtrash(params);
            }
        } else {
            $.mxalert({ msg: "Nothing selected to perform this action." });
        }
        return false;
    });

    //==================Serach=================
    $('div#nav-right a.search,div.search-data a.del').click(function () {
        d = "";
        var ulH = $("div.search-data ul").height();
        if (ulH > 33)
            d = " d";
        $("div.search-data,div.wrap-left,div.wrap-right").toggleClass("active" + d);

        return false;
    });

    if (isSearched()) {
        $('div#nav-right a.search').trigger("click");
    }

    $("#frmSearch #btnReset").click(function () {
        window.location = PAGEURL;
    });

    initAutoComplete($("#frmSearch"));

    $("input#showRecP").keypress(function (event) {
        if (event.which == 13) {
            event.preventDefault();
            $("#frmSearch input#showRec").val($("div.mxpaging input#showRecP").val());
            $("#btnSearch").trigger("click")
        }
    });

    $("div.nav-right a.print").click(function () {
        window.open(ADMINURL + '/core-admin/x-print.php?col=0,3', 'Print', 'width=1250,height=850,resizable=1,toolbar=0,menubar=1,scrollbars=1,status=1');
        return false;
    });

    $("a.fa-history.btn.ico").click(function () {
        showMxLoader();
        $.mxajax({
            url: ADMINURL + "/core-admin/ajax.inc.php",
            data: { xAction: "getModLog", pkValue: $(this).attr("rel"), modName: MODNAME },
            type: 'post',
            dataType: "json"
        }).then(function (resp) {
            if (resp.err == 0) {
                hideMxLoader();
                if (typeof resp.data !== "undefined" && $.trim(resp.data) !== "") {
                    $.mxalert({ msg: resp.data, title: "Log History", modal: true });
                }
            } else {
                hideMxLoader(resp.msg);
            }
        });
        return false;
    });

    var $exportpopup;
    $("div.nav-right a.export").click(function () {
        $exportpopup = $('div.export-popup').mxpopup();
        return false;
    });

    $("div.export-popup input#btnExport").click(function () {
        window.location = SITEURL + '/core/export.inc.php?' + $("form#frmExport").serialize();
        //if (typeof ($exportpopup) !== 'undefined')
        //$exportpopup.hidemxdialog();
        return false;
    });

    $(".only-numeric").on("keypress", function (e) {
        var keyCode = e.which ? e.which : e.keyCode

        if ((keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105)) {
            $("div.export-popup li.message p.e").text("");
        } else {
            $("div.export-popup li.message p.e").text("Only Number more than zero(0)");
            $(this).val($(this).attr("title"));
        }
    });

    $(".only-numeric").on("focusout", function (e) {
        var val = parseInt($.trim($(this).val()));
        if (val < 1) {
            $(this).val($(this).attr("title"));
        }
        if (val > $(this).attr("max")) {
            $(this).val($(this).attr("max"));
        }
    });

    $("table.tbl-list tr").hover(function () {
        $(this).find("div.veiw-edit div").fadeIn("fast");
    }, function () {
        $(this).find("div.veiw-edit div").fadeOut("fast");
    });



    var veWrap = $("div.veiw-edit:eq(0)");
    if (veWrap.length > 0) {
        var btnW = 28;
        var noLng = veWrap.find('div.ve-wrap div.edit a').length;
        if (noLng > 1) {
            $("div.veiw-edit div.ve-wrap").width(btnW * noLng);
        } else {
            $("div.veiw-edit").removeClass("lang");
            $("div.veiw-edit div.ve-wrap").width(btnW * 2);
        }
    }
}
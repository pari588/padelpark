$(document).ready(function (e) {

    $("a.fa-reset.d").click(function () {
        showMxLoader();
        $.mxajax({
            url: MODINCURL,
            type: "POST",
            data: {
                xAction: "restoreSettings"
            },
            dataType: "json",
        }).then(function (result) {
            hideMxLoader();
            window.location.reload();
        });
        return false;
    });

    $("a.fa-trash-o.c").on("click", function () {
        $mxpopupsetting = $.mxconfirm({
            top: "-20%",
            auto: false,
            time: 2000,
            title: "Warning",
            msg: "Are you sure you want delete all logs.<br><br>Deleted logs and cannot be recovered",
            buttons: {
                "ok": {
                    "action": function () {
                        $mxpopupsetting.hidemxdialog();
                        showMxLoader();
                        $.mxajax({
                            url: MODINCURL,
                            type: "POST",
                            data: {
                                xAction: "deleteLog"
                            },
                            dataType: "json",
                        }).then(function (result) {
                            hideMxLoader(result.msg);
                        });
                    }
                },
                "Cancel": {
                    "action": function () {
                        $mxpopupsetting.hidemxdialog();
                    }
                }
            }
        });
        return false;
    });

    $("a.fa-reset.j").click(function () {
        showMxLoader();
        $.mxajax({
            url: MODINCURL,
            type: "POST",
            data: {
                xAction: "resetJs"
            },
            dataType: "json",
        }).then(function (data) {
            hideMxLoader("JS Key updated...");
            if (typeof (data) !== undefined) {
                var json = $.parseJSON(data);
                if (typeof (json.data) !== "undefined") {
                    $("span.jskey").text(json.data.jskey)
                    $("input#JSKEY").val(json.data.jskey);
                }
            }
        });
        return false;
    });

    $(".txt-color").on("change keyup focusout", function () {
        var color = $(this).val();
        if ($.trim(color) != "") {
            $(this).parent().find(".show-color").css('background-color', '#' + color);
        }
    })
    $(".txt-color").trigger("change");

    $("input.ignore-all").on("click", function () {
        var status = $(this).prop('checked');
        $("ul.tbl-ignore").find("input:checkbox").prop('checked', status);
    });

    $("a.fa-reset.o").on("click", function () {
        showMxLoader();
        $.mxajax({
            url: MODINCURL,
            type: "POST",
            data: {
                xAction: "optimizeLog"
            },
            dataType: "json",
        }).then(function (result) {
            hideMxLoader(result.msg);
        });
        return false;
    });
});
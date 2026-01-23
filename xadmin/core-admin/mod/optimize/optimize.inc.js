function displayErrMsg(ptd, msg) {
    if (typeof ptd != "undefined" && ptd.length > 0 && msg != "") {
        ptd.find("p.e").remove();
        ptd.addClass('err');
        ptd.append('<p class="e">' + msg + '</p>');
    }
}

function validateSelection(tr) {
    var vali = true;
    if (typeof tr != "undefined") {
        var ddTable = tr.find("select.tableName");
        var msg = ""; var ptd;
        if (typeof ddTable.val() == "undefined" || ddTable.val() == "") {
            msg = "Please select table";
            ptd = ddTable.closest("td");
            vali = false;
        }
        displayErrMsg(ptd, msg);
        var checked = tr.find(":checkbox:checked");
        if (checked.length < 1) {
            msg = "Please select table file fields";
            ptd = tr.find("ul.table-fields").closest("td");
            vali = false;
        }
        displayErrMsg(ptd, msg);
    }
    return vali;
}

function calculateTotal() {
    var dbCountT = 0;
    $("span.dbCount").each(function () {
        val = $(this).text();
        if (!isNaN(val)) {
            dbCountT = dbCountT + Number(val);
        }
    });
    $("th.dbCountT").text(dbCountT);

    var dirCountT = 0;
    $("td.dirCount").each(function () {
        var val = $(this).text();
        if (!isNaN(val)) {
            dirCountT = dirCountT + Number(val);
        }
    });
    $("th.dirCountT").text(dirCountT);

    var dirSizeT = 0;
    $("td.dirSize").each(function () {
        var val = $(this).attr("title");
        if (!isNaN(val)) {
            dirSizeT = dirSizeT + Number(val);
        }
    });
    $("th.dirSizeT").text(formatFileSize(dirSizeT));
}

function setDbCount(tr, dbCount) {
    var dirCount = tr.find("td.dirCount").text();
    dbCountObj = tr.find("span.dbCount");
    var color = "";
    if (dirCount != dbCount) {
        color = "red";
    }
    dbCountObj.css("color", color);
    dbCountObj.text(dbCount);
    calculateTotal();
}

$(document).ready(function () {
    calculateTotal();
    $('input.btn.refresh').click(function () {
        var tr = $(this).closest("tr.row");
        tr.find("p.e").remove();
        if (validateSelection(tr)) {
            var frm = tr.find("form");
            frm.find("input#xAction").val("getFilesInTable");
            showMxLoader();
            $.mxajax({
                url: MODINCURL,
                type: "POST",
                data: frm.serialize(),
                dataType: "json",
            }).then(function (resp) {
                if (resp.err == 0) {
                    hideMxLoader("TOTAL FILES IN DB: " + resp.data.count);
                    setDbCount(tr, resp.data.count);
                } else {
                    hideMxLoader(resp.msg);
                }
            });
        }
        return false;
    });

    $('input.optimize-it').click(function () {
        var tr = $(this).closest("tr.row");
        if (validateSelection(tr)) {
            var frm = tr.find("form");
            frm.find("input#xAction").val("optimizeFolder");
            $confirmbox = $.mxconfirm({
                top: "20%",
                msg: "Are you sure, you want to proceed",
                buttons: {
                    "Yes": {
                        "action": function () {
                            $confirmbox.hidemxdialog();
                            showMxLoader();
                            $.mxajax({
                                url: MODINCURL,
                                type: "POST",
                                data: frm.serialize(),
                                dataType: "json",
                            }).then(function (resp) {
                                hideMxLoader(resp.msg);
                                if (resp.err == 0) {
                                    $.mxalert({ msg: resp.msg });
                                    setDbCount(tr, resp.data.dbCount);
                                    tr.find("td.dirCount").text(resp.data.dirCount);
                                    tr.find("td.dirSize").attr("title", resp.data.dirSize)
                                    tr.find("td.dirSize").text(formatFileSize(resp.data.dirSize));
                                    calculateTotal();
                                }
                            });
                        }
                    },
                    "Cancel": {
                        "action": function () {
                            $confirmbox.hidemxdialog();
                            return false;
                        }
                    }
                }
            });
        }
        return false;
    });

    $('select.tableName').change(function () {
        var dd = $(this);
        var tr = dd.closest("tr.row");
        if (dd.val()) {
            showMxLoader();
           

            $.mxajax({
                url: MODINCURL,
                type: "POST",
                data: {
                    xAction: "getTableFieldList",
                    tableName: dd.val()
                },
                dataType: "json",
            }).then(function (result) {
                hideMxLoader();
                    if (typeof result.data != 'undefined') {
                        tr.find("ul.table-fields").html(result.data.html);
                        setDbCount(tr, result.data.count.count);
                    }
            });

        } else {
            tr.find("ul.table-fields").html('');
            setDbCount(tr, 0);
        }
    });

    $('a.btn.del-tmp').click(function () {
        showMxLoader();
        

        $.mxajax({
            url: MODINCURL,
            type: "POST",
            data: {
                xAction: "delTmpDir",
            },
            dataType: "json",
        }).then(function (result) {
            hideMxLoader(result.msg);
        });
        return false;
    });
});
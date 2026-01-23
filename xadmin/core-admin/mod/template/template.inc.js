function validateTpl() {
    var reqSet = false;
    $("table.tbl-list tbody tr").each(function () {
        var modType = $(this).find("select#modType").val();
        if ($(this).find("input#metaKey").val() != "") {
            if (modType == 0) {
                if ($(this).find("select#tblMaster").val() != "" && $(this).find("select#pkMaster").val() != "") {
                    reqSet = true;
                }
            } else {
                if ($(this).find("input.metaTitle").val() != "") {
                    reqSet = true;
                }
            }
        }
    });
    return reqSet;
}

$(document).ready(function () {
    $("div.nav-right a.fa-save").click(function () {
        if (validateTpl()) {
            showMxLoader();
            $.mxajax({
                url: MODINCURL,
                type: "POST",
                data: $("form#frmAddEdit").serialize(),
                dataType: "json",
            }).then(function (resp) {
                hideMxLoader(resp.msg);
            });
        } else {
            $.mxalert({ msg: "Nothing to add, please select something" });
        }
        return false;
    });

    $("select#modType").change(function () {
        var val = $(this).val();
        var parent = $(this).closest("tr");
        $("div.dynamic,div.static").slideUp();
        if (val == 0) {
            $("div.dynamic").slideDown();
            $("input#metaKey").removeAttr("readonly");
        } else {
            $("div.static").slideDown();
            $("input#metaKey").attr("readonly", "readonly");
            $("input#metaKey").val($("#modSeoUri").val());
        }
    });
    $("select#modType").trigger("change");

    $("select#tblMaster").change(function () {
        var table = $(this).val();
        if (table != "") {
            $.mxajax({
                type: "POST",
                url: MODINCURL,
                data: { xAction: "mxGetTableFlds", table: table },
                dataType: "json",                
            }).then(function (result) {
                if (typeof result.data != 'undefined') {
                    $("select#pkMaster,select#tplFileCol,select#titleMaster").each(function () {
                        $(this).html('<option value="">--' + $(this).attr("title") + '--</option>' + result.data);
                    });
                }
            });              
           
        } else {
            $("select#pkMaster,select#tplFileCol,select#titleMaster").each(function () {
                $(this).html('<option value="">--' + $(this).attr("title") + '--</option>');
            });
        }
    });

    $("select#tblDetail").change(function () {
        var table = $(this).val();
        var parent = $(this).closest("ul.tbl-form");
        if (table != "") {
            var mval = $("input#metaKeyD").val();
            if ($.trim(mval) == "") {
                $("input#metaKeyD").val(table);
            }
             $.mxajax({
                type: "POST",
                url: MODINCURL,
                data: { xAction: "mxGetTableFlds", table: table },
                dataType: "json",             
            }).then(function (result) {
                if (typeof result.data != 'undefined') {
                    $("select#pkDetail,select#titleDetail").each(function () {
                        $(this).html('<option value="">--' + $(this).attr("title") + '--</option>' + result.data);
                    });
                }
            });    

        } else {
            $("select#pkDetail,select#titleDetail").each(function () {
                $(this).html('<option value="">--' + $(this).attr("title") + '--</option>');
            });
        }
    });

    $("ul.toggle h3").click(function(){
        $(this).parent().find("div.mod-items").slideToggle();
        return false;
    });

});
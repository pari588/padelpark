function initDragSort(levels, callback) {
    $('.sortable').nestedSortable({
        forcePlaceholderSize: true,
        items: 'li',
        handle: 'div.drag',
        placeholder: 'sort-highlight',
        listType: 'ul',
        maxLevels: levels,
        opacity: .6,
        update: function () {
            serialized = $('ul.sortable').nestedSortable('serialize');
            $.mxajax({
                url: ADMINURL + "/core-admin/inc/dragsort/dragsort.inc.php",
                data: serialized + "&xAction=updateSortOrder&modName=" + MODNAME,
                type: "POST",
                dataType: 'json'
            }).then(function (data) {
                if (typeof callback !== "undefined") {
                    var callbackF = eval(callback);
                    if (typeof callbackF == "function") {
                        callbackF(data);
                    }
                }
            });
        }
    });
}

$("div#nav-right a.action").click(function () {
    var action = $(this).attr('rel');
    var arrID = [];
    $("table.tbl-list tbody tr").each(function () {
        var input = $(this).find("input:eq(0)");
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
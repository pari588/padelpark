$.fn.mxautocomplete = function () {
    var el = $(this[0]);
    if (typeof (el) !== "undefined" && el.length) {
        var params = arguments[0] || {};
        var d = {
            frmID: el.closest("form"),
            url: MODINCURL,
            xAction: "getAutocomplete",
            tag: false,
            tagwrap: "",
            full: false,
            send: ""
        };
        var p = $.extend(d, params);

        el.autocomplete({
            source: function (request, response) {
                var arrSend = {};
                if (typeof p.send !== "undefined") {
                    var arrSendF = p.send.split(",");
                    $.each(arrSendF, function (i, fName) {
                        if (typeof fName !== "undefined" && fName != "") {
                            var val = $("#" + fName).val();
                            if (typeof val !== "undefined" && val !== "") {
                                arrSend[fName] = val;
                            }
                        }
                    });
                }
                $.mxajax({
                    type: "POST",
                    url: p.url,
                    dataType: "json",
                    data: {
                        searchString: request.term,
                        xAction: p.xAction,
                        send: JSON.stringify(arrSend)
                    }
                }).then(function (data) {
                    if (!data.length) {
                        var result = [{
                            label: '',
                            value: "No matches found"
                        }];
                        response(result);
                    } else {
                        response($.map(data, function (item) {
                            return {
                                label: item.label,
                                value: item.value,
                                data: item.data
                            }
                        }));
                    }
                });
            },
            select: function (event, ui) {
                if (typeof ui.item.data !== "undefined") {
                    mxRenderValuesAc(ui.item.data, el, p);
                }
                if (typeof p.callback !== "undefined") {
                    var callbackF = eval(p.callback);
                    if (typeof callbackF == "function") {
                        callbackF(ui.item.data, el, p);
                    }
                }
            },
            close: function (el) {
                if (p.tag === true)
                    el.target.value = '';
            },
            delay: 300,
            selectFirst: false,
            autoFocus: false,
            minLength: 1
        });
    }
}

function setAcVals(parent, fld, key, value) {
    var arrSetVal = ["input", "textarea", "select"];
    var tagName = fld.prop("tagName");
    if (fld.length > 0 && $.inArray(tagName.toLowerCase(), arrSetVal) !== -1) {
        if (fld.attr("xtype") == "editor") {
            var instance = CKEDITOR.instances[key];
            if (instance) {
                instance.setData(value)
            }
        } else {
            fld.val(value);
        }
    } else {
        var ul = parent.find("#" + key + "-set");
        if (ul.length > 0) {
            arrV = value.split(",");
            ul.find("input[type=checkbox],input[type=radio]").each(function () {
                if ($.inArray($(this).val(), arrV) !== -1) {
                    $(this).attr("checked", "checked");
                }
            });
        } else {
            fld.html(value);
        }
    }
}

function mxRenderValuesAc(data, el, p) {
    
    var parent, group = el.closest(".grp-set");
    if (group.length > 0 && (p.tagwrap == "" || p.tagwrap == "undefined")) {
        parent = el.closest("td");
        if (parent.length < 1)
            parent = el.closest("li");
    } else {
        if (typeof p.frmID == "string")
            parent = $("#" + p.frmID);
        else
            parent = $(p.frmID);
    }

    if (parent.length) {
        
        if (p.tag) {
            mxSetTagAc(data, el, p, parent);
        } else {
            $.each(data, function (key, value) {
                var fld = parent.find("#" + key);
                setAcVals(parent, fld, key, value);
            });
        }
    }
    el.val('');
}

function mxSetTagAc(data, el, p, parent) {
    var grpSet = parent.closest(".grp-set");
    if (typeof p.tagwrap !== "undefined" && p.tagwrap !== "") {
        if (grpSet.length > 0)
            var ul = grpSet.find("ul" + p.tagwrap);
        else
            var ul = $("ul" + p.tagwrap);
    } else {
        var ul = el.parent().find("ul.mx-tag-wrap");
    }

    if (ul.length > 0) {
        var li = '<li><a href="#" class="del rs" onclick="mxDelTagAc(this); return false;"></a>' + data + '</li>';
        ul.append(li);
        if (grpSet.length > 0) {
            mxGroupSetIndexAc(grpSet);
        }
    }
}

function mxSetTagIndex(tagWrap, index) {
    $(tagWrap).find("input,select,textarea").each(function (i, rc) {
        var name = $(rc).attr('name').split('[');
        $(rc).attr("name", name[0] + '[' + index + '][]');
    });
}

function mxDelTagAc(del) {
    if ($(del).length > 0) {
        var tagWrap = $(del).closest(".mx-tag-wrap");
        var index = tagWrap.closest(".grp-set").index();
        if (index >= 0)
            mxSetTagIndex(tagWrap, index);

        $(del).closest("li").remove();
    }
}
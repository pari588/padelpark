/*
 Extent js validation function
 $MXFUNCV["testfunc"] = function($value, element, param) { alert("HELLLO");};
 $FRM->errMsg["fieldName"] = "Sample message here";
 add in php code after initializing form class
 */
var $MXFUNCV, FLGERR;
$(document).ready(function (e) {
    $MXFUNCV = {
        required: function ($value, el) {
            return $value.length > 0;
        },
        checked: function ($value, el, param) {
            if (param)
                return el.find("input:checked").length >= param;
            else
                return el.find("input:checked").length > 0;
        },
        minlen: function (value, el, param) {
            return (parseFloat(value.length) >= parseFloat(param));
        },
        maxlen: function (value, el, param) {
            return (parseFloat(value.length) <= parseFloat(param) && parseFloat(value.length) > 0);
        },
        rangelen: function (value, el, param) {
            var range = param.split("~");
            return ((parseFloat(value.length) >= parseFloat(range[0])) && (parseFloat(value.length) <= parseFloat(range[1])));
        },
        min: function (value, el, param) {
            if (isNaN(value))
                return false;
            return (parseFloat(value) >= parseFloat(param));
        },
        max: function (value, el, param) {
            if (isNaN(value))
                return false;
            return (parseFloat(value) <= parseFloat(param));
        },
        range: function (value, el, param) {
            if (isNaN(value))
                return false;
            var range = param.split("~");
            return (parseFloat(value) >= parseFloat(range[0]) && parseFloat(value) <= parseFloat(range[1]));
        },
        alpha: function (value) {
            return /^[a-z ._\-]+$/i.test(value);
        },
        number: function (value) {
            return /^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/.test(value);
        },
        digits: function (value) {
            return /^\d+$/.test(value);
        },
        alphanum: function (value) {
            return /^[a-z\d ._\-]+$/i.test(value);
        },
        equalto: function (value, el, param) {
            return value === $("#" + param).val() && value !== "";
        },
        name: function (value, el) {
            return value.match(/^[a-z0-9 ]+$/i);
        },
        email: function (value) {
            return /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(value);
        },
        loginname: function (value, filename) {
            //FILEIMAGE
            return /^[A-Za-z0-9_]{4,100}$/.test(value);
        },
        password: function (value) {
            return /^[A-Za-z0-9!@#$%^&*()_]{5,100}$/.test(value);
        },
        date: function (value) {
            return !/Invalid|NaN/.test(new Date(value));
        },
        time: function (value) {
            return /^(10|11|12|[1-9]):[0-5][0-9]$/.test(value);

        },
        datetime: function (value) {
            return /^(\d{2})\.(\d{2})\.(\d{4}) (\d{2}):(\d{2}):(\d{2})$/.test(value);
        },
        dateISO: function (value) {
            return /^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(value);
        },
        accept: function (value, el, param) {
            param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif|bmp";
            return value.match(new RegExp(".(" + param + ")$", "i"));
        },
        url: function (value) {
            return /^(http|https|ftp)\:\/\/[a-z\d\-\.]+\.[a-z]{2,3}(:[a-z\d]*)?\/?([a-z\d\-\._\?\,\'\/\\\+&amp;%\$#\=~])*$/i.test(value);
        },
        indianmobile: function (value) {
            // Indian mobile number validation: 10 digits starting with 6-9
            return /^[6-9]\d{9}$/.test(value);
        }
    };

    var $MXMSGV = {
        'required': '{TITLE} is mandatory',
        'checked': 'Please select atleast {0} {TITLE}',
        'minlen': '{TITLE} min length should be {0}',
        'maxlen': '{TITLE} max length should be {0}',
        'rangelen': '{TITLE} length must be between {0} and {1}',
        'min': '{TITLE} should be a number greater than or equal to {0}',
        'max': '{TITLE} should be a number less than or equal to {0}',
        'range': '{TITLE} should be a number between {0} and {1}.',
        'alpha': '{TITLE} should be alphabetic characters only.',
        'number': '{TITLE} should be number[0-9]',
        'digit': '{TITLE} should be only digits.',
        'alphanum': '{TITLE} should be alphanumeric characters only.',
        'equalto': '{TITLE} should be equals to {0}',
        'name': '{TITLE} can contain only letters and numbers and space',
        'email': '{TITLE} should be a valid email',
        'loginname': '{TITLE} should be more that 3 characters, can contain only letters, numbers, and underscores',
        'password': '{TITLE} should be more than five characters, should not contain space',
        'image': '{TITLE} should only contain image types',
        'date': '{TITLE} should be a valid date.',
        'dateISO': '{TITLE} should be a valid date (ISO).',
        'time': '{TITLE} is not a valid time',
        'datetime': '{TITLE} is not a valid date time',
        'url': '{TITLE} is not a valid url',
        'indianmobile': '{TITLE} should be a valid 10-digit Indian mobile number (starting with 6-9)'
    };

    $setmxmsg = function (el, arrMsg, validate) {
        var nodeNm = el.prop('nodeName');
        if (nodeNm === "LI" || nodeNm === "li" || nodeNm === "td" || nodeNm === "TD")
            var parent = el;
        else if (nodeNm === "SELECT" || nodeNm === "select")
            var parent = el.parent().parent();
        else
            var parent = el.parent();

        parent.removeClass("err");
        parent.find("p.e").remove();
        if (arrMsg.length) {
            if (nodeNm === "SELECT" || nodeNm === "select")
                var cmsg = validate.msg;
            if (cmsg) {
                var msg = cmsg.replace(/\+/g, ' ');
            } else {
                var msg = arrMsg.join(", ");
                msg = msg.replace("{TITLE}", parent.attr("title"));
                msg = msg.replace(/{TITLE}/g, "");
            }
            if (msg == "none") {
                parent.addClass('err');
            } else {
                var err = $('<p class="e">' + msg + '</p>').hide();
                parent.append(err);
                err.slideDown();
                err.parent().addClass('err');
            }
            FLGERR++;
        }
    };

    $.mxsetvalidate = function (elP, validate, xtype) {
        var el = elP.find('[xtype=' + xtype + ']');
        if (typeof (el) !== "undefined" && el.length > 0) {
            var val = "";
            var requred = false;
            if (xtype === "autocomplete") {
                var p = el.attr("params");
                var prms = {}
                if (typeof (p) !== "undefined" && p !== "null")
                    prms = $.parseJSON(p);

                if (prms.tag) {
                    if (prms.tagwrap !== undefined) {
                        val = $(prms.tagwrap + " li");
                    } else {
                        val = elP.find("ul.mx-tag-wrap li");
                    }
                } else {
                    val = $.trim(el.val());
                }
            } else if (xtype === "file") {
                val = el.find("li");
            } else if ($.inArray(xtype, ["checkbox", "radio"]) !== -1) {
                val = el.find("input:checked");
            } else {
                var val = $.trim(el.val());
            }

            if (typeof (val) == undefined)
                val = "";

            if (validate.indexOf("required") !== -1)
                requred = true;

            if (requred || val.length > 0) {
                var arrVali = validate.split(",");
                if (arrVali.length) {
                    var msgc = "";
                    var arrMsg = [];
                    $.each(arrVali, function (i, func) {
                        var arrF = func.split(":");
                        if (typeof (arrF[0]) !== "undefined") {
                            ret = $MXFUNCV[arrF[0]](val, el, arrF[1]);
                            if (ret === false) {
                                msgc = elP.attr("msg");
                                var msg = $MXMSGV[arrF[0]];
                                if (arrF[1]) {
                                    var params = arrF[1].split("~");
                                    $.each(params, function (i, param) {
                                        msg = msg.replace(new RegExp("\\{" + i + "\\}", "g"), params);
                                    });
                                }
                                if (typeof (msgc) == undefined || $.trim(msgc) == "")
                                    arrMsg.push(msg);
                            }
                        }
                    });
                    if (typeof (msgc) !== undefined && $.trim(msgc) !== "")
                        arrMsg.push(msgc);
                    $setmxmsg(el, arrMsg, validate);
                }
            }
        }
    };

    $.mxsetvalidateS = function ($elem) {
        if ($elem.length) {
            var elP, vali;
            if ($elem.parents('li[validate]').length) {
                elP = $elem.parents('li[validate]');
                vali = elP.attr("validate");
            }
            if ($elem.parents('[validateG]').length) {
                elP = $elem.parents('[validateG]');
                vali = elP.attr("validateG");
            } else if ($elem.parents('li[validateG]').length) {
                elP = $elem.parents('li[validateG]');
                vali = elP.attr("validateG");
            }

            if (vali !== null && vali !== undefined && vali.length > 0 && elP.length > 0) {
                var xtype = elP.find("[xtype]").attr("xtype");
                if (typeof (xtype) !== undefined) {
                    $.mxsetvalidate(elP, vali, xtype);
                }
            }
        }
    };

    $.fn.mxvalidateS = function () {
        var frm = $(this[0]);
        frm.find("input:text,input:password,textarea,select,input:checkbox,input:radio").off("focusout,change,click");
        frm.find("input:text,input:password,textarea,select").on("change", function () {
            $.mxsetvalidateS($(this));
        });

        frm.find("input:text,input:password,textarea,select").on("focusout", function () {
            $.mxsetvalidateS($(this));
        });

        frm.find("input:checkbox,input:radio").on("click", function () {
            $.mxsetvalidateS($(this));
        });

        /* setTimeout(function () {
             var $editors = frm.find("[xtype='editor']");
             if ($editors.length > 0) {
                 $editors.each(function () {
                     var editorID = $(this).attr("id");
                     var instance = CKEDITOR.instances[editorID];
                     if (instance) {
                         instance.on("blur", function () {
                             $.mxsetvalidateS($(this));
                         });
                     }
                 });
             }
         }, 2000);*/
    };

    $.fn.mxvalidate = function () {
        FLGERR = false;
        var frm = $(this[0]);
        frm.find("li[validate],[validateG]").each(function () {
            var vali;
            if ($(this).hasAttr('validateG'))
                vali = $.trim($(this).attr("validateG"));
            else
                vali = $.trim($(this).attr("validate"));

            if (vali !== null && vali !== undefined) {
                var elP = $(this);
                var xtype = elP.find("[xtype]").attr("xtype");
                if (vali !== null && vali !== undefined && vali.length > 0 && elP.length > 0)
                    $.mxsetvalidate(elP, vali, xtype);
            }
        });

        if (FLGERR)
            return false;
        else
            return true;
    };
});

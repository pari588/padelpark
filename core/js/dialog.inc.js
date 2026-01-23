(function ($) {
    $.fn.mxdialog = function () {
        var $objpopup = $(this[0]);
        var params = arguments[0] || {};
        if ($objpopup != null && $objpopup != undefined) {
            var defaults = {modal: false, auto: false, time: 3000};
            var params = $.extend(defaults, params);
            $objpopup.hide();

            if (params.left)
                $objpopup.find("div.body").css('left', params.left);
            if (params.top)
                $objpopup.find("div.body").css('top', params.top);

            if (params.auto) {
                var $timer = setTimeout(function () {
                    clearTimeout($timer);
                    $objpopup.hidemxdialog();
                }, params.time);
            }

            if (params.close) {
                $("."+params.close).click(function () {
                    $objpopup.hidemxdialog();
                    return false;
                });
            }
            
            if (!params.modal) {
                $objpopup.click(function () {
                    $objpopup.hidemxdialog();
                    return false;
                });
            }

            $objpopup.fadeIn(200);
        }
    }

    $.fn.hidemxdialog = function () {
        //alert(this[0]);
        var $objpopup = $(this[0]);
        var params = arguments[0] || {};
        if ($objpopup != null && $objpopup != undefined) {
            $objpopup.fadeOut(200, function () {
                if ($(this).hasClass("mxdestroy"))
                    $(this).remove();
            });
        }
    }

    $.mxalert = function (params) {
        if (params.msg) {
            var defaults = {msg: 'No message', modal: false, top: '', left: '', title: 'Alert', auto: false, time: 3000, close: "close"};
            var params = $.extend(defaults, params);
            var $objpopup = $('<div class="mxdialog alert-popup"><div class="body"><a href="#" class="' + params.close + ' del"></a><h2>' + params.title + '</h2><div class="content">' + params.msg + '</div></div></div>');
            $("body").prepend($objpopup);
            $objpopup.addClass("mxdestroy");
            $objpopup.mxdialog(params);
        }
        return $objpopup;
    }

    $.fn.mxpopup = function () {
        var $objpopup = $(this[0]);
        var params = arguments[0] || {};
        if ($objpopup != null && $objpopup != undefined) {
            var defaults = {modal: true, top: '', left: '', auto: false, time: 3000, close: "close"};
            var params = $.extend(defaults, params);
            $objpopup.mxdialog(params);
        }
        return $objpopup;
    }

    $.mxconfirm = function (params) {
        var defaults = {msg: 'No message', modal: true, top: '', left: '', title: 'Confirm', auto: false, time: 3000, close: "close"};
        var params = $.extend(defaults, params);

        var buttonHTML = '';
        $.each(params.buttons, function (name, obj) {
            buttonHTML += '<a href="#" class="btn ' + obj['class'] + '">' + name + '</a>';
            if (!obj.action) {
                obj.action = function () {};
            }
        });

        var $objpopup = $('<div class="mxdialog"><div class="body"><a href="#" class="' + params.close + ' del"></a><h2>' + params.title + '</h2><div class="content">' + params.msg + '<div class="mx-btn">' + buttonHTML + '</div></div></div></div>');
        $("body").prepend($objpopup);
        $objpopup.addClass("mxdestroy");
        $objpopup.mxdialog(params);

        var buttons = $('div.mxdialog .btn'), i = 0;
        $.each(params.buttons, function (name, obj) {
            buttons.eq(i++).click(function () {
                obj.action();
                return false;
            });
        });
        return $objpopup;
    }
})(jQuery);
var WINWIDTH = 0;
var WINHEIGHT = 0;
var THEME;
function setWindow() {
    WINWIDTH = window.innerWidth;
    WINHEIGHT = window.innerHeight;
}
setWindow();

function handleEscape(e) {
    if (e.keyCode == 27) {
        hideMxPopup();
    }
}
$.fn.hasAttr = function (name) {
    return this.attr(name) !== undefined;
};

$.fn.clickToggle = function (func1, func2) {
    var funcs = [func1, func2];
    this.data('toggleclicked', 0);
    this.click(function () {
        var data = $(this).data();
        var tc = data.toggleclicked;
        $.proxy(funcs[tc], this)();
        data.toggleclicked = (tc + 1) % 2;
    });
    return this;
};

$.extend({
    URLEncode: function (c) {
        var o = '';
        var x = 0;
        c = c.toString();
        var r = /(^[a-zA-Z0-9_.]*)/;
        while (x < c.length) {
            var m = r.exec(c.substr(x));
            if (m != null && m.length > 1 && m[1] != '') {
                o += m[1];
                x += m[1].length;
            } else {
                if (c[x] == ' ')
                    o += '+';
                else {
                    var d = c.charCodeAt(x);
                    var h = d.toString(16);
                    o += '%' + (h.length < 2 ? '0' : '') + h.toUpperCase();
                }
                x++;
            }
        }
        return o;
    },
    URLDecode: function (s) {
        var o = s;
        var binVal, t;
        var r = /(%[^%]{2})/;
        while ((m = r.exec(o)) != null && m.length > 1 && m[1] != '') {
            b = parseInt(m[1].substr(1), 16);
            t = String.fromCharCode(b);
            o = o.replace(m[1], t);
        }
        return o;
    }
});

//Common function for ajax call
$.mxajax = function (params) {
    var defaults = { url: null, data: null, type: 'post', dataType: 'json' }
    var params = $.extend(defaults, params)
    var userAuthToken = localStorage.getItem(SITEURL)
    var headers = (userAuthToken != null && userAuthToken != '') ? { Authorization: 'Bearer ' + userAuthToken } : {}
    return new Promise((resolve, reject) => {
        $.ajax({
            url: params.url,
            type: params.type,
            headers: headers,
            data: params.data,
            dataType: params.dataType,
            success: function (data) {
                if (data.err === 401 || data.err === 400) { //Call ajax to generate JWT token if token expire
                    const oldParam = params
                    let tokenPerm = {
                        url: COREURL + '/jwt.inc.php',
                        data: { xAction: 'mxGenerateJwtToken' },
                    }
                    $.mxajax(tokenPerm).then((json) => {
                        if (typeof json.mxtoken !== 'undefined' && json.mxtoken !== '') {
                            setLocalToken(json.mxtoken)
                            $.mxajax(oldParam).then((res) => { //Call old ajax after token generation
                                resolve(res) //return response
                            })
                        } else {
                            reject(json)
                        }
                    })
                } else {
                    resolve(data) //return response
                }
            },
            error: function (error) {
                reject(error)
            },
        })
    })
}

function setLocalToken(token) {
    if (token) {
        if (localStorage.getItem(SITEURL)) {
            localStorage.removeItem(SITEURL)
        }
        localStorage.setItem(SITEURL, token)
    }
}

function getParameterByName(name, href) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS = "[\\?&]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec(href);
    if (results == null)
        return "";
    else
        return decodeURIComponent(results[1].replace(/\+/g, " "));
}

function getFileExt(filename) {
    var ext = "";
    if (typeof (filename) !== "undefined") {
        var arExt = filename.split(".");
        ext = arExt[arExt.length - 1];
    }
    return ext;
}

function getFileType(ext) {
    var fType = "image";
    if (typeof (ext) !== "undefined") {
        if ($.inArray(ext.toLowerCase(), FILEIMAGE.split("|")) === -1)
            fType = "other"

    }
    return fType;
}

function formatFileSize(bytes) {
    if (typeof bytes !== 'number')
        return '';
    if (bytes >= 1000000000)
        return (bytes / 1000000000).toFixed(2) + ' GB';
    if (bytes >= 1000000)
        return (bytes / 1000000).toFixed(2) + ' MB';
    return (bytes / 1000).toFixed(2) + ' KB';
}


function initMagnific(parent) {
    if (typeof parent !== "undefined" && parent.length > 0) {
        if (parent.find('[fType="image"]').length > 0) {
            parent.find('[fType="image"]').magnificPopup({
                type: 'image'
            });
        }
    } else {
        if ($('[fType="image"]').length > 0) {
            $('[fType="image"]').magnificPopup({
                type: 'image'
            });
        }
    }
}

//==========LOADERS======================

function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return '0 Byte';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

function showFakeProgress() {
    $("div.progress span").css("width", "0%");
    var per = 0;
    var interval = setInterval(function () {
        per++;
        setLoaderPercent(per);
        if (per == 80) {
            clearInterval(interval);
        }
    }, 30);
}

function setLoaderPercent(per) {
    $("div.spinner div").text(per + "%");
    $("div.progress span").css("width", per + "%");
    //$("div.progress span").animate({ "width": per + "%" });
}

function showMxLoader(delay) {
    if ($('div#mxloader').length) {
        $('div#mxloader').remove();
    }
    const str = `<div id="mxloader"><div id="mxmsg"></div>
				<div class="progress"><span></span></div>
				<div class="spinner">
					<div class="f1">100</div>
					<div class="f2">100</div>
					<div class="f3">100</div>
					<div class="f4">100</div>
					<div class="f5">100</div>
					<div class="f6">100</div>
				</div>
			</div>`;
    var $mxloader = $(str);
    $("body").prepend($mxloader);
    $mxloader.hide();
    if (typeof (delay) !== "undefined")
        delay = 150;
    $mxloader.fadeIn(delay);
}

function hideMxLoader(msg, delay) {
    var mdelay = 0;
    if (typeof (msg) !== "undefined") {
        mdelay = 1000;
        $("div#mxmsg").text(msg).fadeIn(150);
    }
    if (typeof (delay) !== "undefined") {
        mdelay = delay;
    }
    if ($('div#mxloader').length) {
        $('div#mxloader').delay(mdelay).fadeOut(200, function () {
            $(this).remove();
        });
    }
}

function showLoader(obj) {
    var loader = $('<div id="pre-loader"><p>Wait...</p></div>');
    $("body").append(loader);
    loader.css("left", obj.offset().left + obj.width() - 70);
    loader.css("top", obj.offset().top - obj.height() - 1);
    loader.fadeIn(100);
}

function hideLoader() {
    $('div#pre-loader').fadeOut(100, function () {
        $(this).remove();
    });
}

function focusOnErr() {
    if ($(".wrap-data").length > 0) {
        $(".wrap-data").animate({
            scrollTop: $(".err:eq(0)").offset().top - ($(".page-nav").offset().top + 50)
        }, 300);
    }
}
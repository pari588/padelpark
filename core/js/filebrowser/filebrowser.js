var MAXSIZE = 20;
var MAXFILES = 10;
var FILEIMAGE = "jpg|jpeg|png|png|gif";
var EXT = "jpg|jpeg|png|png|gif|zip|rar|pdf|doc|docx|xls|xlsx|ppt|pptx";
var SHOWREC = 40;
var MXFILELIST = [];
var MXFIELDNAMES = [];
var ITEMS = {};
var ARRITEM = [];

function initFileBrowser() {
    MXFILELIST = [];
    MXFIELDNAMES = [];
    SHOWREC = 20;
    $('.chkAll').prop('checked', false);
    $("input#showRec").val(SHOWREC);
    $("input#fileName,input#dirName").val("");
    $("div.upload-wrap").slideUp();
    $("div.mx-file-upload ul").html("");
    $("a.sort").attr("sort", "ASC");
}

function mxDelFileS(obj) {
    var fileName = $(obj).attr("fileName");
    var arrFiles = [];
    arrFiles.push(fileName);
    mxDelFileF(arrFiles);
    return false;
}

function mxDelFileF(arrFiles) {
    $mxpopup = $.mxconfirm({
        top: "-20%",
        time: 2000,
        msg: "Are you sure you want to delete this file?",
        buttons: {
            "ok": {
                "action": function () {
                    var fileJ = { "files": arrFiles };
                    var url = COREURL + "/js/filebrowser/filebrowser.inc.php";
                    showMxLoader();
                    $.mxajax({
                        url: MODINCURL,
                        data: { xAction: "mxDelFileF", dirPath: DIRPATH, fileNames: arrFiles },
                        type: "POST",
                        dataType: 'json'
                    }).then(function (data) {
                        hideMxLoader();
                        if (data.err == 0) {
                            if (data.data != null && data.data != undefined) {
                                var filtered = ITEMS[DIRPATH].filter(function (item) {
                                    if ($.inArray(item["n"], data.data) === -1) {
                                        return item["n"];
                                    } else {
                                        var obj = $("ul.item-list").find("a.del[fileName='" + item["n"] + "']");
                                        obj.closest("li").fadeOut(function () {
                                            $(this).remove();
                                        });
                                    }
                                });
                                ITEMS[DIRPATH] = filtered;
                                createDrMenu();
                            }
                        }
                    });
                    $mxpopup.hidemxdialog();
                    return false;
                }
            },
            "Cancel": {
                "action": function () {
                    $mxpopup.hidemxdialog();
                    return false;
                }
            }
        }
    });
    return false;
}

function displayErrMsgF(el, arrMsg) {
    var parent = el.parent();
    parent.removeClass("err");
    parent.find("p.e").remove();
    var err = $('<p class="e">' + arrMsg.join('<br>') + '</p>').hide();
    parent.append(err);
    err.slideDown();
    err.parent().addClass('err');
}

function displayFileF(el, data, e) {
    if (typeof (data) !== "undefined" && typeof (el) !== "undefined") {
        MXFILELIST.push(data.files[0]);
        MXFIELDNAMES.push(data.paramName + '[]');
        var ext = getFileExt(data.files[0]['name']);
        var fType = getFileType(ext);
        var fData = "#";
        var img = '';
        if (fType === "image") {
            fData = e.target.result;
            img = '<img src="' + fData + '" alt="' + data.files[0].name + '">';
        }

        var ul = el.find("ul");
        var li = $('<li title="' + formatFileSize(data.files[0].size) + ' : ' + data.files[0].name + '"><a href="#" class="del rs"></a><a href="' + fData + '" fType="' + fType + '" ext="' + ext + '">' + img + '</a></li>');
        data.context = li.appendTo(ul);
        li.find('a.del').off("click");
        li.find('a.del').click(function (event) {
            event.preventDefault();
            li.fadeOut(function () {
                MXFILELIST.splice(li.index(), 1);
                MXFIELDNAMES.splice(li.index(), 1);
                li.remove();
            });
        });
    }
}

function initFileUpload() {
    var el = $("div.mx-file-upload");
    $(document).on('drop dragover', function (e) {
        e.preventDefault();
    });

    var drop = el.find(".drop");
    drop.find("a").off("click");
    drop.find("a").on("click",function (event) {
        event.preventDefault();
        $(this).parent().find('input').click();
    });

    var fu = el.fileupload({
        url: COREURL + "/js/filebrowser/filebrowser.inc.php",
        dropZone: el,
        add: function (e, data) {
            var errMsg = [];
            var wrap = $(this);
            var params = "{}";
            var jparams = {};
            var prms = {};
            var mxSize = MAXSIZE;
            var maxFiles = MAXFILES;
            var maxW = 0;
            var maxH = 0;
            var fv = { "EXT": EXT, "MAXSIZE": parseInt(MAXSIZE), "MAXFILES": parseInt(MAXFILES) };
            if (typeof (params) !== "undefined" && params !== "null")
                prms = $.parseJSON(params);

            var jparams = $.extend(fv, prms);
            mxSize = jparams["MAXSIZE"];
            maxFiles = jparams["MAXFILES"];
            var ext = jparams["EXT"];
            var extF = getFileExt(data.files[0]['name']);
            if (ext != "" && !ext.split("|").includes(extF))
                errMsg.push('Not an accepted file type, should be ' + ext.replace("|", ", "));

            if (mxSize > 0 && data.files[0]['size'] > 0 && data.files[0]['size'] > (mxSize * 1000000))
                errMsg.push('File size should not exceed ' + mxSize + " Mb");

            if (errMsg.length > 0) {
                displayErrMsgF(wrap, errMsg);
            } else {
                var reader = new FileReader();
                reader.readAsDataURL(data.files[0]);
                reader.onload = function (e) {
                    if (maxFiles > 0 && (wrap.find("ul li").length + 1) > maxFiles) {
                        errMsg.push('You can select maximum ' + maxFiles + " files");
                    }
                    if (errMsg.length > 0) {
                        displayErrMsgF(wrap, errMsg);
                        return false;
                    } else {
                        displayFileF(wrap, data, e)
                    }
                }
            }
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            setLoaderPercent(progress);
        },
        done: function (e, data) {
            var json = $.parseJSON(data.result);
            if (typeof (MXFILELIST) !== "undefined" && typeof (json.err) !== "undefined" && json.err == 0) {
                MXFILELIST = [];
                MXFIELDNAMES = [];
                el.find("ul").html("");
                $("a.fa-add.btn").trigger("click");
                if (typeof json.data[DIRPATH] !== "undefined") {
                    ARRITEM = ITEMS[DIRPATH] = json.data[DIRPATH];
                    initPaging();
                    $("ul.main-nav li.active span").text("(" + ARRITEM.length + ")");
                }
                hideMxLoader(json.msg);
            } else {
                hideMxLoader("Error: Invalid response...");
            }
        },
        fail: function (e, data) {
            hideMxLoader("Error: Invalid response...");
        }
    });

    $("#btnUploadFile").click(function (event) {
        event.preventDefault();
        if (typeof (MXFILELIST) !== "undefined" && MXFILELIST.length > 0) {
            showMxLoader();
            $("div.progress span").css("width", "0%");
            el.fileupload('send', {
                files: MXFILELIST,
                paramName: MXFIELDNAMES
            });
        } else {
            $.mxalert({ msg: 'Please select file to upload...', auto: true });
        }
        return false;
    });

    $("#btnCreateDir").click(function (event) {
        event.preventDefault();
        var dirName = $.trim($("input#dirName").val())
        if (dirName === "") {
            $.mxalert({ msg: 'Please enter directory name...', auto: true });
        } else {
            showMxLoader();
            $.mxajax({
                url: MODINCURL,
                data: { xAction: "createDir", dirPath: DIRPATH + "/" + dirName },
                type: "POST",
                dataType: 'json'
            }).then(function (data) {
                hideMxLoader();
                if (data.err == 0) {
                    $("input#dirName").val("");
                    ITEMS[DIRPATH + "/" + dirName] = [];
                    createDrMenu();
                }
            });

        }
        return false;
    });
}

function sortByKeyDesc(array, key) {
    return array.sort(function (a, b) {
        var x = a[key];
        var y = b[key];
        return ((x > y) ? -1 : ((x < y) ? 1 : 0));
    });
}

function sortByKeyAsc(array, key) {
    return array.sort(function (a, b) {
        var x = a[key];
        var y = b[key];
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    });
}

var OBJPAGING;
function initPaging() {
    if (typeof OBJPAGING !== "undefined")
        OBJPAGING.destroy;

    OBJPAGING = $('#nav-right');
    OBJPAGING.pagination({
        dataSource: ARRITEM,
        pageSize: SHOWREC,
        showPrevious: true,
        showNext: true,
        showGoInput: true,
        showGoButton: true,
        callback: function (data, pagination) {
            listDirFiles(data);
        }
    });

    OBJPAGING.addHook('beforeGoButtonOnClick', function () {
        SHOWREC = $("input#showRec").val();
        if (SHOWREC === "undefined" || $.trim(SHOWREC) == "" || SHOWREC < 1 || isNaN(SHOWREC)) {
            SHOWREC = 1;
            $("input#showRec").val(SHOWREC);
        }

        if (typeof OBJPAGING !== "undefined") {
            OBJPAGING.destroy;
        }

        var tmpItems = [];
        if ($("input#fileName").val() !== "") {
            var search = $("input#fileName").val().toUpperCase();
            $.each(ITEMS[DIRPATH], function (i, v) {
                var searchIn = v["n"].toUpperCase();
                if (searchIn.includes(search)) {
                    tmpItems.push(v);
                }
            });
            ARRITEM = tmpItems;
        } else {
            ARRITEM = ITEMS[DIRPATH];
        }
        initPaging();
    });
}

function listDirFiles(items) {
    var dirUrl = UPLOADURL + DIRPATH;
    $("ul.item-list").html("");
    if (items.length > 0) {
        for (var i = 0; i < items.length; i++) {
            var fileName = items[i]["n"];
            var ext = getFileExt(fileName);
            var fType = getFileType(ext);
            var img = '';
            var filePath = DIRPATH + "/" + fileName;
            var fileUrl = dirUrl + "/" + fileName;
            var fileUrlR = fileUrl;

            if (fType === "image") {
                fileUrl = COREURL + '/image.inc.php?path=' + filePath + '&w=100&h=100';
                img = '<img src="' + fileUrl + '" alt="' + fileName + '" title="' + fileName + '" />';
            }
            var fileInfo = fileName + '(' + formatFileSize(items[i]["s"]);
            var strItems = $('<li ext="' + ext + '"><a class="ckfile" href="' + fileUrlR + '">' + img + '</a><a href="#" class="del rs" onclick="return mxDelFileS(this); return false" fileName="' + fileName + '" ></a><span title="' + fileInfo + '">' + fileInfo + ')</span><i class="chk"><input type="checkbox" value="' + fileName + '" dirPath="' + DIRPATH + '"><em></em></i></li>');
            strItems.hide().appendTo('ul.item-list').delay(i * 30).fadeIn('slow');
        }

        $("ul.item-list li a.ckfile").off("click");
        $("ul.item-list li a.ckfile").on("click", function (event) {
            event.preventDefault();
            var fileUrlR = $(this).attr("href");
            returnFileUrl(fileUrlR);
            return false;
        });

    } else {
        $("ul.item-list").html('<li class="no-rec">No files found</li>');
    }
}

function createDrMenu() {
    var strDirMenu = "";
    $.each(ITEMS, function (dir, arrFl) {
        var arrDir1 = dir.split("/");

        var arrDir = arrDir1.filter(function (v) {
            return v !== ''
        });
        var dirN = arrDir[arrDir.length - 1];
        var space = "&nbsp;";
        var active = "";
        if ("/" + dirN == DIRPATH)
            active = "active";
        strDirMenu += '<li class="' + active + '" dirPath="' + dir + '" title="' + dir + '">' + space.repeat(arrDir.length * 3) + '<div></div><small>' + dirN + '</small> <span>(' + arrFl.length + ')<span></li>';
    });
    $("ul.main-nav").html(strDirMenu);
    $("ul.main-nav li").off("click");
    $("ul.main-nav li").on("click", function (event) {
        event.preventDefault();
        initFileBrowser();
        var dirN = $(this).attr("dirPath");
        if (dirN !== DIRPATH) {
            $("input#dirPath").val(dirN);
            $("div.dir-path").text(dirN + "/");
            DIRPATH = dirN;
            ARRITEM = ITEMS[DIRPATH];
            initPaging();
            $("ul.main-nav li").removeClass("active");
            $(this).addClass("active");
        }
        return false;
    });
}

function getUrlParam(paramName) {
    var reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i');
    var match = window.location.search.match(reParam);

    return (match && match.length > 1) ? match[1] : null;
}

function returnFileUrl(fileUrl) {
    if (typeof fileUrl !== "undefined") {
        var funcNum = getUrlParam('CKEditorFuncNum');
        window.parent.CKEDITOR.tools.callFunction(funcNum, fileUrl, function () {
            var dialog = this.getDialog();
            if (dialog.getName() == 'image') {
                var element = dialog.getContentElement('info', 'txtAlt');
                if (element)
                    element.setValue('alt text');
            }
        });
    }
    window.parent.closeFileBrowser();
}

$(document).ready(function () {
    initFileBrowser();
    if (Object.keys(ITEMS).length > 0) {
        ARRITEM = ITEMS[DIRPATH];
        createDrMenu();
        initPaging();
        initFileUpload();
    }

    $("a.sort").click(function (event) {
        event.preventDefault();
        var sortBy = $(this).attr("sortBy");
        var sort = $(this).attr("sort");
        if (sort === "ASC") {
            $(this).attr("sort", "DESC");
            ARRITEM = sortByKeyDesc(ARRITEM, sortBy);
        } else {
            $(this).attr("sort", "ASC")
            ARRITEM = sortByKeyAsc(ARRITEM, sortBy);
        }
        initPaging();
        return false;
    });

    $("a.fa-add.btn").click(function (event) {
        event.preventDefault();
        $("div.upload-wrap").slideToggle();
        return false;
    });

    $('.chkAll').click(function (event) {
        var status = $(this).prop('checked');
        if (!status)
            status = false;
        $("ul.item-list input:checkbox").each(function () {
            $(this).prop('checked', status);
        });
    });

    $("div.fl-action a.trash").click(function (event) {
        event.preventDefault();
        var arrFiles = [];
        $("ul.item-list input:checkbox").each(function () {
            if ($(this).is(":checked")) {
                if ($(this).val())
                    arrFiles.push($(this).val());
            }
        });

        if (arrFiles.length > 0) {
            mxDelFileF(arrFiles);
        } else {
            $.mxalert({ msg: 'Please select files to delete...', auto: true, time: 1000 });
        }
        return false;
    });
});

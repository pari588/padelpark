var MXFILELIST = new Object();
var MXFIELDNAMES = new Object();
//var MXVDEFAULT = {"EXT": FILEIMAGE, "MAXSIZE": parseInt(MAXSIZE), "MAXFILES": parseInt(MAXFILES)};
//==============File handling function===============
function mxDelFile(obj, params) {
  $mxconfirm = $.mxconfirm({
    top: "-20%",
    time: 2000,
    msg: "Are you sure you want to delete this file?",
    buttons: {
      ok: {
        action: function () {
          if (params != null && params != undefined) {
            params.xAction = "mxDelFile";
            var url = MODINCURL;
            showMxLoader();
            $.mxajax({
              url: url,
              type: "POST",
              data: params,
              dataType: "json",
            }).then(function (json) {
              onResponse(json);
              if (json.err == 0) {
                $(obj)
                  .closest("li")
                  .fadeOut(function () {
                    $(this).remove();
                  });
              }
            });
          }
          $mxconfirm.hidemxdialog();
          return false;
        },
      },
      Cancel: {
        action: function () {
          $mxconfirm.hidemxdialog();
          return false;
        },
      },
    },
  });
  return false;
}

function displayFile(el, data, e) {
  if (typeof data !== "undefined" && typeof el !== "undefined") {
    var frmID = el.closest("form").attr("id");
    var ul = el.find("ul");

    MXFILELIST[frmID].push(data.files[0]);
    MXFIELDNAMES[frmID].push(data.paramName + "[]");

    var ext = getFileExt(data.files[0]["name"]);
    var fType = getFileType(ext);
    var fData = "#";
    var target = "";
    var img = "";
    if (fType === "other") {
      target = ' target="_blank"';
    } else {
      fData = e.target.result;
      img = '<img src="' + fData + '" alt="' + data.files[0].name + '">';
    }

    var li = $(
      '<li title="' +
      formatFileSize(data.files[0].size) +
      " : " +
      data.files[0].name +
      '"><a href="#" class="del rs"></a><a href="' +
      fData +
      '" target="' +
      target +
      '" fType="' +
      fType +
      '" ext="' +
      ext +
      '">' +
      img +
      "</a></li>"
    );
    data.context = li.appendTo(ul);

    $.mxsetvalidateS(el.find("input:file"));

    li.find('[fType="image"]').magnificPopup({ type: "image" });
    li.find("a.del").off("click");
    li.find("a.del").click(function () {
      li.fadeOut(function () {
        MXFILELIST[frmID].splice(li.index(), 1);
        MXFIELDNAMES[frmID].splice(li.index(), 1);
        li.remove();
      });
    });
  }
}

var setHeader = function (xhr) {
  var userAuthToken = localStorage.getItem(SITEURL);
  if (userAuthToken !== "" && userAuthToken !== null) {
    xhr.setRequestHeader("Authorization", "Bearer " + userAuthToken);
  }
};

$.fn.mxfileupload = function () {
  var el = $(this);
  var prms = arguments[0] || {};
  if (el != null && el != undefined) {
    var d = { frmID: el.closest("form").attr("id"), url: MODINCURL };
    var p = $.extend(d, prms);
    var frmID = p.frmID;
    $(document).on("drop dragover", function (e) {
      e.preventDefault();
    });

    var drop = el.find(".drop");
    drop.find("a").off("click");
    drop.find("a").click(function () {
      $(this).parent().find("input").click();
    });

    var fu = el.fileupload({
      url: p.url,
      dropZone: el,
      beforeSend: function (xhr) {
        setHeader(xhr);
      },
      add: function (e, data) {
        var errMsg = [];
        var wrap = $(this);
        var params = wrap.attr("params");
        var jparams = {};
        var prms = {};
        var mxSize = MAXSIZE;
        var maxFiles = MAXFILES;
        var maxW = 0;
        var maxH = 0;
        var fv = {
          EXT: FILEIMAGE,
          MAXSIZE: parseInt(MAXSIZE),
          MAXFILES: parseInt(MAXFILES),
        };
        if (typeof params !== "undefined" && params !== "null")
          prms = $.parseJSON(params);

        var jparams = $.extend(fv, prms);
        mxSize = jparams["MAXSIZE"];
        maxFiles = jparams["MAXFILES"];
        var ext = jparams["EXT"];
        var extF = getFileExt(data.files[0]["name"]).toLowerCase();
        if (ext != "" && !ext.split("|").includes(extF))
          errMsg.push(
            "Not an accepted file type, should be " + ext.replace("|", ", ")
          );

        if (typeof jparams["W"] !== "undefined")
          if (jparams["W"] !== "") maxW = jparams["W"];
        if (typeof jparams["H"] !== "undefined") {
          if (jparams["H"] !== "") maxH = jparams["H"];
        }

        if (
          mxSize > 0 &&
          data.files[0]["size"] > 0 &&
          data.files[0]["size"] > mxSize * 1000000
        ) {
          errMsg.push("File size should not exceed " + mxSize + " Mb");
        }
        if (errMsg.length > 0) {
          displayErrMsg(wrap, errMsg);
        } else {
          var reader = new FileReader();
          reader.readAsDataURL(data.files[0]);
          reader.onload = function (e) {
            if (maxFiles > 0 && wrap.find("ul li").length + 1 > maxFiles)
              errMsg.push("You can select maximum " + maxFiles + " files");

            if (jparams["W"] || jparams["H"]) {
              var image = new Image();
              image.src = reader.result;
              image.onload = function () {
                if (maxW > 0 && this.width != maxW)
                  errMsg.push("Width should be " + maxW);
                if (maxH > 0 && this.height != maxH)
                  errMsg.push("Height should be " + maxH);
                if (errMsg.length > 0) {
                  displayErrMsg(wrap, errMsg);
                  return false;
                } else {
                  displayFile(wrap, data, e);
                }
              };
            } else {
              if (errMsg.length > 0) {
                displayErrMsg(wrap, errMsg);
                return false;
              } else {
                displayFile(wrap, data, e);
              }
            }
          };
        }
      },
      progressall: function (e, data) {
        var progress = parseInt((data.loaded / data.total) * 100, 10);
        setLoaderPercent(progress);
      },
      done: function (e, data) {
        onResponse(data.result, frmID, p.callback);
      },
      fail: function (e, data) {
        onResponse(data.result, frmID, p.callback);
      },
    });
  }
};

//==============Common form functions================

function displayErrMsg(el, arrMsg) {
  var parent = el.parent();
  parent.removeClass("err");
  parent.find("p.e").remove();
  var err = $('<p class="e">' + arrMsg.join("<br>") + "</p>").hide();
  parent.append(err);
  err.slideDown();
  err.parent().addClass("err");
  FLGERR++;
}
// Datetime
function initDateTime() {
  $(".hasDatepicker").removeClass("hasDatepicker");
  if ($("input[xtype=date]").length) {
    var defaultsD = {
      dateFormat: "yy-mm-dd",
      numberOfMonths: 2,
      changeMonth: true,
      changeYear: true,
    };
    $("input[xtype=date]").datepicker("destroy");
    $("input[xtype=date]").each(function () {
      var p = $(this).attr("params");
      var params = $.extend(defaultsD, $.parseJSON(p));
      $(this).datepicker(params);
    });
  }

  if ($("input[xtype=time]").length) {
    var defaultsT = {};
    $("input[xtype=time]").timepicker("destroy");
    $("input[xtype=time]").each(function () {
      var p = $(this).attr("params");
      var params = $.extend(defaultsT, $.parseJSON(p));
      $(this).timepicker(params);
    });
  }

  if ($("input[xtype=datetime]").length) {
    var defaultsDT = {
      dateFormat: "yy-mm-dd",
      numberOfMonths: 2,
      changeMonth: true,
      changeYear: true,
    };
    $("input[xtype=datetime]").datetimepicker("destroy");
    $("input[xtype=datetime]").each(function () {
      var p = $(this).attr("params");
      var params = $.extend(defaultsDT, $.parseJSON(p));
      $(this).datetimepicker(params);
    });
  }
}

//Editor
function removeEditor(editorID) {
  if (typeof editorID !== "undefined") {
    var instance = CKEDITOR.instances[editorID];
    if (instance) {
      instance.updateElement();
      instance.destroy(true);
    }
  }
}

function removeEditors(grpWrap) {
  if (grpWrap.length > 0) {
    var $editors = grpWrap.find("[xtype='editor']");
    if ($editors.length > 0) {
      $editors.each(function () {
        var editorID = $(this).attr("id");
        removeEditor(editorID);
      });
    }
  }
}

var MXPOPUPFB;
function closeFileBrowser() {
  if (typeof MXPOPUPFB !== "undefined")
    $("div.mx-file-browser iframe").attr("src", "");
  MXPOPUPFB.hidemxdialog();
}

function resetEditorTheme(editor) {
  if (typeof editor !== "undefined") {
    var id = editor.attr("id");
    removeEditor(id);
    var defaults = {
      toolbar: "medium",
      height: 300,
      allowedContent: true,
      contentsCss: LIBURL + "/js/ckeditor/contents.css",
    };

    var params = $.extend(defaults, $.parseJSON(editor.attr("params")));
    if (THEME !== "light" && THEME !== "moderate")
      params.contentsCss = LIBURL + "/js/ckeditor/skins/prestige/contents.css";
    CKEDITOR.replace(id, params);
    CKEDITOR.on("dialogDefinition", function (ev) {
      var editor = ev.editor;
      var dialogDefinition = ev.data.definition;
      var url = editor.config.filebrowserBrowseUrl;
      var cleanUpFuncRef = CKEDITOR.tools.addFunction(function (a) {
        CKEDITOR.tools.callFunction(1, a, "");
      });
      var tabCount = dialogDefinition.contents.length;
      for (var i = 0; i < tabCount; i++) {
        var browseButton = dialogDefinition.contents[i].get("browse");
        if (browseButton !== null) {
          browseButton.onClick = function (dialog, i) {
            editor._.filebrowserSe = this;
            var iframe = $("div.mx-file-browser")
              .find("iframe")
              .attr({
                src:
                  editor.config.filebrowserBrowseUrl +
                  "?CKEditor=body&CKEditorFuncNum=" +
                  cleanUpFuncRef +
                  "&langCode=en",
              });
            MXPOPUPFB = $("div.mx-file-browser").mxpopup();
          };
        }
      }
    });
  }
}

function initEditors(parent) {
  if (typeof parent !== "undefined") {
    var $editors = parent.find("[xtype='editor']");
  } else {
    var $editors = $("[xtype='editor']");
  }
  if ($editors.length > 0) {
    if ($editors.length) {
      $editors.each(function () {
        resetEditorTheme($(this));
      });
    }
  }
}

function initAutoComplete(parent, flg) {
  parent.find("[xtype=autocomplete]").each(function () {
    var p = $(this).attr("params");
    var defaultAc = {};
    var params = $.extend(defaultAc, $.parseJSON(p));
    if (typeof flg !== "undefined" && flg == 1) {
      parent.find("ul.mx-tag-wrap").html("");
    }
    $(this).mxautocomplete(params);
  });
}

function initSerachNCheckall(mainParent) {
  if ($(".chkall-serach").length > 0) {
    $("ul.tbl-form li").hover(function () {
      if ($(this).find(".chkall-serach").length > 0) {
        $(this).addClass("chkhover");
      }
    }, function () {
      if ($(this).find(".chkall-serach").length > 0) {
        $(this).removeClass("chkhover");
      }
    });
  }
  mainParent.find('.chk-all').off("click");
  mainParent.find('.chk-all').on("click", function () {
    var status = $(this).prop('checked');
    var parent = $(this).closest("li");
    if (parent.length < 1)
      parent = $(this).closest("td");
    parent.find("input:visible").prop('checked', status);
  });

  mainParent.find(".txt-chk-serach").off("keyup");
  mainParent.find(".txt-chk-serach").on("keyup", function () {
    var parent = $(this).closest("li");
    if (parent.length < 1)
      parent = $(this).closest("td");
    var chkli = parent.find(".mx-list li");

    if ($(this).val() == "") {
      parent.find('.chk-all').prop('checked', false);
    }

    var value = this.value.toLowerCase().trim();

    chkli.each(function () {
      var chk = $(this).find(".chk")
      if (chk.length < 1)
        chk = $(this).find(".rdo")

      var text = chk.text().toLowerCase().trim();
      if (text.indexOf(value) == -1) {
        //chk.find("input").prop('checked', false);
        $(this).hide()
      } else {
        $(this).show();
      }
    });
  });
}

function mxValidateServer(objV) {
  for (key in objV) {
    /*var parent = el.parent();
         parent.removeClass("err");
         parent.find("p.e").remove();
         var err = $('<p class="e">' + arrMsg.join('<br>') + '</p>').hide();
         parent.append(err);
         err.slideDown();
         err.parent().addClass('err');*/
  }
}

function onResponse(json, frmID, callback) {
  if (typeof json == "string") {
    json = $.parseJSON(json);
  }
  if (typeof json !== undefined) {
    $("div.progress span").animate({ width: "100%" });
    if (typeof json.err !== "undefined" && json.err == 0) {
      if (typeof MXFILELIST[frmID] !== "undefined") {
        MXFILELIST[frmID] = [];
        MXFIELDNAMES[frmID] = [];
      }
    }
    if (typeof callback == "function") {
      callback(json);
    } else {
      if (typeof json.alert !== "undefined" && $.trim(json.alert) !== "") {
        hideMxLoader();
        $.mxalert({ msg: json.alert });
        return false;
      }
      if (typeof json.rurl !== "undefined" && $.trim(json.rurl) !== "" && json.err == 0) {
        $("div#mxmsg").text(json.msg).fadeIn(150);
        var $timer = setTimeout(function () {
          clearTimeout($timer);
          window.location = json.rurl;
        }, 1000);
      } else {
        hideMxLoader(json.msg);
      }
    }
  } else {
    hideMxLoader("Error: Invalid response...");
  }
  return false;
}

function mxGenerateJwtToken(frm, fileEl, p) {
  $.ajax({
    url: COREURL + "/jwt.inc.php",
    type: "post",
    dataType: "json",
    //headers: { 'Authorization': 'Bearer ' + userAuthToken },
    data: { xAction: "mxGenerateJwtToken" },
    success: function (json) {
      if (typeof json.mxtoken !== "undefined" && json.mxtoken !== "") {
        setLocalToken(json.mxtoken);
      }
      mxSubmitForm(frm, fileEl, p);
    },
    error: function (xhr) {
      if (xhr.status !== 200) {
        // alert("An error occured: " + xhr.status + " " + xhr.statusText);
      }
    },
  });
}

function mxSendFile(params) {
  $("div.progress span").css("width", "0%");
  var defaults = { frmID: "", fileEl: "" };
  var params = $.extend(defaults, params);
  var frmID = params.frmID;
  params.fileEl.fileupload("send", {
    files: MXFILELIST[frmID],
    paramName: MXFIELDNAMES[frmID],
  });
}

function mxValidateToken(params, callback) {
  let valTokenPerm = {
    url: COREURL + "/jwt.inc.php",
    data: { xAction: "mxValidateJwtToken" },
    type: "post",
    dataType: "json",
  };
  $.mxajax(valTokenPerm).then((json) => {
    if (json.isValid && json.isValid == true) {
      if (typeof callback == "function") {
        callback(params);
      }
    }
  });
}

function mxSubmitForm(frm, fileEl, p) {
  var userAuthToken = localStorage.getItem(SITEURL);
  if (userAuthToken !== "" && userAuthToken !== null) {
    if (typeof CKEDITOR !== "undefined") {
      for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
      }
    }
    if (frm.mxvalidate() !== false) {
      showMxLoader();
      var frmID = frm.attr("id");
      if (
        typeof MXFILELIST[frmID] !== "undefined" &&
        MXFILELIST[frmID].length > 0
      ) {
        mxValidateToken({ frmID: frmID, fileEl: fileEl }, mxSendFile);
      } else {
        showFakeProgress();
        $.ajax({
          url: p.url,
          type: "post",
          dataType: "json",
          headers: { Authorization: "Bearer " + userAuthToken },
          data: frm.serialize(),
          success: function (json) {
            //Handle token expire error
            if (json.err === 401) {
              mxGenerateJwtToken(frm, fileEl, p);
            } else {
              onResponse(json, frmID, p.callback);
            }
          },
          error: function (xhr) {
            if (xhr.status !== 200) {
              // alert("An error occured: " + xhr.status + " " + xhr.statusText);
            }
          },
        });
      }
    } else {
      if ($(".err").length) {
        $(".err:eq(0)").focus();
        focusOnErr();
      }
    }
  } else {
    mxGenerateJwtToken(frm, fileEl, p);
  }
}

$.fn.mxinitform = function (params) {
  var params = arguments[0] || {};
  var frm = $(this[0]);
  if (frm.length) {
    var d = { button: "a.fa-save", url: MODINCURL };
    var p = $.extend(d, params);
    frm.mxvalidateS();
    var frmID = frm.attr("id");
    var el = frm.find("div.mx-file-upload");
    MXFILELIST[frmID] = [];
    MXFIELDNAMES[frmID] = [];

    initAutoComplete(frm);
    initSerachNCheckall(frm);

    if (el.length > 0) {
      el.mxfileupload({ frmID: frmID, callback: p.callback, url: p.url }); // sample callback
      //el.mxfileupload({ frmID: frmID });
    }
    $(p.button + "[rel=" + frmID + "]").on("click", function (e) {
      if (typeof p.pcallback == "function") {
        if (frm.mxvalidate() !== false)
          p.pcallback(frm, el, p);
      } else {
        mxSubmitForm(frm, el, p);
      }
      return false;
    });

    frm.keypress(function (event) {
      var keycode = event.keyCode ? event.keyCode : event.which;
      var element = event.target;
      if (keycode == "13" && $(element).attr("xType") != "textarea") {
        if (typeof p.pcallback == "function") {
          if (frm.mxvalidate() !== false)
            p.pcallback(frm, el, p);
        } else {
          mxSubmitForm(frm, el, p);
        }
        return false;
      }
    });
  }
};

function initFormSubmit() {
  if (PAGETYPE == "add" || PAGETYPE == "edit") {
    $("form").each(function () {
      var frm = $(this);
      var auto = frm.attr("auto");
      if (typeof auto == "undefined" || auto !== "false") {
        //frm.mxinitform({callback:callbackTest, url:"http://www.abc.com"});
        frm.mxinitform();
      }
    });
  }
}

function resetmxvalidateS(grpWrap) {
  var frm = grpWrap.closest("form");
  if (frm.length > 0) {
    //frm.mxvalidateS();
    var auto = frm.attr("auto");
    if (typeof auto == "undefined" || auto !== "false") {
      frm.mxvalidateS();
    }
  }
}

function resetFileUpload(grpSet) {
  var el = grpSet.find("div.mx-file-upload");
  if (el.length) {
    el.find("ul").html("");
    el.mxfileupload();
  }
}

function reinitElementJs(grpWrap, grpSet) {
  initEditors(grpWrap);
  resetmxvalidateS(grpWrap);
  initAutoComplete(grpSet, 1);
  initSerachNCheckall(grpSet);
  resetFileUpload(grpSet);
  initMagnific(grpSet);
  initDateTime();
}

function resetElementValues(grpSet, rcheked) {
  grpSet.find("input:text,input:hidden,select,textarea").val("");
  grpSet.find("input:checkbox,input:radio").prop("checked", false);
  if (rcheked.length > 0) {
    $.each(rcheked, function (index, value) {
      value.prop("checked", true);
    });
  }
}

function initDeleteRowSub(grpWrap) {
  var btn = grpWrap.find(".del.row-sub");
  if (btn.length) {
    btn.off("click");
    btn.on("click", function () {
      var grpSet = grpWrap.find(".grp-set-sub");
      if (grpSet.length > 1) {
        removeEditors(grpWrap);
        $(this).closest(".grp-set-sub").remove();
        resetGroupIndexSub(grpWrap);
        initEditors(grpWrap);

        var callback = $(this).data("callback");
        if (typeof callback !== "undefined") {
          var callbackF = eval(callback);
          if (typeof callbackF == "function") {
            callbackF(grpSet);
          }
        }
      }
      return false;
    });
  }
}

function initAddRowSub(grpWrap) {
  var add = grpWrap.find(".add-set-sub");
  if (add.length > 0) {
    add.off("click");
    add.click(function () {
      removeEditors(grpWrap);
      var grpSetF = grpWrap.find(".grp-set-sub:first");
      var rcheked = [];
      grpSetF.find("input:radio:checked").each(function () {
        rcheked.push($(this));
      });

      var grpSet = grpSetF.clone(false);
      grpWrap.append(grpSet);
      resetElementValues(grpSet, rcheked);
      resetGroupIndexSub(grpWrap);
      reinitElementJs(grpWrap, grpSet);
      initDeleteRowSub(grpWrap);

      var callback = $(this).data("callback");
      if (typeof callback !== "undefined") {
        var callbackF = eval(callback);
        if (typeof callbackF == "function") callbackF(grpSet);
      }
      return false;
    });
  }
}

function initDeleteRow(grpWrap) {
  var btn = grpWrap.find(".del.row");
  if (btn.length) {
    btn.off("click");
    btn.on("click", function () {
      var grpSet = grpWrap.find(".grp-set");
      if (grpSet.length > 1) {
        $(this).closest(".grp-set").remove();
        removeEditors(grpWrap);
        resetGroupIndex(grpWrap);
        initEditors(grpWrap);

        var callback = $(this).data("callback");
        if (typeof callback !== "undefined") {
          var callbackF = eval(callback);
          if (typeof callbackF == "function") {
            callbackF(grpSet, "del");
          }
        }
      }
      return false;
    });
  }
}

function initAddRow(grpWrap) {
  var add = grpWrap.find(".add-set");
  if (add.length > 0) {
    add.off("click");
    add.click(function () {
      removeEditors(grpWrap);
      var grpSetF = grpWrap.find(".grp-set:first");
      var rcheked = [];
      grpSetF.find("input:radio:checked").each(function () {
        rcheked.push($(this));
      });

      var grpSet = grpSetF.clone(false);
      grpWrap.append(grpSet);
      resetElementValues(grpSet, rcheked);
      resetGroupIndex(grpWrap);
      reinitElementJs(grpWrap, grpSet);
      initDeleteRow(grpWrap);

      var grpWrapSub = grpSet.find(".grp-wrap-sub");
      if (grpWrapSub.length > 0) {
        initAddRowSub(grpWrapSub);
        initDeleteRowSub(grpWrapSub);
      }

      var callback = $(this).data("callback");
      if (typeof callback !== "undefined") {
        var callbackF = eval(callback);
        if (typeof callbackF == "function") callbackF(grpSet, "add");
      }
      return false;
    });
  }
}

//==============START RESET INDEXE==================
function mxGroupSetIndexAc(grpSet) {
  var index = grpSet.index();
  $(grpSet)
    .find(".mx-tag-wrap")
    .each(function (iUl, ul) {
      mxSetTagIndex(ul, index);
    });
}

function renameGroupIndex(el, xtype, strNm, strID) {
  if ($.inArray(xtype.toLowerCase(), ["checkbox", "radio", "file"]) !== -1) {
    $(el).find("input").each(function (i, rc) {
      var arrName = $(rc).attr("name").split("[");
      var extBracket = "";
      if (typeof $(rc).attr("rowGrp") !== "undefined") {
        extBracket = "[]";
      }
      var name = arrName[0] + strNm + extBracket;
      $(rc).attr("name", name);
    });
  } else {
    var flg = 0;
    if (typeof $(el).attr("rowGrp") === "undefined") {
      var arrName = $(el).attr("name").split("[");
      var id = $(el).attr("id");
      if (typeof id !== "undefined") $(el).attr("id", arrName[0] + strID);
      $(el).attr("name", arrName[0] + strNm);
    }
  }
}

function resetGroupIndexSub(grpWrap) {
  var grpSet = grpWrap.closest(".grp-set");
  var iTr = "";
  if (grpSet.length > 0) var iTr = grpSet.index();

  grpWrap.find(".grp-set-sub").each(function (subIndex, grpSet) {
    $(grpSet)
      .find("[xtype]")
      .each(function (iEl, el) {
        var xtype = $(el).attr("xtype");
        if (typeof xtype !== "undefined") {
          renameGroupIndex(
            el,
            xtype,
            "[" + iTr + "]" + "[" + subIndex + "]",
            "_" + iTr + "_" + subIndex
          );
        }
      });
    //mxGroupSetIndexAc($(grpSet));
  });
}

function resetGroupIndex(grpWrap) {
  grpWrap.find(".grp-set").each(function (iTr, grpSet) {
    $(grpSet)
      .find("[xtype]")
      .each(function (iEl, el) {
        var xtype = $(el).attr("xtype");
        if (typeof xtype !== "undefined") {
          var nmSub = "";
          var idSub = "";
          var groupSetSub = $(el).closest(".grp-set-sub");
          if (groupSetSub.length > 0) {
            var subIndex = groupSetSub.index();
            nmSub = "[" + subIndex + "]";
            idSub = "_" + subIndex;
          }
          renameGroupIndex(el, xtype, "[" + iTr + "]" + nmSub, "_" + iTr + idSub);
        }
      });
    mxGroupSetIndexAc($(grpSet));
  });
}
//==============END RESET INDEXE==================
function initGroupSet() {
  $(".grp-wrap").each(function (index, grpWrap) {
    initAddRow($(grpWrap));
    initDeleteRow($(grpWrap));
    resetGroupIndex($(grpWrap));
  });

  $(".grp-wrap-sub").each(function (index, grpWrap) {
    initAddRowSub($(grpWrap));
    initDeleteRowSub($(grpWrap));
    //resetGroupIndex($(grpWrap), ".grp-set-sub");
  });
}

$(document).ready(function () {
  initGroupSet();
});

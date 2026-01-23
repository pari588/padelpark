$(document).ready(function () {
  MXTRASHPRE = trashRecord;

   $("a.popup-gallery").fancybox({
      openEffect	: 'none',
      closeEffect	: 'none',
        showCloseButton : true,
        closeBtn: true,
    });
  
  $(".expense-image").click(function () {
    var expenseEntryID = $(this).attr("rel");
    $mxpopup = $(".expense-image-popup").mxpopup();
    expenseImageData(expenseEntryID);
  });
  });
  
  function trashRecord(data) {
    var aUrl = SITEURL + "/xadmin/mod/expense-entry/x-expense-entry.inc.php";
    if (data.xAction == "trash") {
      var status = 0;
    } else {
      var status = 1;
    }
  
    $.mxajax({
      url: aUrl,
      data: {
        xAction: "updateBalanceAfterTrash",
        expenseEntryID: data.id,
        status: status,
      },
      type: "POST",
      dataType: "json",
    }).then(function (response) {
      if (response.err == 0) {
        mxtrash(data);
      }
    });
  }

  function expenseImageData(expenseEntryID) {
  // alert(expenseEntryID);
  var aUrl = SITEURL + "/xadmin/mod/expense-entry/x-expense-entry.inc.php";
  $.mxajax({
    url: aUrl,
    data: {
      xAction: "fetchExpenseImages",
      expenseEntryID: expenseEntryID,
    },
    type: "POST",
    dataType: "json",
  }).then(function (response) {
    if (response.err == 0) {
      $(".expense-details").html(response.str);
       $("a.popup-gallery").fancybox({
      openEffect	: 'none',
      closeEffect	: 'none',
        showCloseButton : true,
        closeBtn: true,
    });
    }
  });
  }

  
function loadImage(index, elClass) {
  var len = $(elClass + ' img.pimg').length
  if (index < len) {
    var _imgL = $(elClass + ' img.pimg:eq(' + index + ')')
    var _src = _imgL.attr('src')
    var _img = new Image()
    _img.src = _src
    _img.onload = function () {
      _imgL.fadeIn(300)
      index++
      //console.log(index,Date.now());
      loadImage(index, elClass)
    }
  }
}

loadImage(0, 'ul.list-work-d')

$('ul.list-work-d li').hover(
  function () {
    $(this).find('div.title').fadeIn(400)
    $(this).find('div.title h2').animate({ marginTop: '52%' }, 200)
    $(this).find('div.links').animate({ top: '35%' }, 200)
    $(this).find('img').animate({ width: '106%', marginLeft: '-3%' }, 1000)
  },
  function () {
    $(this).find('div.title').fadeOut(200)
    $(this).find('div.title h2').animate({ marginTop: '45%' }, 200)
    $(this).find('div.links').animate({ top: '45%' }, 200)
    $(this).find('img').animate({ width: '100%', marginLeft: '0%' }, 500)
  }
)

function resizeWorkD() {
  $('ul.list-work-d li').css('min-height', WINHEIGHT - HEADERH + 'px')
  $('div.scroll-work').css('height', WINHEIGHT - HEADERH + 'px')
}

function resizeWork() {
  $('ul.list-work li').css('height', WINWIDTH / 4 + 'px')
  var workH = $('div.wrap-work h1').height()
  var scrollWrap = $('div.scroll-work')
  scrollWrap.css('height', WINHEIGHT - HEADERH - workH + 'px')
}


  
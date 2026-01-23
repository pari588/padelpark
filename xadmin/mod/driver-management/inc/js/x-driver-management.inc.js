$(document).ready(function () {
  $('#download-report').on('click', function () {
    $('.open-move-popup').mxpopup()
  })
  // Start: Download Driver Report into CSV.
  $('a.driver-csv-report').click(function () {
    var year = $('#year').val()
    if (year == '') {
      $.mxalert({ msg: 'Please Select year' })
      return false
    }
    var month = $('#month').val()
    if (month == '') {
      $.mxalert({ msg: 'Please Select month' })
      return false
    }
    var week = $('#week').val()
    var xAction = $('input.xAction').val()
    var aUrl =
      ADMINURL +
      '/mod/driver-management/x-driver-management.inc.php/?xAction=' +
      xAction +
      '&year=' +
      year +
      '&month=' +
      month +
      '&week=' +
      week
    $('#frm-memInvite').trigger('reset')
    window.location = aUrl
    $('.open-move-popup').hide()
  })
  // End.

  $('.verify-btn').click(function () {
    showMxLoader()
    var aUrl = ADMINURL + '/mod/driver-management/x-driver-management.inc.php'
    var driverManagementID = $(this).attr('rel')
    $.mxajax({
      type: 'POST',
      url: aUrl,
      data: { xAction: 'verifyMarkin', driverManagementID: driverManagementID },
      dataType: 'json',
    }).then(function (data) {
      hideMxLoader()
      $.mxalert({ msg: data.msg })
    })
  })

  $('#settlePayAll').click(function () {
    var status = $(this).is(':checked')
    if (!status) status = false
    console.log(status)
    $('table.tbl-list tr:not(:first)').each(function () {
      if (!$(this).find('input').eq(1).is(':disabled')) {
        $(this).find('input').eq(1).attr('checked', status)
      }
    })
  })

  $('#settleDriverWelfare').click(function () {
    var driverArr = []
    var paymentArr = []
    var totalWelfareAmount = 0.0
    $.each($('input.settlePay:checked'), function () {
      driverArr.push($(this).val())
      paymentArr.push($(this).data('welfare-amount'))
      totalWelfareAmount =
        parseFloat(totalWelfareAmount) +
        parseFloat($(this).data('welfare-amount'))
    })

    if (driverArr.length < 1) {
      $.mxalert({ msg: 'Please select at least one record.' })
    } else {
      const params = {
        driverArr: driverArr,
        totalWelfareAmount: totalWelfareAmount,
      }
      $confirmbox = $.mxconfirm({
        msg:
          '<br/>Do you want, to settle ' +
          parseFloat(totalWelfareAmount) +
          ' amount ?',
        buttons: {
          Yes: {
            action: function () {
              $confirmbox.hidemxdialog()
              settlePayment(params)
            },
            class: 'thm-btn',
          },
          Cancel: {
            action: function () {
              $confirmbox.hidemxdialog()
              return false
            },
            class: 'thm-btn',
          },
        },
      })
      return false
    }
  })
})

function settlePayment(params = {}) {
  var aUrl = ADMINURL + '/mod/driver-management/x-driver-management.inc.php'
  $.mxajax({
    type: 'POST',
    url: aUrl,
    data: {
      xAction: 'settlePayment',
      driverArr: params.driverArr,
      totalWelfareAmount: params.totalWelfareAmount,
    },
    dataType: 'json',
  }).then(function (data) {
    hideMxLoader()
    $.mxalert({ msg: data.msg })
    if (data.err == 0) {
      setTimeout(function () {
        location.reload()
      }, 2500)
    }
  })
}

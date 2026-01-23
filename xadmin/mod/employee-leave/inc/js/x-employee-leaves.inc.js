const queryString = window.location.search
const urlParams = new URLSearchParams(queryString)

function getCanceledPopup() {
  $('div.att-details .infoData').html(' ')
  $('div.att-details').hide()
  urlParams.delete('showdialog')
  window.location.search = urlParams
}
function MXTRASHPOST(result, params) {
  if (params && params.id) {
    showMxLoader()
    $.mxajax({
      type: 'post',
      data: { leaveIDs: params.id, xAction: 'updateBalanceLeave' },
      url: MODINCURL,
      dataType: 'json',
    }).then(function (data) {
      hideMxLoader()
      if (data.err == 0) {
      } else {
        $.mxalert({ msg: data.msg, title: 'Error in update leaves' })
      }
    })
  }
}

$(window).on('load', function () {
  var leaveid = urlParams.get('leaveID')
  var isDialog = urlParams.get('showdialog')
  if (
    isDialog != '' &&
    isDialog != undefined &&
    isDialog == 'true' &&
    leaveid > 0
  ) {
    setTimeout(function () {
      $('button.leavebutton[leaveid=' + leaveid + ']').trigger('click')
    }, 1000)
  }
})

$(document).ready(function () {
  if (PAGETYPE == 'edit') {
    $('div.leave-details .tbl-list th.add-grp').css('display', 'none')
    $('div.leave-details .tbl-list tr.grp-set .del')
      .parent()
      .css('display', 'none')
  }
  var leavetypeval = $('#leaveType').val()
  if (leavetypeval == '2') {
    $('.attachedFile').show()
  } else {
    $('.attachedFile').hide()
  }

  $('#leaveType').change(function () {
    var leavetypeval = $('#leaveType').val()
    var value = $(this).attr('attachedFile')
    if (leavetypeval == '2') {
      $('.attachedFile').show()
    } else {
      $('.attachedFile').hide()
    }
  })
  $('.btnleave').click(function () {
    var leaveID = $(this).attr('leaveid')
    var userID = $(this).attr('userID')
    var leavestatus = $(this).attr('leavestatus')

    $.mxajax({
      url: ADMINURL + '/mod/employee-leave/x-employee-leave.inc.php',
      data: {
        xAction: 'getApproveDisPopup',
        leaveID: leaveID,
        userID: userID,
      },
      type: 'POST',
      dataType: 'json',
    }).then(function (data) {
      if (data.err == '0') {
        $('form#leaveStatusForm input#leaveID').val(leaveID)
        $(
          'form#leaveStatusForm select[name^="leaveStatus"] option[value="' +
            leavestatus +
            '"]'
        ).attr('selected', 'selected')
        $('form#leaveStatusForm input#snote').val(data?.leaveData[0]?.snote)
        $('div.att-details .infoData').html(data.str)
        $('div.att-details').show()
      } else {
        $.mxalert({ msg: data.msg, title: 'Error while fetch leave details' })
      }
    })
  })
  $('select#userID').change(function () {
    var selectedUser = $(this).val()
    console.log(selectedUser)
    $.mxajax({
      url: ADMINURL + '/mod/employee-leave/x-employee-leave.inc.php',
      data: {
        xAction: 'getUserBalanceLeave',
        selectedUser: selectedUser,
      },
      type: 'POST',
      dataType: 'json',
    }).then(function (data) {
      console.log(data)
      $('input#yrBalanceLeaves').attr('value', data.data)
    })
  })
  $('a.add.add-set').css('display', 'none')
  $('a.del.row').css('display', 'none')
  var startDate
  var endDate
  $('#fromDate').datepicker({
    dateFormat: 'dd-mm-yy',
  })
  $('#toDate').datepicker({
    dateFormat: 'dd-mm-yy',
  })
  $('#fromDate').change(function () {
    startDate = $(this).val()
  })
  $('#toDate').change(function () {
    endDate = $(this).val()
    $('#toDate').datepicker('option', 'minDate', startDate)
  })

  $('#toDate').change(function () {
    var toDate = $(this).val()
    var fromDate = $('#fromDate').val()
    var now = new Date()
    var daysOfYear = []
    var count = 0
    $.mxajax({
      url: ADMINURL + '/mod/employee-leave/x-employee-leave.inc.php',
      data: {
        xAction: 'getHolidays',
        fromDate: fromDate,
        toDate: toDate,
      },
      type: 'POST',
      dataType: 'json',
    }).then(function (data) {
      if (data.count == 0) {
        $('div.leave-details tr.grp-set:gt(0)').remove()
        for (var d = 0; d < data.data.length; d++) {
          $('div.leave-details a.add-set').trigger('click')
          $('div.leave-details tr.grp-set td')
            .find('#leaveDate_' + d)
            .val(data.data[d].leaveDate)

          $('div.leave-details tr.grp-set td')
            .find('#leaveDateFormat_' + d)
            .val(data.data[d].leaveDateFormat)
          $('div.leave-details tr.grp-set td')
            .find('#lType_' + d)
            .html(
              "<option value='' class='default'>--SELECT TYPE--</option>" +
                data.data[d].selectType
            )
        }
        $('div.leave-details tr.grp-set:eq(' + d + ')').remove()
        $('div.leave-details .tbl-list th.add-grp').css('display', 'none')
        $('div.leave-details .tbl-list tr.grp-set .del')
          .parent()
          .css('display', 'none')
      }
    })
  })
})

$(document).ready(function () {
  var leavetypeval = $('#leaveType').val()
  if (leavetypeval == '2') {
    $('.attachedFile').show()
  } else {
    $('.attachedFile').hide()
  }
  
  $('#leaveType').change(function () {
    var leavetypeval = $('#leaveType').val()
    var value = $(this).attr('attachedFile');
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
      $('form#leaveStatusForm input#leaveID').val(leaveID)
      $(
        'form#leaveStatusForm select[name^="leaveStatus"] option[value="' +
          leavestatus +
          '"]'
      ).attr('selected', 'selected')
      $('div.att-details .infoData').html(data)
      $('div.att-details').show()
    })
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
      }
    })
  })
})
function getCanceledPopup() {
  $('div.att-details .infoData').html(' ')
  $('div.att-details').hide()
  location.reload(true)
}

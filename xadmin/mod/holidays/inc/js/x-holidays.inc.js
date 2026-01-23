$(document).ready(function () {
        deleteLeaveBindClick();
        applyLeaveBindClick();
        //Start: To show leave details on click date
        $('a.date').click(function (e) {
            e.preventDefault();
            $('div.leave-details').fadeOut('fast');
            $(this).parent().find('div.leave-details').fadeIn('fast');
            addBodyClick($(this).parent().find('div.leave-details'));
        });
        // End.
        $('select[name=year]').change(function () {
            window.location.href = ADMINURL + "/holidays-edit/?year=" + $(this).val();
        });
        var fadDur = 300;
        $('table.calender td.months').each(function (index, element) {
            console.log(index);
            $(this).fadeIn(fadDur * index);
        });
});

//Start: To add holiday details
var holiClass = ['off-holiday', 'nat-holiday', 'fest-holiday', 'oth-holiday'];
function addHoliday(thisObj) {
        var wampObj = thisObj.parents('div.leave-details');
        var aUrl = ADMINURL + '/mod/holidays/x-holidays.inc.php';
		var hDate = wampObj.find('input[name="date[]"]').val();
		var holiType = wampObj.find('select[name="holidayType[]"]').val();
		var holiTypeText = wampObj.find('select[name="holidayType[]"] option:selected').text();
        var ahReason = wampObj.find('.holiday-reason').val();
		if(hDate!="" && holiType!="" && ahReason!=""){
			showMxLoader();
            $.mxajax({
                type: "POST",
                url: aUrl,
                data: { 'xAction': 'addHoliday', 'ahReason': ahReason, 'date': hDate, 'holiType': holiType },
                dataType: "json",
            }).then(function (data) {
                console.log(data);
                hideMxLoader();
                if (data == "OK") {
                    thisObj.closest('td').find('a.date').attr('title', holiTypeText + '-' + ahReason);
                    thisObj.siblings('a.cancel').addClass('del-holiday');
                    $.mxalert({ msg: "Holiday added successfully!" });
                    deleteLeaveBindClick();
                } else {
                    $.mxalert({ msg: "Something went wrong!" });
                }
            });
		} else {
            $.mxalert({ msg: "Something went wrong. Please add the Holiday again." });
            return false;
        }
	}
// End.
//Start: To delete holiday.
	function deleteHoliday(date,thisObj){
        if (date) {
            var aUrl = ADMINURL + '/mod/holidays/x-holidays.inc.php';
            showMxLoader();
            $.mxajax({
                type: "POST",
                url: aUrl,
                data: { 'date': date, 'xAction': 'deleteHoliday' },
            }).then(function (data) {
                hideMxLoader();
                if (data == "OK") {
                    var hType = thisObj.parents('ul').find('select[name="holidayType[]"]').val();
                    if (holiClass[hType - 1] !== undefined) {
                        thisObj.parents('td').removeClass(holiClass[hType - 1]);
                    }
                    thisObj.parents('ul').find('select[name="holidayType[]"]').val('');
                    thisObj.parents('ul').find('textarea[name="holidayReason[]"]').val('');
                    thisObj.parents('td').find('a.date').attr('title', '-');
                    $.mxalert({ msg: "Holiday deleted successfully!" });
                    $('div.leave-details').fadeOut('fast');
                    applyLeaveBindClick();
                } else {
                    $.mxalert({ msg: "Something went wrong!" });
                }
            });
		}
	}
// End

	function addBodyClick(element){
		$(document).unbind('click');	
		$(document).bind('click', function(event){
			var $target = $(event.target);
			console.log($target.attr('class'));
			if(!$target.parents().is(element) && !$target.is(element) && !$target.is('a.date')){
				$(element).fadeOut('fast');
				//$(document).unbind('click');
			}
		});
	}
//Start: To detlete holidays confirm box.
	function deleteLeaveBindClick(){
		$('div.leave-details a.del-holiday').unbind('click');
        $('div.leave-details a.del-holiday').bind('click', function (e) {
			e.preventDefault();
			var thisObj = $(this);
			var date = $(this).closest('td').find('input[name="date[]"]').val();
            $mxcpopup = $.mxconfirm({
				top:"20%",
				msg:"Are you sure you want delete this holiday?",
				buttons:{
                    "ok": {
                        "action": function ()
                        {
                            $mxcpopup.hidemxdialog(); deleteHoliday(date, thisObj); return false;
                        }
                    },
                    "Cancel": {
                        "action": function () { $mxcpopup.hidemxdialog(); return false; }
                    },
				}
			});
		});
	}
	// End
    //Start: To apply leave bind click
	function applyLeaveBindClick(){
		$('div.leave-details a.apply').unbind('click');
		$('div.leave-details a.apply').bind('click',function(e){
		e.preventDefault();
			var hType = $(this).parents('ul').find('select[name="holidayType[]"]').val();
			$(this).closest('td').attr("class","").addClass("day");
			if(hType!=""){
				$(this).closest('td').addClass(holiClass[hType-1]);
				addHoliday($(this));
			}
			$('div.leave-details').fadeOut('fast');
		});
        }
    // End
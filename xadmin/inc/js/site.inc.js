function isNumberKey(evt)
{
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode != 46 && charCode > 31 
    && (charCode < 48 || charCode > 57))
     return false;

  return true;
}
function isAlphaKey(evt)
{
    var key = evt.keyCode;
    if (key >= 48 && key <= 57) {
        evt.preventDefault();
    }
}
function isNumberMobile(event,mobileLen=9){
    
    var max = mobileLen;
    var currentVal = $(event.target).val();
    currentVal = currentVal.replace(/\D/g,'');
    $(event.target).val(currentVal);
    if($(event.target).val().length > max) {
        $(event.target).val($(event.target).val().substr(0, max));
    } 
}
function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}
$(document).ready(function () {
    $("form#leaveStatusForm").mxinitform();

})


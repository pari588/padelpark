$(document).ready(function () {
    // Start: To show and hide on requirement replacement data.
        var requirementIsForRplc = $(".requirement-replacement").attr("rel");

        if (requirementIsForRplc=="Yes") {
                 $('.motor-details').show();
        } else {
               $('.motor-details').hide();
        }
    // End.
    if (PAGETYPE == 'edit' || PAGETYPE == 'view') {
     
        //Start: Show and add other input type.
        const otherID = ["#dutyID", "#mountingID", "#typeOfMotorID", "#voltageID", "#shaftExtensionID", "#expectedDeliveryTimeID"];
        $.each(otherID, function (index, value) {
            var classNm = $(value).attr("otherName");
            if (PAGETYPE == 'edit') {
                var text = $(value).find("option:selected").text();
            } else {
                var text = $(value).attr("rel");
            }   
            if (text == "Other") {
                $('.' + classNm).show();
            } else {
                $('.' + classNm).hide();
            }
        });
        // End.
    }
});
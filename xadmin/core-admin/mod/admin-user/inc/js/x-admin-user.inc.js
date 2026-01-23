$(document).ready(function () {
    var frm = $('form#frmAddEdit')
    frm.mxinitform({
        pcallback: validateUserPin,
    });

    $("a.resetLeaveCount").click(function () {
        var userID = $(this).data("id");
        showMxLoader();
        $.mxajax({
            url: MODINCURL,
            type: "POST",
            data: {
                userID: userID,
                xAction: "resetUnauthorizedLeaves"
            },
            dataType: "json",
        }).then(function (resp) {
            hideMxLoader();
            $.mxalert({ msg: resp.msg });
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        });
        return false;
    });
});

function validateUserPin(frm, el, p) { 
    if (frm.mxvalidate() !== false) {
        var userPin = $('#userPin').val();
        var userID = $('#userID').val();
        if(userPin){
            console.log(MODINCURL);
            $.mxajax({
                url: MODINCURL,
                data: { userPin: userPin, userID: userID, xAction: "validateUserPin" },
                type: 'post',
                dataType: "json"
            }).then(function (resp) {
                console.log(resp);
                if (resp.err == 0) {
                    mxSubmitForm(frm, el, p);
                } else {
                    $.mxalert({ msg: resp.msg });
                }
            });
        }else{
            mxSubmitForm(frm, el, p);
        }
    }
}
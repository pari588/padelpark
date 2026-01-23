$(document).ready(function () {
    MXTRASHPRE = trashRecord;
    $(".transaction-type input[type='radio']").click(function () {
        validate();
    });
    $(".payment-mode input[type='radio']").click(function () {
        if ($(this).val() == "Cheque") {
            $('.transaction-no').show();
        } else {
            $('.transaction-no').hide();
        }
    });
    if (PAGETYPE == 'edit' || PAGETYPE == 'view') {
        validate();
        var paymentMode = $(".payment-mode input[type='radio']:checked").val();
        if (paymentMode == "Cheque") {
            $('.transaction-no').show();
        } else {
            $('.transaction-no').hide();
        }
        if (PAGETYPE == "view") {
            if ($("li.transaction-type").text().split("Transaction Type")[1] == "Credit") {
                $('.category').hide();
                $('.payment-mode').show();
            }
        }
    }
});


function validate() {
    var transactionType = $(".transaction-type input[type='radio']:checked").val();
    if (transactionType == 1) {
        $('#pettyCashCatID').val('');
        $('.category').removeAttr('validate');
        $('.category').hide();
        $('.payment-mode').show();
    } else {
        $('.category').attr('validate', 'required');
        $('.category').show();
        $('.payment-mode').hide();
    }
}


function trashRecord(data) {
    var aUrl = SITEURL + "/xadmin/mod/petty-cash-book/x-petty-cash-book.inc.php";
    if (data.xAction == "trash") {
        var status = 0;
    } else {
        var status = 1;
    }

    $.mxajax({
        url: aUrl,
        data: { "xAction": "updateBalanceAfterTrash", "pettyCashBookID": data.id, "status": status },
        type: "POST",
        dataType: "json",
    }).then(function (response) {
        if (response.err == 0) {
            mxtrash(data);
        }
    });
}

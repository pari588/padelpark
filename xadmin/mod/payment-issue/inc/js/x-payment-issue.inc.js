$(document).ready(function () {
  MXTRASHPRE = trashRecord;
});

function trashRecord(data) {
  var aUrl = SITEURL + "/xadmin/mod/payment-issue/x-payment-issue.inc.php";
  if (data.xAction == "trash") {
    var status = 0;
  } else {
    var status = 1;
  }

  $.mxajax({
    url: aUrl,
    data: {
      xAction: "updateBalanceAfterTrash",
      paymentIssueID: data.id,
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

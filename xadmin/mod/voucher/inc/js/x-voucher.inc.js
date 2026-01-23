$(document).ready(function () {
    $("a.preview-voucher").click(function () {
        window.open(ADMINURL + '/mod/voucher/inc/x-invoice-priview.php?id=' + $(this).attr("voucherID"), 'Print', 'width=870,height=650,resizable=1,toolbar=0,menubar=1,scrollbars=1,status=1');
        return false;
    });
    $("a.print-voucher").click(function () {
        var action = $(this).attr('rel');
        window.open(ADMINURL + '/mod/voucher/inc/x-invoice-pdf.php?xAction=view&action=' + action + '&id=' + $(this).attr("voucherID"), 'Print', 'width=870,height=650,resizable=1,toolbar=0,menubar=1,scrollbars=1,status=1');
        return false;
    });
    //Start: Get voucher ID to make that voucher's zip.
    $("a.download-zip").click(function () {
        if ($(':checkbox:checked').length > 0) {
            var arrID = [];
            $("table.tbl-list tr:not(:first)").each(function () {
                var input = $(this).find("input:eq(0)");

                if (input.is(":checked")) {
                    if (input.val())
                        arrID.push(input.val());
                }
            });
            var voucherIDs = arrID.join(",");
            window.open(ADMINURL + '/mod/voucher/x-voucher.inc.php?xAction=creatVoucherZip&voucherID=' + voucherIDs);
            return false;
        } else {
            $.mxalert({ msg: "Please select Voucher id to download Zip." });
        }
    });
  //END: Get voucher ID to make that voucher's zip.
});
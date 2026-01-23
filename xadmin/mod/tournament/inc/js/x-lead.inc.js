$(document).ready(function () {
    if (PAGETYPE == 'edit' || PAGETYPE == 'view') {
        $(".lead-detail-date").show();
        $("a.row").removeClass();
    }
    if (PAGETYPE == 'view') {
        $(".view-only").show();
    }
});
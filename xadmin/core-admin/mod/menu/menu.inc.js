$(document).ready(function () {
    $("select#menuType").change(function () {
        var menuType = $(this).val();
        $("li.dynamic,li.static,li.exlink").hide();

        if (menuType != "") {
            $("li." + menuType).show();
        }
    });
    $("select#menuType").trigger("change");

    $("select#templateIDS").change(function () {
        if ($(this).val() != "") {
            var seoUri = $("select#templateIDS option:selected").text();
            if (seoUri != "") {
                $("input#seoUri").val(seoUri);
            }
        } else {
            $("input#seoUri").val("");
        }
    });

    initDragSort(6);
});
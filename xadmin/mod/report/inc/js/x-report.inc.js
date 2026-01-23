$(document).ready(function () {
   $("#listcheckBtn").on("click", function (){
            checkList();
   })

/*------------------ For Exporting File ---------------------*/
   $("#exportBtnReport").click(function() {
    var csv = []; 
 var fromDate = $("#fromDate").val();
        var toDate = $("#toDate").val();
        csv.push(['From Date: ' + fromDate, 'To Date: ' + toDate]);
    
    function parseTableSectionToCSV(sectionSelector) {
        $(sectionSelector + " tr").each(function() {
            var rowData = [];
            $(this).find(" th, td").each(function() {
                var colspan = parseInt($(this).attr("colspan")) || 1;
                var cellContent = $(this).text().trim();
                // Repeat cell content based on colspan
                if (cellContent != '') {
                    for (var i = 1; i <= colspan; i++) {
                        if (i == colspan) {
                            rowData.push('"' + cellContent.replace(/"/g, '""') + '"');
                        } else {
                            rowData.push('""');
                        }
                    }
            
                }
            });       
            csv.push(rowData.join(","));
            
        });
    }
    
    // Parse thead, tbody, and tfoot sections

    parseTableSectionToCSV("thead");
    parseTableSectionToCSV("tbody");
    parseTableSectionToCSV("tfoot");

    var csvContent = csv.join("\n");
    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);
    var link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", "account-report.csv");
    document.body.appendChild(link);
    link.click();

    document.body.removeChild(link);		
});

/*------------------ End For Exporting File ---------------------*/
});


function checkList() {
  showMxLoader();
    var fromDate = $("#fromDate").val();
    var toDate = $("#toDate").val();
    $.mxajax({
        url: ADMINURL + '/mod/report/x-report.inc.php',
        data: {
          xAction: 'checkReportList',
          fromDate: fromDate,
          toDate: toDate,
        },
        type: 'POST',
        dataType: 'json',
    }).then(function (response) {  
      hideMxLoader();
          $(".credit-debit-list").html(response.str);
      })
}

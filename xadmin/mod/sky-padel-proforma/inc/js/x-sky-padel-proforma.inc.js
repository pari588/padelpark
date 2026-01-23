// Sky Padel Proforma Invoice JavaScript

$(document).ready(function() {
    // Auto-populate client details when quotation is selected
    $('select[name="quotationID"]').on('change', function() {
        var quotationID = $(this).val();
        if (quotationID) {
            // Could add AJAX to fetch quotation details and auto-fill
            console.log('Quotation selected:', quotationID);
        }
    });

    // Initialize calculations
    if (typeof calculateTotals === 'function') {
        calculateTotals();
    }
});

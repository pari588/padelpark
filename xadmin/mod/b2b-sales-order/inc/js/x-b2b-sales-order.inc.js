// B2B Sales Order Module JavaScript

$(document).ready(function() {
    $('#orderForm').on('submit', function(e) {
        e.preventDefault();
        saveOrder();
    });
});

function onDistributorChange() {
    var select = document.getElementById('distributorID');
    var option = select.options[select.selectedIndex];

    if (!option.value) {
        $('#creditInfo').hide();
        return;
    }

    var creditLimit = parseFloat(option.dataset.credit) || 0;
    var currentBalance = parseFloat(option.dataset.balance) || 0;
    var availableCredit = creditLimit - currentBalance;
    var paymentTerms = option.dataset.terms || 30;
    taxZone = option.dataset.taxzone || 'Local';

    $('#creditLimit').text('Rs. ' + formatNumber(creditLimit));
    $('#currentBalance').text('Rs. ' + formatNumber(currentBalance)).css('color', currentBalance > 0 ? '#dc3545' : '#198754');
    $('#availableCredit').text('Rs. ' + formatNumber(availableCredit)).css('color', availableCredit > 0 ? '#198754' : '#dc3545');
    $('#paymentTermsDays').val(paymentTerms);
    $('#creditInfo').show();

    updateTaxDisplay();
    calculateTotal();
}

function updateTaxDisplay() {
    if (taxZone == 'Interstate' || taxZone == 'Export') {
        $('#cgstRow, #sgstRow').hide();
        $('#igstRow').show();
    } else {
        $('#cgstRow, #sgstRow').show();
        $('#igstRow').hide();
    }
}

function openProductSearch() {
    $('#productSearch').val('');
    $('#productResults').html('<p style="text-align: center; color: #999; padding: 40px 0;">Type to search products</p>');
    $('#productModal').fadeIn(200);
    setTimeout(function() { $('#productSearch').focus(); }, 300);
}

function closeProductModal() {
    $('#productModal').fadeOut(200);
}

var searchTimer;
var isSearching = false;

function searchProducts() {
    if (isSearching) {
        console.log('Search already in progress, skipping...');
        return;
    }

    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
        var search = $('#productSearch').val().trim();
        console.log('Searching for:', search);

        if (search.length < 2) {
            $('#productResults').html('<p style="text-align: center; color: #999; padding: 20px;">Type at least 2 characters and click Search</p>');
            return;
        }

        isSearching = true;
        $('#productResults').html('<p style="text-align: center; padding: 20px;"><i class="fa fa-spinner fa-spin"></i> Searching...</p>');

        var requestData = {
            xAction: 'GET_PRODUCTS',
            warehouseID: $('[name="warehouseID"]').val() || '0',
            search: search
        };
        console.log('Sending request:', requestData);

        $.ajax({
            url: ADMINURL + '/mod/b2b-sales-order/x-b2b-sales-order.inc.php',
            type: 'POST',
            data: requestData,
            dataType: 'json',
            success: function(res) {
                isSearching = false;
                console.log('AJAX success callback executed');
                console.log('Product search response:', res);
                console.log('Response err:', res.err);
                console.log('Response data:', res.data);
                console.log('Data length:', res.data ? res.data.length : 'no data');

                if (!res.data || res.data.length === 0) {
                    $('#productResults').html('<p style="text-align: center; padding: 20px;">No products found<br>Message: ' + (res.msg || 'no message') + '</p>');
                    return;
                }

                if (res.err == 0 && res.data && res.data.length > 0) {
                    var html = '<table class="tbl-list" style="width: 100%; font-size: 13px;">';
                    html += '<thead><tr style="background: #e9ecef;"><th style="padding: 8px;">SKU</th><th style="padding: 8px;">Product</th><th style="padding: 8px;">HSN</th><th style="padding: 8px; text-align: right;">Price</th><th style="padding: 8px;">GST</th><th style="padding: 8px;">Stock</th><th style="padding: 8px;"></th></tr></thead><tbody>';

                    res.data.forEach(function(p) {
                        var pJson = JSON.stringify(p).replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        html += '<tr style="border-bottom: 1px solid #ddd;">';
                        html += '<td style="padding: 8px;"><strong>' + p.productSKU + '</strong></td>';
                        html += '<td style="padding: 8px;">' + p.productName + '</td>';
                        html += '<td style="padding: 8px;">' + (p.hsnCode || '-') + '</td>';
                        html += '<td style="padding: 8px; text-align: right;">Rs. ' + formatNumber(p.basePrice || 0) + '</td>';
                        html += '<td style="padding: 8px;">' + (p.gstRate || 18) + '%</td>';
                        html += '<td style="padding: 8px;">' + (p.availableQty || 0) + ' ' + (p.uom || 'Pcs') + '</td>';
                        html += '<td style="padding: 8px;"><button type="button" style="background: #0d6efd; color: #fff; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer;" onclick=\'addProduct(' + pJson + ')\'>Add</button></td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                    $('#productResults').html(html);
                } else {
                    console.log('No products found or error in response');
                    $('#productResults').html('<p style="text-align: center; color: #999; padding: 20px;">No products found</p>');
                }
            },
            error: function(xhr, status, error) {
                isSearching = false;
                console.error('Product search AJAX error:', error);
                console.error('XHR status:', xhr.status);
                console.error('XHR responseText:', xhr.responseText);
                $('#productResults').html('<p style="text-align: center; color: #dc3545; padding: 20px;">Error: ' + error + '<br>Status: ' + xhr.status + '</p>');
            }
        });
    }, 300);
}

function addProduct(product) {
    rowCount++;
    var row = rowCount;

    // Remove empty row if exists
    $('#emptyRow').remove();

    var html = '<tr data-row="' + row + '">';
    html += '<td>' + row + '</td>';
    html += '<td>' + product.productSKU + '</td>';
    html += '<td>' + product.productName;
    html += '<input type="hidden" name="items[' + row + '][productID]" value="' + product.productID + '">';
    html += '<input type="hidden" name="items[' + row + '][productSKU]" value="' + product.productSKU + '">';
    html += '<input type="hidden" name="items[' + row + '][productName]" value="' + product.productName + '">';
    html += '<input type="hidden" name="items[' + row + '][hsnCode]" value="' + (product.hsnCode || '') + '">';
    html += '</td>';
    html += '<td><input type="number" name="items[' + row + '][quantity]" class="form-control input-sm qty-input" value="1" min="0.01" step="0.01" onchange="calculateRow(' + row + ')"></td>';
    html += '<td><input type="hidden" name="items[' + row + '][uom]" value="' + (product.uom || 'Pcs') + '">' + (product.uom || 'Pcs') + '</td>';
    html += '<td><input type="number" name="items[' + row + '][unitPrice]" class="form-control input-sm text-right price-input" value="' + product.basePrice + '" min="0" step="0.01" onchange="calculateRow(' + row + ')"></td>';
    html += '<td><input type="number" name="items[' + row + '][discountPercent]" class="form-control input-sm disc-input" value="0" min="0" max="100" step="0.01" onchange="calculateRow(' + row + ')"></td>';
    html += '<td><input type="hidden" name="items[' + row + '][gstRate]" value="' + product.gstRate + '">' + product.gstRate + '%</td>';
    html += '<td class="text-right line-total"><strong>Rs. ' + formatNumber(product.basePrice) + '</strong><input type="hidden" name="items[' + row + '][lineTotal]" value="' + product.basePrice + '"></td>';
    html += '<td><button type="button" class="btn btn-xs btn-danger" onclick="removeRow(' + row + ')"><i class="fa fa-times"></i></button></td>';
    html += '</tr>';

    $('#itemsBody').append(html);
    calculateRow(row);

    closeProductModal();
}

function removeRow(row) {
    $('tr[data-row="' + row + '"]').remove();
    if ($('#itemsBody tr').length == 0) {
        $('#itemsBody').html('<tr id="emptyRow"><td colspan="10" class="text-center text-muted">No items added</td></tr>');
    }
    calculateTotal();
}

function calculateRow(row) {
    var $row = $('tr[data-row="' + row + '"]');
    var qty = parseFloat($row.find('.qty-input').val()) || 0;
    var price = parseFloat($row.find('.price-input').val()) || 0;
    var discPercent = parseFloat($row.find('.disc-input').val()) || 0;
    var gstRate = parseFloat($row.find('input[name$="[gstRate]"]').val()) || 0;

    var lineTotal = qty * price;
    var discountAmount = (lineTotal * discPercent) / 100;
    var taxableValue = lineTotal - discountAmount;

    // GST calculation
    var cgstAmount = 0, sgstAmount = 0, igstAmount = 0;
    if (taxZone == 'Interstate' || taxZone == 'Export') {
        igstAmount = (taxableValue * gstRate) / 100;
    } else {
        cgstAmount = (taxableValue * gstRate / 2) / 100;
        sgstAmount = (taxableValue * gstRate / 2) / 100;
    }

    var lineTotalWithTax = taxableValue + cgstAmount + sgstAmount + igstAmount;

    // Update hidden fields
    $row.find('input[name$="[lineTotal]"]').val(lineTotalWithTax.toFixed(2));
    $row.find('.line-total').html('<strong>Rs. ' + formatNumber(lineTotalWithTax) + '</strong><input type="hidden" name="items[' + row + '][lineTotal]" value="' + lineTotalWithTax.toFixed(2) + '">');

    calculateTotal();
}

function calculateTotal() {
    var subtotal = 0;
    var totalCgst = 0, totalSgst = 0, totalIgst = 0;

    $('#itemsBody tr:not(#emptyRow)').each(function() {
        var row = $(this).data('row');
        var qty = parseFloat($(this).find('.qty-input').val()) || 0;
        var price = parseFloat($(this).find('.price-input').val()) || 0;
        var discPercent = parseFloat($(this).find('.disc-input').val()) || 0;
        var gstRate = parseFloat($(this).find('input[name$="[gstRate]"]').val()) || 0;

        var lineTotal = qty * price;
        var discountAmount = (lineTotal * discPercent) / 100;
        var taxableValue = lineTotal - discountAmount;

        subtotal += taxableValue;

        if (taxZone == 'Interstate' || taxZone == 'Export') {
            totalIgst += (taxableValue * gstRate) / 100;
        } else {
            totalCgst += (taxableValue * gstRate / 2) / 100;
            totalSgst += (taxableValue * gstRate / 2) / 100;
        }
    });

    // Apply order-level discount
    var discountType = $('[name="discountType"]').val();
    var discountValue = parseFloat($('[name="discountValue"]').val()) || 0;
    var discountAmount = 0;

    if (discountType == 'Percent') {
        discountAmount = (subtotal * discountValue) / 100;
    } else {
        discountAmount = discountValue;
    }

    var taxableAmount = subtotal - discountAmount;
    var totalAmount = taxableAmount + totalCgst + totalSgst + totalIgst;

    // Update summary
    $('#summarySubtotal').text('Rs. ' + formatNumber(subtotal));
    $('#summaryDiscount').text('- Rs. ' + formatNumber(discountAmount));
    $('#summaryTaxable').text('Rs. ' + formatNumber(taxableAmount));
    $('#summaryCgst').text('Rs. ' + formatNumber(totalCgst));
    $('#summarySgst').text('Rs. ' + formatNumber(totalSgst));
    $('#summaryIgst').text('Rs. ' + formatNumber(totalIgst));
    $('#summaryTotal').text('Rs. ' + formatNumber(totalAmount));

    // Update hidden fields
    $('#subtotalField').val(subtotal.toFixed(2));
    $('#discountAmountField').val(discountAmount.toFixed(2));
    $('#taxableAmountField').val(taxableAmount.toFixed(2));
    $('#cgstAmountField').val(totalCgst.toFixed(2));
    $('#sgstAmountField').val(totalSgst.toFixed(2));
    $('#igstAmountField').val(totalIgst.toFixed(2));
    $('#totalAmountField').val(totalAmount.toFixed(2));

    // Update credit info
    $('#orderTotal').text('Rs. ' + formatNumber(totalAmount));
}

function saveOrder() {
    // Validate
    if (!$('#distributorID').val()) {
        toastr.error('Please select a distributor');
        return;
    }

    if ($('#itemsBody tr:not(#emptyRow)').length == 0) {
        toastr.error('Please add at least one item');
        return;
    }

    // Collect items
    var items = [];
    $('#itemsBody tr:not(#emptyRow)').each(function() {
        var row = $(this).data('row');
        items.push({
            productID: $(this).find('input[name$="[productID]"]').val(),
            productSKU: $(this).find('input[name$="[productSKU]"]').val(),
            productName: $(this).find('input[name$="[productName]"]').val(),
            hsnCode: $(this).find('input[name$="[hsnCode]"]').val(),
            quantity: $(this).find('.qty-input').val(),
            uom: $(this).find('input[name$="[uom]"]').val(),
            unitPrice: $(this).find('.price-input').val(),
            discountPercent: $(this).find('.disc-input').val(),
            gstRate: $(this).find('input[name$="[gstRate]"]').val(),
            lineTotal: $(this).find('input[name$="[lineTotal]"]').val()
        });
    });

    var formData = {
        xAction: orderID > 0 ? 'UPDATE' : 'ADD',
        orderID: orderID,
        distributorID: $('#distributorID').val(),
        warehouseID: $('#warehouseID').val(),
        orderDate: $('[name="orderDate"]').val(),
        paymentTermsDays: $('#paymentTermsDays').val(),
        discountType: $('[name="discountType"]').val(),
        discountValue: $('[name="discountValue"]').val(),
        discountAmount: $('#discountAmountField').val(),
        cgstAmount: $('#cgstAmountField').val(),
        sgstAmount: $('#sgstAmountField').val(),
        igstAmount: $('#igstAmountField').val(),
        shippingAddress: $('[name="shippingAddress"]').val(),
        notes: $('[name="notes"]').val(),
        items: JSON.stringify(items)
    };

    $('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: ADMINURL + '/mod/b2b-sales-order/x-b2b-sales-order.inc.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(res) {
            $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Save Order');

            if (res.err == 0) {
                toastr.success(res.msg);
                if (res.param) {
                    window.location.href = ADMINURL + '/b2b-sales-order-add/?' + res.param;
                }
            } else {
                toastr.error(res.msg);
            }
        },
        error: function() {
            $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Save Order');
            toastr.error('An error occurred. Please try again.');
        }
    });
}

function confirmOrder() {
    if (!confirm('Confirm this order? This will lock it for editing.')) return;

    $.ajax({
        url: ADMINURL + '/mod/b2b-sales-order/x-b2b-sales-order.inc.php',
        type: 'POST',
        data: {
            xAction: 'CONFIRM',
            orderID: orderID
        },
        dataType: 'json',
        success: function(res) {
            if (res.err == 0) {
                toastr.success(res.msg);
                location.reload();
            } else {
                toastr.error(res.msg);
            }
        },
        error: function() {
            toastr.error('Error confirming order');
        }
    });
}

function formatNumber(num) {
    return parseFloat(num).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

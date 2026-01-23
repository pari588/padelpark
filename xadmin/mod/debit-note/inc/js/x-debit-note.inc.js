/**
 * Debit Note Module JavaScript
 */

$(document).ready(function() {
    // Entity type change handler
    $('[name="entityType"]').on('change', function() {
        var type = $(this).val();
        entityType = type;

        // Hide all entity selectors
        $('.entity-distributor, .entity-location, .entity-customer').hide();

        // Show relevant selector
        if (type === 'Distributor') {
            $('.entity-distributor').show();
        } else if (type === 'Location') {
            $('.entity-location').show();
        } else if (type === 'Customer') {
            $('.entity-customer').show();
        }

        // Clear entity name
        $('#entityNameDisplay').val('');
    });

    // Distributor select change
    $('#distributorID').on('change', function() {
        var distributorID = $(this).val();
        var selected = $(this).find('option:selected');
        $('#entityNameDisplay').val(selected.text());
        $('[name="entityName"]').val(selected.text());

        // Fetch distributor details via AJAX
        if (distributorID) {
            $.mxajax({
                url: MODINCURL,
                data: {
                    xAction: 'GET_ENTITY_DETAILS',
                    entityType: 'Distributor',
                    entityID: distributorID
                }
            }).then(function(res) {
                console.log('Distributor AJAX response:', res);
                if (res.err === 0 && res.data) {
                    $('[name="entityGSTIN"]').val(res.data.gstin || '');
                } else {
                    console.error('Failed to fetch distributor details:', res.msg);
                }
            }).catch(function(err) {
                console.error('AJAX error:', err);
            });
        } else {
            $('[name="entityGSTIN"]').val('');
        }
    });

    // Location select change
    $('#locationID').on('change', function() {
        var locationID = $(this).val();
        var selected = $(this).find('option:selected');
        $('#entityNameDisplay').val(selected.text());
        $('[name="entityName"]').val(selected.text());

        // Fetch location details via AJAX
        if (locationID) {
            $.mxajax({
                url: MODINCURL,
                data: {
                    xAction: 'GET_ENTITY_DETAILS',
                    entityType: 'Location',
                    entityID: locationID
                }
            }).then(function(res) {
                if (res.err === 0 && res.data) {
                    $('[name="entityGSTIN"]').val(res.data.gstin || '');
                }
            });
        } else {
            $('[name="entityGSTIN"]').val('');
        }
    });

    // Customer name change
    $('#customerName').on('input', function() {
        $('#entityNameDisplay').val($(this).val());
        $('[name="entityName"]').val($(this).val());
    });

    // Amount calculation handlers
    $('[name="subtotal"], [name="discountAmount"], [name="cgstRate"], [name="sgstRate"], [name="igstRate"]').on('input', calculateTotal);

    // Form submit - collect items
    $('#frmAddEdit').on('submit', function(e) {
        collectItems();
    });

    // Initialize
    if (items && items.length > 0) {
        renderItems();
    }
});

// Calculate total amount
function calculateTotal() {
    var subtotal = parseFloat($('[name="subtotal"]').val()) || 0;
    var discount = parseFloat($('[name="discountAmount"]').val()) || 0;
    var cgstRate = parseFloat($('[name="cgstRate"]').val()) || 0;
    var sgstRate = parseFloat($('[name="sgstRate"]').val()) || 0;
    var igstRate = parseFloat($('[name="igstRate"]').val()) || 0;

    var taxable = subtotal - discount;
    var cgst = Math.round(taxable * cgstRate / 100 * 100) / 100;
    var sgst = Math.round(taxable * sgstRate / 100 * 100) / 100;
    var igst = Math.round(taxable * igstRate / 100 * 100) / 100;
    var total = taxable + cgst + sgst + igst;

    $('[name="totalAmount"]').val(total.toFixed(2));
}

// Add item row
function addItemRow() {
    var html = `
        <tr class="item-row item-editable">
            <td class="row-num"></td>
            <td><input type="text" class="item-name" placeholder="Description" style="width:100%"></td>
            <td><input type="text" class="item-sku" placeholder="SKU" style="width:100%"></td>
            <td><input type="number" class="item-qty" value="1" min="1" style="width:60px" onchange="calculateItemRow(this)"></td>
            <td><input type="number" class="item-price" value="0" step="0.01" style="width:80px" onchange="calculateItemRow(this)"></td>
            <td><input type="number" class="item-gst" value="18" step="0.01" style="width:50px" onchange="calculateItemRow(this)"></td>
            <td class="item-tax" align="right">Rs. 0.00</td>
            <td class="item-total" align="right"><strong>Rs. 0.00</strong></td>
            <td><button type="button" onclick="removeItemRow(this)" class="btn btn-sm btn-danger">X</button></td>
        </tr>
    `;

    $('#noItemsRow').hide();
    $('#itemsBody').append(html);
    updateRowNumbers();
}

// Calculate single item row
function calculateItemRow(input) {
    var $row = $(input).closest('tr');
    var qty = parseFloat($row.find('.item-qty').val()) || 0;
    var price = parseFloat($row.find('.item-price').val()) || 0;
    var gstRate = parseFloat($row.find('.item-gst').val()) || 0;

    var subtotal = qty * price;
    var tax = Math.round(subtotal * gstRate / 100 * 100) / 100;
    var total = subtotal + tax;

    $row.find('.item-tax').text('Rs. ' + tax.toFixed(2));
    $row.find('.item-total').html('<strong>Rs. ' + total.toFixed(2) + '</strong>');

    // Update totals
    updateTotals();
}

// Remove item row
function removeItemRow(btn) {
    $(btn).closest('tr').remove();
    updateRowNumbers();
    updateTotals();

    if ($('#itemsBody .item-row').length === 0) {
        $('#noItemsRow').show();
    }
}

// Update row numbers
function updateRowNumbers() {
    var num = 0;
    $('#itemsBody .item-row').each(function() {
        num++;
        $(this).find('.row-num').text(num);
    });
}

// Update totals from items
function updateTotals() {
    var subtotal = 0;
    var totalTax = 0;

    $('#itemsBody .item-row').each(function() {
        var qty = parseFloat($(this).find('.item-qty').val()) || 0;
        var price = parseFloat($(this).find('.item-price').val()) || 0;
        var gstRate = parseFloat($(this).find('.item-gst').val()) || 0;

        var rowSubtotal = qty * price;
        var rowTax = Math.round(rowSubtotal * gstRate / 100 * 100) / 100;

        subtotal += rowSubtotal;
        totalTax += rowTax;
    });

    $('[name="subtotal"]').val(subtotal.toFixed(2));
    calculateTotal();
}

// Collect items into JSON
function collectItems() {
    var itemsData = [];

    $('#itemsBody .item-row').each(function() {
        var $row = $(this);

        // Check if editable row or display row
        if ($row.hasClass('item-editable')) {
            var qty = parseFloat($row.find('.item-qty').val()) || 0;
            var price = parseFloat($row.find('.item-price').val()) || 0;
            var gstRate = parseFloat($row.find('.item-gst').val()) || 0;

            var subtotal = qty * price;
            var tax = Math.round(subtotal * gstRate / 100 * 100) / 100;
            var cgst = tax / 2;
            var sgst = tax / 2;

            itemsData.push({
                productID: 0,
                productSKU: $row.find('.item-sku').val() || '',
                productName: $row.find('.item-name').val() || '',
                hsnCode: '',
                quantity: qty,
                uom: 'Pcs',
                unitPrice: price,
                discountPercent: 0,
                discountAmount: 0,
                taxableAmount: subtotal,
                gstRate: gstRate,
                cgstAmount: cgst,
                sgstAmount: sgst,
                igstAmount: 0,
                totalAmount: subtotal + tax
            });
        } else {
            // Existing item from data attribute
            var itemData = $row.data('item');
            if (itemData) {
                itemsData.push(itemData);
            }
        }
    });

    $('#itemsJson').val(JSON.stringify(itemsData));
}

// Render items from array
function renderItems() {
    $('#noItemsRow').hide();
    $('#itemsBody .item-row').remove();

    items.forEach(function(item, idx) {
        var tax = parseFloat(item.cgstAmount || 0) + parseFloat(item.sgstAmount || 0) + parseFloat(item.igstAmount || 0);
        var html = `
            <tr class="item-row" data-item='${JSON.stringify(item).replace(/'/g, "&#39;")}'>
                <td>${idx + 1}</td>
                <td>${escapeHtml(item.productName || '')}</td>
                <td>${escapeHtml(item.productSKU || '')}</td>
                <td align="center">${parseFloat(item.quantity || 0).toFixed(0)}</td>
                <td align="right">Rs. ${parseFloat(item.unitPrice || 0).toFixed(2)}</td>
                <td align="center">${item.gstRate || 18}%</td>
                <td align="right">Rs. ${tax.toFixed(2)}</td>
                <td align="right"><strong>Rs. ${parseFloat(item.totalAmount || 0).toFixed(2)}</strong></td>
                ${isDraft ? '<td><button type="button" onclick="removeItemRow(this)" class="btn btn-sm btn-danger">X</button></td>' : ''}
            </tr>
        `;
        $('#itemsBody').append(html);
    });
}

// Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Approve debit note
function approveDebitNote() {
    if (!confirm('Are you sure you want to approve this Debit Note? This will update outstanding and deduct stock.')) {
        return;
    }

    $.mxajax({
        url: ADMINURL + '/mod/debit-note/x-debit-note.inc.php',
        data: {
            xAction: 'APPROVE',
            debitNoteID: debitNoteID
        }
    }).then(function(res) {
        if (res.err === 0) {
            alert(res.msg || 'Debit Note approved successfully');
            location.reload();
        } else {
            alert(res.msg || 'Failed to approve Debit Note');
        }
    });
}

// Cancel debit note
function cancelDebitNote() {
    if (!confirm('Are you sure you want to cancel this Debit Note?')) {
        return;
    }

    $.mxajax({
        url: ADMINURL + '/mod/debit-note/x-debit-note.inc.php',
        data: {
            xAction: 'CANCEL',
            debitNoteID: debitNoteID
        }
    }).then(function(res) {
        if (res.err === 0) {
            alert(res.msg || 'Debit Note cancelled');
            location.reload();
        } else {
            alert(res.msg || 'Failed to cancel Debit Note');
        }
    });
}

// Show collect modal
function showCollectModal() {
    $('#collectModal').show();
}

// Close collect modal
function closeCollectModal() {
    $('#collectModal').hide();
}

// Submit collection
function submitCollection() {
    var collectAmount = parseFloat($('#collectAmount').val()) || 0;
    var paymentMethod = $('#collectPaymentMethod').val();
    var paymentReference = $('#collectPaymentRef').val();
    var notes = $('#collectNotes').val();

    if (collectAmount <= 0) {
        alert('Please enter a valid collection amount');
        return;
    }

    if (collectAmount > balanceAmount) {
        alert('Collection amount cannot exceed balance amount');
        return;
    }

    $.mxajax({
        url: ADMINURL + '/mod/debit-note/x-debit-note.inc.php',
        data: {
            xAction: 'COLLECT',
            debitNoteID: debitNoteID,
            collectAmount: collectAmount,
            paymentMethod: paymentMethod,
            paymentReference: paymentReference,
            notes: notes
        }
    }).then(function(res) {
        if (res.err === 0) {
            alert(res.msg || 'Collection recorded successfully');
            location.reload();
        } else {
            alert(res.msg || 'Failed to record collection');
        }
    });
}

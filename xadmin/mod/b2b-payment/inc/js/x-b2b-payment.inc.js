// B2B Payment Module JavaScript

$(document).ready(function() {
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        savePayment();
    });
});

function togglePaymentFields() {
    var mode = $('#paymentMode').val();
    if (mode == 'Cheque') {
        $('#chequeFields, #chequeDateField').show();
        $('#bankNameField').show();
    } else if (mode == 'Cash' || mode == 'UPI') {
        $('#chequeFields, #chequeDateField').hide();
        $('#bankNameField').hide();
    } else {
        $('#chequeFields, #chequeDateField').hide();
        $('#bankNameField').show();
    }
}

function loadUnpaidInvoices() {
    var distributorID = $('#distributorID').val();
    if (!distributorID) {
        $('#invoiceList').html('<p class="text-center text-muted">Select a distributor to see unpaid invoices</p>');
        return;
    }

    $('#invoiceList').html('<p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading invoices...</p>');

    $.post(ADMINURL + '/mod/b2b-payment/x-b2b-payment.inc.php', {
        xAction: 'GET_UNPAID',
        distributorID: distributorID
    }, function(res) {
        if (res.err == 0) {
            if (res.data.length == 0) {
                $('#invoiceList').html('<p class="text-center text-success">No unpaid invoices</p>');
                return;
            }

            var html = '<table class="table table-bordered table-hover" style="font-size: 13px;">';
            html += '<thead><tr><th width="5%"></th><th>Invoice</th><th>Date</th><th>Due Date</th><th class="text-right">Total</th><th class="text-right">Balance</th><th width="20%">Allocate</th></tr></thead>';
            html += '<tbody>';

            res.data.forEach(function(inv) {
                var isOverdue = new Date(inv.dueDate) < new Date();
                var rowClass = isOverdue ? 'style="background: #fff3cd;"' : '';
                var preSelected = (preSelectInvoice == inv.invoiceID);

                html += '<tr ' + rowClass + '>';
                html += '<td><input type="checkbox" class="allocate-check" data-invoice="' + inv.invoiceID + '" data-balance="' + inv.balanceAmount + '" ' + (preSelected ? 'checked' : '') + '></td>';
                html += '<td><strong>' + inv.invoiceNo + '</strong></td>';
                html += '<td>' + formatDate(inv.invoiceDate) + '</td>';
                html += '<td>' + formatDate(inv.dueDate) + (isOverdue ? ' <span class="label label-danger">Overdue</span>' : '') + '</td>';
                html += '<td class="text-right">Rs. ' + formatNumber(inv.totalAmount) + '</td>';
                html += '<td class="text-right" style="color: #dc3545;">Rs. ' + formatNumber(inv.balanceAmount) + '</td>';
                html += '<td><input type="number" class="form-control input-sm allocate-amount" data-invoice="' + inv.invoiceID + '" value="' + (preSelected ? inv.balanceAmount : 0) + '" min="0" max="' + inv.balanceAmount + '" step="0.01" onchange="updateAllocations()" ' + (preSelected ? '' : 'disabled') + '></td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            html += '<p class="text-muted" style="font-size: 12px;">Check the invoices you want to allocate payment to, then enter the amount for each.</p>';

            $('#invoiceList').html(html);

            // Bind checkbox change
            $('.allocate-check').on('change', function() {
                var invoiceID = $(this).data('invoice');
                var balance = $(this).data('balance');
                var amountInput = $('.allocate-amount[data-invoice="' + invoiceID + '"]');

                if ($(this).is(':checked')) {
                    amountInput.prop('disabled', false).val(balance);
                } else {
                    amountInput.prop('disabled', true).val(0);
                }
                updateAllocations();
            });

            // Pre-fill payment amount if invoice is pre-selected
            if (preSelectInvoice > 0) {
                var preSelectBalance = $('.allocate-check[data-invoice="' + preSelectInvoice + '"]').data('balance');
                if (preSelectBalance) {
                    $('#paymentAmount').val(preSelectBalance);
                }
                updateAllocations();
            }
        } else {
            $('#invoiceList').html('<p class="text-center text-danger">' + res.msg + '</p>');
        }
    }, 'json');
}

function updateAllocations() {
    var paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
    var totalAllocated = 0;

    $('.allocate-amount:not(:disabled)').each(function() {
        var amount = parseFloat($(this).val()) || 0;
        var maxAmount = parseFloat($(this).attr('max')) || 0;

        // Cap at invoice balance
        if (amount > maxAmount) {
            amount = maxAmount;
            $(this).val(maxAmount);
        }

        totalAllocated += amount;
    });

    // Cap total at payment amount
    if (totalAllocated > paymentAmount) {
        // Reduce allocations proportionally
        var ratio = paymentAmount / totalAllocated;
        totalAllocated = 0;
        $('.allocate-amount:not(:disabled)').each(function() {
            var newAmount = Math.floor(parseFloat($(this).val()) * ratio * 100) / 100;
            $(this).val(newAmount);
            totalAllocated += newAmount;
        });
    }

    var unallocated = paymentAmount - totalAllocated;

    $('#totalAllocated').text('Rs. ' + formatNumber(totalAllocated));
    $('#unallocatedAmount').text('Rs. ' + formatNumber(unallocated))
        .css('color', unallocated > 0 ? '#ffc107' : '#198754');
}

function savePayment() {
    // Validate
    if (!$('#distributorID').val()) {
        toastr.error('Please select a distributor');
        return;
    }

    var paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
    if (paymentAmount <= 0) {
        toastr.error('Please enter payment amount');
        return;
    }

    // Collect allocations
    var allocations = [];
    $('.allocate-amount:not(:disabled)').each(function() {
        var amount = parseFloat($(this).val()) || 0;
        if (amount > 0) {
            allocations.push({
                invoiceID: $(this).data('invoice'),
                amount: amount
            });
        }
    });

    var formData = $('#paymentForm').serialize();
    formData += '&allocations=' + encodeURIComponent(JSON.stringify(allocations));

    $.ajax({
        url: ADMINURL + '/mod/b2b-payment/x-b2b-payment.inc.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Recording...');
        },
        success: function(res) {
            $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Record Payment');

            if (res.err == 0) {
                toastr.success(res.msg);
                if (res.param) {
                    window.location.href = ADMINURL + '/b2b-payment-add/?' + res.param;
                }
            } else {
                toastr.error(res.msg);
            }
        },
        error: function() {
            $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Record Payment');
            toastr.error('An error occurred. Please try again.');
        }
    });
}

function formatNumber(num) {
    return parseFloat(num).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    var d = new Date(dateStr);
    return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

// Distributor Module JavaScript

$(document).ready(function() {
    // Initialize form
    if ($('#mxForm').length) {
        $('#mxForm').on('submit', function(e) {
            e.preventDefault();
            saveDistributor();
        });
    }
});

function saveDistributor() {
    var form = $('#mxForm');
    var formData = form.serialize();

    // Validate required fields
    if (!$('[name="distributorName"]').val().trim()) {
        toastr.error('Distributor name is required');
        $('[name="distributorName"]').focus();
        return;
    }

    $.ajax({
        url: ADMINURL + '/mod/distributor/x-distributor.inc.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            form.find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        },
        success: function(res) {
            form.find('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Save Distributor');

            if (res.err == 0) {
                toastr.success(res.msg || 'Distributor saved successfully');
                if (res.param) {
                    window.location.href = ADMINURL + '/distributor-add/?' + res.param;
                }
            } else {
                toastr.error(res.msg || 'Failed to save distributor');
            }
        },
        error: function() {
            form.find('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Save Distributor');
            toastr.error('An error occurred. Please try again.');
        }
    });
}

// Contact Management
function addContact() {
    resetContactForm();
    $('#contactModal .modal-title').text('Add Contact');
    $('#contactModal').modal('show');
}

function editContact(contactID) {
    // Find contact row and populate form
    var row = $('tr[data-contact-id="' + contactID + '"]');
    if (row.length) {
        $('#contactID').val(contactID);
        $('#contactName').val(row.find('td:eq(0)').text());
        $('#contactDesignation').val(row.find('td:eq(1)').text() === '-' ? '' : row.find('td:eq(1)').text());
        $('#contactPhone').val(row.find('td:eq(2)').text());
        $('#contactEmail').val(row.find('td:eq(3)').text());
        $('#contactIsPrimary').prop('checked', row.find('td:eq(4)').text().indexOf('Yes') >= 0);

        $('#contactModal .modal-title').text('Edit Contact');
        $('#contactModal').modal('show');
    }
}

function resetContactForm() {
    $('#contactID').val(0);
    $('#contactName').val('');
    $('#contactDesignation').val('');
    $('#contactPhone').val('');
    $('#contactEmail').val('');
    $('#contactIsPrimary').prop('checked', false);
}

function saveContact() {
    var contactID = parseInt($('#contactID').val());
    var action = contactID > 0 ? 'UPDATE_CONTACT' : 'ADD_CONTACT';

    var data = {
        xAction: action,
        contactID: contactID,
        distributorID: distributorID,
        contactName: $('#contactName').val(),
        designation: $('#contactDesignation').val(),
        contactPhone: $('#contactPhone').val(),
        contactEmail: $('#contactEmail').val(),
        isPrimary: $('#contactIsPrimary').is(':checked') ? 1 : 0
    };

    if (!data.contactName.trim()) {
        toastr.error('Contact name is required');
        return;
    }

    $.post(ADMINURL + '/mod/distributor/x-distributor.inc.php', data, function(res) {
        if (res.err == 0) {
            toastr.success(res.msg);
            $('#contactModal').modal('hide');
            location.reload(); // Refresh to show updated contacts
        } else {
            toastr.error(res.msg);
        }
    }, 'json');
}

function deleteContact(contactID) {
    if (!confirm('Are you sure you want to delete this contact?')) return;

    $.post(ADMINURL + '/mod/distributor/x-distributor.inc.php', {
        xAction: 'DELETE_CONTACT',
        contactID: contactID
    }, function(res) {
        if (res.err == 0) {
            toastr.success(res.msg);
            $('tr[data-contact-id="' + contactID + '"]').fadeOut(function() {
                $(this).remove();
                if ($('#contactsTable tbody tr').length == 0) {
                    $('#contactsTable tbody').append('<tr id="noContactsRow"><td colspan="6" class="text-center text-muted">No contacts added</td></tr>');
                }
            });
        } else {
            toastr.error(res.msg);
        }
    }, 'json');
}

// Credit limit update
function updateCreditLimit() {
    var newLimit = prompt('Enter new credit limit:', $('[name="creditLimit"]').val());
    if (newLimit === null) return;

    var reason = prompt('Reason for change:');
    if (reason === null) return;

    $.post(ADMINURL + '/mod/distributor/x-distributor.inc.php', {
        xAction: 'UPDATE_CREDIT',
        distributorID: distributorID,
        creditLimit: newLimit,
        reason: reason
    }, function(res) {
        if (res.err == 0) {
            toastr.success(res.msg);
            location.reload();
        } else {
            toastr.error(res.msg);
        }
    }, 'json');
}

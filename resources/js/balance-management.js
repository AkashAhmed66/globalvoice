'use strict';

$(function() {
  var offCanvasForm = $('#offcanvasAddRecord');

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  var isEditMode = false; // Track if it's an edit operation
  var balanceId = null; // Store the current balance ID for edit

  // Delete Record
  $(document).on('click', '.delete-record', function() {
    var button = $(this);
    var balance_id = button.data('id');

    // sweetalert for confirmation of delete
    Swal.fire({
      title: 'Are you sure?',
      text: 'You won\'t be able to revert this!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function(result) {
      if (result.value) {
        // delete the data
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}users/balance-delete/${balance_id}`,
          success: function(response) {
            window.location.href = `${baseUrl}users/balance-list`;
            dt_user.draw();
          },
          error: function(error) {
            console.log(error);
          }
        });

        // success sweetalert
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'The balance record has been deleted!',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The Balance record is not deleted!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  // Edit Record
  $(document).on('click', '.edit-record', function() {
    var balance_id = $(this).data('id');

    isEditMode = true;
    balanceId = balance_id; // Store the balance ID

    // Get data
    $.get(`${baseUrl}users/balance/${balance_id}/edit`, function(data) {
      // Check if the data is a string and needs to be parsed
      let jsonData;
      try {
        jsonData = typeof data === 'string' ? JSON.parse(data) : data;
      } catch (e) {
        console.error('Failed to parse JSON:', e);
        return;
      }

      $(`input[name="to"][value="${jsonData.to}"]`).prop('checked', true);
      $('#add-client').val(jsonData.client).trigger('change');
      $('#add-amount').val(jsonData.amount);
    });
  });

  // Validating form and updating balance data
  const addNewBalanceForm = document.getElementById('addNewBalanceForm');

  // Balance form validation
  const fv = FormValidation.formValidation(addNewBalanceForm, {
    fields: {
      
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function(field, ele) {
          // field is the field name & ele is the field element
          return '.mb-5';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function() {

    var url = isEditMode ? `${baseUrl}users/balance-update/${balanceId}` : `${baseUrl}users/balance-store`;
    var method = isEditMode ? 'PUT' : 'POST';

    // Adding or updating balance when form successfully validates
    $.ajax({
      data: $('#addNewBalanceForm').serialize(),
      url: url,
      type: method,
      success: function(response) {
        offCanvasForm.offcanvas('hide');
        Swal.fire({
          icon: 'success',
          title: `Successfully ${response.status}!`,
          text: `Balance ${response.status} Successfully.`,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        }).then(() => {
          // Redirect or reload after the alert
          window.location.href = `${baseUrl}users/balance-list`;
        });
        isEditMode = false; // Reset the edit mode
        balanceId = null; // Reset the balance ID
      },
      error: function(err) {
        console.log(err.responseText); // This will give you more details about the error
        offCanvasForm.offcanvas('hide');
        Swal.fire({
          title: 'Error',
          text: 'Something went wrong, please try again.',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  // Clearing form data when offcanvas hidden
  offCanvasForm.on('hidden.bs.offcanvas', function() {
    fv.resetForm(true);
    // Reset form fields to default values
    $('#to-client').prop('checked', true);
    $('#add-client').val('').trigger('change');
    $('#add-amount').val('');
    isEditMode = false; // Reset the edit mode
    balanceId = null; // Clear the stored balance ID
  });

});

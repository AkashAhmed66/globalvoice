'use strict';

$(function() {
  var offCanvasForm = $('#offcanvasAddRecord');

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  var isEditMode = false; // Track if it's an edit operation
  var numberId = null; // Store the current number ID for edit

  // Delete Record
  $(document).on('click', '.delete-record', function() {
    var button = $(this);
    var number_id = button.data('id');

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
          url: `${baseUrl}users/number-delete/${number_id}`,
          success: function(response) {
            window.location.href = `${baseUrl}users/number-list`;
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
          text: 'The number has been deleted!',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The Number is not deleted!',
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
    var number_id = $(this).data('id');

    isEditMode = true;
    numberId = number_id; // Store the number ID

    // Get data
    $.get(`${baseUrl}users/number/${number_id}/edit`, function(data) {
      // Check if the data is a string and needs to be parsed
      let jsonData;
      try {
        jsonData = typeof data === 'string' ? JSON.parse(data) : data;
      } catch (e) {
        console.error('Failed to parse JSON:', e);
        return;
      }

      $('#add-assign-to').val(jsonData.assign_to).trigger('change');
      $(`input[name="type"][value="${jsonData.type}"]`).prop('checked', true);
      $('#is-booking').prop('checked', jsonData.is_booking == 1);
      $('#add-number').val(jsonData.number);
      $('#add-range').prop('checked', jsonData.add_range == 1);
      $('#add-channel').val(jsonData.channel).trigger('change');
      $('#did-balance').prop('checked', jsonData.did_balance == 1);
    });
  });

  // Validating form and updating number data
  const addNewNumberForm = document.getElementById('addNewNumberForm');

  // Number form validation
  const fv = FormValidation.formValidation(addNewNumberForm, {
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

    var url = isEditMode ? `${baseUrl}users/number-update/${numberId}` : `${baseUrl}users/number-store`;
    var method = isEditMode ? 'PUT' : 'POST';

    // Adding or updating number when form successfully validates
    $.ajax({
      data: $('#addNewNumberForm').serialize(),
      url: url,
      type: method,
      success: function(response) {
        offCanvasForm.offcanvas('hide');
        Swal.fire({
          icon: 'success',
          title: `Successfully ${response.status}!`,
          text: `Number ${response.status} Successfully.`,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        }).then(() => {
          // Redirect or reload after the alert
          window.location.href = `${baseUrl}users/number-list`;
        });
        isEditMode = false; // Reset the edit mode
        numberId = null; // Reset the number ID
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
    $('#add-assign-to').val('').trigger('change');
    $('#type-ipt').prop('checked', true);
    $('#is-booking').prop('checked', false);
    $('#add-number').val('');
    $('#add-range').prop('checked', false);
    $('#add-channel').val('').trigger('change');
    $('#did-balance').prop('checked', false);
    isEditMode = false; // Reset the edit mode
    numberId = null; // Clear the stored number ID
  });

});

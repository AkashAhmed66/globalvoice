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

  //edit
// Edit record
$(document).on('click', '.edit-record', function () {
    let id = $(this).data('id');
    numberId = id;
    isEditMode = true;

    $.ajax({
        url: `${baseUrl}users/number/${id}/edit`,
        type: 'GET',
        success: function (response) {

            // Assign To
            $('#add-assign-to').val(response.assign_to).trigger('change');

            // Type (IPT / Short Code / Toll Free)
           // Uncheck all type radios first
              $('input[name="type"]').prop('checked', false);
              // Check the one that matches the database
              $('input[name="type"]').each(function() {
                  let radioVal = $(this).val().toLowerCase().replace(' ', '_');
                  let dbVal = response.type.toLowerCase().replace(' ', '_');
                  if (radioVal === dbVal) {
                      $(this).prop('checked', true).trigger('change');
                  }
              });


             // ===== SIP Method radios (Register / Peer) =====
            if (response.sip_method === 'Register' || response.sip_method === 'Peer') {
                $('input[name="sip_method"]').prop('checked', false);
                $('input[name="sip_method"][value="' + response.sip_method + '"]').prop('checked', true);
                setTimeout(function() {
                    $('input[name="sip_method"][value="' + response.sip_method + '"]').trigger('change');
                }, 50);
            }


            $('#long-code').val(response.shortcode || '');

            // Show Long Code if type is short_code
            if (response.type.toLowerCase() === 'short_code') {
              $('#longCodeSection').show();
              $('#long-code').val(response.long_code || '');
            } else {
              $('#longCodeSection').hide();
              $('#long-code').val('');
            }


            // Number
            $('#add-number').val(response.number);

            // Add Range
            $('#add-range').prop('checked', response.add_range == 1 || response.add_range === '1');

            // Channel
            $('#add-channel').val(response.channel).trigger('change');

            // Type radio
            $('input[name="type"]').each(function() {
              if ($(this).val().toLowerCase().replace(' ', '_') === response.type.toLowerCase()) {
                $(this).prop('checked', true).trigger('change');
              }
            });

            // DID Balance
            $('#add-range1').prop('checked', response.did_balance === 'on').trigger('change');

            // Is Booking toggle
            $('#is-booking').prop('checked', response.is_booked === 'y');

            // SIP Method
            //$('input[name="sip_method"][value="' + response.sip_method + '"]').prop('checked', true).trigger('change');

            // Status
            $('#add-status').val(response.is_active).trigger('change');




            // Optional fields
            if (response.shortcode) $('#add-shortcode').val(response.shortcode);
            if (response.call_limit_value) $('#call-limit').val(response.call_limit_value);
            if (response.sip_secret) $('#sip-secret').val(response.sip_secret);


            // Show offcanvas
            $('#offcanvasAddRecord').offcanvas('show');
        },
        error: function (xhr) {
            console.error('Failed to fetch number:', xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error fetching number data',
                text: 'Please try again later.',
                customClass: { confirmButton: 'btn btn-danger' }
            });
        }
    });







    // Get data
    $.get(`${baseUrl}users/number/${number_id}/edit`, function(data) {
      // Check if the data is a string and needs to be parsed
      let jsonData;
      try {
        jsonData = typeof data === 'string' ? JSON.parse(data) : data;
        console.log(jsonData)
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

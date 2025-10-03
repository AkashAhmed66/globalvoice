'use strict';

$(function() {
  var offCanvasForm = $('#offcanvasAddRecord');

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  var isEditMode = false; // Track if it's an edit operation
  var userId = null; // Store the current operator ID for edit

  // Delete Record
  $(document).on('click', '.delete-record', function() {
    var button = $(this);
    var user_id = button.data('id');

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
          url: `${baseUrl}users/users-delete/${user_id}`,
          success: function(response) {
            window.location.href = `${baseUrl}users/users-list`;
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
          text: 'The user has been deleted!',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The User is not deleted!',
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
    var user_id = $(this).data('id');

    isEditMode = true;
    userId = user_id; // Store the operator ID

    // Get data
    $.get(`${baseUrl}users/users/${user_id}/edit`, function(data) {
      // Check if the data is a string and needs to be parsed
      let jsonData;
      try {
        jsonData = typeof data === 'string' ? JSON.parse(data) : data;
      } catch (e) {
        console.error('Failed to parse JSON:', e);
        return;
      }

      $('#add-full-name').val(jsonData.full_name || jsonData.name);
      $('#add-name').val(jsonData.name);
      $('#add-email').val(jsonData.email);
      $('#add-mobile').val(jsonData.mobile);
      $('#add-user-group').val(jsonData.user_group_id).trigger('change');
    });
  });

  // Validating form and updating user's data
  const addNewUserForm1 = document.getElementById('addNewUserForm1');

  // User form validation
  const fv = FormValidation.formValidation(addNewUserForm1, {
    fields: {
      full_name: {
        validators: {
          notEmpty: {
            message: 'Please enter full name'
          }
        }
      },
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter name'
          }
        }
      },
      mobile: {
        validators: {
          notEmpty: {
            message: 'Please enter mobile'
          },
          regexp: {
            regexp: /^[0-9+\-\s()]+$/,
            message: 'Please enter a valid mobile number'
          }
        }
      },
      email: {
        validators: {
          notEmpty: {
            message: 'Please enter email'
          },
          emailAddress: {
            message: 'Please enter a valid email address'
          }
        }
      },
      password: {
        validators: {
          callback: {
            message: 'Please enter password',
            callback: function(input) {
              // Only require password if not in edit mode (i.e., creating new)
              if (!isEditMode) {
                return input.value.trim().length > 0;
              }
              return true;
            }
          },
          stringLength: {
            min: 6,
            message: 'Password must be at least 6 characters long',
            enabled: function() {
              // Only enforce length if not in edit mode
              return !isEditMode;
            }
          }
        }
      },
      user_group_id: {
        validators: {
          notEmpty: {
            message: 'Please select user group'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function(field, ele) {
          // field is the field name & ele is the field element
          return '.mb-4, .mb-5';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function() {

    var url = isEditMode ? `${baseUrl}users/users-update/${userId}` : `${baseUrl}users/users-store`;
    var method = isEditMode ? 'PUT' : 'POST';

    // Adding or updating user when form successfully validates
    $.ajax({
      data: $('#addNewUserForm1').serialize(),
      url: url,
      type: method,
      success: function(response) {
        offCanvasForm.offcanvas('hide');
        Swal.fire({
          icon: 'success',
          title: `Successfully ${response.status}!`,
          text: `User ${response.status} Successfully.`,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        }).then(() => {
          // Redirect or reload after the alert
          window.location.href = `${baseUrl}users/users-list`;
        });
        isEditMode = false; // Reset the edit mode
        userId = null; // Reset the operator ID
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
    $('#add-full-name').val('');
    $('#add-name').val('');
    $('#add-email').val('');
    $('#add-mobile').val('');
    $('#add-password').val('');
    $('#add-user-group').val('').trigger('change');
    
    isEditMode = false; // Reset the edit mode
    userId = null; // Clear the stored operator ID
  });

});

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
          url: `${baseUrl}users/mnp-delete/${user_id}`,
          success: function(response) {
            window.location.href = `${baseUrl}users/mnp-list`;
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
    $.get(`${baseUrl}users/mnp/${user_id}/edit`, function(data) {
      // Check if the data is a string and needs to be parsed
      let jsonData;
      try {
        jsonData = typeof data === 'string' ? JSON.parse(data) : data;
      } catch (e) {
        console.error('Failed to parse JSON:', e);
        return;
      }

      $('#add-name').val(jsonData.name);
      $('#add-username').val(jsonData.username);
      $('#add-email').val(jsonData.email);
      $('#add-sms-rate').val(jsonData.sms_rate_id).trigger('change');
      $('#add-sms-senderId').val(jsonData.senderId).trigger('change');
      $('#add-address').val(jsonData.address);
      $('#add-mobile').val(jsonData.mobile);
      $('#add-user-group').val(jsonData.user_group_id).trigger('change');
      $('#add-tps').val(jsonData.tps);
      $('#add-reve-api-key').val(jsonData.user_reve_api_key);
      $('#add-reve-secret-key').val(jsonData.user_reve_secret_key);
    });
  });

  // Validating form and updating user's data
  const addNewUserForm1 = document.getElementById('addNewUserForm1');

  // User form validation
  const fv = FormValidation.formValidation(addNewUserForm1, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter name'
          }
        }
      },
      username: {
        validators: {
          notEmpty: {
            message: 'Please enter user name'
          }
        }
      },
      mobile: {
        validators: {
          notEmpty: {
            message: 'Please enter mobile'
          },
          regexp: {
            regexp: /^[0-9]+$/,
            message: 'The mobile number can only consist of numbers'
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
      confirm_password: {
        validators: {
          callback: {
            message: 'Please confirm your password',
            callback: function(input) {
              if (!isEditMode) {
                return input.value.trim().length > 0;
              }
              return true;
            }
          },
          identical: {
            compare: function() {
              return addNewUserForm1.querySelector('[name="password"]').value;
            },
            message: 'The password and its confirm are not the same',
            enabled: function() {
              return !isEditMode;
            }
          }
        }
      },
      address: {
        validators: {
          notEmpty: {
            message: 'Please enter address'
          }
        }
      },
      user_group_id: {
        validators: {
          notEmpty: {
            message: 'Please enter user type'
          }
        }
      },
      sms_rate_id: {
        validators: {
          notEmpty: {
            message: 'Please enter sms rate'
          }
        }
      },
      /*sms_senderId: {
          validators: {
              notEmpty: {
                  message: 'Please select senderId'
              }
          }
      },*/
      tps: {
        validators: {
          notEmpty: {
            message: 'Please enter TPS'
          },
          numeric: {
            message: 'The TPS must be a valid number'
          }
        }
      }
      /* user_reve_api_key: {
             validators: {
                 notEmpty: {
                     message: 'Please enter api key'
                 }
             }
         },
       user_reve_secret_key: {
             validators: {
                 notEmpty: {
                     message: 'Please enter secret key'
                 }
             }
         }*/
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

    // Validate password and confirm password before submitting
    const password = $('#add-password').val(); // Assuming the password field has id 'add-password'
    const confirmPassword = $('#add-confirm-password').val(); // Assuming the confirm password field has id 'add-confirm-password'

    if (password && !confirmPassword) {
      Swal.fire({
        title: 'Error',
        text: 'Please enter confirm your password.',
        icon: 'error',
        customClass: {
          confirmButton: 'btn btn-danger'
        }
      });
      return; // Prevent form submission if confirm password is empty
    }

    if (password && confirmPassword && password !== confirmPassword) {
      Swal.fire({
        title: 'Error',
        text: 'Confirm passwords do not match.',
        icon: 'error',
        customClass: {
          confirmButton: 'btn btn-danger'
        }
      });
      return; // Prevent form submission if passwords do not match
    }

    var url = isEditMode ? `${baseUrl}users/mnp-update/${userId}` : `${baseUrl}users/mnp-store`;
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
          window.location.href = `${baseUrl}users/mnp-list`;
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
    isEditMode = false; // Reset the edit mode
    userId = null; // Clear the stored operator ID
  });

});

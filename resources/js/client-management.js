'use strict';

$(function () {
  var offCanvasForm = $('#offcanvasAddRecord');

  // Add service row functionality
  function addServiceRow() {
    serviceRowCounter++;

    // Get service type options from the template select (exact same as in create.blade.php)
    var serviceTypeOptions = $('#service-type-template').html();

    const row = `
      <tr>
        <td>
          <input type="hidden" name="services[${serviceRowCounter}][id]" value="0">
          <select class="form-select form-select-sm" name="services[${serviceRowCounter}][service_type]">
            ${serviceTypeOptions}
          </select>
        </td>
        <td><input type="text" class="form-control form-control-sm" name="services[${serviceRowCounter}][service_name]" placeholder="Service Name"></td>
        <td><input type="number" class="form-control form-control-sm" name="services[${serviceRowCounter}][otc]" placeholder="OTC" step="0.01" min="0"></td>
        <td><input type="number" class="form-control form-control-sm" name="services[${serviceRowCounter}][mrc]" placeholder="MRC" step="0.01" min="0"></td>
        <td><input type="date" class="form-control form-control-sm" name="services[${serviceRowCounter}][launch_date]" placeholder="Launch Date"></td>
        <td><input type="date" class="form-control form-control-sm" name="services[${serviceRowCounter}][bill_start_date]" placeholder="Bill Start Date"></td>
        <td>
          <button type="button" class="btn btn-primary btn-sm me-1 add-service-btn">
            <i class="ri-add-line"></i>
          </button>
          <button type="button" class="btn btn-danger btn-sm remove-service-row">
            <i class="ri-delete-bin-line"></i>
          </button>
        </td>
      </tr>
    `;
    $('#servicesTableBody').append(row);

    // Show remove buttons when there are more than 1 row
    if ($('#servicesTableBody tr').length > 1) {
      $('.remove-service-row').show();
    }
  }

  // Add service row button clicks
  $(document).on('click', '#addServiceRow, #addServiceRow2, .add-service-btn', function () {
    addServiceRow();
  });

  // Remove service row
  $(document).on('click', '.remove-service-row', function () {
    $(this).closest('tr').remove();
    // Hide remove buttons if only 2 rows remain
    if ($('#servicesTableBody tr').length <= 2) {
      $('.remove-service-row').hide();
    }
  });

  var isEditMode = false; // Track if it's an edit operation
  var clientId = null; // Store the current client ID for edit
  var serviceRowCounter = 2; // Counter for service rows (starting at 2 since we have 2 initial rows)

  // Delete Record
  $(document).on('click', '.delete-record', function () {
    var button = $(this);
    var client_id = button.data('id');

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
    }).then(function (result) {
      if (result.value) {
        // delete the data
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}users/client-delete/${client_id}`,
          success: function (response) {
            window.location.href = `${baseUrl}users/client-list`;
            dt_user.draw();
          },
          error: function (error) {
            console.log(error);
          }
        });

        // success sweetalert
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: 'The client has been deleted!',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The Client is not deleted!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });

  // Edit Record
  $(document).on('click', '.edit-record', function () {
    var client_id = $(this).data('id');

    isEditMode = true;
    clientId = client_id; // Store the client ID

    // Get data
    $.get(`${baseUrl}users/client/${client_id}/edit`, function (data) {
      // Check if the data is a string and needs to be parsed
      let jsonData;
      try {
        jsonData = typeof data === 'string' ? JSON.parse(data) : data;
      } catch (e) {
        console.error('Failed to parse JSON:', e);
        return;
      }

      console.log(jsonData);

      $('#add-name').val(jsonData.name);
      $('#add-address').val(jsonData.address);
      $('#add-contact-name').val(jsonData.contact_name);
      $('#add-contact-number').val(jsonData.contact_no);
      $('#add-mail').val(jsonData.mail);
      $('#add-web-name').val(jsonData.web_name);
      $('#add-district').val(jsonData.district).trigger('change');
      $('#add-zone').val(jsonData.zone).trigger('change');
      $('#add-credit-limit').val(jsonData.credit_limit);
      $('#add-tariff').val(jsonData.tariff_id).trigger('change');
      $('#enable-isd').prop('checked', jsonData.enable_isd == 1);

      // Populate service rows from data
      if (jsonData.services && jsonData.services.length > 0) {
        // Get service type options from the template (exact same as in create.blade.php)
        var serviceTypeOptions = $('#service-type-template').html();

        // Clear all existing service rows
        $('#servicesTableBody').empty();
        serviceRowCounter = 0;

        jsonData.services.forEach(function (service, index) {
          serviceRowCounter++;
          const rowNumber = serviceRowCounter;

          // Add new row for each service
          const row = `
            <tr>
              <td>
                <input type="hidden" name="services[${rowNumber}][id]" value="${service.id || 0}">
                <select class="form-select form-select-sm" name="services[${rowNumber}][service_type]">
                  ${serviceTypeOptions}
                </select>
              </td>
              <td><input type="text" class="form-control form-control-sm" name="services[${rowNumber}][service_name]" placeholder="Service Name" value="${service.service_name || service.name || ''}"></td>
              <td><input type="number" class="form-control form-control-sm" name="services[${rowNumber}][otc]" placeholder="OTC" step="0.01" min="0" value="${service.otc || 0}"></td>
              <td><input type="number" class="form-control form-control-sm" name="services[${rowNumber}][mrc]" placeholder="MRC" step="0.01" min="0" value="${service.mrc || 0}"></td>
              <td><input type="date" class="form-control form-control-sm" name="services[${rowNumber}][launch_date]" placeholder="Launch Date" value="${service.launch_date || ''}"></td>
              <td><input type="date" class="form-control form-control-sm" name="services[${rowNumber}][bill_start_date]" placeholder="Bill Start Date" value="${service.bill_start_date || ''}"></td>
              <td>
                <button type="button" class="btn btn-primary btn-sm me-1 add-service-btn">
                  <i class="ri-add-line"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm remove-service-row" ${serviceRowCounter === 1 ? 'style="display: none;"' : ''}>
                  <i class="ri-delete-bin-line"></i>
                </button>
              </td>
            </tr>
          `;
          $('#servicesTableBody').append(row);

          // Set the service_type value after row is added
          // The database field is 'type', so use that if service_type is not available
          var serviceTypeValue = service.service_type || service.type || '';

          $(`select[name="services[${rowNumber}][service_type]"]`).val(serviceTypeValue);
        });

        // Show/hide remove buttons based on row count
        if ($('#servicesTableBody tr').length > 1) {
          $('.remove-service-row').show();
        } else {
          $('.remove-service-row').hide();
        }
      }
    });
  });

  // Validating form and updating client data
  const addNewClientForm = document.getElementById('addNewClientForm');

  // Client form validation
  const fv = FormValidation.formValidation(addNewClientForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter name'
          }
        }
      },
      contact_name: {
        validators: {
          notEmpty: {
            message: 'Please enter contact name'
          }
        }
      },
      contact_number: {
        validators: {
          notEmpty: {
            message: 'Please enter contact number'
          },
          regexp: {
            regexp: /^[0-9+\-\s()]+$/,
            message: 'Please enter a valid contact number'
          }
        }
      },
      mail: {
        validators: {
          notEmpty: {
            message: 'Please enter email'
          },
          emailAddress: {
            message: 'Please enter a valid email address'
          }
        }
      },
      web_name: {
        validators: {
          notEmpty: {
            message: 'Please enter web name'
          }
        }
      },
      password: {
        validators: {
          callback: {
            message: 'Please enter password',
            callback: function (input) {
              if (!isEditMode) {
                return input.value.trim().length > 0;
              }
              return true;
            }
          },
          stringLength: {
            min: 6,
            message: 'Password must be at least 6 characters long',
            enabled: function () {
              return !isEditMode;
            }
          }
        }
      },
      district: {
        validators: {
          notEmpty: {
            message: 'Please select district'
          }
        }
      },
      zone: {
        validators: {
          notEmpty: {
            message: 'Please select zone'
          }
        }
      },
      credit_limit: {
        validators: {
          notEmpty: {
            message: 'Please enter credit limit'
          },
          numeric: {
            message: 'Please enter a valid credit limit'
          },
          greaterThan: {
            min: 0,
            message: 'Credit limit must be greater than 0'
          }
        }
      },
      tariff: {
        validators: {
          notEmpty: {
            message: 'Please select tariff'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.mb-4';
        }
      }),
      excluded: new FormValidation.plugins.Excluded({
        excluded: function (field, element, elements) {
          // Exclude services array fields from validation
          return field.indexOf('services[') === 0;
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {

    var url = isEditMode ? `${baseUrl}users/client-update/${clientId}` : `${baseUrl}users/client-store`;
    var method = isEditMode ? 'PUT' : 'POST';

    // Adding or updating client when form successfully validates
    $.ajax({
      data: $('#addNewClientForm').serialize(),
      url: url,
      type: method,
      success: function (response) {
        console.log(response);
        offCanvasForm.offcanvas('hide');
        Swal.fire({
          icon: 'success',
          title: `Successfully ${response.status}!`,
          text: `Client ${response.status} Successfully.`,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        }).then(() => {
          // Redirect or reload after the alert
          window.location.href = `${baseUrl}users/client-list`;
        });
        isEditMode = false; // Reset the edit mode
        clientId = null; // Reset the client ID
      },
      error: function (err) {
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
  offCanvasForm.on('hidden.bs.offcanvas', function () {
    fv.resetForm(true);
    // Reset form fields to default values
    $('#add-name').val('');
    $('#add-address').val('');
    $('#add-contact-name').val('');
    $('#add-contact-number').val('');
    $('#add-mail').val('');
    $('#add-web-name').val('');
    $('#add-password').val('');
    $('#add-district').val('').trigger('change');
    $('#add-zone').val('').trigger('change');
    $('#add-credit-limit').val('500');
    $('#add-tariff').val('').trigger('change');
    $('#enable-isd').prop('checked', false);

    // Reset service table to initial 2 rows
    $('#servicesTableBody tr:gt(1)').remove();
    serviceRowCounter = 2;

    // Clear service fields in first 2 rows
    for (let i = 1; i <= 2; i++) {
      $(`select[name="services[${i}][service_type]"]`).val('');
      $(`input[name="services[${i}][service_name]"]`).val('');
      $(`input[name="services[${i}][otc]"]`).val('');
      $(`input[name="services[${i}][mrc]"]`).val('');
      $(`input[name="services[${i}][launch_date]"]`).val('');
      $(`input[name="services[${i}][bill_start_date]"]`).val('');
    }

    // Hide remove buttons
    $('.remove-service-row').hide();

    isEditMode = false; // Reset the edit mode
    clientId = null; // Clear the stored client ID
  });

});

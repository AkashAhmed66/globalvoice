'use strict';

$(function () {
  var offCanvasForm = $('#offcanvasAddRecord');

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  var isEditMode = false; // Track if it's an edit operation
  var tarifId = null; // Store the current tarif ID for edit

  // Delete Record
  $(document).on('click', '.delete-record', function () {
    var button = $(this);
    var tarif_id = button.data('id');

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
          url: `${baseUrl}users/tarif-delete/${tarif_id}`,
          success: function (response) {
            window.location.href = `${baseUrl}users/tarif-list`;
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
          text: 'The tarif has been deleted!',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The Tarif is not deleted!',
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
    var tarif_id = $(this).data('id');

    isEditMode = true;
    tarifId = tarif_id; // Store the tarif ID

    // Get data
    $.get(`${baseUrl}users/tarif/${tarif_id}/edit`, function (data) {
      // Check if the data is a string and needs to be parsed
      let jsonData;
      try {
        jsonData = typeof data === 'string' ? JSON.parse(data) : data;
      } catch (e) {
        console.error('Failed to parse JSON:', e);
        return;
      }

      $('#add-name').val(jsonData.name);
      $('#add-pulse').val(jsonData.pulse).trigger('change');

      // Populate detail rows from data
      if (jsonData.details && jsonData.details.length > 0) {
        jsonData.details.forEach(function (detail, index) {
          const rowNumber = index + 1;
          if (rowNumber <= 20) { // Only populate up to 20 rows
            $(`input[name="details[${rowNumber}][operator_prefix]"]`).val(detail.operator_prefix || '');
            $(`input[name="details[${rowNumber}][name]"]`).val(detail.name || '');
            $(`input[name="details[${rowNumber}][rate]"]`).val(detail.rate || '0');
            $(`select[name="details[${rowNumber}][status]"]`).val(detail.status || 'Active');
          }
        });
      }
    });
  });

  // Validating form and updating tarif data
  const addNewTarifForm = document.getElementById('addNewTarifForm');

  // Tarif form validation
  const fv = FormValidation.formValidation(addNewTarifForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter name'
          }
        }
      },
      pulse: {
        validators: {
          notEmpty: {
            message: 'Please enter pulse'
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
          // Exclude details array fields from validation
          return field.indexOf('details[') === 0;
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {

    var url = isEditMode ? `${baseUrl}users/tarif-update/${tarifId}` : `${baseUrl}users/tarif-store`;
    var method = isEditMode ? 'PUT' : 'POST';

    // Adding or updating tarif when form successfully validates
    $.ajax({
      data: $('#addNewTarifForm').serialize(),
      url: url,
      type: method,
      success: function (response) {
        offCanvasForm.offcanvas('hide');
        Swal.fire({
          icon: 'success',
          title: `Successfully ${response.status}!`,
          text: `Tarif ${response.status} Successfully.`,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        }).then(() => {
          // Redirect or reload after the alert
          window.location.href = `${baseUrl}users/tarif-list`;
        });
        isEditMode = false; // Reset the edit mode
        tarifId = null; // Reset the tarif ID
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
    $('#add-pulse').val('').trigger('change');

    // Reset all detail rows to default values
    for (let i = 1; i <= 20; i++) {
      $(`input[name="details[${i}][operator_prefix]"]`).val($(`input[name="details[${i}][operator_prefix]"]`).attr('placeholder') || '');
      $(`input[name="details[${i}][name]"]`).val($(`input[name="details[${i}][name]"]`).attr('placeholder') || '');
      $(`input[name="details[${i}][rate]"]`).val('0');
      $(`select[name="details[${i}][status]"]`).val('Active');
    }

    isEditMode = false; // Reset the edit mode
    tarifId = null; // Clear the stored tarif ID
  });

});

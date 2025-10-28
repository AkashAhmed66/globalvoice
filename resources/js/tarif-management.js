'use strict';

$(function () {
  var offCanvasForm = $('#offcanvasAddRecord');

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  var isEditMode = false;
  var tarifId = null;

  // Delete Record
  $(document).on('click', '.delete-record', function () {
    var tarif_id = $(this).data('id');

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.isConfirmed) {
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}users/tarif-delete/${tarif_id}`,
          success: function () {
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'The tarif has been deleted!',
              customClass: { confirmButton: 'btn btn-success' }
            }).then(() => window.location.href = `${baseUrl}users/tarif-list`);
          },
          error: function (error) {
            console.error(error);
          }
        });
      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire({
          title: 'Cancelled',
          text: 'The Tarif is not deleted!',
          icon: 'error',
          customClass: { confirmButton: 'btn btn-success' }
        });
      }
    });
  });

  // Edit Record
  $(document).on('click', '.edit-record', function () {
    tarifId = $(this).data('id');
    isEditMode = true;

    $.get(`${baseUrl}users/tarif/${tarifId}/edit`, function (data) {
      let jsonData;
      try {
        jsonData = typeof data === 'string' ? JSON.parse(data) : data;
      } catch (e) {
        console.error('Failed to parse JSON:', e);
        return;
      }

      $('#add-name').val(jsonData.name);
      $('#add-pulse').val(jsonData.pulse_local).trigger('change');

      if (jsonData.details && jsonData.details.length > 0) {
        jsonData.details.forEach(function (detail, index) {
          $(`input[name="details[${index}][operator_prefix]"]`).val(detail.operator_prefix || '');
          $(`input[name="details[${index}][name]"]`).val(detail.name || '');
          $(`input[name="details[${index}][rate]"]`).val(detail.rate || '0');
          $(`select[name="details[${index}][status]"]`).val(detail.status || 'Active');
        });
      }
    });
  });

  // Form Validation
  const addNewTarifForm = document.getElementById('addNewTarifForm');
  const fv = FormValidation.formValidation(addNewTarifForm, {
    fields: {
      name: {
        validators: {
          notEmpty: { message: 'Tarif Name is required' },
          stringLength: { max: 255, message: 'Tarif Name must be less than 255 characters' }
        }
      },
      pulse_local: {
        validators: {
          notEmpty: { message: 'Pulse is required' }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: '.mb-4'
      }),
      excluded: new FormValidation.plugins.Excluded({
        excluded: function (field) {
          return field.indexOf('details[') === 0; // exclude details[]
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {
    var url = isEditMode ? `${baseUrl}users/tarif-update/${tarifId}` : `${baseUrl}users/tarif-store`;
    var method = isEditMode ? 'PUT' : 'POST';

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
          customClass: { confirmButton: 'btn btn-success' }
        }).then(() => window.location.href = `${baseUrl}users/tarif-list`);
        isEditMode = false;
        tarifId = null;
      },
      error: function (err) {
        console.error(err.responseText);
        offCanvasForm.offcanvas('hide');
        Swal.fire({
          title: 'Error',
          text: 'Something went wrong, please try again.',
          icon: 'error',
          customClass: { confirmButton: 'btn btn-success' }
        });
      }
    });
  });

  // Reset form when offcanvas is hidden
  offCanvasForm.on('hidden.bs.offcanvas', function () {
    fv.resetForm(true);
    $('#add-name').val('');
    $('#add-pulse').val('').trigger('change');

    for (let i = 0; i < 20; i++) {
      $(`input[name="details[${i}][operator_prefix]"]`).val('');
      $(`input[name="details[${i}][name]"]`).val('');
      $(`input[name="details[${i}][rate]"]`).val('0');
      $(`select[name="details[${i}][status]"]`).val('Active');
    }

    isEditMode = false;
    tarifId = null;
  });

});

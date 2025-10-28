'use strict';

$(function () {
  var offCanvasForm = $('#offcanvasAddRecord');

  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  let isEditMode = false;
  let tarifId = null;

  $(document).on('click', '.edit-record', function () {
    tarifId = $(this).data('id');
    isEditMode = true;

    $.get(`${baseUrl}users/tarif/${tarifId}/edit`, function (data) {
      $('#add-name').val(data.tariff.name);
      $('#add-pulse').val(data.tariff.pulse_local);

      data.details.forEach((detail, i) => {
        $(`input[name="details[${i}][rate]"]`).val(detail.rate);
        $(`select[name="details[${i}][status]"]`).val(detail.is_active === 1 ? 'Active' : 'Inactive');
      });

      offCanvasForm.offcanvas('show');
    });
  });

  const addForm = document.getElementById('addNewTarifForm');
  const fv = FormValidation.formValidation(addForm, {
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5(),
      submitButton: new FormValidation.plugins.SubmitButton(),
    }
  }).on('core.form.valid', function () {

    const url = isEditMode ? `${baseUrl}users/tarif-update/${tarifId}` : `${baseUrl}users/tarif-store`;
    const method = isEditMode ? 'PUT' : 'POST';

    $.ajax({
      data: $(addForm).serialize(),
      url: url,
      type: method,
      success: function (response) {
        Swal.fire({
          icon: 'success',
          title: `Tarif ${response.status} successfully!`,
          confirmButtonText: 'OK'
        }).then(() => location.reload());

        isEditMode = false;
      },
      error: function (err) {
        Swal.fire({
          icon: 'error',
          title: 'Failed!',
          text: err.responseText,
        });
      }
    });
  });
});

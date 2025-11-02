'use strict';

$(function () {
  var offCanvasForm = $('#offcanvasAddRecord');

  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  let isEditMode = false;
  let peerId = null;

  // Edit record handler
  $(document).on('click', '.edit-record', function () {
    peerId = $(this).data('id');
    isEditMode = true;

    $.get(`${baseUrl}users/peer/${peerId}/edit`, function (data) {
      $('#peer-id').val(data.peer.id);
      $('#add-name').val(data.peer.name);
      $('#add-channel').val(data.peer.channel);
      $('#add-host').val(data.peer.host);

      $('#offcanvasAddPeerLabel').text('Edit Peer');
      offCanvasForm.offcanvas('show');
    }).fail(function (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Failed to load peer data',
      });
    });
  });

  // Form validation
  const addForm = document.getElementById('peerForm');
  const fv = FormValidation.formValidation(addForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Name is required'
          }
        }
      },
      channel: {
        validators: {
          notEmpty: {
            message: 'Channel is required'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: '.mb-4'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', function () {
    const url = isEditMode ? `${baseUrl}users/peer-update/${peerId}` : `${baseUrl}users/peer-store`;
    const method = isEditMode ? 'PUT' : 'POST';

    $.ajax({
      data: $(addForm).serialize(),
      url: url,
      type: method,
      success: function (response) {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: response.message,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        }).then(() => {
          offCanvasForm.offcanvas('hide');
          location.reload();
        });
      },
      error: function (err) {
        let errorMessage = 'Failed to save peer';
        if (err.responseJSON && err.responseJSON.message) {
          errorMessage = err.responseJSON.message;
        }
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: errorMessage,
          customClass: {
            confirmButton: 'btn btn-danger'
          }
        });
      }
    });
  });

  // Reset form when offcanvas is hidden
  offCanvasForm.on('hidden.bs.offcanvas', function () {
    fv.resetForm(true);
    $('#peer-id').val('0');
    $('#add-name').val('');
    $('#add-channel').val('');
    $('#offcanvasAddPeerLabel').text('Add Peer');
    isEditMode = false;
    peerId = null;
  });

  // Delete handler
  $(document).on('click', '.delete-record', function () {
    const id = $(this).data('id');

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
      if (result.value) {
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}users/peer-delete/${id}`,
          success: function (response) {
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: response.message,
              customClass: {
                confirmButton: 'btn btn-success'
              }
            }).then(() => {
              location.reload();
            });
          },
          error: function (error) {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'Failed to delete peer',
              customClass: {
                confirmButton: 'btn btn-danger'
              }
            });
          }
        });
      }
    });
  });
});

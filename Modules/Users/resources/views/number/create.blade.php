<!-- ADD NEW RECORD -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRecord" aria-labelledby="offcanvasAddUserLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasAddUserLabel" class="offcanvas-title">New</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body mx-0 flex-grow-0 h-100">
    <form class="add-new-number pt-0" id="addNewNumberForm">

      <!-- Assign To -->
      <div class="form-floating form-floating-outline mb-4">
        <select id="add-assign-to" name="assign_to" class="select2 form-select">
          <option value="">--</option>
          @if(isset($users))
            @foreach($users as $user)
              <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
          @endif
        </select>
        <label for="add-assign-to">Assign To <span class="text-danger">*</span></label>
      </div>

      <!-- Type -->
      <div class="mb-4">
        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
        <div class="d-flex gap-4 mt-2">
          <div class="form-check">
            <input class="form-check-input type-radio" type="radio" name="type" id="type-ipt" value="IPT" checked>
            <label class="form-check-label" for="type-ipt">IPT</label>
          </div>
          <div class="form-check">
            <input class="form-check-input type-radio" type="radio" name="type" id="type-short-code" value="Short Code">
            <label class="form-check-label" for="type-short-code">Short Code</label>
          </div>
          <div class="form-check">
            <input class="form-check-input type-radio" type="radio" name="type" id="type-toll-free" value="Toll Free">
            <label class="form-check-label" for="type-toll-free">Toll Free</label>
          </div>
        </div>
      </div>

      <!-- Is booking -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <label class="form-label fw-semibold mb-0">Is booking</label>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="is-booking" name="is_booking" value="1">
        </div>
      </div>

      <!-- Number -->
      <div class="form-floating form-floating-outline mb-4">
        <input type="text" class="form-control" id="add-number" placeholder="Number" name="number" aria-label="Number" />
        <label for="add-number">Number <span class="text-danger">*</span></label>
      </div>

      <!-- Add Range (only for IPT) -->
      <div id="addRangeSection" class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
          <label class="form-label fw-semibold mb-0">Add Range</label>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="add-range" name="add_range" value="1">
          </div>
        </div>
        <div id="rangeCount" class="mt-2" style="display:none;">
          <input type="number" class="form-control" name="range_count" placeholder="Count">
        </div>
      </div>

      <!-- Long Code (only for Short Code) -->
      <div id="longCodeSection" class="form-floating form-floating-outline mb-4" style="display:none;">
        <input type="text" class="form-control" id="long-code" name="long_code" placeholder="Long Code">
        <label for="long-code">Long Code</label>
      </div>

      <!-- DID  -->
      <div id="didsec" class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
          <label class="form-label fw-semibold mb-0">DID Balance</label>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="add-range1" name="Credit Limit" value="1">
          </div>
        </div>
        <div id="rangeCount1" class="mt-2" style="display:none;">
          <input type="number" class="form-control" name="range_count" placeholder="Credit Limit">
        </div>
      </div>


      <!-- SIP Method -->
      <div class="mb-4">
        <label class="form-label fw-semibold">SIP Method <span class="text-danger">*</span></label>
        <div class="d-flex gap-4 mt-2">
          <div class="form-check">
            <input class="form-check-input sip-method" type="radio" name="sip_method" id="sip-register" value="Register">
            <label class="form-check-label" for="sip-register">Register</label>
          </div>
          <div class="form-check">
            <input class="form-check-input sip-method" type="radio" name="sip_method" id="sip-peer" value="Peer" checked>
            <label class="form-check-label" for="sip-peer">Peer</label>
          </div>
        </div>
      </div>

      <!-- SIP Secret & Call Limit (only for Register) -->
      <div id="sipRegisterFields" style="display:none;">
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="sip-secret" name="sip_secret" placeholder="Secret [ maximum 30 character ]" maxlength="30">
          <label for="sip-secret">SIP Secret</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="number" class="form-control" id="call-limit" name="call_limit" placeholder="Call Limit">
          <label for="call-limit">Call Limit</label>
        </div>
      </div>

      <!-- Peer Dropdown (only for Peer method) -->
      <div id="peerSection" class="form-floating form-floating-outline mb-4">
        <select id="peer" name="peer" class="select2 form-select">
          <option value="">--</option>
          @if(isset($peers))
            @foreach($peers as $peer)
              <option value="{{ $peer->id }}">{{ $peer->name }}</option>
            @endforeach
          @endif
        </select>
        <label for="peer">Peer</label>
      </div>

      <!-- Save & Cancel -->
      <div class="mt-4">
        <button type="submit" class="btn btn-success me-sm-3 me-1 data-submit">Save</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
      </div>

    </form>
  </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Script Logic -->
<script>
$(function () {

  // ===== Type Logic =====
  // ===== Type Logic =====
$('input[name="type"]').on('change', function () {
  const selectedType = $(this).val();

  if (selectedType === 'IPT') {
    $('#addRangeSection').slideDown();
    $('#add-range').prop('checked', false);
    $('#rangeCount').hide();
    $('#longCodeSection').slideUp();
    $('#long-code').val('');
  }
  else if (selectedType === 'Short Code') {
    $('#addRangeSection').slideUp();
    $('#add-range').prop('checked', false);
    $('#rangeCount').hide();
    $('#longCodeSection').slideDown();
  }
  else if (selectedType === 'Toll Free') {
    $('#addRangeSection').slideDown();
    $('#add-range').prop('checked', false);
    $('#rangeCount').hide();
    $('#longCodeSection').slideUp();
    $('#long-code').val('');
  }
});



  // ===== Add Range Toggle =====
  $('#add-range').on('change', function () {
    if ($(this).is(':checked')) {
      $('#rangeCount').slideDown();
    } else {
      $('#rangeCount').slideUp();
      $('input[name="range_count"]').val('');
    }
  });

    // ===== Add DID Toggle =====
   $('#add-range1').on('change', function () {
    if ($(this).is(':checked')) {
      $('#rangeCount1').slideDown();
    } else {
      $('#rangeCount1').slideUp();
      $('input[name="range_count1"]').val('');
    }
  });



  // ===== SIP Method Logic =====
  $('input[name="sip_method"]').on('change', function () {
    const selected = $(this).val();

    if (selected === 'Register') {
      $('#sipRegisterFields').slideDown(200);
      $('#peerSection').slideUp(200);
      $('#peer').val('');
    } else if (selected === 'Peer') {
      $('#sipRegisterFields').slideUp(200);
      $('#sip-secret, #call-limit').val('');
      $('#peerSection').slideDown(200);
    }
  });

  // ===== Initialize on Load =====
  const initType = $('input[name="type"]:checked').val();
  if (initType === 'IPT') {
    $('#addRangeSection').show();
    $('#longCodeSection').hide();
  } else if (initType === 'Short Code') {
    $('#addRangeSection').hide();
    $('#longCodeSection').show();
  } else {
    $('#addRangeSection').hide();
    $('#longCodeSection').hide();
  }

  const initSip = $('input[name="sip_method"]:checked').val();
  if (initSip === 'Register') {
    $('#sipRegisterFields').show();
    $('#peerSection').hide();
  } else {
    $('#sipRegisterFields').hide();
    $('#peerSection').show();
  }
});
</script>

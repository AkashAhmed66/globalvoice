<!-- ADD NEW RECORD -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRecord" aria-labelledby="offcanvasAddUserLabel">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-user pt-0" id="addNewUserForm1">

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-name" placeholder="" name="name" aria-label="" />
            <label for="add-name">Name</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-username" placeholder="" name="username" aria-label="" autocomplete="off" />
            <label for="add-username">Username</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-email" class="form-control" placeholder="" aria-label="" name="email" />
            <label for="add-email">Email</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="password" id="add-password" class="form-control" placeholder="" aria-label="" name="password" autocomplete="new-password" />
            <label for="add-password">Password</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="password" id="add-confirm-password" class="form-control" placeholder="" aria-label="" name="confirm_password" />
            <label for="add-confirm-password">Confirm Password</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-mobile" name="mobile" class="form-control" placeholder="" aria-label="" />
            <label for="add-mobile">Mobile</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-address" name="address" class="form-control" placeholder="" aria-label="address" />
            <label for="add-address">Address</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <select id="add-user-group" name="user_group_id" class="select2 form-select">
              <option value="">Select</option>
              @foreach($userGroups as $group)
              <option value="{{ $group->id }}">{{ $group->title }}</option>
             @endforeach
            </select>
            <label for="user_group_id">User Type</label>
          </div>

          <!-- <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-tps" name="tps" class="form-control" placeholder="" aria-label="tps" />
            <label for="add-tps">TPS</label>
          </div> -->
          {{-- <div class="form-floating form-floating-outline mb-5">
            <select id="add-sms-rate" name="sms_rate_id" class="select2 form-select">
              <option value="">Select</option>
              @foreach($smsRates as $rate)
              <option value="{{ $rate->id }}">{{ $rate->rate_name }}</option>
             @endforeach
            </select>
            <label for="user_group_id">SMS Rate</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <select id="add-sms-senderId" name="sms_senderId" class="select2 form-select">
              <option value="">Select</option>
              @foreach($senderIds as $senderId)
                <option value="{{ $senderId->id }}">{{ $senderId->senderid }}</option>
              @endforeach
            </select>
            <label for="sms_senderId">Sender ID</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <select id="add-sms-mask" name="sms_mask" class="select2 form-select">
              <option value="">Select</option>
              @foreach($maskList as $mask)
                <option value="{{ $mask->id }}">{{ $mask->mask }}</option>
              @endforeach
            </select>
            <label for="sms_senderId">Mask</label>
          </div> --}}

          <!-- <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-reve-api-key" required name="user_reve_api_key" class="form-control" placeholder="" aria-label="" />
            <label for="add-mobile">Api Key</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-reve-secret-key" required name="user_reve_secret_key" class="form-control" placeholder="" aria-label="" />
            <label for="add-mobile">Secret Key</label>
          </div> -->

          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>

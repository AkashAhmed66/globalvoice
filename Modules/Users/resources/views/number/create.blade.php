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
                <input class="form-check-input" type="radio" name="type" id="type-ipt" value="IPT" checked>
                <label class="form-check-label" for="type-ipt">
                  IPT
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="type" id="type-short-code" value="Short Code">
                <label class="form-check-label" for="type-short-code">
                  Short Code
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="type" id="type-toll-free" value="Toll Free">
                <label class="form-check-label" for="type-toll-free">
                  Toll Free
                </label>
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

          <!-- Add Range -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <label class="form-label fw-semibold mb-0">Add Range</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="add-range" name="add_range" value="1">
            </div>
          </div>

          <!-- Channel -->
          <div class="form-floating form-floating-outline mb-4">
            <select id="add-channel" name="channel" class="select2 form-select">
              <option value="">Channel</option>
              @if(isset($channels))
                @foreach($channels as $channel)
                  <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                @endforeach
              @endif
            </select>
            <label for="add-channel">Channel</label>
          </div>

          <!-- DID Balance -->
          <div class="d-flex justify-content-between align-items-center mb-5">
            <label class="form-label fw-semibold mb-0">DID Balance</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="did-balance" name="did_balance" value="1">
            </div>
          </div>

          <button type="submit" class="btn btn-success me-sm-3 me-1 data-submit">Save</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>




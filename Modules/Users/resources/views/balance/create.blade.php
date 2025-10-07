<!-- ADD NEW RECORD -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRecord" aria-labelledby="offcanvasAddUserLabel">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">New</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-balance pt-0" id="addNewBalanceForm">

          <!-- To -->
          <div class="mb-4">
            <label class="form-label fw-semibold">To <span class="text-danger">*</span></label>
            <div class="d-flex gap-4 mt-2">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="to" id="to-client" value="Client" checked>
                <label class="form-check-label" for="to-client">
                  Client
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="to" id="to-did" value="DID">
                <label class="form-check-label" for="to-did">
                  DID
                </label>
              </div>
            </div>
          </div>

          <!-- Client -->
          <div class="form-floating form-floating-outline mb-4">
            <select id="add-client" name="client" class="select2 form-select">
              <option value="">--</option>
              @if(isset($clients))
                @foreach($clients as $key => $client)
                  <option value="{{ $key }}">{{ $client }}</option>
                @endforeach
              @endif
            </select>
            <label for="add-client">Client <span class="text-danger">*</span></label>
          </div>

          <!-- Amount -->
          <div class="form-floating form-floating-outline mb-5">
            <input type="number" class="form-control" id="add-amount" placeholder="1" name="amount" aria-label="Amount" step="0.01" min="0" />
            <label for="add-amount">Amount <span class="text-danger">*</span></label>
          </div>

          <button type="submit" class="btn btn-success me-sm-3 me-1 data-submit">Save</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>

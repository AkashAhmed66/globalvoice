<!-- ADD NEW RECORD -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRecord" aria-labelledby="offcanvasAddUserLabel">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add Reseller</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-user pt-0" id="addNewResellerForm">
          
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" class="form-control" id="add-reseller-name" placeholder="John Doe" name="reseller_name" aria-label="John Doe" />
            <label for="add-reseller-name">Reseller Name</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-phone" class="form-control" placeholder="john.doe@example.com" aria-label="john.doe@example.com" name="phone" />
            <label for="add-phone">Phone</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-email" class="form-control" placeholder="john.doe@example.com" aria-label="john.doe@example.com" name="email" />
            <label for="add-email">Email</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-address" name="address" class="form-control" placeholder="Web Developer" aria-label="address" />
            <label for="add-address">Address</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-thana" name="thana" class="form-control" placeholder="Web Developer" aria-label="thana" />
            <label for="add-thana">Thana</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-district" name="district" class="form-control" placeholder="Web Developer" aria-label="district" />
            <label for="add-district">District</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <select id="add-sms-rate" name="sms_rate_id" class="select2 form-select">
              <option value="">Select Rate</option>
              @foreach($smsRates as $rate)
              <option value="{{ $rate->id }}">{{ $rate->rate_name }}</option>
             @endforeach
            </select>
            <label for="sms-rate">SMS Rate</label>
          </div>

          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-tps" name="tps" class="form-control" placeholder="Web Developer" aria-label="tps" />
            <label for="add-tps">TPS</label>
          </div>
          <div class="form-floating form-floating-outline mb-5">
            <input type="text" id="add-url" name="url" class="form-control" placeholder="Web Developer" aria-label="url" />
            <label for="add-url">URL</label>
          </div>
          <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>
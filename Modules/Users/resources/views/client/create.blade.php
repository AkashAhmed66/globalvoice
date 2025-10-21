<!-- ADD NEW RECORD -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRecord" aria-labelledby="offcanvasAddUserLabel" style="width: 1500px;">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">New</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-client pt-0" id="addNewClientForm">

          <!-- Name -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-name" placeholder="name" name="name" aria-label="Name" />
            <label for="add-name">Name <span class="text-danger">*</span></label>
          </div>

          <!-- Address -->
          <div class="form-floating form-floating-outline mb-4">
            <textarea class="form-control" id="add-address" placeholder="Address" name="address" aria-label="Address" rows="3" style="min-height: 80px;"></textarea>
            <label for="add-address">Address</label>
          </div>

          <!-- Contact Name -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-contact-name" placeholder="Contact Name" name="contact_name" aria-label="Contact Name" />
            <label for="add-contact-name">Contact Name <span class="text-danger">*</span></label>
          </div>

          <!-- Contact Number -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-contact-number" placeholder="Contact Number" name="contact_no" aria-label="Contact Number" />
            <label for="add-contact-number">Contact Number <span class="text-danger">*</span></label>
          </div>

          <!-- Mail -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="email" class="form-control" id="add-mail" placeholder="Mail" name="mail" aria-label="Mail" />
            <label for="add-mail">Mail <span class="text-danger">*</span></label>
          </div>

          <!-- Web Name -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-web-name" placeholder="Web Name" name="web_name" aria-label="Web Name" />
            <label for="add-web-name">Web Name <span class="text-danger">*</span></label>
          </div>

          <!-- Password -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="password" class="form-control" id="add-password" placeholder="Password" name="password" aria-label="Password" autocomplete="new-password" />
            <label for="add-password">Password <span class="text-danger">*</span></label>
          </div>

          <!-- District -->
          <div class="form-floating form-floating-outline mb-4">
            <select id="add-district" name="district" class="select2 form-select">
              <option value="">--</option>
              @if(isset($districts))
                @foreach($districts as $district)
                  <option value="{{ $district }}">{{ $district }}</option>
                @endforeach
              @endif
            </select>
            <label for="add-district">District <span class="text-danger">*</span></label>
          </div>

          <!-- Zone -->
          @php
            $zones = ['Central', 'South-East'];
            $selectedZone = old('zone', $user->zone ?? ''); // Adjust this as needed
          @endphp

          <div class="form-floating form-floating-outline mb-4">
            <select id="add-zone" name="zone" class="select2 form-select" required>
              <option value="">--</option>
              @foreach($zones as $zone)
                <option value="{{ $zone }}" {{ $selectedZone === $zone ? 'selected' : '' }}>
                  {{ $zone }}
                </option>
              @endforeach
            </select>
            <label for="add-zone">Zone <span class="text-danger">*</span></label>
          </div>

          <!-- Credit Limit -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="number" class="form-control" id="add-credit-limit" placeholder="500" name="credit_limit" aria-label="Credit Limit" value="500" step="0.01" min="0" />
            <label for="add-credit-limit">Credit Limit <span class="text-danger">*</span></label>
          </div>

          <!-- Tariff -->
          <div class="form-floating form-floating-outline mb-4">
            <select id="add-tariff" name="tariff" class="select2 form-select">
              <option value="">--</option>
              @if(isset($tariffs))
                @foreach($tariffs as $key => $tariff)
                  <option value="{{ $key }}">{{ $tariff }}</option>
                @endforeach
              @endif
            </select>
            <label for="add-tariff">Tariff <span class="text-danger">*</span></label>
          </div>

          <!-- Enable ISD -->
          <div class="d-flex justify-content-between align-items-center mb-5">
            <label class="form-label fw-semibold mb-0">Enable ISD <span class="text-danger">*</span></label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="enable-isd" name="enable_isd" value="1">
            </div>
          </div>

          <!-- Services Section -->
          <div class="mb-4">
            <h6 class="fw-semibold mb-3">Services:</h6>
            <div class="table-responsive">
              <table class="table table-bordered" id="servicesTable">
                <thead class="table-light">
                  <tr>
                    <th style="width: 150px;">Service Type</th>
                    <th style="width: 150px;">Service Name</th>
                    <th style="width: 100px;">OTC</th>
                    <th style="width: 100px;">MRC</th>
                    <th style="width: 130px;">Launch Date</th>
                    <th style="width: 130px;">Bill Start Date</th>
                    <th style="width: 80px;">Actions</th>
                  </tr>
                </thead>
                <tbody id="servicesTableBody">
                  <tr>
                    <td>
                      <input type="hidden" name="services[1][id]" value="0">
                      <select id="service-type-template" class="form-select form-select-sm" name="services[1][service_type]">
                        <option value="">--Service Type</option>
                        @if(isset($service_type))
                          @foreach($service_type as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                          @endforeach
                        @endif
                      </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm" name="services[1][service_name]" placeholder="Service Name"></td>
                    <td><input type="number" class="form-control form-control-sm" name="services[1][otc]" placeholder="OTC" step="0.01" min="0"></td>
                    <td><input type="number" class="form-control form-control-sm" name="services[1][mrc]" placeholder="MRC" step="0.01" min="0"></td>
                    <td><input type="date" class="form-control form-control-sm" name="services[1][launch_date]" placeholder="Launch Date"></td>
                    <td><input type="date" class="form-control form-control-sm" name="services[1][bill_start_date]" placeholder="Bill Start Date"></td>
                    <td>
                      <button type="button" class="btn btn-primary btn-sm me-1" id="addServiceRow">
                        <i class="ri-add-line"></i>
                      </button>
                      <button type="button" class="btn btn-danger btn-sm remove-service-row" style="display: none;">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <button type="submit" class="btn btn-success me-sm-3 me-1 data-submit">Save</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>

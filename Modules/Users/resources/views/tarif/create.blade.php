<!-- ADD NEW RECORD -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRecord" aria-labelledby="offcanvasAddUserLabel" style="width: 1000px;">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">New</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-tarif pt-0" id="addNewTarifForm">

          <!-- Name -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-name" placeholder="name" name="name" aria-label="Name" />
            <label for="add-name">Name <span class="text-danger">*</span></label>
          </div>

          <!-- Pulse -->
          <div class="form-floating form-floating-outline mb-4">
            <select id="add-pulse" name="pulse" class="select2 form-select">
              <option value="">Local pulse</option>
              @if(isset($pulses))
                @foreach($pulses as $pulse)
                  <option value="{{ $pulse->id }}">{{ $pulse->name }}</option>
                @endforeach
              @endif
            </select>
            <label for="add-pulse">Pulse <span class="text-danger">*</span></label>
          </div>

          <!-- Details Section -->
          <div class="mb-4">
            <h6 class="fw-semibold mb-3">Details:</h6>
            <div class="table-responsive">
              <table class="table table-bordered" id="tarifDetailsTable">
                <thead class="table-light">
                  <tr>
                    <th style="width: 60px;">#</th>
                    <th style="width: 140px;">Operator Prefix</th>
                    <th style="width: 200px;">Name</th>
                    <th style="width: 120px;">Rate</th>
                    <th style="width: 140px;">Status</th>
                  </tr>
                </thead>
                <tbody id="tarifDetailsBody">
                  <tr>
                    <td class="text-center fw-bold">1.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[1][operator_prefix]" placeholder="013" value="013"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[1][name]" placeholder="GrameenPhone" value="GrameenPhone"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[1][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[1][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">2.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[2][operator_prefix]" placeholder="017" value="017"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[2][name]" placeholder="GrameenPhone" value="GrameenPhone"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[2][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[2][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">3.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[3][operator_prefix]" placeholder="014" value="014"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[3][name]" placeholder="Banglalink" value="Banglalink"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[3][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[3][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">4.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[4][operator_prefix]" placeholder="019" value="019"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[4][name]" placeholder="Banglalink" value="Banglalink"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[4][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[4][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">5.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[5][operator_prefix]" placeholder="018" value="018"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[5][name]" placeholder="Robi" value="Robi"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[5][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[5][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">6.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[6][operator_prefix]" placeholder="016" value="016"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[6][name]" placeholder="Robi [Airtel]" value="Robi [Airtel]"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[6][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[6][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">7.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[7][operator_prefix]" placeholder="015" value="015"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[7][name]" placeholder="Teletalk" value="Teletalk"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[7][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[7][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">8.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[8][operator_prefix]" placeholder="096" value="096"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[8][name]" placeholder="IPTSP" value="IPTSP"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[8][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[8][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">9.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[9][operator_prefix]" placeholder="10" value="10"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[9][name]" placeholder="Medical Short Code" value="Medical Short Code"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[9][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[9][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">10.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[10][operator_prefix]" placeholder="13" value="13"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[10][name]" placeholder="Travel Short Code" value="Travel Short Code"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[10][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[10][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">11.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[11][operator_prefix]" placeholder="16" value="16"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[11][name]" placeholder="General Short Code" value="General Short Code"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[11][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[11][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">12.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[12][operator_prefix]" placeholder="02" value="02"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[12][name]" placeholder="BTCL" value="BTCL"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[12][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[12][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">13.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[13][operator_prefix]" placeholder="03" value="03"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[13][name]" placeholder="BTCL CTG" value="BTCL CTG"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[13][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[13][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">14.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[14][operator_prefix]" placeholder="04" value="04"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[14][name]" placeholder="BTCL Khulna" value="BTCL Khulna"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[14][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[14][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">15.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[15][operator_prefix]" placeholder="05" value="05"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[15][name]" placeholder="BTCL Bogra" value="BTCL Bogra"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[15][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[15][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">16.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[16][operator_prefix]" placeholder="06" value="06"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[16][name]" placeholder="BTCL Faridpur" value="BTCL Faridpur"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[16][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[16][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">17.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[17][operator_prefix]" placeholder="07" value="07"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[17][name]" placeholder="BTCL Rajshahi" value="BTCL Rajshahi"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[17][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[17][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">18.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[18][operator_prefix]" placeholder="08" value="08"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[18][name]" placeholder="BTCL Sylhet" value="BTCL Sylhet"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[18][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[18][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">19.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[19][operator_prefix]" placeholder="09" value="09"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[19][name]" placeholder="BTCL Mymensigh" value="BTCL Myensigh"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[19][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[19][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center fw-bold">20.</td>
                    <td><input type="text" class="form-control form-control-sm" name="details[20][operator_prefix]" placeholder="0800" value="0800"></td>
                    <td><input type="text" class="form-control form-control-sm" name="details[20][name]" placeholder="Toll Free" value="Toll Free"></td>
                    <td><input type="number" class="form-control form-control-sm" name="details[20][rate]" value="0" step="0.01" min="0"></td>
                    <td>
                      <select class="form-select form-select-sm" name="details[20][status]">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                      </select>
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

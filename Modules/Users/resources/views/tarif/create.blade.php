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
            <input
              type="number"
              id="add-pulse"
              name="pulse"
              class="form-control"
              placeholder="Enter pulse value"
              required
            >
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
                    @foreach($opPrefixes as $index => $prefix)
                        <tr>
                            <td class="text-center fw-bold">{{ $index + 1 }}.</td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                      name="details[{{ $index + 1 }}][operator_prefix]"
                                      value="{{ $prefix->prefix }}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                      name="details[{{ $index + 1 }}][name]"
                                      value="{{ $prefix->detail_name }}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm"
                                      name="details[{{ $index + 1 }}][rate]"
                                      value="0" step="0.01" min="0">
                            </td>
                            <td>
                                <select class="form-select form-select-sm"
                                        name="details[{{ $index + 1 }}][status]">
                                    <option value="Active" selected>Active</option>
                                    <option value="Deactive">Deactive</option>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <button type="submit" class="btn btn-success me-sm-3 me-1 data-submit">Save</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>

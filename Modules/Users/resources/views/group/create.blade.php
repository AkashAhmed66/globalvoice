<!-- ADD NEW RECORD -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRecord" aria-labelledby="offcanvasAddUserLabel">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User Group</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-user pt-0" id="addNewUserGroupForm">
          <input type="hidden" name="id" id="user_id">
          
          <!-- Name Field -->
          <div class="mb-4">
            <label for="add-title" class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="add-title" placeholder="Name" name="name" aria-label="Name" required />
          </div>

          <!-- Roles Section -->
          <div class="mb-4">
            <label class="form-label">Roles</label>
            <div class="row">
              <div class="col-12">
                @if(isset($roles) && count($roles) > 0)
                  @foreach($roles as $key => $role)
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" id="{{ $key }}" name="permissions[]" value="{{ $key }}">
                      <label class="form-check-label" for="{{ $key }}">{{ $role }}</label>
                    </div>
                  @endforeach
                @else
                  <p class="text-muted">No roles available</p>
                @endif
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-start gap-3 mt-4">
            <button type="submit" class="btn btn-success data-submit">Save</button>
            <button type="reset" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cancel</button>
          </div>
        </form>
      </div>
    </div>
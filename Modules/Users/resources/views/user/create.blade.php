<!-- ADD NEW RECORD -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRecord" aria-labelledby="offcanvasAddUserLabel">
      <div class="offcanvas-header border-bottom">
        <h5 id="offcanvasAddUserLabel" class="offcanvas-title">New</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body mx-0 flex-grow-0 h-100">
        <form class="add-new-user pt-0" id="addNewUserForm1">

          <!-- Full Name -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-full-name" placeholder="Full Name" name="full_name" aria-label="Full Name" />
            <label for="add-full-name">Full Name <span class="text-danger">*</span></label>
          </div>

          <!-- Name -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" class="form-control" id="add-name" placeholder="Name" name="name" aria-label="Name" />
            <label for="add-name">Name <span class="text-danger">*</span></label>
          </div>

          <!-- Email -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="email" id="add-email" class="form-control" placeholder="Email" aria-label="Email" name="email" />
            <label for="add-email">Email <span class="text-danger">*</span></label>
          </div>

          <!-- Mobile -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="text" id="add-mobile" name="mobile" class="form-control" placeholder="Mobile" aria-label="Mobile" />
            <label for="add-mobile">Mobile <span class="text-danger">*</span></label>
          </div>

          <!-- Password -->
          <div class="form-floating form-floating-outline mb-4">
            <input type="password" id="add-password" class="form-control" placeholder="Password" aria-label="Password" name="password" autocomplete="new-password" />
            <label for="add-password">Password <span class="text-danger">*</span></label>
          </div>

          <!-- User Group -->
          <div class="form-floating form-floating-outline mb-5">
            <select id="add-user-group" name="user_group_id" class="select2 form-select">
              <option value="">--</option>
              @foreach($userGroups as $group)
              <option value="{{ $group->id }}">{{ $group->name }}</option>
             @endforeach
            </select>
            <label for="add-user-group">User Group <span class="text-danger">*</span></label>
          </div>

          <button type="submit" class="btn btn-success me-sm-3 me-1 data-submit">Save</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </form>
      </div>
    </div>

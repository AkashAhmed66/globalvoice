@extends('layouts/layoutMaster')

@section('title', $title)

<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/tagify/tagify.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    'resources/assets/vendor/libs/animate-css/animate.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/tagify/tagify.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/cleavejs/cleave.js',
    'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite(['resources/assets/js/pages-account-settings-account.js'])
@endsection

@section('content')
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-6">
        <!-- Account -->
        <div class="card-body">
          <div class="d-flex align-items-start align-items-sm-center gap-6">
            <img src="{{ $user->photo ? $user->photo : asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="d-block w-px-100 h-px-100 rounded-4" id="uploadedAvatar" />
            <div class="button-wrapper">
              <label for="upload" class="btn btn-primary me-3 mb-4" tabindex="0">
                <span class="d-none d-sm-block">Upload new photo</span>
                <i class="ri-upload-2-line d-block d-sm-none"></i>
                <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg" />
              </label>
              <button type="button" class="btn btn-outline-danger account-image-reset mb-4">
                <i class="ri-refresh-line d-block d-sm-none"></i>
                <span class="d-none d-sm-block">Reset</span>
              </button>

              <div>Allowed JPG, GIF or PNG. Max size of 800K</div>
            </div>
            @if(session()->has('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <strong>Success!</strong> {{ session()->get('success') }}
              </div>
            @endif
          </div>
        </div>
        <div class="card-body pt-0">
          <form id="formAccountSettings" method="POST" action="{{url('profile-update')}}">
            @csrf
            <div class="row mt-1 g-5">
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input class="form-control" type="text" id="firstName" name="name" value="{{$user->name}}" autofocus />
                  <label for="firstName">Name</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input class="form-control" type="text" value="{{$user->username}}" readonly disabled />
                  <label for="firstName">User Name</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input class="form-control" type="text" id="email" name="email" value="{{$user->email}}" placeholder="john.doe@example.com" />
                  <label for="email">E-mail</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="password" name="password" class="form-control" value="" />
                    <label for="password">Password</label>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="phoneNumber" name="mobile" class="form-control" value="{{$user->mobile}}" />
                    <label for="phoneNumber">Phone Number</label>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="confirm_password" name="confirm_password" class="form-control" value="" />
                    <label for="confirm_password">Confirm Password</label>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control" id="address" name="address" value="{{$user->address}}" />
                  <label for="address">Address</label>
                </div>
              </div>

              <div></div>

              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control" id="push_pull_url" name="push_pull_url" value="{{$user->push_pull_url}}" placeholder="http://abc.com" />
                  <label for="push_pull_url">Push Pull Url</label>
                </div>
              </div>
              <div></div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline">
                  <input type="text" class="form-control" id="dlr_url" name="dlr_url" value="{{$user->dlr_url}}" placeholder="http://abc.com" />
                  <label for="dlr_url">Dlr Url</label>
                </div>
              </div>
            </div>
            <div class="mt-6">
              <button type="submit" class="btn btn-primary me-3">Save changes</button>
              <button type="reset" class="btn btn-outline-secondary">Reset</button>
            </div>
          </form>
        </div>
        <!-- /Account -->
      </div>
    </div>
  </div>
@endsection

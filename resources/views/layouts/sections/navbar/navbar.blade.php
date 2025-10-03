@php
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Facades\DB;
  $containerNav = ($configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
  $navbarDetached = ($navbarDetached ?? '');
  
  // Get user group title if user is authenticated
  $userGroup = null;
  if (Auth::check()) {
    $userGroup = DB::table('user_group')->where('id', Auth::user()->user_group_id)->value('name');
  }
@endphp

<style>
.user-info-rubber {
  background: linear-gradient(145deg, #ffffff, #f8f9fa);
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 50px;
  box-shadow: 
    0 2px 8px rgba(0,0,0,0.1),
    0 1px 3px rgba(0,0,0,0.08),
    inset 0 1px 0 rgba(255,255,255,0.8);
  transition: all 0.2s ease;
  backdrop-filter: blur(10px);
}

.user-info-rubber:hover {
  transform: translateY(-1px);
  box-shadow: 
    0 4px 12px rgba(0,0,0,0.15),
    0 2px 6px rgba(0,0,0,0.1),
    inset 0 1px 0 rgba(255,255,255,0.9);
}

.user-info-rubber:active {
  transform: translateY(0);
  box-shadow: 
    0 1px 4px rgba(0,0,0,0.2),
    inset 0 1px 3px rgba(0,0,0,0.1);
}
</style>

  <!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
  <nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
    @endif
    @if(isset($navbarDetached) && $navbarDetached == '')
      <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{$containerNav}}">
          @endif

          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                <i class="ri-menu-fill ri-22px"></i>
              </a>
          </div>

          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

            {{--<ul class="navbar-nav flex-row align-items-center ms-auto">
                @if (Auth::check())
                     <li>
                       <div class="d-grid px-4 pt-2 pb-1">
                         <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">

                           <i class="ri-logout-box-r-line ms-2 ri-16px"></i>
                         </a>
                       </div>
                     </li>
                     <form method="POST" id="logout-form" action="{{ route('logout') }}">
                       @csrf
                     </form>
                     @endif

             </ul>--}}

            <!-- User -->
            <ul class="navbar-nav flex-row align-items-center ms-auto">
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                  @if (Auth::check())
                    <div class="d-flex align-items-center user-info-rubber px-3 py-2">
                      <div class="d-flex flex-column text-end me-3">
                        <span class="fw-medium text-dark small mb-0" style="line-height: 1.2;">{{ Auth::user()->name }}</span>
                        <small class="text-muted" style="font-size: 0.75rem; line-height: 1;">{{ $userGroup }}</small>
                      </div>
                      <div class="avatar avatar-online">
                        <img src="{{ Auth::check() && Auth::user()->photo ? Auth::user()->photo : asset('assets/img/avatars/1.png') }}" alt class="rounded-circle">
                      </div>
                    </div>
                  @else
                    <div class="avatar avatar-online">
                      <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="rounded-circle">
                    </div>
                  @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="{{ url('profile') }}">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-2">
                          <div class="avatar avatar-online">
                            <img src="{{ Auth::check() && Auth::user()->photo ? Auth::user()->photo : asset('assets/img/avatars/1.png') }}" alt class="rounded-circle">
                          </div>
                        </div>
                        <div class="flex-grow-1">
                      <span class="fw-medium d-block small">
                        @if (Auth::check())
                          {{ Auth::user()->name }}
                        @else
                          John Doe
                        @endif
                      </span>
                          <small class="text-muted">{{ $userGroup }}</small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{ url('profile') }}">
                      <i class="ri-user-3-line ri-22px me-3"></i><span class="align-middle">My Profile</span>
                    </a>
                  </li>
                  <li>
                    @if (\Illuminate\Support\Facades\Session::has('hasClonedUser'))
                      <a class="dropdown-item" href="{{ route('users-login-as', \Illuminate\Support\Facades\Session::get('hasClonedUser')) }}">
                        <i class="ri-user-3-line ri-22px me-3"></i><span class="align-middle">Back to Users</span>
                    @endif
                    @if (\Illuminate\Support\Facades\Session::has('hasClonedAnotherUser'))
                      <a class="dropdown-item" href="{{ route('users-login-as', \Illuminate\Support\Facades\Session::get('hasClonedAnotherUser')) }}">
                        <i class="ri-user-3-line ri-22px me-3"></i><span class="align-middle">Back to Admin</span>
                    @endif
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  @if (Auth::check())
                    <li>
                      <div class="d-grid px-4 pt-2 pb-1">
                        <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                          <small class="align-middle">Logout</small>
                          <i class="ri-logout-box-r-line ms-2 ri-16px"></i>
                        </a>
                      </div>
                    </li>
                    <form method="POST" id="logout-form" action="{{ route('logout') }}">
                      @csrf
                    </form>
                  @else
                    <li>
                      <div class="d-grid px-4 pt-2 pb-1">
                        <a class="btn btn-sm btn-danger d-flex" href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                          <small class="align-middle">Login</small>
                          <i class="ri-logout-box-r-line ms-2 ri-16px"></i>
                        </a>
                      </div>
                    </li>
                  @endif
                </ul>
              </li>
            </ul>
            <!--/ User -->
          </div>





          <!-- Search Small Screens -->
          <div class="navbar-search-wrapper search-input-wrapper {{ isset($menuHorizontal) ? $containerNav : '' }} d-none">
            <input type="text" class="form-control search-input {{ isset($menuHorizontal) ? '' : $containerNav }} border-0" placeholder="Search..." aria-label="Search...">
            <i class="ri-close-fill search-toggler cursor-pointer"></i>
          </div>
          <!--/ Search Small Screens -->
          @if(!isset($navbarDetached))
        </div>
        @endif
      </nav>
      <!-- / Navbar -->

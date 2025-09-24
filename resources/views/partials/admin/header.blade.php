<!--begin::Header-->
<nav id="header" class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <!--begin::Header container-->
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <!--begin::Sidebar toggle-->
        <button class="btn btn-outline-primary d-lg-none me-2" id="sidebarToggle" title="Show sidebar menu">
            <i class="fas fa-bars"></i>
        </button>
        
        <!--begin::Logo image-->
        <a class="navbar-brand d-lg-none ms-3" href="{{ route('dashboard') }}">
            @if($setting->logo && file_exists(public_path('data/setting/' . $setting->logo)))
                <img src="{{ image_check($setting->logo,'setting') }}" alt="Logo" class="img-fluid" style="height:30px;">
            @else
                <span class="fw-bold">{{ $setting->meta_title }}</span>
            @endif
        </a>
        <!--end::Logo for mobile-->

        <!--begin::Navbar items-->
        <div class="ms-auto d-flex align-items-center">
            <!--begin::User menu-->
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-2"></i>
                    {{ session(config('session.prefix').'_nama') ?? 'User' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('logout') }}"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
            <!--end::User menu-->
        </div>
        <!--end::Navbar items-->
    </div>
</nav>
<!--end::Header-->
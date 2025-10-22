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
            
            <!--end::User menu-->
        </div>
        <!--end::Navbar items-->
    </div>
</nav>
<!--end::Header-->
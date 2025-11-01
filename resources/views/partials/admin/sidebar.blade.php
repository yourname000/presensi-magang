<!--begin::Sidebar-->
<nav id="sidebar">
    <div class="sidebar-header text-center py-3">
        @if(isset($setting->logo))
            <img src="{{ image_check($setting->logo,'setting') }}" alt="Logo" class="img-fluid" style="max-height: 60px;">
        @endif
    </div>

    <!--begin::Sidebar menu-->
    <div class="sidebar-menu">
        <ul class="nav flex-column">

            <!-- {{-- 🔹 Menu umum (semua user) --}}
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ $segment1 == 'dashboard' && !$segment2 ? 'active' : '' }}">
                    <i class="fa-solid fa-house-chimney me-2"></i> Beranda
                </a>
            </li>

            {{-- 🔹 Menu khusus untuk Karyawan --}}
            @if(session(config('session.prefix') . '_peran') == 2)
                <li class="sidebar-heading">PROFIL</li>
                <li class="nav-item">
                    <a href="#" onclick="set_modal_profile()" data-bs-toggle="modal" data-bs-target="#kt_modal_profile" class="nav-link">
                        <i class="fa-solid fa-user me-2"></i> Profil
                    </a>
                </li>
            @endif -->

            {{-- 🔹 Menu khusus untuk Admin --}}
            @if(session(config('session.prefix') . '_peran') == 1)
                <li class="sidebar-heading">MANAJEMEN KARYAWAN</li>
                <li class="nav-item">
                    <a href="{{ route('master.departemen') }}" class="nav-link {{ $segment1 == 'master' && $segment2 == 'departemen' ? 'active' : '' }}">
                        <i class="fa-solid fa-layer-group me-2"></i> Departemen
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('master.karyawan') }}" class="nav-link {{ $segment1 == 'master' && $segment2 == 'karyawan' ? 'active' : '' }}">
                        <i class="fa-solid fa-users me-2"></i> Data Karyawan
                    </a>
                </li>

                <li class="sidebar-heading">MANAJEMEN ABSENSI</li>
                <li class="nav-item">
                    <a href="{{ route('presensi.report') }}" class="nav-link {{ ($segment1 == 'laporan' && $segment2 == 'presensi') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-check me-2"></i> Absensi
                    </a>
                </li>

                <li class="sidebar-heading">PENGATURAN</li>
                <li class="nav-item">
                    <a href="{{ route('pengaturan') }}" class="nav-link {{ $segment1 == 'pengaturan' ? 'active' : '' }}">
                        <i class="fa-solid fa-gear me-2"></i> Pengaturan
                    </a>
                </li>

                <li class="sidebar-heading">ADMIN</li>
            @endif

            {{-- 🔹 Menu umum (semua user) --}}
            <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar
                </a>
            </li>
        </ul>
    </div>
    <!--end::Sidebar menu-->
</nav>
<!--end::Sidebar-->

<!-- Overlay for mobile -->
<div class="overlay"></div>

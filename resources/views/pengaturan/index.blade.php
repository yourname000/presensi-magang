@extends('layouts.admin')

@push('styles')
<style>
    .pengaturan-tab-header {
        display: flex;
        justify-content: space-between;
        border-bottom: 2px solid #ddd;
        position: relative;
    }

    .pengaturan-tab-link {
        flex: 1;
        text-align: center;
        padding: 10px;
        font-weight: bold;
        cursor: pointer;
        color: #333;
        font-size: 17px;
    }

    .pengaturan-tab-link.active {
        color: #0d6efd; /* Bootstrap primary color */
    }

    .pengaturan-tab-underline {
        position: absolute;
        bottom: -2px;
        height: 2px;
        background: #0d6efd;
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('script')
<script src="{{ asset('assets/admin/js/modul/pengaturan/all.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid">
    @include('partials.admin.heading')

    <div class="row mb-4">
        <div class="col-lg-10 mx-auto">
            <!-- Card -->
            <div class="card">
                <div class="card-body">
                    <!-- Tab Header -->
                    <div class="pengaturan-tab-header" id="pengaturanTabHeader">
                        <div class="pengaturan-tab-link {{ (!$page || $page == 'lokasi') ? 'active' : '' }}" onclick="set_url_params('lokasi')" data-bs-target="#pengaturan-lokasi">Lokasi</div>
                        <div class="pengaturan-tab-link {{ ($page == 'shift') ? 'active' : '' }}" onclick="set_url_params('shift')" data-bs-target="#pengaturan-shift">Shift & Jam Kerja</div>
                        <div class="pengaturan-tab-link {{ ($page == 'website') ? 'active' : '' }}" onclick="set_url_params('website')" data-bs-target="#pengaturan-website">Website</div>
                        <div class="pengaturan-tab-underline" id="pengaturanTabUnderline"></div>
                    </div>

                    <!-- Tab Content -->
                    <div class="pengaturan-tab-content mt-3">
                        <div id="pengaturan-lokasi" class="{{ (!$page || $page == 'lokasi') ? '' : 'd-none' }} my-3">
                            @include('pengaturan.page.location')
                        </div>
                        <div id="pengaturan-shift" class="{{ ($page != 'shift') ? 'd-none' : '' }} my-3">
                            @include('pengaturan.page.shift')
                        </div>
                        <div id="pengaturan-website" class="{{ ($page != 'website') ? 'd-none' : '' }} my-3">
                            @include('pengaturan.page.website')
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Card -->
        </div>
    </div>
</div>
@endsection

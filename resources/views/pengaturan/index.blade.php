@extends('layouts.admin')

@push('styles')
<style>
    .nav-tabs .nav-link.active {
        color: #4A9289 !important;
        font-weight: bold;
        border-color: #4A9289 #4A9289 #fff;
    }
    .nav-tabs .nav-link {
        font-size: 17px;
        color: #333;
    }
    .pengaturan-card {
    margin-top: 30px;
    }
</style>
@endpush

@push('script')
<script src="{{ asset('assets/js/all.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid p-0">
    @include('partials.admin.heading')

    <div class="row mb-4">
        <div class="col-lg-10 mx-auto">
            
            {{-- Card --}}
            <div class="card pengaturan-card">
                <div class="card-body">
                    <!-- Tab Header -->
                    <ul class="nav nav-tabs" id="pengaturanTab" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link {{ (!$page || $page == 'lokasi') ? 'active' : '' }}" id="lokasi-tab" data-bs-toggle="tab" data-bs-target="#pengaturan-lokasi" type="button" role="tab">Lokasi</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link {{ ($page == 'shift') ? 'active' : '' }}" id="shift-tab" data-bs-toggle="tab" data-bs-target="#pengaturan-shift" type="button" role="tab">Shift & Jam Kerja</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link {{ ($page == 'website') ? 'active' : '' }}" id="website-tab" data-bs-toggle="tab" data-bs-target="#pengaturan-website" type="button" role="tab">Website</button></li>
                    </ul>


                    <!-- Tab Content -->
                    <div class="tab-content mt-3" id="pengaturanTabContent">
                        <div class="tab-pane fade {{ (!$page || $page == 'lokasi') ? 'show active' : '' }}" id="pengaturan-lokasi" role="tabpanel" aria-labelledby="lokasi-tab">@include('pengaturan.page.location')</div>
                        <div class="tab-pane fade {{ ($page == 'shift') ? 'show active' : '' }}" id="pengaturan-shift" role="tabpanel" aria-labelledby="shift-tab">@include('pengaturan.page.shift')</div>
                        <div class="tab-pane fade {{ ($page == 'website') ? 'show active' : '' }}" id="pengaturan-website" role="tabpanel" aria-labelledby="website-tab">@include('pengaturan.page.website')</div>
                    </div>
                    </div>
                </div>
            </div>
            <!-- End Card -->
        </div>
    </div>
</div>
@endsection

@extends('layouts.auth')

@section('content')
<!--begin::Body-->
<div class="d-flex justify-content-center align-items-center p-4 w-100">
    <!--begin::Wrapper-->
    <div class="bg-white d-flex flex-column justify-content-center align-items-center rounded-4 shadow w-100" style="max-width: 650px; height: 600px;">
        <!--begin::Content-->
        <div class="d-flex flex-column align-items-stretch h-100 w-100 px-4">
            <!--begin::Wrapper-->
            <div class="d-flex flex-column justify-content-center flex-fill pb-4">
                
                {{-- Alert Flash Message --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!--begin::Form-->
                <form method="POST" class="w-100 needs-validation" novalidate action="{{ route('login.process') }}">
                    @csrf
                    <!--begin::Heading-->
                    <div class="d-flex justify-content-center align-items-center flex-column text-center mb-4">
                        @if(isset($setting->logo) && $setting->logo  && file_exists(public_path('data/setting/' . $setting->logo )))
                        <div class="mb-3">
                            <img src="{{ image_check($setting->logo,'setting') }}" alt="Logo" class="img-fluid" style="max-width: 200px; max-height: 100px; object-fit: contain;">
                            </div>
                        @endif
                        <!--begin::Subtitle-->
                        <div class="text-muted fw-semibold fs-5">Absensi Karyawan PT Hanampi Sejahtera Kahuripan</div>
                        <!--end::Subtitle=-->
                    </div>
                    <!--begin::Heading-->
                     <!--begin::Input group-->
                     <div class="mb-3" id="req_username">
                        <!--begin::Label-->
                        <label class="form-label fw-semibold">Nama Pengguna <span class="text-danger">*</span></label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="username" class="form-control bg-transparent" style="height:55px; border-radius:15px;" placeholder="Contoh : Admin" autocomplete="off">
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                     <!--begin::Input group-->
                    <div class="mb-3" id="req_kata_sandi">
                        <!--begin::Label-->
                        <label class="form-label fw-semibold">Kata Sandi <span class="text-danger">*</span></label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="password" name="kata_sandi" class="form-control bg-transparent" style="height:55px; border-radius:15px;" placeholder="Contoh : admin12345" autocomplete="off">
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Submit button-->
                    <div class="d-grid mt-4">
                        <button type="submit" id="button_login" class="btn btn-primary fs-5" style="height:55px; border-radius:15px;">
                            <!--begin::Indicator label-->
                            <span class="normal-text">Masuk</span>
                            <!--end::Indicator label-->
                            <!--begin::Indicator progress-->
                            <span class="loading-text d-none">Please wait... 
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            <!--end::Indicator progress-->
                        </button>
                    </div>
                    <!--end::Submit button-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Wrapper-->
</div>
<!--end::Body-->
@endsection
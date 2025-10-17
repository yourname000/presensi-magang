@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/employee.css') }}">

@endpush

@push('script')
<script src="{{ asset('assets/js/employee.js') }}"></script>
@endpush

@section('content')
<!--begin::Container-->
<div class="container-xxl" id="kt_content_container">
    <!--begin::Row-->
    <div class="row gx-5 gx-xl-10 mb-xl-10">
        @include('partials.admin.heading')
    </div>

      {{-- ALERT --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-xmark me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

    <div class="row gx-5 gx-xl-10 mb-xl-10">
        <div class="col-md-12 col-lg-12 col-xl-12 col-xxl-12 mb-4">
            <!--begin::Card widget 16-->
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-center border-0 h-md-100 mb-3 mb-xl-6 shadow-sm" style="background-color: #458c84;">
                <!--begin::Card body-->
                <div class="card-body d-flex justify-content-center py-7 flex-column">
                    <div class="row w-100">
                        <!-- Jam Digital -->
                        <div class="col-12 mb-7">
                            <div class="clock-box w-100 text-center">
                                <div id="time" class="time">00:00:00</div>
                                <div id="date" class="date">{{ $nowdate }}</div>
                            </div>
                        </div>

                       {{-- CARD JAM MASUK --}}
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                            <div class="card shadow-sm rounded-3 text-center p-3 py-7 d-flex justify-content-center align-items-center flex-column">
                                <h3 class="text-muted fs-3 fw-semibold mb-5">
                                    <i class="fa-solid fa-clock me-1"></i> Jam Masuk
                                </h3>
                                <h1 class="text-dark mb-5" style="font-weight:1000; font-size:2.5rem;">
                                    {{ $presensi && $presensi->scan_in ? \Carbon\Carbon::parse($presensi->scan_in)->format('H:i:s') : '00:00:00' }}
                                </h1>
                                <div class="w-100 d-flex justify-content-center align-items-center flex-column">
                                    
                                    @if(!$presensi || !$presensi->scan_in)
                                        {{-- Belum Absen --}}
                                        <span class="badge badge-secondary py-4 px-7 rounded-10">Belum Presensi</span>

                                    @elseif($presensi->terlambat == 'Y')
                                        {{-- Terlambat (berdasarkan field 'terlambat' == 'Y') --}}
                                        <span class="badge badge-danger py-4 px-7 rounded-10">Terlambat</span>
                                    @else
                                        {{-- Hadir / Tepat Waktu (terlambat == 'N') --}}
                                        <span class="badge badge-success py-4 px-7 rounded-10">Masuk Tepat Waktu</span>
                                        
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        {{-- CARD JAM PULANG --}}
                        <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                            <div class="card shadow-sm rounded-3 text-center p-3 py-7 d-flex justify-content-center align-items-center flex-column">
                                <h3 class="text-muted fs-3 fw-semibold mb-5">
                                    <i class="fa-solid fa-person-running me-1"></i> Jam Pulang
                                </h3>
                                <h1 class="text-dark mb-5" style="font-weight:1000; font-size:2.5rem;">
                                    {{ $presensi && $presensi->scan_out ? \Carbon\Carbon::parse($presensi->scan_out)->format('H:i:s') : '00:00:00' }}
                                </h1>
                                <div class="w-100 d-flex justify-content-center align-items-center flex-column">
                                    
                                    @if(!$presensi || !$presensi->scan_out)
                                        {{-- Belum Absen Pulang --}}
                                        @if($presensi && $presensi->scan_in)
                                            <span class="badge badge-warning py-4 px-7 rounded-10">Menunggu Pulang</span>
                                        @else
                                            <span class="badge badge-secondary py-4 px-7 rounded-10">Belum Presensi</span>
                                        @endif
                                        
                                    @elseif($presensi->pulang_cepat > 0) 
                                        {{-- Pulang Cepat (berdasarkan nilai durasi menit > 0) --}}
                                        <span class="badge badge-danger py-4 px-7 rounded-10">Pulang Cepat</span>
                                        
                                    @elseif($presensi->lembur > 0) 
                                        {{-- Lembur (berdasarkan nilai durasi menit > 0) --}}
                                        <span class="badge badge-info py-4 px-7 rounded-10">Lembur</span>
                                        
                                    @else
                                        {{-- Pulang Normal (sudah scan_out, tapi pulang_cepat=0 dan lembur=0) --}}
                                        <span class="badge badge-success py-4 px-7 rounded-10">Pulang Normal</span>
                                        
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Presensi -->
                        <div class="col-12 d-flex justify-content-center align-items-center mt-10">
                            @if($presensi)
                                @if($presensi->scan_in && !$presensi->scan_out)
                                    <button type="button" class="btn btn-primary px-10" onclick="presensi_location('Presensi Pulang')" style="border-radius:20px">Presensi Pulang</button>
                                @endif
                                @if(!$presensi->scan_in)
                                    <button type="button" class="btn btn-primary px-10" onclick="presensi_location('Presensi Masuk')" style="border-radius:20px">Presensi Masuk</button>
                                @endif
                            @else
                                <button type="button" class="btn btn-primary px-10" onclick="presensi_location('Presensi Masuk')" style="border-radius:20px">Presensi Masuk</button>
                            @endif
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card widget 16-->
        </div>
        <!--end::Col-->
    </div>
</div>
<!--end::Container-->


<div class="modal fade" id="presensiModal" tabindex="-1" aria-labelledby="presensiModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:450px">
        <div class="modal-content custom-modal-content shadow-lg">
            <div class="modal-header border-0 pb-0 pt-3" style="background-color: #f8f9fa;">
                <div class="text-center w-100">
                    <p class="mb-0 text-secondary" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-user me-1"></i> {{ $profile->nama ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="modal-header border-0 pt-0 pb-2">
                {{-- LOGIC JUDUL MODAL BERDASARKAN STATUS PRESENSI (SAMA) --}}
                @php
                    $isScanIn = isset($presensi) && $presensi && $presensi->scan_in;
                    $isScanOut = isset($presensi) && $presensi && $presensi->scan_out;
                    $modalTitle = ($isScanIn && !$isScanOut) ? 'Verifikasi Presensi Pulang' : 'Verifikasi Presensi Masuk';
                @endphp
                <h5 class="modal-title w-100 text-center fw-bold" id="presensiModalLabel">{{ $modalTitle }}</h5>
            </div>

            <div class="modal-body pt-0 pb-2">
                <div class="alert alert-success d-flex align-items-center justify-content-center flex-column custom-alert row px-10 py-5" role="alert">
                    <i class="fa-solid fa-check-circle me-2 fs-2x mb-4"></i>
                    <div class="fs-5 text-center">
                        Lokasi Valid - Anda berada dalam radius kantor
                    </div>
                </div>

                <form method="POST" action="{{ route('insert_presensi') }}" id="form_presensi">
                    @csrf 
                    
                    {{-- Input Koordinat (BARU) --}}
                    <input type="hidden" name="latitude" id="inputLatitude" value="">
                    <input type="hidden" name="longitude" id="inputLongitude" value="">

                    {{-- Logic Pilih Shift (SAMA) --}}
                    @if (!$isScanIn)
                    <div class="mb-3" id="shift-selection-area">
                        <label for="pilihShift" class="form-label required-label">Pilih Shift:</label>
                        <select name="id_shift" id="select_id_shift" class="form-select mb-3 mb-lg-0" data-control="select2" data-placeholder="Pilih Shift">
                            <option value=""></option>
                            @if(isset($shift) && $shift->isNotEmpty())
                                @foreach($shift AS $row)
                                    <option value="{{ $row->id_shift }}">{{ $row->nama.' ('.date('H:i',strtotime($row->jam_masuk)).' - '.date('H:i',strtotime($row->jam_pulang)).')' }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="id_shift" value="{{ $presensi->id_shift ?? '' }}">
                    @endif

     
                </form>

            <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                <button type="button" class="btn btn-danger btn-lg flex-fill me-2 custom-batal-btn" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-warning btn-lg flex-fill custom-konfirmasi-btn" onclick="handleKonfirmasi()">
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pegawai -->
<div class="modal fade" id="kt_modal_profile"  data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-body mx-5 mx-xl-15 my-7">
                <!--begin::Form-->
                <form id="form_profile" class="form" action="{{ route('update.profile') }}"  method="POST" enctype="multipart/form-data">
                    <!--begin::Scroll-->
                    <div class="d-flex flex-column me-n7 pe-7" id="#">

                        <!--begin::Input group-->
                        <div class="fv-row mb-7 d-flex justify-content-center align-items-center flex-column">
                            <!--begin::Image input-->
                            <div class="image-input image-input-circle background-partisi" data-kt-image-input="true" style="background-image: url('<?= image_check($profile->image,'user','user') ?>')">
                                <!--begin::Image preview wrapper-->
                                <div id="display_image" class="image-input-wrapper w-125px h-125px background-partisi" style="background-image: url('<?= image_check($profile->image,'user','user') ?>')"></div>
                                <!--end::Image preview wrapper-->

                                <!--begin::Edit button-->
                                <label class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Edit">
                                    <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>

                                    <!--begin::Inputs-->
                                    <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                                    <input type="hidden" name="avatar_remove" />
                                    <input type="hidden" name="name_image" />
                                    <!--end::Inputs-->
                                </label>
                                <!--end::Edit button-->

                                <!--begin::Cancel button-->
                                <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow hps_image" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel">
                                    <i class="ki-outline ki-cross fs-3"></i>
                                </span>
                                <!--end::Cancel button-->

                                <!--begin::Remove button-->
                                <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow hps_image" data-kt-image-input-action="remove" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Delete">
                                    <i class="ki-outline ki-cross fs-3"></i>
                                </span>
                                <!--end::Remove button-->
                            </div>
                            <!--end::Image input-->
                            <!--begin::Hint-->
                            <div class="form-text">Tipe: png, jpg, jpeg.</div>
                            <!--end::Hint-->
                        </div>
                        <!--end::Input group-->


                        <!--begin::Input group-->
                        <div class="fv-row mb-7" id="req_phone">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Nama Pengguna</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="phone" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Contoh : MK289107" autocomplete="off" value="{{ $profile->username ?? '' }}" readonly disabled/>
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7" id="req_kata_sandi">
                            <!--begin::Label-->
                            <label class="kata_sandi fw-semibold fs-6 mb-2">Kata Sandi</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="password" name="kata_sandi" class="form-control mb-3 mb-lg-0" onkeyup="cekValuePassword(this)" placeholder="Contoh : *****" autocomplete="off" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7" id="req_kata_sandi_baru">
                            <!--begin::Label-->
                            <label class="kata_sandi fw-semibold fs-6 mb-2">Kata Sandi Baru</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="password" name="kata_sandi_baru" class="form-control mb-3 mb-lg-0" placeholder="Contoh : *****" autocomplete="off" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="fv-row mb-7" id="req_kata_sandi_konfirm">
                            <!--begin::Label-->
                            <label class="kata_sandi fw-semibold fs-6 mb-2">Konfirmasi Sandi Baru</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="password" name="kata_sandi_konfirm" class="form-control mb-3 mb-lg-0" placeholder="Contoh : *****" autocomplete="off" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->


                    </div>
                    <!--end::Scroll-->
                     <!--begin::Actions-->
                    <div class="w-100 d-flex justify-content-center align-items-center pt-15">
                        <button type="button" class="mx-5 btn-modal btn btn-danger" onclick="close_modal_profile()" data-bs-dismiss="modal" aria-label="Tutup">Batal</button>
                        <button type="button" id="submit_profile" data-loader="big" onclick="submit_form(this,'#form_profile')" class="mx-5 btn-modal btn btn-primary">
                            <span class="indicator-label">Simpan</span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
        </div>
    </div>
</div>


@endsection

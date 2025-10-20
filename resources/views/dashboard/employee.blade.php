@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/employee.css') }}">

@section('content')
<!--begin::Container-->
<div class="container-xxl d-flex flex-column justify-content-center align-items-center min-vh-100 py-5" id="kt_content_container" style="margin-bottom: 0 !important;">
    
    <!--begin::Row Heading-->
    <div class="row gx-5 gx-xl-10 w-100 mb-4">
        @include('partials.admin.heading')
    </div>

    <!--begin::Row Presensi-->
    <div class="row gx-5 gx-xl-10 w-100">
        <div class="col-md-12">
            <div class="card card-flush border-0 shadow-sm" style="background-color: #458c84;">
                <div class="card-body d-flex justify-content-center flex-column py-7">

    {{-- ALERT FLASH MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mt-2" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mt-2" role="alert">
            <i class="fa-solid fa-circle-xmark me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <!--begin::Row Presensi-->
    <div class="row gx-5 gx-xl-10 mb-xl-10 mt-4">
        <div class="col-md-12">
            <div class="card card-flush border-0 h-md-100 shadow-sm" style="background-color: #458c84;">
                <div class="card-body d-flex justify-content-center flex-column py-7">
                    <div class="row w-100">

                        {{-- JAM DIGITAL --}}
                        <div class="col-12 mb-7">
                            <div class="clock-box text-center">
                                <div id="time" class="time">00:00:00</div>
                                <div id="date" class="date">{{ $nowdate }}</div>
                            </div>
                        </div>

                        {{-- CARD JAM MASUK --}}
                        <div class="col-lg-6 mb-5 mt-4">
                            <div class="card shadow-sm rounded-3 text-center p-3 py-7">
                                <h3 class="text-muted fs-3 fw-semibold mb-5">
                                    <i class="fa-solid fa-clock me-1"></i> Jam Masuk
                                </h3>
                                <h1 class="text-dark mb-5 fw-bold" style="font-size:2.5rem;">
                                    {{ $presensi && $presensi->scan_in ? \Carbon\Carbon::parse($presensi->scan_in)->format('H:i:s') : '00:00:00' }}
                                </h1>
                                <div class="w-100 text-center">
                                    @if(!$presensi || !$presensi->scan_in)
                                        <span class="badge bg-secondary py-3 px-5">Belum Presensi</span>
                                    @elseif($presensi->terlambat == 'Y')
                                        <span class="badge bg-danger py-3 px-5">Terlambat</span>
                                    @else
                                        <span class="badge bg-success py-3 px-5">Tepat Waktu</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- CARD JAM PULANG --}}
                        <div class="col-lg-6 mb-5 mt-4">
                            <div class="card shadow-sm rounded-3 text-center p-3 py-7">
                                <h3 class="text-muted fs-3 fw-semibold mb-5">
                                    <i class="fa-solid fa-person-running me-1"></i> Jam Pulang
                                </h3>
                                <h1 class="text-dark mb-5 fw-bold" style="font-size:2.5rem;">
                                    {{ $presensi && $presensi->scan_out ? \Carbon\Carbon::parse($presensi->scan_out)->format('H:i:s') : '00:00:00' }}
                                </h1>
                                <div class="w-100 text-center">
                                    @if(!$presensi || !$presensi->scan_out)
                                        @if($presensi && $presensi->scan_in)
                                            <span class="badge bg-warning py-3 px-5">Menunggu Pulang</span>
                                        @else
                                            <span class="badge bg-secondary py-3 px-5">Belum Presensi</span>
                                        @endif
                                    @elseif($presensi->pulang_cepat != 0 && $presensi->pulang_cepat !== null)
                                        <span class="badge bg-danger py-3 px-5">Pulang Cepat</span>
                                    @elseif($presensi->lembur != 0 && $presensi->lembur !== null)
                                        <span class="badge bg-info py-3 px-5">Lembur</span>
                                    @else
                                        <span class="badge bg-success py-3 px-5">Pulang Normal</span>
                                    @endif

                                </div>
                            </div>
                        </div>

                        {{-- TOMBOL PRESENSI (SATU SAJA) --}}
                        <div class="col-12 d-flex justify-content-center mt-10">
                            @if(!$presensi || !$presensi->scan_in)
                                <button type="button" class="btn btn-primary px-10" onclick="presensi_location('Masuk')" style="border-radius:20px">
                                    Presensi Masuk
                                </button>
                            @elseif($presensi->scan_in && !$presensi->scan_out)
                                <button type="button" class="btn btn-success px-10" onclick="presensi_location('Pulang')" style="border-radius:20px">
                                    Presensi Pulang
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary px-10" disabled style="border-radius:20px">
                                    Presensi Selesai
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PRESENSI --}}
<div class="modal fade" id="presensiModal" tabindex="-1" aria-labelledby="presensiModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:450px">
          <div class="modal-content shadow-lg px-4 py-3"> 
            <div class="modal-header border-0 pb-0 bg-light">
                <div class="text-center w-100">
                    <p class="mb-0 text-secondary small">
                        <i class="fa-solid fa-user me-1"></i> {{ $profile->nama ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="modal-header border-0 pt-0 pb-2">
                @php
                    $isScanIn = $presensi && $presensi->scan_in;
                    $isScanOut = $presensi && $presensi->scan_out;
                    $modalTitle = ($isScanIn && !$isScanOut) ? 'Verifikasi Presensi Pulang' : 'Verifikasi Presensi Masuk';
                @endphp
                <h5 class="modal-title text-center fw-bold w-100" id="presensiModalLabel">{{ $modalTitle }}</h5>
            </div>

            <div class="modal-body pt-0 pb-2">
                <div class="alert alert-success text-center py-3" role="alert">
                    <i class="fa-solid fa-check-circle fs-2 mb-2"></i><br>
                    Lokasi Valid - Anda berada dalam radius kantor
                </div>

                <form method="POST" action="{{ route('update.presensi') }}" id="form_presensi">
                    @csrf
                    <input type="hidden" name="id_presensi" value="{{ $presensi->id_presensi ?? 0 }}">
                    <input type="hidden" name="id_user" value="{{ $profile->id_user ?? Auth::user()->id }}">
                    <input type="hidden" name="id_departemen" value="{{ $profile->id_departemen ?? '' }}">
                    <input type="hidden" name="tanggal_presensi" value="{{ now()->toDateString() }}">
                    <input type="hidden" name="latitude" id="inputLatitude">
                    <input type="hidden" name="longitude" id="inputLongitude">
                    <input type="hidden" name="status" id="inputStatus" value="H">

                    @if(!$isScanIn)
                        <div class="mb-3">
                            <label for="select_id_shift" class="form-label required">Pilih Shift:</label>
                            <select name="id_shift" id="select_id_shift" class="form-select" data-control="select2" data-placeholder="Pilih Shift">
                                <option value=""></option>
                                @foreach($shift ?? [] as $row)
                                    <option value="{{ $row->id_shift }}">
                                        {{ $row->nama.' ('.date('H:i', strtotime($row->jam_masuk)).' - '.date('H:i', strtotime($row->jam_pulang)).')' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="id_shift" value="{{ $presensi->id_shift ?? '' }}">
                    @endif
                </form>
            </div>

            <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-danger w-50 me-2" data-bs-dismiss="modal">
                Batal
            </button>
            <button type="button" class="btn btn-warning w-50" onclick="handleKonfirmasi()">
                Konfirmasi
            </button>
        </div>
    </div>
</div>

@push('script')
<script src="{{ asset('assets/js/employee.js') }}"></script>
@endpush
@endsection

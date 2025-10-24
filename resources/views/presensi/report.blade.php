@extends('layouts.admin')

@push('styles')
<style>
    .search-pane {
        z-index: 50;
        display: inline-block;
        max-height: 220px;
        overflow-y: auto;
        padding: 8px;
        border: 1px solid #ddd;
        background: #f9f9f9;
        transition: all 0.4s ease;
    }

    .hover-card-employee {
        transition: background-color 0.3s ease, transform 0.3s ease;
        border-radius: 12px;
        cursor: pointer;
    }

    .hover-card-employee span {
        color: var(--bs-info);
        transition: color 0.3s ease;
    }

    .hover-card-employee:hover {
        background-color: var(--bs-info);
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .hover-card-employee:hover span {
        color: #ffffff !important;
    }

    .btn-cancel-employee {
        position: absolute;
        background: transparent !important;
        border: 0;
        outline: 0;
        top: 10px;
        right: 20px;
        width: 20px;
        height: 20px;
    }
</style>
@endpush

{{-- FLASH MESSAGE --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i> 
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif


@section('content')
<div class="container-xxl" id="kt_content_container">
    <div class="row gx-5 gx-xl-10 mb-xl-10 mb-sm-5">
        @include('partials.admin.heading') 
    </div>

    {{-- FILTER FORM --}}
    <div class="row mb-5 px-5">
        {{-- Filter Departemen, Bulan, Tahun, Status --}}
        <div class="col-sm-12 col-md-7 col-lg-7">
            <div class="row">
                <div class="col-md-3 pe-0">
                    <label class="form-label mb-1 d-block">Departemen</label>
                    <select id="select_departemen" class="form-select table-filter" name="departemen">
                        <option value="all" {{ $filter['departemen'] == 'all' ? 'selected' : '' }}>Semua Departemen</option>
                        @foreach($departemen as $row)
                            <option value="{{ $row->id_departemen }}" {{ $filter['departemen'] == $row->id_departemen ? 'selected' : '' }}>
                                {{ $row->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 pe-0">
                    <label class="form-label mb-1 d-block">Bulan</label>
                    <select id="select_bulan" class="form-select table-filter" name="bulan">
                        @for($i=1;$i<=12;$i++)
                            @php $month = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $month }}" {{ $filter['bulan'] == $month ? 'selected':'' }}>
                                {{ \Carbon\Carbon::create(null, $i, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 pe-0">
                    <label class="form-label mb-1 d-block">Tahun</label>
                    <select id="select_tahun" class="form-select table-filter" name="tahun">
                        @for($i = date('Y'); $i >= 2024; $i--)
                            <option value="{{ $i }}" {{ $filter['tahun'] == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 pe-0">
                    <label class="form-label mb-1 d-block">Status</label>
                    <select id="select_status" class="form-select table-filter" name="status">
                        <option value="all" {{ $filter['status'] == 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="L" {{ $filter['status'] == 'L' ? 'selected' : '' }}>Lembur</option>
                        {{-- <option value="I" {{ $filter['status'] == 'I' ? 'selected' : '' }}>Izin</option> --}}
                        <option value="T" {{ $filter['status'] == 'T' ? 'selected' : '' }}>Terlambat</option>
                         <option value="P" {{ $filter['status'] == 'P' ? 'selected' : '' }}>Pulang Cepat</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Search --}}
        <div class="col-sm-12 col-md-3 mb-4">
            <label class="form-label mb-1 d-block">Pencarian</label>
            <div class="input-group">
                <input type="text" id="search_table_presensi" class="form-control" placeholder="Cari Karyawan">
                <button type="button" class="btn btn-info" id="btn_search">
                    <i class="fa-solid fa-magnifying-glass text-white"></i>
                </button>
            </div>
        </div>

        {{-- Export --}}
        <div class="col-sm-12 col-md-2 mb-4">
            <label class="form-label mb-1 d-block">Export</label>
            <form id="form_export_presensi" action="{{ route('export.presensi') }}" method="POST">
                @csrf
                <input type="hidden" name="filter_id_departemen" value="{{ $filter['departemen'] }}">
                <input type="hidden" name="filter_bulan" value="{{ $filter['bulan'] }}">
                <input type="hidden" name="filter_tahun" value="{{ $filter['tahun'] }}">
                <input type="hidden" name="filter_status" value="{{ $filter['status'] }}">
                <input type="hidden" name="filter_search" id="export_filter_search" value="">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-file-excel"></i> Cetak Excel
                </button>
            </form>
        </div>
    </div>

    {{-- 🔹 FORM HAPUS MASSAL + TABEL --}}
    <form id="bulk-delete-form" action="{{ route('presensi.multiple_delete') }}" method="POST">
        @csrf
        <div class="d-flex justify-content-start mb-3">
            <button type="button" id="delete-selected-btn" class="btn btn-danger" style="display:none;">
                <i class="fa-solid fa-trash"></i> Hapus Terpilih
            </button>
        </div>

        <div class="row gx-5 gx-xl-10 mb-xl-10 mb-sm-5">
            <div class="col-12">
                <div class="card card-flush">
                    <div class="card-body p-0 table-responsive">
                        <table id="table_presensi" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">
                                        <input type="checkbox" id="checkAll">
                                    </th>
                                    <th class="text-center">Tanggal</th>
                                    <th class="text-center">Departemen</th>
                                    <th class="text-center">NIK</th>
                                    <th class="text-center">Nama Karyawan</th>
                                    <th class="text-center">Shift</th>
                                    <th class="text-center">Masuk</th>
                                    <th class="text-center">Pulang</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Keterangan</th>
                                    <th class="text-center">Terlambat (m)</th>
                                    <th class="text-center">Lembur (m)</th>
                                    <th class="text-center">Pulang Cepat (m)</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($presensi as $row)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="presensi-checkbox" name="id_presensi[]" value="{{ $row->id_presensi }}">
                                    </td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($row->tanggal_presensi)->format('Y-m-d') }}</td>
                                    <td>{{ $row->user->departemen->nama ?? '-' }}</td>
                                    <td>{{ $row->user->nik }}</td>
                                    <td>{{ $row->user->nama }}</td>
                                    <td class="text-center">{{ $row->shift->nama ?? '' }}</td>
                                    <td class="text-center">{{ $row->scan_in ? \Carbon\Carbon::parse($row->scan_in)->format('H:i') : '-' }}</td>
                                    <td class="text-center">{{ $row->scan_out ? \Carbon\Carbon::parse($row->scan_out)->format('H:i') : '-' }}</td>
                                    <td class="text-center">
                                        @if($row->hadir === 'Y')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($row->keterangan)
                                            <span class="badge bg-warning">Izin</span>
                                        @else
                                            <span class="badge bg-danger">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->keterangan ?? '-' }}</td>
                                    <td class="text-center">{{ $row->waktu_terlambat ?? 0 }}</td>
                                    <td class="text-center">{{ $row->lembur ?? 0 }}</td>
                                    <td class="text-center">{{ $row->pulang_cepat ?? 0 }}</td>
                                    <td class="text-center">
                                        <button 
                                            type="button" 
                                            class="btn btn-sm btn-warning btn-edit-presensi"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditPresensi{{ $row->id_presensi }}"
                                            data-id="{{ $row->id_presensi }}"
                                            data-scanin="{{ $row->scan_in ? \Carbon\Carbon::parse($row->scan_in)->format('H:i') : '' }}"
                                            data-scanout="{{ $row->scan_out ? \Carbon\Carbon::parse($row->scan_out)->format('H:i') : '' }}"
                                            data-terlambat="{{ $row->waktu_terlambat ?? 0 }}"
                                            data-pulangcepat="{{ $row->pulang_cepat ?? 0 }}"
                                            data-lembur="{{ $row->lembur ?? 0 }}"
                                            data-keterangan="{{ $row->keterangan ?? '' }}"
                                            data-jammasukshift="{{ $row->shift ? date('H:i', strtotime($row->shift->jam_masuk)) : '' }}"
                                            data-jampulangshift="{{ $row->shift ? date('H:i', strtotime($row->shift->jam_pulang)) : '' }}">
                                            <i class="fa-solid fa-edit text-white"></i>
                                        </button>

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

{{-- 🔹 MODAL EDIT PRESENSI --}}
@foreach($presensi as $row)
<div class="modal fade" id="modalEditPresensi{{ $row->id_presensi }}" tabindex="-1" aria-labelledby="modalEditPresensiLabel{{ $row->id_presensi }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content shadow-lg border-0 rounded-4">
            
            {{-- Header --}}
            <div class="modal-header bg-light border-0 pb-0">
                <h5 class="modal-title fw-bold text-center w-100" id="modalEditPresensiLabel{{ $row->id_presensi }}">
                    Edit Data Presensi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <form method="POST" action="{{ route('update.presensi') }}">
                @csrf
                <input type="hidden" name="id_presensi" value="{{ $row->id_presensi }}">
                <input type="hidden" name="id_user" value="{{ $row->id_user ?? $row->user->id_user ?? '' }}">



                <div class="modal-body pt-2">
                    {{-- Tanggal Presensi --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Presensi</label>
                        <input type="text" name="tanggal_presensi" class="form-control" value="{{ $row->tanggal_presensi }}" readonly>
                    </div>

                    {{-- Pilih Shift --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Shift</label>
                        <select name="id_shift" class="form-select" required>
                            <option value="">-- Pilih Shift --</option>
                            @foreach($shift as $s)
                                <option 
                                    value="{{ $s->id_shift }}"
                                    data-jam-masuk="{{ date('H:i', strtotime($s->jam_masuk)) }}"
                                    data-jam-pulang="{{ date('H:i', strtotime($s->jam_pulang)) }}"
                                    data-lembur="{{ $s->lembur ?? 0 }}" {{-- 👈 tambahkan ini --}}
                                    {{ $row->shift && $row->shift->id_shift == $s->id_shift ? 'selected' : '' }}>
                                    {{ $s->nama }} ({{ date('H:i', strtotime($s->jam_masuk)) }} - {{ date('H:i', strtotime($s->jam_pulang)) }})
                                    - Min. Lembur: {{ $s->lembur ?? 0 }} mnt
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jam Masuk & Pulang --}}
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Jam Masuk</label>
                            <input type="time" name="scan_in" class="form-control"
                                value="{{ $row->scan_in ? \Carbon\Carbon::parse($row->scan_in)->format('H:i') : '' }}">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Jam Pulang</label>
                            <input type="time" name="scan_out" class="form-control"
                                value="{{ $row->scan_out ? \Carbon\Carbon::parse($row->scan_out)->format('H:i') : '' }}">
                        </div>
                    </div>

                    {{-- Hitungan Terlambat, Pulang Cepat, Lembur --}}
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label fw-semibold">Terlambat (m)</label>
                            <input type="number" name="waktu_terlambat" class="form-control" value="{{ $row->waktu_terlambat ?? 0 }}">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label fw-semibold">Pulang Cepat (m)</label>
                            <input type="number" name="pulang_cepat" class="form-control" value="{{ $row->pulang_cepat ?? 0 }}">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label fw-semibold">Lembur (m)</label>
                            <input type="number" name="lembur" class="form-control" value="{{ $row->lembur ?? 0 }}">
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" value="{{ $row->keterangan }}">
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white fw-semibold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

</div>


@push('script')
<script>
    const BASE_URL = "{{ url('/') }}";
    const csrf_token = "{{ csrf_token() }}";

    // Sinkronkan pencarian dengan export
    $(document).ready(function() {
        $('#btn_search').on('click', function() {
            $('#export_filter_search').val($('#search_table_presensi').val());
            presensiTable.search($('#search_table_presensi').val()).draw();
        });
        $('#form_export_presensi').on('submit', function() {
            $('#export_filter_search').val($('#search_table_presensi').val());
        });
    });
</script>
<script src="{{ asset('assets/js/report.js') }}"></script>
@endpush

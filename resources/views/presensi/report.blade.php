@extends('layouts.admin')

@push('styles')
<style>
    /* ... (CSS Anda tetap sama) ... */
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

@section('content')
<div class="container-xxl" id="kt_content_container">
    <div class="row gx-5 gx-xl-10 mb-xl-10 mb-sm-5">
        {{-- Asumsi partials.admin.heading sudah tersedia --}}
        @include('partials.admin.heading') 
    </div>

    {{-- FILTER FORM (Action di-handle oleh JS dengan reload halaman) --}}
    <div class="row mb-5 px-5">
        {{-- Menggunakan row mb-5 px-5 yang sudah Anda tentukan sebelumnya --}}
        
        {{-- Filter Departemen, Bulan, Tahun, Status --}}
        <div class="col-sm-12 col-md-7 col-lg-7">
            <div class="row">
                <div class="col-md-3 pe-0">
                    <label class="form-label mb-1 d-block">Departemen</label>
                    <select id="select_departemen" class="form-select table-filter" name="departemen">
                        {{-- 🚨 KOREKSI: Menggunakan $filter['departemen'] untuk mempertahankan nilai --}}
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
                        {{-- 🚨 KOREKSI: Menggunakan $filter['bulan'] untuk mempertahankan nilai --}}
                        @for($i=1;$i<=12;$i++)
                            @php $month = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                            <option value="{{ $month }}" {{ $filter['bulan'] == $month ? 'selected':'' }}>{{ \Carbon\Carbon::create(null, $i, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 pe-0">
                    <label class="form-label mb-1 d-block">Tahun</label>
                    <select id="select_tahun" class="form-select table-filter" name="tahun">
                        {{-- 🚨 KOREKSI: Menggunakan $filter['tahun'] untuk mempertahankan nilai --}}
                        @for($i = date('Y'); $i >= 2024; $i--)
                            <option value="{{ $i }}" {{ $filter['tahun'] == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 pe-0">
                    <label class="form-label mb-1 d-block">Status</label>
                    <select id="select_status" class="form-select table-filter" name="status">
                        {{-- 🚨 KOREKSI: Menggunakan $filter['status'] untuk mempertahankan nilai --}}
                        <option value="all" {{ $filter['status'] == 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="L" {{ $filter['status'] == 'L' ? 'selected' : '' }}>Lembur</option>
                        <option value="I" {{ $filter['status'] == 'I' ? 'selected' : '' }}>Izin</option>
                        <option value="T" {{ $filter['status'] == 'T' ? 'selected' : '' }}>Terlambat</option>
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

        {{-- Cetak Excel --}}
        <div class="col-sm-12 col-md-2 mb-4">
            <label class="form-label mb-1 d-block">Export</label>
            {{-- 🚨 KOREKSI: Mengirimkan filter yang saat ini aktif ke form export --}}
            <form id="form_export_presensi" action="{{ route('export.presensi') }}" method="POST">
                @csrf
                {{-- Hidden input untuk mengirim filter yang aktif ke Controller export --}}
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

    {{-- TABEL --}}
    <div class="row gx-5 gx-xl-10 mb-xl-10 mb-sm-5">
        <div class="col-12">
            <div class="card card-flush">
                <div class="card-body p-0 table-responsive">
                    <table id="table_presensi" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
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
                            {{-- 🚨 KOREKSI UTAMA: Mencetak data loop dari Controller --}}
                            @php $no = 1; @endphp
                            @foreach($presensi as $row)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                {{-- Kolom 1: Tanggal (Format YYYY-MM-DD wajib untuk sorting DataTables) --}}
                                <td data-order="{{ $row->tanggal_presensi }}" class="text-center">
                                    {{ \Carbon\Carbon::parse($row->tanggal_presensi)->format('Y-m-d') }}
                                </td>
                                {{-- Kolom 2: Departemen --}}
                                <td>{{ $row->user->departemen->nama ?? 'N/A' }}</td>
                                {{-- Kolom 3 & 4: NIK & Nama --}}
                                <td>{{ $row->user->nik }}</td>
                                <td>{{ $row->user->nama }}</td>
                                {{-- Kolom 5 & 6: Shift & Scan In --}}
                                <td class="text-center">{{ $row->shift ?? '-' }}</td>
                                <td class="text-center">{{ $row->scan_in ? \Carbon\Carbon::parse($row->scan_in)->format('H:i') : '-' }}</td>
                                {{-- Kolom 7: Scan Out --}}
                                <td class="text-center">{{ $row->scan_out ? \Carbon\Carbon::parse($row->scan_out)->format('H:i') : '-' }}</td>
                                {{-- Kolom 8: Status (Menggunakan class/text yang bisa dicari oleh JS DataTables) --}}
                                <td class="text-center">
                                    @if($row->hadir === 'Y')
                                        <span class="badge badge-light-success">Hadir</span>
                                    @elseif($row->keterangan)
                                        <span class="badge badge-light-warning">Izin</span>
                                    @else
                                        <span class="badge badge-light-danger">-</span>
                                    @endif
                                </td>
                                {{-- Kolom 9, 10, 11, 12: Keterangan, Terlambat, Lembur, Pulang Cepat --}}
                                <td>{{ $row->keterangan ?? '-' }}</td>
                                <td class="text-center">{{ $row->waktu_terlambat ?? 0 }}</td>
                                <td class="text-center">{{ $row->lembur ?? 0 }}</td>
                                <td class="text-center">{{ $row->pulang_cepat ?? 0 }}</td>
                                {{-- Kolom 13: Aksi --}}
                                <td class="text-center">
                                    <a href="javascript:void(0)" 
                                        onclick="ubah_data({{ $row->id_presensi ?? 'null' }}, {{ $row->user->id_user }}, '{{ $row->tanggal_presensi }}')"
                                        class="btn btn-sm btn-icon btn-warning edit-data-presensi" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#kt_modal_presensi">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Asumsi modal_presensi di-include di sini --}}
{{-- @include('presensi.modal_presensi') --}} 

@endsection

@push('script')
{{-- JS Eksternal --}}
<script>
    const BASE_URL = "{{ url('/') }}";
    const csrf_token = "{{ csrf_token() }}";
    
    // 🚨 Tambahkan logic untuk Export Excel: Mengambil nilai search saat ini.
    $(document).ready(function() {
        $('#btn_search').on('click', function() {
            // Update hidden field export dengan nilai search saat ini
            $('#export_filter_search').val($('#search_table_presensi').val());
            // Lakukan DataTables search di JS (seperti di report.js)
            presensiTable.search($('#search_table_presensi').val()).draw();
        });
        
        // Update hidden field saat tombol export diklik
        $('#form_export_presensi').on('submit', function() {
            $('#export_filter_search').val($('#search_table_presensi').val());
        });
    });
</script>
<script src="{{ asset('assets/js/report.js') }}"></script>
@endpush
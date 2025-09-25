@extends('layouts.admin')

@push('styles')
@endpush

@section('content')
<div class="container-fluid p-0">
    @include('partials.admin.heading')

    <div class="row mb-4">
        <div class="col-lg-10 mx-auto">

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

            {{-- Tombol tambah + Search --}}
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4 ">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKaryawan">
                    <i class="fa-solid fa-plus me-1"></i> Karyawan
                </button>

                <!-- Form Pencarian -->
                <form action="{{ route('master.karyawan') }}" method="GET" class="d-flex" style="max-width:300px;">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari Karyawan" value="{{ request('search') }}">
                        <button class="btn btn-info" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabel Karyawan --}}
            <div class="card shadow-sm">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0" id="table_karyawan">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="min-width:150px;" class="text-center">NIK</th>
                                <th style="min-width:150px;" class="text-center">Departemen</th>
                                <th style="min-width:200px;" class="text-center">Nama Karyawan</th>
                                <th style="min-width:200px;" class="text-center">Nama Pengguna</th>
                                <th style="min-width:100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($karyawan as $k)
                                <tr class="text-center">
                                    <td>{{ $k->nik }}</td>
                                    <td>
                                        {{-- Nama departemen tampil sebagai badge dengan warna dari DB --}}
                                        <span class="badge text-white px-3 py-2"
                                              style="background-color: {{ $k->departemen->warna ?? '#6c757d' }};">
                                            {{ $k->departemen->nama ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $k->nama }}</td>
                                    <td>{{ $k->username }}</td>
                                    <td>
                                        {{-- Tombol Edit --}}
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $k->id_user }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('delete.karyawan') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="id_user" value="{{ $k->id_user }}">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
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
@endsection

{{-- Modal Tambah Karyawan --}}
<div class="modal fade" id="modalTambahKaryawan" tabindex="-1" aria-labelledby="title_modal_tambah" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="title_modal_tambah">Tambah Data Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            
            <div class="modal-body">
                <form action="{{ route('insert.karyawan') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label required">NIK Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="nik" class="form-control" placeholder="Contoh: P0523001" autocomplete="off" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Departemen <span class="text-danger">*</span></label>
                        <select name="id_departemen" class="form-select" required>
                            <option value="">Pilih Departemen</option>
                            @if($departemen->isNotEmpty())
                                @foreach($departemen as $row)
                                    <option value="{{ $row->id_departemen }}">{{ $row->nama }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Abbian Abas" autocomplete="off" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Pengguna <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Contoh: P0523001" autocomplete="off" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="kata_sandi" class="form-control" placeholder="Contoh: P0523001" autocomplete="off" required>
                    </div>

                    <input type="hidden" name="peran" value="2">

                    <div class="d-flex justify-content-center mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

{{-- Modal Edit Karyawan (looping per data) --}}
@foreach($karyawan as $k)
<div class="modal fade" id="modalEdit{{ $k->id_user }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Data Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            
            <div class="modal-body">
                <form action="{{ route('update.karyawan') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_user" value="{{ $k->id_user }}">

                    <div class="mb-3">
                        <label class="form-label required">NIK Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="nik" value="{{ $k->nik }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Departemen <span class="text-danger">*</span></label>
                        <select name="id_departemen" class="form-select" required>
                            <option value="">Pilih Departemen</option>
                            @if($departemen->isNotEmpty())
                                @foreach($departemen as $row)
                                    <option value="{{ $row->id_departemen }}" {{ $k->id_departemen == $row->id_departemen ? 'selected' : '' }}>
                                        {{ $row->nama }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" value="{{ $k->nama }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Pengguna <span class="text-danger">*</span></label>
                        <input type="text" name="username" value="{{ $k->username }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kata Sandi Baru</label>
                        <input type="password" name="kata_sandi" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah" autocomplete="off">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah kata sandi</small>
                    </div>

                    <input type="hidden" name="peran" value="2">

                    <div class="d-flex justify-content-center mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endforeach
@extends('layouts.admin')

@push('script')
<script src="{{ asset('assets/js/karyawan.js') }}"></script>
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
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <div class="d-flex">
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalTambahKaryawan">
                        <i class="fa-solid fa-plus me-1"></i> Karyawan
                    </button>
                    {{-- TOMBOL HAPUS TERPILIH (DIKONTROL OLEH JS) --}}
                    <button type="button" class="btn btn-danger" id="delete-selected-btn" style="display:none;">
                        <i class="fa-solid fa-trash me-1"></i> Hapus Terpilih
                    </button>
                </div>

                {{-- Form Pencarian --}}
                <form action="{{ route('master.karyawan') }}" method="GET" class="d-flex" style="max-width:300px;">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari Karyawan" value="{{ request('search') }}">
                        <button class="btn btn-info" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Form Hapus Banyak --}}
            <form id="bulk-delete-form" action="{{ route('master.karyawan.delete_multiple') }}" method="POST">
                @csrf
                @method('DELETE') 
                {{-- Table Karyawan --}}
                <div class="card shadow-sm">
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0" id="table_karyawan">
                            <thead class="table-light text-center">
                                <tr>
                                    <th style="width: 50px;"><input type="checkbox" id="checkAll"></th>
                                    <th style="min-width:150px;">NIK</th>
                                    <th style="min-width:150px;">Departemen</th>
                                    <th style="min-width:200px;">Nama Karyawan</th>
                                    <th style="min-width:200px;">Nama Pengguna</th>
                                    <th style="min-width:100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($karyawan as $k)
                                    <tr class="text-center">
                                        <td>
                                            <input type="checkbox" name="id_user[]" class="user-checkbox" value="{{ $k->id_user }}">
                                        </td>
                                        <td>{{ $k->nik }}</td>
                                        <td>{{ $k->departemen->nama ?? '-' }}</td>
                                        <td>{{ $k->nama }}</td>
                                        <td>{{ $k->username }}</td>
                                        <td>
                                            {{-- Tombol Edit --}}
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditKaryawan{{ $k->id_user }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

{{-- CATATAN: Blok @push('scripts') yang berisi kode JS inline sudah DIHAPUS di sini. --}}


{{-- Modal Tambah Karyawan --}}
<div class="modal fade" id="modalTambahKaryawan" tabindex="-1" aria-labelledby="title_modal_tambah" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="title_modal_tambah">Tambah Data Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            
            <div class="modal-body">
                <form action="{{ route('master.karyawan.insert') }}" method="POST">
                    @csrf
                    <input type="hidden" name="peran" value="2">

                    <div class="mb-3">
                        <label class="form-label required">NIK Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" placeholder="Contoh: P0523001" autocomplete="off">
                        @error('nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Departemen <span class="text-danger">*</span></label>
                        <select name="id_departemen" class="form-select @error('id_departemen') is-invalid @enderror">
                            <option value="">Pilih Departemen</option>
                            @foreach($departemen as $d)
                                <option value="{{ $d->id_departemen }}">{{ $d->nama }}</option>
                            @endforeach
                        </select>
                        @error('id_departemen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh : Abbian Abas" autocomplete="off">
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Pengguna <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Contoh : P0523001" autocomplete="off">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="kata_sandi" class="form-control @error('kata_sandi') is-invalid @enderror" placeholder="Contoh : P0523001" autocomplete="off">
                        @error('kata_sandi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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
<div class="modal fade" id="modalEditKaryawan{{ $k->id_user }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Data Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            
            <div class="modal-body">
                <form action="{{ route('master.karyawan.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_user" value="{{ $k->id_user }}">
                    <input type="hidden" name="peran" value="2">

                    <div class="mb-3">
                        <label class="form-label required">NIK Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="nik" value="{{ old('nik', $k->nik) }}" class="form-control @error('nik') is-invalid @enderror" autocomplete="off">
                        @error('nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Departemen <span class="text-danger">*</span></label>
                        <select name="id_departemen" class="form-select @error('id_departemen') is-invalid @enderror">
                            <option value="">Pilih Departemen</option>
                            @foreach($departemen as $d)
                                <option value="{{ $d->id_departemen }}" {{ (old('id_departemen', $k->id_departemen) == $d->id_departemen) ? 'selected' : '' }}>{{ $d->nama }}</option>
                            @endforeach
                        </select>
                        @error('id_departemen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama', $k->nama) }}" class="form-control @error('nama') is-invalid @enderror" autocomplete="off">
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Pengguna <span class="text-danger">*</span></label>
                        <input type="text" name="username" value="{{ old('username', $k->username) }}" class="form-control @error('username') is-invalid @enderror" autocomplete="off">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kata Sandi</label>
                        <input type="password" name="kata_sandi" class="form-control @error('kata_sandi') is-invalid @enderror" placeholder="Kosongkan jika tidak diubah" autocomplete="off">
                        @error('kata_sandi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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
@extends('layouts.admin')

@push('styles')
@endpush

@push('script')
<script src="{{ asset('assets/public/js/karyawan.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid p-0">
    @include('partials.admin.heading')

    <div class="row mb-4">
        <div class="col-lg-10 mx-auto">
            {{-- Tombol tambah + Hapus + Search --}}
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <div id="real_action_pane">
                    <button type="button" class="btn btn-primary" onclick="tambah_data()" data-bs-toggle="modal" data-bs-target="#modalKaryawan">
                        <i class="fa-solid fa-plus me-1"></i> Karyawan
                    </button>
                </div>

                <div id="second_action_pane" class="d-none">
                    <button type="button" class="btn btn-danger" onclick="delete_batch(this,'table_karyawan','users','id_user','Karyawan')">
                        <i class="fa-solid fa-trash me-1"></i> Hapus Terpilih
                    </button>
                </div>

                <div class="input-group" style="max-width:300px;">
                    <input type="text" class="form-control" id="search_table_karyawan" placeholder="Cari Karyawan">
                    <button class="btn btn-info" type="button" onclick="click_search('search_table_karyawan','#table_karyawan')">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>

            {{-- Table Karyawan --}}
            <div class="card shadow-sm">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0" 
                           id="table_karyawan" 
                           data-url="{{ route('table.karyawan') }}">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="min-width:50px;" data-orderable="false" data-searchable="false" data-checkbox="true">
                                    <input class="form-check-input checkbox-table" type="checkbox" onchange="checkbox_action(this)" value="all">
                                </th>
                                <th style="min-width:150px;">NIK</th>
                                <th style="min-width:150px;">Departemen</th>
                                <th style="min-width:200px;">Nama Karyawan</th>
                                <th style="min-width:200px;">Nama Pengguna</th>
                                <th style="min-width:100px;" data-orderable="false" data-searchable="false">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Modal Tambah Karyawan --}}
<div class="modal fade" id="modalKaryawan" tabindex="-1" aria-labelledby="title_modal" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="title_modal" data-title="Edit Data Karyawan|Tambah Data Karyawan"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <form id="form_karyawan" action="{{ route('insert.karyawan') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id_user">
                    <input type="hidden" name="role" value="2">

                    {{-- NIK --}}
                    <div class="mb-3">
                        <label class="form-label required">NIK Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="nik" class="form-control" placeholder="Contoh: P0523001" autocomplete="off">
                    </div>

                    {{-- Departemen --}}
                    <div class="mb-3">
                        <label class="form-label required">Nama Departemen <span class="text-danger">*</span></label>
                        <select name="id_departemen" id="select_id_departemen" class="form-select">
                            <option value="">Pilih Departemen</option>
                            @if($departemen->isNotEmpty())
                                @foreach($departemen as $row)
                                    <option value="{{ $row->id_departemen }}">{{ $row->nama }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Nama --}}
                    <div class="mb-3">
                        <label class="form-label required">Nama Karyawan <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh : Abbian Abas" autocomplete="off">
                    </div>

                    {{-- Username --}}
                    <div class="mb-3">
                        <label class="form-label required">Nama Pengguna <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Contoh : P0523001" autocomplete="off">
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label class="form-label required">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" name="kata_sandi" class="form-control" placeholder="Contoh : P0523001" autocomplete="off">
                    </div>

                    {{-- Action --}}
                    <div class="d-flex justify-content-center mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="button" id="submit_karyawan" onclick="submit_form(this,'#form_karyawan')" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

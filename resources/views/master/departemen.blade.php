@extends('layouts.admin')

@push('styles')

@endpush

@push('script')
<script src="{{ asset('assets/public/js/departemen.js') }}"></script> 
@endpush

@section('content')
<!--begin::Container-->
<div class="container-fluid p-0">
    <!--begin::Row-->
    @include('partials.admin.heading')

     <div class="row mb-4">
        <div class="col-lg-10 mx-auto">
            {{-- Tombol tambah + Search --}}
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4 ">
                <button type="button" class="btn btn-primary" onclick="tambah_data()" data-bs-toggle="modal" data-bs-target="#modalDepartemen">
                    <i class="fa-solid fa-plus me-1"></i> Departemen
                </button>

            <div class="input-group" style="max-width:300px;">
                    <input type="text" class="form-control me-2" id="search_table_departemen" placeholder="Cari Departemen">
                    <button class="btn btn-info" type="button" onclick="click_search('search_table_departemen','#table_departemen')">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>

            {{-- Table Departemen --}}
            <div class="card shadow-sm">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0" 
                           id="table_departemen" 
                           data-url="{{ route('table.departemen') }}">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="min-width:100px;">Kode Departemen</th>
                                <th style="min-width:200px;">Nama Departemen</th>
                                <th style="min-width:100px;">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Modal Tambah Departemen --}}
<div class="modal fade" id="modalDepartemen" tabindex="-1" aria-labelledby="title_modal" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="title_modal" data-title="Edit Data Departemen|Tambah Data Departemen"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            
            <div class="modal-body">
                <form id="form_departemen" action="{{ route('insert.departemen') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id_departemen">

                    {{-- Kode Departemen --}}
                    <div class="mb-3">
                        <label class="form-label required">Kode Departemen <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control" placeholder="Contoh : 01, 02" autocomplete="off">
                    </div>

                    {{-- Nama Departemen --}}
                    <div class="mb-3">
                        <label class="form-label required">Nama Departemen <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh : Office, Finance, Electric" autocomplete="off">
                    </div>

                    {{-- Warna --}}
                    <div class="mb-3">
                        <label class="form-label">Warna Identitas <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-2">
                                <input type="color" name="warna" value="#00695C" class="form-control form-control-color" title="Pilih warna" style="height:40px;">
                            </div>
                            <div class="col-10 d-flex align-items-center">
                                <small class="text-muted">Pilih warna identitas untuk masing-masing departemen dengan menekan kotak di sebelah kiri.</small>
                            </div>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Action --}}
                    <div class="d-flex justify-content-center mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="button" id="submit_departemen" onclick="submit_form(this,'#form_departemen')" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
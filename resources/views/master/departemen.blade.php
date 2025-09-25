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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDepartemen">
                    <i class="fa-solid fa-plus me-1"></i> Departemen
                </button>

                <div class="input-group" style="max-width:300px;">
                    <input type="text" class="form-control me-2" id="search_table_departemen" placeholder="Cari Departemen">
                    <button class="btn btn-info" type="button" onclick="click_search('search_table_departemen','#table_departemen')">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>

            {{-- Tabel Departemen --}}
            <div class="card shadow-sm">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0" id="table_departemen">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="min-width:100px;">Kode Departemen</th>
                                <th style="min-width:200px;">Nama Departemen</th>
                                <th style="min-width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departemen as $d)
                                <tr class="text-center">
                                    <td>{{ $d->kode }}</td>
                                    <td>{{ $d->nama }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalDepartemen" 
                                            onclick="editDepartemen({{ $d->id_departemen }}, '{{ $d->kode }}', '{{ $d->nama }}', '{{ $d->warna }}')">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form action="{{ route('master.departemen.delete') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="id_departemen" value="{{ $d->id_departemen }}">
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

{{-- Modal Tambah/Edit Departemen --}}
<div class="modal fade" id="modalDepartemen" tabindex="-1" aria-labelledby="title_modal" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="title_modal">Tambah Data Departemen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            
            <div class="modal-body">
                <form id="form_departemen" action="{{ route('master.departemen.insert') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_departemen" id="id_departemen">

                    <div class="mb-3">
                        <label class="form-label required">Kode Departemen <span class="text-danger">*</span></label>
                        <input type="text" name="kode" id="kode" class="form-control" placeholder="Contoh : 01, 02" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Nama Departemen <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="nama" class="form-control" placeholder="Contoh : Office, Finance, Electric" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Warna Identitas <span class="text-danger">*</span></label>
                        <input type="color" name="warna" id="warna" value="#00695C" class="form-control form-control-color" title="Pilih warna" style="height:40px;">
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

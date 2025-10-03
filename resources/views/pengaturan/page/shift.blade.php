<div class="card p-5 mt-5"> 
    <form id="form_shift" class="form" action="{{ route('setup.shift') }}" method="POST" enctype="multipart/form-data">
        @csrf 
        
        {{-- Kondisi kosong --}}
        <div id="no_data_vector" class="w-100 d-flex {{ ($shift->isNotEmpty()) ? 'd-none' : '' }} justify-content-center align-items-center flex-column py-5">
            <div class="background-partisi-contain" style="background-image:url('{{ image_check('empty.svg','default') }}');width:300px;height:250px;"></div>
            <h3 class="text-center text-info fs-3">Tidak Ada Data</h3>
            <p class="text-muted">Belum ada data shift! Silahkan tambahkan data shift terlebih dahulu</p>
        </div>

        {{-- Daftar Shift lama --}}
        <div id="data_shift" class="{{ ($shift->isEmpty()) ? 'd-none' : '' }}">
            @foreach($shift as $row)
            <div class="p-4 mb-4 bg-light rounded-4 shadow-sm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Kode Shift</label>
                        <input type="text" name="edit_kode[{{ $row->id_shift }}]" class="form-control"
                            value="{{ $row->kode }}" placeholder="Contoh : O" autocomplete="off" />
                    </div>
                    <div class="col-md-6 mb-3 d-flex">
                        <div class="flex-grow-1">
                            <label class="form-label required">Nama Shift</label>
                            <input type="text" name="edit_nama[{{ $row->id_shift }}]" class="form-control"
                                value="{{ $row->nama }}" placeholder="Contoh : Office" autocomplete="off" />
                        </div>
                        <button type="button" onclick="hapus_data(this,event,{{ $row->id_shift }},'shift','id_shift')" 
                                class="btn btn-danger ms-2 align-self-end">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Jam Masuk</label>
                        <input type="time" name="edit_jam_masuk[{{ $row->id_shift }}]" class="form-control"
                            value="{{ date('H:i',strtotime($row->jam_masuk)) }}" />
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Jam Pulang</label>
                        <input type="time" name="edit_jam_pulang[{{ $row->id_shift }}]" class="form-control"
                            value="{{ date('H:i',strtotime($row->jam_pulang)) }}" />
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Batas Lembur (Menit)</label>
                        <input type="number" name="edit_lembur[{{ $row->id_shift }}]" class="form-control"
                            value="{{ $row->lembur }}" placeholder="-" />
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Shift baru dari JS --}}
        <div id="data_add_shift"></div>

        {{-- Tombol aksi --}}
        <div class="w-100 d-flex justify-content-center align-items-center pt-4">
            <button type="button" id="plus_shift" onclick="addShift()" class="btn btn-success me-3">
                + Tambah
            </button>
            <button type="button" id="submit_shift" data-loader="big" 
                onclick="submit_form(this,'#form_shift')" 
                class="btn btn-warning {{ ($shift->isEmpty()) ? 'd-none' : '' }}">
                Simpan
            </button>
        </div>
    </form>

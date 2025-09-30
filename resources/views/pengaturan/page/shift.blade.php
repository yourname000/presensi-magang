{{-- HAPUS TAG <script> DARI ATAS INI --}}

{{-- Tambahkan pembungkus besar untuk meniru area konten utama --}}
<div class="card p-5 mt-5"> 
    
    <form id="form_shift" class="form" action="{{ route('setup.shift') }}" method="POST" enctype="multipart/form-data">
        @csrf 
        
        <div id="no_data_vector" class="w-100 d-flex {{ ($shift->isNotEmpty()) ? 'd-none' : '' }} justify-content-center align-items-center flex-column py-5">
            {{-- Menggunakan kelas Bootstrap untuk padding --}}
            <div class="background-partisi-contain" style="background-image : url('{{ image_check('empty.svg','default') }}');width : 300px;height : 250px;"></div>
            <h3 class="text-center text-info fs-3">Tidak Ada Data</h3>
            <p class="text-muted">Belum ada data shift! Silahkan tambahkan data shift terlebih dahulu</p>
        </div>

        {{-- Daftar Shift yang Sudah Ada (Pastikan style di dalam loop tetap menggunakan col-md-X) --}}
        <div id="data_shift" class="row px-lg-5 px-sm-0 {{ ($shift->isEmpty()) ? 'd-none' : '' }}">
            @if($shift->isNotEmpty())
                @foreach($shift AS $row)
                <div class="col-12 card bg-white border p-3 mb-3">
                    <div class="row">
                        {{-- Baris 1 --}}
                        <div class="col-md-4 col-sm-12">
                            <div class="mb-3" id="req_kode_{{ $row->id_shift }}">
                                <label class="form-label required">Kode Shift</label>
                                <input type="text" name="edit_kode[{{ $row->id_shift }}]" class="form-control" value="{{ $row->kode }}" placeholder="Contoh : O" autocomplete="off" />
                            </div>
                        </div>
                        <div class="col-md-7 col-sm-10"> 
                            <div class="mb-3" id="req_nama_{{ $row->id_shift }}">
                                <label class="form-label required">Nama Shift</label>
                                <input type="text" name="edit_nama[{{ $row->id_shift }}]" class="form-control" value="{{ $row->nama }}" placeholder="Contoh : Office" autocomplete="off" />
                            </div>
                        </div>
                        <div class="col-md-1 col-sm-2 d-flex align-items-center justify-content-center">
                            <button type="button" data-reload="big" onclick="hapus_data(this,event,{{ $row->id_shift }},`shift`,`id_shift`)" class="btn btn-danger btn-sm" title="Delete" style="height: 38px; margin-top: 5px;"> 
                                {{-- Style ditambahkan agar sejajar dengan input di sampingnya --}}
                                <i class="fa fa-trash"></i> 
                            </button>
                        </div>
                        {{-- Baris 2 --}}
                        <div class="col-md-4 col-sm-12">
                            <div class="mb-3" id="req_jam_masuk_{{ $row->id_shift }}">
                                <label class="form-label required">Jam Masuk</label>
                                <input type="time" name="edit_jam_masuk[{{ $row->id_shift }}]" class="form-control" value="{{ date('H:i',strtotime($row->jam_masuk)) }}" placeholder="Contoh : 07:00" autocomplete="off" />
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="mb-3" id="req_jam_pulang_{{ $row->id_shift }}">
                                <label class="form-label required">Jam Pulang</label>
                                <input type="time" name="edit_jam_pulang[{{ $row->id_shift }}]" class="form-control" value="{{ date('H:i',strtotime($row->jam_pulang)) }}" placeholder="Contoh : 17:00" autocomplete="off" />
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="mb-3" id="req_lembur_{{ $row->id_shift }}">
                                <label class="form-label">Batas Lembur (Menit)</label>
                                <input type="number" name="edit_lembur[{{ $row->id_shift }}]" class="form-control" value="{{ $row->lembur }}" placeholder="-" autocomplete="off" />
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        {{-- Container untuk Shift Baru (yang akan diisi oleh addShift()) --}}
        <div id="data_add_shift" class="row px-lg-5 px-sm-0">
            {{-- Hasil dari addShift() akan masuk di sini --}}
        </div>

        {{-- Tombol Aksi --}}
        <div class="w-100 d-flex justify-content-center align-items-center pt-5 pb-5"> 
            <button type="button" id="plus_shift" onclick="addShift()" class="mx-5 btn-modal btn btn-info">
                <i class="fa fa-plus"></i>
                <span class="indicator-label">Tambah</span>
            </button>

            <button type="button" id="submit_shift" data-loader="big" onclick="submit_form(this,'#form_shift')" class="mx-5 btn-modal btn btn-primary {{ ($shift->isEmpty()) ? 'd-none' : '' }}">
                <span class="indicator-label">Simpan</span>
            </button>
        </div>
    </form>
</div>
<script src="{{ asset('assets/js/all.js') }}"></script>

<div class="card p-5 mt-5"> 
    <form id="form_shift_save" class="form" action="{{ route('setup.shift.save') }}" method="POST">
        @csrf
        
        {{-- Alert --}}
        @if(session('success_shift'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                {{ session('success_shift') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        {{-- Data Shift Lama --}}
        <div id="data_shift" class="{{ ($shift->isEmpty()) ? 'd-none' : '' }}">
            @foreach($shift as $i => $row)
            <div class="p-4 mb-4 bg-light rounded-4 shadow-sm shift-box">
                <input type="hidden" name="id[{{ $i }}]" value="{{ $row->id_shift }}">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Kode Shift</label>
                        <input type="text" name="kode[{{ $i }}]" class="form-control"
                            value="{{ $row->kode }}" placeholder="Contoh : O" autocomplete="off" />
                    </div>
                    <div class="col-md-6 mb-3 d-flex">
                        <div class="flex-grow-1">
                            <label class="form-label required">Nama Shift</label>
                            <input type="text" name="nama[{{ $i }}]" class="form-control"
                                value="{{ $row->nama }}" placeholder="Contoh : Office" autocomplete="off" />
                        </div>
                        <a href="{{ route('setup.shift.delete', $row->id_shift) }}" 
                           class="btn btn-danger ms-2 align-self-end"
                           onclick="return confirm('Yakin ingin menghapus shift ini?')">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Jam Masuk</label>
                        <input type="time" name="jam_masuk[{{ $i }}]" class="form-control"
                            value="{{ date('H:i',strtotime($row->jam_masuk)) }}" />
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label required">Jam Pulang</label>
                        <input type="time" name="jam_pulang[{{ $i }}]" class="form-control"
                            value="{{ date('H:i',strtotime($row->jam_pulang)) }}" />
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Batas Lembur (Menit)</label>
                        <input type="number" name="lembur[{{ $i }}]" class="form-control"
                            value="{{ $row->lembur }}" placeholder="-" />
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Data Shift Baru --}}
        <div id="data_add_shift"></div>

        {{-- Kondisi Kosong --}}
        <div id="no_data_vector" class="w-100 d-flex {{ ($shift->isNotEmpty()) ? 'd-none' : '' }} justify-content-center align-items-center flex-column py-5">
            <div class="background-partisi-contain" style="background-image:url('{{ image_check('empty.svg','default') }}');width:300px;height:250px;"></div>
            <h3 class="text-center text-info fs-3">Tidak Ada Data</h3>
            <p class="text-muted">Belum ada data shift! Silahkan tambahkan data shift terlebih dahulu</p>
        </div>

        {{-- Tombol --}}
        <div class="w-100 d-flex justify-content-center align-items-center pt-4">
            <button type="button" id="plus_shift" onclick="addShift()" class="btn btn-success me-3">
                + Tambah Shift
            </button>
            <button type="submit" id="submit_shift" class="btn btn-primary">
                Simpan
            </button>
        </div>
    </form>
</div>

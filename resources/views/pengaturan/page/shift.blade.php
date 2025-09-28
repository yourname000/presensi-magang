<!--begin::Form-->
<form id="form_shift" class="form mt-10 pt-5" action="{{ route('setup.shift') }}"  method="POST" enctype="multipart/form-data">
    <div id="no_data_vector" class="w-100 d-flex {{ ($shift->isNotEmpty()) ? 'd-none' : '' }} justify-content-center align-items-center flex-column ">
        <div class="background-partisi-contain" style="background-image : url('{{ image_check('empty.svg','default') }}');width : 300px;height : 250px;"></div>
        <h3 class="text-center text-info fs-3">Tidak Ada Data</h3>
        <p class="text-muted">Belum ada data shift! Silahkan tambahkan data shift terlebih dahulu</p>
    </div>
    <div id="data_shift" class="row px-lg-5 px-sm-0 {{ ($shift->isEmpty()) ? 'd-none' : '' }}">
        @if($shift->isNotEmpty())
            @foreach($shift AS $row)
            <div class="col-12 card bg-secondary mb-7 rounded py-7 px-4">
                <div class="row w-100">
                    <div class="col-4">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7" id="req_kode_{{ $row->id_shift }}">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Kode Shift</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="edit_kode[{{ $row->id_shift }}]" class="form-control mb-3 mb-lg-0" value="{{ $row->kode }}" placeholder="Contoh : O" autocomplete="off" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <div class="col-7">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7" id="req_nama_{{ $row->id_shift }}">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Nama Shift</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="edit_nama[{{ $row->id_shift }}]" class="form-control mb-3 mb-lg-0" value="{{ $row->nama }}" placeholder="Contoh : Office" autocomplete="off" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <div class="col-1 d-flex align-items-center justify-content-center">
                        <button type="button" data-reload="big" onclick="hapus_data(this,event,{{ $row->id_shift }},`shift`,`id_shift`)"  class="btn btn-icon btn-danger btn-sm" title="Delete">
                            <i class="ki-outline ki-trash fs-2"></i>
                        </button>
                    </div>
                    <div class="col-4">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7" id="req_jam_masuk_{{ $row->id_shift }}">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Jam Masuk</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="time" name="edit_jam_masuk[{{ $row->id_shift }}]" class="form-control mb-3 mb-lg-0" value="{{ date('H:i',strtotime($row->jam_masuk)) }}" placeholder="Contoh : 07:00" autocomplete="off" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <div class="col-4">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7" id="req_jam_pulang_{{ $row->id_shift }}">
                            <!--begin::Label-->
                            <label class="required fw-semibold fs-6 mb-2">Jam Pulang</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="time" name="edit_jam_pulang[{{ $row->id_shift }}]" class="form-control mb-3 mb-lg-0" value="{{ date('H:i',strtotime($row->jam_pulang)) }}" placeholder="Contoh : 17:00" autocomplete="off" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <div class="col-4">
                        <!--begin::Input group-->
                        <div class="fv-row mb-7" id="req_lembur_{{ $row->id_shift }}">
                            <!--begin::Label-->
                            <label class="fw-semibold fs-6 mb-2">Batas Lembur (Menit)</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" name="edit_lembur[{{ $row->id_shift }}]" class="form-control mb-3 mb-lg-0" value="{{ $row->lembur }}" placeholder="-" autocomplete="off" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <div id="data_add_shift" class="row px-lg-5 px-sm-0">
    </div>

    
    <!--begin::Actions-->
    <div class="w-100 d-flex justify-content-center align-items-center pt-15">
        <button type="button" id="plus_shift" onclick="addShift()" class="mx-5 btn-modal btn btn-info">
            <i class="fa-solid fa-plus"></i>
            <span class="indicator-label">Tambah</span>
        </button>

        <button type="button" id="submit_shift" data-loader="big" onclick="submit_form(this,'#form_shift')" class="mx-5 btn-modal btn btn-primary {{ ($shift->isEmpty()) ? 'd-none' : '' }}">
            <span class="indicator-label">Simpan</span>
        </button>
    </div>
    <!--end::Actions-->
</form>
<!--end::Form-->
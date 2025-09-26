<!--begin::Form-->
<form id="form_shift" class="mt-4 pt-3" action="{{ route('setup.shift') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div id="no_data_vector" class="w-100 d-flex {{ ($shift->isNotEmpty()) ? 'd-none' : '' }} justify-content-center align-items-center flex-column ">
        <div class="background-partisi-contain" style="background-image : url('{{ image_check('empty.svg','default') }}');width : 300px;height : 250px;"></div>
        <h3 class="text-center text-info fs-3">Tidak Ada Data</h3>
        <p class="text-muted">Belum ada data shift! Silahkan tambahkan data shift terlebih dahulu</p>
    </div>
    <div id="data_shift" class="row px-4 {{ ($shift->isEmpty()) ? 'd-none' : '' }}">
        @if($shift->isNotEmpty())
            @foreach($shift AS $row)
            <div class="col-12 card bg-light mb-4 rounded py-4 px-3">
                <div class="row w-100">
                    <div class="col-md-4">
                        <!--begin::Input group-->
                        <div class="mb-3" id="req_kode_{{ $row->id_shift }}">
                            <!--begin::Label-->
                            <label class="form-label fw-semibold">Kode Shift</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="edit_kode[{{ $row->id_shift }}]" class="form-control" value="{{ $row->kode }}" placeholder="Contoh : O" autocomplete="off" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <div class="col-md-7">
                        <!--begin::Input group-->
                        <div class="mb-3" id="req_nama_{{ $row->id_shift }}">
                            <!--begin::Label-->
                            <label class="form-label fw-semibold">Nama Shift</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" name="edit_nama[{{ $row->id_shift }}]" class="form-control" value="{{ $row->nama }}" placeholder="Contoh : Office" autocomplete="off" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <a href="{{ route('setup.shift.delete', $row->id_shift) }}" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Yakin ingin menghapus shift ini?')">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <!--begin::Input group-->
                        <div class="mb-3" id="req_jam_masuk_{{ $row->id_shift }}">
                            <!--begin::Label-->
                            <label class="form-label fw-semibold">Jam Masuk</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="time" name="edit_jam_masuk[{{ $row->id_shift }}]" class="form-control" value="{{ date('H:i',strtotime($row->jam_masuk)) }}" placeholder="Contoh : 07:00" autocomplete="off" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <div class="col-md-4">
                        <!--begin::Input group-->
                        <div class="mb-3" id="req_jam_pulang_{{ $row->id_shift }}">
                            <!--begin::Label-->
                            <label class="form-label fw-semibold">Jam Pulang</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="time" name="edit_jam_pulang[{{ $row->id_shift }}]" class="form-control" value="{{ date('H:i',strtotime($row->jam_pulang)) }}" placeholder="Contoh : 17:00" autocomplete="off" required />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <div class="col-md-4">
                        <!--begin::Input group-->
                        <div class="mb-3" id="req_lembur_{{ $row->id_shift }}">
                            <!--begin::Label-->
                            <label class="form-label fw-semibold">Batas Lembur (Menit)</label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="number" name="edit_lembur[{{ $row->id_shift }}]" class="form-control" value="{{ $row->lembur }}" placeholder="-" autocomplete="off" />
                            <!--end::Input-->
                        </div>
                        <!--end::Input group-->
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <!-- Form untuk tambah shift baru -->
    <div id="data_add_shift" class="row px-4">
        <!--begin::Input group-->
        <div class="col-12 card bg-light mb-4 rounded py-4 px-3">
            <div class="row w-100">
                <div class="col-md-4">
                    <!--begin::Input group-->
                    <div class="mb-3">
                        <!--begin::Label-->
                        <label class="form-label fw-semibold">Kode Shift Baru</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="kode_baru" class="form-control" placeholder="Contoh : O" autocomplete="off" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-7">
                    <!--begin::Input group-->
                    <div class="mb-3">
                        <!--begin::Label-->
                        <label class="form-label fw-semibold">Nama Shift Baru</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="nama_baru" class="form-control" placeholder="Contoh : Office" autocomplete="off" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-4">
                    <!--begin::Input group-->
                    <div class="mb-3">
                        <!--begin::Label-->
                        <label class="form-label fw-semibold">Jam Masuk Baru</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="time" name="jam_masuk_baru" class="form-control" placeholder="Contoh : 07:00" autocomplete="off" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-4">
                    <!--begin::Input group-->
                    <div class="mb-3">
                        <!--begin::Label-->
                        <label class="form-label fw-semibold">Jam Pulang Baru</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="time" name="jam_pulang_baru" class="form-control" placeholder="Contoh : 17:00" autocomplete="off" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="col-md-4">
                    <!--begin::Input group-->
                    <div class="mb-3">
                        <!--begin::Label-->
                        <label class="form-label fw-semibold">Batas Lembur Baru (Menit)</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="number" name="lembur_baru" class="form-control" placeholder="-" autocomplete="off" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
            </div>
        </div>
        <!--end::Input group-->
    </div>

    <!--begin::Actions-->
    <div class="w-100 d-flex justify-content-center align-items-center pt-4">
        <button type="submit" class="mx-2 btn btn-primary {{ ($shift->isEmpty()) ? 'd-none' : '' }}">
            <span>Simpan</span>
        </button>
    </div>
    <!--end::Actions-->
</form>
<!--end::Form-->

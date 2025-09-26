<form method="POST" class="form mt-10 pt-5" action="{{ route('setting.website') }}" id="form_ubah_setting" enctype="multipart/form-data">
    @csrf
    <div class="container px-lg-5 px-sm-0">

        <!-- ICONS -->
        <div class="row mb-6">
            <div class="col-lg-12 col-md-12 col-sm-12"></div>
                <div class="mb-3 px-10" id="req_meta_title">
                    <label class="form-label fw-semibold fs-6 mb-2 required">Judul Website</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $result->meta_title) }}" class="form-control form-control-lg mb-3 mb-lg-0" placeholder="Contoh: Web HRM" autocomplete="off" />
                </div>
            </div>
            <!-- ICON -->
            <div class="col-lg-6 d-flex justify-content-center align-items-center flex-column">
                <div class="mb-3 text-center">
                    <div class="mb-2"></div>
                        <img src="{{ image_check($result->icon, 'setting') }}" alt="Icon" class="rounded bg-light" style="width:100px;height:100px;object-fit:contain;background-image: url('{{ image_check('default.jpg','default') }}');">
                    </div>
                    <label class="btn btn-outline-primary btn-sm mb-1">
                        Ubah data
                        <input type="file" name="icon" accept=".png, .jpg, .ico" hidden />
                    </label>
                </div>
                <div class="form-text text-danger">Icon</div>
                <div class="form-text">Tipe yang didukung: png, jpg, ico</div>
                <input type="hidden" name="name_icon" value="{{ $result->icon }}">
            </div>

            <!-- LOGO -->
            <div class="col-lg-6 d-flex justify-content-center align-items-center flex-column">
                <div class="mb-3 text-center">
                    <div class="mb-2">
                        <img src="{{ image_check($result->logo, 'setting') }}" alt="Logo" class="rounded bg-light" style="width:200px;height:125px;object-fit:contain;background-image: url('{{ image_check('default.jpg','default') }}');">
                    </div>
                    <label class="btn btn-outline-primary btn-sm mb-1">
                        Ubah data
                        <input type="file" name="logo" accept=".png, .jpg" hidden />
                    </label>
                </div>
                <div class="form-text text-danger">Logo</div>
                <div class="form-text">Tipe yang didukung: png, jpg</div>
                <input type="hidden" name="name_logo" value="{{ $result->logo }}">
            </div>
        </div>

        <div class="row w-100">
            <div class="col-12 w-100 d-flex justify-content-center">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>

    </div>
</form>

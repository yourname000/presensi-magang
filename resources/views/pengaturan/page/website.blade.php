<form method="POST" class="form mt-10 pt-5" action="{{ route('setting.website') }}" enctype="multipart/form-data">
    @csrf
    <div class="container px-lg-5 px-sm-0">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @elseif(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-info me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


        <div class="row mb-6"> 
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="fv-row mb-7 px-10">
                    <label class="required fw-semibold fs-6 mb-2">Judul Website</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $result->meta_title) }}" class="form-control mb-3 mb-lg-0 form-control-lg" placeholder="Contoh: Web HRM" autocomplete="off" />
                    @error('meta_title')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-lg-6 d-flex justify-content-center align-items-center flex-column pt-3"> 
                <div class="image-input image-input-outline background-partisi-contain" style="background-image: url('{{ asset('data/setting/' . ($result->icon ?? 'default.jpg')) }}')">
                    <div class="image-input-wrapper w-100px h-100px background-partisi-contain"></div>
                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow">
                        <i class="fa fa-pencil fs-7"></i>
                        <input type="file" name="icon" accept=".png, .jpg, .ico" />
                    </label>
                </div>
                <div class="form-text text-danger mt-3">Icon</div> 
                <div class="form-text">Tipe yang didukung: png, jpg, ico</div>
            </div>

            <div class="col-lg-6 d-flex justify-content-center align-items-center flex-column pt-3"> 
                <div class="image-input image-input-outline background-partisi-contain" style="background-image: url('{{ asset('data/setting/' . ($result->logo ?? 'default.jpg')) }}')">
                    <div class="image-input-wrapper w-200px h-125px background-partisi-contain" style="background-size: contain;"></div>
                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow">
                        <i class="fa fa-pencil fs-7"></i>
                        <input type="file" name="logo" accept=".png, .jpg" />
                    </label>
                </div>
                <div class="form-text text-danger mt-3">Logo</div> 
                <div class="form-text">Tipe yang didukung: png, jpg</div>
            </div>

        </div> 

        <div class="row w-100">
            <div class="col-12 w-100 d-flex justify-content-center">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</form>

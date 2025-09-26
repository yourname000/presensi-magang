<!--begin::Form-->
<form id="form_location" class="mt-4" action="{{ route('setup.location') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <!--begin::Input group-->
            <div class="mb-3" id="req_lat">
                <!--begin::Label-->
                <label class="form-label required fw-semibold">Latitude</label>
                <!--end::Label-->
                <!--begin::Input-->
                <input type="text" name="lat" value="{{ $result->lat }}" class="form-control" placeholder="Contoh: -7.1556234" autocomplete="off" />
                <!--end::Input-->
            </div>
            <!--end::Input group-->
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <!--begin::Input group-->
            <div class="mb-3" id="req_lng">
                <!--begin::Label-->
                <label class="form-label required fw-semibold">Longitude</label>
                <!--end::Label-->
                <!--begin::Input-->
                <input type="text" name="lng" value="{{ $result->lng }}" class="form-control" placeholder="Contoh: 112.6136678" autocomplete="off" />
                <!--end::Input-->
            </div>
            <!--end::Input group-->
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <!--begin::Input group-->
            <div class="mb-3" id="req_radius">
                <!--begin::Label-->
                <label class="form-label required fw-semibold">Radius</label>
                <!--end::Label-->
                <div class="input-group">
                    <input type="number" name="radius" value="{{ $result->radius }}" class="form-control" placeholder="Contoh: 50" aria-describedby="radius-label">
                    <span class="input-group-text" id="radius-label">Meter</span>
                </div>
            </div>
            <!--end::Input group-->
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <!--begin::Input group-->
            <div class="mb-3" id="req_lokasi">
                <!--begin::Label-->
                <label class="form-label fw-semibold">Nama Lokasi</label>
                <!--end::Label-->
                <!--begin::Input-->
                <input type="text" name="lokasi" value="{{ $result->lokasi }}" class="form-control" placeholder="Contoh: Kantor Pusat" autocomplete="off" />
                <!--end::Input-->
            </div>
            <!--end::Input group-->
        </div>
    </div>
    
    <!--begin::Actions-->
    <div class="w-100 d-flex justify-content-center align-items-center pt-4">
        <button type="submit" class="btn btn-primary mx-2">
            <span>Simpan</span>
        </button>
    </div>
    <!--end::Actions-->
</form>
<!--end::Form-->
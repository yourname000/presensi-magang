<!--begin::Form-->
<form id="form_location" class="form" action="{{ route('setup.location') }}"  method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <!--begin::Input group-->
            <div class="fv-row mb-7" id="req_lat">
                <!--begin::Label-->
                <label class="required fw-semibold fs-6 mb-2">Latitude</label>
                <!--end::Label-->
                <!--begin::Input-->
                <input type="text" name="lat" value="{{ $result->lat }}" class="form-control mb-3 mb-lg-0 form-control-lg" placeholder="Contoh: -7.1556234" autocomplete="off" />
                <!--end::Input-->
            </div>
            <!--end::Input group-->
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <!--begin::Input group-->
            <div class="fv-row mb-7" id="req_lng">
                <!--begin::Label-->
                <label class="required fw-semibold fs-6 mb-2">Longitude</label>
                <!--end::Label-->
                <!--begin::Input-->
                <input type="text" name="lng" value="{{ $result->lng }}" class="form-control mb-3 mb-lg-0 form-control-lg" placeholder="Contoh: 112.6136678" autocomplete="off" />
                <!--end::Input-->
            </div>
            <!--end::Input group-->
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <!--begin::Input group-->
            <div class="fv-row mb-7" id="req_radius">
                <!--begin::Label-->
                <label class="required fw-semibold fs-6 mb-2">Radius</label>
                <!--end::Label-->
                <div class="input-group">
                    <input type="number" name="radius" value="{{ $result->radius }}" class="form-control mb-3 mb-lg-0 form-control-lg" placeholder="Contoh: 50" aria-describedby="radius-label">
                    <span class="input-group-text" id="radius-label">Meter</span>
                </div>
            </div>
            <!--end::Input group-->
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <!--begin::Input group-->
            <div class="fv-row mb-7" id="req_lokasi">
                <!--begin::Label-->
                <label class="fw-semibold fs-6 mb-2">Nama Lokasi</label>
                <!--end::Label-->
                <!--begin::Input-->
                <input type="text" name="lokasi" value="{{ $result->lokasi }}" class="form-control mb-3 mb-lg-0 form-control-lg" placeholder="Contoh: Kantor Pusat" autocomplete="off" />
                <!--end::Input-->
            </div>
            <!--end::Input group-->
        </div>
    </div>
    
    <!--begin::Actions-->
   <div class="w-100 d-flex justify-content-center align-items-center mt-5">
        <button type="button" id="submit_location" onclick="submit_form(this,'#form_location')" class="btn-modal btn btn-primary">
            <span class="indicator-label">Simpan</span>
        </button>
    </div>
    <!--end::Actions-->
</form>
<!--end::Form-->
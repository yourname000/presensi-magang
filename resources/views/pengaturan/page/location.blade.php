{{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mt-2">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

{{-- Form --}}
<div class="card p-4 mt-4 shadow-sm">
    <form action="{{ route('setup.location') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Latitude <span class="text-danger">*</span></label>
                <input type="text" name="lat" class="form-control" 
                    value="{{ old('lat', $result->lat ?? '') }}" placeholder="Contoh: -7.1556234" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Longitude <span class="text-danger">*</span></label>
                <input type="text" name="lng" class="form-control" 
                    value="{{ old('lng', $result->lng ?? '') }}" placeholder="Contoh: 112.6136678" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Radius (Meter) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="radius" class="form-control" 
                        value="{{ old('radius', $result->radius ?? '') }}" placeholder="Contoh: 50" required>
                    <span class="input-group-text">m</span>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Nama Lokasi</label>
                <input type="text" name="lokasi" class="form-control" 
                    value="{{ old('lokasi', $result->lokasi ?? '') }}" placeholder="Contoh: Kantor Pusat">
            </div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary px-4">Simpan</button>
        </div>
    </form>
</div>

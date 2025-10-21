<div class="modal fade" id="kt_modal_profile" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-body mx-5 mx-xl-15 my-7">
                <form id="form_profile" action="{{ route('update.profile') }}" method="POST">
                    @csrf
                    <div class="text-center mb-3">
                        <img src="{{ $profile->image ? asset('data/user/'.$profile->image) : asset('data/default/user.jpg') }}" 
                             alt="Foto Profil"
                             class="rounded-circle mb-3 shadow-sm"
                             width="120"
                             height="120">
                    </div>

                    <div class="fv-row mb-7">
                        <label class="fw-semibold fs-6 mb-2">Nama Pengguna</label>
                        <input type="text" name="username" 
                               class="form-control form-control-solid" 
                               value="{{ $profile->username ?? '' }}" 
                               readonly>
                    </div>

                    <div class="fv-row mb-7">
                        <label class="fw-semibold fs-6 mb-2">Kata Sandi Lama</label>
                        <input type="password" name="kata_sandi" class="form-control">
                    </div>

                    <div class="fv-row mb-7">
                        <label class="fw-semibold fs-6 mb-2">Kata Sandi Baru</label>
                        <input type="password" name="kata_sandi_baru" class="form-control">
                    </div>

                    <div class="fv-row mb-7">
                        <label class="fw-semibold fs-6 mb-2">Konfirmasi Sandi Baru</label>
                        <input type="password" name="kata_sandi_konfirm" class="form-control">
                    </div>

                    <div class="text-center pt-10">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

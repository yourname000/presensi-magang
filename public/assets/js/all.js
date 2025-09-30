function addShift() {
    $('#no_data_vector').addClass('d-none');
    $('#submit_shift').removeClass('d-none');

    let num = $('#data_add_shift').children('.shift-box').length;

    let html = `
    <div id="pane_add_${num}" class="shift-box p-4 mb-4 bg-light rounded-4 shadow-sm">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label required">Kode Shift</label>
                <input type="text" name="kode[${num}]" class="form-control" placeholder="Contoh : O" />
            </div>
            <div class="col-md-6 mb-3 d-flex">
                <div class="flex-grow-1">
                    <label class="form-label required">Nama Shift</label>
                    <input type="text" name="nama[${num}]" class="form-control" placeholder="Contoh : Office" />
                </div>
                <button type="button" onclick="removeShift(this)" class="btn btn-danger ms-2 align-self-end">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label required">Jam Masuk</label>
                <input type="time" name="jam_masuk[${num}]" class="form-control" placeholder="--:--" />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label required">Jam Pulang</label>
                <input type="time" name="jam_pulang[${num}]" class="form-control" placeholder="--:--" />
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Batas Lembur (Menit)</label>
                <input type="number" name="lembur[${num}]" class="form-control" placeholder="-" />
            </div>
        </div>
    </div>`;

    $('#data_add_shift').append(html);
}

function removeShift(el) {
    $(el).closest('.shift-box').remove();

    // Re-index
    $('#data_add_shift .shift-box').each(function (i) {
        $(this).attr('id', 'pane_add_' + i);
        $(this).find('input[name^="kode"]').attr('name', 'kode[' + i + ']');
        $(this).find('input[name^="nama"]').attr('name', 'nama[' + i + ']');
        $(this).find('input[name^="jam_masuk"]').attr('name', 'jam_masuk[' + i + ']');
        $(this).find('input[name^="jam_pulang"]').attr('name', 'jam_pulang[' + i + ']');
        $(this).find('input[name^="lembur"]').attr('name', 'lembur[' + i + ']');
    });

    if ($('#data_add_shift .shift-box').length === 0 && $('#data_shift .p-4').length === 0) {
        $('#no_data_vector').removeClass('d-none');
        $('#submit_shift').addClass('d-none');
    }
}

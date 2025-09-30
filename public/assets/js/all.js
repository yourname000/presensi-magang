// tambah shift
function addShift() {
    $('#no_data_vector').addClass('d-none');
    $('#submit_shift').removeClass('d-none');
    
    let num = $('#data_add_shift').children('.row').length; 
    let html = '';

    // ROW UTAMA per shift
    html += '<div id="pane_add_' + num + '" class="row pb-4 border-bottom mb-4">'; 

    // ==========================================================
    // BARIS 1: KODE SHIFT (col-4) & NAMA SHIFT (col-8) -> TOTAL 12
    // ==========================================================
    
    // Kode Shift (col-4)
    html += '    <div class="col-md-4 col-sm-12">'; 
    html += '      <div class="mb-3" id="req_kode_new_' + num + '">'; 
    html += '        <label class="form-label required">Kode Shift</label>'; 
    html += '        <input type="text" name="kode[' + num + ']" class="form-control" placeholder="Contoh : S" autocomplete="off" />';
    html += '      </div>';
    html += '    </div>';

    // Nama Shift (col-8)
    html += '    <div class="col-md-8 col-sm-12">'; 
    html += '      <div class="mb-3" id="req_nama_new_' + num + '">';
    html += '        <label class="form-label required">Nama Shift</label>';
    html += '        <input type="text" name="nama[' + num + ']" class="form-control" placeholder="Contoh : Shift Pagi" autocomplete="off" />';
    html += '      </div>';
    html += '    </div>';
    
    // ==========================================================
    // BARIS 2: JAM MASUK (col-3), JAM PULANG (col-3), LEMBUR (col-4), HAPUS (col-2) -> TOTAL 12
    // ==========================================================

    // Jam Masuk (col-3)
    html += '    <div class="col-md-3 col-sm-12">';
    html += '      <div class="mb-3" id="req_jam_masuk_new_' + num + '">';
    html += '        <label class="form-label required">Jam Masuk</label>';
    html += '        <input type="time" name="jam_masuk[' + num + ']" class="form-control" placeholder="--:--" autocomplete="off" />';
    html += '      </div>';
    html += '    </div>';

    // Jam Pulang (col-3)
    html += '    <div class="col-md-3 col-sm-12">';
    html += '      <div class="mb-3" id="req_jam_pulang_new_' + num + '">';
    html += '        <label class="form-label required">Jam Pulang</label>';
    html += '        <input type="time" name="jam_pulang[' + num + ']" class="form-control" placeholder="--:--" autocomplete="off" />';
    html += '      </div>';
    html += '    </div>';

    // Lembur (col-4)
    html += '    <div class="col-md-4 col-sm-10">';
    html += '      <div class="mb-3" id="req_lembur_new_' + num + '">';
    html += '        <label class="form-label">Batas Lembur (Menit)</label>';
    html += '        <input type="number" name="lembur[' + num + ']" class="form-control" placeholder="-" autocomplete="off" />';
    html += '      </div>';
    html += '    </div>';

    // Tombol Hapus (col-2)
    html += '    <div class="col-md-2 col-sm-2 d-flex justify-content-start align-self-end">'; 
    html += '      <button type="button" onclick="removeShift(this)" class="btn btn-danger btn-sm" title="Hapus" style="height: 38px; margin-bottom: 12px;">'; 
    html += '        <i class="fa fa-trash"></i>'; 
    html += '      </button>';
    html += '    </div>';

    html += '  </div>'; // Penutup row (pane_add)

    $('#data_add_shift').append(html);
}

// Fungsi removeShift tetap sama
function removeShift(el) {
    // ... (kode re-indexing dan hapus shift)
    // ... (pastikan semua indexing name dan id disesuaikan)
    $(el).closest('.row').remove();

    // Re-indexing
    $('#data_add_shift .row').each(function (i) {
        $(this).attr('id', 'pane_add_' + i);

        $(this).find('input[name^="kode"]').attr('name', 'kode[' + i + ']');
        $(this).find('input[name^="nama"]').attr('name', 'nama[' + i + ']');

        $(this).find('input[name^="jam_masuk"]').attr('name', 'jam_masuk[' + i + ']');
        $(this).find('input[name^="jam_pulang"]').attr('name', 'jam_pulang[' + i + ']');
        $(this).find('input[name^="lembur"]').attr('name', 'lembur[' + i + ']');
    });

    if ($('#data_add_shift .row').length === 0 && $('#data_shift .row').length === 0) {
        $('#no_data_vector').removeClass('d-none');
        $('#submit_shift').addClass('d-none');
    }
}
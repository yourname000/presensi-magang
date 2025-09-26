
const pengaturanTabs = document.querySelectorAll('.pengaturan-tab-link');
const pengaturanUnderline = document.getElementById('pengaturanTabUnderline');
const pengaturanPanes = document.querySelectorAll('.pengaturan-tab-content > div');

function movePengaturanUnderline(activeTab) {
pengaturanUnderline.style.width = activeTab.offsetWidth + "px";
pengaturanUnderline.style.left = activeTab.offsetLeft + "px";
}

// Set default underline
let activeTab = document.querySelector('.pengaturan-tab-link.active');
movePengaturanUnderline(activeTab);

pengaturanTabs.forEach(tab => {
tab.addEventListener('click', () => {
    // switch active tab
    pengaturanTabs.forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    activeTab = tab;
    movePengaturanUnderline(tab);

    // switch content pakai d-none
    pengaturanPanes.forEach(p => p.classList.add('d-none'));
    const target = document.querySelector(tab.getAttribute('data-bs-target'));
    target.classList.remove('d-none');
});
});

// Update posisi underline saat resize
window.addEventListener('resize', () => {
movePengaturanUnderline(activeTab);
});



function set_url_params(pageValue) {
  const url = new URL(window.location.href);
  url.searchParams.set('page', pageValue);
  window.history.pushState({}, '', url);
}


// tambah shift
function addShift() {
    $('#no_data_vector').addClass('d-none');
    $('#submit_shift').removeClass('d-none');
    let num = $('#data_add_shift').children('.card').length;
    let html = '';

    html += '<div id="pane_add_' + num + '" class="col-12 card bg-secondary mb-7 rounded py-7 px-4">';
    html += '  <div class="row w-100">';

    // Kode Shift
    html += '    <div class="col-4">';
    html += '      <div class="fv-row mb-7" id="req_kode_' + num + '">';
    html += '        <label class="required fw-semibold fs-6 mb-2">Kode Shift</label>';
    html += '        <input type="text" name="kode[' + num + ']" class="form-control mb-3 mb-lg-0" placeholder="Contoh : O" autocomplete="off" />';
    html += '      </div>';
    html += '    </div>';

    // Nama Shift
    html += '    <div class="col-7">';
    html += '      <div class="fv-row mb-7" id="req_nama_' + num + '">';
    html += '        <label class="required fw-semibold fs-6 mb-2">Nama Shift</label>';
    html += '        <input type="text" name="nama[' + num + ']" class="form-control mb-3 mb-lg-0" placeholder="Contoh : Office" autocomplete="off" />';
    html += '      </div>';
    html += '    </div>';

    // Tombol Hapus
    html += '    <div class="col-1 d-flex align-items-center justify-content-center">';
    html += '      <button type="button" onclick="removeShift(this)" class="btn btn-icon btn-danger btn-sm btn-remove-shift" title="Delete">';
    html += '        <i class="ki-outline ki-trash fs-2"></i>';
    html += '      </button>';
    html += '    </div>';

    // Jam Masuk
    html += '    <div class="col-4">';
    html += '      <div class="fv-row mb-7" id="req_jam_masuk_' + num + '">';
    html += '        <label class="required fw-semibold fs-6 mb-2">Jam Masuk</label>';
    html += '        <input type="time" name="jam_masuk[' + num + ']" class="form-control mb-3 mb-lg-0" placeholder="Contoh : 07:00" autocomplete="off" />';
    html += '      </div>';
    html += '    </div>';

    // Jam Pulang
    html += '    <div class="col-4">';
    html += '      <div class="fv-row mb-7" id="req_jam_pulang_' + num + '">';
    html += '        <label class="required fw-semibold fs-6 mb-2">Jam Pulang</label>';
    html += '        <input type="time" name="jam_pulang[' + num + ']" class="form-control mb-3 mb-lg-0" placeholder="Contoh : 17:00" autocomplete="off" />';
    html += '      </div>';
    html += '    </div>';

    // Lembur
    html += '    <div class="col-4">';
    html += '      <div class="fv-row mb-7" id="req_lembur_' + num + '">';
    html += '        <label class="fw-semibold fs-6 mb-2">Batas Lembur</label>';
    html += '        <input type="number" name="lembur[' + num + ']" class="form-control mb-3 mb-lg-0" placeholder="-" autocomplete="off" />';
    html += '      </div>';
    html += '    </div>';

    html += '  </div>'; // row
    html += '</div>';   // card

    $('#data_add_shift').append(html);
}

// remove + reindex + cek child
function removeShift(el) {
    $(el).closest('.card').remove();

    // reindex semua biar id dan name tetap urut
    $('#data_add_shift .card').each(function (i) {
        $(this).attr('id', 'pane_add_' + i);

        $(this).find('[id^="req_kode"]').attr('id', 'req_kode_' + i)
            .find('input').attr('name', 'kode[' + i + ']');

        $(this).find('[id^="req_nama"]').attr('id', 'req_nama_' + i)
            .find('input').attr('name', 'nama[' + i + ']');

        $(this).find('[id^="req_jam_masuk"]').attr('id', 'req_jam_masuk_' + i)
            .find('input').attr('name', 'jam_masuk[' + i + ']');

        $(this).find('[id^="req_jam_pulang"]').attr('id', 'req_jam_pulang_' + i)
            .find('input').attr('name', 'jam_pulang[' + i + ']');

        $(this).find('[id^="req_lembur"]').attr('id', 'req_lembur_' + i)
            .find('input').attr('name', 'lembur[' + i + ']');
    });

    // cek apakah masih ada child di #data_add_shift
    if ($('#data_add_shift .card').length === 0) {
        if ($('#data_shift').children().length > 0) {
            // ada data lama, tampilkan kembali data_shift
            $('#data_shift').removeClass('d-none');
        } else {
            // tidak ada data lama maupun baru, tampilkan pesan no data
            $('#no_data_vector').removeClass('d-none');
            $('#submit_shift').addClass('d-none'); // tombol submit sembunyikan juga
        }
    }
}


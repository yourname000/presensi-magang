let karyawanTable;
let currentFilterStatus = '';

document.addEventListener('DOMContentLoaded', function () {
    karyawanTable = initGlobalDatatable('#table_karyawan', function () {
        return {
            filter_status: currentFilterStatus,
        };
    });

    // Trigger reload on each filter
    document.querySelectorAll('.table-filter').forEach(el => {
        el.addEventListener('change', function () {
            if (karyawanTable) karyawanTable.ajax.reload();
        });
    });
});



function filter_apply(){
    currentFilterStatus = $('#filter_status').val();
    if (karyawanTable) {
        karyawanTable.ajax.reload();
    }
}


var title = $('#title_modal').data('title').split('|');
$(function () {

    $('#kt_modal_karyawan').on('shown.bs.modal', function () {
        $('#select_id_departemen').select2({ dropdownParent: $('#form_karyawan') });
    });

});

function ubah_data(element, id) {
    var form = document.getElementById('form_karyawan');
    $('#title_modal').html(title[0]);
    form.setAttribute('action', BASE_URL + '/master/karyawan/update');
    $.ajax({
        url: BASE_URL + '/single/users/id_user',
        method: 'POST',
        data: { 
            _token : csrf_token,
            id: id 
        },
        dataType: 'json',
        success: function (data) {
            $('input[name="id_user"]').val(data.id_user);
            $('input[name="nama"]').val(data.nama);
            $('input[name="nik"]').val(data.nik);
            $('input[name="username"]').val(data.username);
            $('select[name="id_departemen"]').val(data.id_departemen);
            $('select[name="id_departemen"]').trigger('change');
            $('#form_karyawan label.password').removeClass('required');
        }
    })
}

function tambah_data() {
    var form = document.getElementById('form_karyawan');
    form.setAttribute('action', BASE_URL + '/master/karyawan/insert');
    $('#title_modal').text(title[1]);
    $('#form_karyawan input[type="text"]').val('');
    $('#form_karyawan input[type="email"]').val('');
    $('#form_karyawan input[type="ttl"]').val('');
    $('#form_karyawan label.password').addClass('required');
    $('#form_karyawan textarea').val('');
    $('#form_karyawan select').val('');
    $('#form_karyawan select').trigger('change');
}




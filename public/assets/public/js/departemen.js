let departemenTable;
let currentFilterStatus = '';

document.addEventListener('DOMContentLoaded', function () {
    const tableElement = document.querySelector('#table_departemen');
    if (tableElement) {
        departemenTable = initGlobalDatatable('#table_departemen', function () {
            return {
                filter_status: currentFilterStatus
            };
        });
    }

    // Trigger reload on each filter
    document.querySelectorAll('.table-filter').forEach(el => {
        el.addEventListener('change', function () {
            if (departemenTable) departemenTable.ajax.reload();
        });
    });
});

// Trigger reload saat filter diubah
function filter_status(element) {
    currentFilterStatus = element.value;
    if (departemenTable) {
        departemenTable.ajax.reload();
    }
}

var title = $('#title_modal').data('title').split('|');

function ubah_data(element, id) {
    var form = document.getElementById('form_departemen');
    $('#title_modal').text(title[0]);
    form.setAttribute('action', BASE_URL + '/master/departemen/update');

    $.ajax({
        url: BASE_URL + '/master/departemen/get',
        method: "POST",
        data: { 
            _token : csrf_token,
            id: id 
        },
        dataType: 'json',
        success: function (data) {
            if (data.status === false) {
                alert(data.message);
            } else {
                $('input[name="id_departemen"]').val(data.id_departemen);
                $('input[name="kode"]').val(data.kode);
                $('input[name="nama"]').val(data.nama);
                $('input[name="warna"]').val(data.warna);
            }
        }
    })
}

// Fungsi hapus data
function hapus_data(id, table) {
    if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
        $.ajax({
            url: BASE_URL + '/master/departemen/delete',
            method: "POST",
            data: { 
                _token : csrf_token,
                id: id 
            },
            success: function (res) {
                if (res.status) {
                    alert(res.alert.message);
                    if (departemenTable) departemenTable.ajax.reload();
                } else {
                    alert(res.message ?? 'Gagal menghapus data');
                }
            }
        });
    }
}

function tambah_data() {
    var form = document.getElementById('form_departemen');
    form.setAttribute('action', BASE_URL + '/master/departemen/insert');
    $('#title_modal').text(title[1]);
    $('#form_departemen input[type="text"]').val('');
    $('#form_departemen input[type="email"]').val('');
    $('#form_departemen input[type="color"]').val('#00695C');
    $('#form_departemen label.password').addClass('required');
    $('#form_departemen textarea').val('');
}

// Fungsi submit form (insert/update)
function submit_form(button, formSelector) {
    let form = document.querySelector(formSelector);
    let url = form.getAttribute("action");
    let formData = new FormData(form);

    // Disable button biar ga double submit
    button.disabled = true;

    $.ajax({
        url: url,
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            button.disabled = false;

            if (res.status) {
                alert(res.alert.message);

                // Tutup modal
                let modal = bootstrap.Modal.getInstance(document.querySelector(res.modal.id));
                modal.hide();

                // Reset form
                form.reset();

                // Reload table
                if (departemenTable) departemenTable.ajax.reload();
            } else {
                alert(res.alert ? res.alert.message : "Terjadi kesalahan");
            }
        },
        error: function (xhr) {
            button.disabled = false;
            console.error(xhr.responseText);
            alert("Terjadi error saat mengirim data.");
        }
    });
}

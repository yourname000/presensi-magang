$(document).ready(function () {
    // === Inisialisasi DataTables TANPA AJAX (Client-Side) ===
    // DataTables akan bekerja pada semua baris HTML yang sudah dicetak oleh Blade.
    window.presensiTable = $('#table_presensi').DataTable({
        paging: true,
        searching: false, // Biarkan DataTables menangani pencarian global
        ordering: false,
        responsive: true,
        autoWidth: false,
        order: [[1, 'desc']], // Urutkan default berdasarkan kolom Tanggal (indeks 1) secara Descending
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(disaring dari total _MAX_ data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            },
            processing: "Memproses..."
        }
    });

    // ===============================================
    // 🚨 KOREKSI UTAMA: Ganti applyFilters() dengan Reload Halaman
    // Semua filter dropdown harus memicu Controller untuk memuat ulang data.
    // ===============================================
    $('.table-filter').on('change', function () {
        // Dapatkan nilai filter terbaru dari semua dropdown
        const departemen = $('#select_departemen').val() || '';
        const bulan = $('#select_bulan').val() || '';
        const tahun = $('#select_tahun').val() || '';
        const status = $('#select_status').val() || '';
        
        // Bangun URL baru
        let url = new URL(window.location.href);
        url.searchParams.set('departemen', departemen);
        url.searchParams.set('bulan', bulan);
        url.searchParams.set('tahun', tahun);
        url.searchParams.set('status', status);

        // Redirect ke URL baru untuk memicu controller memproses ulang data
        window.location.href = url.toString();
    });

    // === Event pencarian manual ===
    $('#btn_search').on('click', function () {
        const val = $('#search_table_presensi').val();
        // Memicu DataTables search pada data yang sudah ada di klien
        presensiTable.search(val).draw();
    });

    // Tekan enter di input pencarian
    $('#search_table_presensi').on('keyup', function (e) {
        if (e.key === 'Enter') {
            presensiTable.search(this.value).draw();
        }
    });

    // Hapus event change pada input search agar tidak konflik dengan #btn_search (optional)
    $('#search_table_presensi').off('change');

    // === Modal Presensi (Select2) ===
    $(function () {
        $('#kt_modal_presensi').on('shown.bs.modal', function () {
            $('#select_id_departemen, #select_id_shift, #select_status, #select_status_terlambat, #select_status_pulang_cepat')
                .select2({ dropdownParent: $('#form_presensi') });
        });
    });
});

// ===============================================
// 🚨 HAPUS FUNGSI applyFilters() DAN VARIABEL GLOBALNYA
// Karena filter dilakukan di server (Controller), fungsi ini tidak lagi diperlukan
// dan berpotensi menyebabkan bug karena mencoba memfilter data yang sudah di-filter.
// ===============================================
// Hapus:
// let currentFilterDepartemen = '';
// function applyFilters() { ... }
// ===============================================


// === Fungsi ubah data presensi ===
function ubah_data(id_presensi = null, id_user = null, tgl = null) {
    const form = document.getElementById('form_presensi');
    // ... (logic ubah_data lainnya tetap sama)
    $('#fake_tanggal_presensi').val(tgl);
    $('input[name="tanggal_presensi"]').val(tgl);
    $('input[name="id_user"]').val(id_user);
    $('input[name="id_presensi"]').val(id_presensi);
    $('#title_modal').text($('#title_modal').data('title').split('|')[0]);
    form.setAttribute('action', BASE_URL + '/presensi/update');
    
    // 🚨 Tambahkan pemanggilan AJAX untuk mengisi data presensi (jika ada)
    // Jika id_presensi null, reset form. Jika tidak, ambil data.
    if (id_presensi) {
        $.ajax({
            url: BASE_URL + '/presensi/single/' + id_presensi,
            type: 'GET',
            success: function(response) {
                 // Asumsi respons adalah HTML dari view 'presensi.single'
                 $('#kt_modal_presensi .modal-body').html(response);
            },
            error: function() {
                alert('Gagal mengambil data presensi.');
            }
        });
    } else {
        // Jika presensi baru (Alpha), panggil endpoint yang hanya butuh user/tanggal
        $.ajax({
            url: BASE_URL + '/presensi/single/null/' + id_user + '?tanggal=' + tgl,
            type: 'GET',
            success: function(response) {
                 $('#kt_modal_presensi .modal-body').html(response);
            },
            error: function() {
                alert('Gagal mengambil data user/tanggal.');
            }
        });
    }
}

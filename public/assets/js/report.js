$(document).ready(function () {
    // === Inisialisasi DataTables TANPA AJAX (Client-Side) ===
    // DataTables akan bekerja pada semua baris HTML yang sudah dicetak oleh Blade.
    window.presensiTable = $('#table_presensi').DataTable({
        paging: true,
        searching: false, // Biarkan DataTables menangani pencarian global
        ordering: false,
        lengthChange: false,
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


 // ===============================================
    // ✅ SISTEM CHECKBOX SELECT-ALL + HAPUS MASSAL
    // ===============================================
    const deleteBtn = $('#delete-selected-btn');         // Tombol "Hapus Terpilih"
    const selectAll = $('#checkAll');                    // Checkbox utama di header
    const itemCheckboxes = '.presensi-checkbox';         // Checkbox per baris
    const bulkDeleteForm = $('#bulk-delete-form');       // Form hapus massal

    // 🔸 Fungsi untuk menampilkan/sembunyikan tombol hapus
    const updateDeleteButtonVisibility = () => {
        const checkedCount = $(itemCheckboxes + ':checked').length;
        deleteBtn.toggle(checkedCount > 0);
    };

    // 🔸 Event saat checkbox baris berubah
    $('body').on('change', itemCheckboxes, function () {
        const checkedCount = $(itemCheckboxes + ':checked').length;
        const totalCount = presensiTable.rows({ page: 'current' }).nodes().to$().find(itemCheckboxes).length;

        selectAll.prop('checked', checkedCount === totalCount);
        updateDeleteButtonVisibility();
    });

    // 🔸 Event saat "Pilih Semua" diklik
    selectAll.on('change', function () {
        const isChecked = $(this).prop('checked');
        $(itemCheckboxes).prop('checked', isChecked);
        updateDeleteButtonVisibility();
    });

    // 🔸 Klik tombol hapus massal
    deleteBtn.on('click', function () {
        const selectedCount = $(itemCheckboxes + ':checked').length;
        if (selectedCount > 0 && confirm(`Yakin ingin menghapus ${selectedCount} data presensi terpilih?`)) {
            bulkDeleteForm.submit();
        }
    });

// 🔸 Jalankan saat halaman pertama kali dimuat
updateDeleteButtonVisibility();


/// === AUTO FILL MODAL EDIT PRESENSI ===
$(document).on('click', '.btn-edit-presensi', function () {
    const id = $(this).data('id');
    const modal = $(`#modalEditPresensi${id}`);

    // Ambil elemen input & select
    const $shiftSelect = modal.find('select[name="id_shift"]');
    const $scanIn = modal.find('input[name="scan_in"]');
    const $scanOut = modal.find('input[name="scan_out"]');
    const $terlambat = modal.find('input[name="waktu_terlambat"]');
    const $pulangCepat = modal.find('input[name="pulang_cepat"]');
    const $lembur = modal.find('input[name="lembur"]');

    // Fungsi bantu hitung selisih menit
    function hitungMenit(time1, time2) {
        const t1 = new Date(`2000-01-01T${time1}:00`);
        const t2 = new Date(`2000-01-01T${time2}:00`);
        return Math.abs((t2 - t1) / 60000);
    }

    // Fungsi hitung presensi (bisa jalan walau salah satu field belum diisi)
    function hitungPresensi() {
        const scan_in = $scanIn.val();
        const scan_out = $scanOut.val();
        const selectedOption = $shiftSelect.find(':selected');
        const jamMasukShift = selectedOption.data('jam-masuk');
        const jamPulangShift = selectedOption.data('jam-pulang');
        const batasLembur = selectedOption.data('lembur') || 0;

        // Reset dulu
        let menitTerlambat = 0;
        let menitPulangCepat = 0;
        let menitLembur = 0;

        // Hitung keterlambatan kalau scan_in sudah diisi
        if (scan_in && jamMasukShift) {
            if (scan_in > jamMasukShift) {
                menitTerlambat = hitungMenit(jamMasukShift, scan_in);
            }
        }

        // Hitung pulang cepat / lembur kalau scan_out sudah diisi
        if (scan_out && jamPulangShift) {
            if (scan_out < jamPulangShift) {
                menitPulangCepat = hitungMenit(scan_out, jamPulangShift);
            } else if (scan_out > jamPulangShift) {
                const totalLembur = hitungMenit(jamPulangShift, scan_out);
                if (totalLembur >= batasLembur) {
                    menitLembur = totalLembur;
                }
            }
        }

        // Masukkan hasil ke input
        $terlambat.val(menitTerlambat);
        $pulangCepat.val(menitPulangCepat);
        $lembur.val(menitLembur);
    }

    // Jalankan perhitungan kalau ada perubahan:
    $scanIn.on('change', hitungPresensi);
    $scanOut.on('change', hitungPresensi);
    $shiftSelect.on('change', hitungPresensi);
});

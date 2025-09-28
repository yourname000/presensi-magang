$(document).ready(function() {
    // Deklarasi Elemen
    const deleteBtn = $('#delete-selected-btn');
    const selectAll = $('#checkAll');
    const userCheckboxes = '.user-checkbox';
    const bulkDeleteForm = $('#bulk-delete-form');

    // 1. INISIALISASI DATATABLES
    const dataTable = $('#table_karyawan').DataTable({
        paging: true,
        searching: false,
        ordering: true,
        // Matikan sorting pada kolom checkbox (index 0) agar #checkAll bisa diklik
        columnDefs: [{ targets: 0, orderable: false }]
    });

    // Fungsi untuk mengupdate visibilitas tombol Hapus Terpilih
    const updateDeleteButtonVisibility = () => {
        const checkedCount = $(userCheckboxes + ':checked').length;
        deleteBtn.toggle(checkedCount > 0); // Mengganti if/else dengan toggle
    };

    // 2. Event Listener untuk Checkbox Data (Delegasi ke 'body')
    // Dipasang di 'body' agar tetap berfungsi saat DataTables refresh (paging/sorting)
    $('body').on('change', userCheckboxes, function() {
        const isChecked = $(this).prop('checked');
        const checkedCount = $(userCheckboxes + ':checked').length;
        const totalCount = dataTable.rows({ page: 'current' }).nodes().to$().find(userCheckboxes).length;

        // Atur status #checkAll
        selectAll.prop('checked', (checkedCount === totalCount));
        
        // Panggil update visibility
        updateDeleteButtonVisibility();
    });

    // 3. Event Listener untuk Checkbox 'Pilih Semua' (#checkAll)
    selectAll.on('change', function() {
        const isChecked = $(this).prop('checked');
        // Centang/uncheck semua checkbox data yang terlihat di halaman
        $(userCheckboxes).prop('checked', isChecked);
        updateDeleteButtonVisibility();
    });

    // 4. Konfirmasi dan Submit Form Hapus Terpilih
    deleteBtn.on('click', function() {
        if ($(userCheckboxes + ':checked').length > 0) {
            if (confirm('Apakah Anda yakin ingin menghapus data yang dipilih?')) {
                bulkDeleteForm.submit();
            }
        }
    });

    // Panggil saat halaman pertama kali dimuat
    updateDeleteButtonVisibility();
});
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.row-check');

    if (selectAll) {
        // Klik "select all"
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });

        // Kalau salah satu row di-uncheck, otomatis "select all" ikut mati
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                if (!this.checked) {
                    selectAll.checked = false;
                } else if (document.querySelectorAll('.row-check:checked').length === checkboxes.length) {
                    selectAll.checked = true;
                }
            });
        });
    }
});
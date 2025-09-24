let dashboardTable;

document.addEventListener('DOMContentLoaded', function () {
    dashboardTable = initGlobalDatatable('#table_dashboard');

    // Trigger reload on each filter
    document.querySelectorAll('.table-filter').forEach(el => {
        el.addEventListener('change', function () {
            if (dashboardTable) dashboardTable.ajax.reload();
        });
    });
});
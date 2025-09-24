window.initGlobalDatatable = function (selector, extraDataCallback = null) {
    const table = document.querySelector(selector);
    if (!table) return null; // table ga ketemu

    const ajaxUrl = table.getAttribute('data-url');
    if (!ajaxUrl) return null; // data-url kosong


    const hasNumbering = table.querySelector('thead th')?.textContent.trim().toLowerCase() === 'no';

    const columns = Array.from(table.querySelectorAll('thead th')).map((th, index) => {
        if (hasNumbering && index === 0) {
            return {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            };
        }

        return {
            data: hasNumbering ? index - 1 : index,
            orderable: th.getAttribute('data-orderable') !== 'false',
            searchable: th.getAttribute('data-searchable') !== 'false'
        };
    });



    const dataTable = $(table).DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: ajaxUrl,
            type: 'POST',
            data: function (d) {
                d._token = csrf_token;
                if (typeof extraDataCallback === 'function') {
                    Object.assign(d, extraDataCallback());
                }
            },
        },
        lengthMenu: [
            [5, 10, 25, 50, 100],
            [5, 10, 25, 50, 100]
        ],
        paging: true,
        searching: true,
        ordering: true,
        columns: columns,
    });

    // Optional: kalau kamu punya input pencarian global
    $('.search-datatable').on('keyup', debounce(function () {
        dataTable.search(this.value).draw();
    }, 500));

    return dataTable;
};

function debounce(func, wait, immediate) {
    let timeout;
    return function () {
        let context = this, args = arguments;
        let later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        let callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}
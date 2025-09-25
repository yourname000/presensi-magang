<!-- jQuery (required for Bootstrap JS and DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap Bundle (JS + Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>                                                    

<!-- DataTables (Bootstrap 5 integration) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Inisialisasi DataTables -->
<script>
    // DataTables untuk Departemen
    $(document).ready(function() {
        $('#table_departemen').DataTable({
            paging: true,
            searching: false,
            ordering: true
        });
    });

    // DataTables untuk Karyawan
    $(document).ready(function() {
        $('#table_karyawan').DataTable({
            paging: true,
            searching: false,
            ordering: true
        });
    });
</script>

<!-- Vis Timeline -->
<link href="https://cdn.jsdelivr.net/npm/vis-timeline@7.7.0/styles/vis-timeline-graph2d.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/vis-timeline@7.7.0/standalone/umd/vis-timeline-graph2d.min.js"></script>

<!-- Sidebar Toggle Script -->
<script>
    $(document).ready(function() {
        // Toggle sidebar
        $('#sidebarToggle').on('click', function() {
            $('#sidebar').toggleClass('active');
            $('#content-wrapper').toggleClass('active');
            $('.overlay').toggleClass('active');
        });

        // Close sidebar when clicking overlay (mobile)
        $('.overlay').on('click', function() {
            $('#sidebar').removeClass('active');
            $('#content-wrapper').removeClass('active');
            $('.overlay').removeClass('active');
        });

        // Auto open sidebar on desktop
        if ($(window).width() >= 768) {
            $('#sidebar').addClass('active');
            $('#content-wrapper').addClass('active');
        }

        // Handle window resize
        $(window).resize(function() {
            if ($(window).width() >= 768) {
                $('#sidebar').addClass('active');
                $('#content-wrapper').addClass('active');
                $('.overlay').removeClass('active');
            } else {
                $('#sidebar').removeClass('active');
                $('#content-wrapper').removeClass('active');
            }
        });

        // Prevent form submission on Enter key
        $(document).on('keypress', function(e) {
            if (e.keyCode === 13 || e.which === 13) {
                if (!$(e.target).is('textarea')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>

@stack('script')

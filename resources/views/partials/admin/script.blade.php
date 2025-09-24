<script>
    var BASE_URL = "{{ url('/') }}";
    var hostUrl = "{{ asset('assets/admin/') }}";
    var css_btn_confirm = 'btn btn-primary';
    var css_btn_cancel = 'btn btn-danger';
    var csrf_token = "{{ csrf_token() }}";
    var base_foto = "{{ image_check('notfound.jpg','default') }}";
    var user_base_foto = "{{ image_check('user.jpg','default') }}";
    var div_loading = '<div class="logo-spinner-parent">\
                    <div class="logo-spinner">\
                        <div class="logo-spinner-loader"></div>\
                    </div>\
                    <p id="text_loading">Tunggu sebentar...</p>\
                </div>';
</script>

<!-- jQuery (required for Bootstrap JS and DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap Bundle (JS + Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>                                                    

<!-- DataTables (Bootstrap 5 integration) -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

<!-- Custom Scripts -->
<script src="{{ asset('assets/public/js/mekanik.js') }}"></script>
<script src="{{ asset('assets/public/js/function.js') }}"></script>
<script src="{{ asset('assets/public/js/global.js') }}"></script>
<script src="{{ asset('assets/public/js/custom-datatable.js') }}"></script>

@stack('script')
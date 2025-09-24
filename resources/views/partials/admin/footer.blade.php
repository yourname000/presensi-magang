<!--begin::Footer-->
<footer id="footer" class="py-3 bg-white border-top" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000;">
    <div class="container-fluid">
        <div class="text-center text-muted">
            <span class="fw-semibold me-1">2025 &copy;</span>
            <a href="{{ url('/') }}" target="_blank" class="text-decoration-none text-dark fw-bold">
                {{ $setting->meta_title ?? 'Dashboard' }} 
                @if(isset($setting->meta_author))
                    By {{ $setting->meta_author }}
                @endif
            </a>
        </div>
    </div>
</footer>
<!--end::Footer-->
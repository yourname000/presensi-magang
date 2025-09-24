<!--CARD NAMA MENU ADMIN (EXP:DATA DEPARTEMEN)-->

@if(isset($title))
<div class="container-fluid p-0">
    <div style="
        background: linear-gradient(90deg,#26A69A 0%, #113355 50%, #00695C 100%); 
        margin: 0;
        padding: 20px;
    ">
        <div class="text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
                @if(isset($icon))
                <span class="me-3 text-white fs-1">{!! $icon !!}</span>
                @endif
                <h3 class="text-white fs-2 fw-bold m-0">{{ ucwords($title) }}</h3>
            </div>
            @if(isset($subtitle))
            <p class="text-white fs-6 mb-0">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
</div>
@endif
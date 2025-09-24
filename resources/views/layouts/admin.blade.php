@php
    $segment1 = request()->segment(1);
    $segment2 = request()->segment(2);
@endphp
<!DOCTYPE html>
<html lang="en">
    @include('partials.admin.head')
    <!--begin::Body-->
    <body class="d-flex flex-column min-vh-100">
        <!--begin::Wrapper-->
        <div class="d-flex" style="min-height: 100vh;">
            @include('partials.admin.sidebar')

            <!--begin::Content wrapper-->
            <div id="content-wrapper" class="d-flex flex-column flex-grow-1">
                @include('partials.admin.header')

                <!--begin::Main content-->
                <main class="flex-grow-1" style="padding-top: 0; margin-top: 0;">
                    @yield('content')
                </main>
                <!--end::Main content-->

                @include('partials.admin.footer')
            </div>
            <!--end::Content wrapper-->
        </div>
        <!--end::Wrapper-->

        @include('partials.global.modal_embed')
        @include('partials.global.loading')
        @include('partials.admin.script')
    </body>
    <!--end::Body-->
</html>
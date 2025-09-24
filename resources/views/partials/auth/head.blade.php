<!--begin::Head-->
<head>
     @if(isset($setting->icon) && $setting->icon)
    <link rel="shortcut icon" href="{{ image_check($setting->icon,'setting') }}" />
    @endif
    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Bootstrap CSS-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--end::Bootstrap CSS-->

    <style>
        body{
            background: linear-gradient(
                120deg,
                #113355 0%,    /* biru tua */
                #113355 20%,   /* biru tua diperluas */
                #1d4d66 35%,   /* transisi biru ke teal (lebih smooth) */
                #26A69A 55%,   /* teal terang di tengah */
                #1b7267 75%,   /* transisi teal ke hijau tua */
                #00695C 100%   /* hijau tua */
            );
        }
    </style>
</head>
<!--end::Head-->
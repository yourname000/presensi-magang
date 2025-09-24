<!--begin::Head-->
<head>
    <base href="{{ url('/') }}"/>
    <title>{{ (isset($setting->meta_title)) ? ucwords($setting->meta_title) : '' }}{{ (isset($title)) ? ' | '.$title : '' }}</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    @if(isset($setting->meta_description) && $setting->meta_description)
    <meta name="description" content="{{ $setting->meta_description }}" />
    @endif
    @if(isset($setting->meta_keyword) && $setting->meta_keyword)
    <meta name="keywords" content="{{ $setting->meta_keyword }}" />
    @endif
    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700">
    <!--end::Fonts-->

     <!-- UNTUK SEO -->
    @if(isset($setting->icon) && $setting->icon)
    <link rel="shortcut icon" href="{{ image_check($setting->icon,'setting') }}" />
    @endif

    <!-- Bootstrap (CSS + JS) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!--end::Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <!-- <link href="{{ asset('assets/public/css/custom_pribadi.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/public/css/loading_custom.css') }}" rel="stylesheet" type="text/css" /> -->
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<!-- Custom CSS -->

<style>
        /* Layout Styles */
        body {
            background-color: #f5f5f5;
        }

        /* Sidebar Styles */
        #sidebar {
            min-height: 100vh;
            margin-left: -250px;
            transition: margin 0.3s;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            background: linear-gradient(180deg, #007A7A 0%, #006666 100%);
        }

        #sidebar.active {
            margin-left: 0;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        #sidebar .nav-link {
            padding: 10px 20px;
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        #sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #FF286B;
        }

        #sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.15);
            border-left-color: #FF286B;
        }

        #sidebar .sidebar-heading {
            padding: 10px 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 600;
            margin-top: 15px;
        }

        /* Content Wrapper */
        #content-wrapper {
            width: 100%;
            padding-left: 0;
            transition: padding-left 0.3s;
            position: relative;
            z-index: 1;
        }

        #content-wrapper.active {
            padding-left: 250px;
        }

        /* Header */
        #header {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            padding: 15px 20px;
            position: relative;
            z-index: 10;
        }

        /* Main content */
        main {
            padding: 0 !important;
            margin-top: 0 !important;
            position: relative;
            z-index: 5;
        }

        main .container-fluid {
            padding: 15px;
        }

        /* Overlay for mobile */
        .overlay {
            display: none;
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 1040;
        }

        .overlay.active {
            display: block;
        }

        /* Responsive */
        @media (min-width: 768px) {
            #sidebar {
                margin-left: 0;
            }
            
            #content-wrapper {
                padding-left: 250px;
            }
            
            #sidebarToggle {
                display: none;
            }
        }

        @media (max-width: 767px) {
            #content-wrapper.active {
                padding-left: 0;
            }
        }

        /* Custom Styles */
        .cursor-pointer {
            cursor: pointer !important;
        }

        .cursor-disabled {
            cursor: not-allowed !important;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }

        /* Footer */
        #footer {
            background: #fff;
            margin-top: auto;
            border-top: 1px solid #e5e5e5;
        }
        main {
            padding-bottom: 80px !important; /* Beri ruang untuk footer fixed */
        }
</style>
    @stack('styles')
</head>
<!--end::Head-->
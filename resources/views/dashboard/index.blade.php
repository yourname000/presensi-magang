@extends('layouts.admin')

@push('styles')
@endpush

@push('script')
<script src="{{ asset('assets/admin/js/dashboard.js') }}"></script>
@endpush

@section('content')
<!--begin::Container-->
<div class="container-fluid p-0">
    <!--begin::Row-->
    @include('partials.admin.heading')

    <div class="container-fluid mt-4">
        <div class="row g-3">
        </div>
    </div>
</div>
<!--end::Container-->
@endsection
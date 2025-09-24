
<!DOCTYPE html>
<html lang="en">
	@include('partials.auth.head')
	<!--begin::Body-->
	<body class="bg-light">
		<div class="d-flex justify-content-center align-items-center min-vh-100">
                @yield('content')
			</div>
		</div>
	</body>
	<!--end::Body-->
</html>
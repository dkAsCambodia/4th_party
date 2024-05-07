
<!DOCTYPE html>
<html lang="en">
<head>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="keywords" content="admin, dashboard">
        <meta name="author" content="Soeng Souy">
        <meta name="robots" content="index, follow">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Payment Gateway">
        <meta property="og:title" content="Payment Gateway">
        <meta property="og:description" content="Payment Gateway">
        <meta property="og:image" content="{{ URL::to('assets/images/logo.png') }}">
        <meta name="format-detection" content="telephone=no">
        <!-- PAGE TITLE HERE -->
        <title>{{ trans('messages.PAYMENT GATEWAY') }}</title>
        <!-- FAVICONS ICON -->
        <link rel="shortcut icon" type="image/png" href="{{ URL::to('assets/images/favicon.png') }}">
        <link href="{{ URL::to('assets/css/style.css') }}" rel="stylesheet">
        {{-- message toastr --}}
        <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
        <script src="{{ URL::to('assets/js/toastr_jquery.min.js') }}"></script>
        <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>
    </head>
    <body class="vh-100">
        <style>
            .invalid-feedback{
                font-size: 14px;
            }
        </style>
        <div class="authincation h-100">
            <div class="container h-100">
                <!-- Main Wrapper -->
                @yield('content')
                <!-- /Main Wrapper -->
            </div>
        </div>
    <!-- Required vendors -->
    <script src="{{ URL::to('assets/vendor/global/global.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/custom.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/dlabnav-init.js') }}"></script>
    {{-- <script src="{{ URL::to('assets/js/styleSwitcher.js') }}"></script> --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
    </script>
    @yield('script')
</body>
</html>

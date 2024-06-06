<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title')</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Ionicons -->
    <link rel="stylesheet" href="{{ asset('css/ionicons.min.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> -->
    <link href={{ asset('fonts/fonts.css') }} rel='stylesheet'>
    {{-- <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet"> --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @yield('css')
</head>

<body class="hold-transition login-page mx-5 md:mx-20">
    <div class=" fixed w-4/5 h-4/5 saturate-0 sepia-[.4] bg-gray-600 p-5 m-5 rounded-xl shadow-[2px_2px_5px] bg-cover bg-center filter blur-[2px] "
        style="background-image: url({{ asset('images/bg.jpg') }});">
    </div>
    <div
        class="login-box flex flex-col md:flex-row justify-center justify-items-center align-middle items-center w-full p-0 m-5 md:p-20 md:m-20">

        <div
            class="login-logo  hidden md:flex md:w-1/2 hover:shadow-yellow-500 justify-center justify-items-center align-middle items-center">
            <img src="{{ asset('images/qcl watermark.png') }}" alt="AdminLTE Logo"
                class="img rounded-3xl shadow  border-8 border-solid border-sky-950 hover:bg-blue-950 bg-opacity-35 hover:bg-opacity-90 bg-gray-900 max-w-80 max-h-80 filter hover:grayscale-0 grayscale-0 hover:shadow-none transition-all duration-500" />
        </div>
        <!-- /.login-logo -->
        <div class="card md:w-1/2 max-w-96 max-h-96">
            <div href="../../index2.html" class="text-lg font-bold card-header text-center">{{ config('app.name') }}
            </div>
            <div class="card-body login-card-body">
                @yield('content')
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <!-- <script src="{{ asset('js/app.js') }}"></script> -->

    @yield('js')

</body>

</html>

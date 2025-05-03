@extends('layouts.auth')

@section('css')
    <style>
        .invalid-feedback {
            display: block
        }
    </style>
@endsection

@section('content')
    {{-- <p class="login-box-msg">Sign in to start your session</p> --}}
    <form action="{{ route('login') }}" method="post" class="">
        @csrf
        <div class="form-group">
            @if (env('DEMO_MODE') == true)
                <div class="alert alert-info alert-dismissible flex  items-center">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-info-circle rounded-full bg-blue-100 p-2 text-blue-600"></i>
                    {{-- <span class="text-sm font-semibold">Demo Mode</span> --}}
                    <div>
                        Username: <strong>admin@wt.pos</strong><br>
                        Password: <strong>admin123</strong>
                    </div>
                </div>
            @endif
            <div class="input-group">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    placeholder="Email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope"></span>
                    </div>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">

            <div class="input-group">
                <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password"
                    name="password" required autocomplete="current-password">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="col p-0 m-0">
            <div class="">
                <div class="px-2 accent-emerald-500">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">
                        Remember Me
                    </label>
                </div>
            </div>
            <!-- /.col -->
            <button type="submit"
                class=" border-2 border-sky-500  w-full text-center rounded-md min-h-10 hover:shadow-slate-500 hover:shadow-sm hover:font-bold transition-all duration-50 px-0 mx-0">Login</button>
            <!-- /.col -->
        </div>
    </form>
    {{--
    <p class="mb-1">
        <a href="{{ route('password.request') }}">I forgot my password</a>
    </p>
    <p class="mb-0">
        <a href="{{ route('register') }}" class="text-center">Register a new membership</a>
    </p> --}}
@endsection

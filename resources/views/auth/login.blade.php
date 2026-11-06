@extends('layouts.guest')

@section('title', 'Sign in')
@section('art', 'Doors open soon.')

@section('form')
    <h1>Sign in</h1>
    <p class="muted mb-4">New here? <a href="{{ route('register') }}" class="text-decoration-underline">Create an account</a></p>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" autocomplete="current-password" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" id="remember" type="checkbox" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a class="muted" href="{{ route('password.request') }}">Forgot password?</a>
        </div>
        <button class="btn btn-accent w-100" type="submit">Sign in</button>
    </form>
@endsection

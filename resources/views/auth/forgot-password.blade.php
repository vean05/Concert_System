@extends('layouts.guest')

@section('title', 'Reset password')

@section('form')
    <h1>Reset password</h1>
    <p class="muted mb-4">Enter your email and we will send you a link to choose a new password.</p>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-accent w-100" type="submit">Email reset link</button>
    </form>

    <a class="back-link mt-3 mb-0" href="{{ route('login') }}"><i class="fas fa-arrow-left"></i> Back to sign in</a>
@endsection

@extends('layouts.guest')

@section('title', 'Create account')
@section('art', 'Never miss the encore.')

@section('form')
    <h1>Create account</h1>
    <p class="muted mb-4">Already a member? <a href="{{ route('login') }}" class="text-decoration-underline">Sign in</a></p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="name">Name</label>
            <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" autocomplete="new-password" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Confirm password</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
        </div>
        <button class="btn btn-accent w-100" type="submit">Create account</button>
    </form>
@endsection

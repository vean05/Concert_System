@extends('layouts.guest')

@section('title', 'Choose a new password')

@section('form')
    <h1>New password</h1>
    <p class="muted mb-4">Choose a new password for your account.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email', $request->email) }}" autocomplete="username" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">New password</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" autocomplete="new-password" required autofocus>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Confirm new password</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
        </div>
        <button class="btn btn-accent w-100" type="submit">Reset password</button>
    </form>
@endsection

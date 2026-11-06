@extends('layouts.guest')

@section('title', 'Confirm password')

@section('form')
    <h1>Confirm password</h1>
    <p class="muted mb-4">This is a secure area. Please confirm your password to continue.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label" for="password">Password</label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" autocomplete="current-password" required autofocus>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-accent w-100" type="submit">Confirm</button>
    </form>
@endsection

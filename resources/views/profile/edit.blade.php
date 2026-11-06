@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
    <div class="page">
        <div class="container" style="max-width: 620px">
            <a class="back-link" href="{{ route('profile.show') }}"><i class="fas fa-arrow-left"></i> Back</a>
            <div class="page-head"><h1>Edit profile</h1></div>

            <form class="panel" action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <h3>Change password</h3>
                <p class="muted">Leave these empty to keep your current password.</p>
                <div class="mb-3">
                    <label class="form-label" for="current_password">Current password</label>
                    <input class="form-control @error('current_password') is-invalid @enderror" id="current_password" type="password" name="current_password" autocomplete="current-password">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">New password</label>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" autocomplete="new-password" minlength="8">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password_confirmation">Confirm new password</label>
                    <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                </div>

                <button class="btn btn-accent w-100" type="submit">Save changes</button>
            </form>
        </div>
    </div>
@endsection

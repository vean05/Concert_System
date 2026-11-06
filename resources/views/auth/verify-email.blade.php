@extends('layouts.guest')

@section('title', 'Verify email')

@section('form')
    <h1>Verify email</h1>
    <p class="muted mb-4">Click the link we emailed you to finish signing up. Need another one?</p>

    @if(session('status') === 'verification-link-sent')
        <div class="alert alert-success">A new verification link has been sent to your email address.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="btn btn-accent w-100" type="submit">Resend verification email</button>
    </form>
    <form class="mt-2" method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-ghost w-100" type="submit">Log out</button>
    </form>
@endsection

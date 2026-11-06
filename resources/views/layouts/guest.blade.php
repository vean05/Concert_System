@extends('layouts.app')

@section('content')
    <div class="auth-shell">
        <div class="auth-art">
            <img src="{{ asset(config('media.scenes.stage.photo') ?: 'images/hero-concert-bg.jpg') }}" alt="">
            <h2 class="shout">@yield('art', 'See it live.')</h2>
        </div>
        <div class="auth-form">
            <div>
                @yield('form')
            </div>
        </div>
    </div>
@endsection

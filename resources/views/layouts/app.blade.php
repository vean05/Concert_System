<!DOCTYPE html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <script>document.documentElement.classList.remove('no-js');</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Find & Book Concert Tickets') · ConcertHub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=Geist:wght@400..700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    @stack('head')
</head>
<body class="@yield('body_class')">
    @unless(View::hasSection('bare'))
    <nav class="navbar navbar-expand-lg site-nav" aria-label="Main">
        <div class="container">
            <a class="brand" href="{{ route('home') }}">Concert<span>Hub</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-controls="siteNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="siteNav">
                <form class="nav-search" action="{{ route('concerts.index') }}" method="GET" role="search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" name="q" value="{{ request()->routeIs('concerts.index') ? request('q') : '' }}" placeholder="Search artists or tours" aria-label="Search artists or tours">
                </form>
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('concerts.index', 'concerts.show') ? 'active' : '' }}" href="{{ route('concerts.index') }}">Concerts</a>
                    </li>
                    @auth
                        @if(auth()->user()->is_admin)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Admin</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('orders.index', 'orders.show') ? 'active' : '' }}" href="{{ route('orders.index') }}">My Tickets</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('concerts.favourites') ? 'active' : '' }}" href="{{ route('concerts.favourites') }}">Favourites</a>
                            </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @unless(auth()->user()->is_admin)
                                    <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profile</a></li>
                                    <li><a class="dropdown-item" href="{{ route('payment_cards.index') }}">Payment cards</a></li>
                                    <li><a class="dropdown-item" href="{{ route('profile.reviews') }}">My reviews</a></li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Account settings</a></li>
                                @endunless
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Log out</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Sign in</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-accent btn-sm" href="{{ route('register') }}">Sign up</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
    @endunless

    <main>
        @if(session('success') || session('error'))
            <div class="container pt-3 flash">
                <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} alert-dismissible fade show mb-0" role="alert">
                    {{ session('success') ?? session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @yield('content')

        <div class="mini-player" id="miniPlayer" hidden>
            <img id="miniArt" alt="">
            <div class="text-truncate">
                <strong id="miniTitle"></strong>
                <small id="miniArtist"></small>
            </div>
            <a id="miniLink" target="_blank" rel="noopener" aria-label="Open on Apple Music" title="Open on Apple Music"><i class="fab fa-apple"></i></a>
            <button type="button" id="miniToggle" aria-label="Pause"><i class="fas fa-pause"></i></button>
            <button type="button" id="miniClose" aria-label="Close player"><i class="fas fa-xmark"></i></button>
            <div class="mini-player__bar"><i id="miniBar"></i></div>
        </div>
    </main>

    @unless(View::hasSection('bare'))
        <footer class="site-footer">
            <div class="container d-flex flex-wrap justify-content-between gap-2">
                <span>&copy; {{ date('Y') }} ConcertHub</span>
                <span class="d-flex gap-3">
                    <a href="{{ route('concerts.index') }}">Browse concerts</a>
                    <a href="{{ route('credits') }}">Photo credits</a>
                </span>
            </div>
        </footer>
    @endunless

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/site.js') }}"></script>
    @stack('scripts')
</body>
</html>

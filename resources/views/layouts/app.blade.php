<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ConcertHub') - Find & Book Concert Tickets</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --secondary: #004e89;
            --dark: #1a1a1a;
            --light: #f8f9fa;
            --text-dark: #2c3e50;
            --accent: #00b4d8;
        }

        * {
            font-family: 'Poppins', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f0f4f8 50%, #e8eef7 100%);
            color: var(--text-dark);
            overflow-x: hidden;
            min-height: 100vh;
            position: relative;
        }

        /* 背景装饰 - Apple Store 风格 */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(124, 58, 237, 0.06) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 180, 216, 0.06) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        main {
            position: relative;
            z-index: 1;
        }

        /* Navbar Styling - 玻璃态效果 */
        .navbar {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 4px 20px 0 rgba(31, 38, 135, 0.08);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ff6b35 0%, #00b4d8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }

        .navbar-nav .nav-link {
            color: #2c3e50 !important;
            font-weight: 500;
            margin: 0 0.8rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transition: width 0.3s ease;
        }

        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 100%;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary) !important;
            transform: translateY(-2px);
        }

        /* Alert Messages - 高级效果 */
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #155724;
            border-radius: 16px;
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            font-weight: 500;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #721c24;
            border-radius: 16px;
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            font-weight: 500;
        }

        /* Premium Buttons */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            text-transform: none;
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        /* Primary Button */
        .btn-primary {
            background: linear-gradient(135deg, #ff6b35 0%, #ff8a50 100%);
            color: white;
            box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
            padding: 0.7rem 1.8rem;
            border-radius: 12px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #ff5a1a 0%, #ff7a3a 100%);
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(124, 58, 237, 0.45);
            color: white;
        }

        .btn-primary:active {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 107, 53, 0.3);
        }

        /* Auth Buttons - 高级样式 */
        .btn-auth {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.7rem 1.8rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-login {
            border: 2px solid rgba(124, 58, 237, 0.5);
            color: var(--primary);
            background: rgba(124, 58, 237, 0.05);
            backdrop-filter: blur(10px);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, var(--primary) 0%, #ff8a50 100%);
            color: white;
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(124, 58, 237, 0.35);
        }

        .btn-register {
            background: rgba(167, 139, 250, 0.15);
            color: #7c3aed;
            border: 2px solid rgba(124, 58, 237, 0.2);
            backdrop-filter: blur(10px);
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-register:hover {
            transform: translateY(-4px);
            background: linear-gradient(135deg, #7c3aed, #a78bfa) rgba(167, 139, 250, 0.25);
            border-color: rgba(124, 58, 237, 0.5);
            color: white;
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.3);
        }

        /* Secondary Button */
        .btn-secondary {
            background: rgba(100, 116, 139, 0.1);
            color: #0f172a;
            border: 1px solid rgba(100, 116, 139, 0.2);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(100, 116, 139, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            color: #0f172a;
        }

        /* Outline Button */
        .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Footer - 高级样式 */
        .footer {
            background: rgba(30, 30, 40, 0.8);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
            padding: 4rem 0 2rem;
            margin-top: 5rem;
        }

        .footer a {
            color: var(--accent);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .footer a:hover {
            color: #ff6b35;
            transform: translateX(2px);
        }

        /* 平滑的过渡效果 */
        .transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.3rem;
            }

            .btn-auth {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }

            .navbar-nav .nav-link {
                margin: 0.3rem 0;
            }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fas fa-music"></i> ConcertHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('concerts.index') }}">Browse Concerts</a>
                    </li>

                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('orders.index') }}">My Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile.show') }}">Profile</a>
                        </li>

                        @if(auth()->user()->is_admin)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}" style="color: #ec4899; font-weight: 600;">
                                    <i class="fas fa-cogs"></i> Admin
                                </a>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profile</a></li>
                                @if(auth()->user()->is_admin)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-cogs" style="margin-right: 0.5rem;"></i> Admin Panel</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn btn-login btn-auth me-2" href="{{ route('login') }}">Sign In</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-register btn-auth" href="{{ route('register') }}">Sign Up</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main>
        @if(session('success'))
            <div class="container mt-4">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mt-4">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <h5><i class="fas fa-music"></i> ConcertHub</h5>
                    <p>Your ultimate destination for concert tickets.</p>
                </div>
                <div class="col-md-3 mb-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('concerts.index') }}">Browse Concerts</a></li>
                        <li><a href="{{ route('home') }}">Home</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-3">
                    <h6>Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-3">
                    <h6>Follow Us</h6>
                    <a href="#" class="me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1)">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>&copy; 2024 ConcertHub. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

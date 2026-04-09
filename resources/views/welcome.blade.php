<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>ConcertHub - Find & Book Concert Tickets</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
            }

            html {
                scroll-behavior: smooth;
            }

            body {
                background: linear-gradient(135deg, #f8fafc 0%, #f0f4f8 50%, #e8eef7 100%);
                color: #2c3e50;
                overflow-x: hidden;
                min-height: 100vh;
                position: relative;
            }

            /* 背景动画装饰 */
            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: 
                    radial-gradient(circle at 20% 50%, rgba(236, 72, 153, 0.06) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(0, 180, 216, 0.06) 0%, transparent 50%);
                pointer-events: none;
                z-index: 0;
            }

            .relative {
                position: relative;
                z-index: 1;
            }

            .welcome-nav {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                box-shadow: 0 4px 20px 0 rgba(31, 38, 135, 0.08);
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
                padding: 1.2rem 2rem;
                position: sticky;
                top: 0;
                z-index: 1000;
            }

            .nav-container {
                max-width: 1200px;
                margin: 0 auto;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .nav-brand {
                font-size: 1.6rem;
                font-weight: 700;
                background: linear-gradient(135deg, #ec4899 0%, #00b4d8 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                letter-spacing: -0.5px;
                text-decoration: none;
            }

            .nav-links {
                display: flex;
                gap: 2rem;
                list-style: none;
            }

            .nav-links a {
                color: #2c3e50;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.3s ease;
                position: relative;
            }

            .nav-links a::after {
                content: '';
                position: absolute;
                bottom: -5px;
                left: 0;
                width: 0;
                height: 2px;
                background: linear-gradient(90deg, #ec4899, #00b4d8);
                transition: width 0.3s ease;
            }

            .nav-links a:hover {
                color: #ec4899;
            }

            .btn-nav {
                display: flex;
                gap: 1rem;
            }

            .btn-signin, .btn-signup {
                padding: 0.7rem 1.8rem;
                border-radius: 12px;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border: none;
                cursor: pointer;
            }

            .btn-signin {
                border: 2px solid rgba(236, 72, 153, 0.3);
                color: #ec4899;
                background: rgba(236, 72, 153, 0.08);
                backdrop-filter: blur(10px);
            }

            .btn-signin:hover {
                background: linear-gradient(135deg, #ec4899, #f472b6);
                color: white;
                transform: translateY(-4px);
                box-shadow: 0 12px 32px rgba(236, 72, 153, 0.25);
                border-color: #ec4899;
            }

            .btn-signup {
                background: linear-gradient(135deg, #ec4899, #00b4d8);
                color: white;
                box-shadow: 0 8px 24px rgba(236, 72, 153, 0.25);
            }

            .btn-signup:hover {
                transform: translateY(-4px);
                box-shadow: 0 16px 48px rgba(236, 72, 153, 0.35);
            }

            /* Hero Section */
            .hero {
                text-align: center;
                flex: 1;
                padding: 3rem 2rem;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }

            .hero h1 {
                font-size: 3.5rem;
                font-weight: 700;
                color: #1a1a2e;
                margin-bottom: 1.5rem;
                letter-spacing: -1px;
                line-height: 1.2;
            }

            .hero p {
                font-size: 1.2rem;
                color: #4a5568;
                margin-bottom: 2.5rem;
                max-width: 600px;
                line-height: 1.6;
            }

            .hero-buttons {
                display: flex;
                gap: 1.5rem;
                justify-content: center;
                flex-wrap: wrap;
            }

            .btn-primary {
                padding: 1rem 2.5rem;
                font-size: 1rem;
                border-radius: 12px;
                background: linear-gradient(135deg, #ec4899, #00b4d8);
                color: white;
                text-decoration: none;
                font-weight: 600;
                box-shadow: 0 8px 24px rgba(236, 72, 153, 0.25);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .btn-primary:hover {
                transform: translateY(-4px);
                box-shadow: 0 16px 48px rgba(236, 72, 153, 0.35);
                color: white;
            }

            .btn-secondary {
                padding: 1rem 2.5rem;
                font-size: 1rem;
                border-radius: 12px;
                background: rgba(100, 116, 139, 0.1);
                color: #2c3e50;
                text-decoration: none;
                font-weight: 600;
                border: 2px solid rgba(100, 116, 139, 0.2);
                backdrop-filter: blur(10px);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .btn-secondary:hover {
                background: rgba(100, 116, 139, 0.15);
                transform: translateY(-4px);
                box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
                border-color: rgba(100, 116, 139, 0.3);
                color: #2c3e50;
            }

            /* Main container */
            .main-container {
                max-width: 1200px;
                margin: 0 auto;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                position: relative;
                z-index: 1;
            }

            /* Features Section */
            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 2rem;
                margin: 4rem 2rem;
                position: relative;
                z-index: 1;
            }

            .feature-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border-radius: 20px;
                padding: 2.5rem 2rem;
                border: 1px solid rgba(100, 116, 139, 0.1);
                text-align: center;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08);
            }

            .feature-card:hover {
                transform: translateY(-12px) scale(1.02);
                box-shadow: 0 16px 40px rgba(31, 38, 135, 0.15);
                border-color: rgba(236, 72, 153, 0.3);
            }

            .feature-card i {
                font-size: 2.5rem;
                background: linear-gradient(135deg, #ec4899, #00b4d8);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                margin-bottom: 1rem;
            }

            .feature-card h3 {
                font-size: 1.2rem;
                color: #1a1a2e;
                margin-bottom: 0.8rem;
                font-weight: 700;
            }

            .feature-card p {
                color: #4a5568;
                line-height: 1.6;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .hero h1 {
                    font-size: 2.5rem;
                }

                .hero p {
                    font-size: 1rem;
                }

                .nav-links {
                    gap: 1rem;
                }

                .btn-nav {
                    flex-direction: column;
                    gap: 0.5rem;
                }

                .hero-buttons {
                    flex-direction: column;
                    gap: 1rem;
                }

                .btn-primary, .btn-secondary {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="welcome-nav">
            <div class="nav-container">
                <a href="/" class="nav-brand">
                    <i class="fas fa-music"></i> ConcertHub
                </a>
                <ul class="nav-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="{{ route('concerts.index') }}">Browse Events</a></li>
                </ul>
                <div class="btn-nav">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-signin">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-signin">Sign In</a>
                        <a href="{{ route('register') }}" class="btn-signup">Sign Up</a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-container">
            <div class="hero relative">
                <h1>Discover Your Next Favorite Concert</h1>
                <p>Browse thousands of live events, secure your tickets, and create unforgettable memories with ConcertHub.</p>
                <div class="hero-buttons">
                    <a href="{{ route('concerts.index') }}" class="btn-primary">
                        <i class="fas fa-play"></i> Explore Events
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn-secondary">
                            <i class="fas fa-user-plus"></i> Get Started
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </body>
</html>


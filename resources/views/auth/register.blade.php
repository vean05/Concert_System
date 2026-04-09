@extends('layouts.app')

@section('title', 'Create Account - ConcertHub')

@section('content')
<div style="min-height: calc(100vh - 200px); display: flex; align-items: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-radius: 20px; padding: 3rem; box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12); border: 1px solid rgba(100, 116, 139, 0.1);">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <h1 style="font-size: 2rem; font-weight: 700; color: #1a1a2e;">
                            <i class="fas fa-music" style="color: #ff6b35;"></i> Join ConcertHub
                        </h1>
                        <p style="color: #4a5568; margin-top: 0.5rem;">Create your account to start booking concerts</p>
                    </div>

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); color: #721c24; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
                            <i class="fas fa-exclamation-circle"></i>
                            <ul style="list-style: none; padding: 0; margin: 0; margin-top: 0.5rem;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Registration Form -->
                    <form method="POST" action="{{ route('register') }}" novalidate>
                        @csrf

                        <!-- Full Name -->
                        <div class="mb-4">
                            <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1a1a2e;">
                                <i class="fas fa-user" style="color: #ff6b35;"></i> Full Name
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="John Doe"
                                value="{{ old('name') }}"
                                required 
                                autofocus
                                style="padding: 0.85rem 1.2rem; border: 1.5px solid rgba(100, 116, 139, 0.2); border-radius: 12px; transition: all 0.3s ease; background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px);"
                                onfocus="this.style.borderColor='#ff6b35'; this.style.boxShadow='0 0 0 4px rgba(255,107,53,0.1)'; this.style.background='rgba(255, 255, 255, 0.8)';"
                                onblur="this.style.borderColor='rgba(100, 116, 139, 0.2)'; this.style.boxShadow='none'; this.style.background='rgba(255, 255, 255, 0.6)';"
                            >
                            @error('name')
                                <small class="text-danger d-block mt-2"><i class="fas fa-times-circle"></i> {{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1a1a2e;">
                                <i class="fas fa-envelope" style="color: #ff6b35;"></i> Email Address
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="your@email.com"
                                value="{{ old('email') }}"
                                required
                                style="padding: 0.85rem 1.2rem; border: 1.5px solid rgba(100, 116, 139, 0.2); border-radius: 12px; transition: all 0.3s ease; background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px);"
                                onfocus="this.style.borderColor='#ff6b35'; this.style.boxShadow='0 0 0 4px rgba(255,107,53,0.1)'; this.style.background='rgba(255, 255, 255, 0.8)';"
                                onblur="this.style.borderColor='rgba(100, 116, 139, 0.2)'; this.style.boxShadow='none'; this.style.background='rgba(255, 255, 255, 0.6)';"
                            >
                            @error('email')
                                <small class="text-danger d-block mt-2"><i class="fas fa-times-circle"></i> {{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1a1a2e;">
                                <i class="fas fa-lock" style="color: #ff6b35;"></i> Password
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="••••••••"
                                required
                                style="padding: 0.85rem 1.2rem; border: 1.5px solid rgba(100, 116, 139, 0.2); border-radius: 12px; transition: all 0.3s ease; background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px);"
                                onfocus="this.style.borderColor='#ff6b35'; this.style.boxShadow='0 0 0 4px rgba(255,107,53,0.1)'; this.style.background='rgba(255, 255, 255, 0.8)';"
                                onblur="this.style.borderColor='rgba(100, 116, 139, 0.2)'; this.style.boxShadow='none'; this.style.background='rgba(255, 255, 255, 0.6)';"
                            >
                            @error('password')
                                <small class="text-danger d-block mt-2"><i class="fas fa-times-circle"></i> {{ $message }}</small>
                            @enderror
                            <small style="color: #4a5568; display: block; margin-top: 0.5rem;">
                                <i class="fas fa-info-circle"></i> At least 8 characters recommended
                            </small>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #1a1a2e;">
                                <i class="fas fa-check-circle" style="color: #ff6b35;"></i> Confirm Password
                            </label>
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                placeholder="••••••••"
                                required
                                style="padding: 0.85rem 1.2rem; border: 1.5px solid rgba(100, 116, 139, 0.2); border-radius: 12px; transition: all 0.3s ease; background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px);"
                                onfocus="this.style.borderColor='#ff6b35'; this.style.boxShadow='0 0 0 4px rgba(255,107,53,0.1)'; this.style.background='rgba(255, 255, 255, 0.8)';"
                                onblur="this.style.borderColor='rgba(100, 116, 139, 0.2)'; this.style.boxShadow='none'; this.style.background='rgba(255, 255, 255, 0.6)';"
                            >
                            @error('password_confirmation')
                                <small class="text-danger d-block mt-2"><i class="fas fa-times-circle"></i> {{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Terms Checkbox -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="terms" 
                                    required
                                    style="cursor: pointer;"
                                >
                                <label class="form-check-label" for="terms" style="cursor: pointer; color: #4a5568;">
                                    I agree to the <a href="#" style="color: #ff6b35; text-decoration: none; font-weight: 600;">Terms & Conditions</a>
                                </label>
                            </div>
                        </div>

                        <!-- Sign Up Button -->
                        <button 
                            type="submit" 
                            class="btn btn-primary w-100"
                            style="background: linear-gradient(135deg, #7c3aed 0%, #00b4d8 100%); border: none; color: white; font-weight: 600; padding: 0.85rem; border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 15px rgba(124, 58, 237, 0.25);"
                            onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 30px rgba(124, 58, 237, 0.35)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(124, 58, 237, 0.25)';"
                        >
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </form>

                    <!-- Sign In Link -->
                    <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(100, 116, 139, 0.1);">
                        <p style="color: #4a5568;">Already have an account?</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary w-100" style="border: 2px solid rgba(255, 107, 53, 0.3); color: #ff6b35; font-weight: 600; margin-top: 0.5rem; border-radius: 12px; padding: 0.85rem; background: rgba(255, 107, 53, 0.05); transition: all 0.3s ease;" onmouseover="this.style.background='linear-gradient(135deg, #ff6b35, #ff8a50)'; this.style.color='white'; this.style.borderColor='#ff6b35'; this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 24px rgba(255, 107, 53, 0.3)';" onmouseout="this.style.background='rgba(255, 107, 53, 0.05)'; this.style.color='#ff6b35'; this.style.borderColor='rgba(255, 107, 53, 0.3)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

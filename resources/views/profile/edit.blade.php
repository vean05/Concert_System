@extends('layouts.app')

@section('title', 'Edit Profile - ConcertHub')

@section('content')
<style>
    .edit-profile-container {
        padding: 2rem 0;
        max-width: 700px;
        margin: 0 auto;
    }

    .edit-header {
        margin-bottom: 3rem;
    }

    .edit-header h1 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 1.5rem;
        color: #5BA3C0;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .back-link:hover {
        transform: translateX(-5px);
        color: #4A8FA3;
    }

    .form-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section:last-child {
        margin-bottom: 0;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.95rem;
    }

    .form-group input {
        width: 100%;
        padding: 0.9rem 1.2rem;
        border: 1.5px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.6);
    }

    .form-group input:focus {
        outline: none;
        border-color: #17a2b8;
        box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.1);
        background: rgba(255, 255, 255, 0.9);
    }

    .form-group input.is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 0.4rem;
    }

    .form-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, #e0e0e0, transparent);
        margin: 2.5rem 0;
    }

    .form-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-submit {
        flex: 1;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(23, 162, 184, 0.35);
    }

    .btn-cancel {
        flex: 1;
        padding: 1rem 2rem;
        background: white;
        color: #6c757d;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn-cancel:hover {
        background: #f8f9fa;
        border-color: #6c757d;
        color: #2c3e50;
    }

    .success-alert {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        padding: 1.2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        border-left: 4px solid #28a745;
    }

    .info-text {
        font-size: 0.9rem;
        color: #666;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .edit-profile-container {
            padding: 1rem 0;
        }

        .form-card {
            padding: 1.5rem;
        }

        .form-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="edit-profile-container container">
    <a href="{{ route('profile.show') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Profile
    </a>

    <div class="edit-header">
        <h1><i class="fas fa-user-edit" style="color: #17a2b8;"></i> Edit Profile</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Oops! Something went wrong:</strong>
            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" class="form-card">
        @csrf
        @method('PUT')

        <!-- Personal Information Section -->
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-id-card"></i> Personal Information</h3>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $user->name) }}" 
                    required 
                    class="@error('name') is-invalid @enderror"
                    placeholder="Enter your full name"
                >
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email', $user->email) }}" 
                    required 
                    class="@error('email') is-invalid @enderror"
                    placeholder="Enter your email address"
                >
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <p class="info-text">We'll never share your email with anyone else.</p>
            </div>
        </div>

        <div class="form-divider"></div>

        <!-- Security Section -->
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-lock"></i> Security Settings</h3>

            <div class="form-group">
                <label for="password">New Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="@error('password') is-invalid @enderror"
                    placeholder="Leave blank to keep current password"
                >
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <p class="info-text">Minimum 8 characters. Leave blank if you don't want to change it.</p>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    placeholder="Confirm your new password"
                >
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-buttons">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <a href="{{ route('profile.show') }}" class="btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

@endsection

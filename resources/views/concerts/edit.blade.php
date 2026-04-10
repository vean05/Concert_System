@extends('layouts.app')

@section('title', 'Edit Concert - ConcertHub')

@section('content')
<style>
    .form-container {
        padding: 2rem 0;
    }

    .form-header {
        margin-bottom: 2rem;
    }

    .form-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .form-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
    }

    .form-group {
        margin-bottom: 2rem;
    }

    .form-group:last-of-type {
        margin-bottom: 0;
    }

    .form-label {
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
    }

    .form-label i {
        color: #7c3aed;
        margin-right: 0.5rem;
        font-size: 1.1rem;
    }

    .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 0.8rem 1rem;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
    }

    .form-control:focus {
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        outline: none;
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    .form-control.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 0.5rem;
        display: block;
    }

    .error-alert {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border: none;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .error-alert i {
        margin-right: 0.5rem;
    }

    .error-alert ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .error-alert li {
        margin-bottom: 0.3rem;
    }

    .error-alert li:last-child {
        margin-bottom: 0;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .form-row.full {
        grid-template-columns: 1fr;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #f0f0f0;
    }

    .btn-submit {
        flex: 1;
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
        border: none;
        padding: 1rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 152, 0, 0.3);
    }

    .btn-cancel {
        flex: 1;
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        border: none;
        padding: 1rem;
        border-radius: 8px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
    }

    .btn-cancel:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
        color: white;
        text-decoration: none;
    }

    .help-text {
        color: #666;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-header h1 {
            font-size: 1.6rem;
        }
    }
</style>

<div class="form-container container">
    <!-- Back Button -->
    <a href="javascript:history.back()" style="display: inline-block; margin-bottom: 1.5rem; color: #7c3aed; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <!-- Header -->
    <div class="form-header">
        <h1><i class="fas fa-edit" style="color: #7c3aed;"></i> Edit Concert</h1>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="error-alert">
            <i class="fas fa-exclamation-circle"></i>
            <strong>Please fix the following errors:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="form-card">
        <form action="{{ auth()->user()->is_admin ? route('admin.concerts.update', $concert) : route('concerts.update', $concert) }}" method="POST" novalidate enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Basic Info Section -->
            <fieldset style="margin-bottom: 2rem;">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading"></i> Concert Title
                        </label>
                        <input 
                            type="text" 
                            class="form-control @error('title') is-invalid @enderror" 
                            id="title" 
                            name="title" 
                            placeholder="e.g., Summer Music Festival 2024"
                            value="{{ old('title', $concert->title) }}" 
                            required
                        >
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="artist" class="form-label">
                            <i class="fas fa-music"></i> Artist Name
                        </label>
                        <input 
                            type="text" 
                            class="form-control @error('artist') is-invalid @enderror" 
                            id="artist" 
                            name="artist" 
                            placeholder="e.g., Taylor Swift"
                            value="{{ old('artist', $concert->artist) }}" 
                            required
                        >
                        @error('artist')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="venue" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Venue
                        </label>
                        <input 
                            type="text" 
                            class="form-control @error('venue') is-invalid @enderror" 
                            id="venue" 
                            name="venue" 
                            placeholder="e.g., Madison Square Garden"
                            value="{{ old('venue', $concert->venue) }}" 
                            required
                        >
                        @error('venue')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="date" class="form-label">
                            <i class="fas fa-calendar-alt"></i> Concert Date
                        </label>
                        <input 
                            type="date" 
                            class="form-control @error('date') is-invalid @enderror" 
                            id="date" 
                            name="date" 
                            value="{{ old('date', $concert->date) }}" 
                            required
                        >
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <!-- Pricing & Tickets Section -->
            <fieldset style="margin-bottom: 2rem;">
                <div class="form-row">
                    <div class="form-group">
                        <label for="ticket_price" class="form-label">
                            <i class="fas fa-dollar-sign"></i> Ticket Price
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            class="form-control @error('ticket_price') is-invalid @enderror" 
                            id="ticket_price" 
                            name="ticket_price" 
                            placeholder="e.g., 99.99"
                            value="{{ old('ticket_price', $concert->ticket_price) }}" 
                            required
                        >
                        <div class="help-text">Price per single ticket in USD</div>
                        @error('ticket_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="total_ticket" class="form-label">
                            <i class="fas fa-ticket-alt"></i> Total Tickets Available
                        </label>
                        <input 
                            type="number" 
                            class="form-control @error('total_ticket') is-invalid @enderror" 
                            id="total_ticket" 
                            name="total_ticket" 
                            placeholder="e.g., 500"
                            value="{{ old('total_ticket', $concert->total_ticket) }}" 
                            required
                        >
                        <div class="help-text">Total number of tickets available for sale</div>
                        @error('total_ticket')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <!-- Image Upload Section -->
            <div class="form-row full">
                <div class="form-group">
                    <label for="image" class="form-label">
                        <i class="fas fa-image"></i> Concert Image
                    </label>
                    @if($concert->image_path)
                        <div style="margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                            <p style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #666;"><strong>Current Image:</strong></p>
                            <img src="{{ asset('storage/' . $concert->image_path) }}" alt="Current concert image" style="max-width: 200px; border-radius: 8px;">
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: #999;">Upload a new image to replace it</p>
                        </div>
                    @endif
                    <input 
                        type="file" 
                        class="form-control @error('image') is-invalid @enderror" 
                        id="image" 
                        name="image" 
                        accept="image/*"
                    >
                    <div class="help-text">Upload a new concert poster or promotional image (JPEG, PNG, GIF - max 2MB)</div>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Description Section -->
            <div class="form-row full">
                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left"></i> Concert Description
                    </label>
                    <textarea 
                        class="form-control @error('description') is-invalid @enderror" 
                        id="description" 
                        name="description" 
                        rows="6" 
                        placeholder="Provide a detailed description of the concert, including lineup, highlights, and any special information for attendees..."
                        required
                    >{{ old('description', $concert->description) }}</textarea>
                    <div class="help-text">Give potential attendees important details about the concert</div>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Seating Areas Section -->
            <div class="form-row full">
                <div class="form-group">
                    <label for="seating_areas" class="form-label">
                        <i class="fas fa-chair"></i> Seating Areas (Optional)
                    </label>
                    <textarea 
                        class="form-control @error('seating_areas') is-invalid @enderror" 
                        id="seating_areas" 
                        name="seating_areas" 
                        rows="3" 
                        placeholder="e.g., VIP Front, Regular, Balcony"
                    >{{ old('seating_areas', implode(', ', json_decode($concert->seating_areas, true) ?? [])) }}</textarea>
                    <div class="help-text">Specify different seating areas/categories available for this concert (comma-separated)</div>
                    @error('seating_areas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Update Concert
                </button>
                <a href="{{ route('concerts.show', $concert) }}" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

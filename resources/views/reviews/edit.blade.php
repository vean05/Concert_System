@extends('layouts.app')

@section('title', 'Edit Review - ConcertHub')

@section('content')
<style>
    .review-form-container {
        padding: 2rem 0;
    }

    .review-header {
        margin-bottom: 2rem;
    }

    .review-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 0.5rem;
    }

    .concert-info {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
    }

    .concert-info-icon {
        font-size: 2.5rem;
        margin-right: 1.5rem;
    }

    .concert-info-text h5 {
        margin: 0 0 0.5rem 0;
    }

    .concert-info-text p {
        margin: 0;
        opacity: 0.95;
    }

    .review-form-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section:last-of-type {
        margin-bottom: 0;
    }

    .form-label {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }

    .form-label i {
        color: #7c3aed;
        margin-right: 0.5rem;
        font-size: 1.3rem;
    }

    .rating-container {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .rating-option {
        position: relative;
        display: flex;
        flex-direction: column-reverse;
        align-items: center;
        width: 80px;
    }

    .rating-option input {
        display: none;
    }

    .rating-option label {
        width: 100%;
        padding: 1rem;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 0.3rem;
        background: white;
    }

    .rating-option label i {
        color: #ffc107;
        font-size: 1.2rem;
    }

    .rating-option input:checked + label {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        border-color: #ff9800;
        color: white;
    }

    .rating-option input:checked + label i {
        color: white;
    }

    .rating-option label:hover {
        border-color: #ff6b35;
        background: #fff8f5;
    }

    .rating-value {
        font-size: 0.75rem;
        color: #666;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .textarea-wrapper {
        position: relative;
    }

    .form-control {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 1rem;
        transition: all 0.3s ease;
        font-family: 'Poppins', sans-serif;
    }

    .form-control:focus {
        border-color: #ff6b35;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        outline: none;
    }

    .char-count {
        color: #999;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    .char-count.warning {
        color: #ff9800;
    }

    .char-count.danger {
        color: #dc3545;
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

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-submit {
        flex: 1;
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
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

    @media (max-width: 768px) {
        .concert-info {
            flex-direction: column;
            text-align: center;
        }

        .concert-info-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .rating-container {
            flex-wrap: wrap;
        }

        .rating-option {
            width: calc(50% - 0.5rem);
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>

<div class="review-form-container container">
    <!-- Header -->
    <div class="review-header">
        <h1><i class="fas fa-edit" style="color: #7c3aed;"></i> Edit Your Review</h1>
    </div>

    <!-- Concert Info Banner -->
    <div class="concert-info">
        <div class="concert-info-icon">
            <i class="fas fa-microphone"></i>
        </div>
        <div class="concert-info-text">
            <h5>{{ $concert->title }}</h5>
            <p>
                <i class="fas fa-user-music"></i> {{ $concert->artist }} • 
                <i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($concert->date)->format('F d, Y') }}
            </p>
        </div>
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

    <!-- Review Form -->
    <div class="review-form-card">
        <form action="{{ route('reviews.update', $review) }}" method="POST" id="reviewForm">
            @csrf
            @method('PUT')

            <!-- Rating Section -->
            <div class="form-section">
                <label class="form-label">
                    <i class="fas fa-star"></i> Rate Your Experience (Required)
                </label>
                <div class="rating-container">
                    @for($i = 1; $i <= 5; $i++)
                        <div class="rating-option">
                            <input 
                                type="radio" 
                                name="rating" 
                                id="rating{{ $i }}" 
                                value="{{ $i }}" 
                                @if(old('rating', $review->rating) == $i) checked @endif
                                required
                            >
                            <label for="rating{{ $i }}">
                                @for($j = 0; $j < $i; $j++)
                                    <i class="fas fa-star"></i>
                                @endfor
                            </label>
                            <div class="rating-value">{{ $i }}/5</div>
                        </div>
                    @endfor
                </div>
                @error('rating')
                    <div style="color: #dc3545; font-size: 0.85rem; margin-top: 0.5rem;">
                        <i class="fas fa-times-circle"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Comment Section -->
            <div class="form-section">
                <label for="comment" class="form-label">
                    <i class="fas fa-comment-dots"></i> Share Your Thoughts
                </label>
                <div class="textarea-wrapper">
                    <textarea 
                        class="form-control @error('comment') is-invalid @enderror" 
                        id="comment" 
                        name="comment" 
                        rows="8"
                        placeholder="Tell us about your concert experience... What did you enjoy? Any suggestions?"
                        required
                        maxlength="1000"
                        oninput="updateCharCount()"
                    >{{ old('comment', $review->comment) }}</textarea>
                </div>
                <div class="char-count" id="charCount">
                    <span id="charLength">{{ strlen($review->comment) }}</span> / 1000 characters
                </div>
                @error('comment')
                    <div style="color: #dc3545; font-size: 0.85rem; margin-top: 0.5rem;">
                        <i class="fas fa-times-circle"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Update Review
                </button>
                <a href="{{ route('concerts.show', $concert) }}" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function updateCharCount() {
        const textarea = document.getElementById('comment');
        const charLength = document.getElementById('charLength');
        const charCount = document.getElementById('charCount');
        const length = textarea.value.length;
        
        charLength.textContent = length;
        
        if (length > 900) {
            charCount.classList.add('danger');
            charCount.classList.remove('warning');
        } else if (length > 750) {
            charCount.classList.add('warning');
            charCount.classList.remove('danger');
        } else {
            charCount.classList.remove('warning', 'danger');
        }
    }

    // Initialize char count on page load
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('comment');
        if (textarea.value) {
            updateCharCount();
        }
    });
</script>
@endsection

@extends('layouts.app')

@section('title', 'All Reviews - ConcertHub')

@section('content')
<style>
    .reviews-container {
        padding: 2rem 0;
    }

    .reviews-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .reviews-header h1 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0;
    }

    .btn-back-to-profile {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-back-to-profile:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
        color: white;
        text-decoration: none;
    }

    .review-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
        transition: all 0.3s ease;
        border-left: 5px solid #7c3aed;
        margin-bottom: 1.5rem;
    }

    .review-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .review-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .review-card-header h5 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }

    .review-card-header h5 a {
        color: #ff6b35;
        text-decoration: none;
    }

    .review-card-header h5 a:hover {
        text-decoration: underline;
    }

    .review-card-artist {
        color: #666;
        font-size: 0.9rem;
        margin: 0;
    }

    .review-card-body {
        padding: 1.5rem;
    }

    .review-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .review-rating i {
        color: #ffc107;
        font-size: 1.1rem;
    }

    .review-rating-value {
        color: #666;
        font-size: 0.9rem;
    }

    .review-comment {
        color: #555;
        line-height: 1.6;
        margin-bottom: 1rem;
        padding: 1rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        border-left: 3px solid #7c3aed;
    }

    .review-meta {
        color: #999;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }

    .review-meta .updated {
        color: #999;
    }

    .review-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .review-actions a,
    .review-actions button {
        padding: 0.6rem 0.8rem;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
    }

    .btn-edit-review {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
    }

    .btn-edit-review:hover {
        transform: scale(1.05);
        color: white;
        text-decoration: none;
    }

    .btn-delete-review {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .btn-delete-review:hover {
        transform: scale(1.05);
        color: white;
    }

    .btn-view-concert {
        background: linear-gradient(135deg, #004e89 0%, #0077b6 100%);
        color: white;
    }

    .btn-view-concert:hover {
        transform: scale(1.05);
        color: white;
        text-decoration: none;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .empty-state i {
        font-size: 3rem;
        color: #ffc107;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: #2c3e50;
        font-weight: 700;
    }

    .empty-state a {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        color: white;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        margin-top: 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .empty-state a:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.3);
        color: white;
        text-decoration: none;
    }

    .pagination {
        margin-top: 2rem;
    }

    .page-link {
        color: #ff6b35;
        border-color: #e0e0e0;
    }

    .page-link:hover {
        color: #d94a2a;
        background-color: #fff8f5;
    }

    .page-item.active .page-link {
        background-color: #7c3aed;
        border-color: #7c3aed;
    }

    @media (max-width: 768px) {
        .reviews-header {
            flex-direction: column;
            gap: 1rem;
        }

        .reviews-header h1 {
            font-size: 1.8rem;
        }

        .review-actions {
            flex-direction: column;
            gap: 0.3rem;
        }

        .review-actions a,
        .review-actions button {
            width: 100%;
        }
    }
</style>

<div class="reviews-container container">
    <!-- Header -->
    <div class="reviews-header">
        <h1><i class="fas fa-star" style="color: #ffc107;"></i> All Reviews</h1>
        <a href="{{ route('profile.show') }}" class="btn-back-to-profile">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>

    <!-- Reviews List -->
    @forelse($reviews as $review)
        <div class="review-card">
            <!-- Header -->
            <div class="review-card-header">
                <h5>
                    <a href="{{ route('concerts.show', $review->concert) }}">
                        {{ $review->concert->title }}
                    </a>
                </h5>
                <p class="review-card-artist">
                    <i class="fas fa-user-music"></i> {{ $review->concert->artist }}
                </p>
            </div>

            <!-- Body -->
            <div class="review-card-body">
                <!-- Rating -->
                <div class="review-rating">
                    @for($i = 0; $i < $review->rating; $i++)
                        <i class="fas fa-star"></i>
                    @endfor
                    <span class="review-rating-value">({{ $review->rating }}/5 Stars)</span>
                </div>

                <!-- Comment -->
                <div class="review-comment">
                    <p style="margin: 0;">"{{ $review->comment }}"</p>
                </div>

                <!-- Meta Info -->
                <div class="review-meta">
                    <i class="fas fa-clock"></i> Posted {{ $review->created_at->diffForHumans() }}
                    @if($review->updated_at->ne($review->created_at))
                        <br>
                        <i class="fas fa-edit updated"></i> Updated {{ $review->updated_at->diffForHumans() }}
                    @endif
                </div>

                <!-- Actions -->
                <div class="review-actions">
                    <a href="{{ route('reviews.edit', $review) }}" class="btn-edit-review">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete-review" onclick="return confirm('Are you sure you want to delete this review?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                    <a href="{{ route('concerts.show', $review->concert) }}" class="btn-view-concert">
                        <i class="fas fa-music"></i> Concert
                    </a>
                </div>
            </div>
        </div>

    @empty
        <div class="empty-state">
            <i class="fas fa-star"></i>
            <h3>No Reviews Yet</h3>
            <p>You haven't written any reviews. Book a concert ticket and share your experience!</p>
            <a href="{{ route('concerts.index') }}">
                <i class="fas fa-music"></i> Explore Concerts
            </a>
        </div>
    @endforelse

    <!-- Pagination -->
    @if($reviews->count() > 0)
        <div class="mt-5">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('title', 'Concert Reviews - ConcertHub')

@section('content')
<style>
    .page-container { padding: 2rem 0; }

    .back-link {
        display: inline-block;
        margin-bottom: 1.5rem;
        color: #5BA3C0;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .back-link:hover { transform: translateX(-5px); color: #4A8FA3; }

    .page-header { margin-bottom: 2rem; }
    .page-header h1 { font-size: 2rem; font-weight: 700; color: #1a1a2e; margin: 0; }

    .review-card {
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.6);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(31,38,135,0.10);
        border-left: 5px solid #5BA3C0;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    .review-card:hover { transform: translateY(-4px); box-shadow: 0 14px 35px rgba(31,38,135,0.15); }

    .review-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .reviewer-info { display: flex; align-items: center; gap: 0.6rem; }
    .reviewer-avatar {
        width: 38px; height: 38px;
        background: linear-gradient(135deg, #5BA3C0, #4A8FA3);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 0.9rem;
    }
    .reviewer-name { font-weight: 700; color: #2c3e50; font-size: 0.95rem; }
    .reviewer-time { color: #999; font-size: 0.8rem; }

    .concert-tag {
        background: rgba(91,163,192,0.12);
        color: #5BA3C0;
        border: 1px solid rgba(91,163,192,0.3);
        border-radius: 20px;
        padding: 0.3rem 0.9rem;
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    .concert-tag:hover { background: #5BA3C0; color: white; text-decoration: none; }

    .review-body { padding: 1.5rem; }

    .stars { color: #ffc107; font-size: 1rem; margin-bottom: 0.8rem; }
    .stars span { color: #999; font-size: 0.85rem; margin-left: 0.4rem; }

    .review-comment {
        color: #555;
        line-height: 1.7;
        padding: 1rem 1.2rem;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 8px;
        border-left: 3px solid #5BA3C0;
        font-style: italic;
    }

    .empty-state {
        text-align: center; padding: 4rem 2rem;
        background: white; border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .empty-state i { font-size: 3rem; color: #ffc107; margin-bottom: 1rem; display: block; }
    .empty-state h3 { color: #2c3e50; font-weight: 700; }
</style>

<div class="page-container container">
    <a href="{{ route('profile.show') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Profile
    </a>

    <div class="page-header">
        <h1><i class="fas fa-comments" style="color:#5BA3C0;"></i> Reviews on My Concerts</h1>
    </div>

    @forelse($reviews as $review)
        <div class="review-card">
            <!-- Header: reviewer + concert tag -->
            <div class="review-header">
                <div class="reviewer-info">
                    <div class="reviewer-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="reviewer-name">{{ $review->user->name ?? 'Unknown User' }}</div>
                        <div class="reviewer-time">
                            <i class="fas fa-clock"></i> {{ $review->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                <a href="{{ route('concerts.show', $review->concert) }}" class="concert-tag">
                    <i class="fas fa-music"></i> {{ $review->concert->title }}
                </a>
            </div>

            <!-- Body: rating + comment -->
            <div class="review-body">
                <div class="stars">
                    @for($i = 0; $i < $review->rating; $i++)
                        <i class="fas fa-star"></i>
                    @endfor
                    @for($i = $review->rating; $i < 5; $i++)
                        <i class="far fa-star"></i>
                    @endfor
                    <span>({{ $review->rating }}/5 Stars)</span>
                </div>
                <div class="review-comment">
                    "{{ $review->comment }}"
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fas fa-star"></i>
            <h3>No Reviews Yet</h3>
            <p>No users have reviewed your published concerts yet.</p>
        </div>
    @endforelse

    @if($reviews->count() > 0)
        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
@endsection

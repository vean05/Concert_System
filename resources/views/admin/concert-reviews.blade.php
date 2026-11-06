@extends('layouts.app')

@section('title', 'Reviews')

@section('content')
    <div class="page">
        <div class="container">
            <div class="page-head"><h1>Reviews</h1></div>
            @include('admin._nav')

            @if($reviews->isEmpty())
                <div class="empty"><p class="mb-0">No reviews yet.</p></div>
            @else
                <div class="panel">
                    @foreach($reviews as $review)
                        <article class="review">
                            <div class="d-flex justify-content-between gap-3">
                                <span>
                                    <a href="{{ route('admin.concerts.show', $review->concert) }}"><strong>{{ $review->concert->title }}</strong></a>
                                    <span class="muted">by {{ $review->user->name }}</span>
                                </span>
                                <span class="stars" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            </div>
                            <p class="mt-2 mb-1">{{ $review->comment }}</p>
                            <span class="small-label">{{ $review->created_at->format('d M Y') }}</span>
                        </article>
                    @endforeach
                </div>
                {{ $reviews->links() }}
            @endif
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'My Reviews')

@section('content')
    <div class="page">
        <div class="container" style="max-width: 860px">
            <div class="page-head"><h1>My reviews</h1></div>

            @if($reviews->isEmpty())
                <div class="empty">
                    <h3>No reviews yet</h3>
                    <p class="mb-0">Book a concert and share how it went.</p>
                </div>
            @else
                <div class="panel">
                    @foreach($reviews as $review)
                        <article class="review">
                            <div class="d-flex justify-content-between gap-3">
                                <a href="{{ route('concerts.show', $review->concert) }}"><strong>{{ $review->concert->title }}</strong></a>
                                <span class="stars" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            </div>
                            <p class="mt-2 mb-2">{{ $review->comment }}</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="small-label me-2">{{ $review->created_at->format('d M Y') }}</span>
                                <a class="btn btn-ghost btn-sm" href="{{ route('reviews.edit', $review) }}">Edit</a>
                                <form action="{{ route('reviews.destroy', $review) }}" method="POST" data-confirm="Delete this review?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger-ghost btn-sm" type="submit">Delete</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
                {{ $reviews->links() }}
            @endif
        </div>
    </div>
@endsection

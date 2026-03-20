@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>My Reviews</h1>
        </div>
        <div class="col-md-4">
            <a href="{{ route('profile.show') }}" class="btn btn-secondary float-end">Back to Profile</a>
        </div>
    </div>

    <div class="row">
        @forelse($reviews as $review)
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5><a href="{{ route('concerts.show', $review->concert) }}">{{ $review->concert->title }}</a></h5>
                                <small class="text-muted">By {{ $review->concert->artist }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            @for($i = 0; $i < $review->rating; $i++)
                                <span class="text-warning">★</span>
                            @endfor
                            <span class="ms-2 text-muted">({{ $review->rating }}/5)</span>
                        </div>
                        <p>{{ $review->comment }}</p>
                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                        
                        @if($review->updated_at->ne($review->created_at))
                            <br>
                            <small class="text-muted">Updated {{ $review->updated_at->diffForHumans() }}</small>
                        @endif

                        <div class="mt-3">
                            <a href="{{ route('reviews.edit', $review) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('reviews.destroy', $review) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this review?')">Delete</button>
                            </form>
                            <a href="{{ route('concerts.show', $review->concert) }}" class="btn btn-primary btn-sm">View Concert</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    You haven't written any reviews yet. <a href="{{ route('concerts.index') }}">Browse concerts</a> and book a ticket to write a review!
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($reviews->count() > 0)
        <div class="row mt-4">
            <div class="col-md-12">
                {{ $reviews->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit Review')

@section('content')
    <div class="page">
        <div class="container" style="max-width: 620px">
            <a class="back-link" href="{{ route('concerts.show', $concert) }}"><i class="fas fa-arrow-left"></i> Back to concert</a>
            <div class="page-head">
                <div>
                    <h1>Edit your review</h1>
                    <p>{{ $concert->title }}</p>
                </div>
            </div>

            <form class="panel" action="{{ route('reviews.update', $review) }}" method="POST">
                @csrf
                @method('PUT')
                @include('reviews._form', ['review' => $review])
                <button class="btn btn-accent w-100" type="submit">Save changes</button>
            </form>
        </div>
    </div>
@endsection

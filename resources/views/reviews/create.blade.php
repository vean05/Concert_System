@extends('layouts.app')

@section('title', 'Review ' . $concert->title)

@section('content')
    <div class="page">
        <div class="container" style="max-width: 620px">
            <a class="back-link" href="{{ route('concerts.show', $concert) }}"><i class="fas fa-arrow-left"></i> Back to concert</a>
            <div class="page-head">
                <div>
                    <h1>Write a review</h1>
                    <p>{{ $concert->title }}</p>
                </div>
            </div>

            <form class="panel" action="{{ route('reviews.store', $concert) }}" method="POST">
                @csrf
                @include('reviews._form')
                <button class="btn btn-accent w-100" type="submit">Post review</button>
            </form>
        </div>
    </div>
@endsection

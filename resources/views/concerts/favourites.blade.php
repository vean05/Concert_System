@extends('layouts.app')

@section('title', 'Favourites')

@section('content')
    <div class="page">
        <div class="container">
            <div class="page-head">
                <div>
                    <h1>Favourites</h1>
                    <p>Shows you are keeping an eye on</p>
                </div>
            </div>

            @if($concerts->isEmpty())
                <div class="empty">
                    <h3>No favourites yet</h3>
                    <p>Tap the heart on a concert to save it here.</p>
                    <a class="btn btn-accent" href="{{ route('concerts.index') }}">Browse concerts</a>
                </div>
            @else
                <div class="concert-grid">
                    @foreach($concerts as $concert)
                        <x-concert-card :concert="$concert" :favourited="in_array($concert->id, $favouriteIds)" />
                    @endforeach
                </div>
                {{ $concerts->links() }}
            @endif
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'My Tickets')

@section('content')
    <div class="page">
        <div class="container" style="max-width: 920px">
            <div class="page-head">
                <h1>My tickets</h1>
            </div>

            @forelse($orders as $order)
                @include('orders._row', ['order' => $order])
            @empty
                <div class="empty">
                    <h3>No tickets yet</h3>
                    <p>When you book a concert, your tickets will live here.</p>
                    <a class="btn btn-accent" href="{{ route('concerts.index') }}">Find a concert</a>
                </div>
            @endforelse

            {{ $orders->links() }}
        </div>
    </div>
@endsection

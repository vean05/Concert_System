@extends('layouts.app')

@section('title', 'Add Payment Card')

@section('content')
    <div class="page">
        <div class="container" style="max-width: 620px">
            <a class="back-link" href="{{ route('payment_cards.index') }}"><i class="fas fa-arrow-left"></i> Payment cards</a>
            <div class="page-head"><h1>Add a card</h1></div>

            <form class="panel" action="{{ route('payment_cards.store') }}" method="POST">
                @csrf
                @include('payment_cards._fields')
                <button class="btn btn-accent w-100 mt-3" type="submit">Save card</button>
            </form>
        </div>
    </div>
@endsection

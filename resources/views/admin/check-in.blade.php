@extends('layouts.app')

@section('title', 'Door')

@section('content')
    @php
        $scan = session('scan');
        $scanned = $scan && $scan['order_id'] ? \App\Models\Order::with('concert', 'user')->find($scan['order_id']) : null;
        $messages = [
            'admitted' => ['Let them in', 'door-result--ok'],
            'already_in' => ['Already used', 'door-result--bad'],
            'expired' => ['Code expired. Ask them to refresh the ticket', 'door-result--bad'],
            'cancelled' => ['This order was cancelled', 'door-result--bad'],
            'invalid' => ['Not a valid ticket', 'door-result--bad'],
        ];
    @endphp

    <div class="page">
        <div class="container">
            <div class="page-head">
                <div>
                    <h1>Door</h1>
                    <p>Scan or paste the code from a ticket. Codes change every 30 seconds, so a screenshot will not get in.</p>
                </div>
            </div>
            @include('admin._nav')

            <form class="door-form" action="{{ route('admin.check-in.store') }}" method="POST">
                @csrf
                <input class="form-control" type="text" name="code" placeholder="Ticket code" aria-label="Ticket code" autocomplete="off" autofocus required>
                <button class="btn btn-accent" type="submit">Check ticket</button>
            </form>

            @if($scan)
                <div class="door-result {{ $messages[$scan['status']][1] }}" role="status">
                    <strong>{{ $messages[$scan['status']][0] }}</strong>
                    @if($scanned)
                        <span>{{ $scanned->reference }} · {{ $scanned->quantity }} {{ \Illuminate\Support\Str::plural('ticket', $scanned->quantity) }} · {{ $scanned->user->name }} · {{ $scanned->concert->title }}</span>
                        @if($scan['status'] === 'already_in')
                            <span>First scanned at {{ $scanned->checked_in_at->format('H:i, d M') }}</span>
                        @endif
                    @endif
                </div>
            @endif

            <h2 class="h6 mt-4">Latest at the door</h2>
            @if($recent->isEmpty())
                <div class="empty"><p class="mb-0">Nobody has been checked in yet.</p></div>
            @else
                <div class="panel">
                    <ul class="order-facts mb-0">
                        @foreach($recent as $order)
                            <li>
                                <span class="text-body">{{ $order->reference }} · {{ $order->user->name }} · {{ $order->concert->title }}</span>
                                <span>{{ $order->checked_in_at->format('H:i, d M') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection

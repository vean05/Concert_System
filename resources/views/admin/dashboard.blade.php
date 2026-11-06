@extends('layouts.app')

@section('title', 'Admin')

@section('content')
    <div class="page">
        <div class="container">
            <div class="page-head">
                <h1>Admin</h1>
                <a class="btn btn-accent" href="{{ route('admin.concerts.create') }}"><i class="fas fa-plus"></i> New concert</a>
            </div>
            @include('admin._nav')

            <div class="stat-grid">
                <div class="stat"><strong>{{ number_format($stats['upcoming']) }}</strong><span class="small-label">Upcoming of {{ number_format($stats['concerts']) }} concerts</span></div>
                <div class="stat"><strong>{{ number_format($stats['tickets']) }}</strong><span class="small-label">Tickets sold</span></div>
                <div class="stat"><strong>${{ number_format($stats['revenue'], 2) }}</strong><span class="small-label">Revenue</span></div>
                <div class="stat"><strong>{{ number_format($stats['users']) }}</strong><span class="small-label">Users</span></div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <h2>Upcoming</h2>
                    @if($upcomingConcerts->isEmpty())
                        <div class="empty">
                            <p>No upcoming concerts are scheduled.</p>
                            <a class="btn btn-accent" href="{{ route('admin.concerts.create') }}">Add a concert</a>
                        </div>
                    @else
                        <div class="table-wrap">
                            <table class="data-table" style="min-width: 0">
                                <thead><tr><th>Concert</th><th>Date</th><th>Sold</th></tr></thead>
                                <tbody>
                                    @foreach($upcomingConcerts as $concert)
                                        <tr>
                                            <td><a href="{{ route('admin.concerts.show', $concert) }}">{{ $concert->title }}</a></td>
                                            <td class="mono">{{ $concert->date->format('d M Y') }}</td>
                                            <td class="mono">{{ number_format($concert->ticketsSold()) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="col-lg-6">
                    <h2>Latest orders</h2>
                    @if($recentOrders->isEmpty())
                        <div class="empty"><p class="mb-0">No orders yet.</p></div>
                    @else
                        <div class="table-wrap">
                            <table class="data-table" style="min-width: 0">
                                <thead><tr><th>Customer</th><th>Concert</th><th>Total</th></tr></thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td><a href="{{ route('admin.users.show', $order->user) }}">{{ $order->user->name }}</a></td>
                                            <td>{{ \Illuminate\Support\Str::limit($order->concert->title, 28) }}</td>
                                            <td class="mono">${{ number_format($order->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

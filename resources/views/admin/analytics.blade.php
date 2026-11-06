@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
    @php
        $maxMonthRevenue = max($months->max('revenue'), 1);
        $maxTickets = max($popularConcerts->max(function ($concert) { return $concert->ticketsSold(); }), 1);
        $maxReviews = max($reviewedConcerts->max('reviews_count'), 1);
    @endphp

    <div class="page">
        <div class="container">
            <div class="page-head"><h1>Analytics</h1></div>
            @include('admin._nav')

            <div class="stat-grid">
                <div class="stat"><strong>${{ number_format($stats['revenue'], 2) }}</strong><span class="small-label">Revenue</span></div>
                <div class="stat"><strong>{{ number_format($stats['tickets']) }}</strong><span class="small-label">Tickets sold</span></div>
                <div class="stat"><strong>{{ number_format($stats['concerts']) }}</strong><span class="small-label">Concerts</span></div>
                <div class="stat"><strong>{{ number_format($stats['users']) }}</strong><span class="small-label">Users</span></div>
                <div class="stat"><strong>{{ $stats['reviews'] ? $stats['average_rating'] : '–' }}</strong><span class="small-label">Avg rating, {{ $stats['reviews'] }} {{ \Illuminate\Support\Str::plural('review', $stats['reviews']) }}</span></div>
            </div>

            <section class="panel">
                <h2>Revenue, last 12 months</h2>
                <div class="bar-chart" role="img" aria-label="Monthly revenue for the last 12 months">
                    @foreach($months as $month)
                        <div title="{{ $month['label'] }}: ${{ number_format($month['revenue'], 2) }}, {{ $month['tickets'] }} tickets">
                            <i style="height: {{ round($month['revenue'] / $maxMonthRevenue * 88) }}%"></i>
                            <span>{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <section class="panel h-100">
                        <h2>Best sellers</h2>
                        @foreach($popularConcerts as $concert)
                            <div class="bar-row">
                                <a class="text-truncate" href="{{ route('admin.concerts.show', $concert) }}">{{ $concert->title }}</a>
                                <div class="meter"><span style="width: {{ round($concert->ticketsSold() / $maxTickets * 100) }}%"></span></div>
                                <span class="mono text-end">{{ number_format($concert->ticketsSold()) }}</span>
                            </div>
                        @endforeach
                    </section>
                </div>
                <div class="col-lg-6">
                    <section class="panel h-100">
                        <h2>Most reviewed</h2>
                        @foreach($reviewedConcerts as $concert)
                            <div class="bar-row">
                                <a class="text-truncate" href="{{ route('admin.concerts.show', $concert) }}">{{ $concert->title }}</a>
                                <div class="meter" style="--swatch: var(--accent)"><span style="width: {{ round($concert->reviews_count / $maxReviews * 100) }}%"></span></div>
                                <span class="mono text-end">{{ $concert->reviews_count }}</span>
                            </div>
                        @endforeach
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Manage Concerts')

@section('content')
    <div class="page">
        <div class="container">
            <div class="page-head">
                <h1>Concerts</h1>
                <a class="btn btn-accent" href="{{ route('admin.concerts.create') }}"><i class="fas fa-plus"></i> New concert</a>
            </div>
            @include('admin._nav')

            <form class="row g-2 mb-3" action="{{ route('admin.concerts.index') }}" method="GET" role="search">
                <div class="col-md-6">
                    <input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search title, artist or venue" aria-label="Search concerts">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="sort_by" aria-label="Sort by">
                        @foreach(['date' => 'Sort by date', 'title' => 'Sort by title', 'artist' => 'Sort by artist', 'venue' => 'Sort by venue', 'created_at' => 'Sort by date added'] as $value => $label)
                            <option value="{{ $value }}" {{ request('sort_by', 'date') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="sort_order" aria-label="Sort direction">
                        <option value="asc" {{ request('sort_order') !== 'desc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>
                <div class="col-md-1"><button class="btn btn-ghost w-100" type="submit">Go</button></div>
            </form>

            @if($concerts->isEmpty())
                <div class="empty">
                    <h3>No concerts found</h3>
                    <a class="btn btn-accent" href="{{ route('admin.concerts.create') }}">Add a concert</a>
                </div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th>Concert</th><th>Date</th><th>Venue</th><th>Sold</th><th>Reviews</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($concerts as $concert)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.concerts.show', $concert) }}"><strong>{{ $concert->title }}</strong></a>
                                        @unless($concert->is_published)
                                            <span class="status status--low">Hidden</span>
                                        @endunless
                                        <div class="muted">{{ $concert->artist }}</div>
                                    </td>
                                    <td class="mono">
                                        {{ $concert->date->format('d M Y') }}
                                        @unless($concert->isUpcoming())<div><span class="status">Ended</span></div>@endunless
                                    </td>
                                    <td>{{ $concert->venue }}</td>
                                    <td class="mono">{{ number_format($concert->ticketsSold()) }} / {{ number_format($concert->total_ticket) }}</td>
                                    <td class="mono">{{ $concert->reviews_count }}</td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn btn-ghost btn-sm" href="{{ route('admin.concerts.edit', $concert) }}">Edit</a>
                                            <form action="{{ route('admin.concerts.delete', $concert) }}" method="POST"
                                                  data-confirm="Delete {{ $concert->title }}? This also removes its orders and reviews.">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger-ghost btn-sm" type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $concerts->links() }}
            @endif
        </div>
    </div>
@endsection

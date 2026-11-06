@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="page">
        <div class="container">
            <div class="page-head"><h1>Users</h1></div>
            @include('admin._nav')

            <form class="d-flex gap-2 mb-3" style="max-width: 520px" action="{{ route('admin.users.index') }}" method="GET" role="search">
                <input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Search name or email" aria-label="Search users">
                <button class="btn btn-ghost" type="submit">Search</button>
            </form>

            @if($users->isEmpty())
                <div class="empty"><p class="mb-0">No users match your search.</p></div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Name</th><th>Email</th><th>Orders</th><th>Reviews</th><th>Favourites</th><th>Joined</th></tr></thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user) }}"><strong>{{ $user->name }}</strong></a>
                                        @if($user->is_admin)<span class="status status--low ms-1">Admin</span>@endif
                                    </td>
                                    <td class="muted">{{ $user->email }}</td>
                                    <td class="mono">{{ $user->orders_count }}</td>
                                    <td class="mono">{{ $user->reviews_count }}</td>
                                    <td class="mono">{{ $user->favourite_concerts_count }}</td>
                                    <td class="mono">{{ optional($user->created_at)->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $users->links() }}
            @endif
        </div>
    </div>
@endsection

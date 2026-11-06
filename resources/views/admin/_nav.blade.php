<nav class="admin-tabs" aria-label="Admin">
    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Overview</a>
    <a class="{{ request()->routeIs('admin.concerts.*') ? 'active' : '' }}" href="{{ route('admin.concerts.index') }}">Concerts</a>
    <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Users</a>
    <a class="{{ request()->routeIs('admin.concert_reviews') ? 'active' : '' }}" href="{{ route('admin.concert_reviews') }}">Reviews</a>
    <a class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}" href="{{ route('admin.analytics') }}">Analytics</a>
    <a class="{{ request()->routeIs('admin.check-in') ? 'active' : '' }}" href="{{ route('admin.check-in') }}">Door</a>
    <a class="{{ request()->routeIs('admin.promos.*') ? 'active' : '' }}" href="{{ route('admin.promos.index') }}">Promos</a>
</nav>

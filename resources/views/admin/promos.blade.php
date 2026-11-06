@extends('layouts.app')

@section('title', 'Promo codes')

@section('content')
    <div class="page">
        <div class="container">
            <div class="page-head">
                <div>
                    <h1>Promo codes</h1>
                    <p>Codes work at seat checkout, once per fan.</p>
                </div>
            </div>
            @include('admin._nav')

            <form class="panel promo-create" action="{{ route('admin.promos.store') }}" method="POST">
                @csrf
                <div>
                    <label class="form-label" for="code">Code</label>
                    <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="percent_off">Percent off</label>
                    <input class="form-control @error('percent_off') is-invalid @enderror" id="percent_off" name="percent_off" type="number" min="1" max="90" value="{{ old('percent_off', 10) }}" required>
                    @error('percent_off')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="max_uses">Maximum uses</label>
                    <input class="form-control @error('max_uses') is-invalid @enderror" id="max_uses" name="max_uses" type="number" min="1" value="{{ old('max_uses') }}" placeholder="No limit">
                    @error('max_uses')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="expires_at">Ends</label>
                    <input class="form-control @error('expires_at') is-invalid @enderror" id="expires_at" name="expires_at" type="date" value="{{ old('expires_at') }}">
                    @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-accent" type="submit">Add code</button>
            </form>

            @if($promos->isEmpty())
                <div class="empty mt-3"><p class="mb-0">No codes yet.</p></div>
            @else
                <div class="panel">
                    <ul class="order-facts mb-0">
                        @foreach($promos as $promo)
                            <li>
                                <div>
                                    <strong class="mono">{{ $promo->code }}</strong>
                                    <small class="muted d-block">
                                        {{ $promo->percent_off }}% off · used {{ number_format($promo->used_count) }}@if($promo->max_uses) of {{ number_format($promo->max_uses) }}@endif
                                        @if($promo->expires_at) · ends {{ $promo->expires_at->format('d M Y') }}@endif
                                    </small>
                                </div>
                                <form action="{{ route('admin.promos.toggle', $promo) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-ghost btn-sm" type="submit">{{ $promo->is_active ? 'Turn off' : 'Turn on' }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection

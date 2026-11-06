@extends('layouts.app')

@section('title', 'Photo and music credits')

@section('content')
    @php
        $groups = [
            'Artists' => config('media.artists'),
            'Venues' => config('media.venues'),
            'Scenes' => config('media.scenes'),
        ];
    @endphp

    <div class="page">
        <div class="container">
            <div class="page-head">
                <div>
                    <h1>Photo and music credits</h1>
                    <p>Photos are from Wikimedia Commons, resized for this site. Song and video previews are provided by Apple Music and stream from Apple.</p>
                </div>
            </div>

            @foreach($groups as $title => $items)
                <section class="panel">
                    <h2>{{ $title }}</h2>
                    <ul class="list-unstyled mb-0">
                        @foreach($items as $name => $item)
                            <li class="mb-1">
                                <strong>{{ $name }}</strong>:
                                {{ $item['credit']['author'] }},
                                <a href="{{ $item['credit']['source'] }}" target="_blank" rel="noopener">{{ $item['credit']['licence'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </div>
@endsection

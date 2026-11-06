<section class="passport" aria-labelledby="passportTitle">
    <div class="passport__head">
        <div>
            <h2 id="passportTitle">Concert passport</h2>
            <p>{{ $passport['shows'] }} {{ \Illuminate\Support\Str::plural('show', $passport['shows']) }} · {{ $passport['artists'] }} {{ \Illuminate\Support\Str::plural('artist', $passport['artists']) }} · {{ $passport['venues'] }} {{ \Illuminate\Support\Str::plural('venue', $passport['venues']) }}</p>
        </div>
        @if($passport['next'])
            @php $days = now()->startOfDay()->diffInDays($passport['next']['concert']->date->copy()->startOfDay()); @endphp
            <a class="passport__next" href="{{ route('concerts.show', $passport['next']['concert']) }}">
                <strong>{{ $days === 0 ? 'Today' : $days . ' ' . \Illuminate\Support\Str::plural('day', $days) }}</strong>
                <span>until {{ $passport['next']['concert']->artist }}</span>
            </a>
        @endif
    </div>

    <ul class="passport__stamps">
        @foreach($passport['stamps'] as $stamp)
            <li>
                <a class="stamp {{ $stamp['upcoming'] ? 'is-upcoming' : '' }}" href="{{ route('concerts.show', $stamp['concert']) }}">
                    @if($stamp['concert']->hero_url)
                        <img src="{{ $stamp['concert']->hero_url }}" alt="" loading="lazy">
                    @endif
                    <span class="stamp__tag">{{ $stamp['upcoming'] ? 'Upcoming' : 'Past' }}</span>
                    <strong>{{ $stamp['concert']->artist }}</strong>
                    <small>{{ $stamp['concert']->venue }}</small>
                    <small>{{ $stamp['concert']->date->format('d M Y') }} · {{ $stamp['tickets'] }} {{ \Illuminate\Support\Str::plural('ticket', $stamp['tickets']) }}</small>
                </a>
            </li>
        @endforeach
    </ul>
</section>

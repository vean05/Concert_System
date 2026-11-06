<ol class="steps" aria-label="Booking progress">
    @foreach(['Choose seats', 'Pay', 'Your tickets'] as $number => $step)
        <li class="{{ $number + 1 === $current ? 'is-current' : ($number + 1 < $current ? 'is-done' : '') }}" @if($number + 1 === $current) aria-current="step" @endif>
            <span>{{ $number + 1 }}</span> {{ $step }}
        </li>
    @endforeach
</ol>

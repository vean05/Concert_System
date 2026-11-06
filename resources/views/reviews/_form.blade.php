@php $rating = (int) old('rating', optional($review ?? null)->rating ?? 5); @endphp

<div class="mb-3">
    <label class="form-label" for="rating">Rating</label>
    <select class="form-select @error('rating') is-invalid @enderror" id="rating" name="rating" required>
        @foreach([5 => 'Unforgettable', 4 => 'Great', 3 => 'Good', 2 => 'Disappointing', 1 => 'Poor'] as $value => $label)
            <option value="{{ $value }}" {{ $rating === $value ? 'selected' : '' }}>{{ str_repeat('★', $value) }} {{ $label }}</option>
        @endforeach
    </select>
    @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label" for="comment">Your review</label>
    <textarea class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" rows="5" maxlength="1000" required>{{ old('comment', optional($review ?? null)->comment) }}</textarea>
    @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

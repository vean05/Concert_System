@php
    $sold = (int) ($section['sold'] ?? 0);
    $type = $section['type'] ?? 'standing';
    $locked = $locked ?? false;
@endphp

<div class="section-editor-row">
    <input type="hidden" name="sections[{{ $index }}][id]" value="{{ $section['id'] ?? '' }}">
    <div>
        <label class="small-label" for="section-name-{{ $index }}">Name</label>
        <input class="form-control" id="section-name-{{ $index }}" name="sections[{{ $index }}][name]" value="{{ $section['name'] ?? '' }}" maxlength="100" required>
    </div>
    <div>
        <label class="small-label" for="section-type-{{ $index }}">Kind</label>
        <select class="form-select" id="section-type-{{ $index }}" name="sections[{{ $index }}][type]">
            <option value="seated" {{ $type === 'seated' ? 'selected' : '' }}>Seated</option>
            @unless($locked)
                <option value="standing" {{ $type === 'standing' ? 'selected' : '' }}>Standing</option>
            @endunless
        </select>
    </div>
    <div>
        <label class="small-label" for="section-price-{{ $index }}">Price ($)</label>
        <input class="form-control mono" id="section-price-{{ $index }}" type="number" name="sections[{{ $index }}][price]" value="{{ $section['price'] ?? '' }}" min="0" step="0.01" required>
    </div>
    <div>
        <label class="small-label" for="section-capacity-{{ $index }}">Capacity{{ $sold ? " ({$sold} sold)" : '' }}</label>
        <input class="form-control mono" id="section-capacity-{{ $index }}" type="number" name="sections[{{ $index }}][capacity]" value="{{ $section['capacity'] ?? '' }}" min="{{ max(1, $sold) }}" required {{ $locked ? 'readonly' : '' }}>
    </div>
    @unless($locked)
        <div class="align-self-end">
            <button class="btn btn-danger-ghost" type="button" data-remove-section aria-label="Remove ticket type"><i class="fas fa-trash"></i></button>
        </div>
    @endunless
</div>

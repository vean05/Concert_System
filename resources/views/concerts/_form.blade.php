@php
    $concert = $concert ?? null;
    $sections = old('sections');

    if ($sections === null) {
        $sections = $concert
            ? $concert->sections->map(function ($section) {
                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'type' => $section->type,
                    'price' => $section->price,
                    'capacity' => $section->capacity,
                    'sold' => $section->soldCount(),
                ];
            })->all()
            : [['name' => 'General Admission', 'type' => 'standing', 'price' => '', 'capacity' => '']];
    }
@endphp

<div class="row g-4">
    <div class="col-lg-7">
        <section class="panel">
            <h2>Details</h2>
            <div class="mb-3">
                <label class="form-label" for="title">Concert title</label>
                <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', optional($concert)->title) }}" maxlength="255" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="tagline">Tagline</label>
                <input class="form-control @error('tagline') is-invalid @enderror" id="tagline" name="tagline" value="{{ old('tagline', optional($concert)->tagline) }}" maxlength="255">
                @error('tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-7 mb-3">
                    <label class="form-label" for="artist">Artist</label>
                    <input class="form-control @error('artist') is-invalid @enderror" id="artist" name="artist" value="{{ old('artist', optional($concert)->artist) }}" maxlength="255" required>
                    @error('artist')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label" for="genre">Genre</label>
                    <input class="form-control @error('genre') is-invalid @enderror" id="genre" name="genre" value="{{ old('genre', optional($concert)->genre) }}" maxlength="60">
                    @error('genre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-7 mb-3">
                    <label class="form-label" for="venue">Venue</label>
                    <input class="form-control @error('venue') is-invalid @enderror" id="venue" name="venue" value="{{ old('venue', optional($concert)->venue) }}" maxlength="255" required>
                    @error('venue')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label" for="city">City</label>
                    <input class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', optional($concert)->city) }}" maxlength="100">
                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="form-label" for="date">Date</label>
                    <input class="form-control @error('date') is-invalid @enderror" id="date" type="date" name="date" value="{{ old('date', $concert ? $concert->date->toDateString() : '') }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <label class="form-label" for="doors_time">Doors</label>
                    <input class="form-control @error('doors_time') is-invalid @enderror" id="doors_time" type="time" name="doors_time" value="{{ old('doors_time', optional($concert)->doors_time) }}">
                    @error('doors_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 col-6 mb-3">
                    <label class="form-label" for="start_time">Show starts</label>
                    <input class="form-control @error('start_time') is-invalid @enderror" id="start_time" type="time" name="start_time" value="{{ old('start_time', optional($concert)->start_time) }}">
                    @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="lineup">Line-up</label>
                <input class="form-control @error('lineup') is-invalid @enderror" id="lineup" name="lineup" value="{{ old('lineup', implode(', ', optional($concert)->lineup ?? [])) }}" maxlength="500" aria-describedby="lineupHelp">
                <div class="form-text muted" id="lineupHelp">Separate names with commas, headliner first.</div>
                @error('lineup')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-0">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="6" required>{{ old('description', optional($concert)->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel">
            <h2>Poster</h2>
            @if($concert && $concert->photo_url)
                <img class="w-100 rounded mb-3" src="{{ $concert->photo_url }}" alt="Current poster">
            @endif
            <label class="form-label" for="image">{{ $concert ? 'Replace image' : 'Upload image' }}</label>
            <input class="form-control @error('image') is-invalid @enderror" id="image" type="file" name="image" accept="image/jpeg,image/png,image/gif">
            <div class="form-text muted">JPG, PNG or GIF, up to 2 MB.</div>
            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </section>

        <section class="panel">
            <h2>Visibility</h2>
            <input type="hidden" name="is_published" value="0">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="is_published" name="is_published" value="1" {{ old('is_published', optional($concert)->is_published ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_published">Show on the public site</label>
            </div>
            <div class="form-text muted">Hidden concerts keep their orders and can be published again at any time.</div>
        </section>
    </div>

    <div class="col-12">
        <section class="panel">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                <h2 class="mb-0">Ticket types</h2>
                @unless($concert && $concert->hasSeatMap())
                    <button class="btn btn-ghost btn-sm" type="button" id="addSection"><i class="fas fa-plus"></i> Add ticket type</button>
                @endunless
            </div>
            @if($concert && $concert->hasSeatMap())
                <p class="muted">This concert uses the {{ $concert->venueDetails()['name'] }} seat map. Names and prices can change here; seats and capacity come from the map.</p>
            @else
                <p class="muted">Seated types get numbered seats automatically, {{ \App\Services\SeatingService::SEATS_PER_ROW }} to a row. Types with sold tickets cannot be removed or changed to another kind.</p>
            @endif

            @foreach($errors->get('sections*') as $messages)
                <div class="alert alert-danger py-2">{{ $messages[0] }}</div>
            @endforeach

            <div id="sectionRows">
                @foreach($sections as $index => $section)
                    @include('concerts._section_row', ['index' => $index, 'section' => $section, 'locked' => $concert && $concert->hasSeatMap()])
                @endforeach
            </div>

            <template id="sectionTemplate">
                @include('concerts._section_row', ['index' => '__INDEX__', 'section' => ['name' => '', 'type' => 'seated', 'price' => '', 'capacity' => '']])
            </template>
        </section>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const rows = document.getElementById('sectionRows');
    const template = document.getElementById('sectionTemplate').innerHTML;
    let nextIndex = rows.children.length + 1000;

    const addSection = document.getElementById('addSection');

    if (addSection) {
        addSection.addEventListener('click', function () {
            rows.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', nextIndex++));
        });
    }

    rows.addEventListener('click', function (event) {
        const remove = event.target.closest('[data-remove-section]');
        if (!remove) {
            return;
        }

        if (rows.querySelectorAll('.section-editor-row').length === 1) {
            alert('A concert needs at least one ticket type.');
            return;
        }

        remove.closest('.section-editor-row').remove();
    });
})();
</script>
@endpush

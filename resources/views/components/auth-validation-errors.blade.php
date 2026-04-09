@props(['errors'])

@if ($errors->any())
    <div {{ $attributes->merge(['style' => 'background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2); border-radius: 12px; padding: 1rem; margin-bottom: 1rem;']) }}>
        <div class="font-medium" style="color: #721c24;">
            {{ __('Whoops! Something went wrong.') }}
        </div>

        <ul class="mt-3 list-disc list-inside text-sm" style="color: #721c24;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

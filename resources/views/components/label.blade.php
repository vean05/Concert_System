@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm', 'style' => 'color: #1a1a2e;']) }}>
    {{ $value ?? $slot }}
</label>

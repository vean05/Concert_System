@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['style' => 'background: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.2); border-radius: 12px; padding: 1rem; margin-bottom: 1rem; font-medium; font-size: 0.875rem;', 'class' => 'text-green-700']) }}>
        {{ $status }}
    </div>
@endif

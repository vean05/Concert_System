<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-grayscale border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:shadow-lg active:shadow-md focus:outline-none transition ease-in-out duration-150', 'style' => 'background: linear-gradient(135deg, #5BA3C0, #4A8FA3);']) }}>
    {{ $slot }}
</button>

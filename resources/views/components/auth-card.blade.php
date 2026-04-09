<div style="min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding-top: 1.5rem; background: linear-gradient(135deg, #f8fafc 0%, #f0f4f8 50%, #e8eef7 100%);">
    <div>
        {{ $logo }}
    </div>

    <div style="width: 100%; max-width: 28rem; margin-top: 1.5rem; padding: 2rem 2rem; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); border: 1px solid rgba(100, 116, 139, 0.1); box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08); border-radius: 20px; overflow: hidden;">
        {{ $slot }}
    </div>
</div>

<x-app-layout>
    <x-slot name="header">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0;">
            <div>
                <h2 style="font-size: 2rem; font-weight: 700; color: #1a1a2e; margin: 0; letter-spacing: -0.5px;">
                    <i class="fas fa-home"></i> Dashboard
                </h2>
                <p style="color: #4a5568; font-size: 0.95rem; margin-top: 0.5rem;">
                    Welcome back! Here's your concert hub overview.
                </p>
            </div>
        </div>
    </x-slot>

    <div style="padding: 2rem 2rem; min-height: 100vh;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                <div style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); border: 1px solid rgba(100, 116, 139, 0.1); border-radius: 20px; padding: 2rem; box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: #4a5568; font-size: 0.9rem; margin: 0 0 0.5rem 0;">Total Orders</p>
                            <h3 style="font-size: 2.5rem; font-weight: 700; color: #1a1a2e; margin: 0;">0</h3>
                        </div>
                        <div style="font-size: 3rem; background: linear-gradient(135deg, #7c3aed, #00b4d8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                </div>

                <div style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); border: 1px solid rgba(100, 116, 139, 0.1); border-radius: 20px; padding: 2rem; box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: #4a5568; font-size: 0.9rem; margin: 0 0 0.5rem 0;">Saved Events</p>
                            <h3 style="font-size: 2.5rem; font-weight: 700; color: #1a1a2e; margin: 0;">0</h3>
                        </div>
                        <div style="font-size: 3rem; color: #7c3aed;">
                            <i class="fas fa-heart"></i>
                        </div>
                    </div>
                </div>

                <div style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); border: 1px solid rgba(100, 116, 139, 0.1); border-radius: 20px; padding: 2rem; box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08); transition: all 0.3s ease;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <p style="color: #4a5568; font-size: 0.9rem; margin: 0 0 0.5rem 0;">Reviews Written</p>
                            <h3 style="font-size: 2.5rem; font-weight: 700; color: #1a1a2e; margin: 0;">0</h3>
                        </div>
                        <div style="font-size: 3rem; color: #00b4d8;">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); border: 1px solid rgba(100, 116, 139, 0.1); border-radius: 20px; padding: 2.5rem; box-shadow: 0 4px 20px rgba(31, 38, 135, 0.08);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: #1a1a2e; margin: 0;">
                        <i class="fas fa-music" style="color: #7c3aed; margin-right: 0.5rem;"></i>
                        Welcome to ConcertHub!
                    </h2>
                </div>
                
                <div style="color: #4a5568; line-height: 1.8;">
                    <p>👋 You're all set! You're now logged in to your ConcertHub account.</p>
                    <p>Here, you can:</p>
                    <ul style="margin-left: 1.5rem;">
                        <li>✨ <strong>Browse & Book</strong> - Explore thousands of live concerts and secure your tickets</li>
                        <li>🎫 <strong>Manage Orders</strong> - View and track your concert bookings</li>
                        <li>⭐ <strong>Leave Reviews</strong> - Share your concert experiences with other fans</li>
                        <li>❤️ <strong>Save Favorites</strong> - Keep track of events you're interested in</li>
                    </ul>
                    <p style="margin-top: 1.5rem;">
                        <a href="{{ route('concerts.index') }}" style="display: inline-block; background: linear-gradient(135deg, #7c3aed, #00b4d8); color: white; padding: 0.8rem 2rem; border-radius: 12px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 15px rgba(124, 58, 237, 0.35); transition: all 0.3s ease;">
                            🎵 Start Exploring Events
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

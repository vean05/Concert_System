@extends('layouts.app')

@section('title', $concert->title . ' - Admin - ConcertHub')

@section('content')
<style>
    .admin-detail-page {
        padding: 2rem 0;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 1.5rem;
        color: #7c3aed;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .back-link:hover {
        transform: translateX(-5px);
    }

    .detail-header {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .detail-image {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .detail-image img {
        max-width: 100%;
        border-radius: 8px;
        max-height: 400px;
        object-fit: cover;
    }

    .no-image {
        color: #999;
        text-align: center;
    }

    .detail-info {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
    }

    .detail-info h1 {
        font-size: 2rem;
        color: #1a1a2e;
        margin: 0 0 1rem 0;
    }

    .info-row {
        display: flex;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .info-label {
        font-weight: 700;
        color: #7c3aed;
        min-width: 120px;
        margin-right: 1rem;
    }

    .info-value {
        color: #4a5568;
        flex: 1;
    }

    .actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #f0f0f0;
    }

    .btn {
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        flex: 1;
        text-align: center;
    }

    .btn-edit {
        background: linear-gradient(135deg, #00b4d8 0%, #0096c7 100%);
        color: white;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 180, 216, 0.3);
    }

    .btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-item {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
    }

    .stat-item .label {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .stat-item .value {
        font-size: 2rem;
        font-weight: 700;
        color: #7c3aed;
    }

    .section-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.12);
        margin-bottom: 2rem;
    }

    .section-card h2 {
        font-size: 1.5rem;
        color: #1a1a2e;
        margin: 0 0 1.5rem 0;
    }

    .description {
        line-height: 1.6;
        color: #4a5568;
        white-space: pre-wrap;
    }

    .tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .tag {
        background: rgba(124, 58, 237, 0.1);
        border: 1px solid rgba(124, 58, 237, 0.3);
        color: #7c3aed;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .detail-header {
            grid-template-columns: 1fr;
        }

        .actions {
            flex-direction: column;
        }

        .btn {
            flex: unset;
        }
    }
</style>

<div class="admin-detail-page container">
    <a href="javascript:history.back()" class="back-link" style="display: inline-block; margin-bottom: 1.5rem; color: #7c3aed; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <!-- Main Details -->
    <div class="detail-header">
        <div class="detail-image">
            @if($concert->image_path)
                <img src="{{ asset('storage/' . $concert->image_path) }}" alt="{{ $concert->title }}">
            @else
                <div class="no-image">
                    <i class="fas fa-image" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p>No image available</p>
                </div>
            @endif
        </div>

        <div class="detail-info">
            <h1>{{ $concert->title }}</h1>
            
            <div class="info-row">
                <span class="info-label"><i class="fas fa-user-music"></i> Artist</span>
                <span class="info-value">{{ $concert->artist }}</span>
            </div>

            <div class="info-row">
                <span class="info-label"><i class="fas fa-map-marker-alt"></i> Venue</span>
                <span class="info-value">{{ $concert->venue }}</span>
            </div>

            <div class="info-row">
                <span class="info-label"><i class="fas fa-calendar-alt"></i> Date</span>
                <span class="info-value">{{ $concert->date->format('F d, Y \a\t h:i A') }}</span>
            </div>

            <div class="info-row">
                <span class="info-label"><i class="fas fa-dollar-sign"></i> Price</span>
                <span class="info-value">${{ $concert->ticket_price }}</span>
            </div>

            <div class="info-row">
                <span class="info-label"><i class="fas fa-user"></i> Creator</span>
                <span class="info-value">{{ $concert->creator->name ?? 'Unknown' }}</span>
            </div>

            <div class="actions">
                <a href="{{ route('concerts.edit', $concert) }}" class="btn btn-edit">
                    <i class="fas fa-edit"></i> Edit Concert
                </a>
                <form action="{{ route('admin.concerts.delete', $concert) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-delete" style="width: 100%;">
                        <i class="fas fa-trash"></i> Delete Concert
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="label">Total Tickets</div>
            <div class="value">{{ $concert->total_ticket }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Tickets Sold</div>
            <div class="value">{{ $concert->orders->count() }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Reviews</div>
            <div class="value">{{ $concert->reviews->count() }}</div>
        </div>
        <div class="stat-item">
            <div class="label">Favourited By</div>
            <div class="value">{{ $concert->favouritedBy()->count() }}</div>
        </div>
    </div>

    <!-- Description -->
    <div class="section-card">
        <h2><i class="fas fa-align-left" style="color: #7c3aed;"></i> Description</h2>
        <div class="description">{{ $concert->description }}</div>
    </div>

    <!-- Seating Areas -->
    @if($seatingAreas)
        <div class="section-card">
            <h2><i class="fas fa-chair" style="color: #7c3aed;"></i> Seating Areas</h2>
            <div class="tags">
                @foreach($seatingAreas as $area)
                    <span class="tag">{{ $area }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Orders -->
    @if($concert->orders->count() > 0)
        <div class="section-card">
            <h2><i class="fas fa-shopping-cart" style="color: #7c3aed;"></i> Recent Orders (Last 10)</h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; border-bottom: 2px solid #e0e0e0;">Order ID</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; border-bottom: 2px solid #e0e0e0;">Customer</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; border-bottom: 2px solid #e0e0e0;">Quantity</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; border-bottom: 2px solid #e0e0e0;">Total</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; border-bottom: 2px solid #e0e0e0;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($concert->orders->take(10) as $order)
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 1rem;">#{{ $order->id }}</td>
                                <td style="padding: 1rem;">{{ $order->user->name ?? 'Unknown' }}</td>
                                <td style="padding: 1rem;">{{ $order->quantity }}</td>
                                <td style="padding: 1rem;">${{ $order->total_price }}</td>
                                <td style="padding: 1rem;">{{ $order->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Recent Reviews -->
    @if($concert->reviews->count() > 0)
        <div class="section-card">
            <h2><i class="fas fa-star" style="color: #7c3aed;"></i> Recent Reviews (Last 5)</h2>
            @foreach($concert->reviews->take(5) as $review)
                <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f0f0f0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong>{{ $review->user->name ?? 'Anonymous' }}</strong>
                        <span style="color: #ff6b35;">
                            @for($i = 0; $i < $review->rating; $i++)
                                <i class="fas fa-star"></i>
                            @endfor
                        </span>
                    </div>
                    <p style="color: #4a5568; margin: 0;">{{ $review->comment }}</p>
                    <small style="color: #999;">{{ $review->created_at->format('M d, Y') }}</small>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

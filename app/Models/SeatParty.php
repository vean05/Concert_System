<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Friends choosing seats for one concert together. Each still pays for their own. */
class SeatParty extends Model
{
    protected $fillable = ['concert_id', 'owner_id', 'code', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function members()
    {
        return $this->hasMany(SeatPartyMember::class)->orderBy('id');
    }

    public function scopeLive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function isLive(): bool
    {
        return $this->expires_at->isFuture();
    }
}

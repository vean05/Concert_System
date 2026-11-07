<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatPartyMember extends Model
{
    protected $fillable = ['seat_party_id', 'user_id', 'picks', 'seen_at'];

    protected $casts = ['picks' => 'array', 'seen_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

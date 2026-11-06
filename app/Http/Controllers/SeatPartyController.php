<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Models\SeatParty;
use App\Services\SeatPartyService;
use Illuminate\Http\Request;

class SeatPartyController extends Controller
{
    public function start(Concert $concert, SeatPartyService $parties)
    {
        abort_unless($concert->hasSeatMap() && $concert->canBeBooked(), 404);

        return response()->json($parties->state($parties->start(auth()->user(), $concert), auth()->user()));
    }

    public function sync(Request $request, Concert $concert, string $code, SeatPartyService $parties)
    {
        $validated = $request->validate([
            'seat_ids' => 'present|array|max:8',
            'seat_ids.*' => 'integer',
        ]);

        return response()->json($parties->sync($this->party($concert, $code), auth()->user(), $validated['seat_ids']));
    }

    public function leave(Concert $concert, string $code, SeatPartyService $parties)
    {
        $parties->leave($this->party($concert, $code), auth()->user());

        return response()->json(['left' => true]);
    }

    /** Only members may read a group, and an ended group is gone. */
    private function party(Concert $concert, string $code): SeatParty
    {
        $party = SeatParty::live()->where('concert_id', $concert->id)->where('code', $code)->firstOrFail();
        abort_unless($party->members()->where('user_id', auth()->id())->exists(), 403);

        return $party;
    }
}

<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Concert;
use App\Models\Seat;
use App\Models\SeatParty;
use App\Models\User;
use Illuminate\Support\Str;

class SeatPartyService
{
    public const MAX_MEMBERS = 8;
    public const LIFETIME_HOURS = 2;
    public const ONLINE_SECONDS = 20;

    /** The buyer's running group for this concert, or a new one. */
    public function start(User $user, Concert $concert): SeatParty
    {
        $party = SeatParty::live()
            ->where('concert_id', $concert->id)
            ->where('owner_id', $user->id)
            ->first();

        if (! $party) {
            $party = SeatParty::create([
                'concert_id' => $concert->id,
                'owner_id' => $user->id,
                'code' => $this->newCode(),
                'expires_at' => now()->addHours(self::LIFETIME_HOURS),
            ]);
        }

        $party->members()->firstOrCreate(['user_id' => $user->id], ['seen_at' => now()]);

        return $party;
    }

    public function join(User $user, Concert $concert, string $code): SeatParty
    {
        $party = SeatParty::where('concert_id', $concert->id)->where('code', $code)->first();

        if (! $party || ! $party->isLive()) {
            throw new BookingException('That group link has ended. Ask your friend for a new one.');
        }

        $isMember = $party->members()->where('user_id', $user->id)->exists();

        if (! $isMember && $party->members()->count() >= self::MAX_MEMBERS) {
            throw new BookingException('That group is full.');
        }

        $party->members()->firstOrCreate(['user_id' => $user->id], ['seen_at' => now()]);

        return $party;
    }

    /**
     * Share what this member is looking at and read back what the others are.
     * Picks are a preview between friends: they reserve nothing.
     *
     * @param  array<int, int>  $seatIds
     */
    public function sync(SeatParty $party, User $user, array $seatIds): array
    {
        $member = $party->members()->where('user_id', $user->id)->firstOrFail();
        $sectionIds = $party->concert->sections()->pluck('id');

        $member->update([
            'picks' => Seat::whereIn('seat_section_id', $sectionIds)
                ->whereIn('id', array_slice(array_map('intval', $seatIds), 0, SeatHoldService::MAX_SEATS))
                ->pluck('id')
                ->all(),
            'seen_at' => now(),
        ]);

        return $this->state($party, $user);
    }

    public function leave(SeatParty $party, User $user): void
    {
        $party->members()->where('user_id', $user->id)->delete();

        if ($party->members()->doesntExist()) {
            $party->delete();
        }
    }

    public function state(SeatParty $party, User $user): array
    {
        $onlineSince = now()->subSeconds(self::ONLINE_SECONDS);

        return [
            'code' => $party->code,
            'link' => route('seats.show', [$party->concert_id, 'party' => $party->code]),
            'members' => $party->members()->with('user:id,name')->get()->values()->map(function ($member, $slot) use ($user, $onlineSince) {
                return [
                    'slot' => $slot,
                    // First names only: friends of friends may share a link.
                    'name' => Str::before(trim($member->user->name), ' '),
                    'me' => $member->user_id === $user->id,
                    'online' => (bool) optional($member->seen_at)->greaterThan($onlineSince),
                    'picks' => $member->picks ?? [],
                ];
            }),
        ];
    }

    private function newCode(): string
    {
        do {
            $code = Str::lower(Str::random(10));
        } while (SeatParty::where('code', $code)->exists());

        return $code;
    }
}

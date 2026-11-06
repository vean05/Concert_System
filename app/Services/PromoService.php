<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\User;

class PromoService
{
    /**
     * The code if this buyer may use it right now. Lock the row when the result
     * is about to be spent, so the last use cannot be taken twice.
     */
    public function usable(User $user, string $code, bool $lock = false): PromoCode
    {
        $query = PromoCode::where('code', strtoupper(trim($code)));
        $promo = ($lock ? $query->lockForUpdate() : $query)->first();

        if (! $promo || ! $promo->is_active) {
            throw new BookingException('That code is not valid.');
        }

        if ($promo->expires_at && $promo->expires_at->isPast()) {
            throw new BookingException('That code has expired.');
        }

        if ($promo->max_uses !== null && $promo->used_count >= $promo->max_uses) {
            throw new BookingException('That code has been fully used.');
        }

        // Sending tickets on does not free the code up again.
        $alreadyUsed = Order::where('promo_code_id', $promo->id)
            ->where('status', Order::CONFIRMED)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)->orWhere('transferred_from_id', $user->id);
            })
            ->exists();

        if ($alreadyUsed) {
            throw new BookingException('You have already used this code.');
        }

        return $promo;
    }
}

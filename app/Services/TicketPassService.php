<?php

namespace App\Services;

use App\Models\Order;

/**
 * The code on a ticket changes every half minute and is signed with the app key,
 * so a screenshot stops working soon after it is taken. The door accepts the
 * current code and the one before it, to allow for a slow scan.
 */
class TicketPassService
{
    public const SECONDS = 30;

    public const ADMITTED = 'admitted';
    public const ALREADY_IN = 'already_in';
    public const EXPIRED = 'expired';
    public const CANCELLED = 'cancelled';
    public const INVALID = 'invalid';

    public function code(Order $order, ?int $time = null): string
    {
        $window = $this->window($time);

        return "{$order->reference}.{$window}.{$this->signature($order, $window)}";
    }

    public function secondsLeft(?int $time = null): int
    {
        $time = $time ?? time();

        return self::SECONDS - ($time % self::SECONDS);
    }

    /**
     * Check a scanned code and let the ticket in once.
     *
     * @return array{status: string, order: ?Order}
     */
    public function admit(string $code, ?int $time = null): array
    {
        $parts = explode('.', trim($code));

        if (count($parts) !== 3 || ! preg_match('/^CH-(\d+)$/', $parts[0], $reference)) {
            return ['status' => self::INVALID, 'order' => null];
        }

        [, $window, $signature] = $parts;
        $order = Order::with('concert', 'user')->find((int) $reference[1]);

        if (! $order || ! ctype_digit($window) || ! hash_equals($this->signature($order, (int) $window), $signature)) {
            return ['status' => self::INVALID, 'order' => null];
        }

        if ($order->status === Order::CANCELLED) {
            return ['status' => self::CANCELLED, 'order' => $order];
        }

        if ($this->window($time) - (int) $window > 1) {
            return ['status' => self::EXPIRED, 'order' => $order];
        }

        // Only one scan can win this update, even if two doors read it at once.
        $admitted = Order::whereKey($order->id)->whereNull('checked_in_at')->update(['checked_in_at' => now()]);

        return ['status' => $admitted ? self::ADMITTED : self::ALREADY_IN, 'order' => $order->fresh(['concert', 'user'])];
    }

    private function window(?int $time): int
    {
        return (int) floor(($time ?? time()) / self::SECONDS);
    }

    private function signature(Order $order, int $window): string
    {
        return substr(hash_hmac('sha256', "{$order->id}|{$order->user_id}|{$window}", config('app.key')), 0, 16);
    }
}

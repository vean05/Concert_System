<?php

namespace App\Services;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;

class SalesMapService
{
    /** Real sales per block, from confirmed orders only. */
    public function blocks(Concert $concert): array
    {
        $sectionIds = $concert->sections()->pluck('id');
        $seats = Seat::whereIn('seat_section_id', $sectionIds)
            ->selectRaw('block, count(*) as seats')
            ->groupBy('block')
            ->pluck('seats', 'block');

        $sales = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('seats', 'seats.id', '=', 'order_items.seat_id')
            ->whereIn('seats.seat_section_id', $sectionIds)
            ->where('orders.status', Order::CONFIRMED)
            ->selectRaw('seats.block as block, sum(order_items.quantity) as sold, sum(order_items.quantity * order_items.unit_price) as revenue')
            ->selectRaw('sum(case when orders.created_at >= ? then order_items.quantity else 0 end) as recent', [now()->subDay()])
            ->groupBy('seats.block')
            ->get()
            ->keyBy('block');

        return $seats->map(function ($count, $block) use ($sales) {
            $sale = $sales[$block] ?? null;
            $sold = (int) optional($sale)->sold;

            return [
                'block' => (string) $block,
                'seats' => (int) $count,
                'sold' => $sold,
                'percent' => round($sold / $count * 100, 1),
                'revenue' => (float) optional($sale)->revenue,
                'recent' => (int) optional($sale)->recent,
            ];
        })->sortByDesc('sold')->values()->all();
    }
}

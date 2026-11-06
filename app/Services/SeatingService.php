<?php

namespace App\Services;

use App\Models\Concert;
use App\Models\SeatSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SeatingService
{
    public const SEATS_PER_ROW = 20;
    public const MAX_SEATED_CAPACITY = 20000;
    public const COLORS = ['#FF5A36', '#FFC53D', '#3DD6C6', '#8FA6FF', '#F27AC0', '#B7E34A'];

    /**
     * Make a concert's sections match the submitted rows. Sold tickets are
     * protected: a section cannot shrink below its sales, change type, or be
     * removed once tickets in it have been sold.
     *
     * @param  array<int, array{id?: int|string|null, name: string, type: string, price: mixed, capacity: mixed}>  $rows
     */
    public function sync(Concert $concert, array $rows): void
    {
        DB::transaction(function () use ($concert, $rows) {
            $existing = $concert->sections()->lockForUpdate()->get()->keyBy('id');
            $keptIds = collect($rows)->pluck('id')->filter()->map(function ($id) {
                return (int) $id;
            });

            foreach ($existing->except($keptIds->all()) as $removed) {
                if ($removed->soldCount() > 0) {
                    $this->fail("{$removed->name} has sold tickets and cannot be removed.");
                }
                $removed->delete();
            }

            foreach (array_values($rows) as $index => $row) {
                $attributes = [
                    'name' => trim($row['name']),
                    'type' => $row['type'],
                    'price' => $row['price'],
                    'capacity' => (int) $row['capacity'],
                    'color' => self::COLORS[$index % count(self::COLORS)],
                    'sort_order' => $index,
                ];

                if ($attributes['type'] === SeatSection::SEATED && $attributes['capacity'] > self::MAX_SEATED_CAPACITY) {
                    $this->fail("{$attributes['name']} is too large for numbered seats. Use standing, or keep it under " . number_format(self::MAX_SEATED_CAPACITY) . '.');
                }

                $section = empty($row['id']) ? null : $existing->get((int) $row['id']);

                if (! empty($row['id']) && ! $section) {
                    $this->fail('One of the ticket types no longer exists. Reload the page and try again.');
                }

                $section ? $this->updateSection($section, $attributes) : $this->createSection($concert, $attributes);
            }

            $sections = $concert->sections()->get();
            $concert->update([
                'ticket_price' => $sections->min('price'),
                'total_ticket' => $sections->sum('capacity'),
            ]);
        });
    }

    /**
     * A concert on a venue map has fixed seats, so only names and prices can change.
     */
    public function updateTiers(Concert $concert, array $rows): void
    {
        DB::transaction(function () use ($concert, $rows) {
            foreach ($rows as $row) {
                $concert->sections()->whereKey($row['id'] ?? 0)->update([
                    'name' => trim($row['name']),
                    'price' => $row['price'],
                ]);
            }

            $concert->update(['ticket_price' => $concert->sections()->min('price')]);
        });
    }

    /** Row labels run A..Z, AA, AB.. like a venue plan. */
    public function rowLabel(int $index): string
    {
        $label = '';

        for ($index++; $index > 0; $index = intdiv($index - 1, 26)) {
            $label = chr(65 + ($index - 1) % 26) . $label;
        }

        return $label;
    }

    private function createSection(Concert $concert, array $attributes): void
    {
        $section = $concert->sections()->create($attributes);

        if ($section->isSeated()) {
            $this->addSeats($section, 0, $section->capacity);
        }
    }

    private function updateSection(SeatSection $section, array $attributes): void
    {
        $sold = $section->soldCount();

        if ($attributes['capacity'] < $sold) {
            $this->fail("{$section->name} has {$sold} tickets sold, so its capacity cannot go below that.");
        }

        if ($attributes['type'] !== $section->type) {
            if ($sold > 0) {
                $this->fail("{$section->name} has sold tickets, so its type cannot change.");
            }
            $section->seats()->delete();
        }

        $section->update($attributes);

        if ($section->isSeated()) {
            $this->resizeSeats($section);
        }
    }

    private function resizeSeats(SeatSection $section): void
    {
        $count = $section->seats()->count();

        if ($section->capacity > $count) {
            $this->addSeats($section, $count, $section->capacity - $count);
        } elseif ($section->capacity < $count) {
            // Capacity >= sold is already enforced, so enough empty seats exist.
            $ids = $section->seats()->whereNull('order_id')->orderByDesc('id')
                ->limit($count - $section->capacity)->pluck('id');

            foreach ($ids->chunk(1000) as $chunk) {
                $section->seats()->whereIn('id', $chunk)->delete();
            }
        }
    }

    private function addSeats(SeatSection $section, int $alreadyCreated, int $count): void
    {
        $seats = [];

        for ($position = $alreadyCreated; $position < $alreadyCreated + $count; $position++) {
            $seats[] = [
                'seat_section_id' => $section->id,
                'row_label' => $this->rowLabel(intdiv($position, self::SEATS_PER_ROW)),
                'number' => $position % self::SEATS_PER_ROW + 1,
            ];

            if (count($seats) === 1000) {
                DB::table('seats')->insert($seats);
                $seats = [];
            }
        }

        if ($seats) {
            DB::table('seats')->insert($seats);
        }
    }

    private function fail(string $message): void
    {
        throw ValidationException::withMessages(['sections' => $message]);
    }
}

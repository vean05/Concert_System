<?php

namespace App\Services;

use App\Models\Concert;
use Generator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VenueLayoutService
{
    private const INSERT_BATCH = 1000;
    private const ARC_SAMPLES = 48;
    private const FACING_STAGE = -M_PI_2;

    /**
     * @param  array<string, float>  $prices  tier key => ticket price
     */
    public function apply(Concert $concert, string $key, array $prices): void
    {
        $layout = $this->layout($key);

        DB::transaction(function () use ($concert, $key, $layout, $prices) {
            $sectionIds = [];
            $sortOrder = 0;

            foreach ($layout['tiers'] as $tierKey => $tier) {
                $sectionIds[$tierKey] = $concert->sections()->create([
                    'name' => $tier['name'],
                    'type' => 'seated',
                    'price' => $prices[$tierKey],
                    'capacity' => 0,
                    'color' => $tier['color'],
                    'sort_order' => $sortOrder++,
                ])->id;
            }

            $batch = [];
            foreach ($this->seats($layout) as $seat) {
                $batch[] = [
                    'seat_section_id' => $sectionIds[$seat['tier']],
                    'block' => $seat['block'],
                    'row_label' => $seat['row'],
                    'number' => $seat['number'],
                    'x' => $seat['x'],
                    'y' => $seat['y'],
                    'z' => $seat['z'],
                    'rotation' => $seat['rotation'],
                ];

                if (count($batch) === self::INSERT_BATCH) {
                    DB::table('seats')->insert($batch);
                    $batch = [];
                }
            }

            if ($batch) {
                DB::table('seats')->insert($batch);
            }

            foreach ($concert->sections()->get() as $section) {
                $section->update(['capacity' => $section->seats()->count()]);
            }

            $sections = $concert->sections()->get();
            $concert->update([
                'venue_layout' => $key,
                'ticket_price' => $sections->min('price'),
                'total_ticket' => $sections->sum('capacity'),
            ]);
        });
    }

    public function layout(string $key): array
    {
        $layout = config("venues.{$key}");

        if (! $layout) {
            throw new InvalidArgumentException("Unknown venue layout [{$key}].");
        }

        return $layout;
    }

    /**
     * Every seat in a layout with its position.
     */
    public function seats(array $layout): Generator
    {
        if (isset($layout['floor'])) {
            yield from $this->floorSeats($layout);
        }

        foreach ($layout['bowls'] as $bowl) {
            yield from $this->bowlSeats($layout, $bowl);
        }
    }

    private function floorSeats(array $layout): Generator
    {
        $floor = $layout['floor'];

        foreach ($floor['blocks'] as $block) {
            $seatsPerRow = $block['seats_per_row'] ?? $floor['seats_per_row'];
            $width = ($seatsPerRow - 1) * $layout['seat_spacing'];
            $tier = $block['tier'] ?? $floor['tier'];

            // Rows run away from the stage in this direction. 0 is along +x.
            $direction = deg2rad($block['direction'] ?? 0);
            $alongX = cos($direction);
            $alongZ = sin($direction);
            $facing = isset($block['direction']) ? atan2(-$alongX, -$alongZ) : self::FACING_STAGE;

            for ($row = 0; $row < $block['rows']; $row++) {
                for ($seat = 0; $seat < $seatsPerRow; $seat++) {
                    $back = $row * $floor['row_depth'];
                    $across = $seat * $layout['seat_spacing'] - $width / 2;
                    $x = $block['x'] + $back * $alongX - $across * $alongZ;
                    $z = $block['z'] + $back * $alongZ + $across * $alongX;

                    yield $this->seat($tier, $block['label'], $row, $seat + 1, $x, 0.05, $z, $facing);
                }
            }
        }
    }

    private function bowlSeats(array $layout, array $bowl): Generator
    {
        $sweep = deg2rad($bowl['sweep_degrees'] ?? $layout['sweep_degrees']);
        $gap = deg2rad($bowl['block_gap_degrees'] ?? $layout['block_gap_degrees']);
        $blockAngle = 2 * $sweep / $bowl['blocks'];
        $sideFrom = deg2rad($layout['side_from_degrees']);
        $centreX = $layout['centre']['x'] ?? 0;
        $centreZ = $layout['centre']['z'] ?? 0;

        for ($block = 0; $block < $bowl['blocks']; $block++) {
            // Numbering runs from one side of the stage round to the other.
            $start = $sweep - $block * $blockAngle - $gap / 2;
            $end = $start - $blockAngle + $gap;
            $middle = ($start + $end) / 2;
            $tier = $bowl['tiers'][abs($middle) >= $sideFrom ? 'side' : 'end'];

            for ($row = 0; $row < $bowl['rows']; $row++) {
                $radiusX = $bowl['radius_x'] + $row * $bowl['row_depth'];
                $radiusZ = $bowl['radius_z'] + $row * $bowl['row_depth'];
                $y = $bowl['height'] + $row * $bowl['rise'];
                $points = $this->evenlySpacedPoints($start, $end, $radiusX, $radiusZ, $layout);

                foreach ($points as $index => [$x, $z]) {
                    $x += $centreX;
                    $z += $centreZ;
                    $facing = atan2($layout['focus']['x'] - $x, $layout['focus']['z'] - $z);

                    yield $this->seat($tier, (string) ($bowl['first_block'] + $block), $row, $index + 1, $x, $y, $z, $facing);
                }
            }
        }
    }

    /**
     * A plain angle step would bunch seats up in the corners of the bowl, so
     * walk the curve by distance instead.
     */
    private function evenlySpacedPoints(float $start, float $end, float $radiusX, float $radiusZ, array $layout): array
    {
        $samples = [];
        $lengths = [0.0];

        for ($i = 0; $i <= self::ARC_SAMPLES; $i++) {
            $samples[] = $this->pointOnBowl($start + ($end - $start) * $i / self::ARC_SAMPLES, $radiusX, $radiusZ, $layout);

            if ($i > 0) {
                $lengths[$i] = $lengths[$i - 1] + hypot(
                    $samples[$i][0] - $samples[$i - 1][0],
                    $samples[$i][1] - $samples[$i - 1][1]
                );
            }
        }

        $total = $lengths[self::ARC_SAMPLES];
        $count = (int) floor($total / $layout['seat_spacing']);
        $points = [];
        $segment = 1;

        for ($seat = 0; $seat < $count; $seat++) {
            $distance = ($seat + 0.5) * $total / $count;

            while ($segment < self::ARC_SAMPLES && $lengths[$segment] < $distance) {
                $segment++;
            }

            $span = $lengths[$segment] - $lengths[$segment - 1];
            $ratio = $span > 0 ? ($distance - $lengths[$segment - 1]) / $span : 0;
            $points[] = [
                $samples[$segment - 1][0] + ($samples[$segment][0] - $samples[$segment - 1][0]) * $ratio,
                $samples[$segment - 1][1] + ($samples[$segment][1] - $samples[$segment - 1][1]) * $ratio,
            ];
        }

        return $points;
    }

    /** A superellipse gives the rounded-rectangle shape of an arena bowl. */
    private function pointOnBowl(float $angle, float $radiusX, float $radiusZ, array $layout): array
    {
        $cos = cos($angle);
        $sin = sin($angle);
        $power = $layout['corner_power'];
        $radius = pow(pow(abs($cos) / $radiusX, $power) + pow(abs($sin) / $radiusZ, $power), -1 / $power);

        return [$radius * $cos, $radius * $sin];
    }

    private function seat(string $tier, string $block, int $row, int $number, float $x, float $y, float $z, float $facing): array
    {
        return [
            'tier' => $tier,
            'block' => $block,
            'row' => app(SeatingService::class)->rowLabel($row),
            'number' => $number,
            'x' => round($x, 2),
            'y' => round($y, 2),
            'z' => round($z, 2),
            'rotation' => round($facing, 3),
        ];
    }
}

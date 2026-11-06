<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ticket tiers per concert, individual seats, and order line items.
 * Existing concerts and orders are carried over as standing tiers so that
 * availability and order history stay the same.
 */
class CreateSeatingTables extends Migration
{
    private const DEFAULT_SECTION = 'General Admission';
    private const COLORS = ['#FF5A36', '#FFC53D', '#3DD6C6', '#8FA6FF', '#F27AC0', '#B7E34A'];

    public function up()
    {
        Schema::create('seat_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concert_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['seated', 'standing'])->default('standing');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('capacity');
            $table->string('color', 7)->default('#FF5A36');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['concert_id', 'name']);
        });

        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seat_section_id')->constrained()->cascadeOnDelete();
            $table->string('row_label', 8);
            $table->unsignedSmallInteger('number');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            // Reserved for the seat picker: a seat is held while the buyer pays.
            $table->foreignId('held_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('held_until')->nullable();

            $table->unique(['seat_section_id', 'row_label', 'number']);
            $table->index(['seat_section_id', 'order_id']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seat_section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seat_id')->nullable()->constrained()->nullOnDelete();
            // Snapshots: an order must keep reading correctly if the tier changes later.
            $table->string('section_name');
            $table->string('seat_label')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
        });

        $this->carryOverExistingData();

        Schema::table('concerts', function (Blueprint $table) {
            $table->dropColumn('seating_areas');
        });
    }

    public function down()
    {
        Schema::table('concerts', function (Blueprint $table) {
            $table->json('seating_areas')->nullable()->after('image_path');
        });

        foreach (DB::table('concerts')->pluck('id') as $concertId) {
            $names = DB::table('seat_sections')->where('concert_id', $concertId)
                ->where('name', '!=', self::DEFAULT_SECTION)->orderBy('sort_order')->pluck('name');

            if ($names->isNotEmpty()) {
                DB::table('concerts')->where('id', $concertId)->update(['seating_areas' => $names->toJson()]);
            }
        }

        Schema::dropIfExists('order_items');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('seat_sections');
    }

    private function carryOverExistingData(): void
    {
        $now = now();

        foreach (DB::table('concerts')->orderBy('id')->get() as $concert) {
            $areas = json_decode($concert->seating_areas ?? '[]', true) ?: [];
            $names = array_values(array_unique(array_filter(array_map('strval', $areas))));
            $names = $names ?: [self::DEFAULT_SECTION];

            // Areas never had their own capacity, so divide it evenly.
            $share = intdiv($concert->total_ticket, count($names));
            $remainder = $concert->total_ticket % count($names);
            $firstSectionId = null;

            foreach ($names as $index => $name) {
                $sectionId = DB::table('seat_sections')->insertGetId([
                    'concert_id' => $concert->id,
                    'name' => mb_substr($name, 0, 191),
                    'type' => 'standing',
                    'price' => $concert->ticket_price,
                    'capacity' => $share + ($index === 0 ? $remainder : 0),
                    'color' => self::COLORS[$index % count(self::COLORS)],
                    'sort_order' => $index,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $firstSectionId = $firstSectionId ?? $sectionId;
            }

            foreach (DB::table('orders')->where('concert_id', $concert->id)->get() as $order) {
                DB::table('order_items')->insert([
                    'order_id' => $order->id,
                    'seat_section_id' => $firstSectionId,
                    'section_name' => mb_substr($names[0], 0, 191),
                    'quantity' => $order->quantity,
                    'unit_price' => $order->quantity > 0 ? round($order->total_price / $order->quantity, 2) : 0,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ]);
            }
        }
    }
}

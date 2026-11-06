<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Listing details for the marketplace, a published flag so a concert can be
 * hidden without losing it, and 3D positions for seats on a venue map.
 */
class AddMarketAndSeatMapFields extends Migration
{
    public function up()
    {
        Schema::table('concerts', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('image_path');
            $table->string('tagline')->nullable()->after('title');
            $table->string('genre', 60)->nullable()->after('artist');
            $table->string('city', 100)->nullable()->after('venue');
            $table->string('doors_time', 5)->nullable()->after('date');
            $table->string('start_time', 5)->nullable()->after('doors_time');
            $table->json('lineup')->nullable()->after('description');
            $table->string('venue_layout', 60)->nullable()->after('lineup');
        });

        Schema::table('seats', function (Blueprint $table) {
            $table->string('block', 16)->default('')->after('seat_section_id');
            $table->float('x')->default(0);
            $table->float('y')->default(0);
            $table->float('z')->default(0);
            $table->float('rotation')->default(0);

            $table->dropUnique(['seat_section_id', 'row_label', 'number']);
            $table->unique(['seat_section_id', 'block', 'row_label', 'number']);
        });
    }

    public function down()
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->dropUnique(['seat_section_id', 'block', 'row_label', 'number']);
            $table->unique(['seat_section_id', 'row_label', 'number']);
            $table->dropColumn(['block', 'x', 'y', 'z', 'rotation']);
        });

        Schema::table('concerts', function (Blueprint $table) {
            $table->dropColumn([
                'is_published', 'tagline', 'genre', 'city',
                'doors_time', 'start_time', 'lineup', 'venue_layout',
            ]);
        });
    }
}

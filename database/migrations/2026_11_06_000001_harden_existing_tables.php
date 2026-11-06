<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The original tables were created on a server whose default engine is MyISAM,
 * which ignores foreign keys and transactions. Booking depends on both, so
 * convert the tables to InnoDB and add the constraints that never took effect.
 */
class HardenExistingTables extends Migration
{
    private const TABLES = [
        'users', 'password_resets', 'personal_access_tokens', 'migrations',
        'concerts', 'orders', 'reviews', 'concert_user', 'payment_cards',
    ];

    /** [table, column, referenced table, on delete] */
    private const FOREIGN_KEYS = [
        ['concerts', 'created_by', 'users', 'cascade'],
        ['orders', 'user_id', 'users', 'cascade'],
        ['orders', 'concert_id', 'concerts', 'cascade'],
        ['orders', 'payment_card_id', 'payment_cards', 'set null'],
        ['reviews', 'user_id', 'users', 'cascade'],
        ['reviews', 'concert_id', 'concerts', 'cascade'],
        ['concert_user', 'user_id', 'users', 'cascade'],
        ['concert_user', 'concert_id', 'concerts', 'cascade'],
        ['payment_cards', 'user_id', 'users', 'cascade'],
    ];

    public function up()
    {
        // Cards deleted while foreign keys were not enforced left dangling
        // references; "set null" is what the declared constraint would have done.
        DB::table('orders')
            ->whereNotNull('payment_card_id')
            ->whereNotIn('payment_card_id', DB::table('payment_cards')->select('id'))
            ->update(['payment_card_id' => null]);

        if (DB::getDriverName() === 'mysql') {
            foreach (self::TABLES as $table) {
                DB::statement("ALTER TABLE `{$table}` ENGINE=InnoDB");
            }

            foreach (self::FOREIGN_KEYS as [$table, $column, $references, $onDelete]) {
                if (! $this->foreignKeyExists($table, $column)) {
                    Schema::table($table, function (Blueprint $blueprint) use ($column, $references, $onDelete) {
                        $blueprint->foreign($column)->references('id')->on($references)->onDelete($onDelete);
                    });
                }
            }
        }

        Schema::table('concerts', function (Blueprint $table) {
            $table->index('date');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['concert_id', 'status']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'concert_id']);
        });
    }

    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            // MySQL needs an index on user_id for the foreign key once the unique index goes.
            $table->index('user_id', 'reviews_user_id_index');
            $table->dropUnique(['user_id', 'concert_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            // Same for concert_id, whose foreign key now relies on the composite index.
            $table->index('concert_id', 'orders_concert_id_index');
            $table->dropIndex(['concert_id', 'status']);
        });

        Schema::table('concerts', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });
    }

    private function foreignKeyExists(string $table, string $column): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }
}

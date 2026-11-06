<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Users had two admin flags that could disagree: policies read `role` while the
 * admin area read `is_admin`. Keep `is_admin` and carry `role` over to it.
 */
class UseIsAdminAsOnlyAdminFlag extends Migration
{
    public function up()
    {
        DB::table('users')->where('role', 'admin')->update(['is_admin' => true]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user'])->default('user');
        });

        DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
    }
}

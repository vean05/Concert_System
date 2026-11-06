<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromosAndTicketLifecycle extends Migration
{
    public function up()
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->unsignedTinyInteger('percent_off');
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount', 10, 2)->default(0)->after('total_price');
            $table->foreignId('promo_code_id')->nullable()->after('discount')->constrained('promo_codes')->nullOnDelete();
            $table->foreignId('transferred_from_id')->nullable()->after('promo_code_id')->constrained('users')->nullOnDelete();
            $table->timestamp('transferred_at')->nullable()->after('transferred_from_id');
            $table->timestamp('checked_in_at')->nullable()->after('transferred_at');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropConstrainedForeignId('transferred_from_id');
            $table->dropColumn(['discount', 'transferred_at', 'checked_in_at']);
        });

        Schema::dropIfExists('promo_codes');
    }
}

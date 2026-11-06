<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeatPartiesTables extends Migration
{
    public function up()
    {
        Schema::create('seat_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('concert_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('code', 12)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('seat_party_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seat_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('picks')->nullable();
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
            $table->unique(['seat_party_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seat_party_members');
        Schema::dropIfExists('seat_parties');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mountain');
            $table->date('start_date');
            $table->string('name');
            $table->string('nik');
            $table->string('gender');
            $table->string('phone_number');
            $table->text('address');
            $table->string('citizen');
            $table->string('id_card');
            $table->integer('price');
            $table->timestamps();

            $table->foreign('id_mountain')
                  ->references('id')
                  ->on('mountains')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
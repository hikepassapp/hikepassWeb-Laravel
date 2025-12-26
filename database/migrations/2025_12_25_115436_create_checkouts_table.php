<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_checkin');
            $table->text('item_list');
            $table->date('checkout_date');
            $table->timestamps();

            $table->foreign('id_checkin')
                  ->references('id')
                  ->on('checkins')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
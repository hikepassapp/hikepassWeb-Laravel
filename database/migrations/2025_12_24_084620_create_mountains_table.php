<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mountains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('manager')->nullable();
            $table->string('status')->nullable();
            $table->integer('quota')->nullable();
            $table->string('location')->nullable();
            $table->string('contact')->nullable();
            $table->integer('price')->nullable();
            $table->string('duration')->nullable();
            $table->string('pos')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mountains');
    }
};

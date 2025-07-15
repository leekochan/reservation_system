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
        Schema::create('singles', function (Blueprint $table) {
            $table->id('single_id')->primary();

            $table->unsignedBigInteger('reservation_id');
            $table->foreign('reservation_id')
                ->references('reservation_id')
                ->on('reservation_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->date('start_date');
            $table->time('time_from');
            $table->time('time_to');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('singles');
    }
};

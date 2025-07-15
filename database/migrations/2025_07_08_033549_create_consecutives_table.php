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
        Schema::create('consecutive', function (Blueprint $table) {
            $table->id('consecutive_id')->primary();

            $table->unsignedBigInteger('reservation_id');
            $table->foreign('reservation_id')
                ->references('reservation_id')
                ->on('reservation_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->date('start_date');
            $table->time('start_time_from');
            $table->time('start_time_to');
            $table->date('intermediate_date');
            $table->time('intermediate_time_from');
            $table->time('intermediate_time_to');
            $table->date('end_date');
            $table->time('end_time_from');
            $table->time('end_time_to');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consecutives');
    }
};

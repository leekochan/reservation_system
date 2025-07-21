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
        Schema::create('facility_details', function (Blueprint $table) {
            $table->id('facility_details_id')->primary();
            $table->float('package');
            $table->float('per_hour_rate');

            $table->unsignedBigInteger('facility_id');
            $table->foreign('facility_id')
                ->references('facility_id')
                ->on('facilities')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_details');
    }
};

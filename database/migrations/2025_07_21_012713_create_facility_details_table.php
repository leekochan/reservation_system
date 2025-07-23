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

            $table->decimal('facility_per_hour_rate', 10, 2)->nullable();
            $table->decimal('facility_package_rate1', 10, 2)->nullable();
            $table->decimal('facility_package_rate2', 10, 2)->nullable();

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

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
        Schema::create('equipment_details', function (Blueprint $table) {
            $table->id('equipment_details_id')->primary();

            $table->decimal('equipment_per_hour_rate', 10, 2)->nullable();
            $table->decimal('equipment_package_rate1', 10, 2)->nullable();
            $table->decimal('equipment_package_rate2', 10, 2)->nullable();

            $table->unsignedBigInteger('equipment_id');
            $table->foreign('equipment_id')
                ->references('equipment_id')
                ->on('equipments')
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
        Schema::dropIfExists('eqipment_details');
    }
};

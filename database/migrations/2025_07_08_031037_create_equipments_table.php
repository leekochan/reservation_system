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
        Schema::create('equipments', function (Blueprint $table) {
            $table->bigIncrements('equipment_id');
            $table->string('equipment_name');
            $table->string('picture')->nullable();
            $table->string('units');
            $table->decimal('per_hour_rate', 10, 2)->nullable();
            $table->decimal('package_rate1', 10, 2)->nullable();
            $table->decimal('package_rate2', 10, 2)->nullable();
            $table->string('condition')->nullable();
            $table->enum('status', ['not_available', 'available'])
                ->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};

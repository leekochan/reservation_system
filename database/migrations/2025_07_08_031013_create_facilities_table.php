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
        Schema::create('facilities', function (Blueprint $table) {
            $table->bigIncrements('facility_id');
            $table->string('facility_name');
            $table->string('picture');
            $table->decimal('facility_per_hour_rate', 10, 2)->nullable();
            $table->decimal('facility_package_rate1', 10, 2)->nullable();
            $table->decimal('facility_package_rate2', 10, 2)->nullable();
            $table->string('facility_condition')->nullable();
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
        Schema::dropIfExists('facilities');
    }
};

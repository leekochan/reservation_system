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
        Schema::table('reservation_requests', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['equipment_id']);
            
            // Make equipment_id nullable
            $table->unsignedBigInteger('equipment_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('equipment_id')
                ->references('equipment_id')
                ->on('equipments')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservation_requests', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['equipment_id']);
            
            // Make equipment_id not nullable again
            $table->unsignedBigInteger('equipment_id')->nullable(false)->change();
            
            // Re-add the original foreign key constraint
            $table->foreign('equipment_id')
                ->references('equipment_id')
                ->on('equipments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};

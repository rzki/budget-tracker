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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->uuid('overtimeId')->unique();
            $table->foreignId('salary_package_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['weekday', 'weekend']); // Weekday or Weekend overtime
            $table->decimal('hours', 5, 2); // Number of overtime hours
            $table->date('overtime_date');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['salary_package_id', 'overtime_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};

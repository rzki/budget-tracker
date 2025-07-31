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
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->uuid('salaryComponentId')->unique();
            $table->foreignId('salary_package_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., 'Base Salary', 'Phone Credit', 'Parking', 'Meal', 'Transport'
            $table->enum('type', ['fixed', 'relative']); // fixed = static amount, relative = based on work days
            $table->integer('amount');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
};

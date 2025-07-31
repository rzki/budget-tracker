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
        Schema::create('salary_packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('salaryPackageId')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('base_salary');
            $table->date('calculation_period_start'); // 21st of current month
            $table->date('calculation_period_end'); // 20th of next month
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_packages');
    }
};

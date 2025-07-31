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
        Schema::table('salary_packages', function (Blueprint $table) {
            // Jamsostek, BPJS and Tax reductions
            $table->integer('jamsostek_reduction')->default(0);
            $table->integer('bpjs_reduction')->default(0);
            $table->integer('pph21_reduction')->default(0);
            
            // Attendance-based deductions
            $table->integer('sick_days')->default(0);
            $table->integer('break_days')->default(0);
            $table->integer('late_days')->default(0);
            
            // Notes for reductions
            $table->text('reduction_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_packages', function (Blueprint $table) {
            $table->dropColumn([
                'bpjs_reduction',
                'pph21_reduction',
                'sick_days',
                'break_days',
                'late_days',
                'reduction_notes'
            ]);
        });
    }
};

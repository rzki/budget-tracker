<?php

use App\Models\Budget;
use App\Models\Pocket;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_pockets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Budget::class);
            $table->foreignIdFor(Pocket::class);
            $table->integer('allocated_amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pocket_budget');
    }
};

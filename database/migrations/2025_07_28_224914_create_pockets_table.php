<?php

use App\Models\Budget;
use App\Models\User;
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
        Schema::create('pockets', function (Blueprint $table) {
            $table->id();
            $table->uuid('pocketId')->unique();
            $table->foreignIdFor(Budget::class)->nullable()->cascadeOnUpdate()->setNullOnDelete();
            $table->string('name');
            $table->integer('allocated_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

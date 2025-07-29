<?php

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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('transactionId')->unique();
            $table->foreignIdFor(Pocket::class)->nullable()->cascadeOnUpdate()->setNullOnDelete();
            $table->string('name');
            $table->integer('amount');
            $table->enum('type', ['income', 'expense'])->default('income');
            $table->string('description');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

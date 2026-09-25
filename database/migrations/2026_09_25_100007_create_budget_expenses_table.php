<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('budget_category_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->date('spent_at');
            $table->timestamps();

            $table->index(['user_id', 'spent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_expenses');
    }
};

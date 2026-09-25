<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_period_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pay_period_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('percentage', 6, 4);
            $table->decimal('base_amount', 12, 2);
            // Guardado ya calculado (base_amount * percentage) para no
            // recalcular cifras históricas si la regla cambia después.
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_period_deductions');
    }
};

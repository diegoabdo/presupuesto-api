<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pay_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->date('pay_date');
            $table->string('payment_method')->default('Transferencia');
            // medio_tiempo | tiempo_completo | mixto
            $table->string('work_schedule')->default('medio_tiempo');
            $table->timestamps();

            $table->index(['user_id', 'pay_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_periods');
    }
};

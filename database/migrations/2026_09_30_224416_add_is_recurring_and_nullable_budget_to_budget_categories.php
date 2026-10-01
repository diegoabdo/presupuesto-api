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
        Schema::table('budget_categories', function (Blueprint $table) {
            // Nula = sin límite (ej. la categoría "Sin categoría" para
            // gastos sueltos que no quieres encasillar).
            $table->decimal('monthly_budget', 12, 2)->nullable()->default(null)->change();

            // false = categoría de un solo mes (ej. "Viaje a Xela"): deja
            // de listarse en Presupuesto a partir del mes siguiente a
            // cuando se creó. true = se repite cada mes (comportamiento
            // de siempre).
            $table->boolean('is_recurring')->default(true)->after('monthly_budget');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_categories', function (Blueprint $table) {
            $table->decimal('monthly_budget', 12, 2)->default(0)->change();
            $table->dropColumn('is_recurring');
        });
    }
};

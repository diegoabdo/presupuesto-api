<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_goal_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->date('contributed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_contributions');
    }
};

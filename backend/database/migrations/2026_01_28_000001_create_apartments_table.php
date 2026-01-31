<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('number'); // ex: "12", "12A"
            $table->string('staircase')->nullable(); // ex: "A"
            $table->string('floor')->nullable(); // ex: "3"
            $table->timestamps();

            $table->index(['tenant_id', 'number']);
            $table->unique(['tenant_id', 'number', 'staircase', 'floor'], 'apartments_tenant_number_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};


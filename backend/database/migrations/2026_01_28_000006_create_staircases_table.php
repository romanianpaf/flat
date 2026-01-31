<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staircases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('code'); // ex: A, B, C2
            $table->string('name')->nullable(); // ex: "Scara A - Bloc 1"
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'code'], 'staircases_tenant_code_unique');
            $table->index(['tenant_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staircases');
    }
};


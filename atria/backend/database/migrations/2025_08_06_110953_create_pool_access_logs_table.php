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
        Schema::create('pool_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('action', ['unlock', 'lock', 'access_granted', 'access_denied']);
            $table->text('mqtt_message')->nullable();
            $table->text('esp_response')->nullable();
            $table->string('ip_address');
            $table->text('user_agent');
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['automation_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pool_access_logs');
    }
};

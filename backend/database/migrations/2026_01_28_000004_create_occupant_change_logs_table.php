<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occupant_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('occupant_id')->constrained('occupants')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('action', ['created', 'updated', 'submitted', 'approved', 'rejected', 'exported']);
            $table->json('changes')->nullable(); // before/after diff (mascat pentru CNP/CI)
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['occupant_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupant_change_logs');
    }
};


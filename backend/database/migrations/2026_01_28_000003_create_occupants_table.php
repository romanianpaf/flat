<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occupants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('apartment_id')->constrained('apartments')->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');

            // Date sensibile (criptate la nivel de model)
            $table->text('cnp')->nullable();
            $table->text('id_series')->nullable();
            $table->text('id_number')->nullable();

            $table->text('domicile_address');

            $table->enum('role_in_unit', ['owner', 'tenant', 'family', 'other']);
            $table->string('other_role_text')->nullable();

            $table->date('move_in_date');
            $table->date('move_out_date')->nullable();

            $table->boolean('is_minor')->default(false);
            $table->string('legal_guardian_name')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('reject_reason')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();

            $table->index(['apartment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupants');
    }
};


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
        Schema::create('service_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_subcategory_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('type'); // individual / company
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('service_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'service_category_id']);
            $table->index(['tenant_id', 'service_subcategory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_providers');
    }
};

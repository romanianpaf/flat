<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staircases', function (Blueprint $table) {
            // Etajele disponibile, configurate per scară (ex: ["P","1","2","3"]).
            $table->json('floors')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('staircases', function (Blueprint $table) {
            $table->dropColumn('floors');
        });
    }
};

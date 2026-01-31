<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('apartment_id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        // Migrează datele din name în first_name și last_name
        DB::table('registration_requests')->get()->each(function ($row) {
            if ($row->name) {
                $parts = explode(' ', $row->name, 2);
                DB::table('registration_requests')
                    ->where('id', $row->id)
                    ->update([
                        'first_name' => $parts[0],
                        'last_name' => $parts[1] ?? '',
                    ]);
            }
        });

        Schema::table('registration_requests', function (Blueprint $table) {
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            $table->string('name')->nullable()->after('apartment_id');
        });

        DB::table('registration_requests')->get()->each(function ($row) {
            DB::table('registration_requests')
                ->where('id', $row->id)
                ->update([
                    'name' => trim($row->first_name . ' ' . $row->last_name),
                ]);
        });

        Schema::table('registration_requests', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};

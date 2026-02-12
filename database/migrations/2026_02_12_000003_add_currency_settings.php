<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add default currency settings
        DB::table('settings')->insert([
            [
                'key' => 'currency_symbol',
                'value' => 'Rp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'show_pricing',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['currency_symbol', 'show_pricing'])->delete();
    }
};

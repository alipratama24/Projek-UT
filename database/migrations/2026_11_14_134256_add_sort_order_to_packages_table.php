<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('is_featured');
        });

        // Set default sort_order untuk paket yang sudah ada: Birthday (1), Graduation (2), Family (3), Prewedding (4)
        DB::table('packages')->where('slug', 'birthday')->update(['sort_order' => 1]);
        DB::table('packages')->where('slug', 'graduation')->update(['sort_order' => 2]);
        DB::table('packages')->where('slug', 'family-session')->update(['sort_order' => 3]);
        DB::table('packages')->where('slug', 'prewedding')->update(['sort_order' => 4]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};

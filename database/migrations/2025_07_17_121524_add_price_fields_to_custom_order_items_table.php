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
            Schema::table('custom_order_items', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->nullable()->after('quantity');
                $table->decimal('total_price', 10, 2)->nullable()->after('price');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_order_items', function (Blueprint $table) {
            $table->dropColumn(['price', 'total_price']);
        });
    }
};

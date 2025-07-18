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
            Schema::create('custom_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });

            Schema::create('custom_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('custom_order_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('brand')->nullable();
                $table->string('unit');
                $table->integer('quantity');
                $table->string('photo')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_orders');
    }
};

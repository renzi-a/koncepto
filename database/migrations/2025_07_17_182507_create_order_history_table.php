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
         Schema::create('order_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_order_id');
            $table->unsignedBigInteger('user_id');
            $table->text('reason')->nullable();
            $table->string('status')->default('cancelled');
            $table->json('items');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_history');
    }
};

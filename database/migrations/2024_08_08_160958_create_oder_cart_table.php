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
        Schema::create('oder_cart', function (Blueprint $table) {
            $table->id();
            $table->string('ma_bill'); 
            $table->foreignId('product_detail_id')->constrained('product_details');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oder_cart');
    }
};

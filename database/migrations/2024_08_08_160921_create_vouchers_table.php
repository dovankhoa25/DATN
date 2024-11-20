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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('value', 8, 2);
            $table->string('image')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('status')->default(true);
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->unsignedInteger('quantity')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
